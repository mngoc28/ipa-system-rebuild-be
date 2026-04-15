# API Business Rules v1

Tai lieu nay tong hop business rules cho API v1, ap dung theo schema hien tai va luong UI da chot.

## Nguyen Tac Ap Business Rules Chung

- Validation chay theo 3 lop: payload schema, business invariant, authorization scope.
- Reject uu tien tra ma ro nghia:
  - `400` cho format sai
  - `422` cho nghiep vu sai
  - `409` cho xung dot trang thai
  - `403` cho thieu quyen
- Side effects phai transactional: ghi audit log, domain event, notification sau khi commit thanh cong.
- Moi API cap nhat du lieu deu check optimistic lock qua `row_version` hoac `updated_at` de tranh ghi de.
- Moi action quan trong deu ghi `ipa_audit_log` va `traceId` de truy vet.

## 1) Auth APIs

### POST /auth/login

- Validation: `usernameOrEmail` bat buoc, `password` bat buoc, dinh dang email neu nhap email.
- Reject: account khong ton tai, sai mat khau, tai khoan locked, tai khoan inactive, qua so lan thu.
- Side effects: tao `ipa_auth_session`, cap nhat `last_login_at`, ghi `ipa_login_attempt` success/fail, phat notification bao mat neu login bat thuong.

### POST /auth/refresh

- Validation: `refreshToken` bat buoc, dung format, chua het han.
- Reject: token revoked, token khong khop session, user da bi khoa.
- Side effects: rotate access token, co the rotate refresh token, update session expiry, ghi audit refresh.

### POST /auth/logout

- Validation: `refreshToken` hoac session id hop le.
- Reject: session khong ton tai hoac da revoked.
- Side effects: revoke session hien tai, invalidate refresh token, ghi audit logout.

### POST /auth/change-password-first-time

- Validation: `oldPassword` dung, `newPassword` dung policy, `confirm` trung.
- Reject: old password sai, password reuse gan day, khong dat policy.
- Side effects: update hash, insert `ipa_password_history`, revoke toan bo session cu, buoc login lai neu policy yeu cau.

## 2) Delegation APIs

### GET /delegations

- Validation: `fromDate <= toDate`, `page/pageSize` hop le.
- Reject: filter status/type khong thuoc danh muc.
- Side effects: khong doi du lieu; ghi access log nhe neu can.

### POST /delegations

- Validation: `name, direction, countryId, hostUnitId, ownerUserId, startDate, endDate` bat buoc; `endDate >= startDate`.
- Reject: country/unit/user khong ton tai, code trung, owner khong thuoc don vi hop le.
- Side effects: tao delegation, tao trang thai khoi tao `DRAFT`, ghi audit create, push notification cho owner.

### PATCH /delegations/{id}

- Validation: field cho phep update theo trang thai hien tai.
- Reject: delegation khong ton tai, trang thai khong cho phep sua, transition khong hop le.
- Side effects: update ban ghi, ghi history trang thai neu co doi, ghi audit before/after.

### POST /delegations/{id}/submit-approval

- Validation: delegation phai du du lieu toi thieu de trinh duyet.
- Reject: da co approval pending, delegation dang `cancelled/completed`.
- Side effects: tao `ipa_approval_request` + steps, doi status sang `PENDING_APPROVAL`, gui notification approver.

### POST /delegations/{id}/members

- Validation: `fullName` bat buoc; `email/phone` dung format neu co.
- Reject: delegation khong ton tai, delegation locked boi workflow.
- Side effects: insert member, update `participant_count`, ghi audit.

### PUT /delegations/{id}/outcome

- Validation: `progressPercent` trong `[0..100]`.
- Reject: delegation chua `approved/in_progress` nhung co ghi outcome final.
- Side effects: upsert outcome, neu `progress=100` thi goi y chuyen `COMPLETED`, phat event `report.updated`.

## 3) Event/Schedule APIs

### POST /events

- Validation: `startAt < endAt`, `eventType` hop le, organizer hop le.
- Reject: trung lich cung voi cung organizer/location theo policy, delegation khong o trang thai cho phep.
- Side effects: tao event, tao participant mac dinh, gui invite notification.

