<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    /**
     * Show the settings index (account settings, profile, etc.).
     */
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('home', ['profile_tab' => 'appearance']);
    }

    /**
     * Show security settings (password management).
     */
    public function security(Request $request): RedirectResponse
    {
        return redirect()->route('home', ['profile_tab' => 'security']);
    }

    /**
     * Update the current user's sidebar collapsed preference (for any user).
     */
    public function updateSidebarPreference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'collapsed' => 'required|boolean',
        ]);

        $request->user()->update(['sidebar_collapsed' => $validated['collapsed']]);

        return response()->json(['ok' => true]);
    }
}
