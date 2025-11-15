<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
class Routing {

    public static $routes = [
        'login' => 'SecurityController@login',
        'logout' => 'SecurityController@logout',
        'dashboard' => 'DashboardController@dashboard',
        'add-task' => 'DashboardController@addTask',
        'delete-task' => 'DashboardController@deleteTask'
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
