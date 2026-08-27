# DTR System Functionality, Role, and Attendance Policy Summary

## 1. Purpose and interpretation

This document summarizes the behavior currently implemented in the DTR application and proposes a consolidated policy baseline for rules that are incomplete, inconsistent, or absent.

The review is based on the repository as of August 27, 2026. It is a technical description of the software, not an approved HR, Civil Service, payroll, or legal policy. HR and management should approve the proposed rules before they are treated as official policy.

Status labels used below:

- **Implemented** — the workflow and material calculation exist in active code.
- **Partial** — some UI, data, or calculation support exists, but the end-to-end policy is incomplete or inconsistent.
- **Not implemented** — no distinct data model or calculation exists for the requested rule.
- **Proposed policy** — recommended behavior to be approved and implemented.

## 2. Functional overview

The application provides:

- Web authentication, password reset, forced replacement of the known default password, and logout.
- Employee home, daily time-entry history, personal DTR preview/PDF, profile photo, electronic signature, and QR code.
- Leave and flexible-schedule requests with list and calendar views.
- Work-from-home time capture when an approved WFH event exists for the current date.
- QR/scanner attendance capture for Pasig and DAPCC, plus mobile-pool capture with reverse-geocoded location data.
- Admin Coordinator management of official schedules, event evaluation, daily attendance, and individual/bulk DTR reports.
- HR employee master list, DTR generation, event calendar, time-entry monitoring, and official-time change evaluation.
- Separate DAPCC calendars, shifts, day-offs, attendance processing, and weekly Jobber reporting controls.
- Superadmin user, employee, role, and permission administration through Filament, plus access to all authorized application modules.
- Email notifications for applications, evaluations, and official-time changes.
- Optional MOV metadata and private file handling for supported employee events.
- Activity logging on users, employees, events, and time entries.

## 3. Role-based functionality and access

### 3.1 Role matrix

| Role | Effective organizational scope | Main functions |
|---|---|---|
| Employee | Self | View own time entries and DTR; maintain identity photo, signature, and QR code; submit WFH, Hybrid WFH, OB, CDO, and eligible leave requests; edit or remove pending requests; use WFH capture when eligible. |
| Admin Coordinator | Own department only | Employee functions plus official-time management, department event calendar, request approval/disapproval, daily attendance, employee time-entry review, individual DTR, and department bulk DTR. |
| Center Admin Coordinator | All departments with the same `center` | Same coordinator functions across the center. Department fields are exposed where needed for selection and reporting. |
| Group Admin Coordinator | All departments with the same `group` | Same coordinator functions across the group. Department fields are exposed where needed for selection and reporting. |
| HR Administrator | Same center class: DAPCC or non-DAPCC | Employee master list, employee creation/update/status, reports, calendar, time entries, and official-time request evaluation. Pasig HR modules additionally depend on `hr-admin-rsp-view` or `hr-admin-view-any`. |
| Timekeeper | No dedicated web module is defined | Receives the common employee web modules. Scanner/mobile access is not granted by this role alone; mobile login currently depends on GSD office, specific emails, or superadmin. |
| Superadmin | All employees, departments, roles, permissions, routes, menus, policies, and MOVs | Full Filament user administration; all Pasig and DAPCC coordinator/HR menus; global authorization override. Authentication and password-security controls still apply. |

### 3.2 Authorization hierarchy

The application recognizes this organizational hierarchy:

```text
Group
└── Center
    └── Department / Office
        └── Employee
```

Employee, Department, DTR, QR-code, and MOV policies enforce this hierarchy. HR access compares DAPCC versus non-DAPCC rather than granting unrestricted national access. Superadmin is the only role with an application-wide policy override.

### 3.3 Center separation

- **Pasig routes** accept users whose department center is not `DAPCC`.
- **DAPCC routes** require a `DAPCC` department.
- HR and coordinator employee/DTR policies apply the same separation.
- Superadmin can enter both route and menu groups.
- The `view-dapcc` and `view-pasig` gates are also used as data-scope selectors. They intentionally retain center-filtering semantics even when superadmin has general authorization.

