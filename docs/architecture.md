# Solution Architecture – CRM Ticket Module

## 1. System Overview

The CRM Ticket Module is a role-based web application built using Core PHP and MySQL.

The system follows a simplified MVC-inspired separation:

- Configuration isolated in `app/config`
- Public PHP files act as controllers
- Database access handled via PDO
- Business rules enforced at query level

The architecture prioritizes security, ownership validation, and maintainability.

---

## 2. High-Level Architecture

User (Browser)
        ↓
Apache (WAMP Server)
        ↓
Public PHP Controllers
        ↓
PDO Database Layer
        ↓
MySQL Database

All sensitive operations pass through authentication and authorization checks before executing database queries.

---

## 3. Database Design

### Users Table

- id (Primary Key)
- name
- email (Unique)
- password (Hashed)
- created_at

### Tickets Table

- id (Primary Key)
- name
- description
- status (pending, inprogress, completed, onhold)
- file
- created_by (Foreign Key → users.id)
- assigned_to (Foreign Key → users.id)
- assigned_at
- created_at
- updated_at
- deleted_at (Soft Delete column)

---

## 4. Entity Relationships

The system contains two core relational mappings:

### Users → Tickets (created_by)
- One user can create multiple tickets.
- Each ticket belongs to exactly one creator.

### Users → Tickets (assigned_to)
- One user can be assigned multiple tickets.
- A ticket may have one assignee (nullable).

This dual foreign key structure enables:

- Ownership validation
- Assignment-based workflow
- Query-level authorization enforcement

---

## 5. Authorization Model

Access control is enforced at SQL query level rather than relying only on UI restrictions.

### Rules

- Users can view only:
  - Tickets they created
  - Tickets assigned to them

- Only creators can:
  - Edit ticket details
  - Assign tickets
  - Soft delete tickets

- Assignees can:
  - Update ticket status only

This prevents privilege escalation even if endpoints are accessed manually.

---

## 6. Security Architecture

### Authentication
- Session-based authentication
- Password hashing using `password_hash()`
- Verification using `password_verify()`

### SQL Injection Prevention
- All database queries use prepared statements via PDO.

### Soft Delete Strategy
- Records are marked using `deleted_at`.
- Data remains recoverable and historically consistent.

### File Upload Security
- Allowed types: JPG, PNG, PDF
- Maximum size: 2MB
- Server-side validation before upload
- Upload directory excluded from version control

---

## 7. Design Decisions

- Core PHP used intentionally to demonstrate fundamental backend understanding.
- Authorization embedded directly in SQL conditions.
- Minimal dependencies to keep deployment simple.
- Clear folder separation to support maintainability and future scaling.

---

## 8. Scalability Considerations

Potential future enhancements:

- Migration to an MVC framework (e.g., Laravel)
- REST API layer
- Pagination and filtering
- Role-based admin panel
- Logging and audit trail
- Cloud deployment

---

## 9. Known Limitations

- No CSRF protection
- No rate limiting
- No automated tests
- No caching layer
- No background job processing

---

## 10. Architectural Philosophy

This module prioritizes:

- Simplicity
- Strong ownership rules
- Query-level security enforcement
- Clear structural separation
- Data integrity over convenience

The design is intentionally minimal but structured to support controlled expansion.