<?php

namespace App\Http\Controllers;

use App\Models\LotteryRequest;
use App\Models\User;
use App\Mail\AdminNotification;
use App\Mail\CustomerAcceptanceMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LotteryRequestController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'required|string|max:10',
            'phone' => 'required|string|regex:/^\d{7,15}$/',
            'email' => 'required|email|max:255',
            'lottery_numbers' => 'required',
            'amount' => 'required|numeric|min:0.01|max:1000000',
            'currency' => 'required|string|max:10',
            'lottery_type' => 'required|string|max:100',
            'notes' => 'nullable|string'
        ], [
            'amount.max' => 'The amount entered exceeds the maximum allowed limit of 1,000,000 per request.',
            'amount.min' => 'The amount must be at least 0.01.',
            'phone.regex' => 'Phone number should be between 7 and 15 digits.',
            'country_code.required' => 'Please select a country code.',
        ]);

        // Process lottery_numbers if it comes as a string or array
        if (is_string($validated['lottery_numbers'])) {
            $validated['lottery_numbers'] = array_map('trim', explode(',', $validated['lottery_numbers']));
        } elseif (!is_array($validated['lottery_numbers'])) {
            return response()->json(['message' => 'The lottery numbers must be an array or comma-separated string.'], 422);
        }

        $validated['status'] = 'pending';

        $lotteryRequest = LotteryRequest::create($validated);

        // Send email to all approved admins
        try {
            $adminEmails = User::where('is_approved', true)->pluck('email');
            if ($adminEmails->isNotEmpty()) {
                Mail::to($adminEmails)->send(new AdminNotification($lotteryRequest));
            }
        } catch (\Exception $e) {
            // Log the error but don't fail the request if email is not configured properly
            \Log::error('Failed to send admin notification email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Lottery request submitted successfully',
            'data' => $lotteryRequest
        ], 201);
    }

    public function index(Request $request)
    {
        $query = LotteryRequest::query();

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('lottery_type') && $request->lottery_type !== 'all') {
            $query->where('lottery_type', $request->lottery_type);
        }

        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }

        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $requests
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processed'
        ]);

        $lotteryRequest = LotteryRequest::find($id);

        if (!$lotteryRequest) {
            return response()->json(['message' => 'Lottery request not found'], 404);
        }

        $oldStatus = $lotteryRequest->status;
        $lotteryRequest->status = $validated['status'];
        $lotteryRequest->save();

        // Send email to customer if marked as processed
        if ($validated['status'] === 'processed' && $oldStatus !== 'processed') {
            try {
                Mail::to($lotteryRequest->email)->send(new CustomerAcceptanceMail($lotteryRequest));
            } catch (\Exception $e) {
                \Log::error('Failed to send customer acceptance email: ' . $e->getMessage());
            }
        }

        return response()->json([
            'message' => 'Status updated successfully',
            'data' => $lotteryRequest
        ]);
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lottery_requests,id',
            'status' => 'required|in:pending,processed'
        ]);

        if ($validated['status'] === 'processed') {
            // Get the requests that are currently pending and are in the IDs list
            $pendingRequests = LotteryRequest::whereIn('id', $validated['ids'])
                ->where('status', 'pending')
                ->get();

            foreach ($pendingRequests as $lotteryRequest) {
                $lotteryRequest->status = 'processed';
                $lotteryRequest->save();

                try {
                    Mail::to($lotteryRequest->email)->send(new CustomerAcceptanceMail($lotteryRequest));
                } catch (\Exception $e) {
                    \Log::error('Failed to send bulk customer acceptance email: ' . $e->getMessage());
                }
            }
        } else {
            // Just update status normally for other transitions
            LotteryRequest::whereIn('id', $validated['ids'])
                ->update(['status' => $validated['status']]);
        }

        return response()->json([
            'message' => count($validated['ids']) . ' requests updated successfully'
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:lottery_requests,id'
        ]);

        LotteryRequest::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'message' => count($validated['ids']) . ' requests deleted successfully'
        ]);
    }
    public function shareViaEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'file' => 'required|file', // Accepts any format generated by frontend
            'message' => 'nullable|string'
        ]);

        try {
            $toEmail = $validated['email'];
            $file = $request->file('file');
            $customMessage = $validated['message'] ?? 'Please find the attached lottery requests report.';
            $filename = $file->getClientOriginalName();

            $htmlBody = '<h2>Lottery Requests Report</h2><p>' . nl2br(e($customMessage)) . '</p>';

            Mail::html($htmlBody, function ($message) use ($toEmail, $file, $filename) {
                $message->to($toEmail)
                    ->subject('Lottery Requests Report')
                    ->attach($file->getRealPath(), [
                        'as' => $filename,
                        'mime' => $file->getMimeType(),
                    ]);
            });

            return response()->json(['message' => 'Email sent successfully with attachment.']);
        } catch (\Exception $e) {
            \Log::error('Failed to send share email: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send email: ' . $e->getMessage()], 500);
        }
    }
}
