document.addEventListener('DOMContentLoaded', () => {
    const map = L.map('map').setView([43.2965, 5.3698], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    let markers = [], teacherMarker = null, teacherCoord = null;
    const toggleIcon = document.getElementById('toggleIcon');

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
            console.error('Géocodage :', e);
        }
        return null;
    }

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

    const yellowIcon = icon('marker-yellow'), blueIcon = icon('marker-blue');
    const purpleIcon = icon('marker-p', [35, 55])

    function clearMarkers() {
        markers.forEach(m => {
            if (m !== teacherMarker) map.removeLayer(m);
        });
        markers = teacherMarker ? [teacherMarker] : [];
    }

    function addMarker(coord, label, icn) {
        const m = L.marker(coord, {
            icon: icn
        }).addTo(map).bindPopup(label);
        markers.push(m);
    }

    (async () => {
        const addr = window.TEACHER_ADDRESS;
        if (addr) {
            teacherCoord = await geocode(addr);
            if (teacherCoord) {
                teacherMarker = L.marker(teacherCoord, {
                    icon: yellowIcon
                }).addTo(map).bindPopup('Votre position');
                markers.push(teacherMarker);
                map.setView(teacherCoord, 13);
            }
        }
    })();

    initDataTable('homepage-table', '/api/datatable/ask', window.JS_COLUMNS);

    const table = $('#homepage-table').DataTable();
    table.on('select deselect', async () => {
        clearMarkers();
        const sel = table.rows({
            selected: true
        }).data().toArray();
        const b = [];
        for (const row of sel) {
            if (!row.address) continue;
            const c = await geocode(row.address);
            if (c) {
                addMarker(c, `${row.student} - ${row.company}`, purpleIcon);
                b.push(c);
            }
        }
        if (teacherCoord) b.push(teacherCoord);
        if (b.length) map.fitBounds(b, {
            padding: [50, 50]
        });
    });

    function getCheckedRowsData() {
        const checkedRows = [];
        const checkboxes = document.querySelectorAll('.dispatch-checkbox:checked');

        checkboxes.forEach(checkbox => {
            const row = checkbox.closest('tr');
            if (row) {
                const rowData = table.row(row).data();
                if (rowData) {
                    const checkboxValue = checkbox.value;
                    const [teacherId, internshipId] = checkboxValue.split('$');

                    checkedRows.push({
                        ...rowData,
                        teacher_id: teacherId,
                        internship_identifier: internshipId
                    });
                }
            }
        });

        return checkedRows;
    }

    const validateButton = document.querySelector('button[name="selecInternshipSubmitted"]');
    validateButton.addEventListener('click', async function(event) {
        event.preventDefault();

        const checkedRows = getCheckedRowsData();

        if (checkedRows.length === 0) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Veuillez cocher au moins une case "Demander".',
            });
            return;
        }

        if (!window.TEACHER_ID) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'L\'identifiant de l\'enseignant est manquant.',
            });
            return;
        }

        validateButton.disabled = true;
        validateButton.innerHTML = '<i class="material-icons">hourglass_empty</i> Traitement...';

        let hasError = false;

        try {
            for (const row of checkedRows) {
                const response = await fetch('/api/update-internship-request', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        add: true,
                        teacher: window.TEACHER_ID,
                        internship: row.internship_identifier
                    })
                });

                const data = await response.json();
                if (!data.success) {
                    console.error('Error:', data.message);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: `Une erreur est survenue lors de l'enregistrement de la demande pour ${row.student}.`,
                    });
                    hasError = true;
                    break;
                }
            }

            if (!hasError) {
                document.querySelectorAll('.dispatch-checkbox:checked').forEach(checkbox => {
                    checkbox.checked = false;
                });

                table.ajax.reload();

                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: `${checkedRows.length} demande(s) ont été enregistrée(s) avec succès.`,
                });
            }
        } catch (error) {
            console.error('Erreur lors de l\'envoi des demandes:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erreur',
                text: 'Une erreur inattendue s\'est produite.',
            });
        } finally {
            // Réactiver le bouton
            validateButton.disabled = false;
            validateButton.innerHTML = 'Valider';
        }
    });

    const toggleBtn = document.getElementById('toggleViewBtn'),
        tableCont = document.getElementById('tableContainer'),
        stageCont = document.getElementById('viewStageContainer');

    let selectedId = null;
    table.on('select deselect', () => {
        const rows = table.rows({ selected: true }).data().toArray();
        if (rows.length === 1) {
            selectedId = rows[0].internship_identifier;
            toggleBtn.disabled = false;
        } else {
            selectedId = null;
            toggleBtn.disabled = true;
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const internshipParam = urlParams.get('internship');

    if (internshipParam) {
        toggleBtn.disabled = false;
        toggleIcon.textContent = 'apps';
    } else {
        toggleBtn.disabled = true;
        toggleIcon.textContent = 'assignment_ind';
    }

    toggleBtn.addEventListener('click', async () => {
        const url = new URL(window.location.href);

        if (tableCont.style.display !== 'none') {
            if (!selectedId) return;
            url.searchParams.set('internship', selectedId);
            history.replaceState(null, '', url.toString());

            const html = await (await fetch(`/api/viewStage/${selectedId}`)).text();
            stageCont.innerHTML = '';
            const frag = document.createRange().createContextualFragment(html);
            stageCont.appendChild(frag);
            frag.querySelectorAll('script').forEach(s => {
                const ns = document.createElement('script');
                if (s.src) ns.src = s.src;
                else ns.textContent = s.textContent;
                document.head.appendChild(ns);
            });

            const stageCols = [{
                data: 'prof'
            }, {
                data: 'distance'
            }, {
                data: 'score'
            }, {
                data: 'discipline'
            }, {
                data: 'entreprise'
            }, {
                data: 'history'
            }];
            initDataTable('viewStage', `/api/datatable/stage/${selectedId}`, stageCols, false);

            tableCont.style.display = 'none';
            stageCont.style.display = '';
            toggleIcon.textContent = 'apps';
        } else {
            url.searchParams.delete('internship');
            history.replaceState(null, '', url.toString());

            stageCont.style.display = 'none';
            tableCont.style.display = '';
            toggleIcon.textContent = 'assignment_ind';
        }
    });

    document.addEventListener('change', async function(event) {
        if (event.target.classList.contains('dispatch-checkbox')) {
            const checkedRows = getCheckedRowsData();

            clearMarkers();
            const bounds = [];

            for (const row of checkedRows) {
                if (!row.address) continue;
                const coord = await geocode(row.address);
                if (coord) {
                    addMarker(coord, `${row.student} - ${row.company}`, purpleIcon);
                    bounds.push(coord);
                }
            }

            if (teacherCoord) bounds.push(teacherCoord);
            if (bounds.length) {
                map.fitBounds(bounds, { padding: [50, 50] });
            }
        }
    });
});