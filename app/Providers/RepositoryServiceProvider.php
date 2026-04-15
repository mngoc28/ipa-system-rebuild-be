<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\AuditLogRepository\AuditLogRepository;
use App\Repositories\AuditLogRepository\AuditLogRepositoryInterface;
use App\Repositories\AdminUserRepository\AdminUserRepository;
use App\Repositories\AdminUserRepository\AdminUserRepositoryInterface;
use App\Repositories\MasterDataRepository\MasterDataRepository;
use App\Repositories\MasterDataRepository\MasterDataRepositoryInterface;
use App\Repositories\EventRepository\EventRepository;
use App\Repositories\EventRepository\EventRepositoryInterface;
use App\Repositories\PipelineRepository\PipelineRepository;
use App\Repositories\PipelineRepository\PipelineRepositoryInterface;
use App\Repositories\ReportRepository\ReportRepository;
use App\Repositories\ReportRepository\ReportRepositoryInterface;
use App\Repositories\NotificationRepository\NotificationRepository;
use App\Repositories\NotificationRepository\NotificationRepositoryInterface;
use App\Repositories\MinutesRepository\MinutesRepository;
use App\Repositories\MinutesRepository\MinutesRepositoryInterface;
use App\Repositories\ApprovalRepository\ApprovalRepository;
use App\Repositories\ApprovalRepository\ApprovalRepositoryInterface;
use App\Repositories\DocumentRepository\DocumentRepository;
use App\Repositories\DocumentRepository\DocumentRepositoryInterface;
use App\Repositories\TeamRepository\TeamRepository;
use App\Repositories\TeamRepository\TeamRepositoryInterface;
use App\Repositories\SystemSettingRepository\SystemSettingRepository;
use App\Repositories\SystemSettingRepository\SystemSettingRepositoryInterface;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->singleton(AdminUserRepositoryInterface::class, AdminUserRepository::class);
        $this->app->singleton(MasterDataRepositoryInterface::class, MasterDataRepository::class);
        $this->app->singleton(PipelineRepositoryInterface::class, PipelineRepository::class);
        $this->app->singleton(ReportRepositoryInterface::class, ReportRepository::class);
        $this->app->singleton(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->singleton(SystemSettingRepositoryInterface::class, SystemSettingRepository::class);
        $this->app->singleton(ApprovalRepositoryInterface::class, ApprovalRepository::class);
        $this->app->singleton(DocumentRepositoryInterface::class, DocumentRepository::class);
        $this->app->singleton(TeamRepositoryInterface::class, TeamRepository::class);
        $this->app->singleton(
            \App\Repositories\PartnerRepository\PartnerRepositoryInterface::class,
            \App\Repositories\PartnerRepository\PartnerRepository::class
        );
        $this->app->singleton(
            \App\Repositories\TaskRepository\TaskRepositoryInterface::class,
            \App\Repositories\TaskRepository\TaskRepository::class
        );
        $this->app->singleton(
            \App\Repositories\DelegationRepository\DelegationRepositoryInterface::class,
            \App\Repositories\DelegationRepository\DelegationRepository::class
        );
        $this->app->singleton(MinutesRepositoryInterface::class, MinutesRepository::class);
        $this->app->singleton(EventRepositoryInterface::class, EventRepository::class);
    }
}
