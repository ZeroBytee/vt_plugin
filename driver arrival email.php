<?php
/*
Plugin Name: Custom Email Notification
Description: Sends an email when triggered with specific information from the "fluentform_submissions" database table.
Version: 1.1
Author: Miro Schelkens
*/

add_action('vt_ride_claimed', 'send_custom_email_notification', 10, 3);

function send_custom_email_notification($response, $user, $time) {
    // Get all responses from the row where the ID equals the provided ID
    global $wpdb;
    $table_name = $wpdb->prefix . 'fluentform_submissions'; // Replace with your actual table name

    $first_name = $response['names']['first_name'];
    $last_name = $response['names']['last_name']; 
    $email = $response['email'];
    $when_time = $response['when']; 
    $service_type = $response['service'];

    if ($when_time == 'Future') {
        $subject = "Ride confirmed";
        $message = "Dear $first_name $last_name, a driver has claimed your ride, you can be sure to be picked up at your selected destination at the time you selected.<br>";
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM Logo"><br>';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo"><br>';
        
    } 
    elseif ($service_type == 'Toeristische toer') {
        $subject = "Driver on the way";
        $message = "Dear $first_name $last_name, thank you for ordering at VeloTaxi.\n";
        $message .= "You can be sure to be picked up at the selected destination.\n\n";
        $message .= "Kind regards\nVelotaxi\n";
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM Logo">';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo">';
    }
    else {
        $subject = "Driver on the way";
        $message = "Dear $first_name $last_name, thank you for ordering at VeloTaxi.\n";
        $message .= "Your driver will arrive at your location in $time minutes.\n\n";
        $message .= "Kind regards\nVelotaxi\n";
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM Logo">';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo">';
    }   

    $headers = array('Content-Type: text/html; charset=UTF-8');

    $email_sent = wp_mail($email, $subject, $message, $headers);
}
