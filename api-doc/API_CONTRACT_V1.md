# API Contract v1

Tai lieu nay mo ta API contract de xuat dua tren UI hien tai va schema da chot (74 bang), theo huong REST, version hoa v1.

## 0) Chuan Chung

- Base URL: `/api/v1`
- Auth: `Bearer JWT`
- Time format: `ISO-8601 UTC`
- Pagination query: `page`, `pageSize`, `sortBy`, `sortDir`

### Response Envelope

```json
{
  "success": true,
  "data": {},
  "meta": {
    "page": 1,
    "pageSize": 20,
    "total": 100,
    "totalPages": 5,
    "sortBy": "createdAt",
    "sortDir": "desc"
  },
  "message": "OK",
  "requestId": "req_01H...",
  "timestamp": "2026-04-13T10:00:00Z"
}
```

### Error Envelope

```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Du lieu khong hop le",
    "details": [
      {
        "field": "email",
        "message": "Email khong dung dinh dang"
      }
    ]
  },
  "requestId": "req_01H...",
  "timestamp": "2026-04-13T10:00:00Z"
}
```

### HTTP Status Chuan

- `200 OK`
- `201 Created`
- `204 No Content`
- `400 Bad Request`
- `401 Unauthorized`
- `403 Forbidden`
- `404 Not Found`
- `409 Conflict`
- `422 Unprocessable Entity`
- `429 Too Many Requests`
- `500 Internal Server Error`

## 1) Auth

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/auth/login` | `POST` | `{ usernameOrEmail, password }` | `{ accessToken, refreshToken, expiresIn, user }` | `401 INVALID_CREDENTIALS`, `423 ACCOUNT_LOCKED` |
| `/auth/refresh` | `POST` | `{ refreshToken }` | `{ accessToken, expiresIn }` | `401 REFRESH_TOKEN_INVALID`, `401 SESSION_REVOKED` |
| `/auth/logout` | `POST` | `{ refreshToken }` | `204` | `401 UNAUTHORIZED` |
| `/auth/me` | `GET` | Header Bearer | `{ id, fullName, email, roles, permissions, unit }` | `401 UNAUTHORIZED` |
| `/auth/change-password-first-time` | `POST` | `{ oldPassword, newPassword, confirmPassword }` | `{ changed: true }` | `422 PASSWORD_POLICY_VIOLATION`, `400 PASSWORD_MISMATCH` |

## 2) Dashboard

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/dashboard/summary` | `GET` | query: `scope=staff|manager|director|admin` | `{ stats, alerts, overdueTasks }` | `403 FORBIDDEN_SCOPE` |
| `/dashboard/tasks` | `GET` | query: `status, priority, overdue, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |

## 3) Users + RBAC (Admin)

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/admin/users` | `GET` | query: `keyword, status, roleId, unitId, page, pageSize` | `{ items[], meta }` | `403 FORBIDDEN` |
| `/admin/users` | `POST` | `{ username, fullName, email, phone, unitId, roleIds[] }` | `{ id, ... }` | `409 EMAIL_EXISTS`, `422 VALIDATION_ERROR` |
| `/admin/users/{userId}` | `GET` | path `userId` | `{ user, roles[], permissions[] }` | `404 USER_NOT_FOUND` |
| `/admin/users/{userId}` | `PATCH` | `{ fullName?, phone?, unitId?, roleIds?, status? }` | `{ updated: true, user }` | `422 INVALID_ROLE_ASSIGNMENT` |
| `/admin/users/{userId}/lock` | `PATCH` | `{ locked: true|false }` | `{ locked }` | `409 CANNOT_LOCK_SELF` |

## 4) Master Data

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/master-data/{domain}` | `GET` | domain: `countries, delegation-types, priorities, event-types, statuses...` | `{ items[] }` | `404 DOMAIN_NOT_SUPPORTED` |
| `/master-data/{domain}` | `POST` | `{ code, nameVi, nameEn?, sortOrder?, isActive? }` | `{ id, ... }` | `409 CODE_EXISTS` |
| `/master-data/{domain}/{id}` | `PATCH` | `{ nameVi?, isActive?, sortOrder? }` | `{ updated: true }` | `404 ITEM_NOT_FOUND` |
| `/master-data/{domain}/{id}` | `DELETE` | path | `204` | `409 ITEM_IN_USE` |

## 5) Delegations

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/delegations` | `GET` | query: `status, direction, countryId, ownerId, fromDate, toDate, keyword, page, pageSize` | `{ items[], meta }` | `400 INVALID_DATE_RANGE` |
| `/delegations` | `POST` | `{ code?, name, direction, priority, countryId, hostUnitId, ownerUserId, startDate, endDate, objective, description }` | `{ id, ... }` | `422 VALIDATION_ERROR`, `409 CODE_EXISTS` |
| `/delegations/{id}` | `GET` | path | `{ delegation, members[], contacts[], tags[], outcome }` | `404 DELEGATION_NOT_FOUND` |
| `/delegations/{id}` | `PATCH` | editable fields | `{ updated: true, delegation }` | `409 STATUS_TRANSITION_INVALID` |
| `/delegations/{id}/submit-approval` | `POST` | `{ note? }` | `{ approvalRequestId, status }` | `409 ALREADY_SUBMITTED` |

