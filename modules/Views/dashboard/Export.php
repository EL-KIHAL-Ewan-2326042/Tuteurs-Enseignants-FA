<?php
namespace Blog\Views\dashboard;

class Export
{
    public function __construct(private string $category = '')
    {
    }

    public function showView(): void
    {
        // Configuration pour chaque catégorie
        $config = [
            'students' => [
                'title' => 'Exporter des données d\'étudiants',
                'table_name' => 'student',
                'display_name' => 'étudiants'
            ],
            'teachers' => [
                'title' => 'Exporter des données d\'enseignants',
                'table_name' => 'teacher',
                'display_name' => 'enseignants'
            ],
            'internships' => [
                'title' => 'Exporter des données de stages',
                'table_name' => 'internship',
                'display_name' => 'stages'
            ]
        ];

        ?>

        <div class="row">
            <?php
            if (empty($this->category)) {
                echo '<div class="file-field input-field"><p>Veuillez sélectionner une catégorie.</p></div>';
                return;
            }
            elseif (!isset($config[$this->category])) {
                echo '<div class="file-field input-field"><p>Catégorie non reconnue.</p></div>';
                return;
            }

            // Récupérer la configuration pour cette catégorie
            $currentConfig = $config[$this->category];
            ?>

            <h3><?php echo $currentConfig['title']; ?></h3>

            <div class="card-panel white export-container">
                <div class="switch center-align export-item">
                    <label>
                        Liste complète
                        <input type="checkbox" id="export-type-toggle" class="export-toggle">
                        <span class="lever"></span>
                        Modèle vide
                    </label>
                </div>

                <form action="/dashboard" method="POST" id="export-form" class="export-item">
                    <input type="hidden" id="export-field" name="export_list" value="<?php echo $currentConfig['table_name']; ?>">

                    <div class="center-align export-item">
                        <div id="list-info" class="tooltip-container" data-tooltip="Exportation des données complètes dans un fichier CSV">
                            <p>Vous allez exporter la liste complète des <?php echo $currentConfig['display_name']; ?>.</p>
                        </div>
                        <div id="model-info" class="tooltip-container" style="display: none;" data-tooltip="Exportation d'un modèle vide avec uniquement les en-têtes de colonnes">
                            <p>Vous allez exporter un modèle vide pour <?php echo $currentConfig['display_name']; ?>.</p>
                        </div>
                    </div>

                    <div class="input-field center-align export-item">
                        <button class="btn waves-effect waves-light" type="submit">
                            Exporter
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }
}