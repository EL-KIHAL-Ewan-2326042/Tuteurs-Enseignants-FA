<?php
namespace Blog\Views;

class Homepage {
    /**
     * Vue de la homepage
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <p>Ceci est un test <br> </p>
            <!--CONDITION A CHANGER-->
            <?php if (isset($_SESSION['identifier'])) {?>
                <form action="/uploadCsv" method="POST" enctype="multipart/form-data">
                    <label for="file">Choisissez un fichier CSV :</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv">
                    <input type="submit" name="submit" value="Télécharger">
                </form>

            <?php }?>
        </main>
        <?php
    }
}