<?php
namespace Blog\Views;

class Homepage {

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView() {
        ?>
        <main>
            <script>
                <?php
                    if (isset($_SESSION['selected_student'])) { ?>
                    let companyAddress = <?php echo json_encode($_SESSION['selected_student']['address']); ?>;
                    let teacherAddress = <?php echo json_encode($_SESSION['address']); ?>;
                <?php
                }
                ?>
            </script>

            <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

            <div class="card-panel white">
                <form id="searchForm" onsubmit="return false;" method="POST">
                    <label for="search">Rechercher un étudiant:</label>
                    <input type="text" id="search" name="search" autocomplete="off" maxlength="50" required>
                    <div id="searchResults"></div>
                </form>
            </div>
            <div class="center">
                <?php if (isset($_SESSION['selected_student'])) {
                    echo '<h4 class="left-align"> Résultat pour: ' . $_SESSION['selected_student']['firstName'] . ' ' .  $_SESSION['selected_student']['lastName'] . '</h4>';
                }
                ?>
            </div>

            <div id="map"></div>
            <div class="row"></div>

            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCBS2OwTaG2rfupX3wA-DlTbsBEG9yDVKk&callback=initMap" async defer></script>
        </main>
        <?php
    }
}

