<?php
//chargement de l'autoloader
require_once __DIR__ . '/../vendor/autoload.php';

//initialisation variables d'environnement pour test
putenv('APP_ENV=test');

//initialisation base de données
try {
    $db = new PDO('pgsql:host=postgresql-tutormap.alwaysdata.net;dbname=tutormap_v1', 'tutormap', '8exs7JcEpGVfsI');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connexion à la base de données de test échouée : ' . $e->getMessage();
}