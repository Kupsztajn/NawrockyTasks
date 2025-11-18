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
}
