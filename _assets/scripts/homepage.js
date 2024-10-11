/**
 * Recherche etudiant
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchResults = document.getElementById('searchResults');

    searchInput.addEventListener('input', function() {
        const searchTerm = searchInput.value.trim();

        if (searchTerm.length > 0) {
            fetchResults(searchTerm);
        } else {
            searchResults.innerHTML = '';
        }
    });

    function fetchResults(query) {
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
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

    function displayResults(data) {
        searchResults.innerHTML = '';
        if (data.length === 0) {
            searchResults.innerHTML = '<p>Aucun résultat trouvé</p>';
            return;
        }

        const ul = document.createElement('ul');
        data.forEach(student => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = '#';
            a.textContent = `${student.num_eleve} - ${student.nom_eleve}`;
            a.addEventListener('click', function(event) {
                event.preventDefault();
            });
            li.appendChild(a);
            ul.appendChild(li);
        });
        searchResults.appendChild(ul);
    }
});

/**
 * Google Map
 */

let map;
let directionsService;
let directionsRenderer;

function initMap() {
    const centerPoint = { lat: 43.513648188004844, lng: 5.45114076845909 };

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
                travelMode: 'WALKING',
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
                travelMode: 'WALKING',
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
    const origin = { lat: 43.513648188004844, lng: 5.45114076845909 };
    const destination = { lat: 43.52388554394745, lng: 5.442837810787649 };

    try {
        const response = await getDistanceMatrix(origin, destination);
        const result = response.rows[0].elements[0];
        const distance = result.distance.text;
        const duration = result.duration.text;

        document.getElementById('output').innerHTML = `
                  Distance: ${distance} <br> 
                  Temps estimé: ${duration}
              `;

        await getRoute(origin, destination);
    } catch (error) {
        alert(error);
    }
}