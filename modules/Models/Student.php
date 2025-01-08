<?php

namespace Blog\Models;

use Includes\Database;
use PDO;

class Student extends Model {
    private Database $db;

    public function __construct(Database $db) {
        parent::__construct($db);
        $this->db = $db;
    }

    /**
     * Recherche des termes correspondants dans la base de données en fonction des paramètres fournis dans le POST.
     *
     * @return array -Tableau associatif contenant les résultats de la recherche.
     */

    public function correspondTermsStudent(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);

        $query = "
        SELECT student.student_number, student_name, student_firstname, company_name, internship_identifier
        FROM student
        JOIN internship ON student.student_number = internship.student_number
        WHERE internship_identifier ILIKE :searchTerm
        ORDER BY company_name ASC
        LIMIT 5
        ";
        $searchTerm = "$searchTerm%";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Trouve dans le DB les termes correspondant(LIKE)
     * On utilise le POST, avec search qui correspond à la recherche
     * et searchType au type de recherche (studentId, name, ...)
     * @return array tout les termes correspendants
     */
    public function correspondTerms(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $searchType = $_POST['searchType'] ?? 'numeroEtudiant';
        $pdo = $this->db;

        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' & ', explode(' ', $searchTerm));
        $query = '';

        if ($searchType === 'studentNumber') {
            $query = "
            SELECT student_number, student_name, student_firstname,
            ts_rank_cd(to_tsvector('french', student_number), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student
            WHERE student_number ILIKE :searchTerm
            ORDER BY student_number
            LIMIT 5
        ";
            $searchTerm = "$searchTerm%";
        } elseif ($searchType === 'name') {
            $query = "
            SELECT student_number, student_name, student_firstname,
            ts_rank_cd(to_tsvector('french', student_name || ' ' || student_firstname), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student
            WHERE student_name ILIKE :searchTerm OR student_firstname ILIKE :searchTerm
            ORDER BY rank DESC
            LIMIT 5
            ";
            $searchTerm = "%$searchTerm%";
        } elseif ($searchType === 'company') {
            $query = "
            SELECT student.student_number, student_name, student_firstname, company_name,
            ts_rank_cd(to_tsvector('french', internship.company_name), to_tsquery('french', :searchTerm), 32) AS rank
            FROM student JOIN internship ON student.student_number = internship.student_number
            WHERE company_name ILIKE :searchTerm
            ORDER BY rank DESC
            LIMIT 5
            ";
            $searchTerm = "$searchTerm%";
        }

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les départements dont fait partie l'étudiant passé en paramètre
     * @param string $student numéro de l'étudiant dont on récupère les départements
     * @return false|array tableau contenant les départements dont l'étudiant fait partie s'il en a, false sinon
     */
    public function getDepStudent(string $student): false|array {
        $query = 'SELECT department_name
                    FROM study_at
                    WHERE student_number = :student';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}