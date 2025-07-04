const toggleMenu = document.getElementById('toggleMenu');
const mainNav = document.getElementById('mainNav');

toggleMenu.onclick = function() {
    if (mainNav.style.display === 'none') {
        mainNav.style.display = 'flex';
    } else {
        mainNav.style.display = 'none';
    }
};

function disconnect(event) {
    if (event) event.preventDefault();

    Swal.fire({
        title: 'Êtes-vous sûr ?',
        text: 'Vous allez être déconnecté.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#1F63DE',
        cancelButtonColor: '#990000',
        confirmButtonText: 'Oui, déconnectez-moi !',
        cancelButtonText: 'Annuler'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '/logout.php';
        }
    });

    return false;
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
        lengthMenu: [10, 20, 50, 100, 1000],

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
const toastQueue = [];

function showToast(message, type = "info") {
    const colors = {
        success: "#4CAF50",
        error: "#f44336",
        info: "#2196F3"
    };

    const toast = document.createElement("div");
    toast.textContent = message;
    toast.style.position = "fixed";
    toast.style.bottom = "30px";
    toast.style.right = "30px";
    toast.style.backgroundColor = colors[type] || colors.info;
    toast.style.color = "white";
    toast.style.padding = "10px 20px";
    toast.style.borderRadius = "5px";
    toast.style.boxShadow = "0 2px 8px rgba(0,0,0,0.3)";
    toast.style.fontSize = "16px";
    toast.style.zIndex = 9999;
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.5s";

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = "1";
    }, 10);

    setTimeout(() => {
        toast.style.opacity = "0";
        setTimeout(() => {
            toast.remove();
        }, 500);
    }, 3000);
}

function processToastQueue() {
    if (toastQueue.length === 0) return;
    const { message, type } = toastQueue.shift();
    showToast(message, type);
    setTimeout(processToastQueue, 3500); // Laisse le temps d’affichage + animation
}

function addToast(message, type = "info") {
    toastQueue.push({ message, type });
    if (toastQueue.length === 1) {
        processToastQueue();
    }
}