### PATCH /events/{id}

- Validation: khong cho sua truong da khoa (vi du event da `DONE`).
- Reject: overlap lich sau khi sua, trang thai chuyen sai.
- Side effects: update event, ghi reschedule log neu doi thoi gian, gui thong bao cap nhat cho participant.

### POST /events/{id}/join

- Validation: user phai thuoc pham vi duoc moi hoac co quyen tu tham gia.
- Reject: event `cancelled/done`, user bi chan tham gia.
- Side effects: cap nhat `participation_status`, ghi event participant history.

### POST /events/{id}/reschedule-requests

- Validation: `proposedStart < proposedEnd`, `reason` bat buoc.
- Reject: event da ket thuc, da co request pending cua cung user.
- Side effects: tao reschedule request, notify organizer/manager.

## 4) Minutes APIs

### POST /minutes

- Validation: `title, delegationId` bat buoc; `eventId` phai thuoc delegation neu co.
- Reject: delegation khong ton tai hoac khong du quyen truy cap.
- Side effects: tao minutes + version 1, audit create.

### POST /minutes/{id}/versions

- Validation: phai co `contentText` hoac `contentJson`.
- Reject: minutes da `FINAL` va user khong co quyen override.
- Side effects: tang `current_version_no`, insert version, notify watchers.

### POST /minutes/{id}/comments

- Validation: `commentText` khong rong.
- Reject: `versionId` khong thuoc minutes, `parentComment` khong cung minutes.
- Side effects: insert comment, notify owner/mentioned users.

### POST /minutes/{id}/approve

- Validation: `decision` thuoc `APPROVE/REJECT`.
- Reject: user khong phai approver, da co quyet dinh cuoi.
- Side effects: ghi approval log, doi status minutes, neu `APPROVE` du quorum thi set `FINAL`.

## 5) Task APIs

### POST /tasks

- Validation: `title` bat buoc; it nhat mot context `delegation/event/minutes` hoac task global theo policy.
- Reject: `dueAt < now` voi policy khong cho, assignee khong hop le.
- Side effects: tao task + assignees + notify assignees.

### PATCH /tasks/{id}/status

- Validation: status moi thuoc danh muc, transition hop le.
- Reject: tu `DONE` sang `DOING` neu policy cam reopen, task da `cancelled`.
- Side effects: insert `ipa_task_status_history`, cap nhat overdue flag, co the trigger completion metrics.

### PUT /tasks/{id}/assignees

- Validation: danh sach user ton tai, khong trung.
- Reject: task locked hoac user ngoai scope don vi.
- Side effects: sync mapping, notify user moi duoc gan, revoke access user bi go neu can.

### POST /tasks/{id}/comments

- Validation: `commentText` bat buoc.
- Reject: task khong ton tai hoac user khong co quyen xem.
- Side effects: insert comment, notify followers.

## 6) File/Document APIs

### POST /files/upload

- Validation: mime type, size, context hop le.
- Reject: vuot quota, context khong ton tai, extension cam.
- Side effects: luu object storage, insert metadata file, scan virus async, audit upload.

### POST /files/{id}/share

- Validation: phai co `sharedWithUserId` hoac `sharedWithRoleId`; `permissionLevel` hop le.
- Reject: nguoi share khong co quyen chia se, target khong ton tai.
- Side effects: insert share rule, notify recipient, ghi file_access_log action share.

### POST /files/{id}/download-url

- Validation: quyen doc file.
- Reject: file deleted, share het han, permission khong du.
- Side effects: tao signed URL ngan han, ghi access log download intent.

### PATCH /files/{id}

- Validation: `fileName` khong rong, ky tu cam.
- Reject: trung ten trong cung folder neu policy unique.
- Side effects: update metadata, audit rename.

## 7) Partner APIs

### POST /partners

- Validation: `partnerName, countryId, sectorId` bat buoc; `score` trong `[0..5]` neu co.
- Reject: `partner_code` trung, country/sector khong active.
- Side effects: tao partner, audit create, co the tao owner mac dinh.

### PATCH /partners/{id}

