<?php
// AJAX callback logic
function claimRide_callback() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }

    $details = json_decode(stripslashes($_POST['details']), true);
    $user_id = json_decode(stripslashes($_POST['user']), true);

    // Check if a row with the same user_id already exists
    if ($user_id) {
        $row_exists = check_row_exists($user_id);
        if ($row_exists) {
            // Handle case where the row already exists
            wp_send_json_error(array('message' => 'Ride not claimed. User already has a ride in progress.'));
        } else {
            move_row_to_rides_in_progress($details, $user_id);
            wp_send_json_success(array('message' => 'Ride claimed successfully.'));
        }
    }
    
}
add_action('wp_ajax_claimRide_callback', 'claimRide_callback');

// Function to check if a row with the given user_id already exists
function check_row_exists($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rides_in_progress';
    $query = $wpdb->prepare("SELECT user_id FROM $table_name WHERE user_id = %d", $user_id);
    $existing_user_id = $wpdb->get_var($query);

    return !empty($existing_user_id);
}


function move_row_to_rides_in_progress($details, $user) {
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
        'user_id' => $user,
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
