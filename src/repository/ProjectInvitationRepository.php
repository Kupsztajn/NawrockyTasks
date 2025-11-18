<?php

require_once 'src/model/ProjectInvitation.php';

class ProjectInvitationRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('pgsql:host=db;port=5432;dbname=nawrockytasks', 'nawrocky', 'nawrocky');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function save(ProjectInvitation $invitation) {
        $stmt = $this->pdo->prepare("INSERT INTO project_invitations (project_id, inviter_user_id, invitee_user_id, status) VALUES (:project_id, :inviter_user_id, :invitee_user_id, :status)");
        $stmt->execute([
            'project_id' => $invitation->getProjectId(),
            'inviter_user_id' => $invitation->getInviterUserId(),
            'invitee_user_id' => $invitation->getInviteeUserId(),
            'status' => $invitation->getStatus()
        ]);
        $invitation->setId($this->pdo->lastInsertId());
        return $invitation;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT id, project_id, inviter_user_id, invitee_user_id, status, created_at FROM project_invitations WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new ProjectInvitation($row['id'], $row['project_id'], $row['inviter_user_id'], $row['invitee_user_id'], $row['status'], $row['created_at']);
        }
        return null;
    }

    public function findByInviteeId($inviteeId) {
        $stmt = $this->pdo->prepare("SELECT id, project_id, inviter_user_id, invitee_user_id, status, created_at FROM project_invitations WHERE invitee_user_id = :invitee_user_id");
        $stmt->execute(['invitee_user_id' => $inviteeId]);
        $invitations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $invitations[] = new ProjectInvitation($row['id'], $row['project_id'], $row['inviter_user_id'], $row['invitee_user_id'], $row['status'], $row['created_at']);
        }
        return $invitations;
    }

    public function findByProjectId($projectId) {
        $stmt = $this->pdo->prepare("SELECT id, project_id, inviter_user_id, invitee_user_id, status, created_at FROM project_invitations WHERE project_id = :project_id");
        $stmt->execute(['project_id' => $projectId]);
        $invitations = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $invitations[] = new ProjectInvitation($row['id'], $row['project_id'], $row['inviter_user_id'], $row['invitee_user_id'], $row['status'], $row['created_at']);
        }
        return $invitations;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->pdo->prepare("UPDATE project_invitations SET status = :status WHERE id = :id");
        $stmt->execute([
            'status' => $status,
            'id' => $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM project_invitations WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
    public function findByProjectAndInvitee($projectId, $inviteeId) {
    $stmt = $this->pdo->prepare("
        SELECT id, project_id, inviter_user_id, invitee_user_id, status, created_at
        FROM project_invitations
        WHERE project_id = :project_id AND invitee_user_id = :invitee_user_id
    ");
    $stmt->execute([
        'project_id' => $projectId,
        'invitee_user_id' => $inviteeId
    ]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        return new ProjectInvitation(
            $row['id'],
            $row['project_id'],
            $row['inviter_user_id'],
            $row['invitee_user_id'],
            $row['status'],
            $row['created_at']
        );
    }
    return null;
}

}
