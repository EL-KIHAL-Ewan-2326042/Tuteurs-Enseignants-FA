const toggleMenu = document.getElementById('toggleMenu');
const mainNav = document.getElementById('mainNav');

toggleMenu.onclick = function() {
    if (mainNav.style.display === 'none') {
        mainNav.style.display = 'flex';
    } else {
        mainNav.style.display = 'none';
    }
};

function disconnect() {
    if (confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
        window.location.href = '/intramu';
    }
}

function initDataTable(id, ajaxUrl, columns, paginationEnabled = true) {
    if ($.fn.DataTable.isDataTable('#' + id)) {
        $('#' + id).DataTable().ajax.url(ajaxUrl).load();
        return;
    }

    new DataTable('#' + id, {
        scrollX: true,
        responsive: true,
        keys: true,
        order: [],
        ordering: true,
        serverSide: true,
        stateSave: false,
        pageLength: 10,
        processing: true,
        ajax: {
            url: ajaxUrl,
            type: 'POST',
            dataSrc: 'data',
        },
        columns: columns,
        select: {
            items: 'row'
        },
        lengthMenu: [10, 20, 50, 100],

        // Désactive pagination / info si paginationEnabled = false
        paging: paginationEnabled,
        info: paginationEnabled,
        lengthChange: paginationEnabled,

        language: {
            select: {
                rows: {
                    _: "%d lignes sélectionnées",
                    0: "",
                    1: "1 ligne sélectionnée"
                },
                columns: "",
                cells: ""
            },
            buttons: {
                copy: 'Copier',
                print: 'Imprimer',
                colvis: "Afficher / masquer",
                colvisRestore: "Rétablir visibilité",
            },
            emptyTable: "Aucune donnée disponible dans le tableau",
            lengthMenu: paginationEnabled ? "Afficher _MENU_ entrées" : "",
            search: '',
            info: paginationEnabled ? "Affichage de _START_ à _END_ sur _TOTAL_ entrées" : "",
            infoEmpty: paginationEnabled ? 'Aucune entrée' : '',
            infoFiltered: paginationEnabled ? "(filtrées depuis un total de _MAX_ entrées)" : ''
        },
        layout: {
            topStart: {
                buttons: [
                    {
                        extend: 'copy',
                        text: '<i class="material-icons tiny">content_copy</i> Copier',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'excel',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'csv',
                        exportOptions: { columns: ':visible' }
                    },
                    {
                        extend: 'print',
                        text: '<i class="material-icons tiny">print</i> Imprimer',
                        exportOptions: { columns: ':visible' }
                    },
                    'colvis',
                    {
                        text: '<i class="material-icons tiny">select_all</i> <span id="selectText">Tout sélectionner</span>',
                        attr: { id: 'toggleSelectBtn', class: 'dt-button toggle-select-btn' },
                        action: function () {
                            let table = $('#' + id).DataTable();
                            let selected = table.rows({ selected: true }).count();
                            if (selected > 0) {
                                table.rows().deselect();
                                $('#toggleSelectBtn').text('Tout sélectionner');
                            } else {
                                table.rows({ page: 'current' }).select();
                                $('#toggleSelectBtn').text('Tout désélectionner');
                            }
                        }
                    }
                ],
            },
            topEnd: {
                search: { placeholder: 'Rechercher...' },
            },

            // Affiche ou cache selon paginationEnabled
            bottomStart: paginationEnabled ? ['pageLength', 'info'] : [],
            bottomEnd: paginationEnabled ? ['paging'] : []
        }
    });
}

