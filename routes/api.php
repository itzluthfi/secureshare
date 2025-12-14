<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\AuditLogController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::prefix('v1')->group(function () {
    // Authentication
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
});

// Protected routes with rate limiting
Route::prefix('v1')->middleware(['auth:sanctum', 'api.limit:60,1'])->group(function () {
    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/auth/permissions', [AuthController::class, 'getPermissions']);

    // Users (Admin only)
    // Users - GET index (team view) accessible to all authenticated users
    Route::get('/users', [UserController::class, 'index']);
    
    // Users - Admin only operations
    Route::middleware(['App\Http\Middleware\CheckRole:admin'])->group(function () {
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users/{user}', [UserController::class, 'show']);
        Route::put('/users/{user}', [UserController::class, 'update']);
        Route::delete('/users/{user}', [UserController::class, 'destroy']);
        Route::post('/users/{user}/assign-role', [UserController::class, 'assignRole']);
    });

    // Projects - explicit routes to avoid name conflict with web routes
    Route::get('/projects', [ProjectController::class, 'index'])->name('api.projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('api.projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->name('api.projects.show');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('api.projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('api.projects.destroy');
    Route::post('/projects/{project}/members', [ProjectController::class, 'addMember'])->name('api.projects.addMember');
    Route::post('/projects/{project}/invitations/accept', [ProjectController::class, 'acceptInvitation'])->name('api.projects.acceptInvitation');
    Route::post('/projects/{project}/invitations/decline', [ProjectController::class, 'declineInvitation'])->name('api.projects.declineInvitation');
    Route::delete('/projects/{project}/members/{userId}', [ProjectController::class, 'removeMember'])->name('api.projects.removeMember');
    Route::put('/projects/{project}/members/{userId}', [ProjectController::class, 'updateMemberRole'])->name('api.projects.updateMemberRole');
    Route::get('/projects/{project}/activities', [ProjectController::class, 'getActivities'])->name('api.projects.activities');

    // Documents
    Route::get('/projects/{projectId}/documents', [DocumentController::class, 'index']);
    Route::post('/projects/{projectId}/documents', [DocumentController::class, 'store']);
    Route::get('/documents/{id}', [DocumentController::class, 'show']);
    Route::put('/documents/{id}', [DocumentController::class, 'update']);
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy']);
    Route::get('/documents/{id}/download', [DocumentController::class, 'download']);
    Route::post('/documents/{id}/versions', [DocumentController::class, 'uploadVersion']);
    Route::get('/documents/{id}/versions', [DocumentController::class, 'versions']);
    Route::get('/documents/{documentId}/versions/{versionNumber}/download', [DocumentController::class, 'downloadVersion']);

    // Tasks
    Route::get('/tasks', [TaskController::class, 'getAllTasks']); // Get all tasks for calendar
    Route::get('/projects/{projectId}/tasks', [TaskController::class, 'index']);
    Route::post('/projects/{projectId}/tasks', [TaskController::class, 'store']);
    Route::get('/tasks/{id}', [TaskController::class, 'show']);
    Route::put('/tasks/{id}', [TaskController::class, 'update']);
    Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
    Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);

    // Comments
    Route::get('/documents/{documentId}/comments', [CommentController::class, 'index']);
    Route::post('/documents/{documentId}/comments', [CommentController::class, 'store']);
    Route::post('/comments/{id}/reply', [CommentController::class, 'reply']);
    Route::put('/comments/{id}', [CommentController::class, 'update']);
    Route::delete('/comments/{id}', [CommentController::class, 'destroy']);

    // Calendar & Milestones
    Route::get('/calendar/events', [\App\Http\Controllers\Api\CalendarController::class, 'getEvents']);
    Route::get('/calendar/month/{year}/{month}', [\App\Http\Controllers\Api\CalendarController::class, 'getMonthView']);
    Route::post('/milestones', [\App\Http\Controllers\Api\CalendarController::class, 'storeMilestone']);
    Route::put('/milestones/{id}', [\App\Http\Controllers\Api\CalendarController::class, 'updateMilestone']);
    Route::delete('/milestones/{id}', [\App\Http\Controllers\Api\CalendarController::class, 'destroyMilestone']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::put('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);
    Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

    // Audit Logs (Admin only)
    Route::middleware(['App\Http\Middleware\CheckRole:admin'])->group(function () {
        Route::get('/audit-logs', [AuditLogController::class, 'index']);
        Route::get('/audit-logs/{id}', [AuditLogController::class, 'show']);
        Route::get('/audit-logs/export', [AuditLogController::class, 'export']);
    });
});
