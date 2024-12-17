/**
 * Partie 1
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
 * Fetch results via AJAX from the server.
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
 * Display the results from the AJAX response.
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
 * Partie 2
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