## 4. Employment and appointment policy

### 4.1 Plantilla-Based Position (PBP) — Implemented

- Stored as `pbp` and labeled “Plantilla based Position.”
- Standard DTR cutoffs are:
  - First cutoff: day 1 through day 15.
  - Second cutoff: day 16 through the last day of the month.
- Employees classified as PBP can select Official Leave in the employee request interface.
- PBP reports use the Pasig or DAPCC calculation engine according to center.

### 4.2 Non-Plantilla Position (NPP) — Partial

- Stored as `npp` and labeled “Non-plantilla Position.”
- DTR cutoffs are:
  - First cutoff: day 26 of the previous month through day 10 of the selected month.
  - Second cutoff: day 11 through day 25 of the selected month.
- The employee request UI does not offer Official Leave to NPP employees. OB, CDO, WFH, and Hybrid WFH remain selectable.
- There is no NPP-specific time-in/time-out calculation; NPP uses the same fixed/full-flexitime calculation as PBP.
- Previous-month calculation is performed with manual month arithmetic and should be regression-tested at January/year boundaries.

### 4.3 Jobber — Partial and DAPCC-oriented

- `jobber` exists in the `AppointmentStatus` enum and appears in DAPCC employee/report forms.
- Jobber reports select a Sunday as week start and cover seven days.
- Month and cutoff fields are hidden for Jobber bulk/individual DAPCC reporting.
- DAPCC supports employee duty shifts and day-off event types.
- The original `employees.appointment_status` database migration only declares `pbp` and `npp`. Therefore, Jobber enum/UI support and the database schema are inconsistent on databases that enforce enum values.
- No separate Jobber wage, overtime, rest-day premium, night differential, or payroll computation is implemented.
- No employee-facing Jobber policy limits leave or flexible-schedule options by appointment status beyond the PBP-only Official Leave condition.

### 4.4 Co-terminus / Co-term — Not implemented

- No co-terminus appointment status, field, enum, report cutoff, schedule rule, or leave rule exists.
- A co-terminus employee would have to be stored as PBP, NPP, or Jobber, losing the distinction.
- No rule determines whether co-terminus staff follow PBP cutoffs, NPP cutoffs, fixed schedules, or full flexitime.

### 4.5 Active and inactive employment

- `employment_status` is a boolean.
- A global employee scope hides inactive employees from normal queries.
- HR can change employment status in the employee master list.
- Reports and coordinator tables generally select active employees, but inactive-record behavior is not centralized into one explicit reporting policy.

## 5. Official schedule and time-in/time-out policy

### 5.1 Attendance sources

Time entries can be created through:

- Pasig QR/scanner capture (`ros`).
- DAPCC QR/scanner capture (`ros`).
- Approved WFH web capture (`wfh`).
- Mobile-pool capture with coordinates and resolved address (`mvpool`).

Repeated capture toggles between opening a time entry and closing the latest open entry. Pasig blocks a duplicate capture within the same minute. DAPCC warns when an employee has no shift but still records attendance.

### 5.2 Official schedule types

| Schedule | Current implementation |
|---|---|
| Fixed Official Time (FOT) | Stores an official start time. The expected end is normally start plus nine elapsed hours, including a one-hour lunch break. |
| Full Flexitime (FFT) | No stored official start is required. Normal latest start is 10:00 AM, with eight net work hours plus one hour lunch. |

Official-time records include an effectivity date and approved/pending/disapproved status. Coordinator-created schedule changes in the active workflow are saved as approved and may include an MOV and email notification.

### 5.3 Fixed Official Time calculations

- Normal tardiness is measured after the employee's official start.
- A 15-minute grace period is used on ordinary non-flag days.
- More than 15 minutes is counted as tardy; less than 15 minutes is marked as graced.
- Exactly 15 minutes is an uncovered boundary in the current conditional logic: it is neither marked graced nor added as tardy.
- Expected end is normally official start plus nine elapsed hours.
- Leaving before expected end creates undertime.
- Missing time-out is treated as 480 minutes in the tardy/not-completed-hours path.
- A lunch break over 60 minutes creates PM tardiness for the excess minutes.

