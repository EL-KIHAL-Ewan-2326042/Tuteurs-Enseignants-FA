document.addEventListener('DOMContentLoaded', function() {
    // Initialisation des composants Materialize
    M.AutoInit();
    
    // Initialisation de DataTables
    initDataTable();

    // Initialisation de la carte si elle existe
    if (document.getElementById('map')) {
        initMap();
    }

    // Initialisation des événements
    initEvents();
});

function initDataTable() {
    const table = $('#dispatch-table').DataTable({
        responsive: true,
        language: {
            url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/fr-FR.json'
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Tout"]],
        order: [[7, 'desc']], // Tri par défaut sur la colonne score
        columnDefs: [{
            targets: -1, // Dernière colonne (checkbox)
            orderable: false
        }]
    });

    return table;
}

function initMap() {
    const map = L.map('map').setView([46.603354, 1.888334], 6); // Centre de la France
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const markers = {
        teacher: null,
        company: null,
        otherTeachers: []
    };
    let polyline = null;

    // Gestion du clic sur une ligne du tableau
    $('.dispatch-row').on('click', async function() {
        const data = $(this).data('internship-identifier').split('$');
        const companyName = data[0];
        const internshipId = data[1];
        const teacherId = data[2];
        const address = data[3];

        // Nettoyage de la carte
        clearMapMarkers(markers, polyline, map);

        // Géocodage et affichage sur la carte
        const companyCoords = await geocodeAddress(address);
        if (companyCoords) {
            displayCompanyOnMap(companyName, companyCoords, markers, map);
        }
    });

    return map;
}

function clearMapMarkers(markers, polyline, map) {
    if (markers.teacher) map.removeLayer(markers.teacher);
    if (markers.company) map.removeLayer(markers.company);
    markers.otherTeachers.forEach(m => map.removeLayer(m));
    markers.otherTeachers = [];
    if (polyline) map.removeLayer(polyline);
}

async function geocodeAddress(address) {
    try {
        const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
        const data = await response.json();
        if (data.length > 0) {
            return [parseFloat(data[0].lat), parseFloat(data[0].lon)];
        }
        return null;
    } catch (error) {
        console.error('Erreur de géocodage:', error);
        return null;
    }
}

function displayCompanyOnMap(companyName, coords, markers, map) {
    markers.company = L.marker(coords, {
        icon: L.divIcon({
            className: 'custom-marker-icon company',
            html: '<div></div>'
        })
    }).addTo(map).bindPopup(companyName);

    map.setView(coords, 10);
}

function initEvents() {
    // Gestion du chargement
    window.showLoading = function() {
        document.getElementById('loading-section').style.display = 'block';
        document.getElementById('forms-section').style.display = 'none';
    };
} 