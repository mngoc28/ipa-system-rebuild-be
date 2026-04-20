<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\TaskRepository\TaskRepositoryInterface;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

final class TaskService
{
    public function __construct(
        private TaskRepositoryInterface $taskRepository,
        private NotificationService $notificationService,
    ) {
    }

    public function getAll(Request $request): array
    {
        try {
            return [
                'success' => true,
                'data' => $this->taskRepository->getPaginated($request),
                'message' => __('tasks.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::getAll', [
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.fetch_error'),
            ];
        }
    }

    public function getById(int $id): array
    {
        try {
            $task = $this->taskRepository->find($id);

            if (!$task) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('tasks.messages.not_found'),
                ];
            }

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.fetch_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::getById', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.fetch_error'),
            ];
        }
    }

    public function create(array $data): array
    {
        try {
            $task = $this->taskRepository->create($data);

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.create_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::create', [
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.create_error'),
            ];
        }
    }

    public function update(int $id, array $data): array
    {
        try {
            $task = $this->taskRepository->update($id, $data);

            return [
                'success' => true,
                'data' => $task,
                'message' => __('tasks.messages.update_success'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::update', [
                'id' => $id,
                'data' => $data,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.update_error'),
            ];
        }
    }

    public function delete(int $id): array
    {
        try {
            $deleted = $this->taskRepository->delete($id);

            return [
                'success' => $deleted,
                'data' => null,
                'message' => $deleted ? __('tasks.messages.delete_success') : __('tasks.messages.not_found'),
            ];
        } catch (Throwable $throwable) {
            Log::error('TaskService::delete', [
                'id' => $id,
                'error' => $throwable->getMessage(),
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('tasks.messages.delete_error'),
            ];
        }
    }

    public function getComments(int $taskId): array
    {
        try {
            $task = $this->taskRepository->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => __('tasks.messages.not_found')];
            }

            $comments = $task->comments()->with('commenter:id,full_name,avatar_url')->get();

            return [
                'success' => true,
                'data' => $comments,
            ];
        } catch (Throwable $throwable) {
            return ['success' => false, 'message' => $throwable->getMessage()];
        }
    }

    public function addComment(int $taskId, array $data): array
    {
        try {
            $task = $this->taskRepository->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => __('tasks.messages.not_found')];
            }

            $comment = $task->comments()->create([
                'commenter_user_id' => auth()->id(),
                'comment_text' => $data['content'],
            ]);

            // Trigger Notifications
            $this->sendCommentNotifications($task, $comment);

            return [
                'success' => true,
                'data' => $comment->load('commenter:id,full_name,avatar_url'),
                'message' => __('tasks.messages.comment_success'),
            ];
        } catch (Throwable $throwable) {
            return ['success' => false, 'message' => $throwable->getMessage()];
        }
    }

    /**
     * Send notifications to relevant users when a comment is added.
     */
    private function sendCommentNotifications($task, $comment): void
    {
        $commenterId = auth()->id();
        $recipients = collect();

        // 1. Notify Creator (if not commenter)
        if ($task->created_by && $task->created_by !== $commenterId) {
            $recipients->push($task->created_by);
        }

        // 2. Notify Assignees (except commenter)
        foreach ($task->assignees as $assignee) {
            if ($assignee->id !== $commenterId) {
                $recipients->push($assignee->id);
            }
        }

        // 3. Notify @mentioned users
        $mentions = $this->parseMentions($comment->comment_text);
        foreach ($mentions as $mentionedUserId) {
            if ($mentionedUserId !== $commenterId) {
                $recipients->push($mentionedUserId);
            }
        }

        $recipientIds = $recipients->unique()->values()->toArray();

        if (!empty($recipientIds)) {
            $this->notificationService->notify([
                'notification_type_id' => 1, // 'assignment' or create specialized 'comment' type
                'title' => "[Nhiệm vụ] Bình luận mới từ " . auth()->user()->full_name,
                'body' => $comment->comment_text,
                'ref_table' => 'ipa_task',
                'ref_id' => $task->id,
                'severity' => 0,
            ], $recipientIds);
        }
    }

    /**
     * Parse text for @mentions and return user IDs.
     * Expected format: @[Full Name] or @Full Name (depending on frontend implementation)
     */
    private function parseMentions(string $text): array
    {
        // Matches pattern @[Full Name]
        preg_match_all('/@\[(.*?)\]/', $text, $matches);

        if (empty($matches[1])) {
            return [];
        }

        $names = array_map('trim', $matches[1]);
        return AdminUser::whereIn('full_name', $names)->pluck('id')->toArray();
    }

    public function getAttachments(int $taskId): array
    {
        try {
            $task = $this->taskRepository->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => __('tasks.messages.not_found')];
            }

            return [
                'success' => true,
                'data' => $task->attachments,
            ];
        } catch (Throwable $throwable) {
            return ['success' => false, 'message' => $throwable->getMessage()];
        }
    }

    public function addAttachment(int $taskId, $file): array
    {
        try {
            $task = $this->taskRepository->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => __('tasks.messages.not_found')];
            }

            // Simple storage logic (can be replaced with Cloudinary as seen in other modules)
            $path = $file->store('task-attachments', 'public');

            $attachment = $task->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'uploaded_by' => auth()->id(),
            ]);

            return [
                'success' => true,
                'data' => $attachment,
                'message' => __('tasks.messages.upload_success'),
            ];
        } catch (Throwable $throwable) {
            return ['success' => false, 'message' => $throwable->getMessage()];
        }
    }

    public function deleteAttachment(int $taskId, int $attachmentId): array
    {
        try {
            $task = $this->taskRepository->find($taskId);
            if (!$task) {
                return ['success' => false, 'message' => __('tasks.messages.not_found')];
            }

            $attachment = $task->attachments()->find($attachmentId);
            if (!$attachment) {
                return ['success' => false, 'message' => __('tasks.messages.attachment_not_found')];
            }

            $attachment->delete();

            return [
                'success' => true,
                'message' => __('tasks.messages.delete_success'),
            ];
        } catch (Throwable $throwable) {
            return ['success' => false, 'message' => $throwable->getMessage()];
        }
    }
}
