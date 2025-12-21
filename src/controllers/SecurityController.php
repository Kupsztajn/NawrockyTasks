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
                    $_SESSION['user_id'] = $user->getId();
                    $_SESSION['user_email'] = $user->getEmail();
                    $_SESSION['user_is_admin'] = $user->getIsAdmin();
                    header('Location: /dashboard');
                    exit();
                } else {
                return $this->render('login', ['error' => 'Invalid email or password']);
            }
        }
        return $this->render('login');
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $userRepository = new UserRepository();

            if ($userRepository->findByEmail($email)) {
                return $this->render('register', ['error' => 'Email already registered']);
            }

            if (strlen($password) < 6) {
                return $this->render('register', ['error' => 'Password must be at least 6 characters long']);
            }

            if ($password !== $confirmPassword) {
                return $this->render('register', ['error' => 'Passwords do not match']);
            }

            $user = new User();
            $user->setEmail($email);
            $user->setPassword($password);
            $userRepository->save($user);

            header('Location: /login');
            exit();
        }
        return $this->render('register');
    }

    public function logout()
    {
        session_destroy();
        header('Location: /login');
        exit();
    }

    public function account()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }
        $userRepository = new UserRepository();
        $user = $userRepository->findById($_SESSION['user_id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
            $firstName = $_POST['first_name'] ?? '';
            $lastName = $_POST['last_name'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $bio = $_POST['bio'] ?? '';

            $userRepository->updateProfile($_SESSION['user_id'], $firstName, $lastName, $phone, $bio);
            $user = $userRepository->findById($_SESSION['user_id']);
            return $this->render('account', ['user' => $user, 'profile_success' => 'Profile updated successfully']);
        }

        return $this->render('account', ['user' => $user]);
    }

    public function changePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $userRepository = new UserRepository();
            $user = $userRepository->findById($_SESSION['user_id']);

            if (!$user->verifyPassword($currentPassword)) {
                return $this->render('account', ['user' => $user, 'error' => 'Current password is incorrect']);
            }

            if ($newPassword !== $confirmPassword) {
                return $this->render('account', ['user' => $user, 'error' => 'New passwords do not match']);
            }

            if (strlen($newPassword) < 6) {
                return $this->render('account', ['user' => $user, 'error' => 'New password must be at least 6 characters long']);
            }

            $userRepository->updatePassword($user->getId(), $newPassword);
            return $this->render('account', ['user' => $user, 'success' => 'Password changed successfully']);
        }

        header('Location: /account');
        exit();
    }

}
