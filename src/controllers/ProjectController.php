<?php

require_once 'AppController.php';
require_once 'src/repository/ProjectRepository.php';
require_once 'src/repository/TaskRepository.php';
require_once 'src/repository/ProjectInvitationRepository.php';
require_once 'src/repository/UserRepository.php';

class ProjectController extends AppController {

    public function addProject()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $imagePath = null;

            if (!empty($name)) {
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = 'public/uploads/projects/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $uploadDir . $fileName;

                    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        $imagePath = '/' . $targetFile;
                    }
                }

                $project = new Project(null, $_SESSION['user_id'], $name, $description, $imagePath);
                $projectRepository = new ProjectRepository();
                $projectRepository->save($project);
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function project()
    {
        //session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $projectId = $_GET['id'] ?? '';
        if (empty($projectId)) {
            header('Location: /dashboard');
            exit();
        }

        $status = $_GET['status'] ?? 'all';
        if (!in_array($status, ['all', 'pending', 'done'])) {
            $status = 'all';
        }

        $projectRepository = new ProjectRepository();
        $project = $projectRepository->getProjectById($projectId);

        $invitationRepository = new ProjectInvitationRepository();
        $acceptedInvitation = $invitationRepository->findByProjectAndInvitee($projectId, $_SESSION['user_id']);

        if (!$project || ($project->getUserId() != $_SESSION['user_id'] && !$acceptedInvitation)) {
            $this->forbidden();
        }

        $taskRepository = new TaskRepository();
        $tasks = $taskRepository->getTasksByProjectIdAndStatus($projectId, $status);

        return $this->render('project', ['project' => $project, 'tasks' => $tasks, 'currentStatus' => $status]);
    }

    public function projectMembers()
    {
        //session_start();
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

        $invitationRepository = new ProjectInvitationRepository();
        $acceptedInvitation = $invitationRepository->findByProjectAndInvitee($projectId, $_SESSION['user_id']);

        if (!$project || ($project->getUserId() != $_SESSION['user_id'] && !$acceptedInvitation)) {
            header('Location: /dashboard');
            exit();
        }

        $userRepository = new UserRepository();
        $projectUsers = $userRepository->getUsersByProjectId($projectId);

        $isOwner = ($project->getUserId() == $_SESSION['user_id']);

        return $this->render('project-members', [
            'project' => $project,
            'projectUsers' => $projectUsers,
            'isOwner' => $isOwner
        ]);
    }

    public function updateProject() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = $_POST['project_id'] ?? '';
            $name = $_POST['name'] ?? '';
            $description = $_POST['description'] ?? '';
            $imagePath = null;

            if (!empty($projectId) && !empty($name)) {
                $projectRepository = new ProjectRepository();
                $project = $projectRepository->getProjectById($projectId);

                if ($project && $project->getUserId() == $_SESSION['user_id']) {
                    // Handle image upload if provided
                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $uploadDir = 'public/uploads/projects/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }
                        $fileName = uniqid() . '_' . basename($_FILES['image']['name']);
                        $targetFile = $uploadDir . $fileName;

                        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                            $imagePath = '/' . $targetFile;
                        }
                    } else {
                        // Keep existing image if no new one uploaded
                        $imagePath = $project->getImage();
                    }

                    $project->setName($name);
                    $project->setDescription($description);
                    $project->setImage($imagePath);

                    $projectRepository->update($project);
                }
            }
        }

        header('Location: /project?id=' . $projectId);
        exit();
    }

    public function deleteProject() {
        //session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = $_POST['project_id'] ?? '';

            if (!empty($projectId)) {
                $projectRepository = new ProjectRepository();
                $project = $projectRepository->getProjectById($projectId);

                if ($project && $project->getUserId() == $_SESSION['user_id']) {
                    $projectRepository->delete($projectId);
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }

}
