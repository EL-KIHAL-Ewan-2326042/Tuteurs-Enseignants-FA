<?php

class Database
{
    private string $host = "postgresql-tutormap.alwaysdata.net";
    private string $user = "tutormap";
    private string $pass = "8exs7JcEpGVfsI";
    private string $dbname = "tutormap_v2";
    private PDO $conn;

    /**
     * Constructeur de la classe database
     * Lors de la construction de l'objet database, une tentative de connexion est faite vers la bd
     */
    public function __construct()
    {
        try {
            $this->conn = new PDO("pgsql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Erreur de connexion: " . $e->getMessage();
        }
    }

    /**
     * Méthode statique pour obtenir l'instance unique de la classe database(singleton)
     * @return database
     */
    public static function getInstance(): database
    {
        static $instance = null;
        if ($instance === null) {
            $instance = new self();
        }
        return $instance;
    }

    /**
     * Méthode pour récupérer la connexion PDO
     * @return PDO
     */
    public function getConn(): PDO
    {
        return $this->conn;
    }
}

?>