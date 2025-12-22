<?php

require_once 'AppController.php';
require_once 'src/repository/ProjectRepository.php';
require_once 'src/repository/TaskRepository.php';
require_once 'src/repository/ProjectInvitationRepository.php';
require_once 'src/repository/UserRepository.php';

class DashboardController extends AppController {

    public function dashboard()
    {
        //session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit();
        }

        $projectRepository = new ProjectRepository();
        $taskRepository = new TaskRepository();

        $projects = $projectRepository->getProjectsByUserId($_SESSION['user_id']);
        $projectsWithTasks = [];
        foreach ($projects as $project) {
            $tasks = $taskRepository->getTasksByProjectId($project->getId());
            $projectsWithTasks[] = [
                'project' => $project,
                'tasks' => $tasks
            ];
        }

        $invitationRepository = new ProjectInvitationRepository();
        $allInvitations = $invitationRepository->findByInviteeId($_SESSION['user_id']);

        $pendingInvitations = array_filter($allInvitations, fn($inv) => $inv->getStatus() === 'pending');
        
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
}
