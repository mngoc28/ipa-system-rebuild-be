const fs = require('fs');
const path = require('path');

const root = 'd:/ASUS/intern/ipa-rebuild/ipa-system-rebuild-be';
const migrationDir = path.join(root, 'database', 'migrations');
const seederDir = path.join(root, 'database', 'seeders');

const now = new Date('2026-04-13T09:00:00Z');

const tables = [
  { name: 'ipa_country', cols: [
    ['code', 'string', false], ['name_vi', 'string', false], ['name_en', 'string', true], ['is_active', 'boolean', false, true]
  ]},
  { name: 'ipa_md_delegation_type', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true], ['sort_order', 'integer', false, 0]]},
  { name: 'ipa_md_priority', cols: [['code', 'string', false], ['name_vi', 'string', false], ['weight', 'integer', false, 0], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_event_type', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_workflow_status', cols: [['domain_code', 'string', false], ['code', 'string', false], ['name_vi', 'string', false], ['sort_order', 'integer', false, 0], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_task_status', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_terminal', 'boolean', false, false], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_minutes_status', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_partner_status', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_approval_status', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_notification_type', cols: [['code', 'string', false], ['name_vi', 'string', false], ['default_channel', 'smallInteger', false, 0], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_pipeline_stage', cols: [['code', 'string', false], ['name_vi', 'string', false], ['stage_order', 'integer', false, 0], ['is_active', 'boolean', false, true]]},
  { name: 'ipa_md_sector', cols: [['code', 'string', false], ['name_vi', 'string', false], ['is_active', 'boolean', false, true]]},

  { name: 'ipa_role', cols: [['code', 'string', false], ['name', 'string', false], ['is_system', 'boolean', false, true]]},
  { name: 'ipa_permission', cols: [['code', 'string', false], ['module', 'string', false], ['action', 'string', false], ['name', 'string', false]]},

  { name: 'ipa_org_unit', cols: [['unit_code', 'string', false], ['unit_name', 'string', false], ['unit_type', 'string', false], ['parent_unit_id', 'unsignedBigInteger', true], ['manager_user_id', 'unsignedBigInteger', true]]},
  { name: 'ipa_user', cols: [['username', 'string', false], ['email', 'string', false], ['full_name', 'string', false], ['phone', 'string', true], ['avatar_url', 'text', true], ['status', 'smallInteger', false, 1], ['primary_unit_id', 'unsignedBigInteger', true], ['last_login_at', 'timestamp', true]]},

  { name: 'ipa_role_permission', cols: [['role_id', 'unsignedBigInteger', false], ['permission_id', 'unsignedBigInteger', false]]},
  { name: 'ipa_user_role', cols: [['user_id', 'unsignedBigInteger', false], ['role_id', 'unsignedBigInteger', false], ['effective_from', 'timestamp', true], ['effective_to', 'timestamp', true]]},
  { name: 'ipa_user_unit_assignment', cols: [['user_id', 'unsignedBigInteger', false], ['unit_id', 'unsignedBigInteger', false], ['position_title', 'string', true], ['is_primary', 'boolean', false, false]]},

  { name: 'ipa_partner', cols: [['partner_code', 'string', false], ['partner_name', 'string', false], ['country_id', 'unsignedBigInteger', false], ['sector_id', 'unsignedBigInteger', false], ['status', 'smallInteger', false, 0], ['score', 'decimal_3_2', true], ['website', 'text', true], ['notes', 'text', true], ['deleted_at', 'timestamp', true]]},
  { name: 'ipa_partner_contact', cols: [['partner_id', 'unsignedBigInteger', false], ['full_name', 'string', false], ['title', 'string', true], ['email', 'string', true], ['phone', 'string', true], ['is_primary', 'boolean', false, false]]},
  { name: 'ipa_partner_project', cols: [['partner_id', 'unsignedBigInteger', false], ['delegation_id', 'unsignedBigInteger', true], ['project_name', 'string', false], ['stage_id', 'unsignedBigInteger', false], ['estimated_value', 'decimal_18_2', true], ['success_probability', 'decimal_5_2', true], ['status', 'smallInteger', false, 0]]},
  { name: 'ipa_partner_interaction', cols: [['partner_id', 'unsignedBigInteger', false], ['interaction_type', 'smallInteger', false, 0], ['interaction_at', 'timestamp', false], ['owner_user_id', 'unsignedBigInteger', false], ['summary', 'text', true]]},
  { name: 'ipa_partner_score_history', cols: [['partner_id', 'unsignedBigInteger', false], ['old_score', 'decimal_3_2', false], ['new_score', 'decimal_3_2', false], ['reason', 'text', true], ['changed_by', 'unsignedBigInteger', false], ['changed_at', 'timestamp', false]]},

  { name: 'ipa_delegation', cols: [['code', 'string', false], ['name', 'string', false], ['direction', 'smallInteger', false, 1], ['status', 'smallInteger', false, 0], ['priority', 'smallInteger', false, 1], ['country_id', 'unsignedBigInteger', false], ['host_unit_id', 'unsignedBigInteger', false], ['owner_user_id', 'unsignedBigInteger', false], ['start_date', 'date', false], ['end_date', 'date', false], ['participant_count', 'integer', false, 0], ['objective', 'text', true], ['description', 'text', true], ['deleted_at', 'timestamp', true]]},
  { name: 'ipa_delegation_member', cols: [['delegation_id', 'unsignedBigInteger', false], ['full_name', 'string', false], ['title', 'string', true], ['organization_name', 'string', true], ['contact_email', 'string', true], ['contact_phone', 'string', true], ['member_type', 'smallInteger', false, 0]]},
  { name: 'ipa_delegation_contact', cols: [['delegation_id', 'unsignedBigInteger', false], ['partner_contact_id', 'unsignedBigInteger', true], ['name', 'string', false], ['role_name', 'string', true], ['email', 'string', true], ['phone', 'string', true], ['is_primary', 'boolean', false, false]]},
  { name: 'ipa_delegation_checklist', cols: [['delegation_id', 'unsignedBigInteger', false], ['item_name', 'string', false], ['assignee_user_id', 'unsignedBigInteger', true], ['due_date', 'date', true], ['status', 'smallInteger', false, 0], ['priority', 'smallInteger', false, 1]]},
  { name: 'ipa_delegation_outcome', cols: [['delegation_id', 'unsignedBigInteger', false], ['progress_percent', 'decimal_5_2', false, 0], ['summary', 'text', true], ['next_steps', 'text', true], ['report_updated_at', 'timestamp', true]]},
  { name: 'ipa_delegation_tag', cols: [['code', 'string', false], ['name', 'string', false]]},
  { name: 'ipa_delegation_tag_link', cols: [['delegation_id', 'unsignedBigInteger', false], ['tag_id', 'unsignedBigInteger', false]]},

  { name: 'ipa_location', cols: [['name', 'string', false], ['address_line', 'string', true], ['ward', 'string', true], ['district', 'string', true], ['city', 'string', true], ['country_id', 'unsignedBigInteger', true], ['lat', 'decimal_10_7', true], ['lng', 'decimal_10_7', true]]},
  { name: 'ipa_event', cols: [['delegation_id', 'unsignedBigInteger', true], ['title', 'string', false], ['description', 'text', true], ['event_type', 'smallInteger', false, 1], ['status', 'smallInteger', false, 0], ['start_at', 'timestamp', false], ['end_at', 'timestamp', false], ['location_id', 'unsignedBigInteger', true], ['organizer_user_id', 'unsignedBigInteger', false]]},
  { name: 'ipa_event_participant', cols: [['event_id', 'unsignedBigInteger', false], ['user_id', 'unsignedBigInteger', false], ['participation_status', 'smallInteger', false, 0], ['invited_at', 'timestamp', false]]},
  { name: 'ipa_event_external_participant', cols: [['event_id', 'unsignedBigInteger', false], ['full_name', 'string', false], ['organization_name', 'string', true], ['email', 'string', true], ['phone', 'string', true]]},
  { name: 'ipa_event_reschedule_request', cols: [['event_id', 'unsignedBigInteger', false], ['requested_by', 'unsignedBigInteger', false], ['proposed_start_at', 'timestamp', false], ['proposed_end_at', 'timestamp', false], ['reason', 'text', true], ['status', 'smallInteger', false, 0]]},

  { name: 'ipa_minutes', cols: [['delegation_id', 'unsignedBigInteger', false], ['event_id', 'unsignedBigInteger', true], ['title', 'string', false], ['current_version_no', 'integer', false, 1], ['status', 'smallInteger', false, 0], ['owner_user_id', 'unsignedBigInteger', false], ['approved_at', 'timestamp', true]]},
  { name: 'ipa_minutes_version', cols: [['minutes_id', 'unsignedBigInteger', false], ['version_no', 'integer', false], ['content_text', 'text', true], ['content_json', 'json', true], ['change_summary', 'text', true], ['edited_by', 'unsignedBigInteger', false], ['edited_at', 'timestamp', false]]},
  { name: 'ipa_minutes_comment', cols: [['minutes_id', 'unsignedBigInteger', false], ['version_id', 'unsignedBigInteger', true], ['commenter_user_id', 'unsignedBigInteger', false], ['parent_comment_id', 'unsignedBigInteger', true], ['comment_text', 'text', false]]},
  { name: 'ipa_minutes_approval', cols: [['minutes_id', 'unsignedBigInteger', false], ['approver_user_id', 'unsignedBigInteger', false], ['decision', 'smallInteger', false], ['decision_note', 'text', true], ['decided_at', 'timestamp', false]]},
  { name: 'ipa_minutes_signature', cols: [['minutes_id', 'unsignedBigInteger', false], ['signer_user_id', 'unsignedBigInteger', true], ['signer_name', 'string', false], ['signer_role', 'string', true], ['signature_file_id', 'unsignedBigInteger', true], ['signed_at', 'timestamp', true]]},

  { name: 'ipa_task', cols: [['delegation_id', 'unsignedBigInteger', true], ['event_id', 'unsignedBigInteger', true], ['minutes_id', 'unsignedBigInteger', true], ['title', 'string', false], ['description', 'text', true], ['status', 'smallInteger', false, 0], ['priority', 'smallInteger', false, 1], ['due_at', 'timestamp', true], ['is_overdue_cache', 'boolean', false, false], ['created_by', 'unsignedBigInteger', false]]},
  { name: 'ipa_task_assignee', cols: [['task_id', 'unsignedBigInteger', false], ['user_id', 'unsignedBigInteger', false], ['assignment_type', 'smallInteger', false, 1]]},
  { name: 'ipa_task_comment', cols: [['task_id', 'unsignedBigInteger', false], ['commenter_user_id', 'unsignedBigInteger', false], ['comment_text', 'text', false]]},
  { name: 'ipa_task_attachment', cols: [['task_id', 'unsignedBigInteger', false], ['file_id', 'unsignedBigInteger', false]]},
  { name: 'ipa_task_status_history', cols: [['task_id', 'unsignedBigInteger', false], ['old_status', 'smallInteger', false], ['new_status', 'smallInteger', false], ['changed_by', 'unsignedBigInteger', false], ['changed_at', 'timestamp', false]]},

  { name: 'ipa_folder', cols: [['parent_folder_id', 'unsignedBigInteger', true], ['folder_name', 'string', false], ['owner_user_id', 'unsignedBigInteger', false], ['scope_type', 'smallInteger', false, 0]]},
  { name: 'ipa_file', cols: [['folder_id', 'unsignedBigInteger', true], ['file_name', 'string', false], ['file_ext', 'string', true], ['mime_type', 'string', true], ['size_bytes', 'bigInteger', false], ['storage_key', 'string', false], ['checksum', 'string', true], ['uploaded_by', 'unsignedBigInteger', false], ['delegation_id', 'unsignedBigInteger', true], ['minutes_id', 'unsignedBigInteger', true], ['task_id', 'unsignedBigInteger', true]]},
  { name: 'ipa_file_version', cols: [['file_id', 'unsignedBigInteger', false], ['version_no', 'integer', false], ['storage_key', 'string', false], ['size_bytes', 'bigInteger', false], ['updated_by', 'unsignedBigInteger', false], ['updated_at', 'timestamp', false]]},
  { name: 'ipa_file_share', cols: [['file_id', 'unsignedBigInteger', false], ['shared_with_user_id', 'unsignedBigInteger', true], ['shared_with_role_id', 'unsignedBigInteger', true], ['permission_level', 'smallInteger', false, 0], ['expires_at', 'timestamp', true]]},
  { name: 'ipa_file_access_log', cols: [['file_id', 'unsignedBigInteger', false], ['user_id', 'unsignedBigInteger', false], ['action', 'smallInteger', false, 0], ['ip_address', 'string', true], ['action_at', 'timestamp', false]]},

  { name: 'ipa_approval_request', cols: [['request_type', 'string', false], ['ref_table', 'string', false], ['ref_id', 'unsignedBigInteger', false], ['requester_user_id', 'unsignedBigInteger', false], ['current_step', 'integer', false, 1], ['priority', 'smallInteger', false, 1], ['due_at', 'timestamp', true], ['status', 'smallInteger', false, 0]]},
  { name: 'ipa_approval_step', cols: [['approval_request_id', 'unsignedBigInteger', false], ['approver_user_id', 'unsignedBigInteger', false], ['step_order', 'integer', false], ['decision', 'smallInteger', false, 0], ['decision_note', 'text', true], ['decided_at', 'timestamp', true]]},
  { name: 'ipa_approval_history', cols: [['approval_request_id', 'unsignedBigInteger', false], ['old_status', 'smallInteger', false], ['new_status', 'smallInteger', false], ['changed_by', 'unsignedBigInteger', false], ['changed_at', 'timestamp', false]]},

  { name: 'ipa_notification', cols: [['notification_type_id', 'unsignedBigInteger', false], ['title', 'string', false], ['body', 'text', false], ['ref_table', 'string', true], ['ref_id', 'unsignedBigInteger', true], ['severity', 'smallInteger', false, 0]]},
  { name: 'ipa_notification_recipient', cols: [['notification_id', 'unsignedBigInteger', false], ['recipient_user_id', 'unsignedBigInteger', false], ['delivery_status', 'smallInteger', false, 0], ['read_at', 'timestamp', true]]},
  { name: 'ipa_notification_channel', cols: [['notification_id', 'unsignedBigInteger', false], ['channel_type', 'smallInteger', false, 0], ['provider_message_id', 'string', true], ['sent_at', 'timestamp', true], ['fail_reason', 'text', true]]},
  { name: 'ipa_message_template', cols: [['template_code', 'string', false], ['channel_type', 'smallInteger', false, 0], ['language_code', 'string', false, 'vi'], ['subject_template', 'text', true], ['body_template', 'text', false]]},

  { name: 'ipa_report_definition', cols: [['report_code', 'string', false], ['report_name', 'string', false], ['scope_type', 'smallInteger', false, 0], ['owner_role_id', 'unsignedBigInteger', true], ['query_config', 'json', true]]},
  { name: 'ipa_report_run', cols: [['report_definition_id', 'unsignedBigInteger', false], ['run_by', 'unsignedBigInteger', false], ['params_json', 'json', true], ['output_file_id', 'unsignedBigInteger', true], ['status', 'smallInteger', false, 0], ['started_at', 'timestamp', false], ['finished_at', 'timestamp', true], ['error_message', 'text', true]]},
  { name: 'ipa_kpi_metric', cols: [['metric_code', 'string', false], ['metric_name', 'string', false], ['unit', 'string', false], ['scope_type', 'smallInteger', false, 0]]},
  { name: 'ipa_kpi_snapshot', cols: [['metric_id', 'unsignedBigInteger', false], ['snapshot_date', 'date', false], ['org_unit_id', 'unsignedBigInteger', true], ['country_id', 'unsignedBigInteger', true], ['value_numeric', 'decimal_20_4', true], ['value_text', 'string', true]]},
  { name: 'ipa_pipeline_project', cols: [['project_code', 'string', false], ['project_name', 'string', false], ['partner_id', 'unsignedBigInteger', true], ['country_id', 'unsignedBigInteger', false], ['sector_id', 'unsignedBigInteger', false], ['stage_id', 'unsignedBigInteger', false], ['estimated_value', 'decimal_18_2', true], ['success_probability', 'decimal_5_2', true], ['expected_close_date', 'date', true], ['owner_user_id', 'unsignedBigInteger', false], ['status', 'smallInteger', false, 0]]},
  { name: 'ipa_pipeline_stage_history', cols: [['pipeline_project_id', 'unsignedBigInteger', false], ['old_stage_id', 'unsignedBigInteger', false], ['new_stage_id', 'unsignedBigInteger', false], ['changed_by', 'unsignedBigInteger', false], ['changed_at', 'timestamp', false]]},

  { name: 'ipa_system_setting', cols: [['setting_key', 'string', false], ['setting_group', 'string', false], ['setting_value', 'text', true], ['encrypted_value', 'text', true], ['is_secret', 'boolean', false, false], ['updated_by', 'unsignedBigInteger', true]]},
  { name: 'ipa_integration_endpoint', cols: [['provider_code', 'string', false], ['base_url', 'text', true], ['app_id', 'string', true], ['secret_ref', 'string', true], ['status', 'smallInteger', false, 1], ['last_check_at', 'timestamp', true]]},
  { name: 'ipa_integration_health_log', cols: [['integration_id', 'unsignedBigInteger', false], ['check_time', 'timestamp', false], ['status', 'smallInteger', false], ['latency_ms', 'integer', true], ['message', 'text', true]]},

  { name: 'ipa_auth_session', cols: [['user_id', 'unsignedBigInteger', false], ['access_token_jti', 'string', false], ['refresh_token_hash', 'string', false], ['ip_address', 'string', true], ['user_agent', 'text', true], ['issued_at', 'timestamp', false], ['expires_at', 'timestamp', false], ['revoked_at', 'timestamp', true]]},
  { name: 'ipa_password_history', cols: [['user_id', 'unsignedBigInteger', false], ['password_hash', 'string', false], ['changed_at', 'timestamp', false]]},
  { name: 'ipa_login_attempt', cols: [['username_or_email', 'string', false], ['ip_address', 'string', true], ['is_success', 'boolean', false, false], ['reason', 'string', true], ['attempted_at', 'timestamp', false]]},
  { name: 'ipa_audit_log', cols: [['actor_user_id', 'unsignedBigInteger', true], ['action', 'string', false], ['resource_type', 'string', false], ['resource_id', 'unsignedBigInteger', true], ['before_json', 'json', true], ['after_json', 'json', true], ['ip_address', 'string', true], ['user_agent', 'text', true], ['created_at', 'timestamp', false]]},
  { name: 'ipa_domain_event', cols: [['event_name', 'string', false], ['aggregate_type', 'string', false], ['aggregate_id', 'unsignedBigInteger', false], ['payload_json', 'json', false], ['occurred_at', 'timestamp', false], ['published_at', 'timestamp', true]]},
  { name: 'ipa_outbox_event', cols: [['event_type', 'string', false], ['payload_json', 'json', false], ['status', 'smallInteger', false, 0], ['retry_count', 'integer', false, 0], ['next_retry_at', 'timestamp', true], ['created_at', 'timestamp', false]]},
  { name: 'ipa_data_change_history', cols: [['table_name', 'string', false], ['row_id', 'unsignedBigInteger', false], ['operation', 'smallInteger', false], ['diff_json', 'json', false], ['changed_by', 'unsignedBigInteger', true], ['changed_at', 'timestamp', false]]}
];

const fkMap = {
  ipa_user: { primary_unit_id: 'ipa_org_unit' },
  ipa_org_unit: { parent_unit_id: 'ipa_org_unit', manager_user_id: 'ipa_user' },
  ipa_role_permission: { role_id: 'ipa_role', permission_id: 'ipa_permission' },
  ipa_user_role: { user_id: 'ipa_user', role_id: 'ipa_role' },
  ipa_user_unit_assignment: { user_id: 'ipa_user', unit_id: 'ipa_org_unit' },
  ipa_partner: { country_id: 'ipa_country', sector_id: 'ipa_md_sector' },
  ipa_partner_contact: { partner_id: 'ipa_partner' },
  ipa_partner_project: { partner_id: 'ipa_partner', delegation_id: 'ipa_delegation', stage_id: 'ipa_md_pipeline_stage' },
  ipa_partner_interaction: { partner_id: 'ipa_partner', owner_user_id: 'ipa_user' },
  ipa_partner_score_history: { partner_id: 'ipa_partner', changed_by: 'ipa_user' },
  ipa_delegation: { country_id: 'ipa_country', host_unit_id: 'ipa_org_unit', owner_user_id: 'ipa_user' },
  ipa_delegation_member: { delegation_id: 'ipa_delegation' },
  ipa_delegation_contact: { delegation_id: 'ipa_delegation', partner_contact_id: 'ipa_partner_contact' },
  ipa_delegation_checklist: { delegation_id: 'ipa_delegation', assignee_user_id: 'ipa_user' },
  ipa_delegation_outcome: { delegation_id: 'ipa_delegation' },
  ipa_delegation_tag_link: { delegation_id: 'ipa_delegation', tag_id: 'ipa_delegation_tag' },
  ipa_location: { country_id: 'ipa_country' },
  ipa_event: { delegation_id: 'ipa_delegation', location_id: 'ipa_location', organizer_user_id: 'ipa_user' },
  ipa_event_participant: { event_id: 'ipa_event', user_id: 'ipa_user' },
  ipa_event_external_participant: { event_id: 'ipa_event' },
  ipa_event_reschedule_request: { event_id: 'ipa_event', requested_by: 'ipa_user' },
  ipa_minutes: { delegation_id: 'ipa_delegation', event_id: 'ipa_event', owner_user_id: 'ipa_user' },
  ipa_minutes_version: { minutes_id: 'ipa_minutes', edited_by: 'ipa_user' },
  ipa_minutes_comment: { minutes_id: 'ipa_minutes', version_id: 'ipa_minutes_version', commenter_user_id: 'ipa_user', parent_comment_id: 'ipa_minutes_comment' },
  ipa_minutes_approval: { minutes_id: 'ipa_minutes', approver_user_id: 'ipa_user' },
  ipa_minutes_signature: { minutes_id: 'ipa_minutes', signer_user_id: 'ipa_user', signature_file_id: 'ipa_file' },
  ipa_task: { delegation_id: 'ipa_delegation', event_id: 'ipa_event', minutes_id: 'ipa_minutes', created_by: 'ipa_user' },
  ipa_task_assignee: { task_id: 'ipa_task', user_id: 'ipa_user' },
  ipa_task_comment: { task_id: 'ipa_task', commenter_user_id: 'ipa_user' },
  ipa_task_attachment: { task_id: 'ipa_task', file_id: 'ipa_file' },
  ipa_task_status_history: { task_id: 'ipa_task', changed_by: 'ipa_user' },
  ipa_folder: { parent_folder_id: 'ipa_folder', owner_user_id: 'ipa_user' },
  ipa_file: { folder_id: 'ipa_folder', uploaded_by: 'ipa_user', delegation_id: 'ipa_delegation', minutes_id: 'ipa_minutes', task_id: 'ipa_task' },
  ipa_file_version: { file_id: 'ipa_file', updated_by: 'ipa_user' },
  ipa_file_share: { file_id: 'ipa_file', shared_with_user_id: 'ipa_user', shared_with_role_id: 'ipa_role' },
  ipa_file_access_log: { file_id: 'ipa_file', user_id: 'ipa_user' },
  ipa_approval_request: { requester_user_id: 'ipa_user' },
  ipa_approval_step: { approval_request_id: 'ipa_approval_request', approver_user_id: 'ipa_user' },
  ipa_approval_history: { approval_request_id: 'ipa_approval_request', changed_by: 'ipa_user' },
  ipa_notification: { notification_type_id: 'ipa_md_notification_type' },
  ipa_notification_recipient: { notification_id: 'ipa_notification', recipient_user_id: 'ipa_user' },
  ipa_notification_channel: { notification_id: 'ipa_notification' },
  ipa_report_definition: { owner_role_id: 'ipa_role' },
  ipa_report_run: { report_definition_id: 'ipa_report_definition', run_by: 'ipa_user', output_file_id: 'ipa_file' },
  ipa_kpi_snapshot: { metric_id: 'ipa_kpi_metric', org_unit_id: 'ipa_org_unit', country_id: 'ipa_country' },
  ipa_pipeline_project: { partner_id: 'ipa_partner', country_id: 'ipa_country', sector_id: 'ipa_md_sector', stage_id: 'ipa_md_pipeline_stage', owner_user_id: 'ipa_user' },
  ipa_pipeline_stage_history: { pipeline_project_id: 'ipa_pipeline_project', old_stage_id: 'ipa_md_pipeline_stage', new_stage_id: 'ipa_md_pipeline_stage', changed_by: 'ipa_user' },
  ipa_system_setting: { updated_by: 'ipa_user' },
  ipa_integration_health_log: { integration_id: 'ipa_integration_endpoint' },
  ipa_auth_session: { user_id: 'ipa_user' },
  ipa_password_history: { user_id: 'ipa_user' },
  ipa_audit_log: { actor_user_id: 'ipa_user' },
  ipa_data_change_history: { changed_by: 'ipa_user' },
};

function pascalFromTable(table) {
  return table.split('_').map(p => p.charAt(0).toUpperCase() + p.slice(1)).join('');
}

function migrationFileName(index, table) {
  const d = new Date(now.getTime() + index * 1000);
  const ts = `${d.getUTCFullYear()}_${String(d.getUTCMonth()+1).padStart(2,'0')}_${String(d.getUTCDate()).padStart(2,'0')}_${String(d.getUTCHours()).padStart(2,'0')}${String(d.getUTCMinutes()).padStart(2,'0')}${String(d.getUTCSeconds()).padStart(2,'0')}`;
  return `${ts}_create_${table}_table.php`;
}

function colLine(col) {
  const [name, type, nullable, def] = col;
  let line = '';
  switch (type) {
    case 'string': line = `$table->string('${name}')`; break;
    case 'text': line = `$table->text('${name}')`; break;
    case 'boolean': line = `$table->boolean('${name}')`; break;
    case 'smallInteger': line = `$table->smallInteger('${name}')`; break;
    case 'integer': line = `$table->integer('${name}')`; break;
    case 'bigInteger': line = `$table->bigInteger('${name}')`; break;
    case 'unsignedBigInteger': line = `$table->unsignedBigInteger('${name}')`; break;
    case 'timestamp': line = `$table->timestamp('${name}')`; break;
    case 'date': line = `$table->date('${name}')`; break;
    case 'json': line = `$table->json('${name}')`; break;
    case 'decimal_3_2': line = `$table->decimal('${name}', 3, 2)`; break;
    case 'decimal_5_2': line = `$table->decimal('${name}', 5, 2)`; break;
    case 'decimal_10_7': line = `$table->decimal('${name}', 10, 7)`; break;
    case 'decimal_18_2': line = `$table->decimal('${name}', 18, 2)`; break;
    case 'decimal_20_4': line = `$table->decimal('${name}', 20, 4)`; break;
    default: line = `$table->string('${name}')`;
  }
  if (nullable) line += '->nullable()';
  if (def !== undefined && def !== null) {
    if (typeof def === 'string') line += `->default('${def}')`;
    else if (typeof def === 'boolean') line += `->default(${def ? 'true' : 'false'})`;
    else line += `->default(${def})`;
  }
  return `            ${line};`;
}

function fkLines(table) {
  const map = fkMap[table] || {};
  const lines = [];
  for (const [col, parent] of Object.entries(map)) {
    if (parent === table) continue;
    lines.push(`            $table->foreign('${col}')->references('id')->on('${parent}')->nullOnDelete();`);
  }
  return lines;
}

function createMigration(def, index) {
  const className = `Create${pascalFromTable(def.name)}Table`;
  const cols = def.cols.map(colLine).join('\n');
  const fk = fkLines(def.name).join('\n');
  return `<?php

declare(strict_types=1);

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('${def.name}', function (Blueprint $table): void {
            $table->bigIncrements('id');
${cols}
            $table->timestamps();
${fk ? fk : ''}
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('${def.name}');
    }
};
`;
}

function seedValuePhp(table, col) {
  const [name, type] = col;
  const map = fkMap[table] || {};
  if (name in map) return `DB::table('${map[name]}')->value('id')`;
  if (name === 'code' || name.endsWith('_code')) return `'${table.toUpperCase()}_CODE'`;
  if (name === 'email' || name.includes('email')) return `'seed_${table}@example.com'`;
  if (name.includes('phone')) return `'0900000000'`;
  if (name.includes('password_hash')) return `Hash::make('Password@123')`;
  if (name.includes('json')) return `json_encode(['seed' => true])`;
  if (name.includes('date') && type === 'date') return `now()->toDateString()`;
  if (name.includes('at') && type === 'timestamp') return `now()`;

  switch (type) {
    case 'string': return `'${name}_seed'`;
    case 'text': return `'${name} seed text'`;
    case 'boolean': return 'true';
    case 'smallInteger': return '1';
    case 'integer': return '1';
    case 'bigInteger': return '1';
    case 'unsignedBigInteger': return 'null';
    case 'timestamp': return 'now()';
    case 'date': return 'now()->toDateString()';
    case 'json': return `json_encode(['k' => 'v'])`;
    case 'decimal_3_2':
    case 'decimal_5_2':
    case 'decimal_10_7':
    case 'decimal_18_2':
    case 'decimal_20_4': return '1.00';
    default: return `'${name}_seed'`;
  }
}

function createSeeder(def) {
  const className = `${pascalFromTable(def.name)}Seeder`;
  const rowLines = def.cols.map((col) => `                '${col[0]}' => ${seedValuePhp(def.name, col)},`).join('\n');
  return `<?php

declare(strict_types=1);

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Hash;

final class ${className} extends Seeder
{
    public function run(): void
    {
        if (DB::table('${def.name}')->exists()) {
            return;
        }

        DB::table('${def.name}')->insert([
${rowLines}
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
`;
}

fs.mkdirSync(migrationDir, { recursive: true });
fs.mkdirSync(seederDir, { recursive: true });

const migrationFiles = [];
const seederClasses = [];

tables.forEach((table, i) => {
  const mf = migrationFileName(i + 1, table.name);
  const mPath = path.join(migrationDir, mf);
  fs.writeFileSync(mPath, createMigration(table, i), 'utf8');
  migrationFiles.push(mf);

  const sClass = `${pascalFromTable(table.name)}Seeder`;
  const sPath = path.join(seederDir, `${sClass}.php`);
  fs.writeFileSync(sPath, createSeeder(table), 'utf8');
  seederClasses.push(sClass);
});

const dbSeederPath = path.join(seederDir, 'DatabaseSeeder.php');
const callLines = seederClasses.map((c) => `            ${c}::class,`).join('\n');
const dbSeeder = `<?php

declare(strict_types=1);

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
${callLines}
        ]);
    }
}
`;
fs.writeFileSync(dbSeederPath, dbSeeder, 'utf8');

console.log(`Generated migrations: ${migrationFiles.length}`);
console.log(`Generated seeders: ${seederClasses.length}`);
