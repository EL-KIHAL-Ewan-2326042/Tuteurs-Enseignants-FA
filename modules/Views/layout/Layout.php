<?php
/**
 * Fichier contenant la vue de la mise en page présente sur chaque page du site
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/layout
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\layout;

/**
 * Classe gérant l'affichage de la mise en page
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/layout
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Layout
{

    /**
     * Affiche le rendu du haut de page (header)
     *
     * @param string $title       Titre de la page
     * @param string $cssFilePath Chemin vers le fichier CSS de la page
     *
     * @return void
     */
    public function renderTop(string $title, string $cssFilePath): void
    {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title><?php echo $title; ?></title>
                <link rel="icon" type="image/x-icon" href="/favicon.ico">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/
materialize/1.0.0/css/materialize.min.css"
                      rel="stylesheet">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons"
                rel="stylesheet">
                <link rel="stylesheet"
                      href="https://cdn.jsdelivr.net/gh/openlayers/
openlayers.github.io@master/en/v6.5.0/css/ol.css"
                      type="text/css">
                <link href="/_assets/styles/layout.css" rel="stylesheet">
                <?php
                echo '<link href="' . $cssFilePath . '" rel="stylesheet">';
                ?>
            </head>
        <body>
        <nav class="navbar">
            <div class="nav-wrapper container">
                <a href="#" data-target="mobile-demo" class="sidenav-trigger">
                    <i class="material-icons">menu</i>
                </a>
                <a href="/homepage" class="left brand-logo hide-on-med-and-down"
                style="margin-left: 10px;">
                    <img src="https://i.postimg.cc/qMT89vt3/amu-logo.png"
                    alt="Logo de AMU" height="55" width="130">
                </a>
                <ul class="center hide-on-med-and-down">
                    <?php
                    if (isset($_SESSION['identifier'])) {
                        echo '<li><a href="/homepage">ACCUEIL</a></li>';
                    }
                    if (isset($_SESSION['role_name'])
                        && $_SESSION['role_name'] === 'Admin_dep'
                    ) { ?>
                        <li><a href="/dashboard">
                            <?php echo 'GESTION DES DONNEES'; ?>
                        </a></li>
                        <li><a href="/dispatcher">
                            <?php echo 'REPARTITEUR'; ?>
                        </a></li>
                    <?php } ?>
                    <li><a href="/intramu"><?php
                    if (isset($_SESSION['identifier'])) {
                        echo 'DECONNEXION';
                    } else {
                        echo 'INTRAMU';
                    } ?>
                        </a></li>
                </ul>
                <ul class="right">
                    <?php if (isset($_SESSION['identifier'])) { ?>
                        <li class="user-identifier"><a href="/account">
                                <?php echo $_SESSION['fullName']['teacher_firstname']
                                . ' ' . $_SESSION['fullName']['teacher_name'] . ' (';
                                $roles = '';
                                foreach ($_SESSION['roles'] as $role) {
                                    $roles .= $role . ', ';
                                }
                                $roles = substr($roles, 0, -2);
                                echo $roles . ')';
                                ?> </a> </li>
                    <?php } ?>
                </ul>
            </div>
        </nav>
        <ul class="sidenav" id="mobile-demo">
            <?php
            if (isset($_SESSION['identifier'])) {
                echo '<li><a href="/homepage">ACCUEIL</a></li>';
            }
            if (isset($_SESSION['role_name'])
                && $_SESSION['role_name'] === 'Admin_dep'
            ) { ?>
                <li><a href="/dashboard">
                    <?php echo 'GESTION DES DONNEES'; ?>
                </a></li>
                <li><a href="/dispatcher">
                    <?php echo 'REPARTITEUR'; ?>
                </a></li>
            <?php } ?>
            <li><a href="/intramu"><?php
            if (isset($_SESSION['identifier'])) {
                echo 'DECONNEXION';
            } else {
                echo 'INTRAMU';
            } ?></a></li>
        </ul>
        <?php
    }

    /**
     * Affiche le rendu du pied de page (footer)
     *
     * @param string $jsFilePath Chemin vers le fichier JavaScript de la page
     *
     * @return void
     */
    public function renderBottom(string $jsFilePath): void
    {
        ?>
        <footer class="page-footer">
            <div class="container">
                <div class="row">
                    <div class="col s6">
                        &copy; 2024 TutorMap
                    </div>
                    <div class="col s6 right-align">
                        <a href="/mentions-legales">Mentions Légales -</a>
                        <a href="/aboutus"> A Propos</a>
                    </div>
                </div>
            </div>
        </footer>
        <script src="/_assets/scripts/layout.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/
materialize/1.0.0/js/materialize.min.js"></script>
        <?php
        if ($jsFilePath) {
            echo '<script src="' . $jsFilePath . '"></script>';
        }
        $currentUri = $_SERVER['REQUEST_URI'];

        if ($currentUri === '/' || $currentUri === '/homepage'
            || $currentUri === '/dispatcher'
        ) {
            echo '<script 
            src="https://cdn.jsdelivr.net/gh/openlayers/
openlayers.github.io@master/en/v6.5.0/build/ol.js" 
            async defer 
            onload="initMap()">
          </script>';
        }
        ?>
        </body>
        </html>
        <?php
    }
}
?>