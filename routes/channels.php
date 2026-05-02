<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// IT staff private channel — any authenticated IT staff member can listen
Broadcast::channel('it-staff', function (User $user) {
    return $user->isItStaff() && ! $user->isAdmin();
});

// User-specific channel — for admin replies targeted to a specific employee/front desk user
Broadcast::channel('user.{id}', function (User $user, $id) {
    return (int) $user->id === (int) $id;
});
