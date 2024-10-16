<?php
namespace Blog\Views;

class Homepage {

    /**
     * Vue de la homepage
     * @return void
     */
    public function showView($estStagiare) {
        ?>
        <main>
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

            <?php
            if ($estStagiare) { ?>
                <div id="map"></div>
            <?php } else { ?>
                <p>Cet étudiant n'a pas de stage ...</p>
            <?php  } ?>

            <div class="row"></div>

            <script>
                const teacherAddress = "<?php echo isset($_SESSION['address']) ? $_SESSION['address'] : 'Aix-En-Provence'; ?>";
                const companyAddress = "<?php echo isset($_SESSION['selected_student']['address']) ? $_SESSION['selected_student']['address'] : 'Marseille'; ?>";
            </script>
        </main>
        <?php
    }
}

