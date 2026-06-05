<?php

namespace App\Http\Controllers;

use App\Models\LotteryRequest;
use App\Models\User;
use App\Mail\AdminNotification;
use App\Mail\CustomerAcceptanceMail;
use App\Services\WatiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class LotteryRequestController extends Controller
{
    public function store(Request $request)
    {
        // Pre-validate and sanitize phone number
        $phone = preg_replace('/\D/', '', trim($request->phone ?? ''));
        $request->merge(['phone' => $phone]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'country_code' => 'required|string|regex:/^\+\d{1,4}$/',
            'phone' => 'required|string|regex:/^[0-9]{7,15}$/',
            'email' => 'required|email|max:255',
            'lottery_numbers' => 'required',
            'lottery_selections' => 'required|array|min:1',
            'number_types' => 'required|array|min:1',
            'currency' => 'nullable|string|max:10',
            'lottery_type' => 'required|string|max:100',
            'notes' => 'nullable|string'
        ], [
            'name.required' => 'Please enter your full name',
            'country_code.required' => 'Please select a country code',
            'country_code.regex' => 'Please select a valid country code',
            'phone.required' => 'Please enter your phone number',
            'phone.regex' => 'Phone number must contain 7–15 digits',
            'email.required' => 'Please enter your email address',
            'email.email' => 'Please enter a valid email address',
            'lottery_numbers.required' => 'Please enter at least one lottery number',
            'lottery_selections.required' => 'Please select at least one lottery',
            'lottery_selections.min' => 'Please select at least one lottery',
            'number_types.required' => 'Please select at least one number type',
            'number_types.min' => 'Please select at least one number type',
        ]);

        // Reject fake/spam numbers (all digits identical)
        if (preg_match('/^(.)\1+$/', $phone)) {
            return response()->json([
                'message' => 'Please enter a valid phone number',
                'errors' => ['phone' => ['Please enter a valid phone number']]
            ], 422);
        }

        // Phone is already sanitized to digits-only above
        $validated['phone'] = $phone;

        // Process lottery_numbers if it comes as a string or array
        if (is_string($validated['lottery_numbers'])) {
            $validated['lottery_numbers'] = array_map('trim', explode(',', $validated['lottery_numbers']));
        } elseif (!is_array($validated['lottery_numbers'])) {
            return response()->json(['message' => 'The lottery numbers must be an array or comma-separated string.'], 422);
        }

        // Process lottery_selections if it comes as a string
        if (is_string($validated['lottery_selections'])) {
            $validated['lottery_selections'] = array_map('trim', explode(',', $validated['lottery_selections']));
        }

        // Process number_types if it comes as a string
        if (is_string($validated['number_types'])) {
            $validated['number_types'] = array_map('trim', explode(',', $validated['number_types']));
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

        // Send WhatsApp notification via WATI to all approved admins with a saved whatsapp_number
        try {
            $adminsWithWhatsApp = User::where('is_approved', true)
                ->whereNotNull('whatsapp_number')
                ->where('whatsapp_number', '!=', '')
                ->get();
            if ($adminsWithWhatsApp->isNotEmpty()) {
                $watiService = new WatiService();
                $watiService->notifyAdmins($lotteryRequest, $adminsWithWhatsApp);
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send WATI WhatsApp notification: ' . $e->getMessage());
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
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('lottery_numbers', 'like', "%{$search}%")
                    ->orWhereRaw("CONCAT(country_code, ' ', phone) LIKE ?", ["%{$search}%"]);
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('lottery_type') && $request->lottery_type !== 'all') {
            $query->whereJsonContains('lottery_selections', $request->lottery_type);
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
