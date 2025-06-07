function adjustValue(id, delta) {
    const input = document.getElementById(id);
    let value = parseInt(input.value) || 0;
    value += delta;
    if (value >= 0 && value <= 100) {
        input.value = value;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    let checkInterval = setInterval(function() {
        const dataTable = $('#homepage-table').DataTable();

        if (dataTable) {
            clearInterval(checkInterval);

            window.homepageTable = dataTable;

            dataTable.on('draw', function() {
                const addressCells = document.querySelectorAll('#homepage-table tbody td:nth-child(7)');
                addressCells.forEach(function(cell) {
                    if (cell.innerHTML && !cell.querySelector('a')) {
                        const address = cell.innerText;
                        cell.innerHTML = `<a class="fs8 cbr" target="_blank"
                                href="https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(address)}">
                                ${address}
                            </a>`;
                    }
                });
            });

            dataTable.draw();
        }
    }, 100);
});