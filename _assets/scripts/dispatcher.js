/**
 * Partie 1
 */
document.addEventListener(
    'DOMContentLoaded', function () {
        const elems = document.querySelectorAll('select');
        const instances = M.FormSelect.init(elems);

        const searchInputTeacher = document.getElementById('searchTeacher');
        const searchInputInternship = document.getElementById('searchInternship');

        const searchResults = document.getElementById('searchResults');

        if (!searchResults) {
            return;
        }
        searchResults.innerHTML = '<p></p>';

        searchInputTeacher.addEventListener(
            'input', function () {
                const searchTerm = searchInputTeacher.value.trim();

                if (searchTerm.length > 0) {
                    fetchResults(searchTerm, 'searchTeacher');
                } else {
                    searchResults.innerHTML = '<p></p>';
                }
            }
        );

        searchInputInternship.addEventListener(
            'input', function () {
                const searchTerm = searchInputInternship.value.trim();

                if (searchTerm.length > 0) {
                    fetchResults(searchTerm, 'searchInternship');
                } else {
                    searchResults.innerHTML = '<p>Barre de recherche vide</p>';
                }
            }
        );
    }
);

/**
 * Prendre les requetes avec une requête AJAX
 *
 * @param query La requête
 * @param searchType Le type de recheche
 */
function fetchResults(query, searchType)
{
    fetch(
        window.location.href, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(
                {
                    action: 'search',
                    searchType: searchType,
                    search: query,
                }
            ),
        }
    ).then(
        response =>
        { if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
        }
            return response.json();
        }
    ).then(
        data =>
        {
            displayResults(data, searchType);
        }
    ).catch(
        error =>
        {
            console.error('Erreur fetch resultats:', error);
            searchResults.innerHTML = '<p>Erreur lors de la récupération des résultats</p>';
        }
    );
}


/**
 * Afficher les résultats avec la requête AJAX
 *
 * @param data The data received from the server.
 * @param action The action used to determine how to display the results.
 */
function displayResults(data, action)
{
    if (searchResults) {
        searchResults.innerHTML = '';
    }

    if (!data || data.length === 0) {
        searchResults.innerHTML = '<p>Aucun résultat trouvé</p>';
        return;
    }

    const ul = document.createElement('ul');
    data.forEach(
        item =>
        {
            const li = document.createElement('li');
            const a = document.createElement('a');
            if (action === 'searchTeacher') {
                a.textContent = `${item.teacher_name} ${item.teacher_firstname} (ID: ${item.id_teacher})`;
                a.href = '#';
                a.addEventListener(
                    'click', (event) =>
                    {
                        event.preventDefault();
                        const inputField = document.getElementById('searchTeacher');
                        if (inputField) {
                            inputField.value = `${item.id_teacher}`;
                        }
                    }
                );
            } else if (action === 'searchInternship') {
                    a.textContent = item.company_name
                    ? `${item.company_name}: ${item.internship_identifier} - ${item.student_name} ${item.student_firstname}`
                    : `${item.student_number} - ${item.student_name} ${item.student_firstname}`;
                    a.href = '#';
                    a.addEventListener(
                        'click', (event) =>
                        {
                            event.preventDefault();
                            const inputField = document.getElementById('searchInternship');
                            if (inputField) {
                                inputField.value = `${item.internship_identifier}`;
                            }
                        }
                    );
            }

                a.classList.add('left-align');
            li.appendChild(a);
            ul.appendChild(li);
        }
    );
    searchResults.appendChild(ul);
}

/**
 * Partie 2: Coefficients
 */
