<?php
namespace Blog\Views\dashboard;

class Export
{
    public function __construct(private string $category = '')
    {
    }

    public function showView(): void
    {
        ?>
        <div class="export">
            <div class="card-panel white">
                <!--Exportation des listes : Etudiants/enseignants/Stages-->
                <div class="tooltip-container" data-tooltip=
                "Exportation des données d'une liste
                    choisie dans un fichier .csv">(?)</div>
                <h2>Exporter une liste :</h2>
                <form action="/dashboard" method="POST">
                    <div>
                        <label>
                            <span>Choisissez la liste à exporter</span>

                            <select name="export_list" required>
                                <option value="" disabled selected>Choisir</option>
                                <?php if (empty($this->category) || $this->category === 'students'): ?>
                                    <option value="student">Etudiants</option>
                                <?php endif; ?>

                                <?php if (empty($this->category) || $this->category === 'teachers'): ?>
                                    <option value="teacher">Enseignants</option>
                                <?php endif; ?>

                                <?php if (empty($this->category) || $this->category === 'internships'): ?>
                                    <option value="internship">Stages</option>
                                <?php endif; ?>
                            </select>
                        </label>
                    </div>
                    <div class="input-field">
                        <button class="btn waves-effect waves-light"
                                type="submit">Exporter
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>

            <!--Exportation des modèles des tables-->
            <div class="card-panel white">
                <div class="tooltip-container" data-tooltip=
                "Exportation d'un modèle d'une liste choisie dans un fichier
                    .csv (Pour avoir seulement le nom des colonnes)">(?)</div>
                <h2>Exporter un modèle:</h2>
                <form action="/dashboard" method="POST">
                    <div>
                        <label>
                            <span>Choisissez le modèle à exporter</span>
                            <select name="export_model" required>
                                <option disabled selected>Choisir</option>
                                <?php if (empty($this->category) || $this->category === 'students'): ?>
                                    <option value="student">Etudiants</option>
                                <?php endif; ?>

                                <?php if (empty($this->category) || $this->category === 'teachers'): ?>
                                    <option value="teacher">Enseignants</option>
                                <?php endif; ?>

                                <?php if (empty($this->category) || $this->category === 'internships'): ?>
                                    <option value="internship">Stages</option>
                                <?php endif; ?>
                            </select>
                        </label>
                    </div>
                    <div class="input-field">
                        <button class="btn waves-effect waves-light"
                                type="submit">Exporter
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}
