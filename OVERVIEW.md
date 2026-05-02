# IT Helpdesk System — Overview

Internal IT Helpdesk for employees to submit issues and for the Front Desk to log tickets from phone calls or walk-in requests. The system follows real-world IT support workflows.

---

## Goals

- **Ticket creation**: Either **system** (self-service via web) or **call-logged** (Front Desk logs from phone or walk-in).
- **Status workflow**: **Open** → **In Progress** → **Resolved** → **Closed**.
- **Ticket comments & history**: Add comments on a ticket; status changes are recorded in history.

---

## User roles (`users.role`)

| Role        | Value        | Can do |
|-------------|--------------|--------|
| Employee    | `employee`   | Submit tickets (self-service), view own tickets. |
| Front Desk  | `front_desk` | Create tickets for anyone (phone/walk-in), view all tickets. |
| IT Staff    | `it_staff`   | Assign/update/resolve tickets, view all. |
| Admin       | `admin`      | Full access. |

---

## Ticket flow

1. **Creation**
   - **System**: User submits via web → `source = self_service` (displayed as "System").
   - **Call-logged**: Front desk creates from phone or walk-in → `source = phone` or `walk_in` (displayed as "Call-logged"), with optional `requester_name` / `requester_email`.

2. **Status workflow**
   - **Open** → **In Progress** → **Resolved** → **Closed**.
   - On the ticket view, staff can change status and add resolution notes (for Resolved/Closed).
   - Each status change is recorded as a system entry in **Comments & history**.

3. **Comments & history**
   - **Comments**: Anyone can add a comment on the ticket (stored as `ticket_comments` with `type = comment`).
   - **History**: System entries (e.g. "Ticket created (system).", "Status changed from Open to In Progress.") are stored with `type = system`.
   - Both appear in chronological order on the ticket page.

3. **Priority**
   - `low`, `medium`, `high`, `critical` — used for ordering and filtering.

---

## Database (MySQL)

- **users** — `role` added for employee / front_desk / it_staff / admin.
- **ticket_categories** — e.g. Hardware, Software, Network, Access, Email, Other.
- **tickets** — `ticket_number`, title, description, category, priority, status (`open` / `in_progress` / `resolved` / `closed`), source, submitter/requester, assignee, timestamps, resolution notes.
- **ticket_comments** — `ticket_id`, `user_id` (nullable), `type` (`comment` | `system`), `body`, timestamps. Used for both comments and status-change history.

Run migrations and seed categories:

```bash
php artisan migrate
php artisan db:seed
```

---

## Main routes (current)

| Method | URL | Purpose |
|--------|-----|--------|
| GET | `/` | Home — links to submit ticket and ticket list. |
| GET | `/tickets` | List all tickets (filter by status/priority later). |
| GET | `/tickets/create` | Submit a new ticket (self-service). |
| GET | `/tickets/create?source=phone` | Front desk: log from phone. |
| GET | `/tickets/create?source=walk_in` | Front desk: log walk-in. |
| POST | `/tickets` | Create ticket. |
| GET | `/tickets/{id}` | View one ticket (description, status form, comments & history). |
| PUT | `/tickets/{id}` | Update ticket status (and optional resolution notes). |
| POST | `/tickets/{id}/comments` | Add a comment. |

---

## Next steps (optional)

- **Auth**: Add login so employees see only their tickets; front_desk/it_staff see all.
- **Assignment**: UI for IT to assign tickets to users.
- **Notifications**: Email on create/assign/resolve.
- **Reporting**: Counts by status, category, priority, source.