### 5.4 Full Flexitime calculations

- Normal latest start is 10:00 AM.
- Starting after 10:00 AM creates tardiness.
- When starting after 10:00 AM, the code compares time-out against 7:00 PM.
- When starting by 10:00 AM, expected completion is generally time-in plus nine elapsed hours.
- The calculation subtracts 60 minutes for lunch when checking eight net hours.
- On flag-ceremony days, the expected window becomes 8:30 AM to 5:30 PM.
- Missing time-out is treated as 480 minutes in the not-completed-hours path.

### 5.5 Flexing of fixed-schedule tardiness

After initial calculations, selected fixed-schedule tardies may be reclassified as “flexied” when:

- The date is not Monday.
- It is not a flag-enforced tardy.
- The employee completed the required hours.
- Time-in is within one hour after official time-in.

The loop uses `<= 3`, which can allow four reclassifications rather than a clear maximum of three. This requires an HR decision and a code correction.

### 5.6 Breaks, multiple scans, and PM tardiness

- A single completed entry defaults the break to 12:00 PM–1:00 PM.
- With multiple entries, the generator attempts to infer the break from scans between 11:00 AM and 2:00 PM.
- If no break is detected, it defaults to 12:00 PM–1:00 PM.
- Break duration beyond 60 minutes is recorded as PM tardiness.
- Time-in is the first entry of the day. Time-out is derived from the last applicable capture.

## 6. Remote and off-site work policy

### 6.1 Work From Home (WFH) — Implemented with restrictions

- WFH is an employee event with approval status.
- An employee can see and enter the WFH capture module only when an approved WFH event exists for the current date.
- WFH capture opens/closes a time entry tagged `wfh` and carries the employee's current schedule type and official time.
- Employee-created WFH requests begin as pending and must be evaluated by an Admin Coordinator.
- WFH is not selectable on Monday in employee and coordinator request forms.
- Some HR calendar forms allow WFH only on Friday. This conflicts with the employee rule, which allows any non-Monday single date.
- Date-range requests intentionally do not offer WFH.

### 6.2 Hybrid WFH — Partial

- Hybrid WFH (`hwfh`) exists as an event type and DTR remark.
- It follows the same employee request restrictions as WFH.
- The `has-wfh-schedule` gate checks only WFH, not Hybrid WFH. An approved Hybrid WFH event therefore does not unlock the WFH time-capture module.
- Scanner logic that would block onsite capture for WFH is commented out. The system does not currently enforce or differentiate the onsite portion of a hybrid day.
- No rule defines how many onsite versus remote captures are required or how location evidence should be handled.

### 6.3 Official Business (OB) — Implemented as an approved event

- OB can be requested by employees and assigned by authorized staff.
- Employee requests start pending and require coordinator evaluation.
- Approved OB appears as a DTR remark.
- MOV upload/view support exists for employee-specific events, including OB in several event views.
- OB does not automatically create time entries and has no distinct duration, destination, travel-time, or partial-day computation.

### 6.4 Mobile pool / field capture

- Mobile-pool attendance records latitude and longitude.
- Coordinates are reverse-geocoded through Azure Maps and stored with the time entry.
- API tokens use `mvpool:read` and `mvpool:write` abilities.
- This is a capture mechanism, not a formal OB approval substitute.

## 7. Leave policy

### 7.1 Official Leave workflow

- All leave types are stored under the `ala` event tag.
- The selected leave subtype is stored in the event description.
- Employee requests are pending by default.
- Admin Coordinators can approve or disapprove individually or in bulk, optionally adding a disapproval note.
- The employee and coordinator receive email notifications.
- Only approved employee events are included as schedule remarks in the standard Pasig DTR processor.
- Employees can edit or remove only pending requests.
- New employee requests must be at least three calendar days from the current date in the active date controls, although UI wording describes this as “two days after.”
- Existing requests on the same date/range block another request.
- Date ranges create one event per calendar day; weekends and holidays are not automatically excluded.

### 7.2 Leave types represented

