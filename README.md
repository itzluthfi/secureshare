# 🔐 SecureShare - Secure Document Collaboration Platform

SecureShare is a comprehensive document collaboration platform built with Laravel, featuring end-to-end encryption, role-based access control, version management, and real-time collaboration tools.

## ✨ Features

### 1️⃣ Authentication & User Management

-   ✅ **JWT Token Authentication** with Laravel Sanctum
-   ✅ **Role-Based Access Control (RBAC)**: Admin, Manager, Member
-   ✅ **User Management**: CRUD operations for admins
-   ✅ **Password Reset** functionality
-   ✅ **Account Activation/Deactivation**

### 2️⃣ Project Management

-   ✅ Create, edit, and delete projects
-   ✅ Add/remove project members
-   ✅ Assign roles to project members (Owner, Manager, Member)
-   ✅ View projects based on user access permissions

### 3️⃣ Document Management with Encryption

-   ✅ **AES-256 File Encryption** - All uploaded files are encrypted server-side
-   ✅ **Secure Upload/Download** with automatic encryption/decryption
-   ✅ **File Type Validation** and size limits (max 50MB)
-   ✅ **Document Versioning** - Automatic version tracking
-   ✅ **Version History** - View and download previous versions

### 4️⃣ Collaboration Features

-   ✅ **Comments System** with nested replies
-   ✅ **Task Management** with status tracking (To Do, In Progress, Done)
-   ✅ **Task Assignment** to project members
-   ✅ **Priority Levels** (Low, Medium, High)
-   ✅ **Deadline Tracking**

### 5️⃣ Notifications

-   ✅ In-app notifications for:
    -   New document uploads
    -   Task assignments
    -   Comments and replies
    -   Version updates
-   ✅ Real-time notification badge
-   ✅ Mark as read/unread functionality

### 6️⃣ Security Features

-   ✅ **AES-256 File Encryption**
-   ✅ **Policy-based Authorization** for all resources
-   ✅ **Role-based Middleware**
-   ✅ **CSRF Protection**
-   ✅ **API Rate Limiting**

### 7️⃣ Audit Logging

-   ✅ Complete activity tracking:
    -   Login/Logout events
    -   Document uploads/downloads
    -   CRUD operations on all resources
-   ✅ Stores: User, Action, Timestamp, IP Address, User Agent
-   ✅ CSV Export for audit logs
-   ✅ Admin-only access

### 8️⃣ RESTful API

-   ✅ **API Versioning** (/api/v1/...)
-   ✅ Consistent JSON responses
-   ✅ Standard HTTP status codes
-   ✅ Complete endpoint coverage for all features

### 9️⃣ Frontend

-   ✅ **Blade Templates** with responsive design
-   ✅ **jQuery** for AJAX interactions
-   ✅ Real-time notifications without page reload
-   ✅ Modal dialogs for forms
-   ✅ Toast notifications for user feedback

### 🔟 Docker Support

-   ✅ Multi-stage Dockerfile
-   ✅ Docker Compose configuration
-   ✅ Services: PHP-FPM, Nginx, MySQL, Redis, Queue Worker
-   ✅ Development and production ready

## 🚀 Installation

### Prerequisites

-   PHP >= 8.2
-   Composer
-   MySQL / SQLite
-   Node.js & NPM (optional, for asset compilation)

### Setup Instructions

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd secureshare
    ```

2. **Install dependencies**

    ```bash
    composer install
    ```

3. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

4. **Configure database**

    Edit `.env` file:

    ```
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=secureshare
    DB_USERNAME=root
    DB_PASSWORD=
    ```

5. **Run migrations**

    ```bash
    php artisan migrate
    ```

6. **Seed default users**

    ```bash
    php artisan db:seed --class=AdminUserSeeder
    ```

7. **Start the server**

    ```bash
    php artisan serve
    ```

8. **Access the application**

    Open your browser and visit: `http://localhost:8000`

## 👥 Default Login Credentials

After seeding, you can login with these accounts:

-   **Admin**: admin@secureshare.com / password
-   **Manager**: manager@secureshare.com / password
-   **Member**: member@secureshare.com / password

## 🐳 Docker Deployment

```bash
# Build and run with Docker Compose
docker-compose up -d

# Run migrations
docker-compose exec app php artisan migrate

# Seed database
docker-compose exec app php artisan db:seed --class=AdminUserSeeder
```

