/**
 * Partie1: Recherche etudiante
 */

/**
 * A chaque input de la recherche etudiant, on fetch les resultats
 * @type {HTMLElement}
 */
document.addEventListener('DOMContentLoaded', function() {
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

let companyLocation, teacherLocation;
let directionsService, directionsRenderer;

/**
 * Appellé lors du chargement de la homepage, initialise la map selon l'addresse du professeur et du stage
 * @returns {Promise<void>}
 */
async function initMap() {
    if (typeof companyAddress === 'undefined' || typeof teacherAddress === 'undefined') {
        return;
    }

    companyLocation = await geocodeAddress(companyAddress);
    teacherLocation = await geocodeAddress(teacherAddress);

    const centerPoint = { lat: teacherLocation.lat, lng: teacherLocation.lng };

    const map = new google.maps.Map(document.getElementById("map"), {
        center: centerPoint,
        zoom: 13,
        mapId: "HOMEPAGE"
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer({
        suppressMarkers: true
    });


    const companyMarker = new google.maps.marker.AdvancedMarkerElement ({
        map,
        position: companyLocation,
        title: "Company",
        content: document.createElement('div'),
    });

    const teacherMarker = new google.maps.marker.AdvancedMarkerElement ({
        map,
        position: teacherLocation,
        title: "Teacher",
        content: document.createElement('div'),
    });

    companyMarker.content.innerHTML = `
    <div style="
        background-color: white;
        padding: 5px;
        border: 1px solid black;
        border-radius: 4px;
        font-size: 16px;
        color: black;
        text-align: center;
    ">
        Entreprise
    </div>
    `;

    teacherMarker.content.innerHTML = `
    <div style="
        background-color: white;
        padding: 5px;
        border: 1px solid black;
        border-radius: 4px;
        font-size: 16px;
        color: black;
        text-align: center;
    ">
        Domicile
    </div>
    `;

    directionsRenderer.setMap(map);

    await calculateDistance({ lat: companyLocation.lat, lng: companyLocation.lng }, { lat: teacherLocation.lat, lng: teacherLocation.lng });
}

/**
 * Calcul la durée du trajet entre un point d'origine et la destination
 * @param origin
 * @param destination
 * @returns {Promise<unknown>}
 */
function getDistanceMatrix(origin, destination) {
    return new Promise((resolve, reject) => {
        const service = new google.maps.DistanceMatrixService();
        service.getDistanceMatrix(
            {
                origins: [origin],
                destinations: [destination],
                travelMode: google.maps.TravelMode.DRIVING,
                unitSystem: google.maps.UnitSystem.METRIC,
            },
            (response, status) => {
                if (status === 'OK') {
                    resolve(response);
                } else {
                    reject('Erreur: ' + status);
                }
            }
        );
    });
}

/**
 * Permet d'avoir la route la plus optimale, en voiture, entre le point d'origine et de destination
 * @param origin
 * @param destination
 * @returns {Promise<unknown>}
 */
function getRoute(origin, destination) {
    return new Promise((resolve, reject) => {
        directionsService.route(
            {
                origin: origin,
                destination: destination,
                travelMode: 'DRIVING',
                drivingOptions: {
                    departureTime: new Date(),
                    trafficModel: 'pessimistic'
                },
            },
            (response, status) => {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                    resolve(response);
                } else {
                    if (status === 'UNKNOWN_ERROR') {
                        reject('Distance trop loin pour être calculée');
                    }
                    else {
                        reject('Erreur lors du calcul des routes: ' + status);
                    }
                }
            }
        );
    });
}

/**
 * Geocode une adresse en lattitude et longitude
 * @param address
 * @returns {Promise<unknown>}
 */
function geocodeAddress(address) {
    const geocoder = new google.maps.Geocoder();

    return new Promise((resolve, reject) => {
        geocoder.geocode({ 'address': address }, (results, status) => {
            if (status === 'OK') {
                const location = results[0].geometry.location;
                resolve({ lat: location.lat(), lng: location.lng() });
            } else {
                reject('Geocoding failed: ' + status);
            }
        });
    });
}

/**
 * Calcul la distance et la durée renvoyées pour la matrix
 * @returns {Promise<void>}
 */
async function calculateDistance(origin, destination) {
    try {
        const response = await getDistanceMatrix(origin, destination);
        const result = response.rows[0].elements[0];

        if(origin === companyLocation || destination === teacherLocation) {
            await getRoute(origin, destination);
        } else {
            return result.duration;
        }

    } catch (error) {
        alert(error);
    }
}

