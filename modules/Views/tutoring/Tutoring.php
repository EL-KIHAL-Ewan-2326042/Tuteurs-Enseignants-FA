<?php
/**
 * Fichier contenant la vue de la page 'Stages'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/tutoring
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\tutoring;

use Blog\Models\Internship;
use Blog\Models\User;

/**
 * Classe gérant l'affichage de la page 'Stages'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/tutoring
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Tutoring
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param User       $userModel       Instance de la classe Teacher
     *                                    servant de modèle
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     */
    public function __construct(private User $userModel,
        private Internship $internshipModel
    ) {
    }

    /**
     * Vue de la page 'Stages'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <h1 class="center-align">Stages et alternances dans vos départements</h1>

            <?php
            $departments = $this->userModel
                ->getAdminDepartments($_SESSION['identifier']);
            $table = $this->internshipModel
                ->getInternshipsWithTutor($departments);

            if ($table && sizeof($table) > 0) : ?>

            <div class="table">
                <table class="highlight centered" id="tutoring-table">
                    <thead <?php
                    if (count($table) > 1) {
                        echo 'class="clickable"';
                    } ?>>
                        <tr>
                            <th>DEPARTEMENT</th>
                            <th>ETUDIANT</th>
                            <th>FORMATION</th>
                            <th>GROUPE</th>
                            <th>TUTEUR</th>
                            <th>TYPE</th>
                            <th>DATE DE DEBUT</th>
                            <th>DATE DE FIN</th>
                            <th>ENTREPRISE</th>
                            <th>SUJET</th>
                            <th>ADRESSE</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table as $row) : ?>
                            <tr class="tutoring-row">
                                <td><?php
                                    echo str_replace(
                                        '_', ' ', $row['department_name']
                                    )
                                    ?></td>
                                <td><?php
                                    echo $row["student_name"] . " " .
                                        $row["student_firstname"]
                                ?></td>
                                <td><?php
                                    echo str_replace(
                                        '_', ' ',
                                        $row["formation"]
                                    )
                                    ?></td>
                                <td><?php
                                    echo str_replace(
                                        '_', ' ', $row["class_group"]
                                    )
                                    ?></td>
                                <td><?php
                                    echo $row["teacher_name"] . " " .
                                        $row["teacher_firstname"]
                                ?></td>
                                <td><?php
                                    echo strtolower(
                                        $row["type"]
                                    ) === "internship" ? "Stage" : "Alternance";
                                    ?></td>
                                <td data-value="<?php
                                echo $row['start_date_internship'] ?>"><?php
                                    echo substr(
                                        $row["start_date_internship"], -2
                                    ) . '/' . substr(
                                        $row["start_date_internship"], 5, 2
                                    ) . '/' . substr(
                                        $row["start_date_internship"], 0, 4
                                    )
                                ?></td>
                                <td data-value="<?php
                                echo $row['end_date_internship'] ?>"><?php
                                    echo substr(
                                        $row["end_date_internship"], -2
                                    ) . '/' . substr(
                                        $row["end_date_internship"], 5, 2
                                    ) . '/' . substr(
                                        $row["end_date_internship"], 0, 4
                                    )
                                ?></td>
                                <td><?php
                                    echo str_replace(
                                        '_', ' ',
                                        $row["company_name"]
                                    )
                                    ?></td>
                                <td><?php
                                    echo str_replace(
                                        '_', ' ', $row["internship_subject"]
                                    )
                                    ?></td>
                                <td><?php
                                    echo str_replace(
                                        '_', "'", $row['address']
                                    )
                                    ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row">
                    <div class="input-field col s2">
                        <select id="rows-per-page">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="<?php echo count($table)?>">
                                Tout
                            </option>
                        </select>
                        <label
                                for="rows-per-page"
                        >Nombre de lignes par page</label>
                    </div>
                </div>

                <div id="pagination-controls" class="center-align">
                    <button type="button" class=
                    "waves-effect waves-light btn" id="first-page">
                        <i class="material-icons" type="button">
                            first_page
                        </i></button>
                    <button type="button" class=
                    "waves-effect waves-light btn" id="prev-page">
                        <i class="material-icons" type="button">
                            arrow_back
                        </i></button>
                    <div id="page-numbers"></div>
                    <button type="button" class=
                    "waves-effect waves-light btn" id="next-page">
                        <i class="material-icons" type="button">
                            arrow_forward
                        </i></button>
                    <button type="button" class=
                    "waves-effect waves-light btn" id="last-page">
                        <i class="material-icons" type="button">
                            last_page
                        </i></button>
                </div>
            </div>
                <?php
            else :
                echo "<h6 class='left-align'
                >Aucun stage à afficher</h6>";
            endif; ?>
        </main>
        <?php
    }
}