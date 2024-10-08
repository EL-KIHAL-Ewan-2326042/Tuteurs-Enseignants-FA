<?php
namespace Blog\Views;
class Layout {

    /**
     * Rendu du haut de page(header)
     * @param string $title titre
     * @param string $jsFilePath chemin scripts
     * @return void
     */
    public function renderTop(string $title, string $jsFilePath): void {
        ?>
        <!DOCTYPE html>
        <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1">
                <title><?php echo $title; ?></title>
                <link rel="stylesheet" href="https://assets.ubuntu.com/v1/vanilla-framework-version-4.16.0.min.css" />
                <link href="/_assets/styles/layout.css" rel="stylesheet">
                <?php
                if ($jsFilePath) {
                    echo '<script src="' . $jsFilePath . '"></script>';
                }?>
            </head>
        <body>
        <header id="navigation" class="p-navigation">
            <div class="p-navigation__row--25-75">
                <div class="p-navigation__banner">
                    <ul class="p-navigation__items">
                        <li class="p-navigation__item">
                            <button class="js-menu-button p-navigation__link">ACCUEIL</button>
                        </li>
                        <li class="p-navigation__item">
                            <button class="js-menu-button p-navigation__link">INTRAMU</button>
                        </li>
                    </ul>
                </div>
                <nav class="p-navigation__nav" aria-label="Example main">
                    <ul class="p-navigation__items">
                        <li class="p-navigation__item is-selected">
                            <a class="p-navigation__link" href="#">ACCUEIL</a>
                        </li>
                        <li class="p-navigation__item">
                            <a class="p-navigation__link" href="#">INTRAMU</a>
                        </li>
                    </ul>
                    <ul class="p-navigation__items">
                        <li class="p-navigation__item">
                            <a class="p-navigation__link" href="#">A PROPOS</a>
                        </li>

                    </ul>
                </nav>
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
        </body>
        </html>
        <?php
    }
}
?>