<?php

require_once 'src/model/User.php';

class UserRepository {
    private $pdo;

    public function __construct() {
        $this->pdo = new PDO('pgsql:host=db;port=5432;dbname=nawrockytasks', 'nawrocky', 'nawrocky');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT id, email, password, created_at FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User($row['id'], $row['email'], $row['password'], $row['created_at']);
        }
        return null;
    }

    public function findById($id) {
        $stmt = $this->pdo->prepare("SELECT id, email, password, created_at FROM users WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return new User($row['id'], $row['email'], $row['password'], $row['created_at']);
        }
        return null;
    }

    public function save(User $user) {
        $stmt = $this->pdo->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
        $stmt->execute([
            'email' => $user->getEmail(),
            'password' => $user->getPassword()
        ]);
        $user->setId($this->pdo->lastInsertId());
        return $user;
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
        $stmt->execute([
            'password' => $hashedPassword,
            'id' => $userId
        ]);
    }

    public function searchUsers($query) {
        $stmt = $this->pdo->prepare("SELECT id, email FROM users WHERE email ILIKE :query LIMIT 10");
        $stmt->execute(['query' => '%' . $query . '%']);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $users[] = new User($row['id'], $row['email'], null, null);
        }
        return $users;
    }

    public function getUsersByProjectId($projectId) {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT u.id, u.email, u.created_at,
                   CASE 
                       WHEN p.user_id = u.id THEN 'owner'
                       ELSE 'member'
                   END as role
            FROM users u
            LEFT JOIN projects p ON p.id = :project_id AND p.user_id = u.id
            LEFT JOIN project_invitations pi ON pi.invitee_user_id = u.id 
                AND pi.project_id = :project_id 
                AND pi.status = 'accepted'
            WHERE p.user_id = u.id OR (pi.invitee_user_id = u.id AND pi.status = 'accepted')
            ORDER BY role DESC, u.email ASC
        ");
        $stmt->execute(['project_id' => $projectId]);
        $users = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $user = new User($row['id'], $row['email'], null, $row['created_at']);
            $users[] = [
                'user' => $user,
                'role' => $row['role']
            ];
        }
        return $users;
    }
}
