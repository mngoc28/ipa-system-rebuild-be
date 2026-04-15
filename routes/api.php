<?php

declare(strict_types=1);

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\AdminIntegrationController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\PipelineController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SystemSettingController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\DelegationController;
use App\Http\Controllers\MinutesController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\TeamController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::get('/health', function () {
        return response()->json([
            'status' => 'ok',
            'message' => 'API is ready for the new project.',
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('/me', function (Request $request) {
        return response()->json([
            'status' => 'ok',
            'data' => $request->user(),
        ]);
    })->middleware('auth:sanctum');

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

    Route::prefix('tasks')->group(function (): void {
        Route::get('', [TaskController::class, 'index']);
        Route::get('{id}', [TaskController::class, 'show']);
        Route::post('', [TaskController::class, 'store']);
        Route::patch('{id}', [TaskController::class, 'update']);
        Route::delete('{id}', [TaskController::class, 'destroy']);
    });

    Route::prefix('delegations')->group(function (): void {
        Route::get('', [DelegationController::class, 'index']);
        Route::get('{id}', [DelegationController::class, 'show']);
        Route::post('', [DelegationController::class, 'store']);
        Route::patch('{id}', [DelegationController::class, 'update']);
        Route::delete('{id}', [DelegationController::class, 'destroy']);
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

    Route::prefix('minutes')->group(function (): void {
        Route::get('', [MinutesController::class, 'index']);
        Route::get('{id}', [MinutesController::class, 'show']);
        Route::post('', [MinutesController::class, 'store']);
        Route::post('{id}/versions', [MinutesController::class, 'createVersion']);
        Route::post('{id}/comments', [MinutesController::class, 'createComment']);
        Route::post('{id}/approve', [MinutesController::class, 'approve']);
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

    Route::prefix('teams')->group(function (): void {
        Route::get('', [TeamController::class, 'index']);
        Route::get('units', [TeamController::class, 'units']);
        Route::post('members', [TeamController::class, 'storeMember']);
    });

    Route::prefix('approvals')->group(function (): void {
        Route::get('', [ApprovalController::class, 'index']);
        Route::get('{id}', [ApprovalController::class, 'show']);
        Route::post('{id}/decision', [ApprovalController::class, 'decision']);
    });

    Route::prefix('admin/users')->group(function (): void {
        Route::get('', [AdminUserController::class, 'index']);
        Route::get('{userId}', [AdminUserController::class, 'show']);
        Route::post('', [AdminUserController::class, 'store']);
        Route::patch('{userId}', [AdminUserController::class, 'update']);
        Route::patch('{userId}/lock', [AdminUserController::class, 'lock']);
    });

    Route::prefix('admin/system-settings')->group(function (): void {
        Route::get('', [SystemSettingController::class, 'index']);
        Route::patch('', [SystemSettingController::class, 'update']);
    });

    Route::post('admin/integrations/{provider}/test', [AdminIntegrationController::class, 'test']);

    Route::prefix('notifications')->group(function (): void {
        Route::get('', [NotificationController::class, 'index']);
        Route::patch('{id}/read', [NotificationController::class, 'read']);
        Route::patch('read-all', [NotificationController::class, 'readAll']);
        Route::delete('read', [NotificationController::class, 'deleteRead']);
    });

    Route::prefix('dashboard')->group(function (): void {
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('tasks', [DashboardController::class, 'tasks']);
    });

    Route::prefix('reports')->group(function (): void {
        Route::get('summary', [ReportController::class, 'summary']);
        Route::get('definitions', [ReportController::class, 'definitions']);
        Route::post('runs', [ReportController::class, 'store']);
        Route::get('runs/{runId}', [ReportController::class, 'show']);
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

    Route::prefix('admin/audit-logs')->group(function (): void {
        Route::get('', [AuditLogController::class, 'index']);
    });
});
