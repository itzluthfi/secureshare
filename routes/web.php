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
| Web Routes - NO AUTH MIDDLEWARE
|--------------------------------------------------------------------------
| These routes DO NOT use Laravel's session-based auth.
| Authentication is handled via JavaScript + API tokens (Sanctum).
|
*/

// Guest-only routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
Route::get('/reset-password/{token}', [AuthController::class, 'showResetPassword'])->name('password.reset');

// Dashboard
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');

// Projects
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('projects.show');

// Documents
Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
Route::get('/documents/{id}', [DocumentController::class, 'show'])->name('documents.show');

// Tasks
Route::get('/tasks', [TaskController::class, 'index'])->name('tasks.index');

// Inbox
Route::get('/inbox', [InboxController::class, 'index'])->name('inbox');

// Team
Route::get('/team', [TeamController::class, 'index'])->name('team');

// Calendar
Route::view('/calendar', 'calendar')->name('calendar');

// Admin routes
Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
Route::get('/admin/audit-logs', [AdminController::class, 'auditLogs'])->name('admin.audit-logs');
