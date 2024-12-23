/**
 * Dès le chargement de la page on associe un listener à l'input 'newMaxNumber'
 * @type {HTMLElement}
 */
document.addEventListener('DOMContentLoaded', function() {
    const maxNumberInput = document.getElementById("newMaxNumber");
    maxNumberInput.addEventListener("keyup", inputBoundaries);
    maxNumberInput.addEventListener("keypress", inputBoundaries);
    function inputBoundaries() {
        if (Number(maxNumberInput.value) < Number(maxNumberInput.min)) maxNumberInput.value = maxNumberInput.min;
        if (Number(maxNumberInput.value) > Number(maxNumberInput.max)) maxNumberInput.value = maxNumberInput.max;
    }
});

/**
 * Tri du tableau et pagination
 */
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById("account-table") === null) {
        return;
    }

    const rowsPerPageDropdown = document.getElementById('rows-per-page');
    let rowsPerPage = parseInt(rowsPerPageDropdown.value); // Set default to 10

    let rows = document.querySelectorAll('.account-row');
    let totalRows = rows.length;
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    let currentPage = sessionStorage.getItem('page') ? Number(sessionStorage.getItem('page')) : 1;

    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const firstButton = document.getElementById('first-page');
    const lastButton = document.getElementById('last-page');
    const pageNumbersContainer = document.getElementById('page-numbers');

    if (document.getElementById("account-table").rows.length > 2) {
        if (!(sessionStorage.getItem('columnNumber') && sessionStorage.getItem('direction'))) {
            sessionStorage.setItem('columnNumber', "0");
            sessionStorage.setItem('direction', "asc");
        }
        sortTable(Number(sessionStorage.getItem('columnNumber')), true);

        for (let i = 0; i < document.getElementById("account-table").rows[0].cells.length; ++i) {
            document.getElementById("account-table").rows[0].getElementsByTagName("TH")[i].addEventListener('click', () => {
                sortTable(i);
            });
        }
    }

    /**
     * Trie la table prenant pour id "account-table"
     * @param n numéro désignant la colonne par laquelle on trie le tableau
     * @param firstLoad booléen indiquant si cet appel est le premier depuis le chargement de la page
     */
    function sortTable(n, firstLoad = false) {
        let dir, rows, switching, i, x, y, shouldSwitch, column;
        const table = document.getElementById("account-table");
        switching = true;

        if (!firstLoad) {
            if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") dir = "desc";
            else dir = "asc";
        } else dir = sessionStorage.getItem('direction');

        while (switching) {
            switching = false;
            rows = table.rows;
            for (i = 1; i < (rows.length - 1); ++i) {
                shouldSwitch = false;
                x = rows[i].getElementsByTagName("TD")[n];
                y = rows[i + 1].getElementsByTagName("TD")[n];
                if (dir === "asc") {
                    if ((n < 7 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                        || (n === 7 && Number(x.innerHTML.substring(1, x.innerHTML.indexOf(' '))) > Number(y.innerHTML.substring(1, y.innerHTML.indexOf(' '))))) {
                        shouldSwitch = true;
                        break;
                    }
                } else if (dir === "desc") {
                    if ((n < 7 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                        || (n === 7 && Number(x.innerHTML.substring(1, x.innerHTML.indexOf(' '))) < Number(y.innerHTML.substring(1, y.innerHTML.indexOf(' '))))) {
                        shouldSwitch = true;
                        break;
                    }
                }
            }
            if (shouldSwitch) {
                rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
                switching = true;
            }
        }
        for (i = 0; i < rows[0].cells.length; ++i) {
            column = rows[0].getElementsByTagName("TH")[i].innerHTML;
            if (column.substring(column.length-1) === "▲" || column.substring(column.length-1) === "▼") table.rows[0].getElementsByTagName("TH")[i].innerHTML = column.substring(0, column.length-2);
            if (i === n) {
                if (dir === "asc") table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                else table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
            }
        }

        sessionStorage.setItem('columnNumber', n);
        sessionStorage.setItem('direction', dir);
        showPage(currentPage, sessionStorage.getItem('direction'));
    }

    function showPage(page) {
        if (page < 1 || page > totalPages) return;

        rows = document.querySelectorAll('.account-row');

        currentPage = page;
        updatePageNumbers();

        rows.forEach(row => row.style.display = 'none');

        const start = (currentPage - 1) * rowsPerPage;
        const end = currentPage * rowsPerPage;
        const visibleRows = Array.from(rows).slice(start, end);
        visibleRows.forEach(row => row.style.display = '');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
        firstButton.disabled = currentPage === 1;
        lastButton.disabled = currentPage === totalPages;

        sessionStorage.setItem('page', currentPage);
    }

    function updatePageNumbers() {
        pageNumbersContainer.innerHTML = '';

        const maxVisiblePages = 5;
        const halfWindow = Math.floor(maxVisiblePages / 2);
        let startPage = Math.max(currentPage - halfWindow, 1);
        let endPage = Math.min(currentPage + halfWindow, totalPages);

        if (endPage - startPage + 1 < maxVisiblePages) {
            if (startPage === 1) {
                endPage = Math.min(startPage + maxVisiblePages - 1, totalPages);
            } else if (endPage === totalPages) {
                startPage = Math.max(endPage - maxVisiblePages + 1, 1);
            }
        }

        if (startPage > 1) {
            createPageButton(1);
            if (startPage > 2) {
                addEllipsis();
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            createPageButton(i, i === currentPage);
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                addEllipsis();
            }
            createPageButton(totalPages);
        }
    }

    function createPageButton(page, isActive = false) {
        const pageNumberButton = document.createElement('button');
        pageNumberButton.textContent = page;
        pageNumberButton.classList.add('waves-effect', 'waves-light', 'btn');
        pageNumberButton.classList.add('page-number');
        pageNumberButton.disabled = isActive;
        pageNumberButton.addEventListener('click', () => showPage(page));
        pageNumbersContainer.appendChild(pageNumberButton);
    }

    function addEllipsis() {
        const ellipsis = document.createElement('span');
        ellipsis.textContent = '...';
        ellipsis.classList.add('pagination-ellipsis');
        pageNumbersContainer.appendChild(ellipsis);
    }

    rowsPerPageDropdown.addEventListener('change', function () {
        rowsPerPage = parseInt(rowsPerPageDropdown.value);
        totalPages = Math.ceil(rows.length / rowsPerPage);
        currentPage = 1;
        showPage(currentPage);
    });

    firstButton.addEventListener('click', () => showPage(1));
    lastButton.addEventListener('click', () => showPage(totalPages));
    prevButton.addEventListener('click', () => showPage(currentPage - 1));
    nextButton.addEventListener('click', () => showPage(currentPage + 1));

    window.addEventListener('resize', function () {
        totalRows = rows.length;
        totalPages = Math.ceil(totalRows / rowsPerPage);
        if (currentPage > totalPages) currentPage = totalPages;
        showPage(currentPage);
    });

    showPage(currentPage);
});