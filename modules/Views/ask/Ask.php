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
        ?>
        <main>
            <section>
                TABLE ICI
            </section>
            <section id="map" >

            </section>
        </main>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

        <script>
            var map = L.map('map').setView([43.2965, 5.3698], 13);


            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker = L.marker([43.2965, 5.3698]).addTo(map)
                .bindPopup('Aix-Marseille Université')
                .openPopup();
        </script>
        <?php
    }
}