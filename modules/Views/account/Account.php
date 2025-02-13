<?php
/**
 * Fichier contenant la vue de la page 'Compte'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/account
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\account;

use Blog\Models\Internship;
use Blog\Models\Teacher;

/**
 * Classe gérant l'affichage de la page 'Compte'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/account
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Account
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Teacher    $teacherModel    Instance de la classe Teacher
     *                                    servant de modèle
     * @param Internship $internshipModel Instance de la classe Internship
     *                                    servant de modèle
     */
    public function __construct(private Teacher $teacherModel,
        private Internship $internshipModel
    ) {
    }

    /**
     * Vue de la page 'Account'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <h3 class="center-align">Stages et alternances assignés</h3>

        <?php
        $trainees = $this->internshipModel->getInterns($_SESSION['identifier']);
        $result = $this->teacherModel
            ->getMaxNumberTrainees($_SESSION['identifier']);
        $internship = 0;
        $alternance = 0;
        $this->internshipModel->getCountInternsPerType(
            $trainees, $internship, $alternance
        );

        if (isset($_POST['newMaxSubmitted'])) {
            if (!isset($_POST['newMaxIntern'])
                || (isset($result['intern'])
                && intval($_POST['newMaxIntern']) === $result['intern'])
                || intval($_POST['newMaxIntern']) < 0
                || intval($_POST['newMaxIntern']) > 100
            ) {
                $newMaxIntern = -1;
            } else if (intval($_POST['newMaxIntern']) < $internship) {
                $newMaxIntern = -1;
                $tooLowIntern = true;
            }

            if (!isset($_POST['newMaxApprentice'])
                || (isset($result['apprentice'])
                && intval($_POST['newMaxApprentice']) === $result['apprentice'])
                || intval($_POST['newMaxApprentice']) < 0
                || intval($_POST['newMaxApprentice']) > 100
            ) {
                $newMaxApprentice = -1;
            } else if (intval($_POST['newMaxApprentice']) < $alternance) {
                $newMaxApprentice = -1;
                $tooLowApprentice = true;
            }

            if (!(isset($tooLowIntern) && $tooLowIntern
                && isset($tooLowApprentice) && $tooLowApprentice)
                && !(isset($newMaxIntern) && isset($newMaxApprentice))
            ) {

                if (!isset($newMaxIntern)) {
                    $newMaxIntern = intval($_POST['newMaxIntern']) ?? -1;
                }
                if (!isset($newMaxApprentice)) {
                    $newMaxApprentice = intval($_POST['newMaxApprentice']) ?? -1;
                }

                $update = $this->teacherModel->updateMaxiNumberTrainees(
                    $_SESSION['identifier'],
                    $newMaxIntern,
                    $newMaxApprentice
                );
                if (!$update || gettype($update) !== 'boolean') {
                    echo '<h6 class="red-text">Une erreur est survenue</h6>';
                } else {
                    if (!$result) {
                        $result = array();
                    }
                    if ($newMaxIntern !== -1) {
                        $result['intern'] = $newMaxIntern;
                    }
                    if ($newMaxApprentice !== -1) {
                        $result['apprentice'] = $newMaxApprentice;
                    }
                }
            }
        }

        ?>

            <div class="row"></div>

            <div id="count-internships">
                <div>
                <?php

                echo '<h5>';
                if ($internship > 0) {
                    echo "Vous tutorez <strong>" . $internship
                    . "</strong> stage" . ($internship !== 1 ? "s" :'');
                } else {
                    echo "Vous ne tutorez <strong>aucun</strong> stage";
                }
                if (isset($result['intern'])) {
                    echo " sur un maximum de " . $result['intern'];
                }

                echo '</h5><h5>';

                if ($alternance > 0) {
                    echo "Vous tutorez <strong>" . $alternance
                    . "</strong> alternance" . ($alternance !== 1 ? "s" : '');
                } else {
                    echo "Vous ne tutorez <strong>aucune</strong> alternance";
                }
                if (isset($result['apprentice'])) {
                    echo " sur un maximum de " . $result['apprentice'];
                }
                echo '</h5>';

                ?>
                </div>
                <div class="card-panel white">
                    <form method="post">
                        <div class="inline">
                        <?php if (!$result['intern']) : ?>
                            <p class="cell"><?php
                                echo "Valeur maximale de stages introuvable, "
                                . "veuillez en entrer une nouvelle";
                            ?></p>
                        <?php else: ?>
                            <p class="cell"><?php
                                echo "Valeur maximale de stages actuelle: "
                                    . $result['intern']
                            ?></p>
                        <?php endif; ?>
                        <?php if (!$result['apprentice']) : ?>
                            <p class="cell"><?php
                                echo "Valeur maximale d'alternance introuvable, "
                                    . "veuillez en entrer une nouvelle";
                            ?></p>
                        <?php else: ?>
                            <p class="cell"><?php
                                echo "Valeur maximale d'alternances actuelle: "
                                    . $result['apprentice']
                            ?></p>
                        <?php endif; ?>
                        </div>

                        <div class="inline">
                            <p class="cell"><?php
                                echo "Nombre de stages en cours ou à venir "
                                    . "que vous tutorez: " . $internship; ?>
                            </p>
                            <p class="cell"><?php
                                echo "Nombre d'alternances en cours ou à venir "
                                    . "que vous tutorez: " . $alternance; ?>
                            </p>
                        </div>

                        <div class="inline">
                            <div class="input-field cell">
                                <label for="newMaxIntern"><?php
                                    echo "Nouvelle valeur maximale:";
                                ?></label>
                                <input type="number" name="newMaxIntern"
                                       id="newMaxIntern" min="0" max="100"
                                       value="<?php
                                        echo ($result['intern']) ?: 0 ?>" />
                            </div>
                            <div class="input-field cell">
                                <label for="newMaxApprentice"><?php
                                    echo "Nouvelle valeur maximale:";
                                ?></label>
                                <input type="number" name="newMaxApprentice"
                                       id="newMaxApprentice" min="0" max="100"
                                       value="<?php
                                        echo ($result['apprentice']) ?: 0 ?>" />
                            </div>
                        </div>

                        <div class="inline">
                            <div class="cell">
                                <button type="reset" class=
                                        "waves-effect waves-light btn tooltip red"
                                        data-tooltip="Réinitialiser le formulaire"
                                        data-position="top">
                                    Annuler
                                </button>
                            </div>
                            <div class="cell">
                                <button type="submit" name="newMaxSubmitted"
                                    value="1" class=
                                        "waves-effect waves-light btn tooltip"
                                    data-tooltip=
                                        "Soumettre vos nouvelles valeurs maximales"
                                    data-position="top">
                                    Valider
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                    <?php
                    if (isset($update)
                        && (!$update || gettype($update) !== 'boolean')
                    ) {
                        echo '<h6 class="red-text">Une erreur est survenue</h6>';
                    } else {
                        if (isset($tooLowIntern) && $tooLowIntern) {
                            echo
                            '<h6 class="red-text">Vous tutorez déjà plus de <strong>'
                            . $_POST['newMaxIntern'] . '</strong> stage'
                            . ($_POST['newMaxIntern'] > 1 ? 's' : '') . '</h6>';
                        }
                        if (isset($tooLowApprentice) && $tooLowApprentice) {
                            echo
                            '<h6 class="red-text">Vous tutorez déjà plus de <strong>'
                            . $_POST['newMaxApprentice'] . '</strong> alternance'
                            . ($_POST['newMaxApprentice'] > 1 ? 's' : '') . '</h6>';
                        }
                        ?>
            </div>

                        <?php if ($trainees) : ?>
            <div class="row"></div>
            <div id="table">
                <table class="highlight centered" id="account-table">
                    <thead <?php
                    if (count($trainees) > 1) {
                        echo 'id="clickable"';
                    } ?>>
                    <tr>
                        <th>ETUDIANT</th>
                        <th>FORMATION</th>
                        <th>GROUPE</th>
                        <th>HISTORIQUE</th>
                        <th>TYPE</th>
                        <th>ENTREPRISE</th>
                        <th>SUJET</th>
                        <th>ADRESSE</th>
                        <th>
                            <div class="tooltip-container tooltip"
                                 data-tooltip=
                                 "Durée moyenne vous séparant du lieu du stage"
                                 data-position="top">(?)
                            </div>POSITION</th>
                    </tr>
                    </thead>
                    <tbody>
                            <?php foreach ($trainees as $row): ?>
                        <tr class="account-row">
                            <td>
                                <?php echo $row["student_name"] . " "
                                . $row["student_firstname"] ?>
                            </td>
                            <td>
                                <?php echo str_replace(
                                    '_', ' ', $row["formation"]
                                ); ?>
                            </td>
                            <td>
                                <?php echo str_replace(
                                    '_', ' ', $row["class_group"]
                                ); ?>
                            </td>
                            <td>
                                <?php echo $row['internshipTeacher'] > 0
                                ? $row['year'] : '❌'; ?>
                            </td>
                            <td>
                                <?php echo strtolower(
                                    $row['type']
                                ) === "internship" ? "Stage" : "Alternance"; ?>
                            </td>
                            <td>
                                <?php echo str_replace(
                                    '_', ' ', $row["company_name"]
                                ); ?>
                            </td>
                            <td>
                                <?php echo str_replace(
                                    '_', ' ', $row["internship_subject"]
                                ); ?>
                            </td>
                            <td>
                                <?php echo str_replace(
                                    '_', "'", $row['address']
                                ); ?>
                            </td>
                            <td>~<?php echo $row['duration'] ?> minutes</td>
                        </tr>
                            <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="row">
                    <div class="input-field col s2">
                        <label for="rows-per-page"></label>
                        <select id="rows-per-page">
                            <option value="10" selected>10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                            <option value="<?php echo count($trainees)?>">
                                Tout
                            </option>
                        </select>
                        <label>Nombre de lignes par page</label>
                    </div>
                </div>

                <div id="pagination-controls" class="center-align">
                    <button type="button" class="waves-effect waves-light btn"
                        id="first-page"><i class="material-icons" type="button">
                        first_page
                    </i></button>
                    <button type="button" class="waves-effect waves-light btn"
                        id="prev-page"><i class="material-icons" type="button">
                        arrow_back
                    </i></button>
                    <div id="page-numbers"></div>
                    <button type="button" class="waves-effect waves-light btn"
                        id="next-page"><i class="material-icons" type="button">
                        arrow_forward
                    </i></button>
                    <button type="button" class="waves-effect waves-light btn"
                        id="last-page"><i class="material-icons" type="button">
                        last_page
                    </i></button>
                </div>
            </div>
                        <?php endif; ?>
        </main>
                        <?php
                    }
    }
}