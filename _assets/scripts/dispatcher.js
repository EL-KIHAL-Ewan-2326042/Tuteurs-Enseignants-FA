/**
 * Partie 1 Barre de recherche association directe
 */
document.addEventListener('DOMContentLoaded', function () {
    const elems = document.querySelectorAll('select');
    const instances = M.FormSelect.init(elems);

    const searchInputTeacher = document.getElementById('searchTeacher');
    const searchInputInternship = document.getElementById('searchInternship');

    const searchResults = document.getElementById('searchResults');
    searchResults.innerHTML = '<p></p>';

    searchInputTeacher.addEventListener('input', function () {
        const searchTerm = searchInputTeacher.value.trim();

        if (searchTerm.length > 0) {
            fetchResults(searchTerm, 'searchTeacher');
        } else {
            searchResults.innerHTML = '<p></p>';
        }
    });

    searchInputInternship.addEventListener('input', function () {
        const searchTerm = searchInputInternship.value.trim();

        if (searchTerm.length > 0) {
            fetchResults(searchTerm, 'searchInternship');
        } else {
            searchResults.innerHTML = '<p>Barre de recherche vide</p>';
        }
    });
});

/**
 * Prendre les requetes avec une requête AJAX
 * @param query The search query.
 * @param searchType
 */
function fetchResults(query, searchType) {
    fetch(window.location.href, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            action: 'search',
            searchType: searchType,
            search: query,
        }),
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            displayResults(data, searchType);
        })
        .catch(error => {
            console.error('Erreur fetch resultats:', error);
            searchResults.innerHTML = '<p>Erreur lors de la récupération des résultats</p>';
        });
}


/**
 * Afficher les résultats avec la requête AJAX
 * @param data The data received from the server.
 * @param action The action used to determine how to display the results.
 */
function displayResults(data, action) {
    if (searchResults) {
        searchResults.innerHTML = '';
    }

    if (!data || data.length === 0) {
        searchResults.innerHTML = '<p>Aucun résultat trouvé</p>';
        return;
    }

    const ul = document.createElement('ul');
    data.forEach(item => {
        const li = document.createElement('li');
        const p = document.createElement('p');

        if (action === 'searchTeacher') {
            p.textContent = `${item.teacher_name} ${item.teacher_firstname} (ID: ${item.id_teacher})`;
        } else if (action === 'searchInternship') {
            p.textContent = item.company_name
                ? `${item.company_name}: ${item.internship_identifier} - ${item.student_name} ${item.student_firstname}`
                : `${item.student_number} - ${item.student_name} ${item.student_firstname}`;
        }

        p.classList.add('left-align');

        li.appendChild(p);
        ul.appendChild(li);
    });
    searchResults.appendChild(ul);
}

/**
 * Partie 2: Coefficients
 */
document.addEventListener('DOMContentLoaded', function () {
    const selects = document.querySelectorAll('select');
    M.FormSelect.init(selects);

    const saveSelector = document.getElementById('save-selector');
    if (saveSelector) {
        saveSelector.addEventListener('change', function () {
            const form = this.closest('form');
            form.submit();
        });
    }

    const checkboxes = document.querySelectorAll('.criteria-checkbox');

    checkboxes.forEach(checkbox => {
        const hiddenInput = document.querySelector(`input[name="is_checked[${checkbox.dataset.coefInputId}]"]`);

        if (checkbox.checked) {
            hiddenInput.value = '1';
        } else {
            hiddenInput.value = '0';
        }

        checkbox.addEventListener('change', function () {
            if (this.checked) {
                hiddenInput.value = '1';
            } else {
                hiddenInput.value = '0';
            }
        });
    });

    document.querySelectorAll('.coef-input').forEach(input => {
        input.addEventListener('input', function () {
            const value = parseInt(this.value);
            if (value > 100) {
                this.value = 100;
            } else if (value < 0) {
                this.value = 0;
            }
        });
    });

    const criteriaCheckboxes = document.querySelectorAll('.criteria-checkbox');
    const errorMessageElement = document.getElementById('checkboxError');
    const button = document.getElementById('generate-btn');

    function validateCheckboxes() {
        const anyChecked = Array.from(criteriaCheckboxes).some(checkbox => checkbox.checked);

        if (!anyChecked) {
            errorMessageElement.textContent = 'Veuillez sélectionner au moins un critère.';
            button.disabled = true;
        } else {
            errorMessageElement.textContent = '';
            if (button.disabled) {
                button.disabled = false;
            }
        }
    }

    criteriaCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', validateCheckboxes);
    });

    const select = document.getElementById('save-selector');
    const saveButton = document.getElementById('save-btn');

    function updateButtonState() {
        if (select.value === 'default') {
            saveButton.disabled = true;
        } else {
            saveButton.disabled = false;
        }
    }

    select.addEventListener('change', updateButtonState);

    updateButtonState();
    validateCheckboxes();
});

document.querySelectorAll('.criteria-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', function () {
        const hiddenInput = document.querySelector(`input[name="is_checked[${this.dataset.coefInputId}]"]`);
        hiddenInput.value = this.checked ? '1' : '0';
    });
});

function showLoading() {
    const loadingSection = document.getElementById('loading-section');
    const formsSection = document.getElementById('forms-section');

    if (loadingSection && formsSection) {
        loadingSection.style.display = 'block';
        formsSection.style.display = 'none';
    }
}

