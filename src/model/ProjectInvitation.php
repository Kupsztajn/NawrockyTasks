<?php

class ProjectInvitation {
    private $id;
    private $projectId;
    private $inviterUserId;
    private $inviteeUserId;
    private $status;
    private $createdAt;

    public function __construct($id, $projectId, $inviterUserId, $inviteeUserId, $status, $createdAt) {
        $this->id = $id;
        $this->projectId = $projectId;
        $this->inviterUserId = $inviterUserId;
        $this->inviteeUserId = $inviteeUserId;
        $this->status = $status;
        $this->createdAt = $createdAt;
    }

    public function getId() { return $this->id; }
    public function getProjectId() { return $this->projectId; }
    public function getInviterUserId() { return $this->inviterUserId; }
    public function getInviteeUserId() { return $this->inviteeUserId; }
    public function getStatus() { return $this->status; }
    public function getCreatedAt() { return $this->createdAt; }

    public function setId($id) { $this->id = $id; }
    public function setProjectId($projectId) { $this->projectId = $projectId; }
    public function setInviterUserId($inviterUserId) { $this->inviterUserId = $inviterUserId; }
    public function setInviteeUserId($inviteeUserId) { $this->inviteeUserId = $inviteeUserId; }
    public function setStatus($status) { $this->status = $status; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; }
}
