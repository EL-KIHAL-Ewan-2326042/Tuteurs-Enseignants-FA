document.addEventListener('DOMContentLoaded', () => {
    const viewStageContainer = document.getElementById('viewStageContainer');
    viewStageContainer?.addEventListener('change', function(event) {
        if (event.target.classList.contains('dispatch-checkbox')) {
            [...this.querySelectorAll('.dispatch-checkbox')].forEach(cb => {
                if (cb !== event.target) cb.checked = false;
            });
        }
    });

    document.getElementById('checkAll')?.addEventListener('click', () => {
        document.querySelectorAll('.dispatch-checkbox').forEach(cb => cb.checked = true);
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

    const geoCache = JSON.parse(localStorage.getItem('geoCache') || '{}');
    const saveCache = () => localStorage.setItem('geoCache', JSON.stringify(geoCache));

    const geocode = async (addr) => {
        if (geoCache[addr]) return geoCache[addr];
        try {
            const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(addr)}`);
            const data = await res.json();
            if (data[0]) {
                const coords = [+data[0].lat, +data[0].lon];
                geoCache[addr] = coords;
                saveCache();
                return coords;
            }
        } catch (e) {
            console.error('Geocoding error:', e);
        }
        return null;
    };

    const icon = (cls, size = [25, 41]) => L.icon({
        iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
        shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
        iconSize: size,
        iconAnchor: [size[0] / 2, size[1]],
        popupAnchor: [0, -size[1] / 2],
        shadowSize: [41, 41],
        className: cls
    });

    const icons = {
        red: icon('marker-red'),
        yellow: icon('marker-yellow'),
        blue: icon('marker-blue'),
        purple: icon('marker-p', [35, 55]),
        largeRed: icon('marker-red', [35, 55]),
        largeYellow: icon('marker-yellow', [35, 55])
    };

    const clearMarkers = () => {
        markers.forEach(m => map.removeLayer(m));
        markers = [];
        teacherMarker = null;
    };

    const addMarker = (coord, label, icn) => {
        const m = L.marker(coord, { icon: icn }).addTo(map).bindPopup(label);
        markers.push(m);
        return m;
    };

    async function displayStageTeachers(internshipId) {
        if (!internshipId) return;

        try {
            const res = await fetch(`/api/datatable/stage/${internshipId}`);
            const { data = [] } = await res.json();

            if (data.length === 0) return;

            await processTeachersData(data);

            const studentAddress = data[0].internship_address;
            const studentCoord = await geocode(studentAddress);
            if (studentCoord) addMarker(studentCoord, "Étudiant", icons.purple);
        } catch (e) {
            console.error("Erreur lors de l'affichage des enseignants :", e);
        }
    }

    async function processTeachersData(teachers) {
        if (!teachers.length) return;
        const bounds = [];
        const scores = teachers.map(t => parseFloat(t.score?.match(/Score : (\d+\.\d+)/)?.[1]) || 0);
        const maxScore = Math.max(...scores);

        for (const t of teachers) {
            const { prof, teacher_address = "", associate = "", score = "Score : 0 / 5" } = t;
            if (!teacher_address) continue;

            const coord = await geocode(teacher_address);
            if (!coord) continue;

            bounds.push(coord);

            const parsedScore = parseFloat(score.match(/Score : (\d+\.\d+)/)?.[1]) || 0;
            const isAssociated = associate.includes("checked");

            let icn = icons.blue;
            let label = `${prof}<br>Score: ${parsedScore}`;

            if (isAssociated) {
                icn = icons.largeRed;
                label += "<br><strong>Déjà associé</strong>";
            } else if (parsedScore === maxScore) {
                icn = icons.largeYellow;
                label += "<br><strong>Meilleur score</strong>";
            }

            addMarker(coord, label, icn);
        }

        if (teacherCoord) bounds.push(teacherCoord);
        if (bounds.length) map.fitBounds(bounds, { padding: [50, 50] });
    }

    // DataTable selection
    const table = $('#dispatch-table').DataTable();
    let selectedId = null;

    let debounceSelection;
    table.on('select deselect', () => {
        clearTimeout(debounceSelection);
        debounceSelection = setTimeout(async () => {
            clearMarkers();
            if (teacherCoord) teacherMarker = addMarker(teacherCoord, 'Votre position', icons.red);

            const sel = table.rows({ selected: true }).data().toArray();
            const bounds = [];

            for (const row of sel) {
                if (row.address) {
                    const coord = await geocode(row.address);
                    if (coord) {
                        addMarker(coord, `${row.student} - ${row.subject}`, icons.purple);
                        bounds.push(coord);
                    }
                }

                if (row.teacher_address) {
                    const coordt = await geocode(row.teacher_address);
                    if (coordt) {
                        addMarker(coordt, `${row.teacher}`, icons.yellow);
                        bounds.push(coordt);
                    }
                }
            }

            if (teacherCoord) bounds.push(teacherCoord);
            if (bounds.length) map.fitBounds(bounds, { padding: [50, 50] });

            selectedId = sel.length === 1 ? sel[0].internship_identifier : null;
            toggleBtn.disabled = !selectedId;
        }, 300);
    });

    const urlParams = new URLSearchParams(window.location.search);
    const internshipParam = urlParams.get('internship');

    if (internshipParam) {
        toggleBtn.disabled = false;
        toggleIcon.textContent = 'apps';
        displayStageTeachers(internshipParam); // Appel unique
    } else {
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'assignment_ind';
    }

    const stageDataCache = {};
    async function preloadStageData(internshipId) {
        if (!stageDataCache[internshipId]) {
            const res = await fetch(`/api/dispatcherViewStage/${internshipId}`);
            stageDataCache[internshipId] = await res.text();
        }
    }

    table.on('select', async () => {
        const sel = table.rows({ selected: true }).data().toArray();
        if (sel.length === 1) await preloadStageData(sel[0].internship_identifier);
    });

    // Toggle vue stage/table
    toggleBtn.addEventListener('click', async () => {
        const url = new URL(window.location.href);

        if (tableCont.style.display !== 'none') {
            if (!selectedId) return;

            url.searchParams.set('internship', selectedId);
            history.replaceState(null, '', url.toString());

            stageCont.innerHTML = stageDataCache[selectedId] || await (await fetch(`/api/dispatcherViewStage/${selectedId}`)).text();

            tableCont.style.display = 'none';
            stageCont.style.display = '';
            toggleIcon.textContent = 'apps';

            const stageCols = [
                { data: 'associate', orderable: false, searchable: false },
                { data: 'prof' }, { data: 'distance' }, { data: 'score' },
                { data: 'discipline' }, { data: 'entreprise' },
                { data: 'history' }, { data: 'teacher_address' },
                { data: 'internship_address' }
            ];

            clearMarkers();
            if (teacherCoord) teacherMarker = addMarker(teacherCoord, 'Votre position', icons.red);

            const stageTable = initDataTable('viewStage', `/api/datatable/stage/${selectedId}`, stageCols, false);

            if (stageTable?.ajax) {
                stageTable.ajax.reload(() => displayStageTeachers(selectedId));
            } else {
                displayStageTeachers(selectedId);
            }
        } else {
            url.searchParams.delete('internship');
            history.replaceState(null, '', url.toString());

            stageCont.style.display = 'none';
            tableCont.style.display = '';
            toggleIcon.textContent = 'assignment_ind';

            clearMarkers();
            if (teacherCoord) teacherMarker = addMarker(teacherCoord, 'Votre position', icons.red);
        }
    });
});
