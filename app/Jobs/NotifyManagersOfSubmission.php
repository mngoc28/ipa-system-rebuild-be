<?php

namespace App\Jobs;

use App\Models\Delegation;
use App\Repositories\AdminUserRepository\AdminUserRepository;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class NotifyManagersOfSubmission implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $delegationId;

    /**
     * Create a new job instance.
     *
     * @param int $delegationId
     */
    public function __construct(int $delegationId)
    {
        $this->delegationId = $delegationId;
    }

    /**
     * Execute the job.
     *
     * @param NotificationService $notificationService
     * @param AdminUserRepository $userRepository
     * @return void
     */
    public function handle(NotificationService $notificationService, AdminUserRepository $userRepository)
    {
        try {
            $delegation = Delegation::with('owner')->find($this->delegationId);

            if (!$delegation) {
                return;
            }

            $unitId = $delegation->owner->primary_unit_id ?? null;
            if (!$unitId) {
                return;
            }

            // Get all Managers in the same unit
            $managerIds = $userRepository->getIdsByRoleAndUnit('MANAGER', $unitId);

            if (empty($managerIds)) {
                return;
            }

            $notificationService->notify(
                [
                    'notification_type_id' => 2, // approval
                    'title' => "Hồ sơ đoàn mới chờ duyệt",
                    'body' => "Có hồ sơ đoàn mới \"{$delegation->name}\" cần bạn phê duyệt.",
                    'ref_table' => 'ipa_delegation',
                    'ref_id' => $delegation->id,
                    'severity' => 1, // Info/Normal
                ],
                $managerIds
            );
        } catch (\Exception $e) {
            Log::error("Failed to notify managers for delegation {$this->delegationId}: " . $e->getMessage());
        }
    }
}
