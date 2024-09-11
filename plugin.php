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
 * Get the gravityform title and add it to the button as a data- tag.
 */
add_filter('gform_submit_button', 'add_form_name_data_attr_to_submit', 10, 2);
function add_form_name_data_attr_to_submit($button, $form)
{
    // Return without changes for the admin back-end.
    if (is_admin()) {
        return $button;
    }
    $button = str_replace('>', ' data-form-name="'.sanitize_title($form['title']).'">', $button);

    return $button;
}

/**
 * Get the gravityform title and add it to the dataLayer for Google Tag Manager.
 */
function gravity_form_submission_data_layer(): void
{ ?>
    <script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {

        var buttons = document.querySelectorAll('input.gform_button[type="submit"]');

        if ( buttons) {
            buttons.forEach(function(button) {
                button.addEventListener('click', function(event) {
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'formSubmission',
                        'formTitle': button.getAttribute('data-form-name') ||'Untitled Form'
                    });
                });
            });
        }
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
