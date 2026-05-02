<?php

namespace App\Providers;

use App\Models\TicketCategory;
use App\Models\TicketPriority;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Share ticket form data with the app layout so the submit-ticket modal
        // can be rendered inline (no AJAX fetch needed).
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            if (! auth()->check()) return;
            $user = auth()->user();
            if (! ($user->isAdmin() || $user->isFrontDesk())) return;

            $categories = Cache::remember('ticket_categories_list', 60, fn () =>
                TicketCategory::orderBy('name')->get()
            );
            $priorities = Cache::remember('ticket_priorities_list', 60, fn () =>
                TicketPriority::where('active', true)->orderBy('sort_order')->orderBy('id')->get()
            );

            $view->with('_submitTicketCategories', $categories)
                 ->with('_submitTicketPriorities', $priorities);
        });
    }
}
