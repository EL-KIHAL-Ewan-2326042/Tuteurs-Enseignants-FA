<?php
/**
 * Fichier contenant la vue de la page 'Accueil'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/homepage
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\ask;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Student;
use Blog\Models\Teacher;
use Blog\Views\components\Table;

/**
 * Classe gérant l'affichage de la page 'Accueil'
 *
 * @category View
 * @package  TutorMap/modules/Views/homepage
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
readonly class Ask
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     * @param Student    $studentModel    Instance de la classe Student
     *                                    servant de modèle
     * @param Teacher    $teacherModel    Instance de la classe Teacher
     *                                    servant de modèle
     * @param Department $departmentModel Instance de la classe Department
     *                                    servant de modèle
     */
    public function __construct(private Internship $internshipModel,
                                private Student $studentModel,
                                private Teacher $teacherModel,
                                private Department $departmentModel
    ) {
    }

    /**
     * Affiche la page 'Accueil'
     *
     * @return void
     */
    public function showView(): void
    {
        $headers = ['Élève', 'Formation', 'Groupe', 'Historique', 'Entreprise', 'Sujet', 'Adresse', 'Distance'];

        $jsColumns = [
            ['data' => 'student'],
            ['data' => 'formation'],
            ['data' => 'group'],
            ['data' => 'history'],
            ['data' => 'company'],
            ['data' => 'subject'],
            ['data' => 'address'],
            ['data' => 'distance'],
        ];
        ?>
        <main>
            <div>


            <section>
                <?php Table::render('homepage-table', $headers, $jsColumns, '/api/datatable/ask'); ?>

            </section>
            <section id="map" >

            </section>
            </div>
        </main>

        <script>
            const map = L.map('map').setView([43.2965, 5.3698], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            let markers = [];
            let teacherMarker = null;

            const cacheGeocoding = JSON.parse(localStorage.getItem('geoCache') || '{}');

            function saveCache() {
                localStorage.setItem('geoCache', JSON.stringify(cacheGeocoding));
            }

            function clearMarkers() {
                markers.forEach(marker => {
                    if (marker !== teacherMarker) {
                        map.removeLayer(marker);
                    }
                });
                markers = teacherMarker ? [teacherMarker] : [];
            }

            async function geocodeAddress(address) {
                if (cacheGeocoding[address]) {
                    return cacheGeocoding[address];
                }

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
                    const data = await response.json();

                    if (data.length > 0) {
                        const coords = [parseFloat(data[0].lat), parseFloat(data[0].lon)];
                        cacheGeocoding[address] = coords;
                        saveCache();
                        return coords;
                    }
                } catch (error) {
                    console.error('Erreur géocodage:', error);
                }

                return null;
            }

            function createColoredIcon(cssClass) {
                const icon = L.icon({
                    iconUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
                    shadowUrl: 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
                    iconSize: [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor: [1, -34],
                    shadowSize: [41, 41],
                    className: cssClass // for coloring
                });
                return icon;
            }

            const yellowIcon = createColoredIcon('marker-yellow');
            const blueIcon = createColoredIcon('marker-blue');

            <?php if (!empty($_SESSION['address'])) : ?>
            (async () => {
                const teacherAddress = "<?php echo $_SESSION['address'][0]['address']; ?>";
                const coord = await geocodeAddress(teacherAddress);
                if (coord) {
                    teacherMarker = L.marker(coord, { icon: yellowIcon }).addTo(map).bindPopup("Votre position");
                    markers.push(teacherMarker);
                    map.setView(coord, 13);
                }
            })();
            <?php endif; ?>

            $(document).ready(function () {
                const table = $('#homepage-table').DataTable();

                table.on('select deselect', async function () {
                    clearMarkers();
                    const selectedData = table.rows({ selected: true }).data().toArray();
                    const bounds = [];

                    for (const row of selectedData) {
                        const address = row.address;
                        const label = row.student + ' - ' + row.company;

                        if (address) {
                            const coord = await geocodeAddress(address);
                            if (coord) {
                                const marker = L.marker(coord, { icon: blueIcon }).addTo(map).bindPopup(label);
                                markers.push(marker);
                                bounds.push(coord);
                            }
                        }
                    }

                    if (bounds.length > 0) {
                        map.fitBounds(bounds, { padding: [50, 50] });
                    }
                });
            });
        </script>



        <?php

        unset($_SESSION['selected_student']);
    }
}