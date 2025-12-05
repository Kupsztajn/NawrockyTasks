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
        'leave-project' => 'DashboardController@leaveProject'
    ];

    public static function run($path) {
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
