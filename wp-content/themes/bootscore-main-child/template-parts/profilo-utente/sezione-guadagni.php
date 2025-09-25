<?php

// Importo minimo prelevabile dall'utente
$min_prelievo = SALDO_MINIMO_PRELEVABILE;

$user_id = get_current_user_id();
$num_doc_venduti = get_user_documents_sold_count($user_id);
//$saldo_utente = get_user_meta($user_id, 'saldo_utente', true);
$saldo_utente = ottieni_totale_prelevabile($user_id);
$paypal_email = get_user_meta($user_id, 'paypal_email', true);
$billing_data = get_user_billing_data($user_id);
$first_name = $billing_data['first_name'];
$last_name = $billing_data['last_name'];
$billing_address_1 = $billing_data['billing_address_1'];
$billing_address_num = $billing_data['billing_address_num'];
$billing_city = $billing_data['billing_city'];
$billing_postcode = $billing_data['billing_postcode'];
$billing_country = $billing_data['billing_country'];
$billing_phone = $billing_data['billing_phone'];
$codice_fiscale = $billing_data['codice_fiscale'];
if (empty($billing_address_1) || empty($billing_postcode) || empty($billing_city) || empty($billing_country)) {
    $billing_info = "";
} else {
    $billing_info = $billing_address_1 . ', ' . $billing_address_num . ', ' . $billing_city . ', ' . $billing_postcode . ', ' . $billing_country;
}

$approved_paypal_email = get_paypal_email_confermata($user_id);

?>


<div id="main-profile-section" class="ms-sm-auto px-md-4">
    <div class=" my-5">
        <h2 class="mb-4">I miei guadagni</h2>
        <?php
        //ottieni il template part con il saldo utente, le vendite e i followers
        get_template_part('template-parts/profilo-utente/sezione-guadagni-saldo-vendite-followers', null, array(
            'saldo_utente' => $saldo_utente,
            'num_doc_venduti' => $num_doc_venduti
        ));
        ?>
        <div class="text-center mt-4">
            <button id="prelievoButton" class="btn btn-primary btn-preleva" data-bs-toggle="modal" data-bs-target="#prelievoModal">
                <span>Preleva</span>
            </button>
            <p class="small mt-2">Puoi richiedere il prelievo per un importo minimo di €
                <?php echo number_format($min_prelievo, 2, ',', '.'); ?>
            </p>

            <!-- Modal Bootstrap -->
            <div class="modal fade" id="prelievoModal" tabindex="-1" aria-labelledby="prelievoModalLabel" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="prelievoModalLabel">Preleva Saldo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
                  </div>
                  <div class="modal-body">
                    <form id="withdrawForm" onsubmit="event.preventDefault(); prelevaSaldo('<?php echo admin_url('admin-ajax.php'); ?>', document.getElementById('withdrawAmount').value);">
                      <div class="mb-3">
                        <label for="withdrawAmount" class="form-label">Importo da prelevare (minimo € <?php echo number_format($min_prelievo, 2, ',', '.'); ?>):</label>
                        <input type="number" id="withdrawAmount" name="withdrawAmount" class="form-control" min="<?php echo $min_prelievo; ?>" step="0.01" value="<?php echo $min_prelievo; ?>" required>
                      </div>
                      <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary btn-preleva">
                          <span>Preleva</span>
                          <div id="icon-loading-withdraw-balance" class="btn-loader mx-2" hidden>
                            <span class="spinner-border spinner-border-sm"></span>
                          </div>
                        </button>
                      </div>
                    </form>
                  </div>
                </div>
              </div>
            </div>
        </div>

        <div class="info-section p-4">
            <h3 class="section-title mb-4">Le tue informazioni:</h3>

            <div class="mb-4">
                <h5 class="sub-title">Conto collegato</h5>
                <div class="d-flex align-items-center mb-2">
                    <img src="<?php echo get_stylesheet_directory_uri(); ?>/assets/img/pagina-utente-guadagni/paypal-logo.png"
                        alt="PayPal Logo" class="paypal-logo me-2" style="max-width: 50px; height: auto;">
                    <div class="flex-grow-1">
                        <p class="mb-0">PayPal</p>
                        <p class="text-muted mb-0" id="paypal_email_display">
                            <?php echo empty($paypal_email) ? "Nessun conto PayPal collegato." : $paypal_email; ?>
                        </p>
                        <input type="email" id="email-input" class="form-control form-control-sm w-100"
                            placeholder="Inserisci la mail del tuo conto PayPal." value="<?php echo $paypal_email; ?>"
                            style="display: none;">
                    </div>
                </div>
                <?php if ($approved_paypal_email == 1 || get_user_approval_paypal_expiration(get_current_user_id()) < time()) { ?>

                    <div id="change-paypal-email-box">

                        <button id="edit-paying-method-button" class="btn btn-outline-secondary btn-sm">Modifica</button>
                        <button id="save-paying-method-button" class="btn btn-primary btn-sm" style="display: none;"
                            onclick="updatePaypalInfo('<?php echo admin_url('admin-ajax.php'); ?>')">
                            <div id="icon-loading-update-paypal" class="btn-loader mx-2" hidden
                                style="display: inline-block;">
                                <span class="spinner-border spinner-border-sm"></span>
                            </div>
                            <span>Salva</span>
                        </button>
                        <button id="cancel-paying-method-button" class="btn btn-secondary btn-sm"
                            style="display: none;">Annulla</button>
                    </div>
                <?php } else { ?>
                    <p class="text-muted mb-0">Mail PayPal in approvazione. Controlla la tua casella e conferma che sei
                        stato tu.</p>
                <?php } ?>

            </div>

            <hr>

            <div class="mt-4">
                <h5 class="sub-title">Dati di fatturazione</h5>
                <p id="billing-last-name-first-name-display" class="mb-0"><?php echo $last_name . ' ' . $first_name; ?>
                </p>
                <p id="billing-info-display" class="text-muted mb-0">
                    <?php if ($billing_info != "")
                        echo $billing_info;
                    else
                        echo "Nessun indirizzo di fatturazione impostato."; ?>
                </p>
                <p id="codice-fiscale-display" class="text-muted mb-0"><?php echo $codice_fiscale; ?></p>
                <p id="billing-phone-display" class="text-muted mb-2"><?php echo $billing_phone; ?></p>
                <button id="edit-billing-data-button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#billingDataModal">Modifica</button>
                <!-- Modal Bootstrap per modifica dati fatturazione -->
                <div class="modal fade" id="billingDataModal" tabindex="-1" aria-labelledby="billingDataModalLabel" aria-hidden="true">
                  <div class="modal-dialog modal-dialog-centered modal-lg modal-billing-custom">
                    <?php
                    // effettua il get template part  del form per la modifica dei dati di fatturazione
                    get_template_part('template-parts/profilo-utente/modifica-dati-fatturazione-popup', null, array(
                        'user_id' => $user_id,
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'billing_address_1' => $billing_address_1,
                        'billing_address_num' => $billing_address_num,
                        'billing_city' => $billing_city,
                        'billing_postcode' => $billing_postcode,
                        'billing_country' => $billing_country,
                        'billing_phone' => $billing_phone,
                        'codice_fiscale' => $codice_fiscale
                    ));
                    ?>
                  </div>
                </div>
            </div>
        </div>

    </div>
