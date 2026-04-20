<?php

namespace App\Repositories\DelegationRepository;

use App\Models\Event;
use App\Models\Delegation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DelegationRepository implements DelegationRepositoryInterface
{
    private $model;

    public function __construct(Delegation $model)
    {
        $this->model = $model;
    }

    public function getPaginated(Request $request)
    {
        $query = $this->model->newQuery();

        // Enforce data scoping
        $user = auth()->user();
        if ($user) {
            $isStaff = $user->hasRole('STAFF') && !$user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']);
            $isManager = $user->hasRole('MANAGER') && !$user->hasRole(['ADMIN', 'DIRECTOR']);

            if ($isStaff) {
                $query->where('owner_user_id', $user->id);
            } elseif ($isManager) {
                $query->where(function ($q) use ($user) {
                    $q->where('host_unit_id', $user->primary_unit_id)
                      ->orWhere('owner_user_id', $user->id);
                });
            }
        }

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($request->has('direction')) {
            $query->where('direction', $request->get('direction'));
        }

        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        if ($request->has('country_id')) {
            $query->where('country_id', $request->get('country_id'));
        }

        if ($request->has('owner_user_id') && (!$user || !$user->hasRole('STAFF') || $user->hasRole(['ADMIN', 'DIRECTOR', 'MANAGER']))) {
            $query->where('owner_user_id', $request->get('owner_user_id'));
        }

        // Relations
        $query->with(['country', 'partners', 'hostUnit', 'owner', 'sectors', 'tasks', 'checklist']);

        // Sorting
        $query->orderBy('created_at', 'desc');

        return $query->paginate($request->get('per_page', 10));
    }

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
                    $q->where('host_unit_id', $user->primary_unit_id)
                      ->orWhere('owner_user_id', $user->id);
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

        if ($membersData !== []) {
            $membersToCreate = [];
            foreach ($membersData as $member) {
                if (is_string($member) && trim($member) !== '') {
                    $membersToCreate[] = [
                        'full_name' => trim($member),
                        'member_type' => 0,
                    ];
                } elseif (is_array($member) && !empty($member['fullName'])) {
                    $membersToCreate[] = [
                        'full_name' => trim($member['fullName']),
                        'title' => $member['role'] ?? null,
                        'organization_name' => $member['organizationName'] ?? null,
                        'gender' => $member['gender'] ?? null,
                        'identity_number' => $member['identityNumber'] ?? null,
                        'is_vip' => (bool) ($member['isVip'] ?? false),
                        'member_type' => 0,
                    ];
                }
            }

            if ($membersToCreate !== []) {
                $delegation->members()->createMany($membersToCreate);
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
            ], $scheduleItems);

            Event::insert($events);
        }

        if ($partnerIds !== []) {
            $delegation->partners()->sync($partnerIds);
        }

        if ($sectorIds !== []) {
            $delegation->sectors()->sync($sectorIds);
        }

        if ($checklistItems !== []) {
            $delegation->checklist()->createMany(array_map(fn($item) => [
                'item_name' => $item['itemName'] ?? '',
                'assignee_user_id' => $item['assigneeId'] ?? null,
                'status' => $item['status'] ?? 0,
            ], $checklistItems));
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
        if (array_key_exists('contacts', $data)) {
            $delegation->contacts()->createMany(array_map(fn($c) => [
                'name' => $c['contact_name'] ?? '',
                'role_name' => $c['contact_job'] ?? '',
                'email' => $c['contact_email'] ?? '',
                'phone' => $c['contact_phone'] ?? '',
            ], $data['contacts']));
        }

        return $delegation->load([
            'members',
            'events.location',
            'events.staff',
            'country',
            'partners',
            'hostUnit',
            'owner',
            'sectors',
            'checklist',
            'contacts',
            'outcomes',
        ]);
    }

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
                        'delegation_id' => $record->id,
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
                    \App\Models\Event::insert($events);
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

            return $record->load([
                'members',
                'events.location',
                'events.staff',
                'country',
                'partners',
                'hostUnit',
                'owner',
                'sectors',
                'checklist',
                'contacts',
                'outcomes',
            ]);
        }
        return null;
    }

    public function delete(int $id)
    {
        $record = $this->model->find($id);
        if ($record) {
            return $record->delete();
        }
        return false;
    }

    public function updateComment(int $commentId, array $data)
    {
        return \DB::table('ipa_delegation_comment')
            ->where('id', $commentId)
            ->update($data);
    }

    public function deleteComment(int $commentId)
    {
        return \DB::table('ipa_delegation_comment')
            ->where('id', $commentId)
            ->delete();
    }
}
