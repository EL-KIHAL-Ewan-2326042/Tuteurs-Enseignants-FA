<?php
namespace Blog\Views\dashboard;

class Import
{
    public function __construct(private string $category = '')
    {
    }

    public function showView(): void
    {
        ?>
        <div class="card-panel white">
            <div class="tooltip-container" data-tooltip=
            "1. Exporter le modèle correspondant <br>
                 2. Inscrivez vos données <br>
                 3. Vérifiez que le séparateur est
                 bien une virgule (avec un éditeur de texte) <br>
                 4. Vérifiez qu'un texte comportant une
                 virgule soit encadrer par
                 des guillemets (avec un éditeur de texte) <br>
                 5. Importez le fichier csv ci-dessous">(?)</div>
            <h2>Importer :</h2>

            <?php if (empty($this->category) || $this->category === 'students'): ?>
                <!--Importation de nouveaux étudiants-->
                <div class="row">
                    <h3> Rajouter des étudiants : </h3>
                    <form action="/dashboard" method="POST"
                          enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="student">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="student" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file" name="student"
                                       id="student" accept=".csv"
                                       multiple style="display: none;">
                            </div>

                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                           placeholder="Choisir un fichier" required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                    type="submit" name="submit_student">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($this->category) || $this->category === 'teachers'): ?>
                <!--Importation de nouveaux enseignants-->
                <div class="row">
                    <h3> Rajouter des enseignants : </h3>
                    <form action="/dashboard" method="POST"
                          enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="teacher">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="teacher" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file"
                                       name="teacher"
                                       id="teacher"
                                       accept=".csv"
                                       multiple style="display: none;">
                            </div>
                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                           placeholder="Choisir un fichier" required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                    type="submit" name="submit_teacher">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (empty($this->category) || $this->category === 'internships'): ?>
                <!--Importation de nouveaux stages-->
                <div class="row">
                    <h3> Rajouter des stages : </h3>
                    <form action="/dashboard" method="POST"
                          enctype="multipart/form-data">
                        <input type="hidden" name="table_name" value="internship">
                        <div class="file-field input-field">
                            <div class="btn">
                                <label for="internship" style="cursor: pointer;">
                                    <span>Fichier CSV</span>
                                </label>
                                <input type="file"
                                       name="internship"
                                       id="internship"
                                       accept=".csv"
                                       multiple style="display: none;">
                            </div>
                            <div class="file-path-wrapper">
                                <label>
                                    <input class="file-path validate" type="text"
                                           placeholder="Choisir un fichier"
                                           required>
                                    <span>Format CSV</span>
                                </label>
                            </div>
                            <button class="btn waves-effect waves-light"
                                    type="submit" name="submit_internship">Valider
                                <i class="material-icons right">send</i>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
}