<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IpaLinkedChildSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $userIds = DB::table('ipa_user')->where('id', '>=', 41)->orderBy('id')->pluck('id')->all();
            if (empty($userIds)) {
                $userIds = DB::table('ipa_user')->orderBy('id')->pluck('id')->all();
            }
            $roleIds = DB::table('ipa_role')->orderBy('id')->pluck('id')->all();
            $countryIds = DB::table('ipa_country')->orderBy('id')->pluck('id')->all();
            $sectorIds = DB::table('ipa_md_sector')->orderBy('id')->pluck('id')->all();
            $stageIds = DB::table('ipa_md_pipeline_stage')->orderBy('id')->pluck('id')->all();
            $partnerIds = DB::table('ipa_partner')->orderBy('id')->pluck('id')->all();
            $delegations = DB::table('ipa_delegation')->orderBy('id')->get();
            $delegationIds = $delegations->pluck('id')->all();
            $events = DB::table('ipa_event')->orderBy('id')->get();
            $eventIds = $events->pluck('id')->all();
            $tasks = DB::table('ipa_task')->orderBy('id')->get();
            $taskIds = $tasks->pluck('id')->all();

            if ($userIds === [] || $partnerIds === [] || $delegationIds === [] || $eventIds === [] || $taskIds === []) {
                return;
            }

            $this->seedPartnerTagsAndContacts($partnerIds, $userIds, $stageIds, $delegationIds);
            $this->seedDelegationChildren($delegations, $userIds);
            $this->seedEventChildren($events, $userIds);
            $this->seedTaskChildren($tasks, $userIds);

            $minutesIds = $this->seedMinutesFlow($delegations, $eventIds, $userIds);
            $fileIds = $this->seedFoldersAndFiles($delegationIds, $minutesIds, $taskIds, $userIds);
            $this->seedFileChildren($fileIds, $userIds);
            $this->seedMinutesAttachmentsAndWorkflow($minutesIds, $userIds, $fileIds, $delegationIds, $eventIds);
            $this->seedPipelineAndReporting($partnerIds, $countryIds, $sectorIds, $stageIds, $userIds, $fileIds, $delegationIds, $minutesIds);
            $this->seedNotificationFlow($userIds, $delegationIds, $taskIds, $minutesIds, $roleIds);
        });
    }

    private function seedPartnerTagsAndContacts(array $partnerIds, array $userIds, array $stageIds, array $delegationIds): void
    {
        $tags = [
            ['code' => 'FOLLOW_UP', 'name' => 'Cần theo dõi'],
            ['code' => 'URGENT', 'name' => 'Khẩn'],
            ['code' => 'PARTNERSHIP', 'name' => 'Hợp tác'],
            ['code' => 'TECHNOLOGY', 'name' => 'Công nghệ'],
            ['code' => 'INFRASTRUCTURE', 'name' => 'Hạ tầng'],
            ['code' => 'INVESTMENT', 'name' => 'Đầu tư'],
            ['code' => 'LOGISTICS', 'name' => 'Logistics'],
            ['code' => 'GREEN', 'name' => 'Xanh'],
        ];

        foreach ($tags as $tag) {
            DB::table('ipa_delegation_tag')->updateOrInsert(['code' => $tag['code']], array_merge($tag, ['created_at' => now(), 'updated_at' => now()]));
        }

        $tagIds = DB::table('ipa_delegation_tag')->orderBy('id')->pluck('id')->all();
        $partnerContactRows = [];
        $partnerProjectRows = [];
        $partnerInteractionRows = [];
        $partnerScoreRows = [];

        $contactNames = ['Nguyễn Văn An', 'Trần Thị Bích', 'Lê Minh Tuấn', 'Phạm Quốc Khánh', 'Võ Thị Hồng', 'Đặng Anh Khoa'];
        $titles = ['Giám đốc', 'Trưởng phòng', 'Chuyên viên', 'Phó tổng giám đốc'];

        foreach ($partnerIds as $index => $partnerId) {
            for ($contactIndex = 0; $contactIndex < 2; $contactIndex++) {
                $fullName = $contactNames[($index + $contactIndex) % count($contactNames)] . ' ' . ($contactIndex + 1);
                $partnerContactRows[] = [
                    'partner_id' => $partnerId,
                    'full_name' => $fullName,
                    'title' => $titles[($index + $contactIndex) % count($titles)],
                    'email' => Str::slug($fullName) . '@ipa-danang.gov.vn',
                    'phone' => '09' . str_pad((string) (70000000 + $index * 2 + $contactIndex), 8, '0', STR_PAD_LEFT),
                    'is_primary' => $contactIndex === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $baseScore = 4.0 + (($index % 5) * 0.1);
            $partnerScoreRows[] = [
                'partner_id' => $partnerId,
                'old_score' => round($baseScore - 0.1, 2),
                'new_score' => round($baseScore, 2),
                'reason' => 'Cập nhật theo tiến độ xúc tiến và chất lượng tương tác.',
                'changed_by' => $userIds[$index % count($userIds)],
                'changed_at' => Carbon::now()->subDays(20 - $index),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $partnerInteractionRows[] = [
                'partner_id' => $partnerId,
                'interaction_type' => $index % 4,
                'interaction_at' => Carbon::now()->subDays(10 + $index),
                'owner_user_id' => $userIds[$index % count($userIds)],
                'summary' => 'Trao đổi nhu cầu khảo sát địa điểm và chính sách hỗ trợ.',
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $partnerInteractionRows[] = [
                'partner_id' => $partnerId,
                'interaction_type' => ($index + 1) % 4,
                'interaction_at' => Carbon::now()->subDays(5 + $index),
                'owner_user_id' => $userIds[($index + 1) % count($userIds)],
                'summary' => 'Cập nhật thêm về tiến độ làm việc và đầu mối phối hợp.',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $partnerProjectRows[] = [
                'partner_id' => $partnerId,
                'delegation_id' => $delegationIds[$index % count($delegationIds)],
                'project_name' => 'Dự án mở rộng ' . $partnerId,
                'stage_id' => $stageIds[$index % count($stageIds)],
                'estimated_value' => 1000000 + ($index * 250000),
                'success_probability' => 55 + ($index % 4) * 10,
                'status' => $index % 3,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('ipa_partner_contact')->insert($partnerContactRows);
        DB::table('ipa_partner_score_history')->insert($partnerScoreRows);
        DB::table('ipa_partner_interaction')->insert($partnerInteractionRows);
        DB::table('ipa_partner_project')->insert($partnerProjectRows);

        $contactIds = DB::table('ipa_partner_contact')->orderBy('id')->pluck('id')->all();
        $delegationContactRows = [];
        foreach ($partnerIds as $index => $partnerId) {
            for ($linkIndex = 0; $linkIndex < 2; $linkIndex++) {
                $delegationContactRows[] = [
                    'delegation_id' => $delegationIds[$index % count($delegationIds)],
                    'partner_contact_id' => $contactIds[($index * 2 + $linkIndex) % count($contactIds)],
                    'name' => 'Đầu mối ' . ($index + 1) . '-' . ($linkIndex + 1),
                    'role_name' => $titles[($index + $linkIndex) % count($titles)],
                    'email' => 'contact' . ($index + 1) . ($linkIndex + 1) . '@ipa-danang.gov.vn',
                    'phone' => '09' . str_pad((string) (81000000 + $index * 2 + $linkIndex), 8, '0', STR_PAD_LEFT),
                    'is_primary' => $linkIndex === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('ipa_delegation_contact')->insert($delegationContactRows);

        $tagLinkRows = [];
        foreach ($delegationIds as $index => $delegationId) {
            foreach ([0, 1] as $tagOffset) {
                $tagLinkRows[] = [
                    'delegation_id' => $delegationId,
                    'tag_id' => $tagIds[($index + $tagOffset) % count($tagIds)],
                    'created_at' => now(),
                ];
            }
        }
        DB::table('ipa_delegation_tag_link')->insert($tagLinkRows);
    }

    private function seedDelegationChildren($delegations, array $userIds): void
    {
        $partnerContacts = DB::table('ipa_partner_contact')->orderBy('id')->pluck('id')->all();
        $checklistRows = [];
        $outcomeRows = [];
        $contactRows = [];

        foreach ($delegations as $index => $delegation) {
            for ($itemIndex = 0; $itemIndex < 4; $itemIndex++) {
                $checklistRows[] = [
                    'delegation_id' => $delegation->id,
                    'item_name' => 'Chuẩn bị ' . ['tài liệu', 'lịch làm việc', 'phòng họp', 'biên bản'][$itemIndex],
                    'assignee_user_id' => $userIds[($index + $itemIndex) % count($userIds)],
                    'due_date' => Carbon::parse($delegation->end_date)->subDays(4 - $itemIndex)->toDateString(),
                    'status' => $itemIndex === 3 ? 1 : 0,
                    'priority' => ($itemIndex % 3) + 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $outcomeRows[] = [
                'delegation_id' => $delegation->id,
                'progress_percent' => $delegation->status === 3 ? 100 : ($delegation->status === 2 ? 70 : 25),
                'summary' => 'Tổng hợp tiến độ làm việc cho ' . $delegation->name,
                'next_steps' => 'Tiếp tục theo dõi và chuẩn bị đầu việc giai đoạn sau.',
                'report_updated_at' => Carbon::parse($delegation->end_date)->endOfDay(),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            for ($contactIndex = 0; $contactIndex < 2; $contactIndex++) {
                $contactRows[] = [
                    'delegation_id' => $delegation->id,
                    'partner_contact_id' => $partnerContacts !== [] ? $partnerContacts[($index * 2 + $contactIndex) % count($partnerContacts)] : null,
                    'name' => 'Đại diện ' . ($index + 1) . '-' . ($contactIndex + 1),
                    'role_name' => $contactIndex === 0 ? 'Đầu mối chính' : 'Thư ký đoàn',
                    'email' => 'delegation' . ($index + 1) . ($contactIndex + 1) . '@ipa-danang.gov.vn',
                    'phone' => '09' . str_pad((string) (83000000 + $index * 2 + $contactIndex), 8, '0', STR_PAD_LEFT),
                    'is_primary' => $contactIndex === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('ipa_delegation_checklist')->insert($checklistRows);
        DB::table('ipa_delegation_outcome')->insert($outcomeRows);
        DB::table('ipa_delegation_contact')->insert($contactRows);
    }

    private function seedEventChildren($events, array $userIds): void
    {
        $externalRows = [];
        $rescheduleRows = [];

        foreach ($events as $index => $event) {
            if ($index % 2 === 0) {
                $externalRows[] = [
                    'event_id' => $event->id,
                    'full_name' => 'Ông Nguyễn Văn Minh ' . ($index + 1),
                    'organization_name' => 'FPT',
                    'email' => 'external' . ($index + 1) . '@example.com',
                    'phone' => '09' . str_pad((string) (84000000 + $index), 8, '0', STR_PAD_LEFT),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($index % 3 === 0) {
                $rescheduleRows[] = [
                    'event_id' => $event->id,
                    'requested_by' => $userIds[$index % count($userIds)],
                    'proposed_start_at' => Carbon::parse($event->start_at)->addDays(1),
                    'proposed_end_at' => Carbon::parse($event->end_at)->addDays(1),
                    'reason' => 'Điều chỉnh lịch theo đề nghị của đối tác.',
                    'status' => $index % 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('ipa_event_external_participant')->insert($externalRows);
        DB::table('ipa_event_reschedule_request')->insert($rescheduleRows);
    }

    private function seedTaskChildren($tasks, array $userIds): void
    {
        $assigneeRows = [];
        $historyRows = [];

        foreach ($tasks as $index => $task) {
            $assigneeRows[] = [
                'task_id' => $task->id,
                'user_id' => $userIds[$index % count($userIds)],
                'assignment_type' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $assigneeRows[] = [
                'task_id' => $task->id,
                'user_id' => $userIds[($index + 1) % count($userIds)],
                'assignment_type' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $historyTransitions = match ((int) $task->status) {
                0 => [[0, 0]],
                1 => [[0, 1]],
                default => [[0, 1], [1, 2]],
            };

            foreach ($historyTransitions as $historyIndex => [$oldStatus, $newStatus]) {
                $historyRows[] = [
                    'task_id' => $task->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'changed_by' => $userIds[($index + $historyIndex) % count($userIds)],
                    'changed_at' => $historyIndex === 0
                        ? Carbon::parse($task->created_at)->addDay()
                        : Carbon::parse($task->due_at)->subDay(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('ipa_task_assignee')->insert($assigneeRows);
        DB::table('ipa_task_status_history')->insert($historyRows);
    }

    private function seedMinutesFlow($delegations, array $eventIds, array $userIds): array
    {
        $minutesIds = [];
        $approvalRows = [];

        foreach ($delegations as $index => $delegation) {
            $delegationEventIds = array_slice($eventIds, $index * 3, 3);

            $minutesId = DB::table('ipa_minutes')->insertGetId([
                'delegation_id' => $delegation->id,
                'event_id' => $delegationEventIds[0] ?? null,
                'title' => 'Biên bản làm việc ' . $delegation->code,
                'current_version_no' => 1,
                'status' => $delegation->status === 3 ? 2 : 1,
                'owner_user_id' => $userIds[$index % count($userIds)],
                'approved_at' => $delegation->status === 3 ? Carbon::parse($delegation->end_date)->addDay() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $minutesIds[] = $minutesId;

            $versionIds = [];
            for ($versionNo = 1; $versionNo <= 2; $versionNo++) {
                $versionIds[] = DB::table('ipa_minutes_version')->insertGetId([
                    'minutes_id' => $minutesId,
                    'version_no' => $versionNo,
                    'content_text' => 'Nội dung biên bản phiên ' . $versionNo . ' cho ' . $delegation->name,
                    'content_json' => json_encode(['delegation' => $delegation->code, 'version' => $versionNo], JSON_UNESCAPED_UNICODE),
                    'change_summary' => $versionNo === 1 ? 'Khởi tạo biên bản' : 'Bổ sung ý kiến thống nhất',
                    'edited_by' => $userIds[($index + $versionNo) % count($userIds)],
                    'edited_at' => Carbon::parse($delegation->start_date)->addDays($versionNo),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $commentIds = [];
            for ($commentIndex = 0; $commentIndex < 3; $commentIndex++) {
                $commentIds[] = DB::table('ipa_minutes_comment')->insertGetId([
                    'minutes_id' => $minutesId,
                    'version_id' => $versionIds[$commentIndex % count($versionIds)] ?? null,
                    'commenter_user_id' => $userIds[($index + $commentIndex) % count($userIds)],
                    'parent_comment_id' => $commentIndex === 2 ? ($commentIds[0] ?? null) : null,
                    'comment_text' => ['Đã rà soát biên bản.', 'Cần bổ sung đầu việc theo dõi.', 'Đã cập nhật và chờ phê duyệt.'][$commentIndex],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            for ($approvalIndex = 0; $approvalIndex < 2; $approvalIndex++) {
                $approvalRows[] = [
                    'minutes_id' => $minutesId,
                    'approver_user_id' => $userIds[($index + $approvalIndex + 1) % count($userIds)],
                    'decision' => $delegation->status === 3 ? 1 : 0,
                    'decision_note' => 'Phê duyệt theo luồng làm việc thực tế.',
                    'decided_at' => Carbon::parse($delegation->end_date)->addHours(8 + $approvalIndex),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('ipa_minutes_approval')->insert($approvalRows);

        return $minutesIds;
    }

    private function seedFoldersAndFiles(array $delegationIds, array $minutesIds, array $taskIds, array $userIds): array
    {
        $folderIds = [];
        foreach (['Delegation', 'Minutes', 'Tasks', 'Reports', 'Signatures'] as $index => $name) {
            $folderIds[] = DB::table('ipa_folder')->insertGetId([
                'parent_folder_id' => null,
                'folder_name' => $name,
                'owner_user_id' => $userIds[$index % count($userIds)],
                'scope_type' => $index % 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $fileIds = [];
        foreach ($delegationIds as $index => $delegationId) {
            $fileIds[] = DB::table('ipa_file')->insertGetId([
                'folder_id' => $folderIds[0],
                'file_name' => 'Tài liệu đoàn ' . $delegationId . '.pdf',
                'file_ext' => 'pdf',
                'mime_type' => 'application/pdf',
                'size_bytes' => 120000 + ($index * 5000),
                'storage_key' => 'files/delegation/' . $delegationId . '.pdf',
                'checksum' => 'chk-del-' . $delegationId,
                'uploaded_by' => $userIds[$index % count($userIds)],
                'delegation_id' => $delegationId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($minutesIds as $index => $minutesId) {
            $fileIds[] = DB::table('ipa_file')->insertGetId([
                'folder_id' => $folderIds[1],
                'file_name' => 'Bien-ban-' . $minutesId . '.docx',
                'file_ext' => 'docx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'size_bytes' => 45000 + ($index * 2000),
                'storage_key' => 'files/minutes/' . $minutesId . '.docx',
                'checksum' => 'chk-min-' . $minutesId,
                'uploaded_by' => $userIds[$index % count($userIds)],
                'minutes_id' => $minutesId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($taskIds as $index => $taskId) {
            $fileIds[] = DB::table('ipa_file')->insertGetId([
                'folder_id' => $folderIds[2],
                'file_name' => 'Task-' . $taskId . '.xlsx',
                'file_ext' => 'xlsx',
                'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'size_bytes' => 30000 + ($index * 1500),
                'storage_key' => 'files/task/' . $taskId . '.xlsx',
                'checksum' => 'chk-task-' . $taskId,
                'uploaded_by' => $userIds[$index % count($userIds)],
                'task_id' => $taskId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $fileIds;
    }

    private function seedFileChildren(array $fileIds, array $userIds): void
    {
        $versionRows = [];
        $shareRows = [];
        $logRows = [];

        foreach ($fileIds as $index => $fileId) {
            $versionRows[] = [
                'file_id' => $fileId,
                'version_no' => 1,
                'storage_key' => 'v1/' . $fileId,
                'size_bytes' => 1000 + ($index * 100),
                'updated_by' => $userIds[$index % count($userIds)],
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $shareRows[] = [
                'file_id' => $fileId,
                'shared_with_user_id' => $userIds[($index + 1) % count($userIds)],
                'shared_with_role_id' => null,
                'permission_level' => $index % 2,
                'expires_at' => Carbon::now()->addDays(7),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $logRows[] = [
                'file_id' => $fileId,
                'user_id' => $userIds[$index % count($userIds)],
                'action' => $index % 3,
                'ip_address' => '192.168.1.' . (($index % 200) + 1),
                'action_at' => Carbon::now()->subHours($index),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('ipa_file_version')->insert($versionRows);
        DB::table('ipa_file_share')->insert($shareRows);
        DB::table('ipa_file_access_log')->insert($logRows);
    }

    private function seedMinutesAttachmentsAndWorkflow(array $minutesIds, array $userIds, array $fileIds, array $delegationIds, array $eventIds): void
    {
        $signatureRows = [];
        $attachmentRows = [];
        $historyRows = [];
        $stepRows = [];

        foreach ($minutesIds as $index => $minutesId) {
            $signatureRows[] = [
                'minutes_id' => $minutesId,
                'signer_user_id' => $userIds[$index % count($userIds)],
                'signer_name' => 'Người ký ' . ($index + 1),
                'signer_role' => 'Lãnh đạo',
                'signature_file_id' => $fileIds[$index % count($fileIds)] ?? null,
                'signed_at' => $index % 2 === 0 ? Carbon::now()->subDays(1) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if ($index % 2 === 0) {
                $attachmentRows[] = [
                    'task_id' => DB::table('ipa_task')->orderBy('id')->skip($index)->value('id'),
                    'file_id' => $fileIds[$index % count($fileIds)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        DB::table('ipa_minutes_signature')->insert($signatureRows);
        DB::table('ipa_task_attachment')->insert($attachmentRows);

        foreach ($minutesIds as $index => $minutesId) {
            $requestId = DB::table('ipa_approval_request')->insertGetId([
                'request_type' => 'minutes_approval',
                'ref_table' => 'ipa_minutes',
                'ref_id' => $minutesId,
                'requester_user_id' => $userIds[$index % count($userIds)],
                'current_step' => 2,
                'priority' => ($index % 3) + 1,
                'due_at' => Carbon::now()->addDays(3),
                'status' => $index % 3 === 0 ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            for ($step = 1; $step <= 2; $step++) {
                $stepRows[] = [
                    'approval_request_id' => $requestId,
                    'approver_user_id' => $userIds[($index + $step) % count($userIds)],
                    'step_order' => $step,
                    'decision' => $step === 1 ? 1 : ($index % 2),
                    'decision_note' => 'Xử lý theo luồng phê duyệt.',
                    'decided_at' => Carbon::now()->subDay(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            $historyRows[] = [
                'approval_request_id' => $requestId,
                'old_status' => 0,
                'new_status' => $index % 3 === 0 ? 1 : 2,
                'changed_by' => $userIds[$index % count($userIds)],
                'changed_at' => Carbon::now()->subHours(12),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('ipa_approval_step')->insert($stepRows);
        DB::table('ipa_approval_history')->insert($historyRows);
    }

    private function seedPipelineAndReporting(array $partnerIds, array $countryIds, array $sectorIds, array $stageIds, array $userIds, array $fileIds, array $delegationIds, array $minutesIds): void
    {
        $reportDefs = [
            ['code' => 'RPT_DELEGATION_SUMMARY', 'name' => 'Báo cáo tổng hợp đoàn công tác', 'scope' => 0],
            ['code' => 'RPT_PARTNER_ENGAGEMENT', 'name' => 'Báo cáo tương tác đối tác', 'scope' => 0],
            ['code' => 'RPT_TASK_STATUS', 'name' => 'Báo cáo trạng thái công việc', 'scope' => 1],
            ['code' => 'RPT_APPROVAL_QUEUE', 'name' => 'Danh sách phê duyệt chờ xử lý', 'scope' => 1],
            ['code' => 'RPT_KPI_DASHBOARD', 'name' => 'Tổng quan KPI', 'scope' => 0],
            ['code' => 'RPT_AUDIT_LOG', 'name' => 'Xuất nhật ký hệ thống', 'scope' => 0],
        ];

        foreach ($reportDefs as $index => $definition) {
            DB::table('ipa_report_definition')->updateOrInsert(
                ['report_code' => $definition['code']],
                [
                    'report_name' => $definition['name'],
                    'scope_type' => $definition['scope'],
                    'owner_role_id' => DB::table('ipa_role')->orderBy('id')->value('id'),
                    'query_config' => json_encode(['seed' => true, 'index' => $index], JSON_UNESCAPED_UNICODE),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $metricDefs = [
            ['code' => 'KPI_DELEGATION_COUNT', 'name' => 'Số đoàn công tác', 'unit' => 'count'],
            ['code' => 'KPI_PARTNER_COUNT', 'name' => 'Số đối tác', 'unit' => 'count'],
            ['code' => 'KPI_TASK_COMPLETION_RATE', 'name' => 'Tỷ lệ hoàn thành công việc', 'unit' => 'percentage'],
            ['code' => 'KPI_APPROVAL_TAT', 'name' => 'Thời gian phê duyệt', 'unit' => 'days'],
            ['code' => 'KPI_PROJECT_VALUE', 'name' => 'Giá trị dự án', 'unit' => 'USD'],
            ['code' => 'KPI_EVENT_COUNT', 'name' => 'Số sự kiện', 'unit' => 'count'],
            ['code' => 'KPI_MINUTES_COUNT', 'name' => 'Số biên bản', 'unit' => 'count'],
            ['code' => 'KPI_OVERDUE_TASKS', 'name' => 'Số việc quá hạn', 'unit' => 'count'],
        ];

        foreach ($metricDefs as $index => $metric) {
            DB::table('ipa_kpi_metric')->updateOrInsert(
                ['metric_code' => $metric['code']],
                [
                    'metric_name' => $metric['name'],
                    'unit' => $metric['unit'],
                    'scope_type' => $index % 3,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $metricIds = DB::table('ipa_kpi_metric')->orderBy('id')->pluck('id')->all();
        $snapshotRows = [];
        foreach ($metricIds as $index => $metricId) {
            for ($snapshotIndex = 0; $snapshotIndex < 2; $snapshotIndex++) {
                $snapshotRows[] = [
                    'metric_id' => $metricId,
                    'snapshot_date' => Carbon::now()->subDays(($index * 2) + $snapshotIndex)->toDateString(),
                    'org_unit_id' => DB::table('ipa_org_unit')->orderBy('id')->skip($index % DB::table('ipa_org_unit')->count())->value('id'),
                    'country_id' => $countryIds[$index % count($countryIds)],
                    'value_numeric' => $snapshotIndex === 0 ? (100 + $index * 5) : null,
                    'value_text' => $snapshotIndex === 1 ? 'GOOD' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        DB::table('ipa_kpi_snapshot')->insert($snapshotRows);

        $pipelineRows = [];
        foreach ($partnerIds as $index => $partnerId) {
            $pipelineRows[] = [
                'project_code' => 'PROJ-2026-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
                'project_name' => 'Dự án ' . $partnerId,
                'partner_id' => $partnerId,
                'country_id' => $countryIds[$index % count($countryIds)],
                'sector_id' => $sectorIds[$index % count($sectorIds)],
                'stage_id' => $stageIds[$index % count($stageIds)],
                'estimated_value' => 2000000 + ($index * 500000),
                'success_probability' => 50 + (($index % 5) * 8),
                'expected_close_date' => Carbon::now()->addMonths($index % 4)->toDateString(),
                'owner_user_id' => $userIds[$index % count($userIds)],
                'status' => $index % 4,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ipa_pipeline_project')->insert($pipelineRows);

        $pipelineIds = DB::table('ipa_pipeline_project')->orderBy('id')->pluck('id')->all();
        $pipelineHistoryRows = [];
        foreach ($pipelineIds as $index => $pipelineId) {
            $pipelineHistoryRows[] = [
                'pipeline_project_id' => $pipelineId,
                'old_stage_id' => $stageIds[$index % count($stageIds)],
                'new_stage_id' => $stageIds[($index + 1) % count($stageIds)],
                'changed_by' => $userIds[$index % count($userIds)],
                'changed_at' => Carbon::now()->subDays($index),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ipa_pipeline_stage_history')->insert($pipelineHistoryRows);

        $reportRunRows = [];
        foreach ($reportDefs as $index => $definition) {
            $reportId = DB::table('ipa_report_definition')->where('report_code', $definition['code'])->value('id');
            $reportRunRows[] = [
                'report_definition_id' => $reportId,
                'run_by' => $userIds[$index % count($userIds)],
                'params_json' => json_encode(['delegationId' => $delegationIds[$index % count($delegationIds)]], JSON_UNESCAPED_UNICODE),
                'output_file_id' => $fileIds[$index % count($fileIds)] ?? null,
                'status' => $index % 4,
                'started_at' => Carbon::now()->subHours(12 - $index),
                'finished_at' => $index % 4 === 2 ? Carbon::now()->subHours(2) : null,
                'error_message' => $index % 4 === 3 ? 'Lỗi dữ liệu đầu vào.' : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        DB::table('ipa_report_run')->insert($reportRunRows);
    }

    private function seedNotificationFlow(array $userIds, array $delegationIds, array $taskIds, array $minutesIds, array $roleIds): void
    {
        $notificationTypeId = DB::table('ipa_md_notification_type')->orderBy('id')->value('id');
        if (! $notificationTypeId) {
            return;
        }

        $templates = [
            ['code' => 'DELEGATION_CREATED', 'channel' => 0, 'subject' => 'Đoàn công tác mới: {{delegation_name}}', 'body' => 'Đoàn {{delegation_name}} đã được khởi tạo.'],
            ['code' => 'TASK_OVERDUE', 'channel' => 2, 'subject' => null, 'body' => 'Công việc {{task_title}} đã quá hạn cần xử lý ngay.'],
            ['code' => 'APPROVAL_NEEDED', 'channel' => 0, 'subject' => 'Cần phê duyệt: {{request_type}}', 'body' => 'Vui lòng xử lý yêu cầu {{request_type}} trước hạn.'],
            ['code' => 'MINUTES_APPROVED', 'channel' => 0, 'subject' => 'Biên bản đã được duyệt', 'body' => 'Biên bản {{minutes_title}} đã được phê duyệt.'],
            ['code' => 'EVENT_UPDATED', 'channel' => 2, 'subject' => null, 'body' => 'Sự kiện {{event_title}} vừa được cập nhật lịch.'],
            ['code' => 'REPORT_READY', 'channel' => 0, 'subject' => 'Báo cáo sẵn sàng', 'body' => 'Báo cáo {{report_code}} đã hoàn thành.'],
        ];

        foreach ($templates as $template) {
            DB::table('ipa_message_template')->updateOrInsert(
                ['template_code' => $template['code']],
                [
                    'channel_type' => $template['channel'],
                    'language_code' => 'vi',
                    'subject_template' => $template['subject'],
                    'body_template' => $template['body'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        foreach (range(0, 7) as $index) {
            $notificationId = DB::table('ipa_notification')->insertGetId([
                'notification_type_id' => $notificationTypeId,
                'title' => 'Thông báo IPA ' . ($index + 1),
                'body' => 'Nội dung thông báo cho luồng nghiệp vụ thực tế.',
                'ref_table' => $index % 2 === 0 ? 'ipa_delegation' : 'ipa_task',
                'ref_id' => $index % 2 === 0 ? $delegationIds[$index % count($delegationIds)] : $taskIds[$index % count($taskIds)],
                'severity' => $index % 4,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $recipientRows = [];
            foreach ([0, 1, 2] as $recipientIndex) {
                $recipientRows[] = [
                    'notification_id' => $notificationId,
                    'recipient_user_id' => $userIds[($index + $recipientIndex) % count($userIds)],
                    'delivery_status' => $recipientIndex === 0 ? 2 : ($recipientIndex === 1 ? 1 : 0),
                    'read_at' => $recipientIndex === 0 ? Carbon::now()->subHours(1) : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('ipa_notification_recipient')->insert($recipientRows);

            $channelRows = [];
            foreach ([0, 1] as $channelIndex) {
                $channelRows[] = [
                    'notification_id' => $notificationId,
                    'channel_type' => $channelIndex,
                    'provider_message_id' => 'MSG-' . $notificationId . '-' . $channelIndex,
                    'sent_at' => Carbon::now()->subMinutes($index * 5 + $channelIndex),
                    'fail_reason' => $channelIndex === 1 && $index % 3 === 0 ? 'Timeout from provider' : null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('ipa_notification_channel')->insert($channelRows);
        }
    }
}