<?php
/**
 * Fichier contenant le modèle associé aux informations des utilisateurs
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
use mysql_xdevapi\Exception;
use PDO;

/**
 * Classe gérant toutes les fonctionnalités du site associées
 * aux informations des utilisateurs. Elle hérite de la classe 'Model'
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
class User extends Model
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
     * On vérifie si l'utilisateur existe dans le BD,
     * si oui return vrai(true) sinon faux(false)
     *
     * @param string $identifier Identifiant entré
     * @param string $password   Mot de passe entré
     *
     * @return bool renvoie vrai(true) s'il y a corrependance, sinon faux(false)
     */
    public function doLogsExist(string $identifier, string $password): bool
    {
        if (empty($identifier) || empty($password)) {
            return false;
        }

        $db = $this->_db;
        $query = 'SELECT user_pass FROM user_connect WHERE user_id = :user_id';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        $result = $stmt->fetch($db->getConn()::FETCH_ASSOC);

        if ($result && isset($result['user_pass'])) {
            if (password_verify($password, $result['user_pass'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Récupère les rôles de l'utilisateur passé en paramètre
     *
     * @param string $identifier Identifiant de l'utilisateur
     *
     * @return array|false Renvoie false si l'identifiant ne correspond pas à celui
     * de l'utilisateur connecté, sinon renvoie une liste contenant les rôles de
     * l'utilisateur s'il en a, false sinon
     */
    public function getRoles(string $identifier): false|array
    {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->_db;
        $query = 'SELECT role_name FROM has_role '
                . 'WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * Récupère le role le plus haut de l'utilisateur passé en paramètre
     *
     * @param string $identifier Identifiant de l'utilisateur
     *
     * @return false|string Renvoie false si l'identifiant ne correspond pas à celui
     * de l'utilisateur connecté, sinon renvoie le rôle le plus haut s'il en a, une
     * chaîne de caractères vide sinon
     */
    public function getHighestRole(string $identifier): false|string
    {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $roles = $this->getRoles($identifier);
        if (!$roles) {
            return '';
        }

        if (in_array('Super_admin', $roles)) {
            return 'Super_admin';
        }
        if (in_array('Admin_dep', $roles)) {
            return 'Admin_dep';
        }

        return 'Teacher';
    }

    /**
     * Récupère les départements auxquels l'utilisateur passé en paramètre appartient
     *
     * @param string $identifier Identifiant de l'utilisateur
     *
     * @return false|array Renvoie false si l'identifiant ne correspond pas à celui
     *  de l'utilisateur connecté, sinon renvoie une liste contenant les départements
     * auxquels il appartient qui peut être vide s'il n'y en a aucun
     */
    public function getRoleDepartment(string $identifier): false|array
    {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->_db;
        $query = 'SELECT DISTINCT department_name FROM has_role '
                . 'WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        return $roles ?: [];
    }

    /**
     * Récupère la liste des sauvegardes disponibles dans la base de données
     *
     * @return array|null Renvoie un tableau associatif contenant les identifiants
     * des sauvegardes disponibles, ou `null` en cas d'échec
     */
    public function showCoefficients(): ?array
    {
        try {
            $query = "SELECT DISTINCT id_backup "
                    . "FROM id_backup ORDER BY id_backup ASC";
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception) {
            return null;
        }
    }

    /**
     * Charge les coefficients d'un utilisateur pour une sauvegarde donnée
     *
     * @param string $user_id   L'identifiant de l'utilisateur pour lequel
     *                          les coefficients sont chargés.
     * @param int    $id_backup L'identifiant de la sauvegarde pour laquelle
     *                          les coefficients sont récupérés
     *
     * @return array|false Renvoie un tableau associatif des coefficients
     * si la requête réussit, ou `false` en cas d'erreur ou de données non trouvées
     */
    public function loadCoefficients(string $user_id, int $id_backup): array|false
    {
        try {
            $query = "SELECT backup.name_criteria, backup.coef,"
                    . " backup.is_checked, distribution_criteria.description "
                    . "FROM backup JOIN distribution_criteria "
                    . "ON backup.name_criteria = distribution_criteria.name_criteria "
                    . "WHERE user_id = :user_id "
                    . "AND id_backup = :id_backup "
                    . "ORDER BY name_criteria ASC";
            $stmt = $this->_db->getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':id_backup', $id_backup);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (\Exception) {
            return false;
        }
    }

    /**
     * Permet de sauvegarder les coefficients dans la base de données
     *
     * @param array  $data      Tableau associatif contenant les informations
     *                          sur les critères à mettre à jour
     *                          ('name_criteria' (string),
     *                          'coef' et 'is_checked'` (int))
     * @param string $user_id   Identifiant de l'utilisateur pour lequel
     *                          les coefficients doivent être mis à jour
     * @param int    $id_backup Identifiant de la sauvegarde pour laquelle
     *                          les coefficients doivent être mis à jour
     *
     * @return bool Renvoie 'true' si la mise à jour a réussi, 'false' sinon
     */
    public function saveCoefficients(
        array $data, string $user_id, int $id_backup = 0
    ): bool {
        try {
            $query = "UPDATE backup "
                    . "SET coef = :coef, is_checked = :is_checked "
                    . "WHERE user_id = :user_id "
                    . "AND id_backup = :id_backup "
                    . "AND name_criteria = :name_criteria";

            foreach ($data as $singleData) {
                $stmt = $this->_db->getConn()->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':id_backup', $id_backup);
                $stmt->bindParam(':name_criteria', $singleData['name_criteria']);
                $stmt->bindParam(':coef', $singleData['coef']);
                $stmt->bindParam(':is_checked', $singleData['is_checked']);
                $stmt->execute();
            }

            return true;
        } catch (Exception) {
            return false;
        }
    }

    /**
     * Récupère les critères de distribution et leur associe des valeurs par défaut :
     * 'coef' = 1 et 'is_checked' = true.
     *
     * @return array Renvoie un tableau associatif contenant la liste des critères,
     * associés aux valeurs par défaut
     **/
    public function getDefaultCoef(): array
    {
        $query = "SELECT name_criteria, description "
                . "FROM distribution_criteria "
                . "ORDER BY name_criteria ASC";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->execute();
        $defaultCriteria = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($defaultCriteria as &$criteria) {
            $criteria['coef'] = 1;
            $criteria['is_checked'] = true;
        }

        return $defaultCriteria;
    }

    /**
     * Insère un utilisateur dans la table user_connect
     *
     * @param string $userId    Identifiant de l'utilisateur
     * @param string $user_pass Mot de passe de l'utilisateur
     *
     * @return void
     */
    public function insertUserConnect(string $userId, string $user_pass): void
    {
        $query = "INSERT INTO user_connect (user_id, user_pass) "
                . "VALUES (:user_id, :user_pass)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':user_pass', password_hash($user_pass, PASSWORD_DEFAULT));
        $stmt->execute();
    }

    /**
     * Insère une association utilisateur-départment dans la table has_role
     *
     * @param string $userId     Identifiant de l'utilisateur
     * @param string $department Nom du département
     *
     * @return void
     */
    public function insertHasRole(string $userId, string $department): void
    {
        $query = "INSERT INTO has_role (user_id, role_name, department_name) "
                . "VALUES (:user_id, 'Professeur' ,:department)";
        $stmt = $this->_db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }

}