<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
class Routing {

    public static $routes = [
        'login' => 'SecurityController@login',
        'register' => 'SecurityController@register',
        'logout' => 'SecurityController@logout',
        'account' => 'SecurityController@account',
        'change-password' => 'SecurityController@changePassword',
        'dashboard' => 'DashboardController@dashboard',
        'project' => 'DashboardController@project',
        'project-members' => 'DashboardController@projectMembers',
        'add-project' => 'DashboardController@addProject',
        'add-task' => 'DashboardController@addTask',
        'delete-task' => 'DashboardController@deleteTask',
        'update-task-status' => 'DashboardController@updateTaskStatus',
        'search-users' => 'DashboardController@searchUsers',
        'invite-user' => 'DashboardController@inviteUser',
        'accept-invitation' => 'DashboardController@acceptInvitation',
        'decline-invitation' => 'DashboardController@declineInvitation',
        'remove-user' => 'DashboardController@removeUserFromProject',
        'leave-project' => 'DashboardController@leaveProject',
        'delete-project' => 'DashboardController@deleteProject',
        'admin-users' => 'DashboardController@adminUsers',
        'delete-user' => 'DashboardController@deleteUser',
        'toggle-admin' => 'DashboardController@toggleAdmin'
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
