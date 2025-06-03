<?php
/**
 * Fichier contenant le modèle associé aux informations des étudiants
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

/**
 * Classe gérant toutes les fonctionnalités du site associées
 * aux informations des étudiants. Elle hérite de la classe 'Model'
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
class Student extends Model
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
     * Recherche des termes correspondants dans la base de données
     * en fonction des paramètres fournis dans le POST
     *
     * @return array|false Renvoie un tableau associatif contenant
     * les résultats de la recherche, false sinon
     */
    public function correspondTermsStudent(): array|false
    {
        $searchTerm = $_POST['search'] ?? '';
        $pdo = $this->_db;

        $searchTerm = trim($searchTerm);

        $query
            = "SELECT student.student_number, student_name, "
            . "student_firstname, company_name, internship_identifier "
            . "FROM student "
            . "JOIN internship "
            . "ON student.student_number = internship.student_number "
            . "WHERE company_name ILIKE :searchTerm "
            . "ORDER BY company_name ASC";
        $searchTerm = "$searchTerm%";

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFullName(string $studentNumber): string
    {
        $stmt = $this->_db->getConn()->prepare("SELECT CONCAT(student_name, ' ', student_firstname) FROM student where student_number = :studentNumber ");
        $stmt->bindValue(':studentNumber', $studentNumber);
        $stmt->execute();
        return $stmt->fetchColumn();
    }
    /**
     * Trouve dans le DB les termes correspondant(LIKE)
     * On utilise le POST, avec search qui correspond à la recherche
     * et searchType au type de recherche (studentId, name, ...)
     *
     * @return array Renvoie un tableau contenant tous les termes correspondants
     */
    public function correspondTerms(): array
    {
        $searchTerm = $_POST['search'] ?? '';
        $searchType = $_POST['searchType'] ?? 'numeroEtudiant';
        $pdo = $this->_db;

        $searchTerm = trim($searchTerm);
        $tsQuery = implode(' & ', explode(' ', $searchTerm));
        $query = '';

        if ($searchType === 'studentNumber') {
            $query
                = "SELECT student_number, student_name, student_firstname, "
                . "ts_rank_cd(to_tsvector('french', student_number), "
                . "to_tsquery('french', :searchTerm), 32) AS rank "
                . "FROM student "
                . "WHERE student_number ILIKE :searchTerm "
                . "ORDER BY rank, student_number";
            $searchTerm = "$searchTerm%";
        } elseif ($searchType === 'name') {
            $query
                = "SELECT student_number, student_name, student_firstname, "
                . "ts_rank_cd(to_tsvector( "
                    . "'french', student_name || ' ' || student_firstname "
                . "), to_tsquery('french', :searchTerm), 32) AS rank "
                . "FROM student "
                . "WHERE student_name ILIKE :searchTerm "
                . "OR student_firstname ILIKE :searchTerm "
                . "ORDER BY rank, student_name, student_firstname DESC";
            $searchTerm = "%$searchTerm%";
        } elseif ($searchType === 'company') {
            $query
                = "SELECT student.student_number, student_name, "
                . "student_firstname, company_name, "
                . "ts_rank_cd(to_tsvector('french', internship.company_name), "
                . "to_tsquery('french', :searchTerm), 32) AS rank "
                . "FROM student "
                . "JOIN internship "
                . "ON student.student_number = internship.student_number "
                . "WHERE company_name ILIKE :searchTerm "
                . "ORDER BY rank, company_name DESC";
            $searchTerm = "$searchTerm%";
        }

        $stmt = $pdo->getConn()->prepare($query);
        $stmt->bindValue(':searchTerm', $searchTerm);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les départements dont fait partie l'étudiant passé en paramètre
     *
     * @param string $student Numéro de l'étudiant
     *
     * @return false|array Renvoie un tableau contenant les départements
     * dont l'étudiant fait partie s'il en a, false sinon
     */
    public function getDepStudent(string $student): false|array
    {
        $query = 'SELECT department_name '
                . 'FROM study_at '
                . 'WHERE student_number = :student';
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindParam(':student', $student);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }



}