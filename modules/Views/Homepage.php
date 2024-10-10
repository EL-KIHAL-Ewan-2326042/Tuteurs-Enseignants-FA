<?php
namespace Blog\Views;

require 'vendor/autoload.php';

use yidas\googleMaps\Client;

class Homepage {
    /**
     * Vue de la homepage
     * @return void
     */
    public function showView() {
        $coeffDuree = 50;
        $coeffPert = 50;
        $coeffAsso = 50;

        $gmaps = new \yidas\googleMaps\Client(['key'=>'AIzaSyB5ZrbmaUNAlL8bVUXigjaYWzb3Qi9f_j4']);
        $adresseEt = "All. des Platanes, 13590 Meyreuil";
        $adresseProf = "L'Escale , Piscine & Spa, 52 Rue des Myosotis, 13590 Meyreuil";
        $resultDirections = $gmaps->directions($adresseEt, $adresseProf, [
            'mode' => 'driving',
            'departure_time' => time(),
        ]);
        foreach($resultDirections as $resultDirection) {
            echo $resultDirection;
        }
        $duree = (int)($coeffDuree/$resultDirections['duration_in_traffic']);

        $discipline = "exploitation de base de données";
        $arrayDiscip = explode(' ', $discipline);

        $motsClesDiscipline = "base de données;php;sql";
        $arrayMCDiscip = explode(';', $motsClesDiscipline);

        // $sujet = <result requete sql>['sujet'];
        // $arraySujet = explode(' ', $sujet);

        $tags = "base de données;algo;feur";
        $arrayTags = explode(';', $tags);

        $dejaAsso = $coeffAsso*3;

        $points = 0;

        foreach($arrayTags as $tag) {
            foreach($arrayDiscip as $discip) {
                if ($tag == $discip) $points += 5;
            }
            foreach($arrayMCDiscip as $mCDiscip) {
                if ($mCDiscip == $discip) ++$points;
            }
        }

        $points = $points*$coeffPert + $duree + $dejaAsso;
        $points = $points/($coeffDuree+$coeffPert+$coeffAsso);

        echo $duree;
        echo $dejaAsso;
        echo $points;
        ?>
        <main>
            <p>Ceci est un test</p>
        </main>
        <?php
    }
}