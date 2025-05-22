<?php
/**
 * Fichier contenant le modèle associé aux enseignants
 *
 * PHP version 8.3
 *
 * @category Model
 * @package  TutorMap/modules/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Models;

use includes\Database;
use PDO;
use PDOException;

/**
 * Classe gérant toutes les fonctionnalités du site associées
 * aux enseignants. Elle hérite de la classe 'Model'
 *
 * PHP version 8.3
 *
 * @category Model
 * @package  TutorMap/modules/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Teacher extends Model
{
    private Database $_db;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Database $db Instance de la classe Database
     *                     servant de lien avec la base de données
     */
    public function __construct(Database $db)
    {
        parent::__construct($db);
        $this->_db = $db;
    }

    /**
     * Récupère le nom et le prénom de l'enseignant passé en paramètre
     *
     * @param string $identifier Identifiant de l'enseignant
     *
     * @return array|null Renvoie null si l'identifiant est vide,
     * sinon renvoie une liste contenant le nom et le prénom de
     * l'enseignant s'il les trouve, false sinon
     */
    public function getFullName(string $identifier): ?array
    {
        if (empty($identifier)) {
            return null;
        }
        $db = $this->_db;
        $query = 'SELECT teacher_name, teacher_firstname '
                    . 'FROM teacher '
                    . 'WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $identifier);
        $stmt->execute();
        return $stmt->fetch($db->getConn()::FETCH_ASSOC);
    }

    /**
     * Récupère les adresses de l'enseignant passé en paramètre
     *
     * @param string $id_teacher Identifiant de l'enseignant
     *
     * @return false|array Renvoie une liste contenant les adresses de l'enseignant,
     * false sinon
     */
    public function getAddress(string $id_teacher): false|array
    {

        $db = $this->_db;
        $query = 'SELECT address FROM has_address WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $id_teacher);
        $stmt->execute();

        return $stmt->fetchAll($db->getConn()::FETCH_ASSOC);
    }

    /**
     * Récupère une liste des identifiants des enseignants
     * associés aux départements du rôle de l'admin
     *
     * @return array|false Tableau contenant les identifiants des enseignants,
     * ou `false` en cas d'erreur si aucun enseignant n'est trouvé
     * pour les départements spécifiés.
     */
    public function createListTeacher(): false|array
    {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher "
                . "FROM Teacher "
                . "JOIN Has_role ON Teacher.Id_Teacher = Has_role.User_id "
                . "where Department_name IN ($placeholders)";

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Recherche des termes correspondants dans la base de données
     * en fonction des paramètres fournis dans le POST
     *
     * @return array|false Renvoie un tableau associatif contenant
     * les résultats de la recherche, false sinon
     */
    public function correspondTermsTeacher(): array|false
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->_db;

        $searchTerm = trim($searchTerm);

            $query
                = "SELECT id_teacher, teacher_name, teacher_firstname "
                . "FROM teacher "
                . "WHERE teacher_name ILIKE :searchTerm "
                . "OR teacher_firstname ILIKE :searchTerm "
                . "ORDER BY id_teacher ASC";

        $searchTerm = "$searchTerm%";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère le nombre maximum de stagiaires et alternants
     * que l'enseignant passé en paramètre peut avoir
     *
     * @param string $teacher Identifiant de l'enseignant
     *
     * @return false|array Renvoie le nombre maximum de stagiaires et alternants,
     * false sinon
     */
    public function getMaxNumberTrainees(string $teacher): false|array
    {
        $query = 'SELECT maxi_number_intern AS intern, '
                . 'maxi_number_apprentice AS apprentice '
                . 'FROM teacher '
                . 'WHERE id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Met à jour le nombre maximum de stagiaires et alternants
     * que l'enseignant passé en paramètre peut avoir
     *
     * @param string $teacher    numéro de l'enseignant
     * @param int    $intern     nouveau nombre maximum de stagiaires
     * @param int    $apprentice nouveau nombre maximum d'alternants
     *
     * @return bool|string renvoie true si l'update a fonctionné,
     * false si les nouvelles valeurs maximales passées en paramètre sont nulles,
     * sinon une chaîne de caractères contenant le message d'erreur
     */
    public function updateMaxiNumberTrainees(
        string $teacher, int $intern, int $apprentice
    ): bool|string {
        if (!($intern > 0 || $apprentice > 0)) {
            return false;
        }

        $query = 'UPDATE teacher SET ';
        if ($intern > 0) {
            $query .= 'maxi_number_intern = :intern';
            if ($apprentice > 0) {
                $query .= ', maxi_number_apprentice = :apprentice';
            }
        } else {
            $query .= 'maxi_number_apprentice = :apprentice';
        }
        $query .= ' WHERE id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        if ($intern > 0) {
            $stmt->bindParam(':intern', $intern);
        }
        if ($apprentice > 0) {
            $stmt->bindParam(':apprentice', $apprentice);
        }

        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }


    /**
     * Renvoie les disiplines qu'enseigne l'enseignant passé en paramètre
     *
     * @param string $id_teacher Identifiant du prof
     *
     * @return array|false result de la requete
     */
    public function getDisciplines(string $id_teacher): false|array
    {
        $pdo = $this->_db;

        $query = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindParam(':id', $id_teacher);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Renvoie un tableau contenant tous les stages à venir des étudiants faisant
     * partie des départements passés en paramètre et n'ayant pas encore de tuteur,
     * et leurs informations. Les stages sélectionnés sont uniquement ceux des
     * étudiants faisant partie d'au moins un des départements passés en paramètre.
     * Les stages n'ont pas encore débuté et n'ont aucun tuteur attribué
     *
     * @param array      $departments     Liste des départements dont on
     *                                    veut récupérer les stages des
     *                                    étudiants
     * @param string     $identifier      Identifiant de l'enseignant
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     * @param Department $departmentModel Instance de la classe Department
     *                                    servant de modèle
     *
     * @return array Renvoie un tableau contenant les informations relatives à chaque
     * stage, le nombre fois où l'enseignant connecté a été le tuteur de l'étudiant
     * ainsi qu'une note représentant la pertinence du stage pour l'enseignant
     */
    public function getStudentsList(
        array $departments,
        string $identifier,
        Internship $internshipModel,
        Department $departmentModel
    ): array {
        // on récupère pour chaque étudiant des départements de $departments
        // les informations de leur prochain stage s'ils ont en un et
        // s'ils n'ont pas encore de tuteur
        $studentsList = array();
        foreach ($departments as $department) {
            $newList = $departmentModel->getInternshipsPerDepartment($department);
            if ($newList) {
                $studentsList = array_merge($studentsList, $newList);
            }
        }

        // on supprime les doubles s'il y en a
        $studentsList = array_unique($studentsList, 0);

        // on stocke les stages déjà demandés par l'enseignant
        $requests = $internshipModel->getRequests($identifier);
        if (!$requests) {
            $requests = array();
        }

        // pour chaque stage on initialise
        // de nouveaux attributs qui leur sont relatifs
        foreach ($studentsList as &$row) {
            // le nombre de stages complétés par l'étudiant
            $internships = $internshipModel->getInternships($row['student_number']);

            // l'année durant laquelle le dernier stage/alternance
            // de l'étudiant a eu lieu avec l'enseignant comme tuteur
            $row['year'] = "";

            // le nombre de fois où l'enseignant a été le tuteur de l'étudiant
            $row['internshipTeacher'] = $internships ? $internshipModel
                ->getInternshipTeacher($internships, $identifier, $row['year']) : 0;

            // true si l'enseignant a déjà demandé à tutorer le stage, false sinon
            $row['requested'] = in_array($row['internship_identifier'], $requests);

            // durée en minute séparant l'enseignant de l'adresse
            // de l'entreprise où l'étudiant effectue son stage
            $row['duration'] = $internshipModel->getDistance(
                $row['internship_identifier'], $identifier, isset($row['id_teacher'])
            );
        }

        return $studentsList;
    }

    /**
     * Récupère tous les départements où enseigne l'enseignant passé en paramètre
     *
     * @param string $teacher_id Identifiant de benignant
     *
     * @return false|array Renvoie un tableau contenant tous les départements dont
     * l'enseignant connecté fait partie, false sinon
     */
    public function getDepTeacher(string $teacher_id): false|array
    {
        $query = 'SELECT DISTINCT department_name '
                . 'FROM has_role '
                . 'WHERE user_id = :teacher_id';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher_id', $teacher_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les stages disponibles pour l'enseignant, avec pagination,
     * recherche et tri pour DataTables
     *
     * @param array  $departments Départements sélectionnés
     * @param string $identifier  Identifiant de l'enseignant connecté
     * @param int    $start       Index de départ pour la pagination
     * @param int    $length      Nombre d'éléments à récupérer
     * @param string $search      Texte de recherche global
     * @param array  $order       Informations de tri [column, direction]
     *
     * @return array Tableau des stages avec les informations associées
     */
    public function paginate(
        array $departments,
        string $identifier,
        int $start,
        int $length,
        string $search = '',
        array $order = []
    ): array {
        // Liste des colonnes pour le tri
        $columns = [
            0 => 'student.student_name',
            1 => 'student.formation',
            2 => 'student.class_group',
            3 => null, // Historique - pas directement triable via SQL
            4 => 'internship.company_name',
            5 => 'internship.internship_subject',
            6 => 'internship.address',
            7 => null, // Position/Durée - pas directement triable via SQL
            8 => null  // Choix - pas directement triable via SQL
        ];

        // Construit les placeholders pour les départements
        $placeholders = implode(',', array_fill(0, count($departments), '?'));
        $params = $departments;

        // Construction de la requête SQL de base
        $query = 'SELECT DISTINCT internship.internship_identifier, internship.company_name, '
                . 'internship.internship_subject, internship.address, '
                . 'internship.student_number, internship.type, '
                . 'student.student_name, student.student_firstname, '
                . 'student.formation, student.class_group '
                . 'FROM internship '
                . 'JOIN student ON internship.student_number = student.student_number '
                . 'JOIN study_at ON internship.student_number = study_at.student_number '
                . 'WHERE study_at.department_name IN (' . $placeholders . ') '
                . 'AND internship.id_teacher IS NULL '
                . 'AND internship.end_date_internship > CURRENT_DATE';

        // Ajout de la condition de recherche
        if (!empty($search)) {
            $query .= ' AND (student.student_name ILIKE ? OR '
                    . 'student.student_firstname ILIKE ? OR '
                    . 'student.formation ILIKE ? OR '
                    . 'student.class_group ILIKE ? OR '
                    . 'internship.company_name ILIKE ? OR '
                    . 'internship.internship_subject ILIKE ? OR '
                    . 'internship.address ILIKE ?)';
            $searchParam = '%' . $search . '%';
            $params = array_merge($params, array_fill(0, 7, $searchParam));
        }

        // Ajout de l'ordre de tri
        if (!empty($order) && isset($order['column']) &&
            isset($columns[$order['column']]) && $columns[$order['column']] !== null) {
            $query .= ' ORDER BY ' . $columns[$order['column']] . ' '
                    . (strtoupper($order['dir']) === 'DESC' ? 'DESC' : 'ASC');
        } else {
            $query .= ' ORDER BY student.student_name ASC';
        }

        // Ajout de la pagination
        $query .= ' LIMIT ? OFFSET ?';
        $params[] = $length;
        $params[] = $start;

        // Exécution de la requête
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Enrichir les résultats avec des informations supplémentaires
        $internshipModel = new Internship($this->_db);
        $requests = $internshipModel->getRequests($identifier);
        if (!$requests) {
            $requests = [];
        }

        foreach ($results as &$row) {
            // Historique de tutorat
            $internships = $internshipModel->getInternships($row['student_number']);
            $row['year'] = "";
            $row['internshipTeacher'] = $internships ?
                $internshipModel->getInternshipTeacher(
                    $internships, $identifier, $row['year']
                ) : 0;

            // Si l'enseignant a déjà demandé à tutorer ce stage
            $row['requested'] = in_array($row['internship_identifier'], $requests);

            // Durée/distance depuis l'adresse de l'enseignant
            $row['duration'] = $internshipModel->getDistance(
                $row['internship_identifier'],
                $identifier,
                isset($row['id_teacher'])
            );
        }

        return $results;
    }

    /**
     * Compte le nombre total de stages disponibles sans filtrage
     *
     * @param array $departments Départements sélectionnés
     *
     * @return int Nombre total de stages
     */
    public function countAll(array $departments): int
    {
        $placeholders = implode(',', array_fill(0, count($departments), '?'));

        $query = 'SELECT COUNT(DISTINCT internship.internship_identifier) '
                . 'FROM internship '
                . 'JOIN study_at ON internship.student_number = study_at.student_number '
                . 'WHERE study_at.department_name IN (' . $placeholders . ') '
                . 'AND internship.id_teacher IS NULL '
                . 'AND internship.end_date_internship > CURRENT_DATE';

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($departments);

        return (int)$stmt->fetchColumn();
    }

    /**
     * Compte le nombre de stages disponibles après application des filtres
     *
     * @param array  $departments Départements sélectionnés
     * @param string $search      Texte de recherche global
     *
     * @return int Nombre de stages après filtrage
     */
    public function countFiltered(array $departments, string $search = ''): int
    {
        $placeholders = implode(',', array_fill(0, count($departments), '?'));
        $params = $departments;

        $query = 'SELECT COUNT(DISTINCT internship.internship_identifier) '
                . 'FROM internship '
                . 'JOIN student ON internship.student_number = student.student_number '
                . 'JOIN study_at ON internship.student_number = study_at.student_number '
                . 'WHERE study_at.department_name IN (' . $placeholders . ') '
                . 'AND internship.id_teacher IS NULL '
                . 'AND internship.end_date_internship > CURRENT_DATE';

        if (!empty($search)) {
            $query .= ' AND (student.student_name ILIKE ? OR '
                    . 'student.student_firstname ILIKE ? OR '
                    . 'student.formation ILIKE ? OR '
                    . 'student.class_group ILIKE ? OR '
                    . 'internship.company_name ILIKE ? OR '
                    . 'internship.internship_subject ILIKE ? OR '
                    . 'internship.address ILIKE ?)';
            $searchParam = '%' . $search . '%';
            $params = array_merge($params, array_fill(0, 7, $searchParam));
        }

        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($params);

        return (int)$stmt->fetchColumn();
    }
}