| Code | Leave type | Status |
|---|---|---|
| `vl` | Vacation Leave | Implemented as selectable subtype |
| `mfl` | Mandatory/Forced Leave | Implemented as selectable subtype |
| `sl` | Sick Leave | Implemented as selectable subtype |
| `wl` | Wellness Leave | Implemented as selectable subtype |
| `ml` | Maternity Leave | Implemented as selectable subtype |
| `pl` | Paternity Leave | Implemented as selectable subtype |
| `spl` | Special Privilege Leave | Implemented as selectable subtype |
| `soloparentl` | Solo Parent Leave | Implemented as selectable subtype |
| `studyl` | Study Leave | Implemented as selectable subtype |
| `rp` | Rehabilitation Privilege | Implemented as selectable subtype |
| `slbw` | Special Leave Benefits for Women | Implemented as selectable subtype |
| `sel` | Special Emergency/Calamity Leave | Implemented as selectable subtype |
| `al` | Adoption Leave | Implemented as selectable subtype |

### 7.3 Leave limitations

The system does not implement:

- Leave-credit balances, accrual, deduction, or insufficient-balance checks.
- Different advance-notice rules for VL, SL, emergency leave, or special leave.
- Mandatory attachment rules by leave type or duration.
- Half-day leave duration and AM/PM selection.
- Exclusion of weekends, holidays, or scheduled days off from multi-day leave.
- Gender, tenure, service-length, parenthood, or other eligibility validation.
- A separate “Special Leave” umbrella policy beyond the selectable subtype list.
- NPP, Jobber, or co-terminus leave eligibility rules approved by HR.

## 8. Organization-wide calendar events

### 8.1 Flag ceremony — Implemented

- Flag Ceremony is a global event without an employee HRIS number.
- Monday is treated as a flag day even if no flag event record exists.
- Standard fixed schedules use 8:30 AM as the flag reference unless the employee's official time is earlier.
- Full-flexitime flag days use 8:30 AM–5:30 PM.
- Flag-related tardiness is not eligible for flex reclassification.
- Flag events appear as DTR remarks.
- Authorized HR/superadmin users can create special events; created special events are approved immediately in the Pasig HR flow.

### 8.2 Whole-day suspension — Partial

In the Pasig HR creation flow:

- Whole-day suspension is stored as 8:00 AM–5:00 PM.
- It appears as a global DTR remark.
- The fixed-schedule processor treats a range where start and end differ as a whole-day suspension with a 5:00 PM endpoint.

The code does not consistently suppress all tardy/undertime calculations for whole-day suspension, especially for full-flexitime entries.

### 8.3 Partial-day / half-day suspension — Partial and inconsistent

In the Pasig HR creation flow:

- Partial suspension is encoded with `start == end == selected suspension time`.
- Fixed and full-flexitime processors use that time as the expected end.
- This supports an afternoon early-release interpretation, not morning suspension or a general half-day schedule.

In the DAPCC HR creation flow:

- A selected partial suspension time is stored as the start, but end remains 5:00 PM.
- The DAPCC processor tests `start == end` to identify partial suspension, so the stored representation does not activate the intended shortened endpoint.

There is no explicit AM/PM half-day type, paid-hours rule, or validation that the selected time is within the employee's schedule.

### 8.4 Holidays — Partial

- Holiday is a global calendar event with a free-text description.
- It appears as a DTR remark.
- In the standard processor, fixed-schedule tardy and undertime calculations are skipped when a holiday exists.
- The full-flexitime branch does not have the same holiday guard, so behavior differs by schedule type.
- DAPCC displays holiday remarks but does not contain a complete, explicit holiday exemption branch.

### 8.5 Special holidays — Not implemented as a distinct category

- The application has only one Holiday tag.
- Regular holiday, special non-working holiday, special working holiday, local holiday, and center-specific holiday are not distinguished.
- No holiday pay, overtime, premium, or attendance-credit computation exists.
- Holiday scope is global in the current event/report queries; there is no explicit center, location, or employee eligibility field.

### 8.6 Weekends and rest days — Partial

