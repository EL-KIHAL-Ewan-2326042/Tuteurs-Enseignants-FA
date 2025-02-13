<?php
/**
 * Fichier contenant le modèle associé aux informations des stages/alternances
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

use Includes\Database;
use PDO;
use PDOException;

/**
 * Classe gérant toutes les fonctionnalités du site associées
 * aux informations des stages/alternances. Elle hérite de la classe 'Model'
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
class Internship extends Model
{
    private Database $_db;

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Database $_db Instance de la classe Database
     *                      servant de lien avec la base de données
     */
    public function __construct(Database $_db)
    {
        parent::__construct($_db);
        $this->_db = $_db;
    }

    /**
     * Renvoie un tableau contenant les informations de
     * chaque tutorat terminé de l'élève passé en paramètre
     *
     * @param string $student le numéro de l'étudiant dont on
     *                        récupère les informations
     *
     * @return false|array tableau contenant, pour chaque tutorat,
     * le numéro d'enseignant du tuteur, le numéro de l'élève et les dates,
     * false sinon
     */
    public function getInternships(string $student): false|array
    {
        $query = 'SELECT id_teacher, student_number, '
                    . 'Start_date_internship, End_date_internship '
                    . 'FROM internship '
                    . 'WHERE student_number = :student '
                    . 'AND end_date_internship < CURRENT_DATE '
                    . 'AND id_teacher IS NOT NULL '
                    . 'ORDER BY start_date_internship ASC';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie le nombre de fois où l'enseignant passé
     * en paramètre a été tuteur dans le tableau passé en paramètre
     *
     * @param array  $internshipStudent Tableau renvoyé par la méthode
     *                                  'getInternships()'
     * @param string $teacher           Numéro de l'enseignant
     * @param string $year              Dernière année durant laquelle
     *                                  l'enseignant a été tuteur
     *
     * @return int nombre de fois où l'enseignant connecté a été tuteur
     * dans le tablau passé en paramètre
     */
    public function getInternshipTeacher(
        array $internshipStudent,
        string $teacher,
        string &$year
    ): int {
        $internshipTeacher = 0;
        foreach ($internshipStudent as $row) {
            if ($row['id_teacher'] == $teacher) {
                ++$internshipTeacher;
                if ($internshipTeacher == 1) {
                    $year = substr($row['start_date_internship'], 0, 4);
                }
            }
        }
        return $internshipTeacher;
    }

    /**
     * Récupère les informations relatives aux stages et alternances
     * à venir ou en cours dont l'enseignant passé en paramètre est le tuteur
     *
     * @param string $teacher Numéro de l'enseignant
     *
     * @return array Renvoie un tableau (pouvant être vide s'il n'y a aucun résultat
     * ou qu'il y a eu une erreur) contenant le nom de l'entreprise, son adresse, le
     * sujet du stage, son type, le nom et prénom de l'étudiant,
     * sa formation et son groupe
     */
    public function getInterns(string $teacher): array
    {
        $query = 'SELECT company_name, internship_subject, address, student_name, '
                    . 'student_firstname, type, formation, class_group, '
                    . 'internship.student_number, internship_identifier, id_teacher '
                    . 'FROM internship '
                    . 'JOIN student '
                        . 'ON internship.student_number = student.student_number '
                    . 'WHERE id_teacher = :teacher '
                    . 'AND end_date_internship > CURRENT_DATE';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        $studentsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$studentsList) {
            return array();
        }

        foreach ($studentsList as &$row) {
            // le nombre de stages complétés par l'étudiant
            $internships = $this->getInternships($row['student_number']);

            // l'année durant laquelle le dernier stage/alternance
            // de l'étudiant a eu lieu avec l'enseignant comme tuteur
            $row['year'] = "";

            // le nombre de fois où l'enseignant a été le tuteur de l'étudiant
            $row['internshipTeacher'] = $internships ? $this->getInternshipTeacher(
                $internships,
                $teacher,
                $row['year']
            ) : 0;

            // durée en minute séparant l'enseignant de l'adresse
            // de l'entreprise où l'étudiant effectue son stage
            $row['duration'] = $this->getDistance(
                $row['internship_identifier'],
                $teacher,
                isset($row['id_teacher'])
            );
        }

        return $studentsList;
    }

    /**
     * Calcule la distance entre un stage et un enseignant
     *
     * @param string $internship_identifier L'identifiant du stage
     * @param string $id_teacher            L'identifiant du professeur
     * @param bool   $bound                 True si un enseignant est déjà associé
     *                                      au stage, false sinon
     *
     * @return int distance en minute entre les deux
     */
    public function getDistance(
        string $internship_identifier,
        string $id_teacher,
        bool $bound
    ): int {

        $query = 'SELECT * '
                    . 'FROM Distance '
                    . 'WHERE internship_identifier = :idInternship '
                    . 'AND id_teacher = :idTeacher';
        $stmt0 = $this->_db->getConn()->prepare($query);
        $stmt0->bindParam(':idInternship', $internship_identifier);
        $stmt0->bindParam(':idTeacher', $id_teacher);
        $stmt0->execute();

        $minDuration = $stmt0->fetchAll(PDO::FETCH_ASSOC);
        if ($minDuration) {
            return $minDuration[0]['distance'];
        }

        $query = 'SELECT Address '
                    . 'FROM Internship '
                    . 'WHERE internship_identifier = :internship_identifier';
        $stmt1 = $this->_db->getConn()->prepare($query);
        $stmt1->bindParam(':internship_identifier', $internship_identifier);
        $stmt1->execute();
        $addressInternship = $stmt1->fetch(PDO::FETCH_ASSOC);

        $query = 'SELECT Address FROM Has_address WHERE Id_teacher = :idTeacher';
        $stmt2 = $this->_db->getConn()->prepare($query);
        $stmt2->bindParam(':idTeacher', $id_teacher);
        $stmt2->execute();
        $addressesTeacher = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        $latLngStudent = $this->geocodeAddress($addressInternship['address']);

        $minDuration = PHP_INT_MAX;

        foreach ($addressesTeacher as $address) {
            $latLngTeacher = $this->geocodeAddress($address['address']);

            if (!$latLngStudent || !$latLngTeacher) {
                continue;
            }

            $duration = $this->calculateDuration($latLngStudent, $latLngTeacher);

            if ($duration < $minDuration) {
                (int) $minDuration = $duration;
            }
        }

        if (!$minDuration || $minDuration > 999999) {
            return 60;
        }

        if (!$bound) {
            $query = 'INSERT INTO Distance '
                     . '(id_teacher, internship_identifier, distance) '
                     . 'VALUES (:id_teacher, :id_internship, :distance) '
                     . 'ON CONFLICT (id_teacher, internship_identifier) '
                     . 'DO UPDATE SET distance = EXCLUDED.distance;';

            $stmt3 = $this->_db->getConn()->prepare($query);
            $stmt3->bindParam(':id_teacher', $id_teacher);
            $stmt3->bindParam(':id_internship', $internship_identifier);
            $stmt3->bindParam(':distance', $minDuration);
            $stmt3->execute();
        }

        return $minDuration;
    }

    /**
     * Permet de mettre à jour le nombre de stage ou alternance selon le type
     *
     * @param array $trainees   la liste de stage/alternance
     * @param int   $internship le nombre de stage
     * @param int   $alternance le nombre d'alternance
     *
     * @return void
     */
    public function getCountInternsPerType(array $trainees,
        int   &$internship, int &$alternance
    ): void {
        $internship = 0;
        $alternance = 0;
        if (empty($trainees)) {
            return;
        }

        foreach ($trainees as $trainee) {
            if (strtolower($trainee['type']) == 'internship') {
                ++$internship;
            }
            if (strtolower($trainee['type']) == 'alternance') {
                ++$alternance;
            }
        }
    }

    /**
     * Calcule la pertinence des stages pour un professeur
     * et des stages en fonction de plusieurs critères de pondération.
     *
     * @param Department $departmentModel le modèmle de la table departement
     * @param Teacher    $teacherModel    le modèle de la table
     *                                    professeur
     * @param array      $teacher         la liste des professeur
     * @param array      $dictCoef        Tableau associatif des critères de calcul
     *                                    et leurs coefficients
     * 
     * @return array|array[] Tableau d'associations
     * ('id_teacher', 'internship_identifier', 'score' et type')
     */
    public function relevanceTeacher(Department $departmentModel,
        Teacher $teacherModel, array $teacher, array $dictCoef
    ): array {
        $identifier = $teacher['id_teacher'];

        $internshipList = array();
        $departments = $teacherModel->getDepTeacher($identifier);
        foreach ($departments as $listDepTeacher) {
            foreach ($listDepTeacher as $department) {
                $newList = $departmentModel
                    ->getInternshipsPerDepartment($department);
                if ($newList) {
                    $internshipList = array_merge($internshipList, $newList);
                }
            }
        }

        $result = array();

        foreach ($internshipList as $internship) {
            $result[] = $this->calculateRelevanceTeacherStudentsAssociate(
                $teacher, $dictCoef, $internship
            );
        }

        if (!empty($result)) {
            return $result;
        }
        return [[]];
    }

    /**
     * Permet de calculer pour un stage/alternance
     * le score avec tous les professeur de son departement
     *
     * @param string $internship l'identifiant du stage/alternance
     * @param array  $dictCoef   Tableau associatif des critères
     *                           de calcul et leurs coefficients
     *
     * @return array|array[] Tableau d'associations
     */
    public function relevanceInternship(string $internship, array $dictCoef): array
    {
        $_db = $this->_db;

        $query= "SELECT Teacher.Id_teacher, Teacher.teacher_name, 
                Teacher.teacher_firstname,
                SUM(CASE WHEN internship.type = 'alternance' THEN 1 ELSE 0 END) 
                    AS current_count_apprentice, 
                SUM(CASE WHEN internship.type = 'Internship' THEN 1 ELSE 0 END) 
                    AS current_count_intern FROM Teacher  
                JOIN has_role ON Teacher.Id_teacher = has_role.user_id 
                JOIN Study_at 
                    ON Study_at.department_name = has_role.department_name
                JOIN Student ON Student.student_number = Study_at.student_number
                JOIN INTERNSHIP 
                    ON Internship.student_number = Student.student_number
                WHERE has_role.department_name IN (
                    SELECT department_name FROM Study_at 
                    JOIN Student ON Study_at.student_number = Student.student_number
                    JOIN Internship 
                        ON Internship.student_number = Internship.student_number 
                            WHERE Internship.internship_identifier = :internship
                    GROUP BY department_name) 
                AND Internship.internship_identifier = :internship
                GROUP BY Teacher.Id_teacher, Teacher.teacher_name, 
                         Teacher.teacher_firstname
                HAVING Teacher.Maxi_number_intern > SUM(CASE 
                    WHEN internship.type = 'Internship' THEN 1 ELSE 0 END) 
                   AND Teacher.Maxi_number_apprentice > SUM(CASE 
                       WHEN internship.type = 'alternance' THEN 1 ELSE 0 END)";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $teacherList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $query = "SELECT Internship.Internship_identifier, "
                  . "Internship.Company_name, Internship.Internship_subject, "
                  . "Internship.Address, Internship.Student_number, "
                  . "Internship.Type, Student.Student_name, "
                  . "Student.Student_firstname, Student.Formation, "
                  . "Student.Class_group "
                    . "FROM Internship JOIN Student ON "
                        . "Internship.Student_number = Student.Student_number "
                    . "WHERE Internship.internship_identifier = :internship";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->bindValue(':internship', $internship);
        $stmt->execute();
        $internship = $stmt->fetchAll(PDO::FETCH_ASSOC);


        $result = array();

        foreach ($teacherList as $teacher) {
            $result[] = $this
                ->calculateRelevanceTeacherStudentsAssociate(
                    $teacher, $dictCoef, $internship[0]
                );
        }

        if (!empty($result)) {
            usort($result, fn($a, $b) => $b['score'] <=> $a['score']);
            return $result;
        }
        return [[]];
    }

    /**
     * Renvoie un score associé à la pertinence entre
     * le sujet du stage et les disciplines enseignées
     * par le professeur, tous deux passés en paramètre
     *
     * @param string $internshipId numéro du stage
     * @param string $identifier   identifiant de l'enseignant
     *
     * @return float score associé à la pertinence
     * entre le sujet de stage et les disciplines
     * enseignées par le professeur connecté
     */
    public function scoreDiscipSubject(
        string $internshipId, string $identifier
    ): float {
        $pdo = $this->_db;

        // on récupère les mots-clés relatifs au sujet du stage
        $query = "SELECT keywords "
                    . "FROM internship "
                    . "WHERE internship_identifier = :internshipId";
        $stmt1 = $pdo->getConn()->prepare($query);
        $stmt1->bindParam(':internshipId', $internshipId);
        $stmt1->execute();
        $result = $stmt1->fetch(PDO::FETCH_ASSOC);

        // si on n'a trouvé aucun mot-clé, alors on renvoie 0
        if (!$result) {
            return 0;
        }
        $searchTerm1 = $result['keywords'];

        $searchTerm1 = trim($searchTerm1);
        $tsQuery1 = implode(' | ', explode(' ', $searchTerm1));
        $tsQuery1 = implode(' & ', explode('_', $tsQuery1));

        // on récupère les disciplines enseignées par l'enseignant
        $query = "SELECT discipline_name FROM is_taught WHERE id_teacher = :id";
        $stmt2 = $pdo->getConn()->prepare($query);
        $stmt2->bindParam(':id', $identifier);
        $stmt2->execute();
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // si on n'a trouvé aucune discipline, alors on renvoie 0
        if (!$result) {
            return 0;
        }
        $searchTerm2 = "";

        for ($i = 0; $i < count($result); ++$i) {
            $searchTerm2 .= $result[$i]['discipline_name'];
            if ($i < count($result) - 1) {
                $searchTerm2 .= ' ';
            }
        }

        $searchTerm2 = trim($searchTerm2);
        $tsQuery2 = implode(' | ', explode(' ', $searchTerm2));
        $tsQuery2 = implode(' & ', explode('_', $tsQuery2));

        // on convertit les mots-clés et les disciplines pour pouvoir les comparer
        $query = "SELECT to_tsquery('french', :searchTerm1) AS "
                  . "internship, to_tsquery('french', :searchTerm2) AS discip";
        $stmt3 = $pdo->getConn()->prepare($query);
        $stmt3->BindValue(':searchTerm1', $tsQuery1);
        $stmt3->bindValue(':searchTerm2', $tsQuery2);
        $stmt3->execute();

        $result = $stmt3->fetch(PDO::FETCH_ASSOC);

        $internship = explode(' | ', $result['internship']);
        $disciplines = explode(' | ', $result['discip']);

        $score = 0;

        foreach ($internship as $subject) {
            $subj = explode(' & ', $subject);
            foreach ($disciplines as $discipline) {
                if ($subject == $discipline) {
                    $score += 1/count($internship);
                } else {
                    foreach ($subj as $sub) {
                        foreach (explode(' & ', $discipline) as $discip) {
                            if ($discip == $sub) {
                                $score += 1/(count($internship)*count($subj));
                            }
                        }
                    }
                } if ($score === 1) {
                    break;
                }
            } if ($score === 1) {
                break;
            }
        }

        return $score;
    }

    /**
     * Permet de savoir si un professeur a tutorer un etudiant
     *
     * @param string $internship_identifier l'identifiant du stage/alternance
     * @param string $id_teacher            l'identifiant du professeur
     *
     * @return bool
     */
    public function isRequested(
        string $internship_identifier, string $id_teacher
    ): bool {
        $query = "SELECT * FROM is_requested WHERE "
                  . "internship_identifier = :internship_identifier "
                  . "AND id_teacher = :id_teacher";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':internship_identifier', $internship_identifier);
        $stmt->bindParam(':id_teacher', $id_teacher);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return 0;
        }
        return 1;
    }

    /**
     * Permet de trouver la meilleure combinaison possible
     * tuteur-stage et le renvoie sous forme de tableau
     *
     * @param Department $departmentModel l'instance du modèle department
     * @param Teacher    $teacherModel    l'instance du
     *                                    modèle teacher
     * @param array      $dicoCoef        dictionnaire
     *                                    cle->nom_critere et valeur->coef
     *
     * @return array|array[] resultat final sous forme de matrice
     */
    public function dispatcher(
        Department $departmentModel, Teacher $teacherModel, array $dicoCoef
    ): array {
        $_db = $this->_db;

        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Teacher.Id_teacher, Teacher.teacher_name, 
                  Teacher.teacher_firstname,
                  Maxi_number_intern AS max_intern, 
                  Maxi_number_apprentice AS max_apprentice, 
                  SUM(CASE WHEN internship.type = 'alternance' THEN 1 ELSE 0 END) 
                  AS current_count_apprentice, 
                  SUM(CASE WHEN internship.type = 'Internship' THEN 1 ELSE 0 END) 
                  AS current_count_intern 
                  FROM Teacher 
                  JOIN (SELECT DISTINCT user_id, department_name FROM has_role) 
                  AS has_role ON Teacher.Id_teacher = has_role.user_id 
                  LEFT JOIN internship 
                  ON Teacher.Id_teacher = internship.Id_teacher 
                  WHERE department_name IN ($placeholders) 
                  GROUP BY Teacher.Id_teacher";

        $stmt = $_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);

        $teacherData = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $listTeacherMaxIntern = [];
        $listTeacherMaxApprentice = [];
        $listTeacherIntern = [];
        $listTeacherApprentice = [];

        foreach ($teacherData as $teacher) {
            $listTeacherMaxIntern[$teacher['id_teacher']] = $teacher['max_intern'];
            $listTeacherMaxApprentice[$teacher['id_teacher']] = $teacher['max_apprentice'];
            $listTeacherIntern[$teacher['id_teacher']] = $teacher['current_count_intern'];
            $listTeacherApprentice[$teacher['id_teacher']] = $teacher['current_count_apprentice'];
        }

        $listFinal = [];
        $listStart = [];
        $listEleveFinal = [];

        foreach ($teacherData as $teacher) {
            foreach ($this->relevanceTeacher(
                $departmentModel, $teacherModel, $teacher, $dicoCoef
            ) as $association) {
                $listStart[] = $association;
            }
        }

        if (empty($listStart)) {
            return [[], []];
        }

        $assignedCountsIntern = $listTeacherIntern;
        $assignedCountsApprentice = $listTeacherApprentice;

        while (!empty($listStart)) {
            usort($listStart, fn($a, $b) => $b['score'] <=> $a['score']);
            $topCandidate = $listStart[0];
            $assignedTopIntern = $assignedCountsIntern[$topCandidate['id_teacher']];
            $assignedTopApprentice = $assignedCountsApprentice[$topCandidate['id_teacher']];
            $listTopIntern = $listTeacherMaxIntern[$topCandidate['id_teacher']];
            $listTopApprentice = $listTeacherMaxApprentice[$topCandidate['id_teacher']];
            if ($assignedTopIntern < $listTopIntern
                && !in_array($topCandidate['internship_identifier'], $listEleveFinal)
                && $topCandidate['type'] === 'Internship'
            ) {
                    $listFinal[] = $topCandidate;
                    $listEleveFinal[] = $topCandidate['internship_identifier'];
                    ++ $assignedCountsIntern[$topCandidate['id_teacher']];
            }
            elseif ($assignedTopApprentice < $listTopApprentice
                && !in_array($topCandidate['internship_identifier'], $listEleveFinal)
                && $topCandidate['type'] === 'alternance'
            ) {
                $listFinal[] = $topCandidate;
                $listEleveFinal[] = $topCandidate['internship_identifier'];
                ++ $assignedCountsApprentice[$topCandidate['id_teacher']];
            }
            else {
                array_shift($listStart);
            }
        }
        return [$listFinal, $assignedCountsIntern, $assignedCountsApprentice];
    }

    /**
     * Récupère une liste des identifiants
     * de stages des étudiants inscrits
     * dans les départements associés au rôle de l'admin.
     *
     * @return array|false Un tableau contenant les identifiants
     * des stages ou `false` en cas d'erreur ou si
     * aucun stage n'est trouvé pour les départements spécifiés.
     */
    public function createListInternship(): false|array
    {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT Internship.Internship_identifier "
                  . "FROM Internship JOIN Study_at "
                  . "ON Internship.Student_number = Study_at.Student_number "
                  . "WHERE Department_name IN ($placeholders)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère une liste des élèves et professeurs associés
     * inscrits dans les départements dont l'admin est responsable.
     *
     * @return array|false Un tableau contenant
     * les paires (id_teacher, internship_identifier)
     * pour chaque étudiant dans les départements concernés,
     * ou `false` en cas d'erreur ou si aucun résultat n'est trouvé.
     */
    public function createListAssociate(): false|array
    {
        $roleDepartments = $_SESSION['role_department'];
        $placeholders = implode(',', array_fill(0, count($roleDepartments), '?'));

        $query = "SELECT internship.Id_teacher, internship.Internship_identifier "
                  . "FROM internship JOIN Study_at "
                  . "ON internship.Student_number = Study_at.Student_number "
                  . "WHERE Department_name "
                  . "IN ($placeholders) "
                  . "AND internship.Id_teacher IS NOT NULL";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute($roleDepartments);
        return $stmt->fetchAll(PDO::FETCH_NUM);
    }

    /**
     * Fonction permettant d'associer un enseignant à un stage.
     *
     * @return string Un message confirmant l'enregistrement
     * de l'association entre l'enseignant et le stage.
     */
    public function insertResponsible(): string
    {
        $query = 'UPDATE internship SET Id_teacher = :Id_teacher '
                  . 'WHERE Internship_identifier = :Internship_identifier';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':Internship_identifier', $_POST['searchInternship']);
        $stmt->bindParam(':Id_teacher', $_POST['searchTeacher']);
        $stmt->execute();
        return "Association " .
            $_POST['searchTeacher'] .
            " et " .
            $_POST['searchInternship'] . " enregistrée.";
    }

    /**
     * Cette fonction effectue la mise à jour des
     * informations dans la base de données en
     * associant un enseignant et un score de pertinence à un stage.
     *
     * **Paramètres :**
     *
     * @param String $id_prof       : L'identifiant de
     *                              l'enseignant à
     *                              associer au stage.
     * @param String $Internship_id : L'identifiant du stage
     *                              auquel l'enseignant est affectée
     * @param float  $Score         : Le score de pertinence
     *                              attribué à cette
     *                              association (représente
     *                              la qualité de la
     *                              répartition).
     *
     * @return string Message confirmant
     * l'enregistrement de l'association entre l'enseignant et le stage.."
     */
    public function insertIsResponsible(
        String $id_prof, String $Internship_id, float $Score
    ): string {
        $query = 'UPDATE internship '
                  . 'SET Id_teacher = :id_prof, Relevance_score = :Score '
                  . 'WHERE Internship_identifier = :Internship_id';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':id_prof', $id_prof);
        $stmt->bindParam(':Score', $Score);
        $stmt->bindParam(':Internship_id', $Internship_id);
        $stmt->execute();
        return "Association " .
            $id_prof . " et " .
            $Internship_id . " enregistrée. <br>";
    }

    /**
     * Renvoie l'historique (de stage) le plus recent d'un etudiant s'il en a un
     *
     * @param string $student_number l'identifiant de l'étudiant
     *
     * @return mixed renvoie l'historique des
     * stages/alternance de l'étudiant s'il existe
     */
    public function getStudentHistory(string $student_number): mixed
    {
        $query = "SELECT End_date_internship "
                  . "FROM Internship "
                  . "WHERE Student_number = :student_number "
                  . "AND Start_date_internship < CURRENT_DATE";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student_number', $student_number);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_COLUMN);
    }

    /**
     * Récupère les informations relatives
     * au prochain stage de l'étudiant passé en paramètre
     *
     * @param string $student numéro de l'étudiant
     *
     * @return false|array tableau contenant le numéro de stage,
     * le nom de l'entreprise,
     * le sujet du stage et le numéro de l'enseignant tuteur
     */
    public function getInternshipStudent(string $student): false|array
    {
        $query = 'SELECT internship_identifier, company_name, '
                  . 'internship_subject, address, '
                  . 'internship.id_teacher, teacher_name, '
                  . 'teacher_firstname, formation, class_group '
                  . 'FROM internship '
                  . 'JOIN student '
                      . 'ON internship.student_number = student.student_number '
                  . 'LEFT JOIN teacher '
                      . 'ON internship.id_teacher = teacher.id_teacher '
                  . 'WHERE internship.student_number = :student '
                  . 'AND start_date_internship > CURRENT_DATE '
                  . 'ORDER BY start_date_internship ASC '
                  . 'LIMIT 1';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Renvoie l'adresse de l'entreprise de l'etudiant.
     *
     * @param string $studentId le numero de l'etudiant
     *
     * @return string|false l'addresse de l'etudiant, 
     * false si ce n'est pas le même étudiant
     */
    public function getInternshipAddress(string $studentId): string|false
    {
        if ($studentId !== $_POST['student_id']) {
            return false;
        }

        $pdo = $this->_db;

        $query = 'SELECT address FROM internship '
                  . 'WHERE internship.student_number = :student_number';

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':student_number', $studentId);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Insère ou supprime de la table is_requested
     * l'enseignant et le stage passés en paramètre
     *
     * @param bool   $add        est true s'il faut ajouter la ligne,
     *                           false s'il faut la supprimer
     * @param string $teacher    numéro de
     *                           l'enseignant
     * @param string $internship numéro du stage
     *
     * @return true|string renvoie true si la requête a fonctionné,
     * sinon l'erreur dans un string
     */
    public function updateSearchedStudentInternship(
        bool $add, string $teacher, string $internship
    ): true|string {
        $current_requests = $this->getRequests($teacher);
        if ($add) {
            if (!in_array($internship, $current_requests)) {
                $query
                    = 'INSERT INTO is_requested(id_teacher, internship_identifier) '
                    . 'VALUES(:teacher, :internship)';
                $stmt = $this->_db->getConn()->prepare($query);
                $stmt->bindParam(':teacher', $teacher);
                $stmt->bindParam(':internship', $internship);
            } else {
                return true;
            }
        } else {
            if (in_array($internship, $current_requests)) {
                $query = 'DELETE FROM is_requested '
                            . 'WHERE  id_teacher = :teacher '
                            . 'AND internship_identifier = :internship';
                $stmt = $this->_db->getConn()->prepare($query);
                $stmt->bindParam(':teacher', $teacher);
                $stmt->bindParam(':internship', $internship);
            } else {
                return true;
            }
        }
        try {
            $stmt->execute();
        } catch(PDOException $e) {
            return $e->getMessage();
        }
        return true;
    }

    /**
     * Met à jour la table is_requested en
     * fonction des stages demandés par l'enseignant passé en paramètre
     *
     * @param array  $requests tableau contenant les numéros de stage que
     *                         l'enseignant souhaite tutorer
     * @param string $teacher  numéro de l'enseignant
     *
     * @return bool|string
     */
    public function updateRequests(array $requests, string $teacher): bool|string
    {
        $current_requests = $this->getRequests($teacher);
        if (!$current_requests) { 
            $current_requests = array();
        }

        $to_add = array_diff($requests, $current_requests);
        $to_delete = array_diff($current_requests, $requests);

        foreach ($to_add as $request) {
            $query = 'INSERT INTO is_requested(id_teacher, internship_identifier) '
                        . 'VALUES(:teacher, :internship)';
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $teacher);
            $stmt->bindParam(':internship', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }

        foreach ($to_delete as $request) {
            $query = 'DELETE FROM is_requested '
                        . 'WHERE  id_teacher = :teacher '
                        . 'AND internship_identifier = :internship';
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':teacher', $teacher);
            $stmt->bindParam(':internship', $request);

            try {
                $stmt->execute();
            } catch(PDOException $e) {
                return $e->getMessage();
            }
        }
        return true;
    }

    /**
     * Renvoie tous les stages que
     * l'enseignant passé en paramètre a demandé à tutorer
     *
     * @param string $teacher numéro de l'enseignant
     * 
     * @return false|array tableau contenant le numéro
     * d'étudiant de l'élève du stage dont
     * l'enseignant connecté a fait la demande, false sinon
     */
    public function getRequests(string $teacher): false|array
    {
        $query = 'SELECT internship_identifier '
                    . 'FROM is_requested '
                    . 'WHERE  id_teacher = :teacher';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':teacher', $teacher);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Vérifie si une association
     * id_teacher et student_number 
     * existe déjà avant insertion
     *
     * @param string $idTeacher     L'identifiant de l'enseignant
     * @param string $studentNumber Le numéro d'étudiant
     * 
     * @return bool Retourne true si l'association existe déjà, sinon false
     */
    public function internshipExists(string $idTeacher, string $studentNumber): bool
    {
        $query
            = "SELECT COUNT(*) "
            . "FROM internship "
            . "WHERE id_teacher = :id_teacher AND student_number = :student_number";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':id_teacher', $idTeacher);
        $stmt->bindValue(':student_number', $studentNumber);
        $stmt->execute();

        // Retourne True si un enregistrement existe, False sinon
        return $stmt->fetchColumn() > 0;
    }
}