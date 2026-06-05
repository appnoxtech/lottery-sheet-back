<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // 1. Notify the newly registered admin that their request is pending
        try {
            Mail::to($user->email)->send(new \App\Mail\AdminPendingNotification($user));
        } catch (\Exception $e) {
            \Log::error('Could not send pending notification to user: ' . $e->getMessage());
        }

        // 2. Send Approval Request to all approved admins
        try {
            $adminEmails = User::where('is_approved', true)->pluck('email');
            if ($adminEmails->isNotEmpty()) {
                Mail::to($adminEmails)->send(new \App\Mail\AdminApprovalRequest($user));
            } else {
                // Fallback to super admin email if no admins approved yet
                $superAdminEmail = env('SUPER_ADMIN_EMAIL', 'master@example.com');
                Mail::to($superAdminEmail)->send(new \App\Mail\AdminApprovalRequest($user));
            }
        } catch (\Exception $e) {
            \Log::error('Could not send approval email to admins: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Registration successful. Your account is pending Super Admin approval. You will be notified via email once approved.'
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ], [
            'email.email' => 'Enter a valid email address',
            'email.required' => 'Email address is required',
            'password.required' => 'Password is required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials provided. Please check your email and password.'
            ], 422);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Your email address is not verified. Please check your inbox.', 'email_not_verified' => true], 403);
        }

        if (!$user->is_approved) {
            return response()->json(['message' => 'Your account is pending approval from the super admin.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully']);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            ['token' => $token, 'created_at' => now()]
        );

        // Send email with reset token
        try {
            Mail::to($request->email)->send(new \App\Mail\PasswordResetMail($token));
        } catch (\Exception $e) {
            \Log::error('Could not send password reset email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'We have e-mailed your password reset token!'
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'token' => 'required|string',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:100',
                'confirmed',
                'regex:/[a-z]/',      // at least one lowercase letter
                'regex:/[A-Z]/',      // at least one uppercase letter
                'regex:/[@$!%*#?&]/'  // at least one special character
            ],
        ], [
            'password.min' => 'Password must be at least 8 characters.',
            'password.max' => 'Password cannot exceed 100 characters.',
            'password.regex' => 'Password must include at least one uppercase letter, one lowercase letter, and one special character.',
            'password.confirmed' => 'The password confirmation does not match.'
        ]);

        $passwordReset = DB::table('password_resets')
            ->where('email', $request->email)
            ->where('token', $request->token)
            ->first();

        if (!$passwordReset) {
            return response()->json(['message' => 'Invalid or expired token.'], 400);
        }

        $user = User::where('email', $request->email)->first();

        // Prevent reusing current password
        if (Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'New password cannot be the same as your current password.'], 422);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_resets')->where('email', $request->email)->delete();

        return response()->json(['message' => 'Password has been successfully reset.']);
    }

    public function verifyEmail(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            return response()->json(['message' => 'Invalid verification link'], 403);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect(env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000') . '/admin/login?verified=already');
        }

        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));

        return redirect(env('NEXT_PUBLIC_FRONTEND_URL', 'http://localhost:3000') . '/admin/login?verified=1');
    }

    public function resendVerification(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email is already verified.'], 400);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification link sent!']);
    }

    public function approveAdmin(Request $request, $id, $hash)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!hash_equals((string) $hash, sha1($user->email))) {
            return response()->json(['message' => 'Invalid approval link'], 403);
        }

        if ($user->is_approved) {
            return response()->json(['message' => 'User is already approved.']);
        }

        $user->is_approved = true;
        $user->save();

        // After approval, trigger the verification email to the user
        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            \Log::error('Could not send verification email after approval: ' . $e->getMessage());
        }

        return response()->json(['message' => 'Admin access approved and verification email sent to ' . $user->email]);
    }

    public function getPendingUsers(Request $request)
    {
        if (!$this->isSuperAdmin($request->user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $users = User::where('is_approved', false)->orderBy('created_at', 'desc')->get();
        return response()->json(['data' => $users]);
    }

    public function approveUser(Request $request, $id)
    {
        if (!$this->isSuperAdmin($request->user())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->is_approved) {
            return response()->json(['message' => 'User is already approved']);
        }

        $user->is_approved = true;
        $user->save();

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Exception $e) {
            \Log::error('Could not send verification email: ' . $e->getMessage());
        }

        return response()->json(['message' => 'User approved successfully']);
    }

    private function isSuperAdmin($user)
    {
        return $user && $user->email === env('SUPER_ADMIN_EMAIL', 'master@example.com');
    }

    /**
     * Update the authenticated admin's profile (name, whatsapp_number).
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'whatsapp_number'  => [
                'nullable',
                'string',
                'regex:/^\+?[1-9]\d{6,14}$/',
            ],
        ], [
            'whatsapp_number.regex' => 'Please enter a valid WhatsApp number with country code (e.g. +919876543210)',
        ]);

        $user = $request->user();

        if (isset($validated['name'])) {
            $user->name = $validated['name'];
        }

        if (array_key_exists('whatsapp_number', $validated)) {
            // Store without the + prefix — WatiService will strip it anyway
            $user->whatsapp_number = $validated['whatsapp_number'] ?? null;
        }

        $user->save();

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user,
        ]);
    }
}
