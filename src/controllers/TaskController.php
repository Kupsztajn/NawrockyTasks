<?php

require_once 'AppController.php';
require_once 'src/repository/TaskRepository.php';
require_once 'src/repository/ProjectRepository.php';

class TaskController extends AppController {
    
    public function addTask()
    {
        ////session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $project_id = $_POST['project_id'] ?? '';
            $title = $_POST['title'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!empty($title) && !empty($project_id)) {
                $task = new Task(null, $project_id, $title, $description);
                $taskRepository = new TaskRepository();
                $taskRepository->save($task);
            }
        }

        // Redirect back to the project page instead of dashboard
        header('Location: /project?id=' . $project_id);
        exit();
    }

    public function deleteTask()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? 'all';

            if (!empty($id)) {
                $taskRepository = new TaskRepository();
                $task = $taskRepository->getTaskById($id); // Get task to find project_id
                $taskRepository->delete($id);
                if ($task) {
                    header('Location: /project?id=' . $task->getProjectId() . '&status=' . urlencode($status));
                    exit();
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function updateTaskStatus()
    {
        //session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $status = $_POST['status'] ?? 'all';

            if (!empty($id)) {
                $taskRepository = new TaskRepository();
                $task = $taskRepository->getTaskById($id);
                if ($task) {
                    // Toggle status
                    $newStatus = $task->getStatus() === 'done' ? 'pending' : 'done';
                    $task->setStatus($newStatus);
                    $taskRepository->update($task);
                    header('Location: /project?id=' . $task->getProjectId() . '&status=' . urlencode($status));
                    exit();
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }
}