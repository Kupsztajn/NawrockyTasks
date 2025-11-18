<?php

require_once 'src/model/Project.php';

class ProjectRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('pgsql:host=db;port=5432;dbname=nawrockytasks', 'nawrocky', 'nawrocky');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getProjectsByUserId($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute(['user_id' => $userId]);
        $projects = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $projects[] = new Project($row['id'], $row['user_id'], $row['name'], $row['description'], $row['created_at']);
        }
        return $projects;
    }

    public function save(Project $project) {
        $stmt = $this->pdo->prepare("INSERT INTO projects (user_id, name, description, created_at) VALUES (:user_id, :name, :description, :created_at)");
        $stmt->execute([
            'user_id' => $project->getUserId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'created_at' => $project->getCreatedAt()
        ]);
        $project->setId($this->pdo->lastInsertId());
        return $project;
    }

    public function update(Project $project) {
        $stmt = $this->pdo->prepare("UPDATE projects SET name = :name, description = :description WHERE id = :id");
        $stmt->execute([
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'id' => $project->getId()
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}
