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

            <h4 class="left-align">Sélectionnez le(s) département(s) :</h4>

            <div class="row"></div>

            <?
            if(isset($_POST['selecDepSubmitted'])) {
                if(isset($_POST['selecDep'])) {
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
                    if(!$update || gettype($update) !== 'boolean') echo '<p class="red-text">Une erreur est survenue</p>';
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
                                    <?
                                    foreach($table as $row): ?>
                                        <tr>
                                            <td><? echo $row["student_name"] . " " . $row["student_firstname"] ?></td>
                                            <td><? echo $row["internshipTeacher"] ?></td>
                                            <td>
                                            <script>
                                                window.addEventListener('load', async function () {
                                                    let addressTeach = [];

                                                    const geocodeAddresses = async () => {
                                                        const checkGoogleMaps = setInterval(async () => {
                                                            if (typeof google !== 'undefined' && google.maps && google.maps.Geocoder) {
                                                                clearInterval(checkGoogleMaps);

                                                                const addressTeachPromises = [
                                                                    <? foreach($_SESSION['address'] as $address): ?>
                                                                    geocodeAddress(<?= '"' . str_replace('_', "'", $address['address']) . '"' ?>),
                                                                    <? endforeach; ?>
                                                                ];

                                                                addressTeach = await Promise.all(addressTeachPromises);

                                                                const addressStudent = await geocodeAddress(<?= '"' . str_replace('_', "'", $row["address"]) . '"' ?>);

                                                                const durationPromises = addressTeach.map(teacherAddress => calculateDistance(addressStudent, teacherAddress));
                                                                const durations = await Promise.all(durationPromises);

                                                                const durationValues = durations.map(duration => duration.value);
                                                                const durationMin = Math.min(...durationValues);

                                                                if (durationMin) {
                                                                    await fetch(window.location.href, {
                                                                        method: 'POST',
                                                                        headers: {
                                                                            'Content-Type': 'application/x-www-form-urlencoded',
                                                                        },
                                                                        body: `shortest_duration[]=${durationMin}`
                                                                    });
                                                                }
                                                            } else {
                                                                console.log('Google Maps API is not yet loaded.');
                                                            }
                                                        }, 100);
                                                    };

                                                    await geocodeAddresses();
                                                });
                                            </script>
                                            </td>
                                            <td> <? echo str_replace('_', ' ', $row["internship_subject"]) ?> </td>
                                            <td> <? echo str_replace('_', ' ', $row["company_name"]) ?> </td>
                                            <td> <? echo "<strong>" . round($row['relevance'], 2) . "</strong>/5" ?> </td>
                                            <td>
                                                <label>
                                                    <input type="checkbox" name="selecStudent[]" class="center-align filled-in" value="<?= $row['student_number'] ?>" <? if($row['requested']): ?> checked="checked" <? endif; ?> />
                                                    <span></span>
                                                </label>
                                            </td>
                                        </tr>
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

