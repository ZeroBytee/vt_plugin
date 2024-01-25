<?php
/**
 * Plugin Name: velotaxi
 * Plugin URI: https://concept24.x10.mx/
 * Description: A plugin specially designed to handle the back-end of the velotaxi website.
 * Version: 1.2.1
 * Author: Wout
 * Author URI: https://concept24.x10.mx/
 **/


// TODO:
// refresh datatable wnr er een nieuwe order is --
// add functie om een rit te claimen --
// -> rit naar de table "vt_rides_in_progress" -- 
// pagina waar de driver zijn rit kan zien. --
// eventueel een pagina waar de klant de status kan volgen, wanneer de chauffeur er zal zijn etc.
// driver/klant (moet besproken worden) kan rit markeren als gedaan
// rit klaar -> verplaats naar "vt_completed_rides"
// admin pagina voor alle ritten die klaar zijn



// adds the CSS stylesheet
function velotaxi_enqueue_scripts() {
    wp_enqueue_script('jquery');
}
add_action('wp_enqueue_scripts', 'velotaxi_enqueue_scripts');


function velotaxi_datatable_styles() {
    wp_enqueue_style('velotaxi', plugin_dir_url(__FILE__) . 'styles.css');
}
add_action('wp_enqueue_scripts', 'velotaxi_datatable_styles');

