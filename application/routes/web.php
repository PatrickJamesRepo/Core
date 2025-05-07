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

// Public routes
Route::get('/', [HomeController::class, 'index']);
// Updated name to match tests
Route::get('event/{eventUUID}', [HomeController::class, 'event'])->name('event.show');

// Auth routes
if (app()->environment(['testing','dusk.local','dusk.testing'])) {
    Auth::routes();
} else {
    Auth::routes([
        'register' => false,
        'reset'    => false,
        'verify'   => false,
    ]);
}

// Protected routes
Route::middleware('auth')->group(function() {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

    // Profile
    Route::prefix('profile')->group(function() {
        Route::get('/', [ProfileController::class, 'index'])->name('account.profile');
        Route::post('update', [ProfileController::class, 'update'])->name('account.profile.update');
    });

    // Admin: Manage Users
    Route::prefix('admin/manage-users')
        ->middleware('admin.only')
        ->group(function() {
            Route::get('/', [ManageUsersController::class, 'index'])->name('admin.manage-users.index');
            Route::get('add-user', [ManageUsersController::class, 'addUser'])->name('admin.manage-users.add-user');
            Route::get('{userId}/edit', [ManageUsersController::class, 'edit'])->name('admin.manage-users.edit');
            Route::post('save', [ManageUsersController::class, 'save'])->name('admin.manage-users.save');
        });

    // Admin: Manage Events (resource with admin. prefix)
    Route::prefix('admin/manage-events')
        ->middleware('admin.only')
        ->name('admin.manage-events.')
        ->group(function() {
            Route::get('/',                  [ManageEventsController::class, 'index'])->name('index');
            Route::get('create',             [ManageEventsController::class, 'create'])->name('create');
            Route::post('/',                 [ManageEventsController::class, 'store'])->name('store');
            Route::get('{event}/edit',       [ManageEventsController::class, 'edit'])->name('edit');
            Route::put('{event}',            [ManageEventsController::class, 'update'])->name('update');
            Route::delete('{event}',         [ManageEventsController::class, 'destroy'])->name('destroy');
        });

    // Staff: Scan Tickets
    Route::prefix('staff/scan-tickets')
        ->middleware('staff.only')
        ->group(function() {
            Route::get('/', [ScanTicketsController::class, 'index'])->name('staff.scan-tickets.index');
            Route::get('{eventUUID}', [ScanTicketsController::class, 'event'])->name('staff.scan-tickets.event');
            Route::post('ajax/register-ticket', [ScanTicketsController::class, 'ajaxRegisterTicket'])->name('staff.scan-tickets.ajax.register-ticket');
        });

    // Env check
    Route::get('/env-check', function () {
        return [
            'env'           => config('app.env'),
            'db_connection' => config('database.default'),
            'db_name'       => config('database.connections.mysql.database'),
            'app_url'       => config('app.url'),
        ];
    });
});
