<?php

namespace Blog\Models;

use Includes\Database;
use PDO;

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
    public function getAddress(string $Id_teacher): false|array {

        $db = $this->db;
        $query = 'SELECT address FROM has_address WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $Id_teacher);
        $stmt->execute();

        return $stmt->fetchAll($db->getConn()::FETCH_ASSOC);
    }

    /**
     * Récupère une liste des identifiants des enseignants associés aux départements du rôle de l'admin.
     *
     * @return array|false Tableau contenant les identifiants des enseignants ou `false` en cas d'erreur si aucun enseignant n'est trouvé pour les départements spécifiés.
     */
    public function createListTeacher() {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher FROM Teacher JOIN Has_role ON Teacher.Id_Teacher = Has_role.User_id
                    where Department_name IN ($placeholders)";

        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Recherche des termes correspondants dans la base de données en fonction des paramètres fournis dans le POST.
     *
     * @return array -Tableau associatif contenant les résultats de la recherche.
     */

    public function correspondTermsTeacher(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);

        $query = "
            SELECT id_teacher, teacher_name, teacher_firstname
            FROM teacher
            WHERE id_teacher ILIKE :searchTerm
            ORDER BY id_teacher ASC
            LIMIT 5
        ";

        $searchTerm = "$searchTerm%";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le nombre maximum de stagiaires et alternants que l'enseignant passé en paramètre peut avoir
     * @param string $teacher numéro de l'enseignant
     * @return false|string nombre maximum de stagiaires et alternants, sinon false
     */
    public function getMaxNumberInterns(string $teacher): false|string {
        $query = 'SELECT maxi_number_trainees
                    FROM teacher
                    WHERE id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Met à jour le nombre maximum de stagiaires et alternants que l'enseignant passé en paramètre peut avoir
     * @param string $teacher numéro de l'enseignant
     * @param int $maxi_number_trainees nouveau nombre maximum de stagiaires et alternants
     * @return true|string renvoie true si l'update a fonctionné, sinon l'erreur dans un string
     */
    public function updateMaxiNumberTrainees(string $teacher, int $maxi_number_trainees): true|string {
        $query = 'UPDATE teacher
                    SET maxi_number_trainees = :maxi_number_trainees
                    WHERE id_teacher = :teacher';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':maxi_number_trainees', $maxi_number_trainees);
        $stmt->bindParam(':teacher', $teacher);

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }


    /**
     * Renvoie le ou les disiplines d'un professeur
     * @param string $id_teahcer identifiant du prof
     * @return array|false result de la requete
     */
    public function getDisciplines(string $id_teahcer) {
        $pdo = $this->db;

        $query = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindParam(':id', $id_teahcer);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}