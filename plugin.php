<?php
/**
 * Plugin Name: wp-gravityforms-hooks
 * Plugin URI:  https://github.com/real-coder-pty-ltd/wp-gravityforms-hooks
 * Description: Hooks and filters for Gravityforms.
 * Version:     1.0.3
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
function gravity_form_submission_data_layer(): void
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

/**
 * Display zipcode before city in Address Fields
 *
 * @link https://docs.gravityforms.com/gform_address_display_format/
 */
function strt_address_format()
{
    return 'zip_before_city';
}
add_filter('gform_address_display_format', 'strt_address_format', 10, 2);

/**
 * Add a new phone format for Australia.
 */
function au_phone_format($phone_formats): array
{
    $phone_formats['au'] = [
        'label' => 'Australia',
        'mask' => '9999 999 999',
        'regex' => '/^\({0,1}((0|\+61)(2|4|3|7|8)){0,1}\){0,1}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{2}(\ |-){0,1}[0-9]{1}(\ |-){0,1}[0-9]{3}$/',
        'instruction' => 'Australian phone numbers.',
    ];

    return $phone_formats;
}
add_filter('gform_phone_formats', 'au_phone_format');
