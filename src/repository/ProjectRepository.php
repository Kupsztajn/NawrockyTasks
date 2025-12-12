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
            $projects[] = new Project($row['id'], $row['user_id'], $row['name'], $row['description'], $row['image'], $row['created_at']);
        }
        return $projects;
    }

    public function save(Project $project) {
        $stmt = $this->pdo->prepare("INSERT INTO projects (user_id, name, description, image, created_at) VALUES (:user_id, :name, :description, :image, :created_at)");
        $stmt->execute([
            'user_id' => $project->getUserId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'image' => $project->getImage(),
            'created_at' => $project->getCreatedAt()
        ]);
        $project->setId($this->pdo->lastInsertId());
        return $project;
    }

    public function update(Project $project) {
        $stmt = $this->pdo->prepare("UPDATE projects SET name = :name, description = :description, image = :image WHERE id = :id");
        $stmt->execute([
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'image' => $project->getImage(),
            'id' => $project->getId()
        ]);
    }

    public function delete($id) {
        $stmt = $this->pdo->prepare("DELETE FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    public function getProjectById($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM projects WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new Project($row['id'], $row['user_id'], $row['name'], $row['description'], $row['image'], $row['created_at']);
        }
        return null;
    }
}
