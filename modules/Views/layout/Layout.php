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
                <meta name="description"
                      content="Auteurs: Alvares Titouan, Avias Daphné,
Kerbadou Islem, Pellet Casimir">
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
                <!-- import de jquery pour datatables -->
                <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
                <!-- Datatables -->
                <link href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.1/b-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.1.0/fc-5.0.4/fh-4.0.2/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css" rel="stylesheet" integrity="sha384-GtIvcdMAKob7NWWr7RiaLQQIiPJxV6mh6xzMZyAcBoNPd9ncw8pHzQQ/WH3i+dav" crossorigin="anonymous">

                <script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.1/b-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.1.0/fc-5.0.4/fh-4.0.2/kt-2.12.1/r-3.0.4/rg-1.5.1/rr-1.5.0/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.js" integrity="sha384-co6kyZuor4wcWh3jmK7akJDnA0v0x201Du7NXjrjCN3kHzW5rPpgkIwrUa63Ty5z" crossorigin="anonymous"></script>
                <!-- colvis -->
                <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>

                <!-- searchbuilder -->
                <link href="https://cdn.datatables.net/searchbuilder/1.8.2/css/searchBuilder.dataTables.min.css" rel="stylesheet" />
                <script src="https://cdn.datatables.net/searchbuilder/1.8.2/js/dataTables.searchBuilder.min.js"></script>

                <!-- page input -->
                <script src="https://cdn.datatables.net/plug-ins/2.3.1/pagination/input.js"></script>

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
                    <img src="https://i.imgur.com/CcTBFfX.png"
                    alt="Logo de AMU" height="55" width="130">
                </a>
                <ul class="center hide-on-med-and-down">
                    <?php
                    if (isset($_SESSION['identifier'])
                        && isset($_SESSION['roles'])
                        && is_array($_SESSION['roles'])
                        && in_array('Enseignant', $_SESSION['roles'])
                    ) {
                        echo '<li><a href="/homepage">DEMANDE</a></li>';
                    }
                    if (isset($_SESSION['role_name'])
                        && $_SESSION['role_name'] === 'Admin_dep'
                    ) { ?>
                        <li><a href="/tutoring">
                            <?php echo 'STAGES'; ?>
                            </a></li>
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
                            foreach (array_unique($_SESSION['roles']) as $role) {
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
            if (isset($_SESSION['identifier'])
                && isset($_SESSION['roles'])
                && is_array($_SESSION['roles'])
                && in_array('Enseignant', $_SESSION['roles'])
            ) {
                echo '<li><a href="/homepage">DEMANDE</a></li>';
            }
            if (isset($_SESSION['role_name'])
                && $_SESSION['role_name'] === 'Admin_dep'
            ) { ?>
                <li><a href="/tutoring">
                    <?php echo 'STAGES'; ?>
                </a></li>
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
                        &copy; <?php echo date("Y"); ?> TutorMap
                    </div>
                    <div class="col s6 right-align">
                        <a href="/legal-notices">Mentions légales </a>-
                        <a href="/aboutus"> À propos</a>
                    </div>
                </div>
            </div>
        </footer>
        <script src="/_assets/scripts/layout.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/
materialize/1.0.0/js/materialize.min.js"></script>
        <script src="/_assets/scripts/datatables/datatables-config.js"></script>
        <?php
        if ($jsFilePath) {
            echo '<script src="' . $jsFilePath . '"></script>';
        }
        $currentUri = $_SERVER['REQUEST_URI'];

        if ($currentUri === '/' || $currentUri === '/homepage'
            || $currentUri === '/dispatcher'
        ) {
            echo '<script async defer
            src="https://cdn.jsdelivr.net/gh/openlayers/
openlayers.github.io@master/en/v6.5.0/build/ol.js" 
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