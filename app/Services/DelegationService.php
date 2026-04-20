<?php

namespace App\Services;

use App\Repositories\DelegationRepository\DelegationRepositoryInterface;
use App\Repositories\AdminUserRepository\AdminUserRepositoryInterface;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DelegationService
{
    protected $repository;
    protected $notificationService;
    protected $userRepository;

    public function __construct(
        DelegationRepositoryInterface $repository,
        NotificationService $notificationService,
        \App\Repositories\AdminUserRepository\AdminUserRepositoryInterface $userRepository
    ) {
        $this->repository = $repository;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    public function listDelegations(Request $request)
    {
        return $this->repository->getPaginated($request);
    }

    public function getDelegation(int $id)
    {
        return $this->repository->getById($id);
    }

    public function createDelegation(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $delegation = $this->repository->create($data);
                if ($delegation && (int)$delegation->status === 1) {
                    $this->notifyManagersOfSubmission($delegation);
                }
                return $delegation;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    public function updateDelegation(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                $oldDelegation = $this->repository->getById($id);
                $oldStatus = $oldDelegation ? (int)$oldDelegation->status : null;

                $delegation = $this->repository->update($id, $data);

                if ($delegation) {
                    $newStatus = (int)$delegation->status;
                    if ($newStatus === 1 && $oldStatus !== 1) {
                        $this->notifyManagersOfSubmission($delegation);
                    } elseif ($oldStatus === 1 && in_array($newStatus, [3, 6, 2])) {
                        $this->notifyStaffOfDecision($delegation, $newStatus);
                    }
                }

                return $delegation;
            });
        } catch (\Exception $e) {
            Log::error('Failed to update delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function notifyManagersOfSubmission($delegation): void
    {
        try {
            $managerIds = $this->userRepository->getIdsByRoleAndUnit('MANAGER', (int)$delegation->host_unit_id);
            if (!empty($managerIds)) {
                $this->notificationService->notify([
                    'notification_type_id' => 2, // approval
                    'title' => 'Hồ sơ đoàn mới chờ duyệt',
                    'body' => "Có hồ sơ đoàn mới \"{$delegation->name}\" cần bạn phê duyệt.",
                    'ref_table' => 'ipa_delegation',
                    'ref_id' => $delegation->id,
                    'severity' => 1,
                ], $managerIds);
            }
        } catch (\Exception $e) {
            Log::error('DelegationService::notifyManagersOfSubmission: ' . $e->getMessage());
        }
    }

    protected function notifyStaffOfDecision($delegation, int $newStatus): void
    {
        try {
            $statusText = match ($newStatus) {
                3 => 'đã được phê duyệt',
                6 => 'đã bị từ chối',
                2 => 'cần được chỉnh sửa lại',
                default => 'đã thay đổi trạng thái',
            };

            $body = "Hồ sơ đoàn \"{$delegation->name}\" của bạn {$statusText}.";
            if (!empty($delegation->approval_remark)) {
                $body .= " Ghi chú: {$delegation->approval_remark}";
            }

            $this->notificationService->notify([
                'notification_type_id' => 2, // approval
                'title' => 'Kết quả phê duyệt hồ sơ đoàn',
                'body' => $body,
                'ref_table' => 'ipa_delegation',
                'ref_id' => $delegation->id,
                'severity' => $newStatus === 3 ? 1 : 2,
            ], (int)$delegation->owner_user_id);
        } catch (\Exception $e) {
            Log::error('DelegationService::notifyStaffOfDecision: ' . $e->getMessage());
        }
    }

    public function deleteDelegation(int $id)
    {
        try {
            return $this->repository->delete($id);
        } catch (\Exception $e) {
            Log::error('Failed to delete delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    // --- Delegation Comments ---

    public function listComments(int $delegationId)
    {
        try {
            $delegation = $this->repository->getById($delegationId);
            if (!$delegation) {
                return ['success' => false, 'message' => 'Không tìm thấy đoàn công tác.'];
            }

            $comments = $delegation->comments()->with('commenter:id,full_name,avatar_url')->orderBy('created_at', 'asc')->get();

            return ['success' => true, 'data' => $comments];
        } catch (\Exception $e) {
            Log::error('Failed to list delegation comments: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function addComment(int $delegationId, string $content, int $commenterId)
    {
        try {
            $delegation = $this->repository->getById($delegationId);
            if (!$delegation) {
                return ['success' => false, 'message' => 'Không tìm thấy đoàn công tác.'];
            }

            $comment = $delegation->comments()->create([
                'commenter_user_id' => $commenterId,
                'comment_text' => $content,
            ]);

            $this->sendCommentNotifications($delegation, $comment, $commenterId);

            return [
                'success' => true,
                'data' => $comment->load('commenter:id,full_name,avatar_url'),
                'message' => 'Đã gửi bình luận.',
            ];
        } catch (\Exception $e) {
            Log::error('Failed to add delegation comment: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateComment(int $commentId, string $content, int $userId)
    {
        try {
            $comment = \DB::table('ipa_delegation_comment')->where('id', $commentId)->first();
            if (!$comment) {
                return ['success' => false, 'message' => 'Không tìm thấy bình luận.'];
            }

            if ((int)$comment->commenter_user_id !== $userId) {
                return ['success' => false, 'message' => 'Bạn không có quyền sửa bình luận này.'];
            }

            $this->repository->updateComment($commentId, [
                'comment_text' => $content,
                'updated_at' => now(),
            ]);

            return ['success' => true, 'message' => 'Đã cập nhật bình luận.'];
        } catch (\Exception $e) {
            Log::error('Failed to update delegation comment: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteComment(int $commentId, int $userId)
    {
        try {
            $comment = \DB::table('ipa_delegation_comment')->where('id', $commentId)->first();
            if (!$comment) {
                return ['success' => false, 'message' => 'Không tìm thấy bình luận.'];
            }

            if ((int)$comment->commenter_user_id !== $userId) {
                return ['success' => false, 'message' => 'Bạn không có quyền xóa bình luận này.'];
            }

            $this->repository->deleteComment($commentId);

            return ['success' => true, 'message' => 'Đã xóa bình luận.'];
        } catch (\Exception $e) {
            Log::error('Failed to delete delegation comment: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function sendCommentNotifications($delegation, $comment, int $commenterId): void
    {
        $recipients = collect();

        // 1. Notify Owner (if not commenter)
        if ($delegation->owner_user_id && $delegation->owner_user_id !== $commenterId) {
            $recipients->push($delegation->owner_user_id);
        }

        // 2. Notify @mentioned users
        $mentions = $this->parseMentions($comment->comment_text);
        foreach ($mentions as $mentionedUserId) {
            if ($mentionedUserId !== $commenterId) {
                $recipients->push($mentionedUserId);
            }
        }

        $recipientIds = $recipients->unique()->values()->toArray();

        if (!empty($recipientIds)) {
            $this->notificationService->notify([
                'notification_type_id' => 1, // You could create a specialized 'comment' type
                'title' => "[Đoàn công tác] Bình luận mới từ " . auth()->user()->full_name,
                'body' => $comment->comment_text,
                'ref_table' => 'ipa_delegation',
                'ref_id' => $delegation->id,
                'severity' => 0,
            ], $recipientIds);
        }
    }

    private function parseMentions(string $text): array
    {
        preg_match_all('/@\[(.*?)\]/', $text, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $names = array_map('trim', $matches[1]);
        return \App\Models\AdminUser::whereIn('full_name', $names)->pluck('id')->toArray();
    }
}
