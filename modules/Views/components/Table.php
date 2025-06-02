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
        <style>
            table.dataTable {
                background-color: var(--couleur-blanc);
                border-collapse: collapse;
                width: 100%;
                border-radius: 8px 8px  0 0;
                overflow: hidden;
                box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            }
            /* Table header */
            table.dataTable thead {
                background-color: var(--couleur-bleu-roi);
                color: var(--couleur-blanc);
            }
            table.dataTable thead th {
                padding: .5rem 1rem;
                font-size: 0.9rem;
                text-align: left;
                border-bottom: 2px solid var(--couleur-bleu-roi);
            }

            /* Table body */
            table.dataTable tbody tr {
                transition: background-color 0.3s ease;
            }

            table.dataTable tbody tr:nth-child(even) {
                background-color: var(--couleur-bleu-pale);
            }

            table.dataTable tbody tr:hover {
                background-color: var(--couleur-jaune);
            }

            table.dataTable tbody td {
                padding: 10px 14px;
                font-size: 0.85rem;
                border-bottom: 1px solid #e0e0e0;
            }

            #homepage-table_wrapper button, #homepage-table_wrapper input {
                transition: all .2s ease-in;
                background: transparent;
                border: 1px solid var(--couleur-bleu-roi);

                color: var(--couleur-bleu-roi);
            }
            #homepage-table_wrapper button:hover {
                transition: all .2s ease-in;
                background-color: var(--couleur-bleu);
                border: 1px solid var(--couleur-bleu);
                color: var(--couleur-jaune);

            }

            .dt-buttons {
                display: flex;
                flex-basis: max-content;
                flex-flow: row wrap;
                font-size: 0.95rem;
            }

            .dt-buttons span {
                height: 100%;
            }


        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                window.initDataTable('<?= $id ?>', '<?= $ajaxUrl ?>', <?= $jsColumnsJson ?>, <?= json_encode($paginationEnabled) ?>);
            });
        </script>


        <?php
    }
}
