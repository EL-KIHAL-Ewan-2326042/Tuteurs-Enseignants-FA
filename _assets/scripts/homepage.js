/**
 * Partie1: Recherche etudiante
 */

/**
 * A chaque input de la recherche etudiant, on fetch les resultats
 * @type {HTMLElement}
 */
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.trim();

        if (searchTerm.length > 0) {
            fetchResults(searchTerm);
        }
    })
});

/**
 * Pour un string, on fait un post faisant une requête SQL à la BD
 * Enfin, on affiche les resultats retournés par la BD
 * @param query
 */
function fetchResults(query) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'search',
            search: query
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
 * On entour autour d'une balise a, et dès qu'elle est enclenché, on choisit l'etudiant
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
        a.textContent = `${student.num_eleve} - ${student.nom_eleve} ${student.prenom_eleve}`;
        a.classList.add('left-align');
        a.addEventListener('click', function(event) {
            event.preventDefault();
            selectStudent(student.num_eleve, student.nom_eleve, student.prenom_eleve);
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
    getStudentLocation().then();
}

/**
 * Partie2: Map Intéractive
 */

let map, directionsService, directionsRenderer, companyLocation, teacherLocation;

function geocodeAddress(address) {
    geocoder = new google.maps.Geocoder();
    if (geocoder) {
        geocoder.geocode({
            'address': address
        }, function (results, status) {
            if (status === google.maps.GeocoderStatus.OK) {
                return results[0];
            }
        });
    }
}

async function getStudentLocation() {
    try {
        companyLocation = await geocodeAddress('Marseille');
        teacherLocation = await geocodeAddress('Paris');
        if (teacherLocation && companyLocation) {
            initMap();
        }
    } catch (error) {
        console.error(error);
    }
}

function initMap() {
    const centerPoint = { lat: companyLocation.lat, lng: companyLocation.lng };

    map = new google.maps.Map(document.getElementById("map"), {
        center: centerPoint,
        zoom: 13,
    });

    directionsService = new google.maps.DirectionsService();
    directionsRenderer = new google.maps.DirectionsRenderer();

    directionsRenderer.setMap(map);

    calculateDistance();
}

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
                    reject('Erreur lors du calcul des routes: ' + status);
                }
            }
        );
    });
}

async function calculateDistance() {
    const origin = { lat: companyLocation.lat, lng: companyLocation.lng };
    const destination = { lat: teacherLocation.lat, lng: teacherLocation.lng };

    try {
        const response = await getDistanceMatrix(origin, destination);
        const result = response.rows[0].elements[0];

        const duration = result.duration.text;
        console.log(duration);

        await getRoute(origin, destination);
    } catch (error) {
        alert(error);
    }
}