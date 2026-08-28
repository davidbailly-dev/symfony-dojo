<?php

namespace App\Entity;

class Book {
    private $id;
    private $title;
    private $description;

    public function __construct(int $id, string $title, string $description) {
        $this->id = $id;
        $this-> title = $title;
        $this->description = $description;
    }

    public function getId() {
        return $this->id;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getDescription() {
        return $this->description;
    }
}