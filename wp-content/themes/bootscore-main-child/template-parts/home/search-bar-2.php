<?php
/**
 * Template part to display the search bar on the home page
 *
 * @package Bootscore
 * @version 6.0.0
 */
?>

<div class="search-section-wrapper py-2">
    <form id="search-form" role="search" class="search-form" method="get" action="<?php echo esc_url(RICERCA_PAGE); ?>">
                    <div class="search-bar-wrapper">
                        <div class="search-bar flex items-center bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <i class="fa-solid fa-search search-icon ml-3"></i>
                            <input type="text" 
                                   class="form-control search-input pl-10 pr-3 py-2 w-full" 
                                   placeholder="Cerca documenti, corsi o libri" 
                                   aria-label="Ricerca" 
                                   name="search">
                        </div>
                        <div class="search-suggestions"></div>
                        
                    </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.search-input').forEach(searchInput => {
        let suggestions = document.createElement('ul');
        suggestions.className = 'list-group search-suggestions-list';
        suggestions.style.display = 'none';
        document.querySelector('.search-suggestions').appendChild(suggestions);

        searchInput.addEventListener('input', function() {
            let query = this.value;
            if (query.length < 3) {
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
                return;
            }

            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                method: 'POST',
                data: {
                    action: 'search_documents',
                    search: query,
                    max: 3,
                    nonce: '<?php echo wp_create_nonce('search_products_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        let results = response.data;
                        let html = '';

                        results.forEach(item => {
                            html += item.html_mini;
                        });

                        html += `
                            <li class="list-group-item list-group-item-action text-center">
                                <button type="submit" class="btn btn-link text-decoration-none text-dark fw-bold show-all-results">
                                    Mostra tutti i risultati
                                </button>
                            </li>`;

                        suggestions.innerHTML = html;
                        suggestions.style.display = results.length ? 'block' : 'none';
                    }
                },
                error: function(error) {
                    console.error(error);
                }
            });
        });

        document.addEventListener('click', function(event) {
            if (!event.target.closest('.search-bar-wrapper')) {
                suggestions.innerHTML = '';
                suggestions.style.display = 'none';
            }
        });

        suggestions.addEventListener('click', function(event) {
            if (event.target.classList.contains('show-all-results')) {
                document.getElementById('search-form').submit();
            }
        });
    });
});
</script>

<style>
.search-section-wrapper {
    padding: 2rem 0;
}

.search-bar-wrapper {
    position: relative;
}

.search-bar {
    display: flex;
    align-items: center;
    position: relative;
    background: var(--white);
    border-radius: var(--border-radius-lg);
    box-shadow: var(--box-shadow);
    transition: var(--transition);
}

.search-bar:focus-within {
    box-shadow: var(--box-shadow-lg);
    transform: translateY(-2px);
}

.search-bar .search-icon {
    position: absolute;
    left: 1.25rem;
    color: var(--gray-400);
    font-size: 1.1rem;
    transition: var(--transition);
}

.search-bar:focus-within .search-icon {
    color: var(--primary);
}

.search-bar .search-input {
    padding: 1rem 1rem 1rem 3rem;
    height: 3.5rem !important;
    border: 2px solid var(--gray-200);
    border-radius: var(--border-radius-lg);
    font-size: 1.1rem;
    transition: var(--transition);
}

.search-bar .search-input:focus {
    border-color: var(--primary);
    box-shadow: none;
}

.search-suggestions-list {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 0.5rem;
    background: var(--white);
    border-radius: var(--border-radius);
    box-shadow: var(--box-shadow-lg);
    z-index: 1050;
    max-height: 400px;
    overflow-y: auto;
}

.search-suggestions-list .list-group-item {
    padding: 1rem;
    border: none;
    border-bottom: 1px solid var(--gray-200);
    transition: var(--transition);
}

.search-suggestions-list .list-group-item:last-child {
    border-bottom: none;
}

.search-suggestions-list .list-group-item:hover {
    background-color: var(--gray-50);
}

.search-suggestions-list .btn-link {
    color: var(--primary);
    font-weight: 600;
    padding: 0.5rem 1rem;
    transition: var(--transition);
}

.search-suggestions-list .btn-link:hover {
    color: var(--primary-dark);
    background-color: var(--gray-50);
    border-radius: var(--border-radius);
}
</style>
