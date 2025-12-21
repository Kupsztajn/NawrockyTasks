<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
require_once 'src/controllers/ProjectController.php';
require_once 'src/controllers/TaskController.php';
require_once 'src/controllers/InvitationController.php';
require_once 'src/controllers/AdminController.php';

class Routing {

    public static $routes = [
        'login' => 'SecurityController@login',
        'register' => 'SecurityController@register',
        'logout' => 'SecurityController@logout',
        'account' => 'SecurityController@account',
        'change-password' => 'SecurityController@changePassword',
        'dashboard' => 'DashboardController@dashboard',
        'project' => 'ProjectController@project',
        'project-members' => 'ProjectController@projectMembers',
        'add-project' => 'ProjectController@addProject',
        'add-task' => 'TaskController@addTask',
        'delete-task' => 'TaskController@deleteTask',
        'update-task-status' => 'TaskController@updateTaskStatus',
        'search-users' => 'InvitationController@searchUsers',
        'invite-user' => 'InvitationController@inviteUser',
        'accept-invitation' => 'InvitationController@acceptInvitation',
        'decline-invitation' => 'InvitationController@declineInvitation',
        'remove-user' => 'InvitationController@removeUserFromProject',
        'leave-project' => 'InvitationController@leaveProject',
        'delete-project' => 'ProjectController@deleteProject',
        'admin-users' => 'AdminController@adminUsers',
        'delete-user' => 'AdminController@deleteUser',
        'toggle-admin' => 'AdminController@toggleAdmin'
    ];

    public static function run($path) {
        session_start();

        // Admin-only routes
        $adminRoutes = ['admin-users', 'delete-user', 'toggle-admin'];

        if (in_array($path, $adminRoutes)) {
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
                http_response_code(403);
                include 'public/views/403.html';
                exit();
            }
        }

        if (array_key_exists($path, self::$routes)) {
            // Pobierz wpis z tablicy routes
            $route = self::$routes[$path];

            // Rozdziel na nazwę kontrolera i metodę
            list($controllerName, $action) = explode('@', $route);

            // Utwórz obiekt kontrolera
            $controller = new $controllerName();

            // Wywołaj metodę kontrolera
            $controller->$action();
        } else {
            include 'public/views/404.html';
        }
    }
}
