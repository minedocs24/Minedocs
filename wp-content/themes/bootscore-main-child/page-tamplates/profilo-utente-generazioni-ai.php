<?php

/**
 * Template Name: Profilo Utente Generazioni AI
 *
 * Pagina per visualizzare le generazioni AI dell'utente
 *
 * @package Bootscore
 * @version 6.0.0
 */

// Verifica se l'utente è loggato
if (!is_user_logged_in()) {
    wp_redirect( home_url('/login') );
    return;
}

// Exit if accessed directly
defined('ABSPATH') || exit;

$class_padding_admin = current_user_can('administrator') ? 'pt-5' : '';

get_header();

// Carica gli script necessari per la gestione delle generazioni AI
wp_enqueue_script('studia-con-ai-script', get_stylesheet_directory_uri() . '/assets/js/studia-con-ai.js', array('jquery'), null, true);
wp_localize_script('studia-con-ai-script', 'env_studia_con_ai', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce'    => wp_create_nonce('studia_con_ai_nonce'),
    'nonce_summary_jobs' => wp_create_nonce('nonce_summary_jobs'),
    'nonce_get_job_details' => wp_create_nonce('nonce_get_job_details'),
    'nonce_delete_job' => wp_create_nonce('nonce_delete_job'),
    'nonce_summary_download' => wp_create_nonce('nonce_summary_download'),
    'home_url' => home_url(),
    'hide_params' => true
));
?>

<!-- Overlay -->
<div id="overlay-profilo-utente"></div>

<div class="d-flex overflow-hidden w-100">
  <!-- Sidebar desktop -->
  <div id="sidebarDesktop-profilo-utente" class="p-3 d-none d-lg-block flex-shrink-0" style="min-width: 250px; width: 350px;">
    <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'generazioni-ai')); ?>
  </div>

  <!-- Sidebar mobile -->
  <div id="sidebarMobile-profilo-utente" class="d-lg-none p-3">
    <?php get_template_part('template-parts/profilo-utente/sidebar-new', null, array('current_page' => 'generazioni-ai')); ?>
  </div>
  
  <!-- Contenuto principale -->
  <div class="flex-grow-1 overflow-auto p-4">
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">
                <i class="fas fa-robot me-2 text-primary"></i>
                Le mie generazioni AI
              </h5>
              <button id="refresh-jobs" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-sync-alt"></i> Aggiorna
              </button>
            </div>
            <div class="card-body">
              <div class="alert alert-info mb-4" role="alert">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Generazioni AI:</strong> Qui puoi visualizzare tutte le tue generazioni AI, controllarne lo stato e scaricare i risultati completati.
              </div>
              
              <div id="jobs-container">
                <div class="text-center text-muted">
                  <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                  <p>Caricamento generazioni in corso...</p>
                </div>
              </div>
              
              <!-- Paginazione -->
              <div id="jobs-pagination" class="mt-4" style="display: none;">
                <nav aria-label="Paginazione generazioni">
                  <ul class="pagination justify-content-center" id="pagination-container">
                    <!-- Contenuto dinamico -->
                  </ul>
                </nav>
                <div class="text-center mt-2">
                  <small class="text-muted">
                    <span id="pagination-info">Mostrando 0-0 di 0 generazioni</span>
                  </small>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal per i dettagli del job -->
<div class="modal fade" id="jobDetailsModal" tabindex="-1" aria-labelledby="jobDetailsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="jobDetailsModalLabel">
          <i class="fas fa-robot me-2"></i>Dettagli Generazione AI
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="jobDetailsContent">
        <!-- Contenuto dinamico -->
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
      </div>
    </div>
  </div>
</div>


