<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\IntegrationController as AdminIntegrationController;
use App\Http\Controllers\Admin\MaintenanceController as AdminMaintenanceController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\MasterData\MasterDataController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Pipeline\PipelineController;
use App\Http\Controllers\Report\ReportController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Partner\PartnerController;
use App\Http\Controllers\Task\TaskController;
use App\Http\Controllers\Delegation\DelegationController;
use App\Http\Controllers\Minutes\MinutesController;
use App\Http\Controllers\Admin\AnnouncementController;
use App\Http\Controllers\Approval\ApprovalController;
use App\Http\Controllers\Document\DocumentController;
use App\Http\Controllers\Team\TeamController;
use App\Http\Controllers\Profile\ProfileController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is ready for the new project.',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/ping', function () {
        return response()->json([
            'status' => 'pong',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/test-db', function () {
        try {
            \DB::connection()->getPdo();
            $tables = \DB::select('SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = \'public\'');
            return response()->json([
                'status' => 'success',
                'database' => \DB::connection()->getDatabaseName(),
                'tables' => array_column($tables, 'tablename')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    });

    Route::prefix('auth')->group(function (): void {

        Route::post('login', [AuthController::class, 'login']);
        Route::get('me', [AuthController::class, 'me'])->middleware('jwt.auth');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('jwt.auth');
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    Route::get('/me', [AuthController::class, 'me'])->middleware('jwt.auth');

    Route::middleware('jwt.auth')->prefix('profile')->group(function (): void {
        Route::patch('', [ProfileController::class, 'update']);
        Route::post('avatar', [ProfileController::class, 'updateAvatar']);
    });

    // Helper closure for common modules accessible by all roles (with their own prefixes)
    $sharedModuleRoutes = function () {
        Route::prefix('users')->group(function (): void {
            Route::get('', [AdminUserController::class, 'index']);
            Route::get('roles', [AdminUserController::class, 'roles']);
            Route::get('units', [AdminUserController::class, 'units']);
            Route::post('{userId}/reset-password', [AdminUserController::class, 'resetPassword']);
            Route::get('{userId}', [AdminUserController::class, 'show']);
            Route::post('', [AdminUserController::class, 'store'])->middleware('role:ADMIN');
            Route::patch('{userId}', [AdminUserController::class, 'update'])->middleware('role:ADMIN');
            Route::patch('{userId}/lock', [AdminUserController::class, 'lock'])->middleware('role:ADMIN');
            Route::delete('{userId}', [AdminUserController::class, 'destroy'])->middleware('role:ADMIN');
            Route::post('{userId}/avatar', [AdminUserController::class, 'updateAvatar'])->middleware('role:ADMIN');
        });

        Route::prefix('master-data')->group(function (): void {
            Route::get('{domain}', [MasterDataController::class, 'index']);
            Route::get('{domain}/{id}', [MasterDataController::class, 'show']);
            Route::post('{domain}', [MasterDataController::class, 'store'])->middleware('role:ADMIN,DIRECTOR');
            Route::patch('{domain}/{id}', [MasterDataController::class, 'update'])->middleware('role:ADMIN,DIRECTOR');
            Route::delete('{domain}/{id}', [MasterDataController::class, 'destroy'])->middleware('role:ADMIN');
        });

        Route::prefix('tasks')->group(function (): void {
            Route::get('', [TaskController::class, 'index']);
            Route::get('{id}', [TaskController::class, 'show']);
            Route::post('', [TaskController::class, 'store']);
            Route::patch('{id}', [TaskController::class, 'update']);
            Route::delete('{id}', [TaskController::class, 'destroy']);

            // New Task features
            Route::get('{taskId}/comments', [TaskController::class, 'getComments']);
            Route::post('{taskId}/comments', [TaskController::class, 'addComment']);
            Route::get('{taskId}/attachments', [TaskController::class, 'getAttachments']);
            Route::post('{taskId}/attachments', [TaskController::class, 'uploadAttachment']);
            Route::delete('{taskId}/attachments/{attachmentId}', [TaskController::class, 'deleteAttachment']);
        });

        Route::prefix('delegations')->group(function (): void {
            Route::get('', [DelegationController::class, 'index']);
            Route::get('{id}', [DelegationController::class, 'show']);
            Route::post('', [DelegationController::class, 'store']);
            Route::patch('{id}', [DelegationController::class, 'update']);
            Route::delete('{id}', [DelegationController::class, 'destroy']);
            Route::get('{id}/comments', [DelegationController::class, 'listComments']);
            Route::post('{id}/comments', [DelegationController::class, 'addComment']);
            Route::put('{id}/comments/{commentId}', [DelegationController::class, 'updateComment']);
            Route::delete('{id}/comments/{commentId}', [DelegationController::class, 'deleteComment']);
        });

        Route::prefix('events')->group(function (): void {
            Route::get('', [EventController::class, 'index']);
            Route::get('{id}', [EventController::class, 'show']);
            Route::post('', [EventController::class, 'store']);
            Route::patch('{id}', [EventController::class, 'update']);
            Route::delete('{id}', [EventController::class, 'destroy']);
            Route::post('{id}/join', [EventController::class, 'join']);
            Route::post('{id}/reschedule-requests', [EventController::class, 'requestReschedule']);
        });

        Route::prefix('partners')->group(function (): void {
            Route::get('', [PartnerController::class, 'index']);
            Route::get('options', [PartnerController::class, 'options']);
            Route::get('{id}', [PartnerController::class, 'show']);
            Route::post('', [PartnerController::class, 'store']);
            Route::patch('{id}', [PartnerController::class, 'update']);
            Route::delete('{id}', [PartnerController::class, 'destroy']);
            Route::post('{id}/contacts', [PartnerController::class, 'storeContact']);
            Route::post('{id}/interactions', [PartnerController::class, 'storeInteraction']);
        });

        Route::prefix('notifications')->group(function (): void {
            Route::get('', [NotificationController::class, 'index']);
            Route::get('count', [NotificationController::class, 'count']);
            Route::patch('{id}/read', [NotificationController::class, 'read']);
            Route::patch('read-all', [NotificationController::class, 'readAll']);
            Route::delete('read', [NotificationController::class, 'deleteRead']);
        });

        Route::prefix('folders')->group(function (): void {
            Route::get('', [DocumentController::class, 'foldersIndex']);
            Route::post('', [DocumentController::class, 'foldersStore']);
        });

        Route::prefix('files')->group(function (): void {
            Route::get('', [DocumentController::class, 'filesIndex']);
            Route::get('{id}', [DocumentController::class, 'filesShow']);
            Route::post('upload', [DocumentController::class, 'filesUpload']);
            Route::patch('{id}', [DocumentController::class, 'filesPatch']);
            Route::post('{id}/share', [DocumentController::class, 'filesShare']);
            Route::post('{id}/download-url', [DocumentController::class, 'filesDownloadUrl']);
        });

        Route::prefix('minutes')->group(function (): void {
            Route::get('', [MinutesController::class, 'index']);
            Route::get('{id}', [MinutesController::class, 'show']);
            Route::post('', [MinutesController::class, 'store']);
            Route::post('{id}/versions', [MinutesController::class, 'createVersion']);
            Route::post('{id}/comments', [MinutesController::class, 'createComment']);
        });

        Route::prefix('pipeline')->group(function (): void {
            Route::get('summary', [PipelineController::class, 'summary']);
            Route::get('projects', [PipelineController::class, 'index']);
            Route::get('projects/{id}', [PipelineController::class, 'show']);
            Route::post('projects', [PipelineController::class, 'store']);
            Route::patch('projects/{id}', [PipelineController::class, 'update']);
            Route::delete('projects/{id}', [PipelineController::class, 'destroy']);
            Route::patch('projects/{id}/stage', [PipelineController::class, 'patchStage']);
        });

        Route::prefix('reports')->group(function (): void {
            Route::get('summary', [ReportController::class, 'summary']);
        });

        Route::get('dashboard/summary', [DashboardController::class, 'summary']);

        Route::prefix('teams')->group(function (): void {
            Route::get('', [TeamController::class, 'index']);
            Route::get('units', [TeamController::class, 'units']);
        });
    };

    // --- ADMIN CLUSTER ---
    Route::middleware(['jwt.auth', 'role:ADMIN'])->prefix('admin')->group(function () use ($sharedModuleRoutes): void {
        Route::prefix('system-settings')->group(function (): void {
            Route::get('', [SystemSettingController::class, 'index']);
            Route::patch('', [SystemSettingController::class, 'update']);
            Route::get('stats', [SystemSettingController::class, 'getOperationalStats']);
        });

        Route::apiResource('announcements', AnnouncementController::class)->only(['index', 'store', 'update', 'destroy']);

        Route::post('integrations/{provider}/test', [AdminIntegrationController::class, 'test']);

        Route::prefix('maintenance')->group(function (): void {
            Route::post('cache-clear', [AdminMaintenanceController::class, 'clearCache']);
        });

        Route::prefix('audit-logs')->group(function (): void {
            Route::get('', [AuditLogController::class, 'index']);
        });

        $sharedModuleRoutes();
    });

    // --- DIRECTOR CLUSTER ---
    Route::middleware(['jwt.auth', 'role:ADMIN,DIRECTOR'])->prefix('director')->group(function () use ($sharedModuleRoutes): void {
        Route::prefix('reports')->group(function (): void {
            Route::get('definitions', [ReportController::class, 'definitions']);
            Route::post('runs', [ReportController::class, 'store']);
            Route::get('runs/{runId}', [ReportController::class, 'show']);
        });

        $sharedModuleRoutes();
    });

    // --- MANAGER CLUSTER ---
    Route::middleware(['jwt.auth', 'role:ADMIN,DIRECTOR,MANAGER'])->prefix('manager')->group(function () use ($sharedModuleRoutes): void {
        Route::prefix('teams')->group(function (): void {
            Route::post('members', [TeamController::class, 'storeMember']);
        });

        Route::prefix('reports')->group(function (): void {
            Route::get('definitions', [ReportController::class, 'definitions']);
            Route::post('runs', [ReportController::class, 'store']);
            Route::get('runs/{runId}', [ReportController::class, 'show']);
        });

        Route::prefix('approvals')->group(function (): void {
            Route::get('', [ApprovalController::class, 'index']);
            Route::get('summary', [ApprovalController::class, 'summary']);
            Route::get('{id}', [ApprovalController::class, 'show']);
            Route::post('{id}/decision', [ApprovalController::class, 'decision']);
        });

        Route::post('minutes/{id}/approve', [MinutesController::class, 'approve']);

        $sharedModuleRoutes();
    });

    // --- STAFF CLUSTER ---
    Route::middleware(['jwt.auth', 'role:ADMIN,DIRECTOR,MANAGER,STAFF'])->prefix('staff')->group(function () use ($sharedModuleRoutes): void {
        Route::get('dashboard/tasks', [DashboardController::class, 'tasks']);
        $sharedModuleRoutes();
    });
});
