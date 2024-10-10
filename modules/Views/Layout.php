<?php
namespace Blog\Views;
class Layout {

    /**
     * Rendu du haut de page(header)
     * @param string $title titre de la page
     * @param string $cssFilePath chemin styles
     * @param string $jsFilePath chemin scripts
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
                <link href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css" rel="stylesheet">
                <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
                    <li><a href="/intramu">INTRAMU</a></li>
                    <li><a href="/aboutus">A PROPOS</a></li>
                </ul>
            </div>
        </nav>
        <ul class="sidenav" id="mobile-demo">
            <li><a href="/homepage">ACCUEIL</a></li>
            <li><a href="/intramu">INTRAMU</a></li>
            <li><a href="/aboutus">A PROPOS</a></li>
        </ul>
        <?php
    }

    /**
     * Rendu du pied de page(footer)
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
        <?php
        if ($jsFilePath) {
            echo '<script src="' . $jsFilePath . '"></script>';
        }
        ?>
        <script src="/_assets/scripts/layout.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>
        </body>
        </html>
        <?php
    }
}
?>