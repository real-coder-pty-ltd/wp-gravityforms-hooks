<?php

/**
 * Plugin Name: wp-gravityforms-hooks
 * Plugin URI:  https://github.com/real-coder-pty-ltd/wp-gravityforms-hooks
 * Description: Hooks and filters for Gravityforms.
 * Version:     1.0.0
 * Author:      Matthew Neal
 * Author URI:  https://github.com/matt-neal
 */
if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    return;
}

require_once $composer;

/**
 * Get the gravityform title and add it to the form element as a data- tag.
 *
 * @param  string  $form_tag
 * @param  array  $form
 */
function add_form_name_data_attr($form_tag, $form): string
{
    $form_tag = str_replace('>', ' data-form-name="'.sanitize_title($form['title']).'">', $form_tag);

    return $form_tag;
}
add_filter('gform_form_tag', 'add_form_name_data_attr', 10, 2);

/**
 * Get the gravityform title and add it to the dataLayer.
 */
function gravity_form_submission_data_layer()
{ ?>
    <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        var formObjects = {};
        var forms = document.querySelectorAll('form[id^="gform_"]');
    
        forms.forEach(function(form) {
            var formID = form.id.split('_')[1];
            formObjects[formID] = form;
        });
    
        jQuery(document).bind("gform_confirmation_loaded", function(event, formID) {
    
            if (formObjects[formID]) {
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'formSubmission',
                    'formID': formID,
                    'formTitle': formObjects[formID].getAttribute('data-form-name') || 'Untitled Form'
                });
            } else {
                console.warn('Form with ID ' + formID + ' not found in formObjects.');
            }
          });
    });
    </script>
    <?php }
add_action('wp_footer', 'gravity_form_submission_data_layer');
