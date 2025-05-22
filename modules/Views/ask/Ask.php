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
                <div class="center">
                    <?php
                    if (isset($_POST['cancelSearch'])) {
                        unset($_SESSION['selected_student']);
                    }

                    if (isset($_POST['searchedStudentSubmitted'])) {

                        $update = $this->internshipModel
                            ->updateSearchedStudentInternship(
                                isset($_POST['searchedStudent']),
                                $_SESSION['identifier'],
                                $_POST['searchedStudentSubmitted']
                            );

                        if (!$update || gettype($update) !== 'boolean') {
                            echo '<h6 class="red-text">Une erreur est survenue</h6>';
                        } else {
                            echo '<h6 class="green-text">'
                                . 'Vos choix ont bien été pris en compte</h6>';
                        }
                    }
                    ?>
                    <?php
                    if (isset($_SESSION['selected_student']['firstName'])
                        && isset($_SESSION['selected_student']['lastName'])
                    ) {
                        echo '<h2 class="left-align"> Résultat pour: ' .
                            $_SESSION['selected_student']['firstName'] . ' ' .
                            $_SESSION['selected_student']['lastName'] . '</h2>';
                        if (!isset($_SESSION['selected_student']['address'])
                            || $_SESSION['selected_student']['address'] === ''
                        ) {
                            echo "<p>Cet étudiant n'a pas de stage ...</p>";
                        } else {
                            $internshipInfos = $this->internshipModel
                                ->getInternshipStudent(
                                    $_SESSION['selected_student']['id']
                                );
                            if ($internshipInfos) {
                                $internships = $this->internshipModel->getInternships(
                                    $_SESSION['selected_student']['id']
                                );
                                $year = "";
                                $nbInternships = $this->internshipModel
                                    ->getInternshipTeacher(
                                        $internships, $_SESSION['identifier'], $year
                                    );
                                $distance = $this->internshipModel->getDistance(
                                    $internshipInfos['internship_identifier'],
                                    $_SESSION['identifier'],
                                    isset($internshipInfos['id_teacher'])
                                );
                                $inDep = false;
                                foreach ($this->studentModel->getDepStudent(
                                    $_SESSION['selected_student']['id']
                                ) as $dep
                                ) {
                                    if (in_array(
                                        $dep, $this->teacherModel
                                        ->getDepTeacher($_SESSION['identifier'])
                                    )
                                    ) {
                                        $inDep = true;
                                        break;
                                    }
                                }
                                if (!$internshipInfos['id_teacher'] && $inDep) {
                                    echo '<form method="post" class="center-align">';
                                } else {
                                    echo '<div class="center-align">';
                                }
                                ?>
                                <table class="highlight centered
                                responsive-table" id="search-table">
                                    <thead>
                                    <tr>
                                        <th>FORMATION</th>
                                        <th>GROUPE</th>
                                        <th>
                                            <div class="tooltip-container tooltip"
                                                 data-tooltip="Dernier antécédent
                                                 d'accompagnement"
                                                 data-position="top">(?)</div>
                                            HISTORIQUE
                                        </th>
                                        <th>ENTREPRISE</th>
                                        <th>SUJET</th>
                                        <th>ADRESSE</th>
                                        <th>
                                            <div class="tooltip-container tooltip"
                                                 data-tooltip="Durée moyenne vous
                                                séparant du lieu du stage"
                                                 data-position="top">(?)</div>
                                            POSITION
                                        </th>
                                        <th>
                                            <div class="tooltip-container tooltip"
                                                 data-tooltip="Voeux formulés"
                                                 data-position="top">(?)</div>
                                            CHOIX
                                        </th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $internshipInfos['formation']
                                            ) ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $internshipInfos['class_group']
                                            ) ?></td>
                                        <td>
                                            <?php echo $nbInternships > 0
                                                ? $year : 'Non' ?>
                                        </td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $internshipInfos["company_name"]
                                            ) ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', ' ',
                                                $internshipInfos[
                                                "internship_subject"
                                                ]
                                            ) ?></td>
                                        <td><?php
                                            echo str_replace(
                                                '_', "'",
                                                $internshipInfos['address']
                                            ) ?></td>
                                        <td>~<?php
                                            echo $distance . " minute"
                                                . ($distance > 1 ? "s" : "")
                                            ?></td>
                                        <td>
                                            <?php
                                            if (!$inDep) {
                                                echo "<strong>" .
                                                    $_SESSION['selected_student']['firstName']. ' ' .
                                                    $_SESSION['selected_student']['lastName'] .
                                                    "</strong> ne fait partie d'aucun " .
                                                    "de vos départements";
                                            } else {
                                                $id_teacher = $internshipInfos['id_teacher'];
                                                if ($id_teacher) {
                                                    if ($id_teacher === $_SESSION['identifier']
                                                    ) {
                                                        echo "Vous êtes déjà le tuteur de " .
                                                            "<strong>" . $_SESSION['selected_student']
                                                            ['firstName'] . ' ' .
                                                            $_SESSION['selected_student']
                                                            ['lastName'] . "</strong> !";
                                                    } else {
                                                        echo "<strong>";
                                                        if ($internshipInfos['teacher_name']
                                                            && $internshipInfos['teacher_firstname']
                                                        ) {
                                                            echo $internshipInfos
                                                                ['teacher_name'] . " " .
                                                                $internshipInfos
                                                                ['teacher_firstname'];
                                                        } else {
                                                            echo $id_teacher;
                                                        }
                                                        echo "</strong>";
                                                    }
                                                } else {
                                                    ?>
                                                    <label class="center">
                                                        <input type="checkbox"
                                                               name="searchedStudent"
                                                               class="center-align filled-in"
                                                               value="1"
                                                            <?php echo in_array(
                                                                $internshipInfos
                                                                ["internship_identifier"],
                                                                $this->internshipModel->getRequests(
                                                                    $_SESSION['identifier']
                                                                )
                                                            ) ? 'checked="checked"' : ''
                                                            ?> />
                                                        <span>Cocher</span>
                                                    </label>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                                <?php
                                if (!$internshipInfos['id_teacher'] && $inDep) {
                                    ?>
                                    <div class="row"></div>
                                    <button class="waves-effect waves-light btn tooltip"
                                            name="searchedStudentSubmitted" value="<?php echo
                                    $internshipInfos["internship_identifier"] ?>"
                                            type="submit" formmethod="post" data-tooltip=
                                            "Valider votre choix" data-position="top">
                                        Valider
                                    </button>
                                    <?php
                                    echo "</form>";
                                } else {
                                    echo "</div>";
                                }
                                echo '<div class="row"></div>';
                            } else {
                                echo "<p>Cet étudiant n'a pas de stage ...</p>";
                            }
                        }
                        ?>
                        <form method="post" class="center-align">
                            <button class=
                                    "waves-effect waves-light btn btn-annuler tooltip"
                                    name="cancelSearch" value="1" type="submit"
                                    formmethod="post" data-tooltip="Annuler la recherche"
                                    data-position="top">Annuler</button>
                        </form>
                        <?php
                    } else {
                        echo '</div>';
                    }
                    ?>
                </div>
            </section>
            <section id="map" >

            </section>
        </main>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
        <script>



            $(document).ready(function() {
                fetch('/api/datatable', {
                    method: 'POST'
                })
                    .then(response => response.text())
                    .then(data => {
                        console.log('Contenu JSON :', data);
                    })
                    .catch(error => {
                        console.error('Erreur :', error);
                    });

                // Fonction pour sélectionner tous les éléments visibles
                function selectAllVisible() {
                    let table = $('#homepage-table').DataTable();
                    let currentMode = table.select.items();

                    if (currentMode === 'row') {
                        table.rows({page: 'current'}).select();
                    } else {
                        table.columns(':visible').select();
                    }
                }

                // Fonction pour désélectionner tous les éléments
                function deselectAll() {
                    let table = $('#homepage-table').DataTable();
                    let currentMode = table.select.items();

                    if (currentMode === 'row') {
                        table.rows().deselect();
                    } else {
                        table.columns().deselect();
                    }
                }

                new DataTable('#homepage-table', {
                    keys: true,
                    fixedHeader: true,
                    order: [],
                    ordering: true,
                    serverSide: true,
                    stateSave: true,
                    pageLength: 10,
                    processing: true,
                    // ""pagingType": "input",

                    ajax: {
                        url: '/api/datatable',
                        type: 'POST',
                        dataSrc: 'data',
                        cache: true
                    },
                    columns: [
                        { data: 'student' },
                        { data: 'formation' },
                        { data: 'group' },
                        { data: 'history' },
                        { data: 'company' },
                        { data: 'subject' },
                        { data: 'address' },
                        { data: 'duration' },
                        { data: 'choice' }
                    ],

                    select: {
                        style: 'multi',
                        items: 'row'
                    },

                    language: {
                        select: {
                            rows: {_: "%d lignes sélectionnées", 0: "", 1: "1 ligne sélectionnée"},
                            columns: "", cells: ""
                        }
                    },

                    layout: {
                        topStart: {
                            buttons: [
                                {
                                    extend: 'copy',
                                    exportOptions: {columns: ':visible'}
                                },
                                {
                                    extend: 'excel',
                                    exportOptions: {columns: ':visible'}
                                },
                                {
                                    extend: 'csv',
                                    exportOptions: {columns: ':visible'}
                                },
                                {
                                    extend: 'print',
                                    exportOptions: {columns: ':visible'}
                                },
                                'colvis',
                                {
                                    text: 'Tout sélectionner',
                                    attr: {id: 'selectAllBtn', class: 'dt-button select-all-btn'},
                                    action: selectAllVisible
                                },
                                {
                                    text: 'Tout désélectionner',
                                    attr: {id: 'deselectAllBtn', class: 'dt-button deselect-all-btn'},
                                    action: deselectAll
                                }
                            ]
                        },
                        topEnd: {
                            search: {placeholder: 'Rechercher...'}
                        },
                        bottomStart: ['info'],
                        bottomEnd: ['paging']
                    }
                });

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
        </script>
        <?php
    }
}