<?php

namespace App\Http\Controllers;

use App\Events\TicketsUpdated;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('name')->get();
        $onlineUserIds = array_fill_keys(User::getOnlineUserIds(), true);
        $roles = [
            User::ROLE_EMPLOYEE   => User::roleLabel(User::ROLE_EMPLOYEE),
            User::ROLE_FRONT_DESK => User::roleLabel(User::ROLE_FRONT_DESK),
            User::ROLE_IT_STAFF   => User::roleLabel(User::ROLE_IT_STAFF),
            User::ROLE_ADMIN      => User::roleLabel(User::ROLE_ADMIN),
        ];
        return view('users.index', compact('users', 'onlineUserIds', 'roles'));
    }

    public function create(): View
    {
        $roles = [
            User::ROLE_EMPLOYEE => User::roleLabel(User::ROLE_EMPLOYEE),
            User::ROLE_FRONT_DESK => User::roleLabel(User::ROLE_FRONT_DESK),
            User::ROLE_IT_STAFF => User::roleLabel(User::ROLE_IT_STAFF),
            User::ROLE_ADMIN => User::roleLabel(User::ROLE_ADMIN),
        ];

        return view('users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email'),
            ],
            'role' => 'required|in:employee,front_desk,it_staff,admin',
        ], [
            'email.unique' => 'User already exists with that email. You already put it like that.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => null,
            'role' => $validated['role'],
            'password_change_count' => 0,
        ]);

        $msg = 'User "' . $validated['name'] . '" has been created. They will set their password on first login.';

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $msg]);
        }

        return redirect()->route('users.index')->with('success', $msg);
    }

    public function edit(User $user): View
    {
        $roles = [
            User::ROLE_EMPLOYEE => User::roleLabel(User::ROLE_EMPLOYEE),
            User::ROLE_FRONT_DESK => User::roleLabel(User::ROLE_FRONT_DESK),
            User::ROLE_IT_STAFF => User::roleLabel(User::ROLE_IT_STAFF),
            User::ROLE_ADMIN => User::roleLabel(User::ROLE_ADMIN),
        ];
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => 'required|in:employee,front_desk,it_staff,admin',
            'new_password' => 'nullable|confirmed|min:8',
            'clear_password' => 'nullable|boolean',
        ], [
            'email.unique' => 'User already exists with that email. You already put it like that.',
        ]);

        $user->email = $validated['email'];
        $user->role = $validated['role'];

        if ($user->id !== auth()->id() && $user->hasPasswordSet()) {
            if (! empty($validated['clear_password'])) {
                $user->password = null;
                $user->password_change_count = 0;
            } elseif (! empty($validated['new_password'])) {
                $user->password = $validated['new_password'];
            }
        }

        $user->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => 'User "' . $user->name . '" has been updated.']);
        }

        return redirect()->route('users.index')->with('success', 'User "' . $user->name . '" has been updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse|JsonResponse
    {
        if ($user->id === auth()->id()) {
            $msg = 'You cannot delete your own account.';
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['message' => $msg], 422);
            }
            return redirect()->route('users.index')->with('error', $msg);
        }

        $name = $user->name;
        $user->delete();
        Cache::put('tickets_list_updated_at', now()->timestamp, 3600);
        try {
            event(new TicketsUpdated('User deleted.'));
        } catch (\Throwable $e) {
            // Keep deletion flow working even if broadcaster is unavailable.
        }

        $message = 'User "' . $name . '" has been removed.';
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['message' => $message]);
        }

        return redirect()->route('users.index')->with('success', $message);
    }
}
