/**
 * Partie1: Recherche etudiante
 */

/**
 * A chaque input de la recherche etudiant, on fetch les resultats
 * @type {HTMLElement}
 */
document.addEventListener('DOMContentLoaded', function() {
    M.Tooltip.init(document.querySelectorAll('.tooltip'), {
        exitDelay: 100,
    });

    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems);

    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('searchResults');
    const searchType = document.getElementById('searchType');
    searchResults.innerHTML = '<p>Barre de recherche vide</p>'

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.trim();

        if (searchTerm.length > 0) {
            fetchResults(searchTerm, searchType.value);
        }
        else {
            searchResults.innerHTML = '<p>Barre de recherche vide</p>'
        }
    })
});

/**
 * Pour un string, on fait un post faisant une requête SQL à la BD
 * Enfin, on affiche les resultats retournés par la BD selon le type de recherche
 * @param query la recherche en elle-même
 * @param searchType numéro etudiant, nom de famille, ...
 */
function fetchResults(query, searchType) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'search',
            search: query,
            searchType: searchType
        })
    })
        .then(response => response.json())
        .then(data => {
            displayResults(data);
        })
        .catch(error => {
            console.error('Erreur fetch resultats:', error);
    });
}

/**
 * Selon les resultats renvoyés par la BD, on affiche le num, nom et prenom etudiant
 * On entour autour d'une balise a, et dès qu'elle est enclenché, on choisi l'etudiant
 * @param data
 */
function displayResults(data) {
    if (searchResults) {
        searchResults.innerHTML = '';
    }

    if (data.length === 0) {
        if (searchResults) {
            searchResults.innerHTML = '<p>Aucun étudiant trouvé</p>';
        }
        return;
    }

    const ul = document.createElement('ul');
    data.forEach(student => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = '#';
        if (student.company_name) {
            a.textContent = `${student.company_name}: ${student.student_number} - ${student.student_name} ${student.student_firstname}`;
        }
        else {
            a.textContent = `${student.student_number} - ${student.student_name} ${student.student_firstname}`;
        }
        a.classList.add('left-align');
        a.addEventListener('click', function(event) {
            event.preventDefault();
            selectStudent(student.student_number, student.student_name, student.student_firstname);
        });
        li.appendChild(a);
        ul.appendChild(li);
    });
    searchResults.appendChild(ul);
}

/**
 * Pour l'étudiant choisie, on crée un form discret et on l'envoie en tant que requête POST
 * Avec les informations données en paramètre
 * @param studentId
 * @param studentFirstName
 * @param studentLastName
 */
function selectStudent(studentId, studentFirstName, studentLastName) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = window.location.href;

    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'student_id';
    inputId.value = studentId;

    const inputFirstName = document.createElement('input');
    inputFirstName.type = 'hidden';
    inputFirstName.name = 'student_firstName';
    inputFirstName.value = studentFirstName;

    const inputLastName = document.createElement('input');
    inputLastName.type = 'hidden';
    inputLastName.name = 'student_lastName';
    inputLastName.value = studentLastName;

    const inputAction = document.createElement('input');
    inputAction.type = 'hidden';
    inputAction.name = 'action';
    inputAction.value = 'select_student';

    form.appendChild(inputId);
    form.appendChild(inputFirstName);
    form.appendChild(inputLastName);
    form.appendChild(inputAction);

    document.body.appendChild(form);

    form.submit();
}

/**
 * Partie2: Map Intéractive
 */

/**
 * Initialise la carte en fonction des adresses du professeur et de l'entreprise
 * @returns {Promise<void>}
 */
async function initMap() {
    const mapElement = document.getElementById("map");

    if (!mapElement) {
        return;
    }

    if (typeof companyAddress === "undefined" || typeof teacherAddress === "undefined") {
        console.error("'companyAddress' ou 'teacherAddress' est indéfini.");
        return;
    }

    try {
        const companyLocation = await geocodeAddress(companyAddress);
        const teacherLocation = await geocodeAddress(teacherAddress);

        const map = new ol.Map({
            target: mapElement,
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM(),
                }),
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([
                    (companyLocation.lon + teacherLocation.lon) / 2,
                    (companyLocation.lat + teacherLocation.lat) / 2,
                ]),
                zoom: 6,
            }),
        });

        const companyMarker = new ol.Overlay({
            position: ol.proj.fromLonLat([companyLocation.lon, companyLocation.lat]),
            element: createMarkerElement("Entreprise"),
        });
        const teacherMarker = new ol.Overlay({
            position: ol.proj.fromLonLat([teacherLocation.lon, teacherLocation.lat]),
            element: createMarkerElement("Vous"),
        });

        map.addOverlay(companyMarker);
        map.addOverlay(teacherMarker);

        await calculateDistance(companyLocation, teacherLocation, map);
    } catch (error) {
        console.error("Erreur lors de l'initialisation de la carte :", error);
    }
}

