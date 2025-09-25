function load_template_part(template_part, target_element, loading_function = null, success_function = null, error_function = null) {
    jQuery(document).ready(function($) {
        if (loading_function) {
            loading_function();
        }

        $.ajax({
            url: ajax_object.ajax_url,
            type: 'POST',
            data: {
                action: 'get_template_part_code',
                template_part: template_part
            },
            success: function(response) {
                $(target_element).html(response.data.template_code);
                if (success_function) {
                    success_function(response);
                }
            },
            error: function(xhr, status, error) {
                //console.log('AJAX Error: ' + status + error);
                if (error_function) {
                    error_function(xhr, status, error);
                }
            }
        });
    });
}
