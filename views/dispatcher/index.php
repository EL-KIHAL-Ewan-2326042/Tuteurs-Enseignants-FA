<?php
require_once 'views/components/header.php';
?>

<div class="container">
    <h3 class="center-align">Répartiteur de tuteurs enseignants</h3>

    <?php if (!isset($_POST['action']) || $_POST['action'] !== 'generate') : ?>
        <div class="row">
            <!-- Formulaire des critères -->
            <div class="col s12 m6">
                <?php include 'views/dispatcher/components/criteria_form.php'; ?>
            </div>

            <!-- Formulaire d'association directe -->
            <div class="col s12 m6">
                <?php include 'views/dispatcher/components/direct_association_form.php'; ?>
            </div>
        </div>

        <!-- Section de chargement -->
        <?php include 'views/dispatcher/components/loading_section.php'; ?>

    <?php endif; ?>

    <?php if (isset($_POST['coef']) && isset($_POST['action']) && $_POST['action'] === 'generate') : ?>
        <!-- Section de la carte et du tableau -->
        <div class="row">
            <!-- Carte -->
            <div class="col s12">
                <?php include 'views/dispatcher/components/map_section.php'; ?>
            </div>

            <!-- Tableau des répartitions -->
            <div class="col s12">
                <?php include 'views/dispatcher/components/dispatch_table.php'; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php
require_once 'views/components/footer.php';
?> 