/**
 * Géocode une adresse
 * @param {string} address Adresse à géocoder
 * @returns {Promise<Object>} Localisation géocodée { lat, lon }
 */
async function geocodeAddress(address) {
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(
        address
    )}&format=json&limit=1`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.length > 0 && data[0].lat && data[0].lon) {
            return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
        } else {
            throw new Error("Le géocodage n'a retourné aucun résultat.");
        }
    } catch (error) {
        console.error("Erreur de géocodage :", error);
        throw error;
    }
}

/**
 * Calcule et affiche la route entre deux points
 * @param {Object} origin Coordonnées de l'origine { lat, lon }
 * @param {Object} destination Coordonnées de la destination { lat, lon }
 * @param {Object} map Instance de la carte OpenLayers
 */
async function calculateDistance(origin, destination, map) {
    const url = `https://router.project-osrm.org/route/v1/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.routes && data.routes.length > 0) {
            const route = data.routes[0];

            const routeCoords = route.geometry.coordinates.map((coord) =>
                ol.proj.fromLonLat(coord)
            );

            const routeLayer = new ol.layer.Vector({
                source: new ol.source.Vector({
                    features: [
                        new ol.Feature({
                            geometry: new ol.geom.LineString(routeCoords),
                        }),
                    ],
                }),
                style: new ol.style.Style({
                    stroke: new ol.style.Stroke({
                        color: "red",
                        width: 3,
                    }),
                }),
            });

            map.addLayer(routeLayer);
        } else {
            console.error("Aucun itinéraire trouvé.");
        }
    } catch (error) {
        console.error("Erreur lors de la récupération de l'itinéraire :", error);
    }
}

/**
 * Crée un élément de marqueur
 * @param {string} label Étiquette du marqueur
 * @returns {HTMLElement} Élément du marqueur
 */
function createMarkerElement(label) {
    const marker = document.createElement("div");
    marker.className = "marker";
    marker.textContent = label;
    marker.style.backgroundColor = "blue";
    marker.style.color = "white";
    marker.style.padding = "5px";
    marker.style.borderRadius = "50%";
    marker.style.textAlign = "center";
    return marker;
}

/**
 * Partie3: Tri du tableau et pagination
 */

