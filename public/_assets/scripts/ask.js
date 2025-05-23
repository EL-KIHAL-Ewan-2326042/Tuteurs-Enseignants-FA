document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si la table a déjà été initialisée
    if (!$.fn.DataTable.isDataTable('#homepage-table')) {
        initializeDataTable();
    }
});

function toggleSelection() {
    let table = $('#homepage-table').DataTable();
    let selectedRows = table.rows({selected: true}).count();
    let toggleButton = $('#toggleSelectBtn');

    if (selectedRows > 0) {
        table.rows().deselect();
        toggleButton.text('Tout sélectionner');
    } else {
        table.rows({page: 'current'}).select();
        toggleButton.text('Tout désélectionner');
    }
}

function initializeDataTable() {

    fetch('/api/datatable', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            console.log('Contenu JSON :', data);
        })
        .catch(error => {
            console.error('Erreur :', error);
        });


    new DataTable('#homepage-table', {
        scrollX: true,
        keys: true,
        fixedHeader: true,
        order: [],
        ordering: true,
        serverSide: true,
        stateSave: false,
        pageLength: 10,
        processing: true,
        // ""pagingType": "input",

        ajax: {
            url: '/api/datatable',
            type: 'POST',
            dataSrc: 'data',
        },
        columns: [
            {data: 'student'},
            {data: 'formation'},
            {data: 'group'},
            {data: 'history'},
            {data: 'company'},
            {data: 'subject'},
            {data: 'address'},
            {data: 'distance'},
        ],

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
                        action: function(e, dt, node, config) {
                            toggleSelection();
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