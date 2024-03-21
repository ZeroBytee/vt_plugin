<?php
/**
 * Plugin Name: velotaxi
 * Description: A plugin designed for handling the backend of the Velotaxi website.
 * Version: PRE-3.1.4
 * Author: Wout, Miro, Nils
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

    velotaxi_create_tables();
}
add_action('wp_enqueue_scripts', 'velotaxi_enqueue_scripts');


function velotaxi_create_tables() {
    global $wpdb;

    // Table names
    $completed_rides_table = $wpdb->prefix . 'completed_rides';
    $deleted_rides_table = $wpdb->prefix . 'deleted_rides';

    // Check if tables exist
    if ($wpdb->get_var("SHOW TABLES LIKE '$completed_rides_table'") != $completed_rides_table) {
        // Table does not exist, create it
        $sql = "CREATE TABLE $completed_rides_table (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `form_id` int(10) UNSIGNED DEFAULT NULL,
          `serial_number` int(10) UNSIGNED DEFAULT NULL,
          `response` longtext DEFAULT NULL,
          `source_url` varchar(255) DEFAULT NULL,
          `user_id` int(10) UNSIGNED DEFAULT NULL,
          `status` varchar(45) DEFAULT 'unread' COMMENT 'possible values: read, unread, trashed',
          `is_favourite` tinyint(1) NOT NULL DEFAULT 0,
          `browser` varchar(45) DEFAULT NULL,
          `device` varchar(45) DEFAULT NULL,
          `ip` varchar(45) DEFAULT NULL,
          `city` varchar(45) DEFAULT NULL,
          `country` varchar(45) DEFAULT NULL,
          `payment_status` varchar(45) DEFAULT NULL,
          `payment_method` varchar(45) DEFAULT NULL,
          `payment_type` varchar(45) DEFAULT NULL,
          `currency` varchar(45) DEFAULT NULL,
          `payment_total` float DEFAULT NULL,
          `total_paid` float DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          `claimed_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    // Check if tables exist
    if ($wpdb->get_var("SHOW TABLES LIKE '$deleted_rides_table'") != $deleted_rides_table) {
        // Table does not exist, create it
        $sql = "CREATE TABLE $deleted_rides_table (
          `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
          `form_id` int(10) UNSIGNED DEFAULT NULL,
          `serial_number` int(10) UNSIGNED DEFAULT NULL,
          `response` longtext DEFAULT NULL,
          `source_url` varchar(255) DEFAULT NULL,
          `user_id` int(10) UNSIGNED DEFAULT NULL,
          `status` varchar(45) DEFAULT 'unread' COMMENT 'possible values: read, unread, trashed',
          `is_favourite` tinyint(1) NOT NULL DEFAULT 0,
          `browser` varchar(45) DEFAULT NULL,
          `device` varchar(45) DEFAULT NULL,
          `ip` varchar(45) DEFAULT NULL,
          `city` varchar(45) DEFAULT NULL,
          `country` varchar(45) DEFAULT NULL,
          `payment_status` varchar(45) DEFAULT NULL,
          `payment_method` varchar(45) DEFAULT NULL,
          `payment_type` varchar(45) DEFAULT NULL,
          `currency` varchar(45) DEFAULT NULL,
          `payment_total` float DEFAULT NULL,
          `total_paid` float DEFAULT NULL,
          `created_at` timestamp NULL DEFAULT NULL,
          `updated_at` timestamp NULL DEFAULT NULL,
          `claimed_by` int(11) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);
    }

    // Execute the SQL statement for altering table vt_fluentform_submissions
    $alter_sql = "ALTER TABLE vt_fluentform_submissions ADD claimed_by INT NULL AFTER updated_at;";
    $wpdb->query($alter_sql);
}

// Include the file containing the AJAX callback logic
require_once(plugin_dir_path(__FILE__) . 'ajax-handler.php');

// Include the file containing the datatable creation logic
require_once(plugin_dir_path(__FILE__) . 'datatable-handler.php');
