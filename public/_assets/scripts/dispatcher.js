document.addEventListener('DOMContentLoaded', () => {
    // Gestion des checkboxes pour la vue des étudiants
    document.getElementById('viewStageContainer').addEventListener('change', function(event) {
        if (event.target.classList.contains('dispatch-checkbox')) {
            const checkboxes = this.querySelectorAll('.dispatch-checkbox');
            checkboxes.forEach(otherCheckbox => {
                if (otherCheckbox !== event.target) {
                    otherCheckbox.checked = false;
                }
            });
        }
    });

    document.getElementById('checkAll').addEventListener('click', function() {
        const checkboxes = document.querySelectorAll('.dispatch-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = true;
        });
    });

    // Initialisation de la carte
    const map = L.map('map').setView([43.2965, 5.3698], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let markers = [], teacherMarker = null, teacherCoord = null;
    const toggleIcon = document.getElementById('toggleIcon');
    const toggleBtn = document.getElementById('toggleViewBtn');
    const tableCont = document.getElementById('tableContainer');
    const stageCont = document.getElementById('viewStageContainer');

    // Cache pour le géocodage
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
        } catch (e) {
            console.error('Geocoding error:', e);
        }
        return null;
    }

    function icon(cls, size = [25, 41]) {
        return L.icon({
            iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
            shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
            iconSize: size,
            iconAnchor: [size[0] / 2, size[1]],
            popupAnchor: [0, -size[1] / 2],
            shadowSize: [41, 41],
            className: cls
        });
    }

    const redIcon = icon('marker-red');
    const yellowIcon = icon('marker-yellow');
    const blueIcon = icon('marker-blue');

    const largeRedIcon = icon('marker-red', [35, 55]);
    const largeYellowIcon = icon('marker-yellow', [35, 55]);

    // Gestion des marqueurs
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


    // Affichage des enseignants pour la vue des stages
    async function displayStageTeachers(internshipId) {
        try {
            const response = await fetch(`/api/datatable/stage/${internshipId}`);
            const data = await response.json();
            await processTeachersData(data.data || []);
        } catch (error) {
            console.error('Error loading teacher data:', error);
        }
    }

    // Traitement des données des enseignants
    async function processTeachersData(teachers) {
        if (!teachers || teachers.length === 0) {
            console.log('No teachers found.');
            return;
        }

        const bounds = [];
        const scores = teachers.map(t => {
            const scoreText = t.score || "Score : 0 / 5";
            const match = scoreText.match(/Score : (\d+\.\d+) \/ 5/);
            return match ? parseFloat(match[1]) : 0;
        });

        const maxScore = Math.max(...scores);

        for (const teacher of teachers) {
            const teacherName = teacher.prof;
            const teacherAddress = teacher.teacher_address || '';

            if (!teacherAddress) {
                console.log(`No address found for teacher: ${teacherName}`);
                continue;
            }

            const coord = await geocode(teacherAddress);
            if (!coord) {
                console.log(`Coordinates not found for: ${teacherAddress}`);
                continue;
            }

            bounds.push(coord);

            const scoreText = teacher.score || "Score : 0 / 5";
            const match = scoreText.match(/Score : (\d+\.\d+) \/ 5/);
            const teacherScore = match ? parseFloat(match[1]) : 0;

            const isAssociated = teacher.associate === true || teacher.associate === "true";
            console.log(teacher.associate, isAssociated)
            let markerIcon = blueIcon;
            let label = `${teacherName}<br>Score: ${teacherScore}`;

            if (isAssociated) {
                markerIcon = largeRedIcon; // Utilisez une icône plus grande pour les enseignants associés
                label += '<br><strong>Déjà associé</strong>';
            } else if (teacherScore === maxScore) {
                markerIcon = largeYellowIcon; // Utilisez une icône plus grande pour les enseignants avec le score maximum
                label += '<br><strong>Meilleur score</strong>';
            }

            addMarker(coord, label, markerIcon);
        }

        if (teacherCoord) bounds.push(teacherCoord);

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }

    // Interaction avec la table des étudiants
    const table = $('#dispatch-table').DataTable();
    let selectedId = null;

    table.on('select deselect', async () => {
        clearMarkers();
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

        if (sel.length === 1) {
            selectedId = sel[0].internship_identifier;
            toggleBtn.disabled = false;
        } else {
            selectedId = null;
            toggleBtn.disabled = true;
        }
    });

    // Gestion du basculement entre les vues
    const urlParams = new URLSearchParams(window.location.search);
    const internshipParam = urlParams.get('internship');

    if (internshipParam) {
        toggleBtn.disabled = false;
        toggleIcon.textContent = 'apps';
        setTimeout(() => displayStageTeachers(internshipParam), 1000);
    } else {
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'assignment_ind';
    }

    // Préchargement des données
    let stageDataCache = {};
    async function preloadStageData(internshipId) {
        if (!stageDataCache[internshipId]) {
            const response = await fetch(`/api/dispatcherViewStage/${internshipId}`);
            const html = await response.text();
            stageDataCache[internshipId] = html;
        }
    }

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

            if (stageDataCache[selectedId]) {
                stageCont.innerHTML = stageDataCache[selectedId];
            } else {
                const html = await (await fetch(`/api/dispatcherViewStage/${selectedId}`)).text();
                stageCont.innerHTML = html;
            }

            const stageCols = [
                { data: 'associate', orderable: false, searchable: false },
                { data: 'prof' },
                { data: 'distance' },
                { data: 'score' },
                { data: 'discipline' },
                { data: 'entreprise' },
                { data: 'history' },
                { data: 'teacher_address' }
            ];

            clearMarkers();
            if (teacherCoord) {
                teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
            }

            tableCont.style.display = 'none';
            stageCont.style.display = '';
            toggleIcon.textContent = 'apps';

            const stageTable = initDataTable('viewStage', `/api/datatable/stage/${selectedId}`, stageCols, false);

            if (stageTable && stageTable.ajax) {
                stageTable.ajax.reload(async () => {
                    await displayStageTeachers(selectedId);
                });
            } else {
                setTimeout(async () => {
                    await displayStageTeachers(selectedId);
                }, 10000);
            }

        } else {
            url.searchParams.delete('internship');
            history.replaceState(null, '', url.toString());

            stageCont.style.display = 'none';
            tableCont.style.display = '';
            toggleIcon.textContent = 'assignment_ind';

            clearMarkers();
            if (teacherCoord) {
                teacherMarker = addMarker(teacherCoord, 'Votre position', redIcon);
            }
        }
    });
});
