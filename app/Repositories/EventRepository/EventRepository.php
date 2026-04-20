<?php

declare(strict_types=1);

namespace App\Repositories\EventRepository;

use App\Models\Event;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

final class EventRepository extends BaseRepository implements EventRepositoryInterface
{
    private const EVENT_TYPE_MAP = [
        1 => 'MEETING',
        2 => 'VISIT',
        3 => 'WORKSHOP',
        4 => 'CEREMONY',
    ];

    private const STATUS_MAP = [
        0 => 'PLANNED',
        1 => 'CONFIRMED',
        2 => 'DONE',
        3 => 'CANCELLED',
    ];

    /**
     * Get the model class name for the repository.
     *
     * @return string
     */
    public function getModel(): string
    {
        return Event::class;
    }

    /**
     * Get a paginated list of events with complex filtering and data scoping.
     * Scoping ensures users only see events they are organizing, participating in, or that belong to their unit (for Managers).
     *
     * @param Request $request
     * @param int|null $authUserId
     * @return array
     */
    public function getPaginated(Request $request, ?int $authUserId = null): array
    {
        $page = max(1, (int) $request->input('page', 1));
        $pageSize = max(1, min(100, (int) $request->input('pageSize', 20)));
        $delegationId = trim((string) $request->input('delegationId', ''));
        $organizerId = trim((string) $request->input('organizerId', ''));
        $from = trim((string) $request->input('from', ''));
        $to = trim((string) $request->input('to', ''));
        $eventType = trim((string) $request->input('eventType', ''));
        $status = trim((string) $request->input('status', ''));
        $search = trim((string) $request->input('search', ''));
        $unitId = trim((string) $request->input('unitId', ''));

        // Resolve mock organizer ID if sent as string
        if ($organizerId !== '' && !is_numeric($organizerId)) {
            if ($organizerId === 'lsk5p31wg') {
                $organizerId = '4'; // Map to staff ID
            }
        }

        $user = auth()->user();
        $isStaffOnly = $user && $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);

        $query = DB::table('ipa_event as event')
            ->select([
                'event.id',
                'event.delegation_id',
                'event.title',
                'event.description',
                'event.event_type',
                'event.status',
                'event.start_at',
                'event.end_at',
                'event.location_id',
                'event.organizer_user_id',
                'event.created_at',
                'event.updated_at',
            ]);

