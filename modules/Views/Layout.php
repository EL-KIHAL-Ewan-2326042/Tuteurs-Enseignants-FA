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
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
                <link href="/_assets/styles/layout.css" rel="stylesheet">
                <?php
                if ($jsFilePath) {
                    echo '<script src="' . $jsFilePath . '"></script>';
                }?>
            </head>
        <body>
            <nav class="navbar navbar-expand-lg">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="/homepage">ACCUEIL</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="/login">INTRAMU</a>
                        </li>
                    </ul>
                </div>
            </nav>
        <?php
    }

    /**
     * Rendu du pied de page(footer)
     * @return void
     */
    public function renderBottom(): void {
        ?>
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
        </body>
        </html>
        <?php
    }
}
?>