document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('map').setView([43.2965, 5.3698], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let markers = [], teacherMarker = null, teacherCoord = null;
    const toggleIcon = document.getElementById('toggleIcon');
    const toggleBtn = document.getElementById('toggleViewBtn');
    const tableCont = document.getElementById('tableContainer');
    const stageCont = document.getElementById('viewStageContainer');

    // ==================== Geocoding Helpers with Cache ====================
    const geoCache = JSON.parse(localStorage.getItem('geoCache') || '{}');
    const saveCache = () => localStorage.setItem('geoCache', JSON.stringify(geoCache));
    async function geocode(addr) {
        if (geoCache[addr]) return geoCache[addr];
        try {
            const r = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}`);
            const d = await r.json();
            if (d.length) {
                const c = [+d[0].lat, +d[0].lon];
                geoCache[addr] = c;
                saveCache();
                return c;
            }
        } catch (e) { console.error('Geocoding error:', e); }
        return null;
    }

    // ==================== Icons ====================
    function icon(cls) {
        return L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41],
            className: cls
        });
    }
    const redIcon = icon('marker-red');
    const yellowIcon = icon('marker-yellow');
    const blueIcon = icon('marker-blue');

    // ==================== Markers ====================
    function clearMarkers() {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        teacherMarker = null;
    }
    function addMarker(coord, label, icn) {
        const m = L.marker(coord, { icon: icn }).addTo(map).bindPopup(label);
        markers.push(m);
        return m;
    }

    // ==================== Teacher Position (if provided) ====================
    (async () => {
        const addr = window.TEACHER_ADDRESS;
        if (addr) {
            teacherCoord = await geocode(addr);
            if (teacherCoord) {
                teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
                map.setView(teacherCoord, 13);
            }
        }
    })();

    // ==================== Stage View Teacher Markers ====================
    async function displayStageTeachers(internshipId) {
        try {
            // Récupérer les données des professeurs pour ce stage via la DataTable
            const stageTable = $('#viewStage').DataTable();

            // Vérifier si la DataTable existe et a des données
            if (!stageTable || !stageTable.data()) {
                console.log('DataTable non initialisée, tentative de récupération directe des données');
                // Fallback : récupérer directement via l'API
                const response = await fetch(`/api/datatable/stage/${internshipId}`);
                const data = await response.json();
                await processTeachersData(data.data || []);
                return;
            }

            // Récupérer les données de la DataTable
            const teachers = stageTable.data().toArray().slice(0, 10);
            await processTeachersData(teachers);

        } catch (error) {
            console.error('Erreur lors du chargement des données des professeurs:', error);
        }
    }

    async function processTeachersData(teachers) {
        if (!teachers || teachers.length === 0) {
            console.log('Aucun enseignant trouvé.');
            return;
        }

        const bounds = [];

        // Calculer le score maximum
        const scores = teachers.map(t => parseFloat(t.score) || 0);
        const maxScore = Math.max(...scores);
        const hasUniqueMaxScore = scores.filter(s => s === maxScore).length === 1;

        // Ajouter les markers des professeurs
        for (const teacher of teachers) {
            if (!teacher.prof) continue;

            // Extraire les informations du professeur
            let teacherName = teacher.prof;
            let teacherAddress = teacher.teacher_address || ''; // Utiliser teacher_address

            if (!teacherAddress) {
                console.log(`Aucune adresse trouvée pour l'enseignant : ${teacherName}`);
                continue;
            }

            console.log(`Géocodage de l'adresse : ${teacherAddress}`);
            const coord = await geocode(teacherAddress);
            if (coord) {
                console.log(`Coordonnées trouvées pour ${teacherName}:`, coord);
                bounds.push(coord);

                // Déterminer la couleur du marker
                let markerIcon = blueIcon; // Couleur par défaut
                let label = `${teacherName}<br>Score: ${teacher.score || 'N/A'}`;

                // Vérifier si c'est le professeur avec le meilleur score
                const teacherScore = parseFloat(teacher.score) || 0;
                if (hasUniqueMaxScore && teacherScore === maxScore) {
                    markerIcon = yellowIcon;
                    label += '<br><strong>Meilleur score</strong>';
                }

                addMarker(coord, label, markerIcon);
            } else {
                console.log(`Aucune coordonnée trouvée pour l'adresse : ${teacherAddress}`);
            }
        }

        // Inclure la position du professeur connecté dans les bounds
        if (teacherCoord) bounds.push(teacherCoord);

        // Ajuster la vue pour inclure tous les markers
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // ==================== DataTable Interaction ====================
    const table = $('#dispatch-table').DataTable();
    let selectedId = null;

    table.on('select deselect', async () => {
        clearMarkers();

        // Remettre le marker du professeur connecté
        if (teacherCoord) {
            teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
        }

        const sel = table.rows({ selected: true }).data().toArray();
        const bounds = [];
        for (const row of sel) {
            if (!row.address) continue;
            const coord = await geocode(row.address);
            if (coord) {
                addMarker(coord, `${row.student} - ${row.subject}`, blueIcon);
                bounds.push(coord);
            }
            const coordt = await geocode(row.teacher_address);
            if (coordt) {
                addMarker(coordt, `${row.teacher}`, yellowIcon);
                bounds.push(coordt);
            }
        }
        if (teacherCoord) bounds.push(teacherCoord);
        if (bounds.length) map.fitBounds(bounds, { padding: [50, 50] });

        // Toggle button state
        if (sel.length === 1) {
            selectedId = sel[0].internship_identifier;
            toggleBtn.disabled = false;
        } else {
            selectedId = null;
            toggleBtn.disabled = true;
        }
    });

    // ==================== Toggle Stage/Table View ====================
    const urlParams = new URLSearchParams(window.location.search);
    const internshipParam = urlParams.get('internship');

    if (internshipParam) {
        toggleBtn.disabled = false;
        toggleIcon.textContent = 'apps';
        // Afficher les markers des professeurs si on est déjà en vue stage
        setTimeout(() => displayStageTeachers(internshipParam), 1000);
    } else {
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'assignment_ind';
    }

    // Précharger les données pour la vue stage
    let stageDataCache = {};
    async function preloadStageData(internshipId) {
        if (!stageDataCache[internshipId]) {
            const response = await fetch(`/api/viewStage/${internshipId}`);
            const html = await response.text();
            stageDataCache[internshipId] = html;
        }
    }

    // Précharger les données pour les stages sélectionnés
    table.on('select', async () => {
        const sel = table.rows({ selected: true }).data().toArray();
        if (sel.length === 1) {
            await preloadStageData(sel[0].internship_identifier);
        }
    });

    toggleBtn.addEventListener('click', async () => {
        const url = new URL(window.location.href);

        if (tableCont.style.display !== 'none') {
            if (!selectedId) return;

            url.searchParams.set('internship', selectedId);
            history.replaceState(null, '', url.toString());

            // Utiliser les données préchargées
            if (stageDataCache[selectedId]) {
                stageCont.innerHTML = stageDataCache[selectedId];
            } else {
                const html = await (await fetch(`/api/viewStage/${selectedId}`)).text();
                stageCont.innerHTML = html;
            }

            // Re-init stage DataTable
            const stageCols = [
                { data: 'prof' },
                { data: 'distance' },
                { data: 'discipline' },
                { data: 'score' },
                { data: 'entreprise' },
                { data: 'history' },
            ];

            // Effacer les markers existants
            clearMarkers();
            if (teacherCoord) {
                teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
            }

            tableCont.style.display = 'none';
            stageCont.style.display = '';
            toggleIcon.textContent = 'apps';

            // Initialiser la DataTable et attendre qu'elle soit prête
            const stageTable = initDataTable('viewStage', `/api/datatable/stage/${selectedId}`, stageCols);

            // Attendre que la DataTable soit complètement chargée
            if (stageTable && stageTable.ajax) {
                stageTable.ajax.reload(async () => {
                    // Une fois les données chargées, afficher les markers
                    await displayStageTeachers(selectedId);
                });
            } else {
                // Fallback si initDataTable ne retourne pas l'objet table
                setTimeout(async () => {
                    await displayStageTeachers(selectedId);
                }, 1000);
            }

        } else {
            url.searchParams.delete('internship');
            history.replaceState(null, '', url.toString());

            stageCont.style.display = 'none';
            tableCont.style.display = '';
            toggleIcon.textContent = 'assignment_ind';

            // Revenir à l'affichage normal
            clearMarkers();
            if (teacherCoord) {
                teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
            }
        }
    });
});
