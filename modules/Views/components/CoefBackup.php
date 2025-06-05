<?php

namespace Blog\Views\components;

class CoefBackup
{
    public static function render($userModel, $errorMessageAfterSort, $checkMessageAfterSort): void
    {
        ?>
        <div id="forms-section">
            <form action="./dispatcher" method="post" onsubmit="showLoading();">
                <?php
                unset($id_backup);
                $id_backup = $_POST['save-selector'] ?? 'new';

                if ($id_backup === 'new' || $id_backup === 'default' || $id_backup === 'i') {
                    $defaultCriteria = $userModel->getDefaultCoef();
                    $listCriteria = [];
                    foreach ($defaultCriteria as $key => $value) {
                        $listCriteria[$key] = $value;
                    }
                    $name_save = 'Nouvelle sauvegarde';
                } else {
                    $listCriteria = $userModel->loadCoefficients(
                        $_SESSION['identifier'],
                        $id_backup
                    );
                    $name_save = $listCriteria[0]['name_save'];
                }
                ?>

                <section class="mpsection">
                    <p>Le Répartiteur </p>
                    <div>
                    <span>Coefficients</span>
                    <div>
                        <?php
                        function normalize_name($string) {
                            // Supprimer les accents
                            $string = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $string);
                            // Remplacer les espaces par des underscores
                            $string = str_replace(' ', '_', $string);
                            // Mettre en minuscules
                            return strtolower($string);
                        }

                        foreach ($listCriteria as $criteria):
                            $value = $criteria['coef'];
                            $originalName = $criteria['name_criteria'];
                            $name = normalize_name($originalName);
                            $description = $criteria['description'];
                            ?>
                            <div>
                                <label>
                                    <input type="hidden"
                                           name="is_checked[<?php echo $name;?>]"
                                           value="<?php echo $value;?>">
                                    <input type="checkbox"
                                           name="criteria_on[<?php echo $name;?>]"
                                           checked="checked"
                                           hidden="hidden"/>
                                    <span data-position="top" data-tooltip="<?php echo $description ?>">
                <?php echo $originalName; ?>
            </span>
                                </label>
                                <input type="number"
                                       name="coef[<?php echo $name; ?>]"
                                       id="<?php echo $name; ?>"
                                       min="1" max="100"
                                       value="<?php echo $value ?>"
                                /><label for="<?php echo $name; ?>" style="display: none;">Coefficient</label>
                            </div>
                        <?php endforeach; ?>


                    </div>
                    </div>
                    <button type="submit" name="action" value="generate" id="generate-btn"
                            data-position="top" data-tooltip="Commencer la répartition">
                        Générer <i class="material-icons right">send</i>
                    </button>
                </section>

                <?php $saves = $userModel->showCoefficients($_SESSION['identifier']); ?>

                <section class="mpsection">
                    <p>La Gestion des sauvegardes</p>
                    <label for="save-selector" class="df fdc g1">
                        Sélectionnez une sauvegarde
                        <select id="save-selector" name="save-selector" onchange="this.form.submit()">
                            <option value="new">Nouvelle Sauvegarde</option>
                            <?php
                            $saveSelected = $_POST['save-selector'] ?? null;
                            foreach ($saves as $save):
                                $id_backup = htmlspecialchars($save['id_backup']);
                                $name_save = htmlspecialchars($save['name_save']);
                                $selected = ($saveSelected == $id_backup) ? 'selected' : '';
                                ?>
                                <option value="<?= $id_backup ?>" <?= $selected ?>>
                                    <?= $name_save ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <?php
                    if ($id_backup == 'new') {
                        $tooltip = "Créer la sauvegarde";
                        $btnValue = "Créer";
                    } else {
                        $tooltip = "Enregistrer la sauvegarde";
                        $btnValue = "Enregistrer";
                    }
                    ?>
                    <div class="df fdc g1">
                        <label for="save-name">Nom de la sauvegarde</label>
                        <input type="text" id="save-name" name="save-name" value="<?php echo $name_save ?>">
                    </div>
                    <div>
                        <button type="submit" name="action-save" value="<?php echo $id_backup ?>" id="save-btn"
                                data-position="top" data-tooltip="<?php echo $tooltip ?>">
                            <?php echo $btnValue ?>
                            <i class="material-icons right">arrow_downward</i>
                        </button>
                        <button type="submit" name="action-delete" value="<?php echo $id_backup ?>" id="delete-btn"
                                data-position="top" data-tooltip="Supprimer la sauvegarde">
                            Supprimer <i class="material-icons right">delete</i>
                        </button>
                    </div>
                </section>
            </form>

            <p class="red-text loose toast" id="checkboxError"><?php echo $errorMessageAfterSort; ?></p>
            <p class="green-text win toast"><?php echo $checkMessageAfterSort; ?></p>

            <div id="loading-section" style="display: none;">
                <p>Chargement en cours, veuillez patienter...</p>
                <div><div class="indeterminate"></div></div>
            </div>
        </div>
        <?php
    }
}