document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById("homepage-table") === null) {
        return;
    }

    const rowsPerPageDropdown = document.getElementById('rows-per-page');
    let rowsPerPage = sessionStorage.getItem("rowsCount") ? Number(sessionStorage.getItem("rowsCount")) : parseInt(rowsPerPageDropdown.value); // Set default to 10
    if (rowsPerPage !== 10) {
        rowsPerPageDropdown.options[rowsPerPage === 20 ? 1 : rowsPerPage === 50 ? 2 : rowsPerPage === 100 ? 3 : 4].selected = true;
    }
    sessionStorage.setItem("rowsCount", String(rowsPerPage));

    let rows = document.querySelectorAll('.homepage-row');
    let totalRows = rows.length;
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    let currentPage = sessionStorage.getItem("page") && Number(sessionStorage.getItem("page")) <= totalPages
    && rowsPerPage === 10 ? Number(sessionStorage.getItem("page")) : 1;

    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const firstButton = document.getElementById('first-page');
    const lastButton = document.getElementById('last-page');
    const pageNumbersContainer = document.getElementById('page-numbers');

    if (document.getElementById("homepage-table").rows.length > 2) {
        if (!(sessionStorage.getItem('columnNumber') && sessionStorage.getItem('direction'))) {
            sessionStorage.setItem('columnNumber', "0");
            sessionStorage.setItem('direction', "asc");
        }
        sortTable(Number(sessionStorage.getItem('columnNumber')), true);

        for (let i = 0; i < document.getElementById("homepage-table").rows[0].cells.length; ++i) {
            document.getElementById("homepage-table").rows[0].getElementsByTagName("TH")[i].addEventListener('click', () => {
                sortTable(i);
            });
        }
    }

    /**
     * Trie la table prenant pour id "homepage-table"
     * @param n numéro désignant la colonne par laquelle on trie le tableau
     * @param firstLoad booléen indiquant si cet appel est le premier depuis le chargement de la page
     */
    function sortTable(n, firstLoad = false) {
        let dir, rows, switching, i, x, y, shouldSwitch, column;
        const table = document.getElementById("homepage-table");
        switching = true;

        if (!firstLoad) {
            if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") dir = "desc";
            else dir = "asc";
        } else dir = sessionStorage.getItem('direction');

        while (switching) {
            switching = false;
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); ++i) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                if (dir === "asc") {
                    if ((n < 7 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                        || (n === 7 && Number(x.innerHTML.substring(1, x.innerHTML.indexOf(' '))) > Number(y.innerHTML.substring(1, y.innerHTML.indexOf(' '))))
                        || (n === 8 && x.getElementsByTagName("INPUT")[0].checked < y.getElementsByTagName("INPUT")[0].checked)) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir === "desc") {
                    if ((n < 7 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                        || (n === 7 && Number(x.innerHTML.substring(1, x.innerHTML.indexOf(' '))) < Number(y.innerHTML.substring(1, y.innerHTML.indexOf(' '))))
                        || (n === 8 && x.getElementsByTagName("INPUT")[0].checked > y.getElementsByTagName("INPUT")[0].checked)) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
        for (i = 0; i < rows[0].cells.length; ++i) {
            column = rows[0].getElementsByTagName("TH")[i].innerHTML;
            if (column.substring(column.length-1) === "▲" || column.substring(column.length-1) === "▼") table.rows[0].getElementsByTagName("TH")[i].innerHTML = column.substring(0, column.length-2);
            if (i === n) {
                if (dir === "asc") table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                else table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
            }
        }

        sessionStorage.setItem('columnNumber', n);
        sessionStorage.setItem('direction', dir);
        showPage(currentPage);
    }

    function showPage(page) {
        if (page < 1 || page > totalPages) return;

        rows = document.querySelectorAll('.homepage-row');

        currentPage = page;
        updatePageNumbers();

        rows.forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = currentPage * rowsPerPage;
        const visibleRows = Array.from(rows).slice(start, end);
        visibleRows.forEach(row => row.style.display = '');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
        firstButton.disabled = currentPage === 1;
        lastButton.disabled = currentPage === totalPages;

        sessionStorage.setItem('page', currentPage);
    }

    function updatePageNumbers() {
        pageNumbersContainer.innerHTML = '';

        const maxVisiblePages = 5;
        const halfWindow = Math.floor(maxVisiblePages / 2);
        let startPage = Math.max(currentPage - halfWindow, 1);
        let endPage = Math.min(currentPage + halfWindow, totalPages);

        if (endPage - startPage + 1 < maxVisiblePages) {
            if (startPage === 1) {
                endPage = Math.min(startPage + maxVisiblePages - 1, totalPages);
            } else if (endPage === totalPages) {
                startPage = Math.max(endPage - maxVisiblePages + 1, 1);
            }
        }

        if (startPage > 1) {
            createPageButton(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            createPageButton(i, i === currentPage);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            createPageButton(totalPages);
        }
    }

    function createPageButton(page, isActive = false) {
        const pageNumberButton = document.createElement('button');
        pageNumberButton.textContent = page;
        pageNumberButton.classList.add('waves-effect', 'waves-light', 'btn');
        pageNumberButton.classList.add('page-number');
        pageNumberButton.disabled = isActive;
        pageNumberButton.addEventListener('click', () => showPage(page));
        pageNumbersContainer.appendChild(pageNumberButton);
    }

    function addEllipsis() {
        const ellipsis = document.createElement('span');
        ellipsis.textContent = '...';
        ellipsis.classList.add('pagination-ellipsis');
        pageNumbersContainer.appendChild(ellipsis);
    }

    rowsPerPageDropdown.addEventListener('change', function () {
        rowsPerPage = parseInt(rowsPerPageDropdown.value);
        sessionStorage.setItem("rowsCount", String(rowsPerPage));
        totalPages = Math.ceil(rows.length / rowsPerPage);
        currentPage = 1;
        showPage(currentPage);
    });

    firstButton.addEventListener('click', () => showPage(1));
    lastButton.addEventListener('click', () => showPage(totalPages));
    prevButton.addEventListener('click', () => showPage(currentPage - 1));
    nextButton.addEventListener('click', () => showPage(currentPage + 1));

    window.addEventListener('resize', function () {
        totalRows = rows.length;
        totalPages = Math.ceil(totalRows / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        showPage(currentPage);
    });

    showPage(currentPage);
});