- Calendars visually mark Saturday and Sunday as weekends.
- Standard reports generate a row for every calendar day in the selected range, including weekends.
- Empty weekends are not explicitly labeled as rest days and are not excluded from request ranges.
- Employees can request OB, CDO, or leave on weekends because no server-side weekend prohibition exists.
- DAPCC Jobber weekly reports intentionally begin on Sunday and cover seven days.
- DAPCC Shift and Day-off events can express rotating work/rest schedules, but there is no generalized rest-day policy for all appointment types.

## 9. DAPCC-specific implementation

DAPCC has a separate event enum and report processor. Additional event types are:

- Shift (`dapcc_shift`).
- Day-off (`dapcc_dayoff`).
- DAPCC-specific suspension, holiday, and flag tags.

Current DAPCC behavior:

- Shifts can be assigned to multiple employees across a date range, with a common or per-employee start time.
- Shift duration is nine elapsed hours and may cross midnight.
- DAPCC attendance can still be captured without an assigned shift; the scanner returns a warning.
- DTR tardiness and undertime use the assigned shift when present.
- Without a shift, the DAPCC fallback latest start is 9:30 AM and the corresponding late-start endpoint is 6:30 PM.
- Jobber reports use Sunday-based weekly selection.
- The DAPCC processor includes employee schedule events without filtering to approved status, unlike the standard Pasig processor.
- DAPCC HR-created events generally rely on the database default `pending` status, including shifts and global events, but the report processor may still consume them. Approval semantics therefore need normalization.
- DAPCC does not calculate PM tardiness or fixed-schedule flex reclassification in the same way as the standard processor.

## 10. DTR reporting policy

### 10.1 Individual and bulk reports

- Employees can preview and print their own DTR.
- Coordinators and HR can create individual and bulk PDFs within policy scope.
- Bulk reports filter by department and appointment status.
- Report output includes daily time-in/out, break, schedule type, tardy, PM tardy, undertime, flex status, event remarks, totals, and signature when available.
- DAPCC output includes shift and excludes some standard Pasig fields.
- MOV links may be embedded in remarks for supported events.

### 10.2 Current cutoff matrix

| Classification | First period | Second period | Alternative |
|---|---|---|---|
| PBP | 1–15 | 16–month end | None |
| NPP | Previous month 26–current month 10 | 11–25 | None |
| Jobber | Not defined by month cutoff | Not defined by month cutoff | DAPCC Sunday through Saturday week |
| Co-terminus | Not implemented | Not implemented | Not implemented |

## 11. Recommended consolidated policy baseline

The following rules should be approved by HR and then implemented as centralized policy/configuration rather than remaining distributed through Livewire components and report classes.

### 11.1 Employment classification

1. Add explicit database-supported classifications for PBP, NPP, Jobber, and Co-terminus.
2. Define each classification's cutoff cycle, leave eligibility, work schedule, rest day, and report format.
3. Decide whether Co-terminus follows PBP or NPP cutoffs; do not infer this from another classification in code.
4. Make active/inactive employment effective-dated so historical reports remain reproducible.

### 11.2 Work schedule and attendance

1. Store schedule policy with effective-from/effective-to dates and retain historical versions.
2. Define eight net work hours plus a one-hour break as a configurable requirement, not a hard-coded assumption.
3. Resolve the exact 15-minute grace-period boundary.
4. Approve an exact monthly limit for flex reclassification and correct the current `<= 3` behavior.
5. Treat missing time-out as an incomplete entry requiring review; do not silently equate it to a full-day tardy without an approved rule.
6. Support overnight shifts by associating time-out with the originating shift/work date.
7. Define correction workflow, approver, reason, audit trail, and lock date for edited time entries.

### 11.3 WFH and Hybrid WFH

1. Define eligible weekdays consistently across employee, coordinator, and HR forms.
2. Approved WFH and Hybrid WFH should each unlock the correct capture workflow.
3. Define Hybrid WFH as either mixed-location captures on one day or a specific remote/onsite time block.
4. Define whether scanner capture is allowed during WFH and how accidental onsite scans are resolved.
5. Require approval before either event affects access or DTR remarks.

