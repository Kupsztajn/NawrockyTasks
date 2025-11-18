<?php

require_once 'src/model/Task.php';

class TaskRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('pgsql:host=db;port=5432;dbname=nawrockytasks', 'nawrocky', 'nawrocky');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getTasksByProjectId($projectId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE project_id = :project_id ORDER BY created_at DESC");
        $stmt->execute(['project_id' => $projectId]);
        $tasks = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tasks[] = new Task($row['id'], $row['project_id'], $row['title'], $row['description'], $row['status'], $row['created_at']);
        }
        return $tasks;
    }

    public function save(Task $task) {
        $stmt = $this->pdo->prepare("INSERT INTO tasks (project_id, title, description, status, created_at) VALUES (:project_id, :title, :description, :status, :created_at)");
        $stmt->execute([
            'project_id' => $task->getProjectId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'created_at' => $task->getCreatedAt()
        ]);
        $task->setId($this->pdo->lastInsertId());
        return $task;
    }

    public function update(Task $task) {
        $stmt = $this->pdo->prepare("UPDATE tasks SET title = :title, description = :description, status = :status WHERE id = :id");
        $stmt->execute([
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'id' => $task->getId()
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getTaskById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Task($row['id'], $row['project_id'], $row['title'], $row['description'], $row['status'], $row['created_at']);
        }
        return null;
    }
}
