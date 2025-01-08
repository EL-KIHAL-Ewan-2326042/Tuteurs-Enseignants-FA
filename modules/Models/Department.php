<?php

namespace Blog\Models;

use Includes\Database;
use PDO;

class Department extends Model {
    private Database $db;

    public function __construct(Database $db) {
        parent::__construct($db);
        $this->db = $db;
    }

    /**
     * Renvoie un tableau contenant les stages des élèves du département passé en paramètre et leurs informations à condition que les stages ne soient ni passés et qu'aucun tuteur ne leur soit attribué
     * @param string $department le département duquel les élèves sélectionnés font partie
     * @return false|array tableau contenant le numéro, le nom et le prénom de l'élève, ainsi que le nom de l'entreprise dans lequel il va faire son stage, le sujet et le numéro du stage, false sinon
     */
    public function getInternshipsPerDepartment(string $department): false|array {
        $query = 'SELECT internship_identifier, internship.company_name, internship.internship_subject, internship.address, internship.student_number, internship.type, student.student_name, student.student_firstname, student.formation, student.class_group
                    FROM internship
                    JOIN student ON internship.student_number = student.student_number
                    JOIN study_at ON internship.student_number = study_at.student_number
                    WHERE department_name = :department_name
                    AND id_teacher IS NULL
                    AND start_date_internship > CURRENT_DATE';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':department_name', $department);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}