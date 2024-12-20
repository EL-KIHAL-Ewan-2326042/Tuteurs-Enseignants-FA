<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

class Account {
    private Database $db;
    private \Blog\Models\GlobalModel $globalModel;

    public function __construct(Database $db, \Blog\Models\GlobalModel $globalModel){
        $this->db = $db;
        $this->globalModel = $globalModel;
    }

    /**
     * Récupère les informations relatives aux stages et alternances à venir ou en cours dont l'enseignant passé en paramètre est le tuteur
     * @param string $teacher numéro de l'enseignant
     * @return array tableau (pouvant être vide s'il n'y a aucun résultat ou qu'il y a eu une erreur) contenant le nom de l'entreprise, son adresse, le sujet du stage, son type, le nom et prénom de l'étudiant, sa formation et son groupe
     */
    public function getInterns(string $teacher): array {
        $query = 'SELECT company_name, internship_subject, address, student_name, student_firstname, type, formation, class_group, internship.student_number, internship_identifier, id_teacher
                    FROM internship
                    JOIN student ON internship.student_number = student.student_number
                    WHERE id_teacher = :teacher
                    AND end_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$studentsList) return array();

        foreach($studentsList as &$row) {
            // le nombre de stages complétés par l'étudiant
            $internships = $this->globalModel->getInternships($row['student_number']);

            // l'année durant laquelle le dernier stage/alternance de l'étudiant a eu lieu avec l'enseignant comme tuteur
            $row['year'] = "";

            // le nombre de fois où l'enseignant a été le tuteur de l'étudiant
            $row['internshipTeacher'] = $internships ? $this->globalModel->getInternshipTeacher($internships, $teacher, $row['year']) : 0;

            // durée en minute séparant l'enseignant de l'adresse de l'entreprise où l'étudiant effectue son stage
            $row['duration'] = $this->globalModel->getDistance($row['internship_identifier'], $teacher, isset($row['id_teacher']));
        }

        return $studentsList;
    }

    public function getCountInternsPerType(array $interns, &$internship, &$alternance): void {
        $internship = 0;
        $alternance = 0;
        if (empty($interns)) return;

        foreach ($interns as $intern) {
            if (strtolower($intern['type']) == 'internship') ++$internship;
            if (strtolower($intern['type']) == 'alternance') ++$alternance;
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