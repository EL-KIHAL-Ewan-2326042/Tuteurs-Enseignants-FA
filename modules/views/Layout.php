<?php
namespace Blog\Views;
class Layout {

    /**
     * Rendu du haut de page(header)
     * @param string $title titre
     * @param string $description description
     * @param string $cssFilePath chemin css
     * @param string $jsFilePath chemin js
     * @return void
     */
    public function renderTop(string $title, string $description, string $cssFilePath, string $jsFilePath): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
            <link type="text/css" rel="stylesheet" href="/_assets/includes/css/materialize.min.css"  media="screen,projection"/>
            <script type="text/javascript" src="/_assets/includes/js/materialize.min.js"></script>
            <script src="/_assets/includes/js/layout.js"></script>
            <?php
            echo '<link rel="stylesheet" href="' . $cssFilePath . '">';
            if ($jsFilePath) {
                echo '<script src="' . $jsFilePath . '"></script>';
            }?>
            <meta name="description" content="<?php echo $description; ?>">
            <meta name="author" content="ALVARES Titouan, AVIAS Daphné, KERBADOU Islem, PELLET Casimir">
            <link rel="icon" href="">
            <title><?php echo $title; ?></title>
        </head>
        <body>
        <header>
            <div class="header-left">
                <div id="mySidenav" class="sidenav">
                    <a id="closeBtn" href="#" class="close">×</a>
                    <ul id = "menu">
                        <?php
                        if ($_SESSION['id_admin']) {
                            ?>
                            <li><a class="a-header" href="/account">DECONNEXION</a></li>
                        <?php }
                        else { ?>
                            <li><a class="a-header" href="/login">INTRANET</a></li>
                            <?php
                        }?>
                    </ul>
                </div>
                <a href="#" id="openBtn">
                  <span class="burger-icon">
                    <span></span>
                    <span></span>
                    <span></span>
                  </span>
                </a>
                <a href="/homepage"><img alt="Logo AMU" src="https://i.postimg.cc/FHSFJvWp/AMU-Logo.png" width="220" height="65" class="logo"></a>
                <ul class="menu">
                    <?php
                    if ($_SESSION['id_tenrac']) {
                        ?>
                        <li><a class="a-header" href="/account">DECONNEXION</a></li>
                    <?php }
                    else { ?>
                        <li><a class="a-header" href="/login">INTRANET</a></li>
                        <?php
                    }?>
                </ul>
            </div>
            <div class="header-right">
                <?php echo $_SESSION['id_admin'];?>
            </div>
        </header>
        <?php
    }

    /**
     * Rendu du pied de page(footer)
     * @return void
     */
    public function renderBottom(): void {
        ?>
        <footer>
            <div class="footer-first-part">
                <p>Salut1</p>
            </div>
            <div class="footer-second-part">
                <p>Salut1</p>
            </div>
            <div class="footer-third-part">
                <div class="W3C-logo">
                    <p>
                        <a target="_blank" href="<?php echo 'https://validator.w3.org/nu/?doc=https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
                            <img src="https://i.imgur.com/O6cKBc5.png"
                                 alt="Validation HTML" id="html5Validator">
                        </a>
                    </p>
                    <p>
                        <a target="_blank" href="https://jigsaw.w3.org/css-validator/check/referer">
                            <img src="https://jigsaw.w3.org/css-validator/images/vcss-blue"
                                 alt="Validation CSS" id="css3Validator">
                        </a>
                    </p>
                </div>
                <p class="copyright">©2024 - Aix-Marseille Université</p>
            </div>
        </footer>
        </body>
        </html>
        <?php
    }
}
?>