### Delegation Sub-resources

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/delegations/{id}/members` | `POST` | `{ fullName, title?, organizationName?, contactEmail?, contactPhone?, memberType }` | `{ id, ... }` | `422 VALIDATION_ERROR` |
| `/delegations/{id}/members/{memberId}` | `PATCH` | `{ ... }` | `{ updated: true }` | `404 MEMBER_NOT_FOUND` |
| `/delegations/{id}/checklists` | `POST` | `{ itemName, assigneeUserId?, dueDate?, status, priority }` | `{ id, ... }` | `422 INVALID_ASSIGNEE` |
| `/delegations/{id}/outcome` | `PUT` | `{ progressPercent, summary, nextSteps }` | `{ id, progressPercent }` | `422 PROGRESS_OUT_OF_RANGE` |

## 6) Schedule / Events

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/events` | `GET` | query: `from, to, delegationId?, organizerId?, joinedOnly?, page, pageSize` | `{ items[], meta }` | `400 INVALID_DATE_RANGE` |
| `/events` | `POST` | `{ delegationId?, title, eventType, status, startAt, endAt, locationId?, organizerUserId, participantUserIds[] }` | `{ id, ... }` | `422 END_BEFORE_START` |
| `/events/{id}` | `PATCH` | `{ title?, startAt?, endAt?, status?, locationId? }` | `{ updated: true }` | `409 EVENT_OVERLAP` |
| `/events/{id}/join` | `POST` | `{ joined: true|false }` | `{ participationStatus }` | `409 PARTICIPATION_STATE_INVALID` |
| `/events/{id}/reschedule-requests` | `POST` | `{ proposedStartAt, proposedEndAt, reason }` | `{ id, status }` | `422 INVALID_TIME_RANGE` |

## 7) Minutes

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/minutes` | `GET` | query: `delegationId?, status?, keyword?, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |
| `/minutes` | `POST` | `{ delegationId, eventId?, title, content? }` | `{ id, currentVersionNo }` | `422 VALIDATION_ERROR` |
| `/minutes/{id}` | `GET` | path | `{ minutes, versions[], comments[], approvals[] }` | `404 MINUTES_NOT_FOUND` |
| `/minutes/{id}/versions` | `POST` | `{ contentText?, contentJson?, changeSummary }` | `{ versionNo, editedAt }` | `422 CONTENT_REQUIRED` |
| `/minutes/{id}/comments` | `POST` | `{ versionId?, commentText, parentCommentId? }` | `{ id, ... }` | `404 VERSION_NOT_FOUND` |
| `/minutes/{id}/approve` | `POST` | `{ decision, decisionNote? }` | `{ approved: true, status }` | `409 ALREADY_FINAL` |

## 8) Tasks

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/tasks` | `GET` | query: `status, priority, assigneeId, delegationId, overdue, keyword, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |
| `/tasks` | `POST` | `{ title, description?, delegationId?, eventId?, minutesId?, priority, dueAt?, assigneeUserIds[] }` | `{ id, ... }` | `422 INVALID_CONTEXT` |
| `/tasks/{id}` | `PATCH` | `{ title?, description?, priority?, dueAt? }` | `{ updated: true }` | `404 TASK_NOT_FOUND` |
| `/tasks/{id}/status` | `PATCH` | `{ status, reason? }` | `{ status, changedAt }` | `409 STATUS_TRANSITION_INVALID` |
| `/tasks/{id}/assignees` | `PUT` | `{ assigneeUserIds[] }` | `{ assignees[] }` | `422 INVALID_ASSIGNEE` |

## 9) Documents / Files

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/folders` | `GET` | query: `parentId?, scopeType?` | `{ items[] }` | `400 INVALID_SCOPE` |
| `/folders` | `POST` | `{ parentFolderId?, folderName, scopeType }` | `{ id, ... }` | `409 FOLDER_DUPLICATE` |
| `/files/upload` | `POST` | multipart: file + context(`delegationId/minutesId/taskId/folderId`) | `{ id, fileName, sizeBytes, storageKey }` | `413 FILE_TOO_LARGE`, `415 UNSUPPORTED_MEDIA_TYPE` |
| `/files/{id}` | `GET` | path | `{ file, versions[], shares[] }` | `404 FILE_NOT_FOUND` |
| `/files/{id}` | `PATCH` | `{ fileName }` | `{ updated: true, file }` | `404 FILE_NOT_FOUND` |
| `/files/{id}/share` | `POST` | `{ sharedWithUserId?, sharedWithRoleId?, permissionLevel, expiresAt? }` | `{ shareId }` | `422 SHARE_TARGET_REQUIRED` |
| `/files/{id}/download-url` | `POST` | `{}` | `{ url, expiresAt }` | `403 NO_FILE_ACCESS` |

## 10) Teams

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/teams` | `GET` | query: `unitId?` | `{ members[], activities[], summary }` | `400 INVALID_FILTER` |
| `/teams/members` | `POST` | `{ fullName?, email?, username?, phone?, positionTitle?, unitId? }` | `{ id, name, role, email, status, tasks, performance }` | `422 VALIDATION_ERROR` |

