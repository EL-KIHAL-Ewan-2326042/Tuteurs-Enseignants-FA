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
            <div>


            <section>
                <form method="post" class="center-align table">
                    <table class="highlight centered responsive-table" id="homepage-table">
                        <thead>
                        <tr>
                            <th>ETUDIANT</th>
                            <th>FORMATION</th>
                            <th>GROUPE</th>
                            <th>
                                <div class="tooltip-container tooltip"
                                     data-tooltip="Dernier antécédent d'accompagnement"
                                     data-position="top">(?)</div>
                                HISTORIQUE
                            </th>
                            <th>ENTREPRISE</th>
                            <th>SUJET</th>
                            <th>ADRESSE</th>
                            <th>
                                <div class="tooltip-container tooltip"
                                     data-tooltip="Durée moyenne vous séparant du lieu du stage"
                                     data-position="top">(?)</div>
                                POSITION
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>



                    <button class="waves-effect waves-light
                            btn btn-annuler tooltip"
                            type="reset" id="resetForm" data-tooltip=
                            "Annuler les modifications"
                            data-position="top">Annuler</button>
                    <button class="waves-effect waves-light btn tooltip"
                            name="selecInternshipSubmitted" value="1"
                            type="submit" data-tooltip="Envoyer vos choix"
                            data-position="top">Valider</button>
                </form>


            </section>
            <section id="map" >

            </section>
            </div>
        </main>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>
            $(document).ready(function() {

                <?php if (!empty($_SESSION['address'])) : ?>
                const teacherAddress = "<?php echo $_SESSION['address'][0]['address']; ?>";
                <?php endif;
                if(isset($_SESSION['selected_student']['address'])) : ?>
                const companyAddress = "<?php echo $_SESSION['selected_student']['address']; ?>";
                <?php endif; ?>
            });

            var map = L.map('map').setView([43.2965, 5.3698], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            var marker = L.marker([43.2965, 5.3698]).addTo(map)
                .bindPopup('Aix-Marseille Université')
                .openPopup();

            <?php if (!empty($_SESSION['address'])) : ?>
            const teacherAddress = "<?php echo $_SESSION['address'][0]['address']; ?>";
            <?php endif;
            if(isset($_SESSION['selected_student']['address'])) : ?>
            const companyAddress = "<?php echo $_SESSION['selected_student']['address']; ?>";
            <?php endif; ?>

        </script>
        <?php

        unset($_SESSION['selected_student']);
    }
}