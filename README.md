# CRM - Ticket Management Module

A secure and role-based CRM Ticket Management System built using Core PHP and MySQL.  
This project demonstrates authentication, authorization, database design, secure file handling, and structured Git workflow.

---

## Project Overview

The CRM Ticket Module allows users to:

- Register and authenticate securely
- Create and manage support tickets
- Assign tickets to other users
- Enforce strict role-based access control
- Soft delete tickets without permanent removal
- Upload supporting files securely

The system ensures that:
- Users can only view their own created or assigned tickets
- Only authors can modify full ticket details
- Assignees can only update ticket status
- Unauthorized access is prevented at query level

---

## Features

### Authentication & Security
- Secure password hashing (`password_hash`)
- Session-based authentication
- Prepared statements (PDO) to prevent SQL injection
- Role-based access checks on every sensitive operation
- Soft delete implementation using `deleted_at`

### Ticket Management
- Create Ticket
- Edit Ticket
- Assign Ticket
- Update Status (Author + Assignee rules enforced)
- Soft Delete (Author only)
- File Upload (with type and size validation)

### UI & UX
- Bootstrap 5 integration
- Responsive layout
- Status badges with visual indicators
- Consistent form design
- Structured CSS architecture (`assets/css/style.css`)

---

## Tech Stack

- PHP (Core PHP)
- MySQL
- PDO
- Bootstrap 5
- WAMP (Local Development)

---

## Folder Structure

crm-ticket/
│
├── app/
│ └── config/
│ └── database.php # PDO database connection
│
├── database/
│ └── migrations/ # SQL schema definitions
│
├── public/
│ ├── login.php # Authentication
│ ├── register.php
│ ├── dashboard.php # Ticket listing
│ ├── ticket.php # Create ticket
│ ├── edit-ticket.php # Update ticket
│ ├── delete-ticket.php # Soft delete
│ ├── uploads/ # Runtime uploaded files (ignored in Git)
│ └── assets/
│ └── css/
│ └── style.css
│
├── docs/
│ └── architecture.md
│
└── README.md

---

## Database Design

### Users Table
- id
- name
- email (unique)
- password (hashed)
- created_at

### Tickets Table
- id
- name
- description
- status
- file
- created_by (FK → users.id)
- assigned_to (FK → users.id)
- assigned_at
- created_at
- updated_at
- deleted_at (Soft Delete)

---

## Installation Guide

1. Clone repository
2. Place inside WAMP `www` directory
3. Create a new MySQL database
4. Run migration SQL manually via phpMyAdmin
5. Configure DB credentials in `app/config/database.php`
6. Visit: http://localhost/crm-ticket/public


---

## Future Enhancements

- Search & filtering
- Pagination
- Dashboard analytics
- Flash messages
- REST API version
- Deployment to shared hosting

---

## Author

Shivam Naithani  
B.Tech (Computer Science)  
Full Stack Developer