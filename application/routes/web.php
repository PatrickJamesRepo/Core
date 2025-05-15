<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    HomeController,
    DashboardController,
    Account\ProfileController,
    Admin\ManageUsersController,
    Admin\ManageEventsController,
    Staff\ScanTicketsController
};

// Public Routes

Route::get('/', [HomeController::class, 'index']);
Route::get('event/{eventUUID}', [HomeController::class, 'event'])->name('event');

// Authentication Routes (Conditional for testing environments)
if (app()->environment(['testing', 'dusk.local', 'dusk.testing'])) {
    Auth::routes(); // Enable register + reset in test/dusk env
} else {
    Auth::routes([
        'register' => false,
        'reset'    => false,
        'verify'   => false,
    ]);
}

// Test Routes (Only available in testing environments)
if (app()->environment(['testing', 'dusk.local', 'dusk.testing'])) {
    // Test Login Route
    Route::post('test-login', function () {
        $user = \App\Models\User::factory()->create(); // Create a test user
        \Illuminate\Support\Facades\Auth::login($user); // Log the user in
        return response()->json(['message' => 'User logged in successfully', 'user' => $user]);
    });

    // Test Logout Route
    Route::post('test-logout', function () {
        \Illuminate\Support\Facades\Auth::logout(); // Log out the user
        return response()->json(['message' => 'User logged out successfully']);
    });

    // Test Session Validation Route
    Route::get('test-session', function () {
        if (auth()->check()) {
            return response()->json(['message' => 'User is authenticated', 'user' => auth()->user()]);
        } else {
            return response()->json(['message' => 'User is not authenticated'], 401);
        }
    });
}

// Protected Routes (Only accessible to authenticated users)
Route::middleware('auth')->group(function() {
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard.index');

    // Profile Routes
    Route::prefix('profile')->group(function() {
        Route::get('/', [ProfileController::class, 'index'])
            ->name('account.profile');
        Route::post('update', [ProfileController::class, 'update'])
            ->name('account.profile.update');
    });

    // Admin: Manage Users Routes (with admin middleware)
    Route::prefix('admin/manage-users')
        ->middleware('admin.only') // Admin-only access
        ->group(function() {
            Route::get('/',           [ManageUsersController::class, 'index'])
                ->name('admin.manage-users.index');
            Route::get('add-user',   [ManageUsersController::class, 'addUser'])
                ->name('admin.manage-users.add-user');
            Route::get('{user}/edit', [ManageUsersController::class, 'edit'])
                ->name('admin.manage-users.edit');
            Route::post('save',      [ManageUsersController::class, 'save'])
                ->name('admin.manage-users.save');
        });

    // Admin: Manage Events Routes (full resource except show/edit)
    Route::prefix('admin/manage-events')
        ->middleware('admin.only') // Admin-only access
        ->name('admin.manage-events.')
        ->group(function() {
            Route::get('/',            [ManageEventsController::class, 'index'])
                ->name('index');
            Route::get('create',       [ManageEventsController::class, 'create'])
                ->name('create');
            Route::post('/',           [ManageEventsController::class, 'store'])
                ->name('store');
            Route::get('{event}',      [ManageEventsController::class, 'show'])
                ->name('show');    // Newly added show route
            Route::get('{event}/edit', [ManageEventsController::class, 'edit'])
                ->name('edit');
            Route::put('{event}',      [ManageEventsController::class, 'update'])
                ->name('update');
            Route::delete('{event}',   [ManageEventsController::class, 'destroy'])
                ->name('destroy');
        });

    // Staff: Scan Tickets Routes (Requires Staff authentication)
    Route::prefix('staff/scan-tickets')
        ->middleware('staff.only') // Staff-only access
        ->group(function() {
            Route::get('/',                      [ScanTicketsController::class, 'index'])
                ->name('staff.scan-tickets.index');
            Route::get('{eventUUID}',            [ScanTicketsController::class, 'event'])
                ->name('staff.scan-tickets.event');
            Route::post('ajax/register-ticket',  [ScanTicketsController::class, 'ajaxRegisterTicket'])
                ->name('staff.scan-tickets.ajax.register-ticket');
        });

    // Debugging Route (for checking env, db, etc.)
    Route::get('/env-check', fn() => [
        'env'           => config('app.env'),
        'db'            => config('database.default'),
        'app_url'       => config('app.url'),
    ]);
});

// Additional middlewares for redirecting based on role or authentication status
Route::middleware('admin.only')->group(function() {
    // Admin-only routes can be added here if needed
});

Route::middleware('staff.only')->group(function() {
    // Staff-only routes can be added here if needed
});