</div>



<style>
    /* Styling per le schede informative */
    .info-card {
        border: 1px solid #ddd;
        border-radius: 15px;
        padding: 20px;
        background-color: #f9f9f9;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: box-shadow 0.3s ease;
    }

    .info-card:hover {
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .info-icon {
        font-size: 24px;
        color: #007bff;
    }

    .info-title {
        font-size: 16px;
        color: #666;
        margin: 10px 0 5px;
    }

    .is-invalid {
        border: 1px solid red;
    }

    .info-value {
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    /* Styling per il pulsante Preleva */
    .btn-preleva {
        padding: 10px 40px;
        border-radius: 30px;
        font-weight: bold;
        box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
        transition: box-shadow 0.3s ease;
    }

    .btn-preleva:hover {
        box-shadow: 0 6px 12px rgba(0, 123, 255, 0.4);
    }

    /* Messaggio informativo sotto il pulsante */
    .small {
        color: #666;
    }

    /* Contenitore per la sezione delle informazioni */
    .info-section {
        border: 1px solid #ddd;
        border-radius: 10px;
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    /* Titoli delle sezioni */
    .section-title {
        font-size: 20px;
        font-weight: bold;
        color: #333;
    }

    /* Titoli delle sottosezioni */
    .sub-title {
        font-size: 16px;
        font-weight: bold;
        color: #333;
        margin-bottom: 10px;
    }

    /* Logo PayPal */
    .paypal-logo {
        width: 40px;
        height: auto;
    }

    /* Spaziatura per il contenuto di testo secondario */
    .text-muted {
        color: #6c757d;
    }

    /* The Modal (background) */
    .modal {
        display: none;
        /* Hidden by default */
        position: fixed;
        /* Stay in place */
        z-index: 1;
        /* Sit on top */
        left: 0;
        top: 0;
        width: 100%;
        /* Full width */
        height: 100%;
        /* Full height */
        overflow: auto;
        /* Enable scroll if needed */
        background-color: rgb(0, 0, 0);
        /* Fallback color */
        background-color: rgba(0, 0, 0, 0.4);
        /* Black w/ opacity */
    }

    /* Modal Content/Box */
    .modal-content {
        background-color: #fefefe;
        margin: 15% auto;
        /* 15% from the top and centered */
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        /* Could be more or less, depending on screen size */
    }

    /* The Close Button */
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }

    /* Modal Content/Box */
    .modal-billing-custom .modal-content {
        width: 100%;
        max-width: 700px;
        margin: 0 auto;
    }
    @media (max-width: 576px) {
        .modal-billing-custom .modal-content {
            max-width: 98vw;
            padding: 10px;
        }
        .modal-billing-custom .modal-body {
            padding: 8px 2px;
        }
        .modal-billing-custom .form-control {
            font-size: 1rem;
            min-width: 0;
        }
        .modal-billing-custom label.form-label {
            font-size: 0.95rem;
        }
    }
</style>