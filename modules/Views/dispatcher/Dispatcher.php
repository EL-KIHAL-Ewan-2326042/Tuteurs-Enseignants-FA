<?php

namespace Blog\Views\dispatcher;

use Blog\Views\components\CoefBackup;
use Blog\Views\components\Table;
use Blog\Views\components\ViewStage;
use Blog\Models\Internship;

class Dispatcher
{
    public function __construct(
        private Internship $internshipModel,
        private $userModel,
        private $teacherModel,
        private $departmentModel,
        private string $errorMessageAfterSort,
        private string $errorMessageDirectAssoc,
        private string $checkMessageDirectAssoc,
        private string $checkMessageAfterSort
    ) {}

    public function showView(): void
    {
        ?>
        <main>
            <?php
            $internshipId = $_GET['internship'] ?? null;
            $btnDisabled = $internshipId ? '' : 'disabled';
            $btnIcon = $internshipId ? 'apps' : 'assignment_ind';

            if (($_POST['action'] ?? '') !== 'generate') {
                CoefBackup::render(
                    $this->userModel,
                    $this->errorMessageAfterSort,
                    $this->checkMessageAfterSort
                );
            }

            if (isset($_POST['coef'], $_POST['action']) && $_POST['action'] === 'generate'):
                $_SESSION['last_dict_coef'] = array_filter($_POST['coef'], fn($coef, $key) =>
                isset($_POST['criteria_on'][$key]), ARRAY_FILTER_USE_BOTH);
                ?>
                <div class="partie2">
                    <div id="tableContainer" class="dataTable">
                        <form action="./dispatcher" method="post">
                            <?php
                            Table::render(
                                'dispatch-table',
                                ['Etudiant','Enseignant', 'Stage', 'Formation', 'Groupe', 'Sujet', 'Adresse', 'Score', 'internship_identifier', 'teacher_address', 'Associer'],
                                [
                                    ['data' => 'student'],
                                    ['data' => 'teacher'],
                                    ['data' => 'internship'],
                                    ['data' => 'formation'],
                                    ['data' => 'group'],
                                    ['data' => 'subject'],
                                    ['data' => 'address'],
                                    ['data' => 'score'],
                                    ['data' => 'internship_identifier'],
                                    ['data' => 'teacher_address'],
                                    ['data' => 'associate']
                                ],
                                '/api/dispatch-list'
                            );
                            ?>
                            <button type="submit" name="selecInternshipSubmitted" value="1" class="btn">Valider</button>
                        </form>
                    </div>

                    <div id="viewStageContainer" class="dataTable" <?= $internshipId ? '' : 'style="display:none;"' ?>>
                        <?php if ($internshipId) ViewStage::render($internshipId); ?>
                    </div>

                    <div class="cont-map">
                        <button id="toggleViewBtn" title="Basculer la vue" <?= $btnDisabled ?>>
                            <i class="material-icons" id="toggleIcon"><?= $btnIcon ?></i>
                        </button>
                        <section id="map"></section>
                    </div>
                </div>

                <style>
                    main { max-width: 100%; min-height: 80vh; }
                    .partie2 { display: flex; gap: 2rem; margin: 1rem; justify-content: space-around; }
                    .partie2 > .dataTable { max-width: 65%; overflow: auto; max-height: 80vh }
                    .partie2 > .cont-map { width: 35%; position: relative; }
                    #map {
                        width: 100%; height: 100%;
                        background-color: var(--couleur-bleu-roi);
                        border-radius: .4rem;
                        box-shadow: 0 2px 16px rgba(14, 30, 37, 0.32);
                    }
                    #toggleViewBtn {
                        z-index: 1000; position: absolute; bottom: 1rem; left: 1rem;
                    }
                </style>

                <script>
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
                        const yellowIcon = icon('marker-yellow'), blueIcon = icon('marker-blue');

                        // ==================== Markers ====================
                        function clearMarkers() {
                            markers.forEach(m => { if (m !== teacherMarker) map.removeLayer(m); });
                            markers = teacherMarker ? [teacherMarker] : [];
                        }
                        function addMarker(coord, label, icn) {
                            const m = L.marker(coord, { icon: icn }).addTo(map).bindPopup(label);
                            markers.push(m);
                        }

                        // ==================== Teacher Position (if provided) ====================
                        (async () => {
                            const addr = window.TEACHER_ADDRESS;
                            if (addr) {
                                teacherCoord = await geocode(addr);
                                if (teacherCoord) {
                                    teacherMarker = L.marker(teacherCoord, { icon: yellowIcon })
                                        .addTo(map).bindPopup('Votre position');
                                    markers.push(teacherMarker);
                                    map.setView(teacherCoord, 13);
                                }
                            }
                        })();

                        // ==================== DataTable Interaction ====================
                        const table = $('#dispatch-table').DataTable();
                        let selectedId = null;

                        table.on('select deselect', async () => {
                            clearMarkers();
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
                                    { data: 'history' },
                                    { data: 'distance' },
                                    { data: 'discipline' },
                                    { data: 'score' },
                                    { data: 'entreprise' }
                                ];
                                initDataTable('viewStage', `/api/datatable/stage/${selectedId}`, stageCols);

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
                    });
                </script>
            <?php endif; ?>
        </main>

        <?php
    }
}