Access the application at: `http://localhost:8000`

## 📡 API Endpoints

### Authentication

-   `POST /api/v1/auth/register` - Register new user
-   `POST /api/v1/auth/login` - Login
-   `POST /api/v1/auth/logout` - Logout
-   `POST /api/v1/auth/forgot-password` - Request password reset
-   `POST /api/v1/auth/reset-password` - Reset password

### Projects

-   `GET /api/v1/projects` - List projects
-   `POST /api/v1/projects` - Create project
-   `GET /api/v1/projects/{id}` - View project
-   `PUT /api/v1/projects/{id}` - Update project
-   `DELETE /api/v1/projects/{id}` - Delete project
-   `POST /api/v1/projects/{id}/members` - Add member
-   `PUT /api/v1/projects/{id}/members/{userId}` - Update member role
-   `DELETE /api/v1/projects/{id}/members/{userId}` - Remove member

### Documents

-   `GET /api/v1/projects/{projectId}/documents` - List documents
-   `POST /api/v1/projects/{projectId}/documents` - Upload document (encrypted)
-   `GET /api/v1/documents/{id}` - View document metadata
-   `GET /api/v1/documents/{id}/download` - Download and decrypt
-   `POST /api/v1/documents/{id}/versions` - Upload new version
-   `GET /api/v1/documents/{id}/versions` - List versions
-   `GET /api/v1/documents/{documentId}/versions/{versionNumber}/download` - Download specific version

### Tasks

-   `GET /api/v1/projects/{projectId}/tasks` - List tasks
-   `POST /api/v1/projects/{projectId}/tasks` - Create task
-   `PUT /api/v1/tasks/{id}` - Update task
-   `PUT /api/v1/tasks/{id}/status` - Update status
-   `DELETE /api/v1/tasks/{id}` - Delete task

### Comments

-   `GET /api/v1/documents/{documentId}/comments` - List comments
-   `POST /api/v1/documents/{documentId}/comments` - Add comment
-   `POST /api/v1/comments/{id}/reply` - Reply to comment
-   `PUT /api/v1/comments/{id}` - Update comment
-   `DELETE /api/v1/comments/{id}` - Delete comment

### Notifications

-   `GET /api/v1/notifications` - Get notifications
-   `GET /api/v1/notifications/unread-count` - Get unread count
-   `PUT /api/v1/notifications/{id}/read` - Mark as read
-   `PUT /api/v1/notifications/read-all` - Mark all as read

### Audit Logs (Admin Only)

-   `GET /api/v1/audit-logs` - List audit logs
-   `GET /api/v1/audit-logs/export` - Export as CSV

## 🔒 Security

-   All files are encrypted with AES-256-CBC before storage
-   Encryption keys and IVs are stored securely in the database
-   Role-based access control on all routes
-   Policy-based authorization for resources
-   CSRF protection on all forms
-   SQL injection protection via Eloquent ORM
-   XSS protection via Blade templating
-   API rate limiting to prevent abuse

## 📁 Project Structure

```
secureshare/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/          # API Controllers
│   │   │   └── Web/          # Web Controllers
│   │   ├── Middleware/       # Custom Middleware
│   ├── Models/               # Eloquent Models
│   ├── Policies/             # Authorization Policies
│   └── Services/             # Business Logic Services
├── database/
│   ├── migrations/           # Database Migrations
│   └── seeders/              # Database Seeders
├── resources/
│   └── views/                # Blade Templates
├── routes/
│   ├── api.php               # API Routes
│   └── web.php               # Web Routes
├── docker/                   # Docker Configuration
├── Dockerfile
└── docker-compose.yml
```

## 🛠️ Technology Stack

-   **Backend:** Laravel 12, PHP 8.2
-   **Authentication:** Laravel Sanctum
-   **Database:** MySQL / SQLite
-   **Frontend:** Blade, jQuery, Vanilla CSS
-   **Encryption:** OpenSSL AES-256-CBC
-   **Containerization:** Docker, Docker Compose
-   **Web Server:** Nginx
-   **Cache/Queue:** Redis

## 📝 License

This project is open-source and available under the MIT License.

## 👨‍💻 Development

### Running Tests

```bash
php artisan test
```

### Queue Worker (for notifications)

```bash
php artisan queue:work
```

### Clearing Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📧 Support

For support, email: support@secureshare.com

---

**Built with ❤️ using Laravel**