/**
 *  Partie3: Pagination et bouton tout cocher
 */

document.addEventListener('DOMContentLoaded', function () {
    M.Tooltip.init(document.querySelectorAll('.star-rating'), {
        exitDelay: 100,
    });

    // Initialize the Materialize select dropdown
    M.FormSelect.init(document.querySelectorAll('select'));

    const rowsPerPageDropdown = document.getElementById('rows-per-page');
    let rowsPerPage = parseInt(rowsPerPageDropdown.value); // Set default to 10

    const rows = document.querySelectorAll('.dispatch-row');
    let totalRows = rows.length;
    let totalPages = Math.ceil(totalRows / rowsPerPage);
    let currentPage = 1;

    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const firstButton = document.getElementById('first-page');
    const lastButton = document.getElementById('last-page');
    const pageNumbersContainer = document.getElementById('page-numbers');

    function showPage(page) {
        if (page < 1 || page > totalPages) return;

        currentPage = page;
        updatePageNumbers();

        rows.forEach(row => row.style.display = 'none');
        addSelectAllRow();

        const start = (currentPage - 1) * rowsPerPage;
        const end = currentPage * rowsPerPage;
        const visibleRows = Array.from(rows).slice(start, end);
        visibleRows.forEach(row => row.style.display = '');

        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;
        firstButton.disabled = currentPage === 1;
        lastButton.disabled = currentPage === totalPages;
    }

    function updatePageNumbers() {
        pageNumbersContainer.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const pageNumberButton = document.createElement('button');
            pageNumberButton.textContent = i;
            pageNumberButton.classList.add('waves-effect', 'waves-light', 'btn');
            pageNumberButton.classList.add('page-number');
            pageNumberButton.disabled = (i === currentPage);
            pageNumberButton.addEventListener('click', () => showPage(i));

            pageNumbersContainer.appendChild(pageNumberButton);
        }
    }

    function addSelectAllRow() {
        const tbody = document.querySelector('#dispatch-table tbody');
        let selectAllRow = document.querySelector('#select-all-row');

        if (selectAllRow) {
            selectAllRow.remove();
        }

        selectAllRow = document.createElement('tr');
        selectAllRow.id = 'select-all-row';

        selectAllRow.innerHTML = `<td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td></td>
                                                          <td><strong>Tout cocher</strong></td>
                                                          <td><label class="center">
                                                               <input type="checkbox" id="select-all-checkbox" class="center-align filled-in" />
                                                               <span></span>
                                                           </label></td>`;
        tbody.appendChild(selectAllRow);

        const selectAllCheckboxElem = document.getElementById('select-all-checkbox');

        selectAllCheckboxElem.addEventListener('change', function () {
            const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
            visibleRows.forEach(row => {
                const checkbox = row.querySelector('input[type="checkbox"]');
                checkbox.checked = selectAllCheckboxElem.checked;
            });
        });
    }

    function toggleSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all-checkbox');
        const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
        selectAllCheckbox.checked = visibleRows.every(row => row.querySelector('input[type="checkbox"]').checked);
    }

    document.querySelectorAll('.dispatch-row input[type="checkbox"]:not(#select-all-checkbox)').forEach(checkbox => {
        checkbox.addEventListener('change', toggleSelectAllCheckbox);
    });

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

    showPage(1);
});

/**
 * Partie 4: Vue Etudiante
 */
document.addEventListener('DOMContentLoaded', function () {

    function getDictCoef() {
        var jsonString = document.getElementById('dictCoefJson').value;
        return JSON.parse(jsonString);
    }

    function getTeachersForInternship(Internship_identifier) {
        console.log(Internship_identifier, getDictCoef());
        fetch(window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: 'TeachersForinternship',
                Internship_identifier: Internship_identifier,
                dicoCoef: getDictCoef()
            })
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
        })
        .catch(error => {
            console.error('Erreur fetch resultats:', error);
        });
    }

    const tableBody = document.querySelector('#dispatch-table tbody');

    if (tableBody) {
        let lastTapTime = 0;
        let tapTimeout;

        ['click', 'touchstart'].forEach(eventType => {
            tableBody.addEventListener(eventType, function (event) {
                clearTimeout(tapTimeout);

                const currentTime = new Date().getTime();
                const timeSinceLastTap = currentTime - lastTapTime;

                if (timeSinceLastTap > 100 && timeSinceLastTap < 300) {
                    const clickedRow = getClickedRow(event.target);
                    clickedRowIdentifier = clickedRow.getAttribute('data-internship-identifier');
                }

                if (timeSinceLastTap < 300 && timeSinceLastTap > 100) {
                    const currentRow = getClickedRow(event.target);
                    const currentRowIdentifier = currentRow.getAttribute('data-internship-identifier');

                    if (currentRowIdentifier === clickedRowIdentifier) {
                        const [Internship_identifier, studentNumber] = currentRowIdentifier.split('$');

                        getTeachersForInternship(Internship_identifier);
                    }
                } else {
                    lastTapTime = currentTime;
                    tapTimeout = setTimeout(function() {
                        lastTapTime = 0;
                        clickedRowIdentifier = null;
                    }, 500);
                }

                event.preventDefault();
            });
        });

        function getClickedRow(element) {
            while (element && element.tagName !== 'TR') {
                element = element.parentElement;
            }
            return element;
        }

    } else {
        console.error('Table with ID "dispatch-table" not found.');
    }
});