## 11) Partner CRM

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/partners` | `GET` | query: `status, countryId, sectorId, keyword, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |
| `/partners` | `POST` | `{ partnerCode?, partnerName, countryId, sectorId, status, score?, website?, notes? }` | `{ id, ... }` | `409 PARTNER_CODE_EXISTS` |
| `/partners/{id}` | `PATCH` | `{ ... }` | `{ updated: true }` | `404 PARTNER_NOT_FOUND` |
| `/partners/{id}/contacts` | `POST` | `{ fullName, title?, email?, phone?, isPrimary? }` | `{ id, ... }` | `422 VALIDATION_ERROR` |
| `/partners/{id}/interactions` | `POST` | `{ interactionType, interactionAt, summary }` | `{ id }` | `422 INVALID_INTERACTION_TYPE` |

## 12) Approvals

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/approvals` | `GET` | query: `status, type, requesterId?, approverId?, fromDate?, toDate?, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |
| `/approvals/{id}` | `GET` | path | `{ request, steps[], history[] }` | `404 APPROVAL_NOT_FOUND` |
| `/approvals/{id}/decision` | `POST` | `{ decision: APPROVE|REJECT, decisionNote? }` | `{ status, decidedAt }` | `409 STEP_NOT_ACTIVE`, `403 NOT_ASSIGNED_APPROVER` |

## 13) Notifications

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/notifications` | `GET` | query: `unreadOnly?, type?, page, pageSize` | `{ items[], meta, unreadCount }` | `400 INVALID_FILTER` |
| `/notifications/{id}/read` | `PATCH` | `{ read: true }` | `{ readAt }` | `404 NOTIFICATION_NOT_FOUND` |
| `/notifications/read-all` | `PATCH` | `{}` | `{ updatedCount }` | `401 UNAUTHORIZED` |
| `/notifications/read` | `DELETE` | `{}` | `{ deletedCount }` | `409 NOTHING_TO_DELETE` |

## 14) Reports + Pipeline

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/reports/definitions` | `GET` | query: `scopeType?` | `{ items[] }` | `403 FORBIDDEN` |
| `/reports/runs` | `POST` | `{ reportCode, params }` | `{ runId, status }` | `404 REPORT_CODE_NOT_FOUND` |
| `/reports/runs/{runId}` | `GET` | path | `{ status, startedAt, finishedAt?, outputFileId?, errorMessage? }` | `404 RUN_NOT_FOUND` |
| `/pipeline/projects` | `GET` | query: `stageId?, countryId?, sectorId?, ownerUserId?, page, pageSize` | `{ items[], meta }` | `400 INVALID_FILTER` |
| `/pipeline/projects` | `POST` | `{ projectCode?, projectName, partnerId?, countryId, sectorId, stageId, estimatedValue?, successProbability?, expectedCloseDate?, ownerUserId }` | `{ id, ... }` | `422 PROBABILITY_OUT_OF_RANGE` |
| `/pipeline/projects/{id}/stage` | `PATCH` | `{ newStageId, reason? }` | `{ stageId, changedAt }` | `409 STAGE_TRANSITION_INVALID` |

## 15) System Settings + Integrations + Audit (Admin)

| Endpoint | Method | Request | Response | Error case |
|---|---|---|---|---|
| `/admin/system-settings` | `GET` | query: `group?` | `{ items[] }` | `403 FORBIDDEN` |
| `/admin/system-settings` | `PATCH` | `{ items: [{ key, value }] }` | `{ updatedCount }` | `422 SETTING_INVALID` |
| `/admin/integrations/{provider}/test` | `POST` | `{ overrideConfig? }` | `{ status, latencyMs, message }` | `502 INTEGRATION_UNREACHABLE` |
| `/admin/audit-logs` | `GET` | query: `actorUserId?, action?, resourceType?, fromDate?, toDate?, page, pageSize` | `{ items[], meta }` | `400 INVALID_DATE_RANGE` |
| `/admin/audit-logs/export` | `POST` | `{ filters }` | `{ fileId, downloadUrl }` | `413 EXPORT_TOO_LARGE` |

## Error Code Set Khuyen Nghi (Toi Thieu)

- `AUTH`: `INVALID_CREDENTIALS`, `TOKEN_EXPIRED`, `SESSION_REVOKED`
- `VALIDATION`: `VALIDATION_ERROR`, `INVALID_DATE_RANGE`, `INVALID_FILTER`
- `BUSINESS`: `STATUS_TRANSITION_INVALID`, `STAGE_TRANSITION_INVALID`, `ITEM_IN_USE`
- `RESOURCE`: `NOT_FOUND_*`, `ALREADY_EXISTS_*`
- `PERMISSION`: `FORBIDDEN`, `NOT_ASSIGNED_APPROVER`, `NO_FILE_ACCESS`
- `SYSTEM`: `RATE_LIMITED`, `INTERNAL_ERROR`, `INTEGRATION_UNREACHABLE`
