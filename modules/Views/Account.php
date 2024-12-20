<?php

namespace Blog\Views;

class Account {

    public function __construct(private readonly \Blog\Models\Account $model) { }

    /**
     * Vue de Account
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <h3 class="center-align">Stages et alternances assignés</h3>

            <?
            $interns = $this->model->getInterns($_SESSION['identifier']) ?? array();
            $max = $this->model->getMaxNumberInterns($_SESSION['identifier']);
            $max = false;
            ?>

            <div class="row"></div>

            <div class="countInternships">
                <div class="cell">
                    <?
                    $internship = 0;
                    $alternance = 0;
                    $this->model->getCountInternsPerType($interns, $internship, $alternance);
                    echo '<h5>';
                    if ($internship + $alternance > 0) {
                        if ($internship > 0) {
                            if ($internship === 1) {
                                echo "Vous êtes le tuteur de <strong>1</strong> stage ";
                            } else {
                                echo "Vous êtes le tuteur de <strong>" . $internship . "</strong> stages ";
                            }
                        } else echo "Vous n'êtes le tuteur d'<strong>aucun</strong> stage ";
                        if ($alternance > 0) {
                            if ($alternance === 1) {
                                echo "et d'<strong>1</strong> alternance";
                            } else {
                                echo "et de <strong>" . $alternance . "</strong> alternances";
                            }
                        } else echo "mais d'<strong>aucune</strong> alternance";
                    } else echo "Vous n'êtes le tuteur d'<strong>aucun stage ni alternance</strong>";
                    if ($max) echo " sur un maximum de <strong>" . $max . "</strong> au total";
                    echo '</h5>';
                    ?>
                </div>
                <? if (!$max): ?>
                    <div class="cell">
                        <p>Valeur maximale introuvable, veuillez en entrer une nouvelle</p>
                    </div>
                <? endif; ?>
                <div class="cell form">
                    <form method="post">
                        <div class="input-field">
                            <label for="newMaxNumber">Nouvelle valeur maximale:</label>
                            <input type="number" name="newMaxNumber" id="newMaxNumber" min="1" max="100" value="<?= ($max) ? $max : 1 ?>" />
                        </div>
                        <div>
                            <button class="waves-effect waves-light btn">Valider</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    <?php
    }
}