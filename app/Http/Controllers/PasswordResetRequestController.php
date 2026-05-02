<?php

namespace App\Http\Controllers;

use App\Mail\PasswordResetApproved;
use App\Models\PasswordResetRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetRequestController extends Controller
{
    /** Non-admin user submits a password reset request from the login page */
    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => 'required|email']);

        $user = User::where('email', $request->email)->first();

        if (! $user) {
            return response()->json(['message' => 'No account found for that email.'], 404);
        }

        if (! $user->emergency_email) {
            return response()->json(['message' => 'No emergency email set. Please contact your administrator directly.'], 422);
        }

        // Prevent spam: one pending request at a time
        $existing = PasswordResetRequest::where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->exists();

        if ($existing) {
            return response()->json(['message' => 'A request is already pending. Please wait for admin approval.'], 422);
        }

        PasswordResetRequest::create(['user_id' => $user->id, 'status' => 'pending']);

        // Reset failed attempts now that they've asked for help
        $user->failed_login_attempts = 0;
        $user->save();

        return response()->json(['message' => 'Request sent. You will receive an email once the admin approves it.']);
    }

    /** Admin: list pending requests (JSON for notification badge) */
    public function pending(): JsonResponse
    {
        $requests = PasswordResetRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get()
            ->map(fn($r) => [
                'id'              => $r->id,
                'user_name'       => $r->user->name,
                'user_email'      => $r->user->email,
                'emergency_email' => $r->user->emergency_email ?? null,
                'created_at'      => $r->created_at->diffForHumans(),
            ]);

        return response()->json(['requests' => $requests, 'count' => $requests->count()]);
    }

    /** Admin: approve — generate token and email user */
    public function approve(PasswordResetRequest $resetRequest): JsonResponse
    {
        if (! Auth::user()?->isAdmin()) abort(403);

        if (! $resetRequest->isPending()) {
            return response()->json(['message' => 'Request already handled.'], 422);
        }

        $token = Str::random(64);
        $resetRequest->update([
            'status'           => 'approved',
            'token'            => $token,
            'token_expires_at' => now()->addHours(2),
        ]);

        // Send to emergency email — use admin's name as sender
        $emailTo = $resetRequest->user->emergency_email;
        $adminName = 'BSTC - ' . Auth::user()->name;
        Mail::to($emailTo)->send(new PasswordResetApproved($resetRequest, $adminName));

        return response()->json(['message' => 'Approved. The email reset link was sent to ' . $emailTo . '.']);
    }

    /** Admin: ignore */
    public function ignore(PasswordResetRequest $resetRequest): JsonResponse
    {
        if (! Auth::user()?->isAdmin()) abort(403);

        if (! $resetRequest->isPending()) {
            return response()->json(['message' => 'Request already handled.'], 422);
        }

        $resetRequest->update(['status' => 'ignored']);

        return response()->json(['message' => 'Request ignored.']);
    }

    /** Show the reset password form (via emailed link) */
    public function showResetForm(string $token): View|RedirectResponse
    {
        $resetRequest = PasswordResetRequest::where('token', $token)
            ->where('status', 'approved')
            ->where('token_expires_at', '>', now())
            ->first();

        if (! $resetRequest) {
            return redirect()->route('landing')->with('error', 'This reset link is invalid or has expired.');
        }

        // Redirect to landing page with token so the reset modal can open immediately.
        return redirect()->route('landing', ['reset_password' => 1])->with('reset_token', $token);
    }

    /** Handle the new password submission */
    public function submitReset(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'token'    => 'required|string',
            'password' => 'required|confirmed|min:8',
        ]);

        $resetRequest = PasswordResetRequest::where('token', $request->token)
            ->where('status', 'approved')
            ->where('token_expires_at', '>', now())
            ->first();

        if (! $resetRequest) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This reset link is invalid or has expired.'], 422);
            }
            return back()
                ->withErrors(['token' => 'This reset link is invalid or has expired.'])
                ->withInput();
        }

        $user = $resetRequest->user;
        $user->password = $request->password;
        $user->failed_login_attempts = 0;
        $user->save();

        $resetRequest->update(['token' => null, 'status' => 'ignored']);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Password updated successfully.']);
        }

        return redirect()->route('landing', ['login' => 1])->with('success', 'Password updated successfully. You can now sign in.');
    }
}
