<?php

require_once 'AppController.php';
require_once 'src/repository/ProjectRepository.php';
require_once 'src/repository/TaskRepository.php';
require_once 'src/repository/ProjectInvitationRepository.php';
require_once 'src/repository/UserRepository.php';

class DashboardController extends AppController {

    public function dashboard()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $projectRepository = new ProjectRepository();
        $taskRepository = new TaskRepository();

        // Twoje projekty własne
        $projects = $projectRepository->getProjectsByUserId($_SESSION['user_id']);
        $projectsWithTasks = [];
        foreach ($projects as $project) {
            $tasks = $taskRepository->getTasksByProjectId($project->getId());
            $projectsWithTasks[] = [
                'project' => $project,
                'tasks' => $tasks
            ];
        }

        // Wszystkie zaproszenia dla użytkownika
        $invitationRepository = new ProjectInvitationRepository();
        $allInvitations = $invitationRepository->findByInviteeId($_SESSION['user_id']);

        // Zaproszenia pending
        $pendingInvitations = array_filter($allInvitations, fn($inv) => $inv->getStatus() === 'pending');

        // Projekty, do których zostało zaakceptowane zaproszenie
        $invitedProjects = [];
        foreach ($allInvitations as $invitation) {
            if ($invitation->getStatus() === 'accepted') {
                $project = $projectRepository->getProjectById($invitation->getProjectId());
                if ($project) {
                    $invitedProjects[] = [
                        'project' => $project,
                        'status' => $invitation->getStatus()
                    ];
                }
            }
        }

        return $this->render('dashboard', [
            'projectsWithTasks' => $projectsWithTasks,
            'pendingInvitations' => $pendingInvitations,
            'invitedProjects' => $invitedProjects
        ]);
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

    public function updateTaskStatus()
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
                $task = $taskRepository->getTaskById($id);
                if ($task) {
                    // Toggle status
                    $newStatus = $task->getStatus() === 'done' ? 'pending' : 'done';
                    $task->setStatus($newStatus);
                    $taskRepository->update($task);
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

        // Sprawdzenie uprawnień
        $invitationRepository = new ProjectInvitationRepository();
        $acceptedInvitation = $invitationRepository->findByProjectAndInvitee($projectId, $_SESSION['user_id']);

        if (!$project || ($project->getUserId() != $_SESSION['user_id'] && !$acceptedInvitation)) {
            $this->forbidden();
        }

        $taskRepository = new TaskRepository();
        $tasks = $taskRepository->getTasksByProjectId($projectId);

        return $this->render('project', ['project' => $project, 'tasks' => $tasks]);
    }

    public function searchUsers()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        $query = $_GET['q'] ?? '';
        if (empty($query)) {
            echo json_encode([]);
            exit();
        }

        $userRepository = new UserRepository();
        $usersFromDb = $userRepository->searchUsers($query);
        $users = [];
        foreach ($usersFromDb as $user) {
            if ($user->getId() != $_SESSION['user_id']) {
                $users[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail()
                ];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($users);
        exit();
    }

    public function inviteUser()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }

        $projectId = $_POST['project_id'] ?? '';
        $inviteeId = $_POST['invitee_id'] ?? '';

        if (empty($projectId) || empty($inviteeId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing parameters']);
            exit();
        }

        if ($inviteeId == $_SESSION['user_id']) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cannot invite yourself']);
            exit();
        }

        $projectRepository = new ProjectRepository();
        $project = $projectRepository->getProjectById($projectId);

        if (!$project || $project->getUserId() != $_SESSION['user_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Forbidden']);
            exit();
        }

        $invitationRepository = new ProjectInvitationRepository();
        $existingInvitation = $invitationRepository->findByProjectAndInvitee($projectId, $inviteeId);

        if ($existingInvitation) {
            http_response_code(409);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Invitation already sent']);
            exit();
        }

        $invitation = new ProjectInvitation(null, $projectId, $_SESSION['user_id'], $inviteeId, 'pending', null);
        $invitationRepository->save($invitation);

        // tutaj ustaw nagłówek przed jakimkolwiek echo
        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit();
    }
    
    public function acceptInvitation() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['invitation_id'] ?? '';
            if (!empty($id)) {
                $invitationRepository = new ProjectInvitationRepository();
                $invitationRepository->updateStatus($id, 'accepted');
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function declineInvitation() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['invitation_id'] ?? '';
            if (!empty($id)) {
                $invitationRepository = new ProjectInvitationRepository();
                $invitationRepository->updateStatus($id, 'declined');
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function projectMembers()
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

        // Sprawdzenie uprawnień
        $invitationRepository = new ProjectInvitationRepository();
        $acceptedInvitation = $invitationRepository->findByProjectAndInvitee($projectId, $_SESSION['user_id']);

        if (!$project || ($project->getUserId() != $_SESSION['user_id'] && !$acceptedInvitation)) {
            header('Location: /dashboard');
            exit();
        }

        // Pobierz użytkowników projektu
        $userRepository = new UserRepository();
        $projectUsers = $userRepository->getUsersByProjectId($projectId);

        // Sprawdź czy obecny użytkownik jest właścicielem
        $isOwner = ($project->getUserId() == $_SESSION['user_id']);

        return $this->render('project-members', [
            'project' => $project,
            'projectUsers' => $projectUsers,
            'isOwner' => $isOwner
        ]);
    }

    public function removeUserFromProject() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Method not allowed']);
            exit();
        }

        $projectId = $_POST['project_id'] ?? '';
        $userId = $_POST['user_id'] ?? '';

        if (empty($projectId) || empty($userId)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Missing parameters']);
            exit();
        }

        // Sprawdź czy użytkownik jest właścicielem projektu
        $projectRepository = new ProjectRepository();
        $project = $projectRepository->getProjectById($projectId);

        if (!$project || $project->getUserId() != $_SESSION['user_id']) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Only project owner can remove users']);
            exit();
        }

        // Nie można usunąć właściciela projektu
        if ($userId == $project->getUserId()) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Cannot remove project owner']);
            exit();
        }

        // Usuń zaproszenie (co skutkuje usunięciem użytkownika z projektu)
        $invitationRepository = new ProjectInvitationRepository();
        $invitation = $invitationRepository->findByProjectAndInvitee($projectId, $userId);

        if ($invitation) {
            $invitationRepository->delete($invitation->getId());
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
        } else {
            http_response_code(404);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'User not found in project']);
        }
        exit();
    }

    public function leaveProject() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = $_POST['project_id'] ?? '';

            if (!empty($projectId)) {
                $invitationRepository = new ProjectInvitationRepository();
                $invitation = $invitationRepository->findByProjectAndInvitee($projectId, $_SESSION['user_id']);

                if ($invitation && $invitation->getStatus() === 'accepted') {
                    $invitationRepository->delete($invitation->getId());
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }

    public function deleteProject() {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $projectId = $_POST['project_id'] ?? '';

            if (!empty($projectId)) {
                $projectRepository = new ProjectRepository();
                $project = $projectRepository->getProjectById($projectId);

                // Check if the user owns the project
                if ($project && $project->getUserId() == $_SESSION['user_id']) {
                    $projectRepository->delete($projectId);
                }
            }
        }

        header('Location: /dashboard');
        exit();
    }

}
