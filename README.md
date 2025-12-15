# 🔐 SecureShare - Secure Document Collaboration Platform

SecureShare is a comprehensive document collaboration platform built with Laravel 12, featuring end-to-end encryption, role-based access control, version management, and real-time collaboration tools. It is designed for teams that need to share sensitive documents securely and manage projects efficiently.

---

## 📑 Table of Contents

1.  [Features](#-features)
2.  [Installation](#-installation)
3.  [Application Workflow](#-application-workflow)
4.  [Usage Guide](#-usage-guide)
5.  [Database Schema & Relationships](#-database-schema--relationships)
6.  [API Documentation](#-api-documentation)
7.  [Technology Stack](#-%EF%B8%8F-technology-stack)
8.  [Security](#-security)
9.  [Docker Support](#-docker-support)

---

## ✨ Features

-   **🔐 Bank-Grade Security**: AES-256-CBC encryption for all uploaded files.
-   **👥 Role-Based Access Control (RBAC)**: Fine-grained permissions for Admins, Managers, and Members.
-   **📁 Project Management**: Create projects, invite members, and manage roles.
-   **📄 Document Versioning**: Automatic version control with change history.
-   **💬 Real-time Collaboration**: Polymorphic commenting system on projects and documents.
-   **✅ Task Management**: Kanban-style status tracking, priorities, and deadlines.
-   **📅 Calendar Integration**: Visual overview of task deadlines and project milestones.
-   **🔔 Notifications**: Real-time alerts for actions that need your attention.
-   **📜 Audit Logging**: Comprehensive tracking of every action for compliance.

---

## 🚀 Installation

### Prerequisites

-   **PHP**: >= 8.2
-   **Composer**: Latest version
-   **Database**: MySQL 8.0+ or MariaDB 10.10+
-   **Web Server**: Nginx or Apache
-   **Optional**: Node.js & NPM (for frontend assets), Docker (for containerized deployment)

### Step-by-Step Setup

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/itzluthfi/secureshare.git
    cd secureshare
    ```

2.  **Install PHP Dependencies**

    ```bash
    composer install
    ```

3.  **Environment Configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4.  **Configure Database**
    Open `.env` and update your database credentials:

    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=secureshare
    DB_USERNAME=root
    DB_PASSWORD=your_password
    ```

5.  **Run Migrations & Seeders**
    This will create the database structure and populate it with default roles and users.

    ```bash
    php artisan migrate --seed
    # Or specifically:
    # php artisan migrate
    # php artisan db:seed --class=AdminUserSeeder
    ```

6.  **Generate Storage Link**

    ```bash
    php artisan storage:link
    ```

7.  **Generate Swagger Documentation (Optional)**

    ```bash
    php artisan l5-swagger:generate
    ```

8.  **Start the Server**
    ```bash
    php artisan serve
    ```
    Access the app at: `http://localhost:8000`

---

## 🔄 Application Workflow

Understanding how SecureShare works:

1.  **User Registration & Login**:

    -   New users verify their email (if configured).
    -   Secure login via JWT (Sanctum).

2.  **Project Initialization**:

    -   A user (Manager/Admin) creates a **Project**.
    -   They become the **Owner** of that project.

3.  **Team Assembly**:

    -   The Owner invites other users via email to join the project.
    -   Invited users receive a notification and must **Accept** the invitation to access the project.
    -   Roles can be assigned: **Manager** (can edit project settings) or **Member** (can view/contribute).

4.  **Secure Collaboration**:
    -   **Documents**: Users upload files. The server generates a unique encryption key/IV for the file, encrypts it using AES-256, and stores _only_ the encrypted blob. Keys are stored securely in the database.
    -   **Tasks**: Users create tasks, assign them to members, and track progress (Todo -> In Progress -> Done).
    -   **Comments**: Team members discuss specific documents or the project as a whole using the threaded comment system.

---

## 📖 Usage Guide

### 1. Managing Projects

-   Go to **Dashboard**. Click **"New Project"**.
-   Enter a Name and Description.
-   Once created, you are redirected to the **Project Details** page.

### 2. Adding Members

-   In the Project Details page, find the **"Members"** tab.
-   Click **"Add Member"**. Select a user from the list and choose a role.
-   The user will see an invitation in their notifications.

### 3. Uploading & Versioning Documents

-   Navigate to the **"Documents"** tab.
-   Click **"Upload Document"**. Select your file (PDF, DOCX, IMG, etc.).
-   **Encryption happens automatically**.
-   To update a file, click the **"Upload New Version"** icon on the document card. Unlike replacing a file, this keeps the old version secure and accessible in the "History" modal.

### 4. Creating & Managing Tasks

-   Navigate to the **"Tasks"** tab.
-   Click **"Create Task"**.
-   Fill in Title, Description, Priority, and Assignees.
-   You can drag-and-drop tasks between "To Do", "In Progress", and "Done" columns.
-   **Note**: Regular Members can only update the status of tasks assigned to them (unless configured otherwise). Managers can edit all task details.

### 5. Using the Calendar

-   Click **"Calendar"** in the sidebar.
-   All your task deadlines and project milestones are visualized here.
-   Click on an event to see more details or navigate to the project.

---

## 💾 Database Schema & Relationships

The database is designed for scalability and data integrity. Key relationships include:

### 1. Users & RBAC

-   **Table**: `users`
-   **Roles**: Defined via an `enum` or role column (`admin`, `manager`, `member`).
-   **Relation**: `User` has many `Project` (created_by) and belongs to many `Project` (via `project_members`).

### 2. Projects & Members (`project_members`)

-   **Type**: Many-to-Many Relationship.
-   **Pivot Table**: `project_members` stores:
    -   `user_id`: The member.
    -   `project_id`: The project.
    -   `role`: Role within this specific project (owner, manager, member).
    -   `status`: Invitation status (pending, accepted, declined).

### 3. Documents & Encryption

-   **Table**: `documents`
-   **Relation**: Belongs to `Project` and `User` (uploader).
-   **Security Columns**: `encryption_key`, `encryption_iv`, `file_path`.
-   **Versions**: `Document` has many `DocumentVersion`. Each version stores its own file path and change notes.

### 4. Tasks & Assignments

-   **Table**: `tasks`
-   **Relation**: Belongs to `Project` and `User` (creator).
-   **Assignments**: Many-to-Many with `Users` via `task_assignees` table (allowing multiple people per task).

### 5. Polymorphic Comments

-   **Table**: `comments`
-   **Polymorphism**: Uses `commentable_id` and `commentable_type`.
-   **Usage**: Can be attached to a `Project` OR a `Document`.
-   **Structure**: Supports nesting via `parent_id` (Reply system).

### 6. Audit Logs

-   **Table**: `audit_logs`
-   **Relation**: Belongs to `User`.
-   **Polymorphism**: `auditable_type` / `auditable_id` tracks which resource was affected (e.g., "User X deleted Document Y").

---

## 📡 API Documentation

This project uses **Swagger/OpenAPI** annotations. To view the interactive API documentation:

1.  Ensure you have generated the docs: `php artisan l5-swagger:generate`
2.  Visit: `http://localhost:8000/api/documentation`

**Key Endpoints:**

-   `POST /api/v1/auth/login` - Get JWT Token
-   `GET /api/v1/projects` - List your projects
-   `POST /api/v1/projects/{id}/documents` - Upload encrypted file
-   `GET /api/v1/tasks/calendar` - Get all your tasks

---

## 🐳 Docker Support

Deploy instantly using the included Docker configuration.

**Compose Services:**

-   **app**: PHP 8.2 FPM
-   **webserver**: Nginx (Alpine)
-   **db**: MySQL 8.0
-   **redis**: Redis for caching/queues

**Run:**

```bash
docker-compose up -d --build
```

---

## 🤝 Contributing

Contributions are welcome!

1.  Fork the repo
2.  Create a feature branch (`git checkout -b feature/amazing-feature`)
3.  Commit your changes (`git commit -m 'Add amazing feature'`)
4.  Push to the branch (`git push origin feature/amazing-feature`)
5.  Open a Pull Request

## 📧 Support

For security vulnerabilities or support, please contact: **support@secureshare.com**

---

**Built with ❤️ by Luthfi**
