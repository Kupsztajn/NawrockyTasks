# Nawrocky Tasks

A PHP MVC web application for task management with user authentication, project organization, and collaborative features. Built with Docker, PostgreSQL, and modern PHP practices.

## Architecture

```
┌─────────────────┐
│   Presentation  │  ← HTML/CSS/JS Views
├─────────────────┤
│   Controllers   │  ← Business Logic (Security, Dashboard)
├─────────────────┤
│   Repositories  │  ← Data Access Layer
├─────────────────┤
│     Models      │  ← Data Objects (User, Project, Task)
├─────────────────┤
│   PostgreSQL    │  ← Database Layer
└─────────────────┘
```

### Components:
- **Frontend**: HTML templates with CSS styling and JavaScript
- **Backend**: PHP controllers handling HTTP requests and responses
- **Data Layer**: Repository pattern for database operations
- **Database**: PostgreSQL with tables for users, projects, tasks, and invitations
- **Infrastructure**: Docker containers (Nginx, PHP-FPM, PostgreSQL)

## Installation

### Prerequisites
- Docker and Docker Compose installed
- Git

### Setup Steps
1. Clone the repository:
   ```bash
   git clone <repository-url>
   cd nawrockyTasks
   ```

2. Copy environment configuration:
   ```bash
   cp .env.example .env
   ```

3. Configure environment variables in `.env` file (see Environment Variables section)

4. Start the application:
   ```bash
   docker-compose up --build
   ```

5. Access the application at `http://localhost:8080`

The application will be available with sample data pre-loaded.

## Environment Variables

Create a `.env` file based on `.env.example`:

```env
# Database Configuration
DB_HOST=db
DB_PORT=5432
DB_NAME=nawrocky_tasks
DB_USER=nawrocky
DB_PASSWORD=nawrocky

# Application Configuration
APP_ENV=development
APP_URL=http://localhost:8080

# Session Configuration
SESSION_LIFETIME=3600

# Admin Credentials (for initial setup)
ADMIN_EMAIL=admin@example.com
ADMIN_PASSWORD=admin
```

## Test Scenario

Follow these steps to test the application's core functionality:

### 1. Login
- Navigate to `http://localhost:8080`
- Login with admin credentials:
  - Email: `admin@example.com`
  - Password: `admin`
- Verify dashboard loads with existing projects

### 2. Role-Based Access
- As admin user, navigate to `/admin-users`
- Verify admin panel is accessible
- Logout and try accessing `/admin-users` as regular user
- Verify 403 Forbidden error page displays

### 3. CRUD Operations - Projects
- From dashboard, click "Create Project"
- Fill form: Name="Test Project", Description="Testing CRUD"
- Submit and verify project appears in dashboard
- Click project name to view details
- Edit project (if available) or delete project
- Verify changes reflect immediately

### 4. CRUD Operations - Tasks
- In a project view, click "Add Task"
- Fill form: Title="Test Task", Description="Testing task CRUD"
- Assign to a user and submit
- Verify task appears in project
- Update task status (pending → in_progress → completed)
- Delete task and verify removal

### 5. User Management (Admin Only)
- Login as admin
- Go to `/admin-users`
- View user list
- Toggle admin status for a user
- Verify role changes take effect

### 6. Authentication Errors
- Logout from admin account
- Try accessing protected routes like `/dashboard` without login
- Verify redirect to login page (401 equivalent)
- Try accessing `/admin-users` as regular user
- Verify 403 Forbidden page

### 7. Views and Triggers
- Test responsive design by resizing browser window
- Verify navbar shows correct user info and logout option
- Test form validations (empty fields, invalid emails)
- Check error pages (400, 403, 404, 500) by triggering errors

### 8. Project Invitations
- As project owner, invite a user to project
- Login as invited user
- Accept/decline invitation
- Verify project membership changes

## Database Schema

The application uses PostgreSQL with the following tables:
- `users`: User accounts with roles
- `projects`: Project definitions with ownership
- `tasks`: Tasks belonging to projects with assignments
- `project_invitations`: Invitation system for collaboration
- `user_profiles`: Extended user information

## API Endpoints

- `GET/POST /login` - Authentication
- `GET/POST /register` - User registration
- `POST /logout` - Session termination
- `GET /dashboard` - Main dashboard
- `POST /create-project` - Project creation
- `POST /create-task` - Task creation
- `POST /update-task-status` - Task status updates
- `GET /admin-users` - Admin user management (admin only)

## Technologies Used

- **Backend**: PHP 8.3, MVC Architecture
- **Database**: PostgreSQL 15
- **Frontend**: HTML5, CSS3, JavaScript
- **Infrastructure**: Docker, Docker Compose, Nginx
- **Security**: Password hashing, session management, role-based access
