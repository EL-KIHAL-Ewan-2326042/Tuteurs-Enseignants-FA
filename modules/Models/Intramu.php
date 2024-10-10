<?php
namespace Blog\Models;

use Database;

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
        $query = 'SELECT mdp_enseignant FROM enseignant WHERE id_enseignant = :id_enseignant';
        $stmt = $db->getConn()->prepare($query);
        $stmt->bindParam(':id_enseignant', $identifier);
        $stmt->execute();

        $result = $stmt->fetch($db->getConn()::FETCH_ASSOC);

        if ($result && isset($result['mdp_enseignant'])) {
            if (password_verify($password, $result['mdp_enseignant'])) {
                return true;
            }
        }
        return false;
    }
}
?>