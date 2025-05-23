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
use Blog\Views\components\Table;

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
        $headers = ['Élève', 'Formation', 'Groupe', 'Entreprise', 'Sujet', 'Fin De Stage', 'Adresse', 'Distance'];

        $jsColumns = [
            ['data' => 'student'],
            ['data' => 'formation'],
            ['data' => 'group'],
            ['data' => 'company'],
            ['data' => 'subject'],
            ['data' => 'end_date'],
            ['data' => 'address'],
            ['data' => 'distance'],
        ];
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
        <main>
            <div>
                <div>
                <div>
                    <h5>
                        A propos de vous
                    </h5>
                    <div class="fs8">
                        <div>
                            <div class="df ac g1">
                                <i class="material-icons left tiny">supervisor_account</i><span>Roles:</span>
                            </div>

                            <ul class="df ac g1">
                                <?php foreach ($_SESSION['roles'] as $role): ?>
                                    <li class="roles fs8">
                                        <?= htmlspecialchars($role) ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div>
                            <div class="df ac g1">
                                <i class="material-icons left tiny">location_on</i><span>Adresse:</span>
                            </div>

                            <ul>
                                <?php foreach ($_SESSION['address'] as $addr): ?>
                                    <li class="fs8"><?= htmlspecialchars($addr['address']) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>

                        <div>
                            <div class="df ac g1">
                                <i class="material-icons left tiny">apartment</i><span>Département:</span>
                            </div>

                            <ul>
                                <?php foreach ($_SESSION['role_department'] as $dept): ?>
                                    <li class="dep fs8"><?= htmlspecialchars($dept) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                </div>
                <div>
                    <h5>
                        Stages et alternances assignés
                    </h5>
                    <form method="post" name="max_internship" class="card fs8">
                                <div class="df ac g1">
                                    <label for="newMaxIntern">Stages max: </label>
                                    <div >
                                        <button type="button" onclick="adjustValue('newMaxIntern', -1)" class="btn-m moin">
                                            <i class="material-icons tiny">remove</i>
                                        </button>
                                        <input type="number"
                                               id="newMaxIntern"
                                               name="newMaxIntern"
                                               min="0"
                                               max="100"
                                               value="<?php echo $result['intern'] ?: 0; ?>"/>
                                        <button type="button" onclick="adjustValue('newMaxIntern', 1)" class="btn-m plus">
                                            <i class="material-icons tiny">add</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="df ac g1">
                                    <label for="newMaxApprentice">Alternances max: </label>
                                    <div >
                                        <button type="button" onclick="adjustValue('newMaxApprentice', -1)" class="btn-m moin">
                                            <i class="material-icons tiny">remove</i>
                                        </button>
                                        <input type="number"
                                               id="newMaxApprentice"
                                               name="newMaxApprentice"
                                               min="0"
                                               max="100"
                                               value="<?php echo $result['apprentice'] ?: 0; ?>" />
                                        <button type="button" onclick="adjustValue('newMaxApprentice', 1)" class="btn-m plus">
                                            <i class="material-icons tiny">add</i>
                                        </button>
                                    </div>
                                </div>
                        <button type="submit"
                                        name="newMaxSubmitted"
                                        value="1">
                                    Enregistrer les modifications
                        </button>
                    </form>

                </div>
                </div>
                <a href="/intramu" class="deco">Deconnexion</a>


            </div>
            <div>
                <?php Table::render('homepage-table', $headers, $jsColumns, '/api/datatable'); ?>
            </div>
        </main>
        <script>
            function adjustValue(id, delta) {
                const input = document.getElementById(id);
                let value = parseInt(input.value) || 0;
                value += delta;
                if (value >= 0 && value <= 100) {
                    input.value = value;
                }
            }
        </script>
                        <?php

    }
}