<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\ProjectController;
use App\Http\Controllers\Web\DocumentController;
use App\Http\Controllers\Web\AdminController;
use App\Http\Controllers\Web\TaskController;
use App\Http\Controllers\Web\InboxController;
use App\Http\Controllers\Web\TeamController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Session-Based Authentication
|--------------------------------------------------------------------------
| These routes use Laravel's built-in session authentication.
| Guest routes are accessible without login.
| Protected routes require authentication.
|
*/

// Guest-only routes (redirect to dashboard if already logged in)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');
});

// Authenticated routes (require login)
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Projects
    // Projects
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::get('/projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
    
    // Documents
    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/{document}', [DocumentController::class, 'show'])->name('documents.show');
    
    // Tasks
    Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');
    
    // Inbox
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');
    
    // Team
    Route::get('/team', [TeamController::class, 'index'])->name('team');
    
    // Calendar
    Route::view('/calendar', 'calendar')->name('calendar');
    
    // Admin routes (requires admin role)
    Route::middleware('can:viewAny,App\Models\User')->group(function () {
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
    });
});
