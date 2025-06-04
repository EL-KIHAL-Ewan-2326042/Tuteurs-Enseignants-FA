<?php

namespace Blog\Views\components;

class DispatcherViewStage
{
    /**
     * Affiche la vue d’un stage sous forme de DataTable, incluant la colonne 'Associer'
     *
     * @param string $idStage Identifiant du stage sélectionné
     *
     * @return void
     */
    public static function render(string $idStage): void
    {
        $headers = ['Associer', 'Professeur', 'Position', 'Score', 'Discipline', 'Entreprise', 'Historique', 'Adresse', 'Adresse du Stage'];
        $jsColumns = [
            ['data' => 'associate'],
            ['data' => 'prof'],
            ['data' => 'distance'],
            ['data' => 'score'],
            ['data' => 'discipline'],
            ['data' => 'entreprise'],
            ['data' => 'history'],
            ['data' => 'teacher_address'],
            ['data' => 'internship_address'],
        ];

        Table::render(
            'viewStage',
            $headers,
            $jsColumns,
            '/api/datatable/stage/' . urlencode(trim($idStage)),
            false
        );

        // Ajout du bouton Valider
        echo '<form action="./dispatcher" method="post">';
        echo '<input type="hidden" name="internship_id" value="' . htmlspecialchars($idStage) . '">';
        echo '<button type="submit" name="associateTeachers" class="btn-send">Valider</button>';
        echo '</form>';
    }
}
