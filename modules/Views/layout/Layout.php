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
                  content="TutorMap">
            <title><?php echo $title; ?></title>
            <link href="/_assets/font/AMUMonumentGrostesk.css" rel="stylesheet">
            <link rel="icon" type="image/x-icon" href="/favicon.ico">
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
            <!-- Datatables -->
            <!-- CSS DataTables -->
            <link rel="preconnect" href="https://cdn.datatables.net" crossorigin>
            <link rel="preload" as="style" href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.1/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.1.0/fc-5.0.4/fh-4.0.2/kt-2.12.1/r-3.0.4/rr-1.5.0/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css" crossorigin>
            <link rel="stylesheet" href="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.1/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.1.0/fc-5.0.4/fh-4.0.2/kt-2.12.1/r-3.0.4/rr-1.5.0/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.css"
                  integrity="sha384-md9BXRwcqjBMfArJ62lJid/IP6Dr4sEtRIML9sBJ/txsKg56kMVGl1tCkC7tv2rg"
                  crossorigin="anonymous"
                  media="print" onload="this.media='all'">

            <!-- Material Icons -->
            <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" media="print" onload="this.media='all'">

            <!-- JS libraries  -->
            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"
                    integrity="sha384-VFQrHzqBh5qiJIU0uGU5CIW3+OWpdGGJM9LBnGbuIH2mkICcFZ7lPd/AAtI7SNf7"
                    crossorigin="anonymous"
                    defer></script>

            <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"
                    integrity="sha384-/RlQG9uf0M2vcTw3CX7fbqgbj/h8wKxw7C3zu9/GxcBPRKOEcESxaxufwRXqzq6n"
                    crossorigin="anonymous"
                    defer></script>

            <script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.3.1/af-2.7.0/b-3.2.3/b-colvis-3.2.3/b-html5-3.2.3/b-print-3.2.3/cr-2.1.0/fc-5.0.4/fh-4.0.2/kt-2.12.1/r-3.0.4/rr-1.5.0/sp-2.3.3/sl-3.0.0/sr-1.4.1/datatables.min.js"
                    integrity="sha384-nXnoPxa46dpbP4PvaPQZrVt6mo6Ux86MTtOV+l47cM9/JoPQjZmfYImsAWjlJgeG"
                    crossorigin="anonymous"
                    defer></script>

            <?php
            echo '<link href="' . $cssFilePath . '" rel="stylesheet">';
            ?>
        </head>
        <body>
            <header>
                <a href="/homepage" class="logo">
                    amU
                </a>
                <div class="nav">
                    <button id="toggleMenu">
                        <i class="material-icons">menu</i>
                    </button>
                    <nav id="mainNav">
                        <ul>
                            <?php
                            if (isset($_SESSION['identifier'])
                                && isset($_SESSION['roles'])
                                && is_array($_SESSION['roles'])
                                && in_array('Enseignant', $_SESSION['roles'])
                            ) {
                                echo '<li><a href="/ask">DEMANDE</a></li>';
                            }

                            if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Admin_dep') {
                                echo '<li><a href="/dashboard">dashboard</a></li>';
                                echo '<li><a href="/dispatcher">dispatcher</a></li>';
                            }
                            ?>
                        </ul>
                    </nav>
                </div>

                <div>
                    <?php if (isset($_SESSION['identifier'])) { ?>
                        <a href="/account">
            <span><?php echo $_SESSION['fullName']['teacher_firstname']
                    . ' ' . $_SESSION['fullName']['teacher_name'] ?></span>
                            <span><?php echo $_SESSION['identifier']?></span>
                        </a>
                    <?php } ?>
                </div>
            </header>
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
        <footer>
                    <div>
                        &copy; <?php echo date("Y"); ?> TutorMap
                    </div>
                    <div >
                        <a href="/legal-notices">Mentions légales </a>-
                        <a href="/aboutus"> À propos</a>
                    </div>
        </footer>
        <script src="/_assets/scripts/layout.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
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