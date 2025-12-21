<?php

require_once 'AppController.php';
require_once 'src/repository/UserRepository.php';

class AdminController extends AppController {

    public function adminUsers() {
        $this->requireAdmin();

        $userRepository = new UserRepository();
        $users = $userRepository->getAllUsers();

        return $this->render('admin-users', ['users' => $users]);
    }

    public function toggleAdmin() {
        $this->requireAdmin();

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
        $this->requireAdmin();

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
