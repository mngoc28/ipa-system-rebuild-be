<?php

namespace App\Services;

use App\Repositories\DelegationRepository\DelegationRepositoryInterface;
use App\Repositories\AdminUserRepository\AdminUserRepositoryInterface;
use App\Services\NotificationService;
use App\Jobs\NotifyManagersOfSubmission;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class DelegationService
 *
 * Manages business logic for complex business delegations, including submission workflows,
 * multi-role notifications, and structured commenting systems with mentions.
 *
 * @package App\Services
 */
class DelegationService
{
    /**
     * @var DelegationRepositoryInterface
     */
    protected $repository;

    /**
     * @var NotificationService
     */
    protected $notificationService;

    /**
     * @var AdminUserRepositoryInterface
     */
    protected $userRepository;

    /**
     * DelegationService constructor.
     *
     * @param DelegationRepositoryInterface $repository
     * @param NotificationService $notificationService
     * @param AdminUserRepositoryInterface $userRepository
     */
    public function __construct(
        DelegationRepositoryInterface $repository,
        NotificationService $notificationService,
        AdminUserRepositoryInterface $userRepository
    ) {
        $this->repository = $repository;
        $this->notificationService = $notificationService;
        $this->userRepository = $userRepository;
    }

    /**
     * Retrieve a paginated list of delegations.
     *
     * @param Request $request
     * @return mixed
     */
    public function listDelegations(Request $request)
    {
        return $this->repository->getPaginated($request);
    }

    /**
     * Get details for a specific delegation.
     *
     * @param int $id
     * @return mixed
     */
    public function getDelegation(int $id)
    {
        return $this->repository->getById($id);
    }

    /**
     * Create a new delegation and notify managers if submitted (status 1).
     *
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function createDelegation(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                /** @var Delegation|null $delegation */
                $delegation = $this->repository->create($data);
                if ($delegation && (int)$delegation->status === 1) {
                    NotifyManagersOfSubmission::dispatch($delegation->id);
                }
                return $delegation;
            });
        } catch (\Exception $e) {
            Log::error('Failed to create delegation: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing delegation and trigger appropriate notifications based on status changes.
     *
     * @param int $id
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public function updateDelegation(int $id, array $data)
    {
        try {
            return DB::transaction(function () use ($id, $data) {
                /** @var Delegation|null $oldDelegation */
                $oldDelegation = $this->repository->getById($id);
                $oldStatus = $oldDelegation ? (int)$oldDelegation->status : null;

                /** @var Delegation|null $delegation */
                $delegation = $this->repository->update($id, $data);

                if ($delegation) {
                    $newStatus = (int)$delegation->status;
                    if ($newStatus === 1 && $oldStatus !== 1) {
                        NotifyManagersOfSubmission::dispatch($delegation->id);
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


    /**
     * Notify the delegation owner of approval/rejection/request-for-change decisions.
     *
     * @param Delegation $delegation
     * @param int $newStatus
     * @return void
     */
    protected function notifyStaffOfDecision(Delegation $delegation, int $newStatus): void
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

    /**
     * Remove a delegation record.
     *
     * @param int $id
     * @return mixed
     * @throws \Exception
     */
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

    /**
     * Retrieve all comments for a specific delegation, including commenter details.
     *
     * @param int $delegationId
     * @return array
     */
    public function listComments(int $delegationId)
    {
        try {
            /** @var Delegation|null $delegation */
            $delegation = $this->repository->getById($delegationId);
            if (!$delegation) {
                return ['success' => false, 'message' => 'Không tìm thấy đoàn công tác.'];
            }

            $comments = $delegation->comments()->with('commenter:id,full_name,avatar_url')->orderBy('created_at', 'asc')->get();

            return ['success' => true, 'data' => ['items' => $comments]];
        } catch (\Exception $e) {
            Log::error('Failed to list delegation comments: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Add a comment to a delegation and notify involved parties.
     *
     * @param int $delegationId
     * @param string $content
     * @param int $commenterId
     * @return array
     */
    public function addComment(int $delegationId, string $content, int $commenterId)
    {
        try {
            /** @var Delegation|null $delegation */
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

    /**
     * Update an existing comment content.
     *
     * @param int $commentId
     * @param string $content
     * @param int $userId The identifier of the requesting user (auth check).
     * @return array
     */
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

    /**
     * Remove a comment.
     *
     * @param int $commentId
     * @param int $userId The identifier of the requesting user (auth check).
     * @return array
     */
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

    /**
     * Coordinate notifications for new comments, targeting the owner and @mentioned users.
     *
     * @param Delegation $delegation
     * @param \App\Models\DelegationComment $comment
     * @param int $commenterId
     * @return void
     */
    protected function sendCommentNotifications(Delegation $delegation, \App\Models\DelegationComment $comment, int $commenterId): void
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

    /**
     * Extract user mentions from comment text using @[Name] format.
     *
     * @param string $text
     * @return array Array of mentioned user IDs.
     */
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
