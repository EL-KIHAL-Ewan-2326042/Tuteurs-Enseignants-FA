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
     * @param string $identifier
     * @param string $password
     * @return bool
     */
    public function doLogsExist(string $identifier, string $password): bool {
        if (empty($identifier) || empty($password)) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT mdp_user FROM utilisateur WHERE id_user = :id_user';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_user', $identifier);
        $stmt->execute();

        $result = $stmt->fetch($db->getConn()::FETCH_ASSOC);

        if ($result && isset($result['mdp_user'])) {
            if (password_verify($password, $result['mdp_user'])) {
                return true;
            }
        }
        return false;
    }

    public function getRole(string $identifier) {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT nom_role FROM utilisateur 
                  JOIN a_role ON utilisateur.id_user = a_role.id_user
                  WHERE a_role.id_user = :id_user';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_user', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetch($db->getConn()::FETCH_ASSOC);
    }

    public function fetchAll(string $identifier) {
        if ($_SESSION['identifier'] !== $identifier) {
            return false;
        }

        $db = $this->db;
        $query = 'SELECT * FROM enseignant WHERE id_enseignant = :id_enseignant';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_enseignant', $_SESSION['identifier']);
        $stmt->execute();

        return $stmt->fetch($db->getConn()::FETCH_ASSOC);
    }
}
?>