- Validation: trang thai moi hop le theo flow.
- Reject: `ACTIVE -> LEAD` neu policy cam quay nguoc.
- Side effects: update partner, neu score doi thi insert `partner_score_history`.

### POST /partners/{id}/interactions

- Validation: `interactionType, interactionAt` bat buoc.
- Reject: `interactionAt` qua xa tuong lai theo policy.
- Side effects: insert interaction, cap nhat `last_interaction_at` cache.

### POST /partners/{id}/contacts

- Validation: `fullName` bat buoc; email format.
- Reject: set `isPrimary=true` khi da co primary ma policy khong cho auto-demote.
- Side effects: insert contact, auto clear primary cu neu policy cho phep.

## 8) Approval APIs

### GET /approvals

- Validation: filter hop le.
- Reject: user khong co scope duyet tuong ung.
- Side effects: none.

### POST /approvals/{id}/decision

- Validation: decision hop le, optional note theo rule reject bat buoc note.
- Reject: khong phai approver step hien tai, request da closed.
- Side effects: update step decision, advance `current_step` hoac close request, cap nhat entity goc, gui notification requester.

### POST /approvals/{id}/recall (neu co)

- Validation: chi requester duoc recall.
- Reject: da co quyet dinh cuoi hoac da qua step khong cho recall.
- Side effects: close request trang thai `CANCELLED`, rollback entity status theo policy.

## 9) Notification APIs

### GET /notifications

- Validation: `page/pageSize` hop le.
- Reject: none dac biet.
- Side effects: none.

### PATCH /notifications/{id}/read

- Validation: notification thuoc user.
- Reject: cross-user access.
- Side effects: set `read_at`, co the giam unread counter cache.

### PATCH /notifications/read-all

- Validation: user authenticated.
- Reject: none.
- Side effects: bulk update `read_at`, publish counter update event.

### DELETE /notifications/read

- Validation: chi xoa read items.
- Reject: khi policy retention cam xoa cung.
- Side effects: soft delete hoac archive theo policy.

## 10) Admin Settings + Integration + Audit

### PATCH /admin/system-settings

- Validation: key ton tai, value dung kieu tung key.
- Reject: sua key secret khi khong co quyen `security-admin`.
- Side effects: encrypt secret fields, write audit, invalidate config cache.

### POST /admin/integrations/{provider}/test

- Validation: provider hop le.
- Reject: config thieu bat buoc.
- Side effects: call external ping, insert `ipa_integration_health_log`.

### GET /admin/audit-logs

- Validation: date range va paging hop le.
- Reject: thieu quyen `admin/auditor`.
- Side effects: none.

### POST /admin/audit-logs/export

- Validation: bo loc hop le, gioi han range.
- Reject: range qua lon vuot policy.
- Side effects: tao report run + file export + notification khi xong.

## 11) Reports + Pipeline

### POST /reports/runs

- Validation: `reportCode` ton tai, `params` dung schema bao cao.
- Reject: report disabled, khong du quyen scope.
- Side effects: tao `report_run RUNNING`, enqueue job, ghi audit.

### GET /reports/runs/{runId}

- Validation: run thuoc tenant/scope user.
- Reject: access cross-scope.
- Side effects: none.

### POST /pipeline/projects

- Validation: `stageId` hop le, `successProbability [0..100]`.
- Reject: `project_code` trung, partner/country/sector khong active.
- Side effects: create project, insert `stage_history` initial.

### PATCH /pipeline/projects/{id}/stage

- Validation: `newStage` khac `oldStage`.
- Reject: transition sai thu tu theo matrix nghiep vu.
- Side effects: update stage, insert `stage_history`, cap nhat KPI snapshot async.

## Rule Matrix Khuyen Nghi Bat Buoc Trien Khai

- Status transition matrix cho delegation, task, minutes, approval, pipeline.
- Permission matrix theo role + unit scope cho tung endpoint.
- Data ownership rule: user chi thay/sua du lieu thuoc unit duoc phan quyen.
- Idempotency key cho cac API tao moi nhay cam: approvals, upload metadata, report runs.
- Retry-safe side effects: notification va outbox chi gui sau commit, co deduplicate key.
