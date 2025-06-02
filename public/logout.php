<?php
/**
 * Fichier de déconnexion
 *
 * PHP version 8.3
 *
 * @category Logout
 * @package  TutorMap/public
 */
session_start();

// Vider toutes les variables de session
$_SESSION = array();

// Détruire la session
session_destroy();

// Rediriger vers la page d'accueil ou de connexion
header('Location: /intramu');
exit();