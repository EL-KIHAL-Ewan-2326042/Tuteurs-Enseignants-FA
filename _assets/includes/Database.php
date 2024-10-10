<?php

class Database
{
    private string $host = "";
    private string $user = "";
    private string $pass = "";
    private string $dbname = "";
    private \exceptions\PDO $conn;

    /**
     * Constructeur de la classe database
     * Lors de la construction de l'objet database, une tentative de connexion est faite vers la bd
     */
    public function __construct()
    {
        try {
            $this->conn = new \exceptions\PDO("mysql:host=$this->host;dbname=$this->dbname", $this->user, $this->pass);
            $this->conn->setAttribute(\exceptions\PDO::ATTR_ERRMODE, \exceptions\PDO::ERRMODE_EXCEPTION);
        } catch (\exceptions\PDOException $e) {
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
     * @return \exceptions\PDO
     */
    public function getConn(): \exceptions\PDO
    {
        return $this->conn;
    }
}

?>