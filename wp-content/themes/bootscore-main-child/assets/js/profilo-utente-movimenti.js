jQuery(document).ready(function ($) {
    const $tableBody = $('#transaction-table-body');
    const $searchInput = $('#search-input');
    const $prevPageBtn = $('#prev-page');
    const $nextPageBtn = $('#next-page');
    const $pageInfo = $('#page-info');
    const menuItems = document.querySelectorAll(".menu-item");
    const filterAllBtn = document.getElementById("filter-all");
    const filterIncomesBtn = document.getElementById("filter-incomes");
    const filterOutcomesBtn = document.getElementById("filter-outcomes");

    let $filteredTransactions = $tableBody.children();
    let currentPage = 1;
    const rowsPerPage = 5;

    // Funzione per aggiornare la tabella
    function renderTable(page) {
        $tableBody.children().hide();
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        let visibleCount = 0;
        $filteredTransactions.each(function (index) {
            const $row = $(this);
            if (!$row.hasClass('details-row')) {
                if (visibleCount >= start && visibleCount < end) {
                    $row.show();
                }
                visibleCount++;
            }
        });

        $('.details-row').hide(); // Nascondi righe dei dettagli
        updatePaginationControls();
        showNoResultsMessage();
    }

    // Funzione per aggiornare i controlli di paginazione
    function updatePaginationControls() {
        const totalPages = Math.ceil($filteredTransactions.not('.details-row').length / rowsPerPage);
        $pageInfo.text(`Pagina ${currentPage} di ${totalPages}`);
        $prevPageBtn.prop('disabled', currentPage === 1);
        $nextPageBtn.prop('disabled', currentPage === totalPages);
    }

    // Funzione per mostrare messaggio "Nessun risultato"
    function showNoResultsMessage() {
        $('#no-results-message').remove();
        if ($filteredTransactions.not('.details-row').length === 0) {
            $tableBody.append('<tr id="no-results-message"><td colspan="6" class="text-center">Non ci sono risultati</td></tr>');
        }
    }

    // Funzione per filtrare le transazioni
    function filterTable(query) {
        const lowerCaseQuery = query.toLowerCase();
        $filteredTransactions = $tableBody.children().filter(function () {
            const $row = $(this);
            if ($row.hasClass('details-row')) return false;
            return $row.text().toLowerCase().includes(lowerCaseQuery);
        });
        currentPage = 1;
        renderTable(currentPage);
    }

    // Funzione per convertire il valore formattato in numero
    function parseCurrency(value) {
        const numericValue = value.replace(/[^\d.-]/g, ''); // Rimuove simboli non numerici
        return parseFloat(numericValue) || 0;
    }

    // Event listener per la ricerca
    $searchInput.on('input', function () {
        filterTable($searchInput.val().trim());
    });

    // Event listener per i pulsanti di paginazione
    $prevPageBtn.on('click', function () {
        if (currentPage > 1) {
            currentPage--;
            renderTable(currentPage);
        }
    });

    $nextPageBtn.on('click', function () {
        const totalPages = Math.ceil($filteredTransactions.not('.details-row').length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            renderTable(currentPage);
        }
    });

    // Funzione per gestire il menu di filtraggio
    function setActiveButton(button) {
        menuItems.forEach(item => item.classList.remove("active"));
        button.classList.add("active");
    }

    // Event listener per i filtri
    filterAllBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredTransactions = $tableBody.children();
        currentPage = 1;
        renderTable(currentPage);
    });

    filterIncomesBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredTransactions = $tableBody.children().filter(function () {
            const amount = parseCurrency($(this).data('entrata') || '0');
            return amount > 0;
        });
        currentPage = 1;
        renderTable(currentPage);
    });

    filterOutcomesBtn.addEventListener("click", function (e) {
        e.preventDefault();
        setActiveButton(this);
        $filteredTransactions = $tableBody.children().filter(function () {
            const amount = parseCurrency($(this).data('uscita') || '0');
            return amount > 0;
        });
        currentPage = 1;
        renderTable(currentPage);
    });

    // Event listener per i dettagli delle transazioni
    $tableBody.on('click', '.btn-link-table', function () {
        const $button = $(this);
        const $detailsRow = $button.closest('tr').next('.details-row');
        if ($detailsRow.length) {
            $detailsRow.toggle();
        }
    });

    // Inizializza la tabella
    renderTable(currentPage);
});
