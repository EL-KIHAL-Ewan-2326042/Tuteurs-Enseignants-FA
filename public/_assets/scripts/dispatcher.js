document.addEventListener('DOMContentLoaded', () => {
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
        var checkboxes = document.querySelectorAll('.dispatch-checkbox');
        checkboxes.forEach(function(checkbox) {
            checkbox.checked = true;
        });
    });


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
    function icon(color) {
        const colorUrl = {
            red: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-red.png',
            yellow: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-yellow.png',
            blue: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-blue.png'
        };
        return L.icon({
            iconUrl: colorUrl[color],
            shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
    }

    const redIcon = icon('red');
    const yellowIcon = icon('yellow');
    const blueIcon = icon('blue');

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

    // ==================== Teacher Position ====================
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
        console.log('Affichage des marqueurs pour stage', internshipId);
        try {
            const response = await fetch(`/api/datatable/stage/${internshipId}`);
            const data = await response.json();
            await processTeachersData(data.data || []);
        } catch (error) {
            console.error('Erreur lors du chargement des données des enseignants:', error);
        }
    }

    async function processTeachersData(teachers) {
        if (!teachers || teachers.length === 0) {
            console.log('Aucun enseignant trouvé.');
            return;
        }

        console.log('Données enseignants reçues:', teachers);
        const bounds = [];

        // Trouver le score maximum
        const scores = teachers.map(t => parseFloat(t.score) || 0);
        const maxScore = Math.max(...scores);
        const hasUniqueMaxScore = scores.filter(s => s === maxScore).length === 1;

        // Limiter à 10 enseignants
        const limitedTeachers = teachers.slice(0, 10);

        for (const teacher of limitedTeachers) {
            if (!teacher.prof) continue;

            const teacherName = teacher.prof;
            const teacherAddress = teacher.teacher_address || '';

            if (!teacherAddress) {
                console.log(`Aucune adresse trouvée pour l'enseignant : ${teacherName}`);
                continue;
            }

            const coord = await geocode(teacherAddress);
            if (!coord) {
                console.log(`Coordonnées introuvables pour : ${teacherAddress}`);
                continue;
            }

            bounds.push(coord);

            const teacherScore = parseFloat(teacher.score) || 0;
            const isAssociated = teacher.associate === true || teacher.associate === "true";

            let markerIcon = blueIcon;
            let label = `${teacherName}<br>Score: ${teacher.score || 'N/A'}`;

            if (isAssociated) {
                markerIcon = redIcon;
                label += '<br><strong>Déjà associé</strong>';
            } else if (hasUniqueMaxScore && teacherScore === maxScore) {
                markerIcon = yellowIcon;
                label += '<br><strong>Meilleur score</strong>';
            }

            addMarker(coord, label, markerIcon);
        }

        if (teacherCoord) bounds.push(teacherCoord);

        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [50, 50] });
        }
    }


    // ==================== DataTable Interaction ====================
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

    // ==================== Toggle Stage/Table View ====================
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