<script>
jQuery(document).ready(function($) {
  // Variabili globali
  let currentJobId = null;
  
  // Variabili per la paginazione
  let currentPage = 1;
  let itemsPerPage = 10;
  let allJobs = {};
  let totalJobs = 0;
  
  // Funzione per caricare i job
  function loadSummaryJobs(page = 1) {
    const container = $('#jobs-container');
    currentPage = page;
    
    $.ajax({
      url: env_studia_con_ai.ajax_url,
      type: 'POST',
      data: {
        action: 'get_summary_jobs',
        nonce: env_studia_con_ai.nonce_summary_jobs,
        page: page,
        per_page: itemsPerPage
      },
      success: function(response) {
        if (response.success) {
          allJobs = response.data.jobs || {};
          totalJobs = response.data.total || 0;
          displaySummaryJobs(allJobs);
          displayPagination(response.data.total || 0, page);
        } else {
          container.html('<div class="alert alert-danger">Errore nel caricamento delle generazioni</div>');
          $('#jobs-pagination').hide();
        }
      },
      error: function() {
        container.html('<div class="alert alert-danger">Errore di connessione</div>');
        $('#jobs-pagination').hide();
      }
    });
  }

  function displaySummaryJobs(jobs) {
    const container = $('#jobs-container');
    
    if (Object.keys(jobs).length === 0) {
      container.html(`
        <div class="text-center text-muted py-5">
          <i class="fas fa-inbox fa-4x mb-4"></i>
          <h5>Nessuna generazione trovata</h5>
          <p class="mb-3">Non hai ancora avviato nessuna generazione AI.</p>
          <a href="${env_studia_con_ai.home_url}/studia-con-ai/" class="btn btn-primary">
            <i class="fas fa-robot me-2"></i>Inizia la tua prima generazione
          </a>
        </div>
      `);
      $('#jobs-pagination').hide();
      return;
    }

    let html = '<div class="table-responsive"><table class="table table-hover">';
    html += '<thead class="table-light"><tr><th>Documento</th><th>Tipo</th><th>Stato</th><th>Data</th><th>Azioni</th></tr></thead><tbody>';
    
    // Converti l'oggetto jobs in array e ordina per data (più recente prima)
    const jobsArray = Object.keys(jobs).map(jobId => ({
      jobId: jobId,
      ...jobs[jobId]
    })).sort((a, b) => new Date(b.request_date) - new Date(a.request_date));
    
    jobsArray.forEach(job => {
      const config = job.config ? JSON.parse(job.config) : {};
      const status = getStatusBadge(job.status);
      const date = new Date(job.request_date).toLocaleString('it-IT');
      const requestType = getRequestTypeLabel(job.request_type);
      
      // Ottieni il nome del file o il titolo del documento
      const fileName = job.file_name || 'Documento sconosciuto';
      const isPlatformDocument = job.is_platform_document || false;
      
      // Determina il sottotitolo (parametri nascosti temporaneamente)
      let subtitle = '';
      
      html += `
        <tr>
          <td>
            <strong>${fileName}</strong><br>
            <small class="text-muted">${subtitle}</small>
          </td>
          <td>${requestType}</td>
          <td>${status}</td>
          <td>${date}</td>
          <td>
            ${getActionButtons(job)}
          </td>
        </tr>
      `;
    });
    
    html += '</tbody></table></div>';
    container.html(html);
  }
  
  function displayPagination(totalItems, currentPage) {
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const paginationContainer = $('#pagination-container');
    const paginationInfo = $('#pagination-info');
    const paginationDiv = $('#jobs-pagination');
    
    if (totalPages <= 1) {
      paginationDiv.hide();
      return;
    }
    
    paginationDiv.show();
    
    // Calcola gli elementi da mostrare
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, totalItems);
    
    // Aggiorna le informazioni
    paginationInfo.text(`Mostrando ${startItem}-${endItem} di ${totalItems} generazioni`);
    
    // Genera i pulsanti di paginazione
    let paginationHtml = '';
    
    // Pulsante "Precedente"
    if (currentPage > 1) {
      paginationHtml += `<li class="page-item">
        <a class="page-link" href="#" onclick="changePage(${currentPage - 1})" aria-label="Precedente">
          <span aria-hidden="true">&laquo;</span>
        </a>
      </li>`;
    } else {
      paginationHtml += `<li class="page-item disabled">
        <span class="page-link" aria-label="Precedente">
          <span aria-hidden="true">&laquo;</span>
        </span>
      </li>`;
    }
    
    // Calcola il range di pagine da mostrare
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, currentPage + 2);
    
    // Aggiusta il range se necessario
    if (endPage - startPage < 4) {
      if (startPage === 1) {
        endPage = Math.min(totalPages, startPage + 4);
      } else {
        startPage = Math.max(1, endPage - 4);
      }
    }
    
    // Pulsanti delle pagine
    for (let i = startPage; i <= endPage; i++) {
      if (i === currentPage) {
        paginationHtml += `<li class="page-item active">
          <span class="page-link">${i}</span>
        </li>`;
      } else {
        paginationHtml += `<li class="page-item">
          <a class="page-link" href="#" onclick="changePage(${i})">${i}</a>
        </li>`;
      }
    }
    
    // Pulsante "Successiva"
    if (currentPage < totalPages) {
      paginationHtml += `<li class="page-item">
        <a class="page-link" href="#" onclick="changePage(${currentPage + 1})" aria-label="Successiva">
          <span aria-hidden="true">&raquo;</span>
        </a>
      </li>`;
    } else {
      paginationHtml += `<li class="page-item disabled">
        <span class="page-link" aria-label="Successiva">
          <span aria-hidden="true">&raquo;</span>
        </span>
      </li>`;
    }
    
    paginationContainer.html(paginationHtml);
  }
  
  // Funzione globale per cambiare pagina
  window.changePage = function(page) {
    loadSummaryJobs(page);
    
    // Scroll fino all'inizio della sezione "Le mie generazioni"
    const jobsSection = document.querySelector('.card');
    if (jobsSection) {
      jobsSection.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'start' 
      });
    }
  };

  function getRequestTypeLabel(requestType) {
    const types = {
      'summary': 'Riassunto',
      'riassunto': 'Riassunto',
      'quiz': 'Quiz',
      'mappa': 'Mappa Concettuale',
      'mindmap': 'Mappa Concettuale',
      'evidenza': 'Evidenziazione',
      'highlight': 'Evidenziazione',
      'interroga': 'Interrogazione'
    };
    return types[requestType] || requestType;
  }

  function getStatusBadge(status) {
    const badges = {
      'pending': '<span class="badge bg-warning"><i class="fas fa-clock me-1"></i>In attesa</span>',
      'processing': '<span class="badge bg-info"><i class="fas fa-cog fa-spin me-1"></i>In elaborazione</span>',
      'completed': '<span class="badge bg-success"><i class="fas fa-check me-1"></i>Completato</span>',
      'error': '<span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Errore</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Sconosciuto</span>';
  }

  function getActionButtons(job) {
    let buttons = `<button class="btn btn-sm btn-outline-primary" onclick="showJobDetails(${job.job_id})">
      <i class="fas fa-eye"></i> Dettagli
    </button>`;
    
    if (job.status === 'completed') {
      buttons += ` <button class="btn btn-sm btn-success" onclick="downloadSummary(${job.job_id})">
        <i class="fas fa-download"></i> Scarica
      </button>`;
    }
    
    if (job.status === 'error') {
      buttons += ` <button class="btn btn-sm btn-warning" onclick="retryJob(${job.job_id})">
        <i class="fas fa-redo"></i> Riprova
      </button>`;
    }
    
    
    return buttons;
  }

  // Funzione globale per mostrare i dettagli del job
  window.showJobDetails = function(jobId) {
    $('#jobDetailsModal').modal('show');
    $('#jobDetailsContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Caricamento...</div>');

    $.ajax({
      url: env_studia_con_ai.ajax_url,
      type: 'POST',
      data: {
        action: 'get_job_details',
        job_id: jobId,
        nonce: env_studia_con_ai.nonce_get_job_details
      },
      success: function(response) {
        if (response.success) {
          displayJobDetails(response.data);
        } else {
          $('#jobDetailsContent').html(`
            <div class="alert alert-danger">
              <h6>Errore</h6>
              <p>${response.data.message}</p>
            </div>
          `);
        }
      },
      error: function() {
        $('#jobDetailsContent').html(`
          <div class="alert alert-danger">
            <h6>Errore di connessione</h6>
            <p>Impossibile caricare i dettagli del job.</p>
          </div>
        `);
      }
    });
  };

  function displayJobDetails(job) {
    const config = job.config ? JSON.parse(job.config) : {};
    const date = new Date(job.request_date).toLocaleString('it-IT');
    const requestType = getRequestTypeLabel(job.request_type);
    const puntiText = job.points_cost ? (job.points_cost === 1 ? '1 punto' : job.points_cost + ' punti') : '-';

    let html = `
      <div class="row">
        <div class="col-md-6">
          <h6><i class="fas fa-info-circle me-2"></i>Informazioni Generali</h6>
          <table class="table table-sm">
            <tr><td><strong>ID Job:</strong></td><td>${job.job_id}</td></tr>
            <tr><td><strong>Tipo:</strong></td><td>${requestType}</td></tr>
            <tr><td><strong>Stato:</strong></td><td>${getStatusBadge(job.status)}</td></tr>
            <tr><td><strong>Data richiesta:</strong></td><td>${date}</td></tr>
            <tr><td><strong>Costo (Punti Pro):</strong></td><td>${puntiText}</td></tr>
          </table>
        </div>
        ${env_studia_con_ai.hide_params ? '' : `
        <div class="col-md-6">
          <h6><i class="fas fa-cog me-2"></i>Parametri Configurazione</h6>
          <table class="table table-sm">
            <tr><td><strong>Modalità:</strong></td><td>${config.mode || '-'}</td></tr>
            <tr><td><strong>Lingua:</strong></td><td>${config.language || '-'}</td></tr>
            <tr><td><strong>Dettaglio:</strong></td><td>${config.detail_level || '-'}</td></tr>
            <tr><td><strong>Tempo lettura:</strong></td><td>${config.reading_time || '-'}</td></tr>
          </table>
        </div>`}
      </div>
    `;
    
    if (job.error_message) {
      html += `
        <div class="alert alert-danger mt-3">
          <h6><i class="fas fa-exclamation-triangle me-2"></i>Errore</h6>
          <p>${job.error_message}</p>
        </div>
      `;
    }
    
    if (job.status === 'completed') {
      html += `
        <div class="alert alert-success mt-3">
          <h6><i class="fas fa-check-circle me-2"></i>Risultato disponibile</h6>
          <button class="btn btn-success" onclick="downloadSummary(${job.job_id})">
            <i class="fas fa-download me-2"></i>Scarica risultato
          </button>
        </div>
      `;
    }
    
    $('#jobDetailsContent').html(html);
  }


  // Funzione globale per riprovare un job (placeholder per ora)
  window.retryJob = function(jobId) {
    showCustomAlert("Info", "Funzionalità di retry in sviluppo", 'bg-info btn-info');
  };

  // Carica i job all'avvio
  loadSummaryJobs(1);

  // Aggiorna la lista quando si clicca su "Aggiorna"
  $('#refresh-jobs').on('click', function() {
    const button = $(this);
    
    // Previene chiamate multiple se il pulsante è già disabilitato
    if (button.prop('disabled')) {
      return;
    }
    
    button.prop('disabled', true);
    button.html('<i class="fas fa-spinner fa-spin"></i>');
    
    // Resetta alla prima pagina
    currentPage = 1;
    loadSummaryJobs(1);
    
    setTimeout(() => {
      button.prop('disabled', false);
      button.html('<i class="fas fa-sync-alt"></i> Aggiorna');
    }, 1000);
  });
});
</script>

<?php
get_footer();
