<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\ItStaffCategoryController;
use App\Http\Controllers\Admin\StaffAnnouncementController;
use App\Http\Controllers\Admin\UserAuditController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\TicketCategoryController as AdminTicketCategoryController;
use App\Http\Controllers\Admin\TicketPriorityController as AdminTicketPriorityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessRequestController;
use App\Http\Controllers\HelpMessageController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\StaffAnnouncementAcknowledgeController;
use App\Http\Controllers\UserController;
use App\Models\StaffAnnouncement;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PasswordResetRequestController;

Route::get('/', function () {
    if (auth()->check()) {
        $openPostLoginModal = session()->pull('show_post_login_modal');
        if ($openPostLoginModal && session()->has('post_login_target')) {
            return view('landing', ['openPostLoginModal' => true]);
        }
        if (session()->has('post_login_target')) {
            session()->forget(['post_login_target', 'post_login_swal_text']);
        }

        return redirect()->route('home');
    }

    return view('landing');
})->middleware('prevent.cache')->name('landing');

// Login entry opens landing with right-slide login modal.
Route::get('login', function () {
    if (auth()->check()) return redirect()->route('home');
    return redirect()->route('landing', ['login' => 1]);
})->middleware('prevent.cache')->name('login');

Route::middleware(['guest', 'prevent.cache'])->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('access-request', [AccessRequestController::class, 'store'])->name('access-request.store');
    Route::get('set-password', [AuthController::class, 'showSetPasswordForm'])->name('auth.set-password');
    Route::post('set-password', [AuthController::class, 'setPassword']);

    // Password reset via admin approval
    Route::post('password-reset-request', [PasswordResetRequestController::class, 'store'])->name('password.reset.request');
    Route::get('reset-password/{token}', [PasswordResetRequestController::class, 'showResetForm'])->name('password.reset.form');
    Route::post('reset-password', [PasswordResetRequestController::class, 'submitReset'])->name('password.reset.submit');
});

