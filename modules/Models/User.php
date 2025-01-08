<?php

namespace Blog\Models;
use Includes\Database;
use mysql_xdevapi\Exception;
use PDO;

class User extends Model {
    private Database $db;

    public function __construct(Database $db) {
        parent::__construct($db);
        $this->db = $db;
    }

    /**
     * On vérifie si l'utilisateur existe dans le BD, si oui return vrai(true) sinon faux(false)
     * @param string $identifier l'identifiant entrée
     * @param string $password le mot de passe entrée
     * @return bool renvoie vrai(true) s'il y a corrependance, sinon faux(false)
     */
    public function doLogsExist(string $identifier, string $password): bool {
        if (empty($identifier) || empty($password)) {
            return false;
        }

        $db = $this->db;
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

    public function getRoles(string $identifier): ?array {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT role_name FROM has_role 
              WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    /**
     * renvoie le role de l'utilisateur selon son identifiant
     * @param string $identifier l'identifiant de l'utilisateur
     * @return mixed renvoie le rôle dans la DB
     */
    public function getHighestRole(string $identifier): mixed
    {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT role_name FROM has_role 
              WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        if (in_array('Super_admin', $roles)) {
            return 'Super_admin';
        }
        if (in_array('Admin_dep', $roles)) {
            return 'Admin_dep';
        }

        return 'Teacher';
    }

    /**
     * renvoie le role_department de l'utilisateur selon son identifiant
     * @param string $identifier l'identifiant de l'utilisateur
     * @return false|mixed renvoie le rôle dans la DB
     */
    public function getRole_department(string $identifier): mixed
    {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT DISTINCT department_name FROM has_role 
              WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        $roles = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

        return $roles ?: [];
    }

    /**
     * Cette fonction permet de récupérer la liste des sauvegardes disponibles dans la base de données.
     *
     * @return array|null Renvoie un tableau associatif contenant les identifiants des sauvegardes disponibles, ou `null` en cas d'échec.
     */
    public function showCoefficients(): ?array {
        try {
            $query = "SELECT DISTINCT id_backup FROM id_backup ORDER BY id_backup ASC";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Cette fonction permet de charger les coefficients d'un utilisateur pour une sauvegarde donnée.
     *
     * @param string $user_id L'identifiant de l'utilisateur pour lequel les coefficients sont chargés.
     * @param int $id_backup L'identifiant de la sauvegarde pour laquelle les coefficients sont récupérés.
     * @return array|false Retourne un tableau associatif des coefficients si la requête réussit, ou `false` en cas d'erreur ou de données non trouvées.
     */
    public function loadCoefficients(string $user_id, int $id_backup): array|false {
        try {
            $query = "SELECT name_criteria, coef, is_checked FROM backup WHERE user_id = :user_id AND id_backup = :id_backup ORDER BY name_criteria ASC";
            $stmt = $this->db->getConn()->prepare($query);
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':id_backup', $id_backup);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Permet de sauvegarder les coefficients dans la base de données.
     *
     * @param array $data Tableau associatif contenant les informations sur les critères à mettre à jour ('name_criteria' (string), 'coef' et 'is_checked'` (int))
     * @param string $user_id Identifiant de l'utilisateur pour lequel les coefficients doivent être mis à jour
     * @param int $id_backup Identifiant de la sauvegarde pour laquelle les coefficients doivent être mis à jour
     * @return bool Retourne True si la mise à jour a réussi, False sinon.
     */

    public function saveCoefficients(array $data, string $user_id, int $id_backup = 0): bool {
        try {
            $query = "UPDATE backup 
                  SET coef = :coef, is_checked = :is_checked 
                  WHERE user_id = :user_id AND id_backup = :id_backup AND name_criteria = :name_criteria";

            foreach ($data as $singleData) {
                $stmt = $this->db->getConn()->prepare($query);
                $stmt->bindParam(':user_id', $user_id);
                $stmt->bindParam(':id_backup', $id_backup);
                $stmt->bindParam(':name_criteria', $singleData['name_criteria']);
                $stmt->bindParam(':coef', $singleData['coef']);
                $stmt->bindParam(':is_checked', $singleData['is_checked']);
                $stmt->execute();
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Récupère les critères de distribution et leur associe des valeurs par défaut 'coef' = 1 et 'is_checked' = true.
     *
     * @return array Tableau associatif contenant la liste des critères, associé au valeur par défaut
     **/

    public function getDefaultCoef(): array
    {
        $query = "SELECT name_criteria FROM distribution_criteria ORDER BY name_criteria ASC";
        $stmt = $this->db->getConn()->prepare($query);
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
     * @param string $userId Identifiant de l'utilisateur
     * @param string $user_pass Mot de passe de l'utilisateur
     * @return void
     */
    public function insertUserConnect(string $userId, string $user_pass): void {
        $query = "INSERT INTO user_connect (user_id, user_pass) VALUES (:user_id, :user_pass)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':user_pass', password_hash($user_pass, PASSWORD_DEFAULT));
        $stmt->execute();
    }

    /**
     * Insère une association utilisateur-départment dans la table has_role
     * @param string $userId Identifiant de l'utilisateur
     * @param string $department Nom du département
     * @return void
     */
    public function insertHasRole(string $userId, string $department): void {
        $query = "INSERT INTO has_role (user_id, role_name, department_name) VALUES (:user_id, 'Professeur' ,:department)";
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindValue(':user_id', $userId);
        $stmt->bindValue(':department', $department);
        $stmt->execute();
    }

}