// saves the user_id into a variable we can use outside of the PHP
function enqueue_script_and_localize_data() {
    // Get the current user ID
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Localize script with user ID and other variables
    wp_localize_script('velotaxi', 'claim_ride_vars', array(
        'nonce' => wp_create_nonce('claim_ride_nonce'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'user_id' => $user_id,
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_script_and_localize_data');

// Create the datatable for new ASAP orders
function createDataTable() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'fluentform_submissions';
    $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    ob_start(); // Start output buffering

    // main styling
    echo '<style>
                /* Add or modify styles as needed */
            .velotaxi-datatable-container {
                overflow-x: auto;
            }

            .velotaxi-datatable {
            
                border-collapse: collapse;
                width: 100%;
                margin-top: 20px;
                border: 1px solid #ddd; /* Adjust outline thickness */
                border-radius: 8px; /* Rounded corners */
                -moz-border-radius: 8px;
                padding: 5px;
            }

            thead {
                border-color: inherit;
                display: table-header-group;
                vertical-align: middle;
            }

            .velotaxi-datatable th, .velotaxi-datatable td {
                padding: 10px;
                text-align: left;
                border: 1px solid #ddd; /* Adjust grid line thickness */
            }

            .velotaxi-datatable th {
                background-color: #3498db; /* Blue header */
                color: #fff; /* White text */
                border-bottom: 1px solid #ddd; /* Adjust border-bottom thickness */
            }

            .velotaxi-datatable tbody tr:nth-child(odd) {
                background-color: #f9f9f9; /* White row */
            }

            .velotaxi-datatable tbody tr:nth-child(even) {
                background-color: #ddd; /* Gray row */
            }

            .velotaxi-datatable tbody tr:hover {
                background-color: #bdc3c7; /* Gray hover effect */
            }

            /* Adjustments for rounded corners */
            .velotaxi-datatable th, .velotaxi-datatable td, .velotaxi-datatable tbody tr:hover {
                border-radius: 0; /* Remove default border-radius */
            }

            /* Modal styles */
            .modal {
                display: none;
                position: fixed;
                z-index: 1;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                overflow: auto;
                background-color: rgb(0, 0, 0);
                background-color: rgba(0, 0, 0, 0.4);
            }

            .modal-content {
                background-color: #fefefe;
                margin: 10% auto; /* Adjust the top margin to center vertically */
                padding: 20px;
                border: 1px solid #888;
                width: 80%;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Add a slight shadow for visual appeal */
            }

            .close {
                color: #aaa;
                float: right;
                font-size: 28px;
                font-weight: bold;
            }

            .close:hover,
            .close:focus {
                color: black;
                text-decoration: none;
                cursor: pointer;
            }

        </style>';

    // Start HTML for the datatable with added styles
    echo '<div class="velotaxi-datatable-container">
            <table class="velotaxi-datatable">
                <thead>
                    <tr>
                        <th class="phone-col">Phone number</th>
                        <th class="pickup-col">Pickup</th>
                        <th class="destination-col">Destination</th>
                        <th class="message-col">Message</th>
                    </tr>
                </thead>
                <tbody>';

    // Loop through data and display values
    foreach ($data as $entry) {
        $response = json_decode($entry['response'], true);

        $numeric_field = $response['numeric-field'];
        $address_1 = isset($response['address_1']['address_line_1']) ? $response['address_1']['address_line_1'] : '';
        $address_2 = isset($response['address_2']['address_line_1']) ? $response['address_2']['address_line_1'] : '';
        $message = isset($response['message']) ? $response['message'] : '';

        // Display values in table rows
        echo "<tr data-details='" . esc_attr(json_encode($entry)) . "'>
                <td class='phone-col'>$numeric_field</td>
                <td class='pickup-col'>$address_1</td>
                <td class='destination-col'>$address_2</td>
                <td class='message-col'>$message</td>
            </tr>";
    }

    // Close the table
    echo '</tbody></table></div>';


    // Modal HTML
    echo '<div id="confirmRide" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2>Claim Ride?</h2>
                <div id="modal-content-details"></div>
                <button onclick="claimRide()">Claim</button>
            </div>
        </div>';
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rows = document.querySelectorAll('.velotaxi-datatable tbody tr');

            rows.forEach(function (row) {
                row.addEventListener('click', function () {
                    // Access data-details attribute from the clicked row
                    var details = JSON.parse(this.getAttribute('data-details'));
                    console.log('Clicked Row Data:', details['response']);
                    openModal(details['response']);
                    
                });
            });
        });

        function openModal(details) {
            var modal = document.getElementById('confirmRide');
            var modalContentDetails = document.getElementById('modal-content-details');

            // Set modal content based on details
            modalContentDetails.innerHTML = `
                <p><strong>Phone Number:</strong> ${details['numeric-field']}</p>
                <p><strong>Pickup:</strong> ${details['address_1']['address_line_1']}</p>
                <p><strong>Destination:</strong> ${details['address_2']['address_line_1']}</p>
                <p><strong>Message:</strong> ${details['message']}</p>
            `;

            modal.style.display = 'block';
        }

        function closeModal() {
            var modal = document.getElementById('confirmRide');
            modal.style.display = 'none';
        }

        function claimRide() {
            // grabs all the info about the chosen ride
            var details = JSON.parse(document.querySelector('.table-row.active').getAttribute('data-details'));

            // AJAX call to send data to the server
            var xhr = new XMLHttpRequest();
            xhr.open('POST', ajaxurl, true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');

            // Construct the data to send to the server
            var data = {
                action: 'claimRide_callback',
                details: JSON.stringify(details), // Send details as a JSON string
                user: claim_ride_vars.user_id, // Include the user ID
            };
        
            // Send the request
            xhr.send('data=' + JSON.stringify(data));

            // Close the modal after claiming the ride
            closeModal();
        }
    </script>

    <?php

function claimRide_callback() {
    check_ajax_referer('claim_ride_nonce', 'security');

    if (isset($_POST['data'])) {
        $data = json_decode(stripslashes($_POST['data']), true);
        $entry = json_decode(stripslashes($_POST['entry']), true);

        // Extract details from the data
        $details = isset($data['details']) ? $data['details'] : array();

        // Move the row from 'fluentform_submissions' to 'vt_rides_in_progress'
        move_row_to_rides_in_progress($details, $entry);

        // Handle other logic as needed...

        wp_send_json_success(array('message' => 'Ride claimed successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Invalid data.'));
    }
}

function move_row_to_rides_in_progress($details, $entry) {
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

    // Delete the row from the source table
    $source_table = $wpdb->prefix . 'fluentform_submissions';
    $wpdb->delete($source_table, array('column_name' => $value_to_match));
}

    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');