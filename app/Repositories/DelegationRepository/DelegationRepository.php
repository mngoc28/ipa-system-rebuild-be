<?php

namespace App\Repositories\DelegationRepository;

use App\Models\Event;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DelegationRepository implements DelegationRepositoryInterface
{
    /**
     * @var Delegation The Eloquent model instance.
     */
    private $model;

    /**
     * DelegationRepository constructor.
     *
     * @param Delegation $model
     */
    public function __construct(Delegation $model)
    {
        $this->model = $model;
    }

    /**
     * Get a paginated list of delegations with scoping, search, and filtering.
     * Uses raw DB query to avoid N+1 queries from Eloquent model appends.
     * Returns a flat, normalized array for list views.
     *
     * @param Request $request
     * @return array
     */
    public function getPaginated(Request $request): array
    {
        $user = auth()->user();
        $perPage = max(1, min(100, (int) $request->get('per_page', 10)));
        $page    = max(1, (int) $request->get('page', 1));

        $query = DB::table('ipa_delegation as d')
            ->leftJoin('ipa_country as c', 'c.id', '=', 'd.country_id')
            ->leftJoin('ipa_user as owner', 'owner.id', '=', 'd.owner_user_id')
            ->leftJoin('ipa_org_unit as hu', 'hu.id', '=', 'd.host_unit_id')
            ->whereNull('d.deleted_at')
            ->select([
                'd.id',
                'd.code',
                'd.name',
                'd.direction',
                'd.status',
                'd.priority',
                'd.country_id',
                'd.host_unit_id',
                'd.owner_user_id',
                'd.start_date',
                'd.end_date',
                'd.participant_count',
                'd.objective',
                'd.description',
                'd.approval_remark',
                'd.created_at',
                'd.updated_at',
                // country flat fields
                'c.name_vi as country_name_vi',
                'c.name_en as country_name_en',
                'c.code as country_code',
                // owner flat fields
                'owner.full_name as owner_full_name',
                'owner.avatar_url as owner_avatar_url',
                // host unit flat fields
                'hu.unit_name as host_unit_name',
                'hu.unit_code as host_unit_code',
            ]);

        // --- Data scoping by role ---
        if ($user) {
            $user->loadMissing('roles');
            $isStaff   = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $query->where('d.owner_user_id', $user->id);
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    $q->where('d.owner_user_id', $user->id)
                      ->orWhere('owner.primary_unit_id', $user->primary_unit_id);
                });
            }
        }

        // --- Filters ---
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('d.name', 'like', "%{$search}%")
                  ->orWhere('d.code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('direction')) {
            $query->where('d.direction', $request->get('direction'));
        }

        if ($request->filled('status')) {
            $query->where('d.status', $request->get('status'));
        }

        if ($request->filled('priority')) {
            $query->where('d.priority', $request->get('priority'));
        }

        if ($request->filled('country_id')) {
            $query->where('d.country_id', $request->get('country_id'));
        }

        if ($request->filled('owner_user_id') && (!$user || !$user->hasRole('STAFF') || $user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']))) {
            $query->where('d.owner_user_id', $request->get('owner_user_id'));
        }

        // --- Count & paginate ---
        $total = (clone $query)->count();

        $rows = $query
            ->orderBy('d.created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // --- Batch-load partner IDs & sector IDs for all delegation IDs in one query ---
        $delegationIds = $rows->pluck('id')->all();

        $partnerMap = [];
        if ($delegationIds) {
            $partnerRows = DB::table('ipa_delegation_partner_link as dpl')
                ->join('ipa_partner as p', 'p.id', '=', 'dpl.partner_id')
                ->whereIn('dpl.delegation_id', $delegationIds)
                ->select('dpl.delegation_id', 'p.id as partner_id', 'p.partner_name')
                ->get();
            foreach ($partnerRows as $pr) {
                $partnerMap[$pr->delegation_id][] = [
                    'id'           => (int) $pr->partner_id,
                    'partner_name' => $pr->partner_name,
                ];
            }
        }

        $sectorMap = [];
        if ($delegationIds) {
            $sectorRows = DB::table('ipa_delegation_sector_link as dsl')
                ->join('ipa_md_sector as s', 's.id', '=', 'dsl.sector_id')
                ->whereIn('dsl.delegation_id', $delegationIds)
                ->select('dsl.delegation_id', 's.id as sector_id', 's.name_vi', 's.code')
                ->get();
            foreach ($sectorRows as $sr) {
                $sectorMap[$sr->delegation_id][] = [
                    'id'      => (int) $sr->sector_id,
                    'name'    => $sr->name_vi,
                    'code'    => $sr->code,
                ];
            }
        }

        $items = $rows->map(fn ($row) => $this->normalizeDelegation($row, $partnerMap, $sectorMap))->all();

        return [
            'items' => $items,
            'meta' => [
                'page'       => $page,
                'pageSize'   => $perPage,
                'total'      => $total,
                'totalPages' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Normalize a raw delegation DB row into a flat response array for list views.
     *
     * @param object $row
     * @param array $partnerMap
     * @param array $sectorMap
     * @return array
     */
    private function normalizeDelegation(object $row, array $partnerMap = [], array $sectorMap = []): array
    {
        $id = (int) $row->id;
        $appUrl = rtrim((string) config('app.url'), '/');

        // Faster avatar URL generation
        $avatarUrl = null;
        if ($row->owner_avatar_url) {
            $avatarUrl = str_starts_with((string) $row->owner_avatar_url, 'http')
                ? $row->owner_avatar_url
                : $appUrl . '/storage/' . $row->owner_avatar_url;
        } else {
            $avatarUrl = 'https://ui-avatars.com/api/?name='
                . urlencode((string) ($row->owner_full_name ?? 'User'))
                . '&background=DBEAFE&color=3B82F6&bold=true';
        }

        return [
            'id'                => $id,
            'code'              => $row->code,
            'name'              => $row->name,
            'direction'         => (int) $row->direction,
            'status'            => (int) $row->status,
            'priority'          => (int) $row->priority,
            'country_id'        => $row->country_id ? (int) $row->country_id : null,
            'host_unit_id'      => $row->host_unit_id ? (int) $row->host_unit_id : null,
            'owner_user_id'     => $row->owner_user_id ? (int) $row->owner_user_id : null,
            'start_date'        => $row->start_date,
            'end_date'          => $row->end_date,
            'participant_count' => (int) $row->participant_count,
            'objective'         => $row->objective,
            'description'       => $row->description,
            'approval_remark'   => $row->approval_remark,
            'created_at'        => $row->created_at,
            'updated_at'        => $row->updated_at,
            // Flat nested: country
            'country' => $row->country_id ? [
                'id'      => (int) $row->country_id,
                'name_vi' => $row->country_name_vi,
                'name_en' => $row->country_name_en,
                'code'    => $row->country_code,
            ] : null,
            // Flat nested: owner
            'owner' => $row->owner_user_id ? [
                'id'         => (int) $row->owner_user_id,
                'full_name'  => $row->owner_full_name,
                'avatar_url' => $avatarUrl,
            ] : null,
            // Flat nested: host_unit
            'host_unit' => $row->host_unit_id ? [
                'id'        => (int) $row->host_unit_id,
                'unit_name' => $row->host_unit_name,
                'unit_code' => $row->host_unit_code,
            ] : null,
            // Batch-loaded relations
            'partners' => $partnerMap[$id] ?? [],
            'sectors'  => $sectorMap[$id] ?? [],
        ];
    }

    /**
     * Get a specific delegation by ID with all relevant relationships.
     * Scoping rules are applied similar to getPaginated.
     *
     * @param int $id
     * @return Delegation|null
     */
    public function getById(int $id)
    {
        $query = $this->model->newQuery();

        $user = auth()->user();
        if ($user) {
            $isStaff = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $query->where('owner_user_id', $user->id);
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    $q->where('owner_user_id', $user->id)
                      ->orWhereHas('owner', function ($ownerQuery) use ($user) {
                          $ownerQuery->where('primary_unit_id', $user->primary_unit_id);
                      });
                });
            }
        }

        return $query->with([
            'members',
            'events',
            'outcomes',
            'country',
            'partners',
            'hostUnit',
            'sectors',
            'checklist',
            'contacts'
        ])->find($id);
    }

    /**
     * Create a new delegation and its related entities (members, events, partners, sectors, checklist, outcomes, contacts).
     *
     * @param array $data
     * @return Delegation
     */
    public function create(array $data)
    {
        $membersData = (array) ($data['members'] ?? []);
        $partnerIds = (array) ($data['partner_ids'] ?? []);
        $sectorIds = (array) ($data['sector_ids'] ?? []);
        $checklistItems = (array) ($data['checklist_items'] ?? []);
        $scheduleItems = array_values(array_filter(
            (array) ($data['schedule_items'] ?? []),
            static fn ($item) => is_array($item) && !empty($item['date']) && !empty($item['title'])
        ));

        unset($data['members'], $data['schedule_items'], $data['partner_ids'], $data['sector_ids'], $data['checklist_items']);

        $data['participant_count'] = count($membersData);

        $delegation = $this->model->create($data);
        $now = now();

        if ($membersData !== []) {
            $membersToCreate = [];
            foreach ($membersData as $member) {
                if (is_string($member) && trim($member) !== '') {
                    $membersToCreate[] = [
                        'delegation_id' => $delegation->id,
                        'full_name' => trim($member),
                        'member_type' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                } elseif (is_array($member) && !empty($member['fullName'])) {
                    $membersToCreate[] = [
                        'delegation_id' => $delegation->id,
                        'full_name' => trim($member['fullName']),
                        'title' => $member['role'] ?? null,
                        'organization_name' => $member['organizationName'] ?? null,
                        'gender' => $member['gender'] ?? null,
                        'identity_number' => $member['identityNumber'] ?? null,
                        'is_vip' => (bool) ($member['isVip'] ?? false),
                        'member_type' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }

            if ($membersToCreate !== []) {
                DB::table('ipa_delegation_member')->insert($membersToCreate);
            }
        }

        if ($scheduleItems !== []) {
            $events = array_map(static fn (array $scheduleItem) => [
                'delegation_id' => $delegation->id,
                'title' => trim((string) $scheduleItem['title']),
                'description' => isset($scheduleItem['note']) ? trim((string) $scheduleItem['note']) : null,
                'event_type' => 1,
                'status' => 0,
                'start_at' => Carbon::parse((string) $scheduleItem['date'])->startOfDay(),
                'end_at' => Carbon::parse((string) $scheduleItem['date'])->endOfDay(),
                'location_id' => $scheduleItem['location_id'] ?? null,
                'staff_id' => $scheduleItem['staff_id'] ?? null,
                'logistics_note' => $scheduleItem['logistics_note'] ?? null,
                'organizer_user_id' => (int) $delegation->owner_user_id,
                'created_at' => $now,
                'updated_at' => $now,
            ], $scheduleItems);

            DB::table('ipa_event')->insert($events);
        }

        if ($partnerIds !== []) {
            $delegation->partners()->sync($partnerIds);
        }

        if ($sectorIds !== []) {
            $delegation->sectors()->sync($sectorIds);
        }

        if ($checklistItems !== []) {
            $checklistData = array_map(fn($item) => [
                'delegation_id' => $delegation->id,
                'item_name' => $item['itemName'] ?? '',
                'assignee_user_id' => $item['assigneeId'] ?? null,
                'status' => $item['status'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ], $checklistItems);
            DB::table('ipa_delegation_checklist')->insert($checklistData);
        }

        // Outcomes
        if (array_key_exists('outcome', $data)) {
            $o = $data['outcome'];
            $delegation->outcomes()->create([
                'rating' => $o['rating'] ?? 0,
                'summary' => $o['summary'] ?? '',
                'next_steps' => $o['next_steps'] ?? '',
            ]);
        }

        // Contacts
        if (isset($data['contacts']) && is_array($data['contacts'])) {
            $contactsData = array_map(fn($c) => [
                'delegation_id' => $delegation->id,
                'name' => $c['contact_name'] ?? '',
                'role_name' => $c['contact_job'] ?? '',
                'email' => $c['contact_email'] ?? '',
                'phone' => $c['contact_phone'] ?? '',
                'created_at' => $now,
                'updated_at' => $now,
            ], $data['contacts']);
            DB::table('ipa_delegation_contact')->insert($contactsData);
        }

        return $delegation;
    }

    /**
     * Update an existing delegation and refresh its related entities.
     * Relational data is typically cleared and recreated for consistency.
     *
     * @param int $id
     * @param array $data
     * @return Delegation|null
     */
    public function update(int $id, array $data)
    {
        $record = $this->model->find($id);
        if ($record) {
            $partnerIds = null;
            if (array_key_exists('partner_ids', $data)) {
                $partnerIds = (array) $data['partner_ids'];
                unset($data['partner_ids']);
            }

            $oldStatus = (int) $record->status;
            $record->update($data);

            // Trigger notification if status changed to PENDING (1)
            $newStatus = (int) ($data['status'] ?? $oldStatus);
            // Status check moved to Service layer

            if ($partnerIds !== null) {
                $record->partners()->sync($partnerIds);
            }

            if (array_key_exists('sector_ids', $data)) {
                $record->sectors()->sync($data['sector_ids']);
            }

            if (array_key_exists('members', $data)) {
                $record->members()->delete();
                $record->members()->createMany(array_map(fn($m) => [
                    'full_name' => $m['fullName'] ?? '',
                    'title' => $m['role'] ?? null,
                    'organization_name' => $m['organizationName'] ?? null,
                    'gender' => $m['gender'] ?? null,
                    'identity_number' => $m['identityNumber'] ?? null,
                    'is_vip' => (bool) ($m['isVip'] ?? false),
                ], $data['members']));
            }

            // Schedule Items (Events)
            if (array_key_exists('schedule_items', $data)) {
                $record->events()->delete();
                $scheduleItems = array_values(array_filter(
                    (array) $data['schedule_items'],
                    static fn ($item) => is_array($item) && !empty($item['date']) && !empty($item['title'])
                ));

                if ($scheduleItems !== []) {
                    $events = array_map(static fn (array $item) => [
                        'title' => trim((string) $item['title']),
                        'description' => isset($item['note']) ? trim((string) $item['note']) : null,
                        'event_type' => 1,
                        'status' => 0,
                        'start_at' => Carbon::parse((string) $item['date'])->startOfDay(),
                        'end_at' => Carbon::parse((string) $item['date'])->endOfDay(),
                        'location_id' => $item['location_id'] ?? null,
                        'staff_id' => $item['staff_id'] ?? null,
                        'logistics_note' => $item['logistics_note'] ?? null,
                        'organizer_user_id' => (int) $record->owner_user_id,
                    ], $scheduleItems);
                    $record->events()->createMany($events);
                }
            }

            // Checklist Items
            if (array_key_exists('checklist_items', $data)) {
                $record->checklist()->delete();
                $record->checklist()->createMany(array_map(fn($item) => [
                    'item_name' => $item['itemName'] ?? '',
                    'assignee_user_id' => $item['assigneeId'] ?? null,
                    'status' => $item['status'] ?? 0,
                ], $data['checklist_items']));
            }

            // Outcomes (handle as single for now)
            if (array_key_exists('outcome', $data)) {
                $o = $data['outcome'];
                $record->outcomes()->delete();
                $record->outcomes()->create([
                    'rating' => $o['rating'] ?? 0,
                    'summary' => $o['summary'] ?? '',
                    'next_steps' => $o['next_steps'] ?? '',
                ]);
            }

            // Contacts
            if (array_key_exists('contacts', $data)) {
                $record->contacts()->delete();
                $record->contacts()->createMany(array_map(fn($c) => [
                    'name' => $c['contact_name'] ?? '',
                    'role_name' => $c['contact_job'] ?? '',
                    'email' => $c['contact_email'] ?? '',
                    'phone' => $c['contact_phone'] ?? '',
                ], $data['contacts']));
            }

            return $record;
        }
        return null;
    }

    /**
     * Delete a delegation record by ID.
     *
     * @param int $id
     * @return bool|null
     */
    public function delete(int $id)
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    /**
     * Update a specific delegation comment directly via DB table.
     *
     * @param int $commentId
     * @param array $data
     * @return int
     */
    public function updateComment(int $commentId, array $data)
    {
        return \DB::table('ipa_delegation_comment')
            ->where('id', $commentId)
            ->update($data);
    }

    /**
     * Delete a specific delegation comment directly via DB table.
     *
     * @param int $commentId
     * @return int
     */
    public function deleteComment(int $commentId)
    {
        return \DB::table('ipa_delegation_comment')
            ->where('id', $commentId)
            ->delete();
    }
}
