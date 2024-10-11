<?php

namespace Blog\Views;

class Dashboard{
    /**
     * Vue de la Dashboard
     * @return void
     */
    public function dashboard(): void {
        ?>
        <main>
            <p>Ceci est un test <br> </p>
            <?php if (isset($_SESSION['role'])=='admin') {?>
                <form action="/uploadCsv" method="POST" enctype="multipart/form-data">
                    <label for="file">Choisissez un fichier CSV :</label>
                    <input type="file" name="csv_file" id="csv_file" accept=".csv">
                    <input type="submit" name="submit" value="Valider">
                </form>
            <?php }?>
        </main>
        <?php
    }
}