document.addEventListener(
    'DOMContentLoaded', function () {
        const selects = document.querySelectorAll('select');
        M.FormSelect.init(selects);

        const saveSelector = document.getElementById('save-selector');
        if (saveSelector) {
            saveSelector.addEventListener(
                'change', function () {
                    const form = this.closest('form');
                    form.submit();
                }
            );
        }

        const checkboxes = document.querySelectorAll('.criteria-checkbox');

        checkboxes.forEach(
            checkbox =>
            {
                const hiddenInput = document.querySelector(`input[name="is_checked[${checkbox.dataset.coefInputId}]"]`);
                if (checkbox.checked) {
                    hiddenInput.value = '1';
                } else {
                        hiddenInput.value = '0';
                }

                    checkbox.addEventListener(
                        'change', function () {
                            if (this.checked) {
                                hiddenInput.value = '1';
                            } else {
                                hiddenInput.value = '0';
                            }
                        }
                    );
            }
        );

        document.querySelectorAll('.coef-input').forEach(
            input =>
            {
                input.addEventListener(
                    'change', function () {
                            let value = parseInt(this.value);

                        if (isNaN(value) || value < 1) {
                            this.value = 1;
                        } else if (value > 100) {
                            this.value = 100;
                        }
                    }
                );
            }
        );
        const criteriaCheckboxes = document.querySelectorAll('.criteria-checkbox');
        const errorMessageElement = document.getElementById('checkboxError');
        const button = document.getElementById('generate-btn');

        let hasInteracted = false;

        function validateCheckboxes()
        {
            const anyChecked = Array.from(criteriaCheckboxes).some(checkbox => checkbox.checked);

            if (!anyChecked && hasInteracted) {
                errorMessageElement.textContent = 'Veuillez sélectionner au moins un critère.';
                button.disabled = true;
            } else {
                errorMessageElement.textContent = '';
                button.disabled = !anyChecked;
            }
        }

        criteriaCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change', function () {
                        hasInteracted = true;
                        validateCheckboxes();
                    }
                );
            }
        );


        validateCheckboxes();

        const select = document.getElementById('save-selector');
        const saveButton = document.getElementById('save-btn');

        function updateButtonState()
        {
            saveButton.disabled = select.value === 'default';
        }

        if (select) {
            select.addEventListener('change', updateButtonState);
            updateButtonState();
        }

    }
);

document.querySelectorAll('.criteria-checkbox').forEach(
    checkbox =>
    {
        checkbox.addEventListener(
            'change', function () {
                const hiddenInput = document.querySelector(`input[name="is_checked[${this.dataset.coefInputId}]"]`);
                hiddenInput.value = this.checked ? '1' : '0';
            }
        );
    }
);

