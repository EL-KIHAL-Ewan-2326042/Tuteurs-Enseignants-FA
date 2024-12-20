<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Account {
    private Database $db;

    public function __construct(Database $db) {
        $this->db = $db;
    }

    /**
     * Récupère les informations relatives aux stages et alternances à venir ou en cours dont l'enseignant passé en paramètre est le tuteur
     * @param string $teacher numéro de l'enseignant
     * @return false|array tableau contenant le nom de l'entreprise, son adresse, le sujet du stage, son type, le nom et prénom de l'étudiant, sa formation et son groupe, false sinon
     */
    public function getInterns(string $teacher): false|array {
        $query = 'SELECT company_name, internship_subject, address, student_name, student_firstname, type, formation, class_group
                    FROM internship
                    JOIN student ON internship.student_number = student.student_number
                    WHERE id_teacher = :teacher
                    AND end_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCountInternsPerType(array $interns, &$internship, &$alternance): void {
        $internship = 0;
        $alternance = 0;
        if (empty($interns)) return;

        foreach ($interns as $intern) {
            if ($intern['type'] == 'Internship') ++$internship;
            if ($intern['type'] == 'alternance') ++$alternance;
        }
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
}