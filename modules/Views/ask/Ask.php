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
        $headers = ['Élève', 'Distance','Formation', 'Entreprise', 'Groupe', 'Historique', 'Sujet', 'Adresse'];

        $jsColumns = [
            ['data' => 'student'],
            ['data' => 'distance'],
            ['data' => 'formation'],
            ['data' => 'company'],
            ['data' => 'group'],
            ['data' => 'history'],
            ['data' => 'subject'],
            ['data' => 'address'],
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
            /* ------- carte -------- */
            const map = L.map('map').setView([43.2965, 5.3698], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            /* ------- variables globales -------- */
            let markers       = [];
            let teacherMarker = null;
            let teacherCoord  = null;

            /* ------- cache géocodage -------- */
            const cacheGeocoding = JSON.parse(localStorage.getItem('geoCache') || '{}');
            const saveCache      = () => localStorage.setItem('geoCache', JSON.stringify(cacheGeocoding));

            async function geocodeAddress(address) {
                if (cacheGeocoding[address]) return cacheGeocoding[address];

                try {
                    const r = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(address)}`);
                    const d = await r.json();
                    if (d.length) {
                        const c = [parseFloat(d[0].lat), parseFloat(d[0].lon)];
                        cacheGeocoding[address] = c; saveCache();
                        return c;
                    }
                } catch (e) { console.error('Erreur géocodage :', e); }
                return null;
            }

            /* ------- fabrique d’icônes colorées -------- */
            function iconWithClass(cls) {
                return L.icon({
                    iconUrl   : 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-icon.png',
                    shadowUrl : 'https://unpkg.com/leaflet@1.7.1/dist/images/marker-shadow.png',
                    iconSize  : [25, 41],
                    iconAnchor: [12, 41],
                    popupAnchor:[1, -34],
                    shadowSize:[41, 41],
                    className : cls
                });
            }
            const yellowIcon = iconWithClass('marker-yellow'); // prof
            const blueIcon   = iconWithClass('marker-blue');   // élèves sélectionnés

            /* ------- gestion des marqueurs -------- */
            function clearMarkers() {
                markers.forEach(m => { if (m !== teacherMarker) map.removeLayer(m); });
                markers = teacherMarker ? [teacherMarker] : [];
            }

            function addMarker(coord, label, icon) {
                const m = L.marker(coord, { icon }).addTo(map).bindPopup(label);
                markers.push(m);
            }

            /* ------- marqueur du prof (permanent) -------- */
            <?php if (!empty($_SESSION['address'])) : ?>
            (async () => {
                const teacherAddress = "<?= addslashes($_SESSION['address'][0]['address']); ?>";
                teacherCoord = await geocodeAddress(teacherAddress);
                if (teacherCoord) {
                    teacherMarker = L.marker(teacherCoord, { icon: yellowIcon })
                        .addTo(map)
                        .bindPopup("Votre position");
                    markers.push(teacherMarker);
                    map.setView(teacherCoord, 13);
                }
            })();
            <?php endif; ?>

            /* ------- interaction DataTable -------- */
            $(document).ready(function () {
                const table = $('#homepage-table').DataTable();

                table.on('select deselect', async function () {
                    clearMarkers();

                    const selected = table.rows({ selected: true }).data().toArray();
                    const bounds   = [];

                    for (const row of selected) {
                        const { address, student, company } = row;
                        if (!address) continue;

                        const coord = await geocodeAddress(address);
                        if (coord) {
                            addMarker(coord, `${student} - ${company}`, blueIcon);
                            bounds.push(coord);
                        }
                    }

                    if (teacherCoord) bounds.push(teacherCoord);

                    if (bounds.length) map.fitBounds(bounds, { padding: [50, 50] });
                });
            });
        </script>



        <?php

        unset($_SESSION['selected_student']);
    }
}