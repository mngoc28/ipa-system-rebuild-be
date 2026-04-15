# UI to API Mapping (v1)

Duoi day la mapping action theo UI hien tai sang API contract da thiet ke.

## Muc tieu

- Xac dinh button/event tren UI goi API nao.
- Mo ta flow: user -> API -> response -> UI update.
- Dong bo implementation giua FE, BE, QA.

## A. Auth

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Click Dang nhap | `POST /auth/login` | `usernameOrEmail, password` | `accessToken, refreshToken, user` | Luu session, chuyen Dashboard theo role |
| App load sau login | `GET /auth/me` | Bearer token | `user, roles, permissions` | Render menu theo quyen |
| Token sap het han | `POST /auth/refresh` | `refreshToken` | `accessToken` moi | Silent refresh, khong reload trang |
| Click Dang xuat | `POST /auth/logout` | `refreshToken` | `204` | Clear store, ve man login |
| Doi mat khau lan dau | `POST /auth/change-password-first-time` | `oldPassword, newPassword` | `changed=true` | Toast thanh cong, ve dashboard |

### Flow chuan

1. User click button.
2. UI validate client-side.
3. Goi API.
4. Neu `2xx`: cap nhat local state + toast + dieu huong.
5. Neu `4xx/5xx`: show error banner/toast, giu nguyen form.

## B. Delegation (List, Form, Detail)

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo trang danh sach doan | `GET /delegations` | `filter, page, pageSize` | `items, meta` | Render bang/Kanban + pagination |
| Search theo ten/ma/quoc gia | `GET /delegations` | `keyword + filter` | `items` | Update list realtime |
| Loc trang thai/quick filter | `GET /delegations` | `status, direction, date range` | `items` | Refetch va reset `page=1` |
| Click Tao doan moi | `POST /delegations` | `name, direction, countryId...` | `id, delegation` | Toast + dieu huong detail/edit |
| Click Luu ho so doan | `PATCH /delegations/{id}` | field thay doi | `updated=true, delegation` | Cap nhat form state, badge status |
| Them thanh vien doan | `POST /delegations/{id}/members` | `fullName, role...` | `member id` | Append row thanh vien |
| Sua thanh vien | `PATCH /delegations/{id}/members/{memberId}` | field thay doi | `updated=true` | Update row tai cho |
| Them dau viec checklist | `POST /delegations/{id}/checklists` | `itemName, assignee, dueDate` | `checklist item` | Append checklist |
| Cap nhat outcome | `PUT /delegations/{id}/outcome` | `progressPercent, summary` | `outcome` | Update card Ket qua |
| Submit phe duyet doan | `POST /delegations/{id}/submit-approval` | `note` | `approvalRequestId` | Doi trang thai sang pending approval |

### Flow chinh

- User thao tac form/list.
- API tra `delegation object` moi nhat.
- UI dong bo state theo object tra ve de tranh lech optimistic state.

## C. Schedule / Event

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo trang lich | `GET /events` | `from, to, joinedOnly` | `items` | Render timeline/calendar |
| Click Them lich moi | `POST /events` | `title, type, startAt, endAt` | `event id` | Chen event vao list |
| Join/Huy tham gia event | `POST /events/{id}/join` | `joined=true/false` | `participationStatus` | Toggle nut THAM GIA |
| De xuat doi lich | `POST /events/{id}/reschedule-requests` | `proposedStartAt, reason` | `request id` | Toast + badge cho duyet |
| Sua event | `PATCH /events/{id}` | `title/time/location/status` | `updated event` | Re-render item lich |

## D. Minutes

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo danh sach bien ban | `GET /minutes` | `delegationId, status` | `items` | Render list |
| Tao bien ban moi | `POST /minutes` | `delegationId, eventId, title` | `id, currentVersionNo` | Dieu huong detail |
| Bat chinh sua + luu version | `POST /minutes/{id}/versions` | `content, changeSummary` | `versionNo` | Update version badge/history |
| Gui comment | `POST /minutes/{id}/comments` | `commentText, versionId` | `comment id` | Append thread comment |
| Phe duyet bien ban | `POST /minutes/{id}/approve` | `decision, note` | `status` | Doi trang thai `draft/internal/final` |

## E. Tasks

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo trang task | `GET /tasks` | `status, priority, assignee` | `items` | Render board/list |
| Tao task nhanh trong modal | `POST /tasks` | `title, priority, dueAt` | `task id` | Add card vao cot TODO |
| Click doi trang thai | `PATCH /tasks/{id}/status` | `status` moi | `status, changedAt` | Move card giua cot |
| Sua task detail | `PATCH /tasks/{id}` | `title/dueAt/priority` | `updated task` | Update card |
| Gan nguoi xu ly | `PUT /tasks/{id}/assignees` | `userIds` | `assignees` | Update assignee avatar/list |

