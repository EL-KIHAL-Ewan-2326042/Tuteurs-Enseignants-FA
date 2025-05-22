/**
 * @fileOverview Définition globale des traductions Datatables
 * @version 1.0
 * @description Ce fichier contient la configuration par défaut pour toutes les instances de Datatables.
 * Il définit les traductions en français pour les messages affichés par Datatables.
 *
 * @license MIT
 */
// Chargement du fichier de traduction depuis le CDN
$.ajax({
    url: 'https://cdn.datatables.net/plug-ins/2.3.1/i18n/fr-FR.json',
    dataType: 'json',
    cache: true,
    success: function(json) {
        // Application des traductions à toutes les instances de DataTables
        $.extend(true, $.fn.dataTable.defaults, {
            language: json
        });
    },
    error: function(xhr, status, error) {
        console.error('Erreur lors du chargement des traductions DataTables:', error);
    }
});