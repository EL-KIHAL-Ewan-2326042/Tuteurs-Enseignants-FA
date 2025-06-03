<?php

namespace Blog\Views\components;

class ViewStage
{
    /**
     * Affiche la vue d’un stage sous forme de DataTable
     *
     * @param string $idStage Identifiant du stage sélectionné
     *
     * @return void
     */
    public static function render(string $idStage): void
    {
        $headers = ['Professeur', 'Position', 'Discipline', 'Score', 'Entreprise', 'Historique'];

        $jsColumns = [
            ['data' => 'prof'],
            ['data' => 'distance'],
            ['data' => 'discipline'],
            ['data' => 'score'],
            ['data' => 'entreprise'],
            ['data' => 'history'],
        ];

        Table::render('viewStage', $headers, $jsColumns, '/api/datatable/stage/' . urlencode(trim($idStage)), true);
    }
}
