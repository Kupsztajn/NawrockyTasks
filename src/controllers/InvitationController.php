<?php

require_once 'AppController.php';
require_once 'src/repository/ProjectInvitationRepository.php';
require_once 'src/repository/UserRepository.php';
require_once 'src/repository/ProjectRepository.php';

class InvitationController extends AppController {

    public function searchUsers()
    {
        //session_start();
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
        //session_start();
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
        //session_start();
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
        //session_start();
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

    public function removeUserFromProject() {
        //session_start();
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
        //session_start();
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

}
