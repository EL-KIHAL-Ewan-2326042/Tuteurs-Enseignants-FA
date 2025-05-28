<?php
/**
 * Fichier contenant la vue de la page 'Répartiteur'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dispatcher
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */

namespace Blog\Views\dispatcher;

use Blog\Models\Department;
use Blog\Models\Internship;
use Blog\Models\Teacher;
use Blog\Models\User;
use Blog\Views\components\CoefBackup;

/**
 * Classe gérant l'affichage de la page 'Répartiteur'
 *
 * PHP version 8.3
 *
 * @category View
 * @package  TutorMap/modules/Views/dispatcher
 *
 * @author Alvares Titouan <titouan.alvares@etu.univ-amu.fr>
 * @author Avias Daphné <daphne.avias@etu.univ-amu.fr>
 * @author Kerbadou Islem <islem.kerbadou@etu.univ-amu.fr>
 * @author Pellet Casimir <casimir.pellet@etu.univ-amu.fr>
 *
 * @license MIT License https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants/blob/main/LICENSE
 * @link    https://github.com/AVIAS-Daphne-2326010/Tuteurs-Enseignants
 */
class Dispatcher
{

    /**
     * Initialise les attributs passés en paramètre
     *
     * @param Internship $internshipModel         Instance de la classe Internship
     *                                            servant de modèle
     * @param User       $userModel               Instance de la classe User
     *                                            servant de modèle
     * @param Teacher    $teacherModel            Instance de la classe Teacher
     *                                            servant de modèle
     * @param Department $departmentModel         Instance de la classe Department
     *                                            servant de modèle
     * @param string     $errorMessageAfterSort   Message d'erreur s'affichant quand
     *                                            la répartition n'a pas fonctionné
     * @param string     $errorMessageDirectAssoc Message d'erreur s'affichant quand
     *                                            l'association n'a pas fonctionné
     * @param string     $checkMessageDirectAssoc Message s'affichant quand
     *                                            la répartition a fonctionné
     * @param string     $checkMessageAfterSort   Message s'affichant quand
     *                                            l'association a fonctionné
     */
    public function __construct(
        private Internship $internshipModel,
        private User $userModel,
        private Teacher $teacherModel,
        private Department $departmentModel,
        private string $errorMessageAfterSort,
        private string $errorMessageDirectAssoc,
        private string $checkMessageDirectAssoc,
        private string $checkMessageAfterSort
    ) {
    }

    /**
     * Affiche la page 'Répartiteur'
     *
     * @return void
     */
    public function showView(): void
    {
        ?>
        <main>
            <?php
        if (!isset($_POST['action']) || $_POST['action'] !== 'generate') : CoefBackup::render($this->userModel, $this->errorMessageAfterSort, $this->checkMessageAfterSort); ?>

              <?php endif ?>

        </main>
        <?php
    }

    /**
     * Renvoie les éléments HTML correspondant à l'affichage
     * en étoiles du score passé en paramètre
     *
     * @param float $score Score de pertinence que
     *                     l'on veut convertir en étoiles
     *
     * @return string Chaîne de caractères contenant
     * les étoiles correspondant au score
     */
    function renderStars(float $score): string
    {
        $fullStars = floor($score);

        $decimalPart = $score - $fullStars;

        $halfStars = ($decimalPart > 0 && $decimalPart < 1) ? 1 : 0;

        $emptyStars = 5 - $fullStars - $halfStars;

        $stars = '';

        for ($i = 0; $i < $fullStars; $i++) {
            $stars .= '<span class="filled"></span>';
        }

        if ($halfStars) {
            $stars .= '<span class="half"></span>';
        }

        for ($i = 0; $i < $emptyStars; $i++) {
            $stars .= '<span class="empty"></span>';
        }

        return $stars;
    }
}