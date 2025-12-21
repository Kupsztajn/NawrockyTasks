<?php

require_once 'AppController.php';
require_once 'src/repository/UserRepository.php';

class AdminController extends AppController {

    public function adminUsers() {
        ////session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if (!isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
            $this->forbidden();
        }

        $userRepository = new UserRepository();
        $users = $userRepository->getAllUsers();

        return $this->render('admin-users', ['users' => $users]);
    }

    public function toggleAdmin() {
        ////session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if (!isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
            $this->forbidden();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? '';

            if (!empty($userId)) {
                $userRepository = new UserRepository();
                $user = $userRepository->findById($userId);

                if ($user) {
                    $newIsAdmin = !$user->getIsAdmin();
                    $userRepository->updateIsAdmin($userId, $newIsAdmin);
                }
            }
        }

        header('Location: /admin-users');
        exit();
    }

    public function deleteUser() {
        //session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if (!isset($_SESSION['user_is_admin']) || !$_SESSION['user_is_admin']) {
            $this->forbidden();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'] ?? '';

            if (!empty($userId)) {
                $userRepository = new UserRepository();
                $userRepository->deleteUser($userId);
            }
        }

        header('Location: /admin-users');
        exit();
    }

}
