<?php

namespace Blog\Views\components;

class Table
{
    /**
     * Génère une table HTML + JS d'initialisation DataTable
     *
     * @param string $id         L'ID de la table HTML
     * @param array  $columns    Titres des colonnes
     * @param array  $jsColumns  Tableau JS formaté pour DataTables (ex: [{data: 'student'}, ...])
     * @param string $ajaxUrl    URL de la source Ajax
     *
     * @return void
     */
    public static function render(string $id, array $columns, array $jsColumns, string $ajaxUrl, bool $paginationEnabled = true): void
    {
        echo '<table id="' . htmlspecialchars($id) . '" class="display nowrap" style="width:100%">';
        echo '<thead><tr>';
        foreach ($columns as $column) {
            echo '<th>' . htmlspecialchars($column) . '</th>';
        }
        echo '</tr></thead>';
        echo '</table>';
        $jsColumnsJson = json_encode($jsColumns);
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('<?= $id ?>', '<?= $ajaxUrl ?>', <?= $jsColumnsJson ?>, <?= json_encode($paginationEnabled) ?>);
            });
        </script>


        <?php
    }
}
