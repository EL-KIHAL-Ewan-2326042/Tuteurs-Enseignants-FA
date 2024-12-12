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
            <h3> Dashboard </h3>
            <div class="card-panel white">
                <h4>Importer :</h4>

                <div class="row">
                    <h5> Rajouter des étudiants : </h5>
                    <form action="/dashboard" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="student">
                        <div class="file-field input-field">
                            <div class="btn btn-behind">
                                <span>Fichier CSV</span>
                                <input type="file" name="student" id="student" accept=".csv" multiple>
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
                    <h5> Rajouter des professeurs : </h5>
                    <form action="/dashboard" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="teacher">
                        <div class="file-field input-field">
                            <div class="btn btn-behind">
                                <span>Fichier CSV</span>
                                <input type="file" name="teacher" id="teacher" accept=".csv" multiple>
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
                    <h5> Rajouter des stages : </h5>
                    <form action="/dashboard" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="internship">
                        <div class="file-field input-field">
                            <div class="btn btn-behind">
                                <span>Fichier CSV</span>
                                <input type="file" name="internship" id="internship" accept=".csv" multiple>
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

            <div class="card-panel white">
                <h4>Exporter :</h4>
                <form action="/dashboard" method="POST">
                    <div>
                        <select name="export_list" required>
                            <option value="" disabled selected>Choisissez la liste à exporter</option>
                            <option value="student">Etudiants</option>
                            <option value="teacher">Professeurs</option>
                            <option value="internship">Stages</option>
                        </select>
                        <label>Choisissez la liste à exporter</label>
                    </div>
                    <div class="input-field">
                        <button class="btn waves-effect waves-light btn-behind" type="submit">Exporter
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </main>
<?php
    }
}