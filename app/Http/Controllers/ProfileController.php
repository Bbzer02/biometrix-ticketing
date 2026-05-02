<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit(): RedirectResponse
    {
        return redirect()->route('home', ['profile_tab' => 'profile']);
    }

    public function update(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        // Users can update name, profile picture, and emergency email
        $validated = $request->validate([
            'name'                 => 'required|string|max:255',
            'emergency_email'      => 'nullable|email|max:255',
            'profile_picture'      => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'profile_picture_data' => 'nullable|string',
        ]);

        $user->name            = $validated['name'];
        $user->emergency_email = $validated['emergency_email'] ?? null;

        // New picture: cropped (base64) or direct file upload
        $dataUrl = $request->input('profile_picture_data');
        $hasNewPicture = false;
        if (! empty($dataUrl) && preg_match('/^data:image\/(jpeg|png|gif|webp);base64,/', $dataUrl)) {
            $user->profile_picture = $this->saveProfilePictureFromDataUrl($user, $dataUrl);
            $hasNewPicture = true;
        } elseif ($request->hasFile('profile_picture')) {
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
            $hasNewPicture = true;
        }

        // Remove profile picture only when no new picture was uploaded
        if ($request->boolean('remove_profile_picture') && ! $hasNewPicture && $user->profile_picture) {
            if (Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            $user->profile_picture = null;
        }

        $user->save();

        // JSON response for AJAX (modal fetch)
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'Saved successfully.']);
        }

        // If only emergency_email was submitted (from security page), redirect back there
        if ($request->has('emergency_email') && !$request->hasFile('profile_picture') && empty($request->input('profile_picture_data')) && !$request->has('remove_profile_picture')) {
            return redirect()->route('settings.security')->with('success_emergency', 'Recovery email saved.');
        }

        return redirect()->route('profile.edit')->with('success', 'Profile updated successfully.');
    }

    /**
     * Decode base64 data URL and save as profile picture. Returns stored path.
     */
    private function saveProfilePictureFromDataUrl($user, string $dataUrl): string
    {
        if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
            Storage::disk('public')->delete($user->profile_picture);
        }

        if (! preg_match('/^data:image\/(\w+);base64,(.+)$/', $dataUrl, $m)) {
            return $user->profile_picture ?? '';
        }

        $extension = $m[1] === 'jpeg' ? 'jpg' : $m[1];
        $data = base64_decode($m[2], true);
        if ($data === false || strlen($data) > 2 * 1024 * 1024) {
            return $user->profile_picture ?? '';
        }

        $filename = 'profile-pictures/' . uniqid('user_' . $user->id . '_', true) . '.' . $extension;
        Storage::disk('public')->put($filename, $data);

        return $filename;
    }

    /**
     * Update password. Only admins can set or change their password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        // Only admins can set or change their password via this endpoint.
        if (! $user->isAdmin()) {
            return redirect()
                ->route('settings.security')
                ->with('error_password', 'Only administrators can change passwords. Contact your administrator if you need a password reset.');
        }

        // First-time set (no password yet): allow for admin.
        if (empty($user->password)) {
            $validated = $request->validate([
                'password' => ['required', 'confirmed', Password::defaults()],
            ]);
            $user->password = $validated['password'];
            $user->password_change_count = 1;
            $user->save();
            return redirect()->route('settings.security')->with('success_password', 'Password set successfully.');
        }

        $rules = [
            'password' => ['required', 'confirmed', Password::defaults()],
            'current_password' => ['required', 'current_password'],
        ];
        $validated = $request->validate($rules);

        $user->password = $validated['password'];
        $user->password_change_count = ($user->password_change_count ?? 0) + 1;
        $user->save();

        return redirect()->route('settings.security')->with('success_password', 'Password updated successfully.');
    }
}
