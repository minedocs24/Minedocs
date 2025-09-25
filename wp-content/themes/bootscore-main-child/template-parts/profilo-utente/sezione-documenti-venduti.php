<?php

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    echo 'Errore: devi essere loggato per visualizzare i tuoi file.';
    return;
}

// Ottieni l'ID dell'utente loggato
$user_id = get_current_user_id();
$key_id_venditore = get_id_venditore_key();

// Ottieni tutti gli ordini di WooCommerce
$venditore_orders = wc_get_orders(array(
    'orderby' => 'date',
    'order' => 'DESC',
    'limit' => -1,
    'meta_query' => array(
        array(
            'key' => $key_id_venditore,
            'value' => $user_id,
            'compare' => '='
        )
    )
));

// Separare gli ordini con fattura richiesta dagli altri
$orders_with_invoice_request = array();
$other_orders = array();

foreach ($venditore_orders as $order) {
    if (get_richiesta_fattura_venditore($order->get_id()) == 'richiesta') {
        $orders_with_invoice_request[] = $order;
    } else {
        $other_orders[] = $order;
    }
}

// Unire gli ordini con fattura richiesta e gli altri ordini
$venditore_orders = array_merge($orders_with_invoice_request, $other_orders);

$orders = array();
foreach ($venditore_orders as $order) {
    $order_id = $order->get_id();
    $order_hid = get_order_hash($order_id);
    $order_date = $order->get_date_created()->date('Y-m-d H:i:s');
    $order_number = $order->get_order_number();
    $items = $order->get_items();
    $first_product_name = count($items) > 0 ? reset($items)->get_name() : 'N/A';

    $guadagno_lordo_venditore = get_guadagno_lordo_venditore($order_id) == '' ? ' - ' : number_format(get_guadagno_lordo_venditore($order_id), 2, ',', '.') . ' €';
    $commissione_minedocs = get_commissione_minedocs($order_id) == '' ? ' - ' : number_format(get_commissione_minedocs($order_id), 2, ',', '.') . ' €';
    $guadagno_netto_venditore = get_guadagno_netto_venditore($order_id) == '' ? ' - ' : number_format(get_guadagno_netto_venditore($order_id), 2, ',', '.') . ' €';

    $orders[] = array(
        'order_id' => $order_id,
        'order_hid' => $order_hid,
        'order_date' => $order_date,
        'order_number' => $order_number,
        'first_product_name' => $first_product_name,
        'guadagno_lordo' => $guadagno_lordo_venditore,
        'commissione' => $commissione_minedocs,
        'guadagno_netto' => $guadagno_netto_venditore,
        'richiesta_fattura' => get_richiesta_fattura_venditore($order_id)
    );
}
?>
<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class="my-5">
        <div class="table-card">
            <div class="table-card-header d-flex flex-column flex-sm-row justify-content-between align-items-center">
                <h2 class="table-card-header-text text-center text-sm-start">Le mie vendite</h2>
                <a href="<?php echo CARICAMENTO_DOCUMENTO_PAGE; ?>" class="upload-btn-table btn-cta mt-2 mt-sm-0">Carica file</a>
            </div>
            <div class="card-body">

                <div class="filter-box d-flex flex-column mb-3">
                    <input type="text" id="search-input" placeholder="Cerca..." class="ricerca-file">
                </div>

                <?php if (count($orders_with_invoice_request) > 0): ?>
                    <div class="alert-box">                    
                        <div class="alert alert-warning" role="alert">
                            Hai <?php echo count($orders_with_invoice_request); ?> richieste di fatturazione.
                        </div>                    
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-profilo align-middle" id="order-table">
                        <thead class="table-dark">
                            <tr>
                                <th>Data</th>
                                <th>Numero Ordine</th>
                                <th>Nome articolo</th>
                                <th>Il tuo guadagno</th>
                                <th>La nostra commissione</th>
                                <th>Totale accreditato</th>
                                <th>Richiesta Fattura</th>
                            </tr>
                        </thead>
                        <tbody id="order-table-body">
                            <?php foreach ($orders as $order): ?>
                            <tr data-order-id="<?php echo esc_attr($order['order_hid']); ?>">
                                <td><?php echo esc_html($order['order_date']); ?></td>
                                <td><?php echo esc_html($order['order_number']); ?></td>
                                <td><?php echo esc_html($order['first_product_name']); ?></td>
                                <td><?php echo esc_html($order['guadagno_lordo']); ?></td>
                                <td><?php echo esc_html($order['commissione']); ?></td>
                                <td><?php echo esc_html($order['guadagno_netto']); ?></td>
                                <td><?php mostra_pulsante_fattura_venditore($order['order_hid']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="pagination-controls">
                    <img id="prev-page" class="table-button-prev" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Precedente" width="16" height="16" />
                    <span id="page-info"></span>
                    <img id="next-page" class="table-button-next" src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/user/sezione-documenti-caricati/leftArrow.png" alt="Successivo" width="16" height="16" />
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rowsPerPage = 4;
    let currentPage = 1;
    const tableBody = document.getElementById('order-table-body');
    const rows = Array.from(tableBody.getElementsByTagName('tr'));
    const pageInfo = document.getElementById('page-info');
    const prevPageBtn = document.getElementById('prev-page');
    const nextPageBtn = document.getElementById('next-page');
    const searchInput = document.getElementById('search-input');

    function displayRows(filteredRows = rows) {
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        filteredRows.forEach((row, index) => {
            row.style.display = (index >= start && index < end) ? '' : 'none';
        });
        pageInfo.textContent = `Pagina ${currentPage} di ${Math.ceil(filteredRows.length / rowsPerPage)}`;
        prevPageBtn.style.visibility = currentPage === 1 ? 'hidden' : 'visible';
        nextPageBtn.style.visibility = currentPage === Math.ceil(rows.length / rowsPerPage) ? 'hidden' : 'visible';
    }

    function filterRows() {
        const searchTerm = searchInput.value.toLowerCase();
        const filteredRows = rows.filter(row => {
            const cells = Array.from(row.getElementsByTagName('td'));
            return cells.some(cell => cell.textContent.toLowerCase().includes(searchTerm));
        });
        rows.forEach(row => {
            row.style.display = 'none';
        });
        filteredRows.forEach(row => {
            row.style.display = '';
        });
        currentPage = 1;
        displayRows(filteredRows);
    }

    prevPageBtn.addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            displayRows();
        }
    });

    nextPageBtn.addEventListener('click', function() {
        if (currentPage < Math.ceil(rows.length / rowsPerPage)) {
            currentPage++;
            displayRows();
        }
    });

    searchInput.addEventListener('input', filterRows);

    displayRows();
});
</script>