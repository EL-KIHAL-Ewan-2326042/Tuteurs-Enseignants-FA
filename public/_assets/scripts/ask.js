document.addEventListener('DOMContentLoaded', function() {
    // Vérifier si la table a déjà été initialisée
    if (!$.fn.DataTable.isDataTable('#homepage-table')) {
        initializeDataTable();
    }
});

// Fonction pour sélectionner tous les éléments visibles
function selectAllVisible() {
    let table = $('#homepage-table').DataTable();
    let currentMode = table.select.items();

    if (currentMode === 'row') {
        table.rows({page: 'current'}).select();
    } else {
        table.columns(':visible').select();
    }
}

// Fonction pour désélectionner tous les éléments
function deselectAll() {
    let table = $('#homepage-table').DataTable();
    let currentMode = table.select.items();

    if (currentMode === 'row') {
        table.rows().deselect();
    } else {
        table.columns().deselect();
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
        keys: true,
        fixedHeader: true,
        order: [],
        ordering: true,
        serverSide: true,
        stateSave: true,
        pageLength: 10,
        processing: true,
        // ""pagingType": "input",

        ajax: {
            url: '/api/datatable',
            type: 'POST',
            dataSrc: 'data',
            cache: true
        },
        columns: [
            {data: 'student'},
            {data: 'formation'},
            {data: 'group'},
            {data: 'history'},
            {data: 'company'},
            {data: 'subject'},
            {data: 'address'},
            {data: 'distance'}
        ],

        select: {
            style: 'multi',
            items: 'row'
        },

        language: {
            select: {
                rows: {_: "%d lignes sélectionnées", 0: "", 1: "1 ligne sélectionnée"},
                columns: "", cells: ""
            }
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
                        attr: {id: 'selectAllBtn', class: 'dt-button select-all-btn'},
                        action: selectAllVisible
                    },
                    {
                        text: 'Tout désélectionner',
                        attr: {id: 'deselectAllBtn', class: 'dt-button deselect-all-btn'},
                        action: deselectAll
                    }
                ]
            },
            topEnd: {
                search: {placeholder: 'Rechercher...'}
            },
            bottomStart: ['info'],
            bottomEnd: ['paging']
        }
    });
}