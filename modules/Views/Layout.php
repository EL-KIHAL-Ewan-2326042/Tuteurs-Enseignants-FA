<?php
namespace Blog\Views;
use Includes\Database;

class Layout {

    /**
     * Rendu du haut de page(header)
     * @param string $title titre de la page
     * @param string $cssFilePath chemin styles
     * @return void
     */
    public function renderTop(string $title, string $cssFilePath): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title><?php echo $title; ?></title>
                <link rel="icon" type="image/x-icon" href="/favicon.ico">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
                <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.5.0/css/ol.css" type="text/css">
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
                <a href="/homepage" class="left brand-logo hide-on-med-and-down">
                    <img src="https://i.postimg.cc/qMT89vt3/amu-logo.png" alt="Logo de AMU" height="60" width="130">
                </a>
                <ul class="right hide-on-med-and-down">
                    <li><a href="/homepage">ACCUEIL</a></li>
                    <?php
                    if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Admin_dep') { ?>
                        <li><a href="/dashboard"> <?php echo 'DASHBOARD'; ?> </a></li>
                        <li><a href="/dispatcher"> <?php echo 'DISPATCHER'; ?> </a></li>
                    <?php } ?>
                    <li><a href="/intramu"><?php
                            if (isset($_SESSION['identifier'])) {
                                echo 'DECONNEXION';
                                }
                            else {
                                echo 'INTRAMU';
                                }?>
                    </a></li>
                    <li><a href="/aboutus">A PROPOS</a></li>
                </ul>
            </div>
        </nav>
        <ul class="sidenav" id="mobile-demo">
            <li><a href="/homepage">ACCUEIL</a></li>
            <?php
            if (isset($_SESSION['role_name']) && $_SESSION['role_name'] === 'Admin_dep') { ?>
                <li><a href="/dashboard"> <?php echo 'DASHBOARD'; ?> </a></li>
                <li><a href="/dispatcher"> <?php echo 'DISPATCHER'; ?> </a></li>
            <?php } ?>
            <li><a href="/intramu"><?php
                    if (isset($_SESSION['identifier'])) {
                        echo 'DECONNEXION';
                    }
                    else {
                        echo 'INTRAMU';
                    }?></a></li>
            <li><a href="/aboutus">A PROPOS</a></li>
        </ul>
        <?php
    }

    /**
     * Rendu du pied de page(footer)
     * @param string $jsFilePath fichier js
     * @return void
     */
    public function renderBottom(string $jsFilePath): void {
        ?>
        <footer class="page-footer">
            <div class="container">
                <div class="row">
                    <div class="col s6">
                        &copy; 2024 TutorMap
                    </div>
                    <div class="col s6 right-align">
                        <a href="/mentions-legales">Mentions Légales</a>
                    </div>
                </div>
            </div>
        </footer>
        <script src="/_assets/scripts/layout.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        <?php
        if ($jsFilePath) {
            echo '<script src="' . $jsFilePath . '"></script>';
        }
        $currentUri = $_SERVER['REQUEST_URI'];

        if ($currentUri === '/' || $currentUri === '/homepage') {
            echo '<script 
            src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.5.0/build/ol.js" 
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