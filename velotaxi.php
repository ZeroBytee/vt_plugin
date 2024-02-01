<?php
/**
 * Plugin Name: velotaxi
 * Plugin URI: https://concept24.x10.mx/
 * Description: A plugin specially designed to handle the back-end of the velotaxi website.
 * Version: 1.3.2
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
// Enqueue scripts and styles
// Enqueue scripts and styles
function velotaxi_enqueue_scripts() {
    // Enqueue jQuery
    wp_enqueue_script('jquery');

    // Enqueue your main script
    wp_enqueue_script('velotaxi', plugin_dir_url(__FILE__) . 'velotaxi.php', array('jquery'), '1.0', true);

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
add_action('wp_enqueue_scripts', 'velotaxi_enqueue_scripts');

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
        echo "<tr data-details='" . esc_attr(json_encode($response)) . "'>
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
                <button id="claim-button" onclick="claimRide()">Claim</button>
            </div>
        </div>';


?>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var rows = document.querySelectorAll('.velotaxi-datatable tbody tr');
    
        rows.forEach(function (row) {
            row.addEventListener('click', function () {
                // Remove the "active" class from all rows
                rows.forEach(function (r) {
                    r.classList.remove('active');
                });
            
                // Access data-details attribute from the clicked row and add active class
                //var details2 = JSON.parse(document.querySelector('.table-row.active').getAttribute('data-details'));
                if (this) {
                    this.classList.add('active');
                    var details = JSON.parse(this.getAttribute('data-details'));
                    console.log('Clicked Row Data:', details);
                    console.log('Nonce Value:', claim_ride_vars.nonce);
                    openModal(details);
                } else {
                    console.error('Clicked row not found');
                }
            });
        });
    });

    function openModal(details) {
        var modal = document.getElementById('confirmRide');
        var modalContentDetails = document.getElementById('modal-content-details');
        var buttonContentDetails = document.getElementById('claim-button');
        // Set modal content based on details
        modalContentDetails.innerHTML = 
            '<p><strong>Phone Number:</strong> ' + details['numeric-field'] + '</p>' +
            '<p><strong>Pickup:</strong> ' + details['address_1']['address_line_1'] + '</p>' +
            '<p><strong>Destination:</strong> ' + details['address_2']['address_line_1'] + '</p>' +
            '<p><strong>Message:</strong> ' + details['message'] + '</p>';
    
        buttonContentDetails.onclick = function() { claimRide(details) };
    
        modal.style.display = 'block';
    }

    function closeModal() {
        var modal = document.getElementById('confirmRide');
        modal.style.display = 'none';
    }

    function claimRide(details) {
        var nonce = claim_ride_vars.nonce;
        var ajaxurl = claim_ride_vars.ajax_url;
    
        // Construct the data to send to the server
        var data = {
            action: 'claimRide_callback',
            details: details,
            user: claim_ride_vars.user_id,
            security: nonce
        };
    
        // Convert data to URL-encoded format
        var formData = new URLSearchParams();
        Object.keys(data).forEach(key => {
            formData.append(key, JSON.stringify(data[key]));
        });
    
        // AJAX call to send data to the server using fetch
        fetch(ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData,
        })
        .then(response => {
            // Log the response for debugging
            console.log('Response:', response);
            return response.json();
        })
        .then(result => {
            console.log('Result:', result);
            if (result.success) {
                closeModal();
            } else {
                console.error('Claim Ride Failed:', result.data ? result.data.message : 'Unknown error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            closeModal();
        });
    }
    
 
        //function claimRide(details) {
        //    var nonce = claim_ride_vars.nonce;
        //    var ajaxurl = claim_ride_vars.ajax_url;
    //
        //    // AJAX call to send data to the server
        //    var xhr = new XMLHttpRequest();
        //    xhr.open('POST', ajaxurl, true);
        //    //xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    //
        //    // Construct the data to send to the server
        //    var data = 'action=claimRide_callback' +
        //               '&details=' + encodeURIComponent(JSON.stringify(details)) +
        //               '&user=' + encodeURIComponent(claim_ride_vars.user_id) +
        //               '&security=' + nonce;
    //
        //    // Send the request
        //    xhr.send(data);
    //
        //    // Close the modal after claiming the ride
        //    closeModal();
        //}
        
    </script>


<?php

function claimRide_callback() {
    $nonce = isset($_POST['security']) ? $_POST['security'] : '';

    if (!wp_verify_nonce($nonce, 'claim_ride_nonce')) {
        wp_send_json_error(array('message' => 'Nonce verification failed.'));
    }

    // Simplified logic for debugging
    wp_send_json_success(array('message' => 'Ride claimed successfully.'));
}



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

    // Delete the row from the source table
    $source_table = $wpdb->prefix . 'fluentform_submissions';
    $wpdb->delete($source_table, array('response' => $details));
}

    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');