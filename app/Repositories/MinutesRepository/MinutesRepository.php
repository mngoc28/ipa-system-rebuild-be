<?php

declare(strict_types=1);

namespace App\Repositories\MinutesRepository;

use App\Models\Minutes;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class MinutesRepository extends BaseRepository implements MinutesRepositoryInterface
{
    private const STATUS_MAP = [
        0 => 'DRAFT',
        1 => 'INTERNAL',
        2 => 'FINAL',
    ];

    private const DECISION_MAP = [
        'APPROVE' => 1,
        'REJECT' => 2,
    ];

    public function getModel(): string
    {
        return Minutes::class;
    }

    public function getPaginated(Request $request): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $delegationId = trim((string) $request->input('delegationId', ''));
        $status = trim((string) $request->input('status', ''));
        $keyword = trim((string) $request->input('keyword', ''));

        $query = DB::table('ipa_minutes as minutes')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'minutes.delegation_id')
            ->leftJoin('ipa_event as event', 'event.id', '=', 'minutes.event_id')
            ->select([
                'minutes.id',
                'minutes.delegation_id',
                'minutes.event_id',
                'minutes.title',
                'minutes.current_version_no',
                'minutes.status',
                'minutes.owner_user_id',
                'minutes.approved_at',
                'minutes.created_at',
                'minutes.updated_at',
                'delegation.name as delegation_name',
                'event.title as event_title',
            ]);

        if ($delegationId !== '') {
            $query->where('minutes.delegation_id', is_numeric($delegationId) ? (int) $delegationId : $delegationId);
        }

        if ($status !== '') {
            $query->where('minutes.status', $this->resolveStatus($status));
        }

        if ($keyword !== '') {
            $query->where(function ($builder) use ($keyword): void {
                $builder->where('minutes.title', 'like', "%{$keyword}%")
                    ->orWhere('delegation.name', 'like', "%{$keyword}%")
                    ->orWhere('event.title', 'like', "%{$keyword}%");
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderByDesc('minutes.updated_at')
            ->orderByDesc('minutes.id')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        return [
            'items' => $rows->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'delegationId' => (string) $row->delegation_id,
                'eventId' => $row->event_id !== null ? (string) $row->event_id : null,
                'title' => (string) $row->title,
                'status' => self::STATUS_MAP[(int) $row->status] ?? 'DRAFT',
                'currentVersionNo' => (int) $row->current_version_no,
            ])->all(),
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'total' => $total,
                'totalPages' => (int) ceil($total / $pageSize),
            ],
        ];
    }

    public function findDetail(string $id): ?array
    {
        $minutes = DB::table('ipa_minutes as minutes')
            ->leftJoin('ipa_delegation as delegation', 'delegation.id', '=', 'minutes.delegation_id')
            ->leftJoin('ipa_event as event', 'event.id', '=', 'minutes.event_id')
            ->select([
                'minutes.id',
                'minutes.delegation_id',
                'minutes.event_id',
                'minutes.title',
                'minutes.current_version_no',
                'minutes.status',
                'minutes.owner_user_id',
                'minutes.approved_at',
                'minutes.created_at',
                'minutes.updated_at',
                'delegation.name as delegation_name',
                'event.title as event_title',
            ])
            ->where('minutes.id', $id)
            ->first();

        if (! $minutes) {
            return null;
        }

        $versions = DB::table('ipa_minutes_version')
            ->where('minutes_id', (int) $minutes->id)
            ->orderBy('version_no')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'minutesId' => (string) $row->minutes_id,
                'versionNo' => (int) $row->version_no,
                'contentText' => $row->content_text !== null ? (string) $row->content_text : null,
                'contentJson' => $row->content_json !== null ? json_decode((string) $row->content_json, true) : null,
                'changeSummary' => $row->change_summary !== null ? (string) $row->change_summary : null,
                'editedAt' => $this->formatDate($row->edited_at ?? null),
            ])
            ->all();

        $comments = DB::table('ipa_minutes_comment')
            ->where('minutes_id', (int) $minutes->id)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'minutesId' => (string) $row->minutes_id,
                'versionId' => $row->version_id !== null ? (string) $row->version_id : null,
                'commentText' => (string) $row->comment_text,
                'parentCommentId' => $row->parent_comment_id !== null ? (string) $row->parent_comment_id : null,
                'createdAt' => $this->formatDate($row->created_at ?? null),
            ])
            ->all();

        $approvals = DB::table('ipa_minutes_approval')
            ->where('minutes_id', (int) $minutes->id)
            ->orderBy('id')
            ->get()
            ->map(fn (object $row): array => [
                'id' => (string) $row->id,
                'minutesId' => (string) $row->minutes_id,
                'decision' => (int) $row->decision === 1 ? 'APPROVE' : 'REJECT',
                'decisionNote' => $row->decision_note !== null ? (string) $row->decision_note : null,
                'decidedAt' => $this->formatDate($row->decided_at ?? null),
                'deciderUserId' => (string) $row->approver_user_id,
            ])
            ->all();

        return [
            'minutes' => [
                'id' => (string) $minutes->id,
                'delegationId' => (string) $minutes->delegation_id,
                'eventId' => $minutes->event_id !== null ? (string) $minutes->event_id : null,
                'title' => (string) $minutes->title,
                'status' => self::STATUS_MAP[(int) $minutes->status] ?? 'DRAFT',
                'currentVersionNo' => (int) $minutes->current_version_no,
                'ownerUserId' => (string) $minutes->owner_user_id,
                'approvedAt' => $this->formatDate($minutes->approved_at ?? null),
                'createdAt' => $this->formatDate($minutes->created_at ?? null),
                'updatedAt' => $this->formatDate($minutes->updated_at ?? null),
            ],
            'versions' => $versions,
            'comments' => $comments,
            'approvals' => $approvals,
        ];
    }

    public function createMinutes(array $attributes, int $ownerUserId): ?array
    {
        return DB::transaction(function () use ($attributes, $ownerUserId): ?array {
            $delegationId = $this->nullableInteger(Arr::get($attributes, 'delegationId', Arr::get($attributes, 'delegation_id')));
            $eventId = $this->nullableInteger(Arr::get($attributes, 'eventId', Arr::get($attributes, 'event_id')));
            $title = trim((string) Arr::get($attributes, 'title', ''));
            $content = Arr::get($attributes, 'content');

            if ($delegationId === null || $title === '' || ! DB::table('ipa_delegation')->where('id', $delegationId)->exists()) {
                return null;
            }

            $eventIsValid = $eventId === null || DB::table('ipa_event')->where('id', $eventId)->where('delegation_id', $delegationId)->exists();
            if (! $eventIsValid) {
                return null;
            }

            $minutesId = (int) DB::table('ipa_minutes')->insertGetId([
                'delegation_id' => $delegationId,
                'event_id' => $eventId,
                'title' => $title,
                'current_version_no' => 1,
                'status' => 0,
                'owner_user_id' => $ownerUserId,
                'approved_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ipa_minutes_version')->insert([
                'minutes_id' => $minutesId,
                'version_no' => 1,
                'content_text' => is_string($content) ? $content : null,
                'content_json' => is_array($content) || is_object($content) ? json_encode($content) : null,
                'change_summary' => 'Initial draft',
                'edited_by' => $ownerUserId,
                'edited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'id' => (string) $minutesId,
                'currentVersionNo' => 1,
            ];
        });
    }

    public function createVersion(string $id, array $attributes, int $editedBy): ?array
    {
        return DB::transaction(function () use ($id, $attributes, $editedBy): ?array {
            $minutes = DB::table('ipa_minutes')->where('id', $id)->first();

            if (! $minutes || (int) $minutes->status === 2) {
                return null;
            }

            $contentText = Arr::get($attributes, 'contentText');
            $contentJson = Arr::get($attributes, 'contentJson');
            if (($contentText === null || $contentText === '') && $contentJson === null) {
                return null;
            }

            $nextVersionNo = ((int) $minutes->current_version_no) + 1;

            DB::table('ipa_minutes_version')->insert([
                'minutes_id' => (int) $minutes->id,
                'version_no' => $nextVersionNo,
                'content_text' => $contentText !== null ? (string) $contentText : null,
                'content_json' => $contentJson !== null ? json_encode($contentJson) : null,
                'change_summary' => (string) Arr::get($attributes, 'changeSummary', 'Update'),
                'edited_by' => $editedBy,
                'edited_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ipa_minutes')->where('id', $id)->update([
                'current_version_no' => $nextVersionNo,
                'updated_at' => now(),
            ]);

            return [
                'versionNo' => $nextVersionNo,
                'editedAt' => now()->toIso8601String(),
            ];
        });
    }

    public function createComment(string $id, array $attributes, int $commenterUserId): ?array
    {
        return DB::transaction(function () use ($id, $attributes, $commenterUserId): ?array {
            $minutes = DB::table('ipa_minutes')->where('id', $id)->first();

            if (! $minutes) {
                return null;
            }

            $commentText = trim((string) Arr::get($attributes, 'commentText', ''));
            if ($commentText === '') {
                return null;
            }

            $versionId = $this->nullableInteger(Arr::get($attributes, 'versionId', Arr::get($attributes, 'version_id')));
            if ($versionId !== null && ! DB::table('ipa_minutes_version')->where('id', $versionId)->where('minutes_id', (int) $minutes->id)->exists()) {
                return null;
            }

            $parentCommentId = $this->nullableInteger(Arr::get($attributes, 'parentCommentId', Arr::get($attributes, 'parent_comment_id')));
            if ($parentCommentId !== null && ! DB::table('ipa_minutes_comment')->where('id', $parentCommentId)->where('minutes_id', (int) $minutes->id)->exists()) {
                return null;
            }

            $commentId = (int) DB::table('ipa_minutes_comment')->insertGetId([
                'minutes_id' => (int) $minutes->id,
                'version_id' => $versionId,
                'commenter_user_id' => $commenterUserId,
                'parent_comment_id' => $parentCommentId,
                'comment_text' => $commentText,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'id' => (string) $commentId,
                'commentText' => $commentText,
                'createdAt' => now()->toIso8601String(),
            ];
        });
    }

    public function approve(string $id, array $attributes, int $approverUserId): ?array
    {
        return DB::transaction(function () use ($id, $attributes, $approverUserId): ?array {
            $minutes = DB::table('ipa_minutes')->where('id', $id)->first();

            if (! $minutes) {
                return null;
            }

            $decision = strtoupper(trim((string) Arr::get($attributes, 'decision', '')));
            if (! array_key_exists($decision, self::DECISION_MAP)) {
                return null;
            }

            $isApprove = $decision === 'APPROVE';
            if ((int) $minutes->status === 2 && $isApprove) {
                return null;
            }

            DB::table('ipa_minutes_approval')->insert([
                'minutes_id' => (int) $minutes->id,
                'approver_user_id' => $approverUserId,
                'decision' => self::DECISION_MAP[$decision],
                'decision_note' => Arr::get($attributes, 'decisionNote', Arr::get($attributes, 'decision_note')),
                'decided_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('ipa_minutes')->where('id', $id)->update([
                'status' => $isApprove ? 2 : 1,
                'approved_at' => $isApprove ? now() : null,
                'updated_at' => now(),
            ]);

            return [
                'approved' => $isApprove,
                'status' => $isApprove ? 'FINAL' : 'INTERNAL',
            ];
        });
    }

    private function resolveStatus(string $status): int
    {
        $normalized = strtoupper(trim($status));

        return match ($normalized) {
            '0', 'DRAFT' => 0,
            '1', 'PENDING', 'INTERNAL' => 1,
            '2', 'FINAL', 'SIGNED' => 2,
            default => 0,
        };
    }

    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function formatDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return Carbon::parse((string) $value)->toIso8601String();
    }
}