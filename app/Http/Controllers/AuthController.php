<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * @return RedirectResponse|JsonResponse
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'nullable|string',
        ]);

        $user = User::where('email', $validated['email'])->first();

        // Detect old user by reading password column directly from DB (avoids any model cast/accessor issues)
        $storedPassword = $user ? DB::table('users')->where('id', $user->id)->value('password') : null;
        $isNewUser = $user && ($storedPassword === null || $storedPassword === '');

        // New user (no password set): allow email-only sign-in, then redirect to set-password
        if ($isNewUser) {
            // Don't log them in yet — send them to set-password page
            $request->session()->put('set_password_email', $user->email);
            $redirectUrl = route('landing', [
                'login' => 1,
                'set_password' => 1,
                'email' => $user->email,
            ]);
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['redirect' => $redirectUrl, 'message' => 'Please set your password.']);
            }
            return redirect($redirectUrl);
        }

        // Existing user (has password in DB): must supply password — cannot log in with email only
        if ($user && ! $isNewUser && empty($validated['password'])) {
            $message = 'Password is required for your account.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['password' => $message])->onlyInput('email');
        }

        if (empty($validated['password'])) {
            $message = 'The provided credentials do not match our records.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['message' => $message], 422);
            }
            return back()->withErrors(['email' => $message])->onlyInput('email');
        }

        if (Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $userId = Auth::id();

            if ($userId) {
                // Reset failed login attempts on success
                Auth::user()->update(['failed_login_attempts' => 0]);

                LoginLog::create([
                    'user_id' => $userId,
                    'event' => LoginLog::EVENT_LOGIN,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
                LoginLog::pruneForUser($userId, 20);
            }

            $redirectResponse = redirect()->intended(route('home'));
            $request->session()->put('post_login_target', $redirectResponse->getTargetUrl());
            $request->session()->put('post_login_swal_text', "You're signed in.");

            if ($request->wantsJson() || $request->ajax()) {
                $request->session()->flash('show_post_login_modal', true);

                return response()->json([
                    'redirect' => route('landing', [], false),
                    'message' => 'Login successful.',
                ]);
            }

            return redirect()->route('landing')->with('show_post_login_modal', true);
        }

        $message = 'The provided credentials do not match our records.';

        // Track failed attempts for non-admin users on both AJAX and normal form submits.
        $showForgotPrompt = false;
        if ($user && ! $user->isAdmin()) {
            $user->increment('failed_login_attempts');
            $showForgotPrompt = $user->fresh()->failed_login_attempts >= 3;
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'message'           => $message,
                'show_forgot_prompt' => $showForgotPrompt,
                'user_email'        => $showForgotPrompt ? $user->email : null,
            ], 422);
        }

        $redirect = back()->withErrors(['email' => $message])->onlyInput('email');
        if ($showForgotPrompt && $user) {
            $redirect->with('show_forgot_prompt', true)->with('forgot_email', $user->email);
        }
        return $redirect;
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            LoginLog::create([
                'user_id' => $userId,
                'event' => LoginLog::EVENT_LOGOUT,
                'ip_address' => $ip,
                'user_agent' => $userAgent,
            ]);
            LoginLog::pruneForUser($userId, 20);
        }

        return redirect()->route('landing')->with('success', 'You have been logged out.');
    }

    public function logoutGet(Request $request): RedirectResponse
    {
        return $this->logout($request);
    }

    /**
     * Show the set-password form (first login after admin created the user).
     */
    public function showSetPasswordForm(Request $request): RedirectResponse
    {
        $email = $request->query('email');
        if (! $email) {
            return redirect()->route('landing')->with('error', 'Please sign in with your email first.');
        }

        $user = User::where('email', $email)->whereNull('password')->first();
        if (! $user) {
            return redirect()->route('landing')->with('error', 'Invalid or expired. Please sign in again.');
        }

        return redirect()->route('landing', [
            'login' => 1,
            'set_password' => 1,
            'email' => $user->email,
        ]);
    }

    /**
     * Set password (first login) and log the user in.
     */
    public function setPassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::where('email', $validated['email'])->whereNull('password')->first();
        if (! $user) {
            return redirect()->route('landing')->with('error', 'Invalid or expired. Please sign in again.');
        }

        $user->password = $validated['password'];
        // First-time password set counts as first change
        $user->password_change_count = 1;
        $user->save();

        Auth::login($user, false);
        $request->session()->regenerate();

        if ($user->id) {
            LoginLog::create([
                'user_id' => $user->id,
                'event' => LoginLog::EVENT_LOGIN,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            LoginLog::pruneForUser($user->id, 20);
        }

        $redirectResponse = redirect()->intended(route('home'));
        $request->session()->put('post_login_target', $redirectResponse->getTargetUrl());
        $request->session()->put('post_login_swal_text', 'Your password is set. You are signed in.');

        return redirect()->route('landing')->with('show_post_login_modal', true);
    }
}
