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
        $headers = ['Professeur', 'Historique', 'Position', 'Discipline', 'Score', 'Entreprise'];

        $jsColumns = [
            ['data' => 'prof'],
            ['data' => 'history'],
            ['data' => 'distance'],
            ['data' => 'discipline'],
            ['data' => 'score'],
            ['data' => 'entreprise'],
        ];

        Table::render('viewStage', $headers, $jsColumns, '/api/datatable/stage/' . urlencode(trim($idStage)), false);
    }
}