function showLoading()
{
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

document.addEventListener(
    'DOMContentLoaded', function () {
        M.Tooltip.init(
            document.querySelectorAll('.star-rating'), {
                exitDelay: 100,
            }
        );

        // Initialize the Materialize select dropdown
        M.FormSelect.init(document.querySelectorAll('select'));

        if (document.getElementById("dispatch-table") === null) {
            return;
        }

        const rowsPerPageDropdown = document.getElementById('rows-per-page');
        let rowsPerPage = sessionStorage.getItem("rowsCount") ? Number(sessionStorage.getItem("rowsCount")) : parseInt(rowsPerPageDropdown.value); // Set default to 10
        if (rowsPerPage !== 10) {
            rowsPerPageDropdown.options[rowsPerPage === 20 ? 1 : rowsPerPage === 50 ? 2 : rowsPerPage === 100 ? 3 : 4].selected = true;
        }
        sessionStorage.setItem("rowsCount", String(rowsPerPage));

        let rows = document.querySelectorAll('.dispatch-row');
        let totalRows = rows.length;
        let totalPages = Math.ceil(totalRows / rowsPerPage);
        let currentPage = 1;

        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const firstButton = document.getElementById('first-page');
        const lastButton = document.getElementById('last-page');
        const pageNumbersContainer = document.getElementById('page-numbers');

        sortTable(7);

        for (let i = 0; i < document.getElementById("dispatch-table").rows[0].cells.length; ++i) {
            document.getElementById("dispatch-table").rows[0].getElementsByTagName("TH")[i].addEventListener(
                'click', () =>
                {
                    sortTable(i);
                }
            );
        }

        /**
         * Trie la table prenant pour id "dispatch-table"
         *
         * @param n numéro désignant la colonne par laquelle on trie le tableau
         */
        function sortTable(n)
        {
            let dir, rows, switching, i, x, y, shouldSwitch, column;
            const table = document.getElementById("dispatch-table");
            switching = true;

            if (table.rows[0].getElementsByTagName("TH")[n].innerHTML.substring(table.rows[0].getElementsByTagName("TH")[n].innerHTML.length - 1) === "▲") { dir = "desc";
            } else { dir = "asc";
            }

            while (switching) {
                switching = false;
                rows = table.rows;
                for (i = 1; i < (rows.length - 1); ++i) {
                    shouldSwitch = false;
                    if (rows[i].id === 'select-all-row'
                        || rows[i + 1].id === 'select-all-row'
                    ) {
                        continue;
                    }

                    x = rows[i].getElementsByTagName("TD")[n];
                    y = rows[i + 1].getElementsByTagName("TD")[n];

                    if (dir === "asc") {
                        if ((n < 7 && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase())
                            || (n === 7 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) < Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 8 && x.getElementsByTagName("INPUT")[0].checked < y.getElementsByTagName("INPUT")[0].checked)
                        ) {
                            shouldSwitch = true;
                            break;
                        }
                    } else if (dir === "desc") {
                        if ((n < 7 && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase())
                            || (n === 7 && Number(x.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')) > Number(y.getElementsByTagName("DIV")[0].getAttribute('data-tooltip')))
                            || (n === 8 && x.getElementsByTagName("INPUT")[0].checked > y.getElementsByTagName("INPUT")[0].checked)
                        ) {
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
                if (column.substring(column.length-1) === "▲" || column.substring(column.length-1) === "▼") { table.rows[0].getElementsByTagName("TH")[i].innerHTML = column.substring(0, column.length-2);
                }
                if (i === n) {
                    if (dir === "asc") { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▲";
                    } else { table.rows[0].getElementsByTagName("TH")[i].innerHTML += " ▼";
                    }
                }
            }

            showPage(currentPage);
        }

        function showPage(page)
        {
            if (page < 1 || page > totalPages) { return;
            }

            rows = document.querySelectorAll('.dispatch-row');

            currentPage = page;
            updatePageNumbers();

            rows.forEach(row => (row.style.display = 'none'));
            addSelectAllRow();

            const start = (currentPage - 1) * rowsPerPage;
            const end = currentPage * rowsPerPage;
            const visibleRows = Array.from(rows).slice(start, end);
            visibleRows.forEach(row => (row.style.display = ''));

            prevButton.disabled = currentPage === 1;
            nextButton.disabled = currentPage === totalPages;
            firstButton.disabled = currentPage === 1;
            lastButton.disabled = currentPage === totalPages;
        }

        function updatePageNumbers()
        {
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

        function createPageButton(page, isActive = false)
        {
            const pageNumberButton = document.createElement('button');
            pageNumberButton.textContent = page;
            pageNumberButton.classList.add('waves-effect', 'waves-light', 'btn');
            pageNumberButton.classList.add('page-number');
            pageNumberButton.disabled = isActive;
            pageNumberButton.addEventListener('click', () => showPage(page));
            pageNumbersContainer.appendChild(pageNumberButton);
        }

        function addEllipsis()
        {
            const ellipsis = document.createElement('span');
            ellipsis.textContent = '...';
            ellipsis.classList.add('pagination-ellipsis');
            pageNumbersContainer.appendChild(ellipsis);
        }

        function addSelectAllRow()
        {
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
                                                          <td>
                                                              <p>
                                                                  <label class="center">
                                                                       <input type="checkbox" id="select-all-checkbox" class="center-align filled-in" />
                                                                       <span data-type="checkbox">Tout cocher</span>
                                                                   </label>
                                                              </p>
                                                           </td>`;
            tbody.appendChild(selectAllRow);

            const selectAllCheckboxElem = document.getElementById('select-all-checkbox');

            selectAllCheckboxElem.addEventListener(
                'change', function () {
                    const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
                    visibleRows.forEach(
                        row =>
                        {
                            const checkbox = row.querySelector('input[type="checkbox"]');
                            checkbox.checked = selectAllCheckboxElem.checked;
                        }
                    );
                }
            );
        }

        function toggleSelectAllCheckbox()
        {
            const selectAllCheckbox = document.getElementById('select-all-checkbox');
            const visibleRows = Array.from(rows).slice((currentPage - 1) * rowsPerPage, currentPage * rowsPerPage);
            selectAllCheckbox.checked = visibleRows.every(row => row.querySelector('input[type="checkbox"]').checked);
        }

        document.querySelectorAll('.dispatch-row input[type="checkbox"]:not(#select-all-checkbox)').forEach(
            checkbox =>
            {
                checkbox.addEventListener('change', toggleSelectAllCheckbox);
            }
        );

        rowsPerPageDropdown.addEventListener(
            'change', function () {
                rowsPerPage = parseInt(rowsPerPageDropdown.value);
                sessionStorage.setItem("rowsCount", String(rowsPerPage));
                totalPages = Math.ceil(rows.length / rowsPerPage);
                currentPage = 1;
                showPage(currentPage);
            }
        );

        firstButton.addEventListener('click', () => showPage(1));
        lastButton.addEventListener('click', () => showPage(totalPages));
        prevButton.addEventListener('click', () => showPage(currentPage - 1));
        nextButton.addEventListener('click', () => showPage(currentPage + 1));

        window.addEventListener(
            'resize', function () {
                totalRows = rows.length;
                totalPages = Math.ceil(totalRows / rowsPerPage);
                if (currentPage > totalPages) { currentPage = totalPages;
                }
                showPage(currentPage);
            }
        );

        showPage(currentPage);
    }
);

/**
 * Partie 4: Vue Etudiante
 */
document.addEventListener(
    'DOMContentLoaded', function () {

        function getDictCoef()
        {
            var jsonString = document.getElementById('dictCoefJson').value;
            try {
                return JSON.parse(jsonString);
            } catch (e) {
                console.error("Invalid JSON string in dictCoefJson:", e);
                return {};
            }
        }

        function getTeachersForInternship(Internship_identifier)
        {
            fetch(
                window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(
                        {
                            action: 'TeachersForinternship',
                            Internship_identifier: Internship_identifier,
                            dicoCoef: JSON.stringify(getDictCoef())
                        }
                    )
                }
            )
                .then(
                    response =>
                    {
                        if (!response.ok) {
                            return response.text().then(
                                errorText =>
                                {
                                    console.error('Fetch error response:', errorText);
                                    throw new Error('Network response was not ok');
                                }
                            );
                        }
                        return response.json();
                    }
                )
                .then(
                    async data =>
                    {
                        createNewTable(data);
                        createTeacherMarkers(data);
                    }
                )
                .catch(
                    error =>
                    {
                        console.error('Fetch error:', error);
                    }
                );
        }

        const tableBody = document.querySelector('#dispatch-table tbody');

        if (tableBody) {
            ['click', 'touchstart'].forEach(
                function (eventType) {
                    tableBody.addEventListener(
                        eventType, function (event) {
                            if (event.target.tagName === 'I' 
                                && event.target.classList.contains('material-icons')
                            ) {
                                const clickedRow = getClickedRow(event.target);
                                if (!clickedRow) {
                                    return;
                                }

                                const clickedRowIdentifier =
                                clickedRow.getAttribute('data-internship-identifier');
                                const [
                                internshipIdentifier,
                                idTeacher,
                                internshipAddress
                                ] = clickedRowIdentifier.split('$');

                                if (event.target.textContent === 'face') {
                                    getTeachersForInternship(internshipIdentifier);
                                    event.preventDefault();
                                } else if (event.target.textContent === 'map') {
                                    updateMap(internshipAddress, idTeacher).then();
                                    event.preventDefault();
                                }
                            }
                        }
                    );
                }
            );
        }

        function getClickedRow(element)
        {
            while (element && element.tagName !== 'TR') {
                element = element.parentElement;
            }
            return element;
        }

        async function createTeacherMarkers(data)
        {
            let minDistance;
            let closestTeacherAddress;
            for (const row of data) {
                const teacherAddresses = await getTeacherAddresses(row.id_teacher);
                const internshipLocation = await geocodeAddress(row.address);
                minDistance = Infinity;

                if (Array.isArray(teacherAddresses)) {
                    for (const item of teacherAddresses) {
                        const location = await geocodeAddress(item.address);
                        const distance = await calculateDistanceOnly(internshipLocation, location);

                        if (distance < minDistance) {
                            minDistance = distance;
                            closestTeacherAddress = location;
                        }
                    }
                } else {
                    closestTeacherAddress = await geocodeAddress(teacherAddresses.address);
                }
                const marker = new ol.Overlay(
                    {
                        position: ol.proj.fromLonLat([closestTeacherAddress.lon, closestTeacherAddress.lat]),
                        element: createMarkerElement(row.teacher_name),
                    }
                );
                const markerFeature = new ol.Feature(
                    {
                        geometry: new ol.geom.Point(
                            ol.proj.fromLonLat([closestTeacherAddress.lon, closestTeacherAddress.lat])
                        ),
                    name: row.teacher_name,
                    }
                );

                markerSource.addFeature(markerFeature);
            }
        }
        async function createNewTable(data)
        {
            const container = document.querySelector('.dispatch-table-wrapper');

            const existingTable = document.getElementById('student-dispatch-table');
            const existingHeader = document.getElementById('student-dispatch-header');

            if (existingTable) {
                existingTable.remove();
            }
            if (existingHeader) {
                existingHeader.remove();
            }

            const header = document.createElement('h3');
            header.id = 'student-dispatch-header';
            header.textContent = `Résultat pour ${data[0].student_firstname} ${data[0].student_name}`;
            header.className = 'center-align flow-text';
            container.appendChild(header);


            const loadingContainer = document.createElement('div');
            loadingContainer.className = 'center-align loading-indicator';
            loadingContainer.innerHTML = `
            <p style="font-size: 24px;">Chargement en cours, veuillez patienter...</p>
            <div class="progress">
            <div class="indeterminate"></div>
            </div>`;
            container.appendChild(loadingContainer);

            loadingContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });

            const newTable = document.createElement('table');
            newTable.className = 'highlight centered responsive-table';
            newTable.id = 'student-dispatch-table';

            const thead = document.createElement('thead');
            thead.innerHTML = `
            <tr>
            <th>Enseignant</th>
            <th>Historique</th>
            <th>Position</th>
            <th>Discipline</th>
            <th>Entreprise</th>
            <th>Score</th>
            <th>Associer</th>
            </tr>`;
            newTable.appendChild(thead);

            const tbody = document.createElement('tbody');

            for (const row of data) {
                const tr = document.createElement('tr');
                row.distance = await getDistance(row.internship_identifier, row.id_teacher);
                row.discipline = await getDisciplines(row.id_teacher);
                let studentHistory = await getStudentHistory(row.student_number);

                if (studentHistory) {
                    row.date_experience = studentHistory;
                }
                else {
                    row.date_experience = '❌';
                }

                tr.className = 'dispatch-row';
                tr.dataset.internshipIdentifier = `${row.internship_identifier}$${row.id_teacher}`;

                tr.innerHTML = `
                <td>${row.teacher_firstname} ${row.teacher_name} (${row.id_teacher})</td>
                <td>${row.date_experience || 'dd/mm/yyyy'}</td>
                <td>${row.distance} min</td>
                <td>${row.discipline}</td>
                <td>${row.company_name}</td>
                <td>
                <div class="star-rating" data-tooltip="${row.score}" data-position="top">
                    ${renderStarsJS(row.score)}
                </div>
                </td>
                <td>
                <p>
                    <label class="center">
                        <input type="checkbox" class="dispatch-checkbox center-align filled-in" name="listTupleAssociate[]" 
                            value="${row.id_teacher}$${row.internship_identifier}$${row.score}" />
                        <span data-type="checkbox">Cocher</span>
                    </label>
                </p>
                </td>`;

                tbody.appendChild(tr);
            }

            newTable.appendChild(tbody);
            container.appendChild(newTable);

            loadingContainer.remove();
        }

        function renderStarsJS(score)
        {
            const fullStars = Math.floor(score);
            const decimalPart = score - fullStars;
            const halfStars = Math.abs(decimalPart - 0.5) <= 0.1 ? 1 : 0;
            const emptyStars = 5 - fullStars - halfStars;

            let stars = '';

            for (let i = 0; i < fullStars; i++) {
                stars += '<span class="filled"></span>';
            }

            if (halfStars) {
                stars += '<span class="half"></span>';
            }

            for (let i = 0; i < emptyStars; i++) {
                stars += '<span class="empty"></span>';
            }

            return stars;
        }

    }
);


/**
 * Partie 5: map OSM 
**/

let map, routeLayer, companyMarker, teacherMarker;

/**
 * Initialise la carte, centree sur la France
 *
 * @returns {Promise<void>}
 */

async function initMap()
{
    const mapElement = document.getElementById("map");

    if (!mapElement) { return;
    }

    try {
        const franceCenter = ol.proj.fromLonLat([2.337, 46.227]);

        map = new ol.Map(
            {
                target: mapElement,
                layers: [
                new ol.layer.Tile(
                    {
                        source: new ol.source.OSM(),
                    }
                ),
            ],
            view: new ol.View(
                {
                    center: franceCenter,
                    zoom: 5,
                }
            ),
            }
        );

        markerSource = new ol.source.Vector();

        clusterSource = new ol.source.Cluster(
            {
                distance: 25,
                source: markerSource,
            }
        );

        clusterLayer = new ol.layer.Vector(
            {
                source: clusterSource,
                style: function (feature) {
                    const size = feature.get("features").length;
                    const color = size > 1 ? "red" : "blue";
                    return new ol.style.Style(
                        {
                            image: new ol.style.Circle(
                                {
                                    radius: size > 1 ? 30 : 20,
                                    fill: new ol.style.Fill({ color }),
                                    stroke: new ol.style.Stroke({ color: "white", width: 2 }),
                                }
                            ),
                        text: new ol.style.Text(
                            {
                                text: size > 1 ? size.toString() : feature.get("features")[0].get("name"),
                                fill: new ol.style.Fill({ color: "white" }),
                                stroke: new ol.style.Stroke({ color: "black", width: 2 }),
                                font: "12px Arial",
                            }
                        ),
                        }
                    );
                },
            }
        );
        map.addLayer(clusterLayer);
    } catch (error) {
        console.error("Error initializing map:", error);
    }
}



/**
 * Obtenir les différentes adresses d'un professeur
 *
 * @param Id_teacher Identifiant du professeur
 *
 * @returns {Promise<Array<string>>}
 */
async function getTeacherAddresses(Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getTeacherAddresses",
                        Id_teacher: Id_teacher,
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Requête au serveur pour avoir la distance minimale entre un prof et un stage
 *
 * @param Internship_identifier
 * @param Id_teacher
 *
 * @returns {Promise<number|*[]>}
 */
async function getDistance(Internship_identifier, Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getDistance",
                        Internship_identifier: Internship_identifier,
                        Id_teacher: Id_teacher
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        const data = await response.json();
        return parseInt(data, 10);
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Requête au serveur pour avoir les disciplines d'un professeur
 *
 * @param Id_teacher
 *
 * @returns {Promise<number|*[]>}
 */
async function getDisciplines(Id_teacher)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getDisciplines",
                        Id_teacher: Id_teacher
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return await response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

async function getStudentHistory(Student_number)
{
    try {
        const response = await fetch(
            window.location.href, {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded",
                },
                body: new URLSearchParams(
                    {
                        action: "getHistory",
                        Student_number: Student_number
                    }
                ),
            }
        );

        if (!response.ok) {
            const errorText = await response.text();
            console.error("Fetch error response:", errorText);
        }

        return await response.json();
    } catch (error) {
        console.error("Fetch error:", error);
        return [];
    }
}

/**
 * Mise à jour de la carte avec deux nouvelles adresses
 *
 * @param {string} InternshipAddress Première adresse
 * @param {string} Id_teacher Deuxième adresse
 */
async function updateMap(InternshipAddress, Id_teacher)
{
    if (!map) {
        console.error("La carte n'est pas initialisée. Appelez initMap d'abord.");
        return;
    }

    try {
        const teacherAddresses = await getTeacherAddresses(Id_teacher);
        const internshipLocation = await geocodeAddress(InternshipAddress);

        let closestTeacherAddress = null;
        let minDistance = Infinity;

        if (Array.isArray(teacherAddresses)) {
            for (const teacher of teacherAddresses) {
                const location = await geocodeAddress(teacher.address);
                const distance = await calculateDistanceOnly(internshipLocation, location);

                if (distance < minDistance) {
                    minDistance = distance;
                    closestTeacherAddress = location;
                }
            }
        } else {
            closestTeacherAddress = await geocodeAddress(teacherAddresses.address);
        }


        placeMarker(internshipLocation, "Entreprise", true);

        centerMap(internshipLocation, closestTeacherAddress);
    } catch (error) {
        console.error("Erreur lors de la mise à jour de la carte :", error);
    }
}

function centerMap(location1, location2)
{
    const view = map.getView();
    view.setCenter(
        ol.proj.fromLonLat(
            [
            (location1.lon + location2.lon) / 2,
            (location1.lat + location2.lat) / 2,
            ]
        )
    );
}

/**
 * Géocode une adresse
 *
 * @param {string} address Adresse à géocoder
 *
 * @returns {Promise<Object>} Localisation géocodée { lat, lon }
 */
async function geocodeAddress(address)
{
    const url = `https://nominatim.openstreetmap.org/search?q=${encodeURIComponent(
        address
    )}&format=json&limit=1`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.length > 0 && data[0].lat && data[0].lon) {
            return { lat: parseFloat(data[0].lat), lon: parseFloat(data[0].lon) };
        }
    } catch (error) {
        console.error("Erreur de géocodage :", error);
    }
}

async function calculateDistanceOnly(origin, destination)
{
    const url = `https://router.project-osrm.org/route/v1/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?overview=false`;

    const response = await fetch(url);
    const data = await response.json();

    if (data.routes && data.routes.length > 0) {
        return data.routes[0].distance;
    } else {
        console.error("Aucun itinéraire trouvé.");
        return Infinity;
    }
}

async function displayRoute(origin, destination)
{
    const url = `https://router.project-osrm.org/route/v1/driving/${origin.lon},${origin.lat};${destination.lon},${destination.lat}?overview=full&geometries=geojson`;

    try {
        const response = await fetch(url);
        const data = await response.json();

        if (data.routes && data.routes.length > 0) {
            const route = data.routes[0];
            const routeCoords = route.geometry.coordinates.map(
                (coord) =>
                ol.proj.fromLonLat(coord)
            );

            if (!routeLayer) {
                routeLayer = new ol.layer.Vector(
                    {
                        source: new ol.source.Vector(),
                        style: new ol.style.Style(
                            {
                                stroke: new ol.style.Stroke(
                                    {
                                        color: "red",
                                        width: 3,
                                    }
                                ),
                            }
                        ),
                    }
                );
                map.addLayer(routeLayer);
            }

            const routeFeature = new ol.Feature(
                {
                    geometry: new ol.geom.LineString(routeCoords),
                }
            );

            routeLayer.setSource(
                new ol.source.Vector(
                    {
                        features: [routeFeature],
                    }
                )
            );

            map.render();
        }
    } catch (error) {
        console.error("Erreur lors de la récupération de l'itinéraire :", error);
    }
}

function placeMarker(location, label, isCompany)
{
    if (isCompany && companyMarker) {
        map.removeOverlay(companyMarker);
    } else if (!isCompany && teacherMarker) {
        map.removeOverlay(teacherMarker);
    }

    const marker = new ol.Overlay(
        {
            position: ol.proj.fromLonLat([location.lon, location.lat]),
            element: createMarkerElement(label),
        }
    );

    if (isCompany) {
        companyMarker = marker;
    } else {
        teacherMarker = marker;
    }

    map.addOverlay(marker);
}

/**
 * Crée un élément de marqueur amélioré
 *
 * @param {string} label Étiquette du marqueur
 *
 * @returns {HTMLElement} Élément du marqueur
 */
function createMarkerElement(label)
{
    const marker = document.createElement("div");
    marker.className = "enhanced-marker";

    const markerLabel = document.createElement("div");
    markerLabel.className = "marker-label";
    markerLabel.textContent = label;

    const pointer = document.createElement("div");
    pointer.className = "marker-pointer";

    marker.appendChild(markerLabel);
    marker.appendChild(pointer);

    marker.style.position = "absolute";
    marker.style.display = "flex";
    marker.style.flexDirection = "column";
    marker.style.alignItems = "center";
    marker.style.zIndex = "1"

    const offsetX = Math.random() * 10 - 5;
    const offsetY = Math.random() * 10 - 5;
    marker.style.transform = `translate(${offsetX}px, ${offsetY}px)`;

    markerLabel.style.backgroundColor = "blue";
    markerLabel.style.color = "white";
    markerLabel.style.padding = "2px 5px";
    markerLabel.style.borderRadius = "3px";
    markerLabel.style.fontSize = "10px";
    markerLabel.style.textAlign = "center";
    markerLabel.style.boxShadow = "0 1px 3px rgba(0, 0, 0, 0.2)";

    pointer.style.width = "0";
    pointer.style.height = "0";
    pointer.style.borderLeft = "4px solid transparent";
    pointer.style.borderRight = "4px solid transparent";
    pointer.style.borderTop = "6px solid blue";
    pointer.style.marginTop = "-1px";

    return marker;
}

initMap();