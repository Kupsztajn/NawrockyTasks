<?php

require_once 'AppController.php';
require_once 'src/repository/UserRepository.php';

class SecurityController extends AppController {

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $userRepository = new UserRepository();
            $user = $userRepository->findByEmail($email);

            if ($user && $user->verifyPassword($password)) {
                session_start();
                $_SESSION['user_id'] = $user->getId();
                $_SESSION['user_email'] = $user->getEmail();
                header('Location: /dashboard');
                exit();
            } else {
                return $this->render('login', ['error' => 'Invalid email or password']);
            }
        }
        return $this->render('login');
    }

    public function logout()
    {
        // Logic for logging out the user
        session_start();
        session_destroy();
        header('Location: /login');
        exit();
    }

    public function account()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        $userRepository = new UserRepository();
        $user = $userRepository->findById($_SESSION['user_id']);
        return $this->render('account', ['user' => $user]);
    }

}
