<?php
/**
 * Plugin Name: velotaxi
 * Description: A plugin designed for handling the backend of the Velotaxi website.
 * Version: 2.5.0
 * Author: Wout
 * Author URI: https://concept24.x10.mx/
 **/

// Enqueue scripts and styles
function velotaxi_enqueue_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    wp_enqueue_script('jquery-ui-core');
    wp_enqueue_script('jquery-effects-core');
    wp_enqueue_script('jquery-ui-dialog');

    // Enqueue your main script
    wp_enqueue_script('velotaxi', plugin_dir_url(__FILE__) . 'js/velotaxi.js', array('jquery', 'jquery-ui-core', 'jquery-effects-core', 'jquery-ui-dialog'), '1.0', true);

    // Localize script with user ID and other variables
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $nonce = wp_create_nonce('claim_ride_nonce');

    wp_localize_script('velotaxi', 'claim_ride_vars', array(
        'nonce' => $nonce,
        'ajax_url' => admin_url('admin-ajax.php'),
        'user_id' => $user_id,
    ));

    // Add the inline script outside the echo block
    wp_add_inline_script('velotaxi', '
        var ajaxurl = "' . admin_url('admin-ajax.php') . '";
        var nonce = "' . $nonce . '";
    ');
}
add_action('wp_enqueue_scripts', 'velotaxi_enqueue_scripts');


// Include the file containing the AJAX callback logic
require_once(plugin_dir_path(__FILE__) . 'ajax-handler.php');

// Include the file containing the datatable creation logic
require_once(plugin_dir_path(__FILE__) . 'datatable-handler.php');
