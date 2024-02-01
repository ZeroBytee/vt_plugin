<?php
// AJAX callback logic
function claimRide_callback() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }

    $details = json_decode(stripslashes($_POST['details']), true);
    move_row_to_rides_in_progress($details);

    wp_send_json_success(array('message' => 'Ride claimed successfully.'));
    
}
add_action('wp_ajax_claimRide_callback', 'claimRide_callback');


function move_row_to_rides_in_progress($details) {
    global $wpdb;

    // Extract relevant data from $details (adjust based on your actual data structure)
    $numeric_field = isset($details['numeric-field']) ? $details['numeric-field'] : '';
    $address_1 = isset($details['address_1']['address_line_1']) ? $details['address_1']['address_line_1'] : '';
    $address_2 = isset($details['address_2']['address_line_1']) ? $details['address_2']['address_line_1'] : '';
    $message = isset($details['message']) ? $details['message'] : '';
    $gdpr = isset($details['gdpr-agreement']) ? $details['gdpr-agreement'] : '';

    // Prepare data for insertion
    $insert_data = array(
        'numeric_field' => $numeric_field,
        'address_1' => $address_1,
        'address_2' => $address_2,
        'message' => $message,
        'gdpr' => $gdpr,
        // Add other columns as needed...
    );

    // insert into new table
    $destination_table = $wpdb->prefix . 'rides_in_progress';
    $wpdb->insert($destination_table, $insert_data);

    // delete from old table
    $table_name = $wpdb->prefix . 'fluentform_submissions';
    $wpdb->delete(
        $table_name,
        array(
            'response' => json_encode($details),
        ),
        array('%s') // Data format for the WHERE clause
    );
}