## F. Documents / Files

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo thu vien tai lieu | `GET /folders` + `GET /files` | `folderId/filter` | `items` | Render thu muc + file |
| Tao thu muc | `POST /folders` | `folderName, parentId` | `folder id` | Append folder card |
| Upload tai lieu | `POST /files/upload` | multipart file + context | `file id, info` | Add file vao grid/list |
| Chia se file | `POST /files/{id}/share` | target user/role, permission | `shareId` | Badge Shared cap nhat |
| Tai file | `POST /files/{id}/download-url` | none | signed URL | Browser download |
| Doi ten / metadata | `PATCH /files/{id}` | `fileName` | `updated` | Update row/file card |

## G. Partners (CRM)

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo danh sach doi tac | `GET /partners` | `status, country, keyword` | `items` | Render cards |
| Them doi tac moi | `POST /partners` | `partnerName, countryId...` | `id, partner` | Add card dau danh sach |
| Nang trang thai doi tac | `PATCH /partners/{id}` | `status` moi | `updated partner` | Update badge `Lead/Partner/Active` |
| Gui mail nhanh (log tuong tac) | `POST /partners/{id}/interactions` | `type=email, summary` | `interaction id` | Toast + danh dau emailed |
| Xem profile doi tac | `GET /partners/{id}` | none | detail + contacts | Open dialog detail |

## H. Notifications

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo trang thong bao | `GET /notifications` | `unreadOnly/type/page` | `items, unreadCount` | Render tabs + badge |
| Danh dau da doc 1 item | `PATCH /notifications/{id}/read` | `read=true` | `readAt` | Item chuyen read style |
| Danh dau doc tat ca | `PATCH /notifications/read-all` | none | `updatedCount` | Reset unread badge |
| Xoa thong bao da doc | `DELETE /notifications/read` | none | `deletedCount` | Remove read items khoi list |

## I. Approvals (Manager)

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo hang doi duyet | `GET /approvals` | `status=pending/approved` | `items` | Render queue |
| Click Phe duyet | `POST /approvals/{id}/decision` | `decision=APPROVE` | `status` | Item chuyen sang tab Da duyet |
| Click Tu choi | `POST /approvals/{id}/decision` | `decision=REJECT` | `status` | Item bien mat khoi pending |
| Mo chi tiet duyet | `GET /approvals/{id}` | none | `request + steps + history` | Open detail panel/modal |

## J. Admin Portal

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| User Management load/search/filter | `GET /admin/users` | `keyword/status/page` | `items, meta` | Update table + stats |
| Them user | `POST /admin/users` | profile + roleIds | `user id` | Add row + stats |
| Khoa/Mo khoa user | `PATCH /admin/users/{id}/lock` | `locked bool` | `locked state` | Update status badge |
| Sua user | `PATCH /admin/users/{id}` | fields | `updated user` | Update row |
| Master-data load | `GET /master-data/{domain}` | domain | items | Render tab data |
| Master-data add/edit/delete | `POST/PATCH/DELETE /master-data/{domain}` | payload | item/204 | Update row |
| System settings load/save | `GET/PATCH /admin/system-settings` | group / items[] | values, updatedCount | Refill form + saved timestamp |
| Test integration | `POST /admin/integrations/{provider}/test` | overrideConfig | status, latency | Show health badge |
| Audit log load/filter | `GET /admin/audit-logs` | `actor/action/date/page` | `items, meta` | Render timeline/table |
| Export audit CSV | `POST /admin/audit-logs/export` | filters | `fileId, url` | Trigger download |

## K. Reports + Pipeline (Director/Manager)

| UI Action | API | Request chinh | Response chinh | UI update |
|---|---|---|---|---|
| Mo bao cao | `GET /reports/definitions` | scope | items | Render list |
| Click Xuat bao cao | `POST /reports/runs` | `reportCode, params` | `runId` | Show running state |
| Poll trang thai run | `GET /reports/runs/{runId}` | none | `status/outputFileId` | Khi success: enable download |
| Mo pipeline | `GET /pipeline/projects` | `stage/country/sector/page` | items | Render funnel/list |
| Tao co hoi pipeline | `POST /pipeline/projects` | project payload | id | Add row/card |
| Doi stage pipeline | `PATCH /pipeline/projects/{id}/stage` | `newStageId` | `stageId, changedAt` | Move stage + update KPI |

## Mau Flow End-to-End Ngan Gon (Ap cho moi action)

1. User action (click/submit/toggle).
2. UI validate + disable button loading.
3. Call API tuong ung.
4. Neu thanh cong: normalize data tu response, cap nhat store/component state, toast success.
5. Neu loi business `409/422`: hien thi message theo `error.code` tai dung field hoac row.
6. Neu loi `401`: refresh token 1 lan, fail nua thi logout.
7. Neu loi `5xx`: toast retry + giu state hien tai.