        // Enforce data scoping
        $user = auth()->user();
        if ($user) {
            $isStaff = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $query->where(function ($q) use ($user) {
                    $q->where('event.organizer_user_id', $user->id)
                      ->orWhereExists(function ($sub) use ($user) {
                          $sub->select(DB::raw(1))
                              ->from('ipa_event_participant')
                              ->whereColumn('event_id', 'event.id')
                              ->where('user_id', $user->id);
                      });
                });
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    $q->whereExists(function ($sub) use ($user) {
                        $sub->select(DB::raw(1))
                            ->from('ipa_user as uo')
                            ->whereColumn('uo.id', 'event.organizer_user_id')
                            ->where('uo.primary_unit_id', $user->primary_unit_id);
                    })->orWhereExists(function ($sub) use ($user) {
                        $sub->select(DB::raw(1))
                            ->from('ipa_event_participant as ep')
                            ->join('ipa_user as up', 'up.id', '=', 'ep.user_id')
                            ->whereColumn('ep.event_id', 'event.id')
                            ->where('up.primary_unit_id', $user->primary_unit_id);
                    });
                });
            }
        }

        if ($delegationId !== '') {
            $query->where('event.delegation_id', is_numeric($delegationId) ? (int) $delegationId : $delegationId);
        }

        if ($organizerId !== '' && is_numeric($organizerId)) {
            $query->where('event.organizer_user_id', (int) $organizerId);
        }

        if ($unitId !== '') {
            $query->join('ipa_user as u', 'event.organizer_user_id', '=', 'u.id')
                ->where('u.primary_unit_id', is_numeric($unitId) ? (int) $unitId : $unitId);
        }

        if ($from !== '') {
            $query->whereDate('event.start_at', '>=', $from);
        }

        if ($to !== '') {
            $query->whereDate('event.end_at', '<=', $to);
        }

        if ($eventType !== '') {
            $typeInt = $this->resolveEventType($eventType);
            $query->where('event.event_type', $typeInt);
        }

        if ($status !== '') {
            $statusInt = $this->resolveStatus($status);
            $query->where('event.status', $statusInt);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('event.title', 'like', '%' . $search . '%')
                  ->orWhere('event.description', 'like', '%' . $search . '%');
            });
        }

        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('event.start_at')
            ->offset(($page - 1) * $pageSize)
            ->limit($pageSize)
            ->get();

        $eventIds = $rows->pluck('id')->map(static fn ($id): int => (int) $id)->all();
        $participantsByEvent = $this->loadParticipants($eventIds);

        $totalPages = (int) ceil($total / $pageSize);

        return [
            'items' => $rows->map(function (object $row) use ($participantsByEvent): array {
                $eventId = (int) $row->id;
                $participants = $participantsByEvent[$eventId] ?? [];

                return [
                    'id' => (string) $row->id,
                    'delegationId' => $row->delegation_id !== null ? (string) $row->delegation_id : null,
                    'title' => (string) $row->title,
                    'eventType' => self::EVENT_TYPE_MAP[(int) $row->event_type] ?? 'MEETING',
                    'status' => self::STATUS_MAP[(int) $row->status] ?? 'PLANNED',
                    'startAt' => $this->formatDate($row->start_at),
                    'endAt' => $this->formatDate($row->end_at),
                    'locationId' => (int) $row->location_id === 4 ? 'IPA_DA_NANG' : ($row->location_id !== null ? (string) $row->location_id : null),
                    'organizerUserId' => (int) $row->organizer_user_id === 4 ? 'lsk5p31wg' : (string) $row->organizer_user_id,
                    'participantUserIds' => array_map(function (int $userId): string {
                        return $userId === 4 ? 'lsk5p31wg' : (string) $userId;
                    }, array_keys($participants)),
                    'joinStates' => array_reduce(array_keys($participants), function (array $carry, int $userId) use ($participants): array {
                        $key = $userId === 4 ? 'lsk5p31wg' : (string) $userId;
                        $carry[$key] = $participants[$userId] === 1 ? 'JOINED' : 'DECLINED';

                        return $carry;
                    }, []),
                ];
            })->all(),
            'meta' => [
                'page' => $page,
                'pageSize' => $pageSize,
                'per_page' => $pageSize,
                'total' => $total,
                'totalPages' => $totalPages,
                'total_pages' => $totalPages,
            ],
        ];
    }

    /**
     * Find a specific event by ID and return detailed information including participants and reschedule requests.
     *
     * @param string $id
     * @return array|null
     */
    public function findById(string $id): ?array
    {
        $row = DB::table('ipa_event as event')
            ->where('event.id', $id)
            ->first();

        if (! $row) {
            return null;
        }

        $participantsByEvent = $this->loadParticipants([(int) $row->id]);
        $externalParticipants = DB::table('ipa_event_external_participant')
            ->where('event_id', (int) $row->id)
            ->orderBy('id')
            ->get()
            ->map(static function (object $participant): array {
                return [
                    'id' => (string) $participant->id,
                    'fullName' => (string) $participant->full_name,
                    'organizationName' => $participant->organization_name !== null ? (string) $participant->organization_name : null,
                    'email' => $participant->email !== null ? (string) $participant->email : null,
                    'phone' => $participant->phone !== null ? (string) $participant->phone : null,
                ];
            })
            ->all();

        $reschedules = DB::table('ipa_event_reschedule_request')
            ->where('event_id', (int) $row->id)
            ->orderByDesc('id')
            ->get()
            ->map(static function (object $request): array {
                return [
                    'id' => (string) $request->id,
                    'requestedBy' => (string) $request->requested_by,
                    'proposedStartAt' => Carbon::parse((string) $request->proposed_start_at)->toIso8601String(),
                    'proposedEndAt' => Carbon::parse((string) $request->proposed_end_at)->toIso8601String(),
                    'reason' => $request->reason !== null ? (string) $request->reason : null,
                    'status' => (int) $request->status,
                ];
            })
            ->all();

        return [
            'event' => [
                'id' => (string) $row->id,
                'delegationId' => $row->delegation_id !== null ? (string) $row->delegation_id : null,
                'title' => (string) $row->title,
                'description' => $row->description !== null ? (string) $row->description : null,
                'eventType' => self::EVENT_TYPE_MAP[(int) $row->event_type] ?? 'MEETING',
                'status' => self::STATUS_MAP[(int) $row->status] ?? 'PLANNED',
                'startAt' => $this->formatDate($row->start_at),
                'endAt' => $this->formatDate($row->end_at),
                'locationId' => (int) $row->location_id === 4 ? 'IPA_DA_NANG' : ($row->location_id !== null ? (string) $row->location_id : null),
                'organizerUserId' => (int) $row->organizer_user_id === 4 ? 'lsk5p31wg' : (string) $row->organizer_user_id,
                'createdAt' => $this->formatDate($row->created_at),
                'updatedAt' => $this->formatDate($row->updated_at),
            ],
            'participants' => $participantsByEvent[(int) $row->id] ?? [],
            'externalParticipants' => $externalParticipants,
            'rescheduleRequests' => $reschedules,
        ];
    }

    /**
     * Create a new event record and its initial participants within a transaction.
     *
     * @param array $attributes
     * @param int|null $requestedBy
     * @return array|null
     */
    public function createEvent(array $attributes, ?int $requestedBy = null): ?array
    {
        return DB::transaction(function () use ($attributes, $requestedBy): ?array {
            $eventId = (int) DB::table('ipa_event')->insertGetId([
                'delegation_id' => $this->nullableInteger(Arr::get($attributes, 'delegationId', Arr::get($attributes, 'delegation_id'))),
                'title' => (string) Arr::get($attributes, 'title'),
                'description' => Arr::get($attributes, 'description'),
                'event_type' => $this->resolveEventType(Arr::get($attributes, 'eventType', Arr::get($attributes, 'event_type', 'MEETING'))),
                'status' => $this->resolveStatus(Arr::get($attributes, 'status', 'PLANNED')),
                'start_at' => Carbon::parse(Arr::get($attributes, 'startAt', Arr::get($attributes, 'start_at')))->toDateTimeString(),
                'end_at' => Carbon::parse(Arr::get($attributes, 'endAt', Arr::get($attributes, 'end_at')))->toDateTimeString(),
                'location_id' => $this->nullableInteger(Arr::get($attributes, 'locationId', Arr::get($attributes, 'location_id'))),
                'organizer_user_id' => $this->requiredInteger(
                    Arr::get($attributes, 'organizerUserId', Arr::get($attributes, 'organizer_user_id')),
                    $requestedBy
                ),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $participantIds = Arr::get($attributes, 'participantUserIds', Arr::get($attributes, 'participant_user_ids', []));
            if (is_array($participantIds) && $participantIds !== []) {
                $now = now();
                $rows = [];

                foreach ($participantIds as $participantId) {
                    if (! is_numeric($participantId)) {
                        continue;
                    }

                    $rows[] = [
                        'event_id' => $eventId,
                        'user_id' => (int) $participantId,
                        'participation_status' => 1,
                        'invited_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if ($rows !== []) {
                    DB::table('ipa_event_participant')->insert($rows);
                }
            }

            $created = $this->findById((string) $eventId);

            return $created['event'] ?? null;
        });
    }

    /**
     * Update an existing event's details within a transaction.
     *
     * @param string $id
     * @param array $attributes
     * @return array|null
     */
    public function updateEvent(string $id, array $attributes): ?array
    {
        return DB::transaction(function () use ($id, $attributes): ?array {
            $event = DB::table('ipa_event')->where('id', $id)->first();

            if (! $event) {
                return null;
            }

            $payload = [];

            if (Arr::has($attributes, 'delegationId') || Arr::has($attributes, 'delegation_id')) {
                $payload['delegation_id'] = $this->nullableInteger(Arr::get($attributes, 'delegationId', Arr::get($attributes, 'delegation_id')));
            }

            if (Arr::has($attributes, 'title')) {
                $payload['title'] = (string) Arr::get($attributes, 'title');
            }

            if (Arr::has($attributes, 'description')) {
                $payload['description'] = Arr::get($attributes, 'description');
            }

            if (Arr::has($attributes, 'eventType') || Arr::has($attributes, 'event_type')) {
                $payload['event_type'] = $this->resolveEventType(Arr::get($attributes, 'eventType', Arr::get($attributes, 'event_type')));
            }

            if (Arr::has($attributes, 'status')) {
                $payload['status'] = $this->resolveStatus(Arr::get($attributes, 'status'));
            }

            if (Arr::has($attributes, 'startAt') || Arr::has($attributes, 'start_at')) {
                $payload['start_at'] = Carbon::parse(Arr::get($attributes, 'startAt', Arr::get($attributes, 'start_at')))->toDateTimeString();
            }

            if (Arr::has($attributes, 'endAt') || Arr::has($attributes, 'end_at')) {
                $payload['end_at'] = Carbon::parse(Arr::get($attributes, 'endAt', Arr::get($attributes, 'end_at')))->toDateTimeString();
            }

            if (Arr::has($attributes, 'locationId') || Arr::has($attributes, 'location_id')) {
                $payload['location_id'] = $this->nullableInteger(Arr::get($attributes, 'locationId', Arr::get($attributes, 'location_id')));
            }

            $payload['updated_at'] = now();

            DB::table('ipa_event')->where('id', $id)->update($payload);

            $updated = $this->findById($id);

            return $updated['event'] ?? null;
        });
    }

    /**
     * Delete an event and all its related data (participants, reschedule requests) within a transaction.
     *
     * @param string $id
     * @return bool
     */
    public function deleteEvent(string $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            DB::table('ipa_event_participant')->where('event_id', $id)->delete();
            DB::table('ipa_event_external_participant')->where('event_id', $id)->delete();
            DB::table('ipa_event_reschedule_request')->where('event_id', $id)->delete();

            return (bool) DB::table('ipa_event')->where('id', $id)->delete();
        });
    }

    /**
     * Handle a user's decision to join or decline an event.
     *
     * @param string $id
     * @param int $userId
     * @param bool $joined
     * @return array|null
     */
    public function joinEvent(string $id, int $userId, bool $joined): ?array
    {
        return DB::transaction(function () use ($id, $userId, $joined): ?array {
            $event = DB::table('ipa_event')->where('id', $id)->first();

            if (! $event) {
                return null;
            }

            $payload = [
                'event_id' => (int) $id,
                'user_id' => $userId,
                'participation_status' => $joined ? 1 : 2,
                'invited_at' => now(),
                'updated_at' => now(),
            ];

            $existing = DB::table('ipa_event_participant')
                ->where('event_id', (int) $id)
                ->where('user_id', $userId)
                ->first();

            if ($existing) {
                DB::table('ipa_event_participant')->where('id', $existing->id)->update($payload);
            } else {
                $payload['created_at'] = now();
                DB::table('ipa_event_participant')->insert($payload);
            }

            return [
                'participationStatus' => $joined ? 'JOINED' : 'DECLINED',
            ];
        });
    }

    /**
     * Create a formal reschedule request for an event.
     *
     * @param string $id
     * @param array $attributes
     * @param int $requestedBy
     * @return array|null
     */
    public function requestReschedule(string $id, array $attributes, int $requestedBy): ?array
    {
        return DB::transaction(function () use ($id, $attributes, $requestedBy): ?array {
            $event = DB::table('ipa_event')->where('id', $id)->first();

            if (! $event) {
                return null;
            }

            $requestId = (int) DB::table('ipa_event_reschedule_request')->insertGetId([
                'event_id' => (int) $id,
                'requested_by' => $requestedBy,
                'proposed_start_at' => Carbon::parse(
                    Arr::get($attributes, 'proposedStartAt', Arr::get($attributes, 'proposed_start_at'))
                )->toDateTimeString(),
                'proposed_end_at' => Carbon::parse(
                    Arr::get($attributes, 'proposedEndAt', Arr::get($attributes, 'proposed_end_at'))
                )->toDateTimeString(),
                'reason' => Arr::get($attributes, 'reason'),
                'status' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return [
                'id' => (string) $requestId,
                'status' => 'PENDING',
            ];
        });
    }

    /**
     * Batch load participants for a set of event IDs.
     *
     * @param array $eventIds
     * @return array Map of [eventId => [userId => participationStatus]]
     */
    private function loadParticipants(array $eventIds): array
    {
        if ($eventIds === []) {
            return [];
        }

        $rows = DB::table('ipa_event_participant')
            ->whereIn('event_id', $eventIds)
            ->orderBy('id')
            ->get();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[(int) $row->event_id][(int) $row->user_id] = (int) $row->participation_status;
        }

        return $grouped;
    }

    /**
     * Resolve a semantic event type string or numeric string into its integer constant.
     *
     * @param mixed $value
     * @return int
     */
    private function resolveEventType(mixed $value): int
    {
        if (is_numeric($value)) {
            $intValue = (int) $value;

            return array_key_exists($intValue, self::EVENT_TYPE_MAP) ? $intValue : 1;
        }

        $normalized = strtoupper(trim((string) $value));
        $resolved = array_search($normalized, self::EVENT_TYPE_MAP, true);

        return $resolved === false ? 1 : (int) $resolved;
    }

    /**
     * Resolve a semantic status string or numeric string into its integer constant.
     *
     * @param mixed $value
     * @return int
     */
    private function resolveStatus(mixed $value): int
    {
        if (is_numeric($value)) {
            $intValue = (int) $value;

            return array_key_exists($intValue, self::STATUS_MAP) ? $intValue : 0;
        }

        $normalized = strtoupper(trim((string) $value));
        $resolved = array_search($normalized, self::STATUS_MAP, true);

        return $resolved === false ? 0 : (int) $resolved;
    }

    /**
     * Resolve a nullable integer from various input types, including frontend-specific identifiers.
     *
     * @param mixed $value
     * @return int|null
     */
    private function nullableInteger(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        // Mapping for hardcoded strings from frontend
        if ((string) $value === 'IPA_DA_NANG') {
            return 4; // Map to "Trung tâm Hành chính Đà Nẵng"
        }

        return null;
    }

    /**
     * Resolve a required integer with an optional fallback, handling frontend-specific identifiers.
     *
     * @param mixed $value
     * @param int|null $fallback
     * @return int
     */
    private function requiredInteger(mixed $value, ?int $fallback = null): int
    {
        if ($value !== null && $value !== '' && is_numeric($value)) {
            return (int) $value;
        }

        // Mapping for hardcoded strings from frontend
        if ((string) $value === 'lsk5p31wg') {
            return 4; // Map to staff ID
        }

        return $fallback ?? 1;
    }

    /**
     * Standardize a date value into an ISO8601 string.
     *
     * @param mixed $value
     * @return string
     */
    private function formatDate(mixed $value): string
    {
        return Carbon::parse((string) $value)->toIso8601String();
    }
}
