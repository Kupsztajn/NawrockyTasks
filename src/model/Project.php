<?php

class Project {
    private $id;
    private $user_id;
    private $name;
    private $description;
    private $image;
    private $created_at;

    public function __construct($id = null, $user_id = null, $name = null, $description = null, $image = null, $created_at = null) {
        $this->id = $id;
        $this->user_id = $user_id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->created_at = $created_at ?: date('Y-m-d H:i:s');
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getUserId() {
        return $this->user_id;
    }

    public function setUserId($user_id) {
        $this->user_id = $user_id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getDescription() {
        return $this->description;
    }

    public function setDescription($description) {
        $this->description = $description;
    }

    public function getCreatedAt() {
        return $this->created_at;
    }

    public function setCreatedAt($created_at) {
        $this->created_at = $created_at;
    }

    public function getImage() {
        return $this->image;
    }

    public function setImage($image) {
        $this->image = $image;
    }
}
