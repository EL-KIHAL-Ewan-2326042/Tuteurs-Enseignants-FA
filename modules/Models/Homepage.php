<?php
namespace Blog\Models;
use Database;

class Homepage {

    private Database $db;

    public function __construct(Database $db) {
        $this->db = new Database();
    }

    public function getAdresseEnseignant(string $id): bool|array {
        $query = 'SELECT adresse FROM enseignant WHERE id_enseignant = :id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch($this->db->getConn()::FETCH_ASSOC);
    }

    public function getStageEleve(string $id): bool|array {
        $query = 'SELECT nom_entreprise, adresse_entreprise, sujet_stage FROM stage WHERE num_eleve = :id';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch($this->db->getConn()::FETCH_ASSOC);
    }

    public function getEleves(int $nb): bool|array {
        $query = 'SELECT num_eleve, nom_eleve, prenom_eleve FROM eleve LIMIT ' . $nb . ' OFFSET 1';
        $stmt = $this->db->getConn()->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}