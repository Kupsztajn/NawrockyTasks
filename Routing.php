<?php
require_once 'src/controllers/SecurityController.php';
require_once 'src/controllers/DashboardController.php';
class Routing {

    public static $routes = [
        'login' => 'SecurityController@login',
        'logout' => 'SecurityController@logout',
        'dashboard' => 'DashboardController@dashboard'
    ];

    public static function run($path) {
        switch ($path) {
            case 'dashboard':
                
                break;

            case 'login':
                // Pobierz wpis z tablicy routes
                $route = self::$routes[$path]; // "SecurityController@login"

                // Rozdziel na nazwę kontrolera i metodę
                list($controllerName, $action) = explode('@', $route);

                // Utwórz obiekt kontrolera
                $controller = new $controllerName();

                // Wywołaj metodę kontrolera
                $controller->$action();
                break;

            default:
                include 'public/views/404.html';
                break;
        }
    }
}
