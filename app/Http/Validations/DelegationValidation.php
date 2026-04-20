<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DelegationValidation
{
    public static function validateStore(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'direction' => 'required|integer|in:1,2', // 1: Inbound, 2: Outbound
            'status' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'country_id' => 'required|integer',
            'host_unit_id' => 'required|integer',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'objective' => 'nullable|string',
            'description' => 'nullable|string',
            'investment_potential' => 'nullable|numeric',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'integer',
            'sector_ids' => 'nullable|array',
            'sector_ids.*' => 'integer',
            'members' => 'nullable|array',
            'members.*.fullName' => 'required|string|max:255',
            'members.*.role' => 'nullable|string|max:255',
            'members.*.organizationName' => 'nullable|string|max:255',
            'members.*.gender' => 'nullable|string|max:10',
            'members.*.identityNumber' => 'nullable|string|max:50',
            'members.*.isVip' => 'nullable|boolean',
            'schedule_items' => 'nullable|array',
            'schedule_items.*.date' => 'required|date',
            'schedule_items.*.title' => 'required|string|max:255',
            'schedule_items.*.note' => 'nullable|string',
            'schedule_items.*.location_id' => 'nullable|integer',
            'schedule_items.*.staff_id' => 'nullable|integer',
            'schedule_items.*.logistics_note' => 'nullable|string',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.itemName' => 'required|string|max:255',
            'checklist_items.*.assigneeId' => 'nullable|integer',
            'checklist_items.*.status' => 'nullable|integer',
            'rating' => 'nullable|integer',
            'outcome' => 'nullable|array',
            'outcome.rating' => 'nullable|integer',
            'outcome.summary' => 'nullable|string',
            'outcome.next_steps' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.contact_name' => 'required|string|max:255',
            'contacts.*.contact_job' => 'nullable|string|max:255',
            'contacts.*.contact_phone' => 'nullable|string|max:50',
            'contacts.*.contact_email' => 'nullable|email|max:255',
        ]);
    }

    public static function validateUpdate(Request $request)
    {
        return Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'direction' => 'sometimes|required|integer|in:1,2',
            'status' => 'nullable|integer',
            'priority' => 'nullable|integer',
            'country_id' => 'sometimes|required|integer',
            'host_unit_id' => 'sometimes|required|integer',
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after_or_equal:start_date',
            'objective' => 'nullable|string',
            'description' => 'nullable|string',
            'investment_potential' => 'nullable|numeric',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'integer',
            'sector_ids' => 'nullable|array',
            'sector_ids.*' => 'integer',
            'members' => 'nullable|array',
            'members.*.fullName' => 'required|string|max:255',
            'members.*.role' => 'nullable|string|max:255',
            'members.*.organizationName' => 'nullable|string|max:255',
            'members.*.gender' => 'nullable|string|max:10',
            'members.*.identityNumber' => 'nullable|string|max:50',
            'members.*.isVip' => 'nullable|boolean',
            'schedule_items' => 'nullable|array',
            'schedule_items.*.date' => 'required|date',
            'schedule_items.*.title' => 'required|string|max:255',
            'schedule_items.*.note' => 'nullable|string',
            'schedule_items.*.location_id' => 'nullable|integer',
            'schedule_items.*.staff_id' => 'nullable|integer',
            'schedule_items.*.logistics_note' => 'nullable|string',
            'checklist_items' => 'nullable|array',
            'checklist_items.*.itemName' => 'required|string|max:255',
            'checklist_items.*.assigneeId' => 'nullable|integer',
            'checklist_items.*.status' => 'nullable|integer',
            'rating' => 'nullable|integer',
            'outcome' => 'nullable|array',
            'outcome.rating' => 'nullable|integer',
            'outcome.summary' => 'nullable|string',
            'outcome.next_steps' => 'nullable|string',
            'contacts' => 'nullable|array',
            'contacts.*.contact_name' => 'required|string|max:255',
            'contacts.*.contact_job' => 'nullable|string|max:255',
            'contacts.*.contact_phone' => 'nullable|string|max:50',
            'contacts.*.contact_email' => 'nullable|email|max:255',
            'approval_remark' => 'nullable|string',
        ]);
    }
}
