<?php

require_once 'AppController.php';
require_once 'src/repository/TaskRepository.php';

class DashboardController extends AppController {

    public function dashboard()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $taskRepository = new TaskRepository();
        $tasks = $taskRepository->getTasksByUserId($_SESSION['user_id']);

        return $this->render('dashboard', ['tasks' => $tasks]);
    }

    public function addTask()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!empty($title)) {
                $task = new Task(null, $_SESSION['user_id'], $title, $description);
                $taskRepository = new TaskRepository();
                $taskRepository->save($task);
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function deleteTask()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';

            if (!empty($id)) {
                $taskRepository = new TaskRepository();
                $taskRepository->delete($id);
            }
        }

        header('Location: /dashboard');
        exit();
    }

}
