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
                if (!$.fn.DataTable.isDataTable('#<?= $id ?>')) {
                    new DataTable('#<?= $id ?>', {
                        scrollX: true,
                        responsive: true,
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
                                colvis: "Afficher / masquer",
                                colvisRestore: "Rétablir visibilité",
                            },
                            emptyTable: "Aucune donnée disponible dans le tableau",
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
                                        text: '<i class="material-icons tiny">content_copy</i> Copier',
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
                                        text: '<i class="material-icons tiny">print</i> Imprimer',
                                        exportOptions: {columns: ':visible'}
                                    },
                                    'colvis',
                                    {
                                        text: '<i class="material-icons tiny">select_all</i> <span id="selectText">Tout sélectionner</span>',
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
