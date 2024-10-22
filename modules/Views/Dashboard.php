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
            <?php if (isset($_SESSION['role']['role_name'])=='Admin_dep') {?>
                <h3> Dashboard </h3>
                <h4>Importer :</h4>
                <div class="cell">
                    <div class="column">
                        <h5> Table étudiant (student) : </h5>
                        <form action="/dashboard" method="POST" enctype="multipart/form-data">
                            <div class="file-field input-field">
                                <div class="btn">
                                    <span>Fichier CSV</span>
                                    <input type="file" name="csv_file_student" id="csv_file_student" accept=".csv" multiple>
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Choisissez un fichier CSV" required>
                                </div>
                                <button class="btn waves-effect waves-light" type="submit" name="submit_student">Valider
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <h5> Table professeur (teacher) : </h5>
                        <form action="/dashboard" method="POST" enctype="multipart/form-data">
                            <div class="file-field input-field">
                                <div class="btn">
                                    <span>Fichier CSV</span>
                                    <input type="file" name="csv_file_teacher" id="csv_file_teacher" accept=".csv" multiple>
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Choisissez un fichier CSV" required>
                                </div>
                                <button class="btn waves-effect waves-light" type="submit" name="submit_teacher">Valider
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="row">
                        <h5> Table entreprise (internship) : </h5>
                        <form action="/dashboard" method="POST" enctype="multipart/form-data">
                            <div class="file-field input-field">
                                <div class="btn">
                                    <span>Fichier CSV</span>
                                    <input type="file" name="csv_file_internship" id="csv_file_internship" accept=".csv" multiple>
                                </div>
                                <div class="file-path-wrapper">
                                    <input class="file-path validate" type="text" placeholder="Choisissez un fichier CSV" required>
                                </div>
                                <button class="btn waves-effect waves-light" type="submit" name="submit_internship">Valider
                                    <i class="material-icons right">send</i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <h4>Exporter :</h4>
                <form action="/dashboard" method="POST">
                    <div class="input-field">
                        <input id="export_table" name="export_table" type="text" class="validate" required>
                        <label for="export_table">Nom de la table</label>
                        <span class="helper-text" data-error="wrong" data-success="right">Ecrire le nom de la table désirée</span>
                    </div>
                    <button class="btn waves-effect waves-light" type="submit">Exporter
                        <i class="material-icons right">send</i>
                    </button>
                </form>

            <?php } else header('Location: /homepage'); ?>
        </main>
        <?php
    }
}