<?php
namespace Blog\Views;

class Homepage {

    public function __construct(private readonly \Blog\Models\Homepage $model) { }

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView(): void {
        ?>
        <main>
            <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

            <div class="card-panel white">
                <form class="col" id="searchForm" onsubmit="return false;" method="POST">
                    <label for="searchType">Type de recherche:</label>
                    <div class="input-field">
                        <select id="searchType" name="searchType">
                            <option value="studentNumber" selected>Numéro Etudiant</option>
                            <option value="name">Nom et Prénom</option>
                            <option value="company">Entreprise</option>
                        </select>
                    </div>
                    <label for="search">Rechercher un étudiant:</label>
                    <input type="text" id="search" name="search" autocomplete="off" maxlength="50" required>
                    <p>Etudiant(s):</p>
                    <div id="searchResults"></div>
                </form>
            </div>
            <div class="center">
                <?php
                if (isset($_SESSION['selected_student']) && isset($_SESSION['selected_student']['firstName']) && isset($_SESSION['selected_student']['lastName'])) {
                    echo '<h4 class="left-align"> Résultat pour: ' . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . '</h4>';
                }
                ?>
            </div>
            <?php
            if (!isset($_SESSION['selected_student']['address']) || $_SESSION['selected_student']['address'] === '') {
                echo "<p>Cet étudiant n'a pas de stage ...</p>";
            }
            else {
                ?>
                <div id="map"></div>
                <?php
            }
            ?>

            <h4 class="center">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <? if (isset($_POST['selecDepSubmitted'])) {

                if (isset($_POST['selecDep'])) {
                    $_SESSION['selecDep'] = $_POST['selecDep'];

                } else {
                    unset($_SESSION['selecDep']);
                }
            }

            $departments = $this->model->getDepTeacher();
            if(!$departments): ?>
                <h6 class="left-align">Vous ne faîtes partie d'aucun département</h6>
            <?
            else: ?>
                <form method="post" class="center-align">
                    <div class="selection">
                        <?
                        foreach($departments as $dep): ?>
                        <label class="formCell">
                            <input type="checkbox" name="selecDep[]" class="filled-in" value="<?= $dep['department_name'] ?>" <? if(isset($_SESSION['selecDep']) && in_array($dep['department_name'], $_SESSION['selecDep'])): ?> checked="checked" <? endif; ?> />
                            <span><? echo str_replace('_', ' ', $dep['department_name']) ?></span>
                        </label>
                        <? endforeach; ?>
                    </div>
                    <input type="hidden" name="selecDepSubmitted" value="1">
                    <button class="waves-effect waves-light btn" type="submit">Afficher</button>
                </form>

                <div class="row"></div>

                <?
                if(isset($_POST['selecStudentSubmitted'])) {

                    if(isset($_POST['selecStudent'])) {
                        $update = $this->model->updateRequests($_POST['selecStudent']);

                    } else {
                        $update = $this->model->updateRequests(array());
                    }

                    if(!$update || gettype($update) !== 'boolean') {
                        echo '<p class="red-text">Une erreur est survenue</p>';
                    }
                }

                if(!empty($_SESSION['selecDep'])):
                    $table = $this->model->getStudentsList($_SESSION['selecDep']);
                    if(empty($table)):
                        echo "<h6 class='left-align'>Aucun stage disponible</h6>";
                    else: ?>
                        <form method="post" class="center-align">
                            <div class="selection">
                                <table class="highlight centered">
                                    <thead>
                                    <tr>
                                        <th>ELEVE</th>
                                        <th>HISTORIQUE</th>
                                        <th>POSITION</th>
                                        <th>SUJET</th>
                                        <th>ENTREPRISE</th>
                                        <th>TOTAL</th>
                                        <th>CHOIX</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <? foreach ($table as $row): ?>
                                        <tr>
                                            <td><?= $row["student_name"] . " " . $row["student_firstname"] ?></td>
                                            <td>
                                                <?php
                                                $internshipCount = count($this->model->getInternships($row['student_number']));
                                                echo $row['internshipTeacher'] > 0 ? 'Oui' : 'Non';
                                                ?>
                                            </td>
                                            <td id="position-<?= $row['student_number'] ?>">Calcul ...</td>
                                            <td><?= str_replace('_', ' ', $row["internship_subject"]) ?></td>
                                            <td><?= str_replace('_', ' ', $row["company_name"]) ?></td>
                                            <td id="totalScore-<?= $row['student_number'] ?>">
                                                <strong>Calcul ...</strong>
                                            </td>
                                            <td>
                                                <label class="center">
                                                    <input type="checkbox" name="selecStudent[]" class="center-align filled-in" value="<?= $row['student_number'] ?>" <?= $row['requested'] ? 'checked="checked"' : '' ?> />
                                                    <span></span>
                                                </label>
                                            </td>
                                        </tr>
                                        <script>
                                            window.addEventListener('load', async function () {
                                                let durationMin;

                                                function calculateScore(dictValues, dictCoef) {
                                                    let totalScore = 0;
                                                    let totalCoef = 0;

                                                    for (let criteria in dictValues) {
                                                        if (dictCoef.hasOwnProperty(criteria)) {
                                                            const value = dictValues[criteria];
                                                            let coef = dictCoef[criteria];

                                                            switch (criteria) {
                                                                case 'Distance':
                                                                    const scoreDuration = (coef / (1 + 0.02 * value));
                                                                    console.log("durée : ", scoreDuration, " - ", coef);
                                                                    totalScore += scoreDuration;
                                                                    break;

                                                                case 'A été responsable':
                                                                    const scoreInternship = (value >= 0) ? value * coef : 0;
                                                                    console.log("asso : ", scoreInternship, " - ", coef);
                                                                    totalScore += scoreInternship;
                                                                    break;

                                                                case 'Cohérence':
                                                                    const scoreRelevance = value * coef;
                                                                    console.log("cohérence : ", scoreRelevance, " - ", coef);
                                                                    totalScore += scoreRelevance;
                                                                    break;

                                                                default:
                                                                    totalScore += value * coef;
                                                                    break;
                                                            }

                                                            totalCoef += coef;
                                                        }
                                                    }

                                                    // Score max/normalisé sur 5, si on a un pb, on renvoie 0
                                                    return Math.max(0, Math.min(5, (totalScore * 5) / totalCoef).toFixed(2)) || 0;
                                                }

                                                const geocodeAddresses = async () => {
                                                    return new Promise((resolve, reject) => {
                                                        const checkGoogleMaps = setInterval(async () => {
                                                            if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                                                                clearInterval(checkGoogleMaps);

                                                                try {
                                                                    const addressTeachPromises = [
                                                                        <? foreach ($_SESSION['address'] as $address): ?>
                                                                        await geocodeAddress('<?= str_replace('_', "'", $address['address']) ?>'),
                                                                        <? endforeach; ?>
                                                                    ];

                                                                    const addressTeach = await Promise.all(addressTeachPromises);
                                                                    const addressStudent = await geocodeAddress('<?= str_replace('_', "'", $row["address"]) ?>');

                                                                    const durationPromises = addressTeach.map(teacherAddress => calculateDistance(addressStudent, teacherAddress, 1));
                                                                    const durations = await Promise.all(durationPromises);

                                                                    const durationValues = durations.map(duration => duration.value);
                                                                    durationMin = Math.min(...durationValues);

                                                                    document.getElementById('position-<?= $row['student_number'] ?>').innerHTML = '~' + Math.floor(durationMin / 60) + ' minutes';
                                                                    resolve(durationMin);
                                                                } catch (error) {
                                                                    reject('Error in geocoding: ' + error);
                                                                }

                                                            } else {
                                                                console.log('Google Maps API is not yet loaded.');
                                                            }
                                                        }, 100);
                                                    });
                                                };

                                                try {
                                                    await geocodeAddresses();

                                                    const dictValues = {
                                                        'A été responsable': <? echo $row['internshipTeacher'] > 0 ? $row['internshipTeacher'] / $internshipCount : 0 ?>,
                                                        'Distance': Math.floor(durationMin / 60),
                                                        'Cohérence': <? echo round($row['relevance'], 2) ?>
                                                    };

                                                    const dictCoef = <?php echo json_encode($this->model->getCoef($_SESSION['identifier']), JSON_NUMERIC_CHECK) ?>;

                                                    document.getElementById('totalScore-<?= $row['student_number'] ?>').innerHTML = calculateScore(dictValues, dictCoef) + '/5';
                                                } catch (error) {
                                                    console.error(error);
                                                }
                                            });
                                        </script>
                                    <? endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <input type="hidden" name="selecStudentSubmitted" value="1">
                            <button class="waves-effect waves-light btn" type="submit">Valider</button>
                        </form>


                    <? endif;
                endif;
            endif; ?>
            <script>
                <? if(isset($_SESSION['address'])): ?>
                    const teacherAddress = "<?= $_SESSION['address'][0]['address']; ?>";
                <? endif;
                if(isset($_SESSION['selected_student']['address'])): ?>
                    const companyAddress = "<?= $_SESSION['selected_student']['address']; ?>";
                <? endif; ?>
            </script>
        </main>
        <?php
    }
}

