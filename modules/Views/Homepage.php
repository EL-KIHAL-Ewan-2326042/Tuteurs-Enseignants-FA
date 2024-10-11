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
            <div id="output"></div>
            <div id="map"></div>

            <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCBS2OwTaG2rfupX3wA-DlTbsBEG9yDVKk&callback=initMap" async defer></script>
        </main>
        <?php
    }
}