Route::middleware(['auth', 'prevent.cache'])->group(function () {
    Route::get('/home', HomeController::class)->name('home');
    Route::get('tickets-updated-at', function () {
        return response()->json(['updated_at' => Cache::get('tickets_list_updated_at', 0)]);
    })->name('tickets.updated-at');
    Route::get('staff-announcements/version', function () {
        $user = auth()->user();
        if (! $user) {
            return response()->json(['version' => '0']);
        }

        // Admin monitors full announcement board (open + acknowledged),
        // while staff dashboards only monitor announcements visible to them.
        $query = $user->isAdmin()
            ? StaffAnnouncement::query()
            : StaffAnnouncement::openForUser($user);

        $count = (int) (clone $query)->count();
        $maxId = (int) ((clone $query)->max('id') ?? 0);
        $maxUpdated = (string) ((clone $query)->max('updated_at') ?? '');
        $maxAck = (string) ((clone $query)->max('acknowledged_at') ?? '');

        return response()->json([
            'version' => sha1($count . '|' . $maxId . '|' . $maxUpdated . '|' . $maxAck),
        ]);
    })->name('staff-announcements.version');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('logout', [AuthController::class, 'logoutGet']);
    Route::get('login-success', function () {
        if (! session()->has('post_login_target')) {
            return redirect()->route('home');
        }

        return redirect()->route('landing')->with('show_post_login_modal', true);
    })->name('auth.post-login');

    Route::post('help', [HelpMessageController::class, 'store'])->name('help.store');
    Route::get('help/messages', [HelpMessageController::class, 'myMessages'])->name('help.messages');
    Route::get('help/unread-count', [HelpMessageController::class, 'unreadCount'])->name('help.unread-count');
    Route::post('help/mark-read', [HelpMessageController::class, 'markRead'])->name('help.mark-read');

    // IT Staff: category management (JSON)
    Route::get('it/categories', [ItStaffCategoryController::class, 'index'])->name('it.categories.index');
    Route::post('it/categories', [ItStaffCategoryController::class, 'store'])->name('it.categories.store');
    Route::put('it/categories/{category}', [ItStaffCategoryController::class, 'update'])->name('it.categories.update');
    Route::delete('it/categories/{category}', [ItStaffCategoryController::class, 'destroy'])->name('it.categories.destroy');
    // IT Staff: priority management (JSON)
    Route::get('it/priorities', [ItStaffCategoryController::class, 'priorityIndex'])->name('it.priorities.index');
    Route::post('it/priorities', [ItStaffCategoryController::class, 'priorityStore'])->name('it.priorities.store');
    Route::put('it/priorities/{priority}', [ItStaffCategoryController::class, 'priorityUpdate'])->name('it.priorities.update');
    Route::delete('it/priorities/{priority}', [ItStaffCategoryController::class, 'priorityDestroy'])->name('it.priorities.destroy');
    // IT Staff: incoming help messages from admin
    Route::get('it/help-inbox', [HelpMessageController::class, 'itInbox'])->name('it.help.inbox');
    Route::post('it/help-inbox/mark-read', [HelpMessageController::class, 'itMarkRead'])->name('it.help.mark-read');
    Route::get('it/help-inbox/unread-count', [HelpMessageController::class, 'itUnreadCount'])->name('it.help.unread-count');
    // IT Staff: user threads (messenger view)
    Route::get('it/help-threads', [HelpMessageController::class, 'itThreads'])->name('it.help.threads');
    Route::get('it/help-thread/{userId}', [HelpMessageController::class, 'itThread'])->name('it.help.thread');
    Route::post('it/help-reply', [HelpMessageController::class, 'itReply'])->name('it.help.reply');
    // Admin: help conversation (IT staff inbox view for admin)
    Route::get('admin/help-conversation', [HelpMessageController::class, 'adminConversation'])->name('admin.help.conversation');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::get('security', [SettingsController::class, 'security'])->name('settings.security');
    Route::put('settings/sidebar', [SettingsController::class, 'updateSidebarPreference'])->name('settings.sidebar');
    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::get('tickets/search', [TicketController::class, 'search'])->name('tickets.search');
    Route::resource('tickets', TicketController::class)->only(['index', 'create', 'store', 'show', 'edit', 'update']);
    // Ticket quick view (modal content only)
    Route::get('tickets/{ticket}/modal', [TicketController::class, 'modal'])->name('tickets.modal');
    // Ticket edit (modal content only)
    Route::get('tickets/{ticket}/edit-modal', [TicketController::class, 'editModal'])->name('tickets.edit-modal');
    // Ticket status update (modal content only)
    Route::get('tickets/{ticket}/status-modal', [TicketController::class, 'statusModal'])->name('tickets.status-modal');
    // Ticket create (modal content only)
    Route::get('tickets/create-modal', [TicketController::class, 'createModal'])->name('tickets.create-modal');
    // Ticket quick close (Resolved -> Closed)
    Route::post('tickets/{ticket}/close', [TicketController::class, 'close'])->name('tickets.close');
    Route::match(['POST', 'DELETE'], 'tickets/{ticket}/destroy', [TicketController::class, 'destroy'])->name('tickets.destroy');
    Route::post('tickets/{ticket}/accept', [TicketController::class, 'accept'])->name('tickets.accept');
    Route::post('tickets/{ticket}/comments', [TicketController::class, 'storeComment'])->name('tickets.comments.store');

    Route::middleware('admin')->prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Global notifications (role-aware in controller; all authenticated users)
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    // JSON endpoint for live header dropdown refresh
    Route::get('notifications/header', [NotificationController::class, 'header'])->name('notifications.header');
    // Mark notifications as seen (clears badge)
    Route::post('notifications/seen', [NotificationController::class, 'markSeen'])->name('notifications.seen');
    // JSON endpoint for live notifications table refresh (admin + role-aware)
    Route::get('notifications/table', [NotificationController::class, 'table'])->name('notifications.table');

    Route::middleware('admin')->group(function () {
        Route::get('/admin/activity', [ActivityController::class, 'index'])->name('admin.activity');
        Route::get('/admin/password-reset-requests', [PasswordResetRequestController::class, 'pending'])->name('admin.password-reset-requests');
        Route::post('/admin/password-reset-requests/{resetRequest}/approve', [PasswordResetRequestController::class, 'approve'])->name('admin.password-reset-requests.approve');
        Route::post('/admin/password-reset-requests/{resetRequest}/ignore', [PasswordResetRequestController::class, 'ignore'])->name('admin.password-reset-requests.ignore');
        Route::get('/admin/notifications', [NotificationController::class, 'index'])->name('admin.notifications.index');
        Route::put('/admin/notifications/{notification}', [NotificationController::class, 'update'])->name('admin.notifications.update');
        Route::get('/admin/audit-trail/users', [UserAuditController::class, 'index'])->name('admin.audit-trail.index');
        Route::get('/admin/audit-trail/users/{user}', [UserAuditController::class, 'show'])->name('admin.audit-trail.show');
        Route::get('/admin/audit-trail/users/{user}/download', [UserAuditController::class, 'download'])->name('admin.audit-trail.download');
        Route::get('/admin/audit-trail/users/{user}/print', [UserAuditController::class, 'print'])->name('admin.audit-trail.print');

        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('settings', [AdminSettingsController::class, 'index'])->name('settings');
            Route::post('settings/reset-system-data', [AdminSettingsController::class, 'resetSystemData'])->name('settings.reset-system-data');
            Route::get('staff-announcements', [StaffAnnouncementController::class, 'index'])->name('staff-announcements.index');
            Route::get('staff-announcements/create', [StaffAnnouncementController::class, 'create'])->name('staff-announcements.create');
            Route::post('staff-announcements', [StaffAnnouncementController::class, 'store'])->name('staff-announcements.store');
            Route::delete('staff-announcements/{staffAnnouncement}', [StaffAnnouncementController::class, 'destroy'])->name('staff-announcements.destroy');

            Route::resource('categories', AdminTicketCategoryController::class)->except(['show']);
            Route::resource('priorities', AdminTicketPriorityController::class)->except(['show']);
        });
    });

    // Staff/admin acknowledgment of announcements
    Route::post('staff-announcements/{announcement}/ack', StaffAnnouncementAcknowledgeController::class)
        ->name('staff-announcements.ack');
});
