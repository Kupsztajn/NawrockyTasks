<?php

require_once 'AppController.php';
require_once 'src/repository/ProjectRepository.php';
require_once 'src/repository/TaskRepository.php';

class DashboardController extends AppController {

    public function dashboard()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $projectRepository = new ProjectRepository();
        $projects = $projectRepository->getProjectsByUserId($_SESSION['user_id']);

        $taskRepository = new TaskRepository();
        $projectsWithTasks = [];
        foreach ($projects as $project) {
            $tasks = $taskRepository->getTasksByProjectId($project->getId());
            $projectsWithTasks[] = [
                'project' => $project,
                'tasks' => $tasks
            ];
        }

        return $this->render('dashboard', ['projectsWithTasks' => $projectsWithTasks]);
    }

    public function addProject()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';

            if (!empty($name)) {
                $project = new Project(null, $_SESSION['user_id'], $name, $description);
                $projectRepository = new ProjectRepository();
                $projectRepository->save($project);
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function addTask()
    {
        session_start();
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
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';

            if (!empty($id)) {
                $taskRepository = new TaskRepository();
                $task = $taskRepository->getTaskById($id); // Get task to find project_id
                $taskRepository->delete($id);
                if ($task) {
                    header('Location: /project?id=' . $task->getProjectId());
                    exit();
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function project()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $projectId = $_GET['id'] ?? '';
        if (empty($projectId)) {
            header('Location: /dashboard');
            exit();
        }

        $projectRepository = new ProjectRepository();
        $project = $projectRepository->getProjectById($projectId);

        if (!$project || $project->getUserId() != $_SESSION['user_id']) {
            header('Location: /dashboard');
            exit();
        }

        $taskRepository = new TaskRepository();
        $tasks = $taskRepository->getTasksByProjectId($projectId);

        return $this->render('project', ['project' => $project, 'tasks' => $tasks]);
    }

}
