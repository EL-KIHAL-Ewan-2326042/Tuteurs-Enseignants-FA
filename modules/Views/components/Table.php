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
    public static function render(string $id, array $columns, array $jsColumns, string $ajaxUrl): void
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
                if (!$.fn.DataTable.isDataTable('#<?= $id ?>')) {
                    new DataTable('#<?= $id ?>', {
                        scrollX: true,
                        keys: true,
                        fixedHeader: true,
                        order: [],
                        ordering: true,
                        serverSide: true,
                        stateSave: false,
                        pageLength: 10,
                        processing: true,
                        ajax: {
                            url: '<?= $ajaxUrl ?>',
                            type: 'POST',
                            dataSrc: 'data',
                        },
                        columns: <?= $jsColumnsJson ?>,
                        select: {
                            style: 'multi',
                            items: 'row'
                        },
                        lengthMenu: [10, 20, 50, 100],
                        language: {
                            select: {
                                rows: {_: "%d lignes sélectionnées", 0: "", 1: "1 ligne sélectionnée"},
                                columns: "", cells: ""
                            },
                            buttons: {
                                copy: 'Copier',
                                print: 'Imprimer',
                                colvis: "Visibilité colonnes",
                                colvisRestore: "Rétablir visibilité",
                            },
                            lengthMenu: "Afficher _MENU_ entrées",
                            search: '',
                            info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                            infoEmpty: 'Aucune entrée',
                            infoFiltered: "(filtrées depuis un total de _MAX_ entrées)"
                        },
                        layout: {
                            topStart: {
                                buttons: [
                                    {
                                        extend: 'copy',
                                        exportOptions: {columns: ':visible'}
                                    },
                                    {
                                        extend: 'excel',
                                        exportOptions: {columns: ':visible'}
                                    },
                                    {
                                        extend: 'csv',
                                        exportOptions: {columns: ':visible'}
                                    },
                                    {
                                        extend: 'print',
                                        exportOptions: {columns: ':visible'}
                                    },
                                    'colvis',
                                    {
                                        text: 'Tout sélectionner',
                                        attr: {id: 'toggleSelectBtn', class: 'dt-button toggle-select-btn'},
                                        action: function () {
                                            let table = $('#<?= $id ?>').DataTable();
                                            let selected = table.rows({selected: true}).count();
                                            if (selected > 0) {
                                                table.rows().deselect();
                                                $('#toggleSelectBtn').text('Tout sélectionner');
                                            } else {
                                                table.rows({page: 'current'}).select();
                                                $('#toggleSelectBtn').text('Tout désélectionner');
                                            }
                                        }
                                    }
                                ],
                            },
                            topEnd: {
                                search: {placeholder: 'Rechercher...'},
                            },
                            bottomStart: ['pageLength', 'info'],
                            bottomEnd: ['paging']
                        }
                    });
                }
            });
        </script>
        <?php
    }
}
