<?php

class Task {
    private $id;
    private $project_id;
    private $title;
    private $description;
    private $status;
    private $created_at;

    public function __construct($id = null, $project_id = null, $title = null, $description = null, $status = 'pending', $created_at = null) {
        $this->id = $id;
        $this->project_id = $project_id;
        $this->title = $title;
        $this->description = $description;
        $this->status = $status;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getProjectId() {
        return $this->project_id;
    }

    public function setProjectId($project_id) {
        $this->project_id = $project_id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }
}
