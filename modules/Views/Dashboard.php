<?php

namespace Blog\Views;

class Dashboard{
    /**
     * Vue de la Dashboard
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <div class="row">
                <?php if (isset($_SESSION['role']['role_name'])=='Admin_dep') {?>
                    <form action="/dashboard" method="POST" enctype="multipart/form-data">
                        <div class="file-field input-field">
                            <div class="btn">
                                <span>Fichier CSV</span>
                                <input type="file" name="csv_file" id="csv_file" accept=".csv" multiple>
                            </div>
                            <div class="file-path-wrapper">
                                <input class="file-path validate" type="text" placeholder="Choisissez un fichier CSV" required>
                            </div>

                            <button class="btn waves-effect waves-light" type="submit" name="submit">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                <?php } else header('Location: /homepage'); ?>
            </div>
        </main>
        <?php
    }
}