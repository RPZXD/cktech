<?php
namespace App\Models;

use PDO;
use PDOException;

class Subject
{
    protected $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    
}
