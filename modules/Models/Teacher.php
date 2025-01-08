<?php

namespace Blog\Models;

use Includes\Database;
use PDO;
use PDOException;

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

    /**
     * Renvoie un tableau contenant tous les stages à venir des étudiants faisant partie des départements passés en paramètre et n'ayant pas encore de tuteur, et leurs informations
     * Les stages sélectionnés sont uniquement ceux des élèves faisant partie d'au moins un des départements passés en paramètre
     * Les stages n'ont pas encore débuté et n'ont aucun tuteur attribué
     * @param array $departments liste des départements dont on veut récupérer les stages des élèves
     * @param string $identifier identifiant de l'enseignant
     * @return array tableau contenant les informations relatives à chaque stage, le nombre fois où l'enseignant connecté a été le tuteur de l'élève ainsi qu'une note représentant la pertinence du stage pour l'enseignant
     */
    public function getStudentsList(array $departments, string $identifier, Internship $internshipModel, Department $departmentModel): array {
        // on récupère pour chaque élève des départements de $departments les informations de leur prochain stage s'ils ont en un et s'ils n'ont pas encore de tuteur
        $studentsList = array();
        foreach($departments as $department) {
            $newList = $departmentModel->getInternshipsPerDepartment($department);
            if($newList) $studentsList = array_merge($studentsList, $newList);
        }

        // on supprime les doubles s'il y en a
        $studentsList = array_unique($studentsList, 0);

        // on stocke les stages déjà demandés par l'enseignant
        $requests = $internshipModel->getRequests($identifier);
        if(!$requests) $requests = array();

        // pour chaque stage on initialise de nouveaux attributs qui leur sont relatifs
        foreach($studentsList as &$row) {
            // le nombre de stages complétés par l'étudiant
            $internships = $internshipModel->getInternships($row['student_number']);

            // l'année durant laquelle le dernier stage/alternance de l'étudiant a eu lieu avec l'enseignant comme tuteur
            $row['year'] = "";

            // le nombre de fois où l'enseignant a été le tuteur de l'étudiant
            $row['internshipTeacher'] = $internships ? $internshipModel->getInternshipTeacher($internships, $identifier, $row['year']) : 0;

            // true si l'enseignant a déjà demandé à tutorer le stage, false sinon
            $row['requested'] = in_array($row['internship_identifier'], $requests);

            // durée en minute séparant l'enseignant de l'adresse de l'entreprise où l'étudiant effectue son stage
            $row['duration'] = $internshipModel->getDistance($row['internship_identifier'], $identifier, isset($row['id_teacher']));
        }

        return $studentsList;
    }

    /**
     * Renvoie tous les départements de l'enseignant passé en paramètre
     * @param string $teacher_id identifiant de l'enseignant
     * @return false|array tableau contenant tous les départements dont l'enseignant connecté fait partie, false sinon
     */
    public function getDepTeacher(string $teacher_id): false|array {
        $query = 'SELECT DISTINCT department_name
                    FROM has_role
                    WHERE user_id = :teacher_id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

}