<?php
/**
 * Fichier permettant d'initialiser le bootstrap
 *
 * PHP version 8.3
 *
 * @category Models
 * @package  TutorMap/tests/Models
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */require_once __DIR__ . '/../vendor/autoload.php';

//initialisation variables d'environnement pour test
putenv('APP_ENV=test');

//initialisation base de données
try {
    $db = new PDO(
        'pgsql:host=postgresql-tutormap.alwaysdata.net;dbname=tutormap_v1',
        'tutormap',
        '8exs7JcEpGVfsI'
    );
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connexion à la base de données de test échouée : ' . $e->getMessage();
}