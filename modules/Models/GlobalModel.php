<?php

namespace Blog\Models;

use Database;
use PDOException;

class GlobalModel {
    public function __construct(Database $db){
        $this->db = $db;
    }





}