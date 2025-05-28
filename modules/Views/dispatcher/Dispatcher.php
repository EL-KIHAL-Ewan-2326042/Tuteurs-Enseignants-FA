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
    ) {
    }

    public function showView(): void
    {
        ?>
        <main>
            <?php
            if (($_POST['action'] ?? '') !== 'generate') {
                // Affichage du formulaire
                CoefBackup::render(
                    $this->userModel,
                    $this->errorMessageAfterSort,
                    $this->checkMessageAfterSort
                );
            } if (isset($_POST['coef']) && isset($_POST['action'])
            && $_POST['action'] === 'generate'
        ) : ?>
        <div class="center">

            <div id="map" class="map-container" style="position: relative;">
                <div id="map-loading-overlay" style="display: none;">
                    <div class="loading-message">Mise à jour de la map...</div>
                    <div class="progress">
                        <div class="indeterminate"></div>
                    </div>
                </div>
            </div>

            <div class="card-container">
                <div class="card-panel white z-depth-1 dispatcher-legend-card">
                    <h6 class="flow-text
                    dispatcher-legend-title">Légende de la carte</h6>
                    <ul class="browser-default dispatcher-legend-list">
                        <li><span class="legend-color-box
                        red"></span> Enseignant sélectionné:
                        Peut être changé lors d'un clique sur une colonne</li>
                        <li><span class="legend-color-box
                        blue"></span> Autres enseignants: comprend seulement
                        ceux du même département que celui de l'élève</li>
                        <li><span class="legend-color-box
                        yellow"></span> Entreprise: l'entreprise de
                            l'élève selectionné</li>
                        <li>
                            <span class="legend-line-box"></span
                            > Distance entre l'enseignant et l'entreprise
                        </li>
                    </ul>
                </div>

                <div class="card-panel white z-depth-1 dispatcher-info-card">
                    <h6 class="flow-text
dispatcher-info-title">Informations</h6>
                    <p class="dispatcher-info-text">
                        <i class="material-icons
    left tiny">mouse</i
                        > Cliquez sur une ligne du tableau
                        pour afficher la vue étudiante et
                        les informations
                        de l'étudiant sur la carte.
                    </p>
                    <p class="dispatcher-info-text">
                        <i class="material-icons left
                        tiny">sort</i> Vous pouvez trier le tableau
                        en cliquant sur le titre d'une colonne.
                    </p>
                    <p class="dispatcher-info-text">
                        <i class="material-icons
                        left tiny">settings</i> Critères et coefficients
                        utilisés pour la répartition :
                        <?php
                        $dictCoef = array_filter(
                            $_POST['coef'], function ($coef, $key) {
                                return isset($_POST['criteria_on'][$key]);
                            }, ARRAY_FILTER_USE_BOTH
                        );
                        ?>
                        <?php if (!empty($dictCoef)) : ?>
                    <ul>
                            <?php foreach ($dictCoef as $name => $coef): ?>
                            <li><strong>
                                <?php echo $name; ?>
                                </strong> : <?php echo $coef; ?></li>
                            <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                        Aucun critère sélectionné.
                    <?php endif; ?>
                    </p>
                </div>
            </div>

        <form action="./dispatcher" method="post">
            <?php
            // Sauvegarde dans la session pour l'API
            $_SESSION['last_dict_coef'] = array_filter($_POST['coef'], function ($coef, $key) {
                return isset($_POST['criteria_on'][$key]);
            }, ARRAY_FILTER_USE_BOTH);

            Table::render(
                'dispatch-table',
                ['Enseignant', 'Etudiant', 'Stage', 'Formation', 'Groupe', 'Sujet', 'Adresse', 'Score', 'Associer'],
                [
                    ['data' => 'teacher'],
                    ['data' => 'student'],
                    ['data' => 'internship'],
                    ['data' => 'formation'],
                    ['data' => 'group'],
                    ['data' => 'subject'],
                    ['data' => 'address'],
                    ['data' => 'score'],
                    ['data' => 'associate']
                ],
                '/api/dispatch-list'
            );
            ?>
            <br>
            <button type="submit" name="selecInternshipSubmitted" value="1" class="btn">Valider</button>
        </form>

    <?php endif; ?>

        </div>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var tooltips = document.querySelectorAll('.tooltipped');
                M.Tooltip.init(tooltips);
            })
        </script>
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