### 11.4 OB and leave

1. Define whether OB is full-day or time-bounded and whether time capture remains required.
2. Add AM/PM and exact-time partial-day support.
3. Define notice periods separately for planned leave, sick leave, and emergency leave.
4. Add leave credits and eligibility only if this DTR system is intended to become the authoritative leave ledger; otherwise integrate with the authoritative HR system.
5. Exclude non-working days from leave ranges according to the employee's actual schedule.
6. Define required MOVs and approvers by event/leave type and duration.

### 11.5 Calendar days

1. Add explicit day types: regular holiday, special non-working holiday, special working holiday, local holiday, whole-day suspension, AM suspension, PM suspension, flag ceremony, weekend, scheduled rest day, and ordinary workday.
2. Add scope: organization-wide, center, group, department, location, or selected employees.
3. A non-working holiday or whole-day suspension should suppress tardy and undertime for all schedule types unless actual-work policy says otherwise.
4. A partial suspension should adjust required start/end and required minutes, not rely on `start == end` as an implicit flag.
5. Weekends should be non-working by default for standard schedules but working/rest status must come from assigned shifts for DAPCC and Jobbers.
6. Holiday and suspension rules should never depend only on display labels or free-text descriptions.

### 11.6 Approval and audit

1. Employee requests start pending.
2. Only authorized users within employee scope can approve/disapprove.
3. Global special events require an explicitly authorized HR/superadmin role and should be approved on creation only if that is an approved business rule.
4. Only approved events affect DTR computation.
5. Every decision records actor, timestamp, reason/note, previous state, and notification result.
6. Published DTR periods should be lockable and reproducible from versioned schedule/event rules.

## 12. Implementation gaps and priority decisions

### Critical

- Align the database appointment-status constraint with Jobber UI support.
- Decide and add Co-terminus policy.
- Normalize approval filtering between Pasig and DAPCC.
- Correct DAPCC partial-suspension encoding.
- Apply holiday/suspension exemptions consistently to fixed, flexitime, and DAPCC calculations.
- Define Hybrid WFH capture behavior and include it in the appropriate access gate.

### High

- Centralize event eligibility and weekday restrictions.
- Explicitly model regular/special/local holidays and AM/PM suspension.
- Exclude or classify weekends/rest days according to schedule.
- Resolve the 15-minute grace boundary and maximum flex count.
- Add automated tests for every attendance policy branch and cutoff boundary.

### Medium

- Add leave balance/integration decisions, partial-day leave, and MOV requirements.
- Normalize DTR period handling at December/January boundaries.
- Replace hard-coded special employee HRIS numbers and scanner emails with configuration or permissions.
- Give Timekeeper a documented role policy or remove the unused role distinction.
- Add effective dating and locking for historical report reproducibility.

## 13. Primary implementation references

- Roles: `app/Enums/Role.php`
- Appointment classifications: `app/Enums/AppointmentStatus.php`
- Standard and DAPCC events: `app/Enums/Events.php`, `app/Enums/Dapcc/Events.php`
- Leave subtypes: `app/Enums/OfficialLeaves.php`
- Schedule types: `app/Enums/ScheduleType.php`
- Role/center routes: `routes/web.php`, `routes/api.php`
- Employee and department authorization: `app/Policies/EmployeePolicy.php`, `app/Policies/DepartmentPolicy.php`
- Employee requests: `app/Livewire/Employee/LeaveFlexiApplication/`
- WFH capture: `app/Livewire/Employee/WorkFromHome.php`
- Scanner/mobile capture: `app/Http/Controllers/Api/V1/AttendanceController.php`
- Standard report generation: `app/Actions/GenerateReport.php`, `app/Actions/ProcessReport.php`
- DAPCC report generation: `app/Actions/ProcessDapccReport.php`
- PDF periods and bulk scope: `app/Http/Controllers/Pdf/DtrReportController.php`
- Special event creation: `app/Livewire/HrAdmin/Events/`, `app/Livewire/Dapcc/HrAdmin/Events/`
