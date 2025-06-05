<?php
namespace Blog\Views\dashboard;

class Import
{
    public function __construct(private string $category = '')
    {
    }

    public function showView(): void
    {

        $config = [
        'students' => [
        'title' => 'Rajouter des étudiants',
        'table_name' => 'student',
        'input_name' => 'student',
        'submit_name' => 'submit_student'
        ],
        'teachers' => [
        'title' => 'Rajouter des enseignants',
        'table_name' => 'teacher',
        'input_name' => 'teacher',
        'submit_name' => 'submit_teacher'
        ],
        'internships' => [
        'title' => 'Rajouter des stages',
        'table_name' => 'internship',
        'input_name' => 'internship',
        'submit_name' => 'submit_internship'
        ]
        ];

        ?>

        <div class="row">
            <?php
            if (empty($this->category)) {
            echo '<div class="file-field input-field"><p>Veuillez sélectionner une catégorie.</p></div>';
            return;
            }
            else if (!isset($config[$this->category])) {
                echo '<div class="file-field input-field"><p>Catégorie non reconnue.</p></div>';
                return;
            }

            // Récupérer la configuration pour cette catégorie
            $currentConfig = $config[$this->category];
            ?>
            <h3><?php echo $currentConfig['title']; ?></h3>
            <form action="/dashboard" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="table_name" value="<?php echo $currentConfig['table_name']; ?>">
                <div class="file-field input-field">
                    <div class="btn">
                        <i class="material-icons">cloud_upload</i>
                        <label for="<?php echo $currentConfig['input_name']; ?>" style="cursor: pointer;">
                            <span>Choisir un fichier CSV</span>
                        </label>
                        <input type="file"
                               name="<?php echo $currentConfig['input_name']; ?>"
                               id="<?php echo $currentConfig['input_name']; ?>"
                               accept=".csv"
                               multiple
                               style="display: none;">
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text"
                               readonly placeholder="Aucun fichier sélectionné" id="file-path-display">
                    </div>
                    <button class="btn waves-effect waves-light"
                            type="submit" name="<?php echo $currentConfig['submit_name']; ?>">Valider
                        <i class="material-icons right">send</i>
                    </button>
                </div>
            </form>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const fileInput = document.getElementById('<?php echo $currentConfig['input_name']; ?>');
                const filePathDisplay = document.getElementById('file-path-display');

                fileInput.addEventListener('change', function() {
                    if (fileInput.files.length > 0) {
                        filePathDisplay.value = fileInput.files[0].name;
                    } else {
                        filePathDisplay.value = '';
                    }
                });
        </script>
        <?php
    }
}