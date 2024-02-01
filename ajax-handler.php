<?php
// AJAX callback logic
function claimRide_callback() {
    // Extract details parameter
    $details = json_decode(stripslashes($_POST['details']), true);

    // Other logic for claiming the ride
    move_row_to_rides_in_progress($details);

    // Send a success response
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

    // Destination table name with the new prefix
    $destination_table = $wpdb->prefix . 'rides_in_progress';

    // Insert the data into the destination table
    $wpdb->insert($destination_table, $insert_data);

    // Delete the row from the source table based on specific columns
    $source_table = $wpdb->prefix . 'fluentform_submissions';
    
    // Prepare and execute the SQL query for deletion
    $wpdb->delete(
        $source_table,
        array(
            'numeric_field' => $numeric_field,
            'address_1' => json_encode(array('address_line_1' => $address_1)),
            'address_2' => json_encode(array('address_line_1' => $address_2)),
            'message' => $message,
            // Add other fields as needed...
        ),
        array('%s', '%s', '%s', '%s') // Data format for the WHERE clause
    );

}