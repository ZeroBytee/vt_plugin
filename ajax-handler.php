<?php
// AJAX callback logic
function claimRide_callback() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }


    $details = json_decode(stripslashes($_POST['details']), true);
    $user_id = json_decode(stripslashes($_POST['user']), true);
    $time = stripslashes($_POST['time']);

    // Check if a row with the same user_id already exists
    if ($user_id) {
        mark_ride_as_claimed($details, $user_id, $time);
        wp_send_json_success(array('message' => 'Ride claimed successfully.' . $time));
    }
}

function mark_ride_as_claimed($details, $user, $time) {
    global $wpdb;

    // Update the 'claimed_by' field in the old table
    $table_name = $wpdb->prefix . 'fluentform_submissions';
    $wpdb->update(
        $table_name,
        array('claimed_by' => $user),
        array('response' => json_encode($details)),
        array('%d'), // Format for the 'claimed_by' field
        array('%s')  // Format for the WHERE clause
    );

    do_action('vt_ride_claimed', $details, $user, $time);
}

//function check_row_claimed($details, $user_id) {
//    global $wpdb;
//
//    $table_name = $wpdb->prefix . 'fluentform_submissions';
//    $response = json_encode($details);
//
//    // Check if the ride is already claimed by someone else
//    $query = $wpdb->prepare("SELECT claimed_by FROM $table_name WHERE response = %s AND claimed_by IS NOT NULL AND claimed_by != %d", $response, $user_id);
//    $claimed_by_others = $wpdb->get_var($query);
//
//    return !empty($claimed_by_others);
//}

// Function to check if a row with the given user_id already exists
function check_row_exists($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rides_in_progress';
    $query = $wpdb->prepare("SELECT user_id FROM $table_name WHERE user_id = %d", $user_id);
    $existing_user_id = $wpdb->get_var($query);

    return !empty($existing_user_id);
}

function unclaimRide_callback() {

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $is_admin = current_user_can('administrator'); 

    $details = json_decode(stripslashes($_POST['details']), true);
    $user_id = json_decode(stripslashes($_POST['user']), true);
    $ride_id = json_decode(stripslashes($_POST['ride_id']), true);
    $entry = json_decode(stripslashes($_POST['entry']), true);

    // Check if the ride is claimed by the current user
    if ($user_id) {
        $claimed_by_user = $entry['claimed_by'] == $user_id;

        if ($claimed_by_user || $is_admin) {
            // Unclaim the ride
            unclaim_ride($ride_id);
            wp_send_json_success(array('message' => 'Ride successfully unclaimed.'));
        } else {
            // Handle case where the ride is not claimed by the current user
            wp_send_json_error(array('message' => 'You cannot unclaim a ride claimed by someone else.'));
        }
    }
}
add_action('wp_ajax_claimRide_callback', 'claimRide_callback');

function unclaim_ride($ride_id) {
    global $wpdb;

    // Update the 'claimed_by' field to null in the old table
    $table_name = $wpdb->prefix . 'fluentform_submissions';
    $wpdb->update(
        $table_name,
        array('claimed_by' => null),
        array('id' => $ride_id),
        array('%s'), // Format for the 'user_id' field
        array('%d')  // Format for the WHERE clause
    );
}

add_action('wp_ajax_unclaimRide_callback', 'unclaimRide_callback');




function move_row_to_rides_in_progress($details, $user) {
    global $wpdb;

    // Extract relevant data from $details (adjust based on your actual data structure)
    $phone = isset($details['input_text']) ? $details['input_text'] : '';
    $from = isset($details['from_place']) ? $details['from_place'] : '';
    $to = isset($details['to_place']) ? $details['to_place'] : '';
    $message = isset($details['more_info']) ? $details['more_info'] : '';

    // Additional fields based on service type
    $when = isset($details['when']) ? $details['when'] : '';
    $from_date = isset($details['from_date']) ? $details['from_date'] : '';
    $to_date = isset($details['to_date']) ? $details['to_date'] : '';
    
    // Prepare data for insertion
    $insert_data = array(
        'phone' => $phone,
        'from' => $from,
        'to' => $to,
        'message' => $message,
        'when' => $when,
        'from_date' => $from_date,
        'to_date' => $to_date,
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

function fulfill_callback() {
    global $wpdb;

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }
    
    $entry = json_decode(stripslashes($_POST['entry']), true);


    $destination_table = $wpdb->prefix . 'completed_rides';
    if ($wpdb->insert($destination_table, $entry)) {
        // Successfully inserted into completed_rides table
        $table_name = $wpdb->prefix . 'fluentform_submissions';
        $deleted = $wpdb->delete(
            $table_name,
            array('id' => $entry['id']),
            array('%s') // Data format for the WHERE clause
        );
        
        if ($deleted !== false) {
            // Successfully deleted from original table
            wp_send_json_success(array('message' => 'Ride fulfilled successfully.'));
        } else {
            // Error occurred while deleting ride
            wp_send_json_error(array('message' => 'Error occurred while removing the ride.'));
        }
    } else {
        // Error occurred while inserting into completed_rides table
        wp_send_json_error(array('message' => 'Error fulfilling the ride: ' . $wpdb->last_error));
    }
}

add_action('wp_ajax_fulfill_callback', 'fulfill_callback');

function managerDeleteRide_callback() {
    global $wpdb;

    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce.'));
    }

    $current_user = wp_get_current_user();
    $is_admin = current_user_can('administrator'); 

    // Check if the current user is an administrator
    if ($is_admin) {
        $ride_id = json_decode(stripslashes($_POST['ride_id']), true);
        $details = json_decode(stripslashes($_POST['details']), true);
        $entry = json_decode(stripslashes($_POST['entry']), true);

        // Delete the ride from the database
        $destination_table = $wpdb->prefix . 'deleted_rides';
        $wpdb->insert($destination_table, $entry);

        $table_name = $wpdb->prefix . 'fluentform_submissions';
        $deleted = $wpdb->delete(
            $table_name,
            array('id' => $ride_id),
            array('%s') // Data format for the WHERE clause
        );
        if ($deleted !== false) {
            // Ride successfully deleted
            wp_send_json_success(array('message' => 'Ride removed successfully.'));
        } else {
            // Error occurred while deleting ride
            wp_send_json_error(array('message' => 'Error occurred while removing the ride.'));
        }
    } else {
        // User is not an administrator
        wp_send_json_error(array('message' => 'You are not allowed to do this.'));
    }
}

add_action('wp_ajax_managerDeleteRide_callback', 'managerDeleteRide_callback');