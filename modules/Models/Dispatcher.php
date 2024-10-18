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

        $query = 'SELECT Id_teacher, 0 FROM Teacher JOIN Teaches ON Teacher.Id_Teacher = Teaches.Id_Teaches
                    where Departement_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $listteacherIntership = $stmt->fetchAll();

        $query = 'SELECT Student_number FROM Student JOIN Study_at ON Study_at.Student_number = Student.Student_number
                    where Departement_name = :Role_departement';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':Role_departement', $_SESSION['role_departement']);
        $stmt->execute();
        $listStudent = $stmt->fetchColumn();

        $listFinal = [];
        $listStart = utile($_SESSION['role_departement'], $dicoCoef);
    }

}