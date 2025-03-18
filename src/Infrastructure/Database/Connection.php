<?php

namespace App\Infrastructure\Database;

use MongoDB\Database;

class Connection
{
    private Database $database;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function getDatabase(): Database
    {
        return $this->database;
    }

    public function getCollection(string $name)
    {
        return $this->database->selectCollection($name);
    }
}
