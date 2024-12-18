document.addEventListener('DOMContentLoaded', function() {
    var elems = document.querySelectorAll('select');
    var instances = M.FormSelect.init(elems);

    // Attendre un moment avant de mettre à jour les options
    setTimeout(function() {
        updateSelectOptions();
    }, 500); // Attendez 500ms avant de mettre à jour
});

function updateSelectOptions() {
    var selectElements = document.querySelectorAll('select');
    selectElements.forEach(select => {
        var selectInstance = M.FormSelect.getInstance(select);
        if (selectInstance) {
            selectInstance.update();
        }
    });
}
