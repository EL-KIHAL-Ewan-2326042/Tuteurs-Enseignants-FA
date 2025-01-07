<?php

namespace Blog\Models;

use Includes\Database;

class Teacher extends Model {
    private Database $db;
    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * @param string $identifier
     * @return array|null
     */
    public function getFullName(string $identifier): ?array {
        if (empty($identifier)) {
            return null;
        }
        $db = $this->db;
        $query = 'SELECT teacher_name, teacher_firstname FROM teacher WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $identifier);
        $stmt->execute();
        return $stmt->fetch($db->getConn()::FETCH_ASSOC);
    }

    /**
     * Recuperer toute une ligne selon la cle primaire dans la table teacher
     * @param string $identifier l'identifiant du professeur
     * @return false|mixed renvoie la ligne dans la DB
     */
    public function getAddress(string $identifier): false|array {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT address FROM has_address WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetchAll($db->getConn()::FETCH_ASSOC);
    }
}