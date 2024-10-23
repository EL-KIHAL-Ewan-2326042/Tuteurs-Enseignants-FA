<?php
namespace Blog\Models;

use Database;use PDOException;

class Intramu {

    private Database $db;

    public function __construct(Database $db) {
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

    /**
     * renvoie le role de l'utilisateur selon son identifiant
     * @param string $identifier l'identifiant de l'utilisateur
     * @return false|mixed renvoie le rôle dans la DB
     */
    public function getRole(string $identifier) {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT role_name FROM has_role 
                  WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * renvoie le role_department de l'utilisateur selon son identifiant
     * @param string $identifier l'identifiant de l'utilisateur
     * @return false|mixed renvoie le rôle dans la DB
     */
    public function getRole_department(string $identifier) {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT Role_department FROM has_role 
                  WHERE has_role.user_id = :user_id';

        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':user_id', $identifier);
        $stmt->execute();

        return $stmt->fetchColumn();
    }

    /**
     * Recuperer toute une ligne selon la cle primaire dans la table teacher
     * @param string $identifier l'identifiant du professeur
     * @return false|mixed renvoie la ligne dans la DB
     */
    public function fetchAll(string $identifier) {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT * FROM teacher WHERE id_teacher = :id_teacher';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_teacher', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetch($db->getConn()::FETCH_ASSOC);
    }
}
?>