<?php

namespace Blog\Models;

use Database;
use PDOException;

class Dispatcher{
    private Database $db;

    public function __construct(Database $db){
        $this->db = $db;
    }

    /**
     * @return array|false
     */
    public function getCriteria()
    {
        $db = $this->db;

        $query = 'SELECT * FROM Backup where user_id = :user_id';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetchAll();
    }


    public function dispatcher(array $dicoCoef){
        $db = $this->db;
        $query = 'SELECT Id_teacher, Maxi_number_trainees FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teaches
                    where Departement_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $listTeacherMax = $stmt->fetchAll();

        $query = 'SELECT Teacher.Id_teacher, 0 FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                     WHERE Department_name = :role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $listteacherIntership = $stmt->fetchAll();

        $query = 'SELECT Teacher.Id_teacher, COUNT(Student_number) FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teacher
                     JOIN Is_responsible ON Teacher.Id_Teacher = Is_responsible.Id_Teacher
                     WHERE Department_name = :role_departement
                     GROUP BY Teacher.Id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $tmp_count_student = $stmt->fetchAll();

        foreach ($tmp_count_student as $count_student) {
            $listteacherIntership[$count_student[0]] = $count_student[1];
        }

        $query = 'SELECT Id_student FROM Is_responsible';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $tmp_student = $stmt->fetchAll();

        $listFinal = [];
        $listStart = utile($_SESSION['role_departement'], $dicoCoef);

        foreach ($listStart as $key => $tuplestart) {
            if (in_array($tuplestart[1], $tmp_student)) {
                unset($listStart[$key]);
            }
        }

        while (count($listStart) == 0){
            $tab_max_table = $listStart[0];
            foreach ($listStart as $association){
                if ($association[2]>$tab_max_table[2]) {
                    $tab_max_table = $association;
                }
            }
            if ($listTeacherMax[$tab_max_table[0]] == $listteacherIntership[$tab_max_table[0]]){
                unset($listStart[array_search($tab_max_table, $listStart)]);
            }
            else {

                $listFinal[] = $tab_max_table;
                unset($listStart[array_search($tab_max_table, $listStart)]);
                if ($tab_max_table[3] == 'alterant') {
                    $listteacherIntership[$tab_max_table[0]] = $listteacherIntership[$tab_max_table[0]] + 2;
                } else {
                    $listteacherIntership[$tab_max_table[0]] = $listteacherIntership[$tab_max_table[0]] + 1;
                }
            }
        }
        return [$listFinal, $listteacherIntership];
    }

}