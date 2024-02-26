<?php

function claimCheck($user_id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'rides_in_progress';
    $query = $wpdb->prepare("SELECT user_id FROM $table_name WHERE user_id = %d", $user_id);
    $existing_user_id = $wpdb->get_var($query);

    return !empty($existing_user_id);
}


function createDataTable() {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $claimed_ride = claimCheck($user_id);

    if ($claimed_ride) {
        global $wpdb;
    
        $table_name = $wpdb->prefix . 'rides_in_progress';
        $data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE user_id = %d", $user_id), ARRAY_A);

        ob_start();
        
        echo '<div id="claimed-ride-details">';
        echo '<p><strong>Ride ID:</strong> ' . esc_html($data['id']) . '</p>';
        echo '<p><strong>Phone Number:</strong> ' . esc_html($data['numeric_field']) . '</p>';
        echo '<p><strong>Pickup:</strong> ' . esc_html($data['address_1']) . '</p>';
        echo '<p><strong>Destination:</strong> ' . esc_html($data['address_2']) . '</p>';
        echo '<p><strong>Message:</strong> ' . esc_html($data['message']) . '</p>';
        echo '</div>';

        return ob_get_clean();
    } else {
        global $wpdb;

        // Get table name
        $table_name = $wpdb->prefix . 'fluentform_submissions';

        // Get data from the database
        $data = $wpdb->get_results(
            $wpdb->prepare("SELECT * FROM $table_name WHERE form_id = %d", 6),
            ARRAY_A
        );

        // Start output buffering
        ob_start();

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
                    background-color: #ffffff; /* White row */
                }

                .velotaxi-datatable tbody tr:nth-child(even) {
                    background-color: #ffffff; /* Gray row */ #ddd old 
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
                    background-color: rgba(0, 0, 0, 0.4);
                    margin-left: auto;
                    margin-right: auto;
                }

                .modal-content {
                    background-color: #fefefe;
                    margin: 10% auto; /* Adjust the top margin to center vertically */
                    padding: 20px;
                    border: 1px solid #888;
                    width: 60%; /* Adjust the width as needed */
                    max-width: 600px; /* Add a maximum width for larger screens */
                    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    margin-left: auto;
                    margin-right: auto;
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

                .alert.error {
                  padding: 20px;
                  background-color: #f44336; /* Red */
                  color: white;
                  margin-bottom: 15px;
                  opacity: 1;
                  transition: opacity 0.6s; /* 600ms to fade out */
                }

                .alert.success {
                    padding: 20px;
                    background-color: #4CAF50; /* Green */
                    color: white;
                    margin-bottom: 15px;
                    opacity: 1;
                    transition: opacity 0.6s; /* 600ms to fade out */
                  }

                .closebtn {
                  margin-left: 15px;
                  color: white;
                  font-weight: bold;
                  float: right;
                  font-size: 22px;
                  line-height: 20px;
                  cursor: pointer;
                  transition: 0.3s;
                }

                .closebtn:hover {
                  color: black;
                }

                /* Add or modify styles as needed */
                .velotaxi-datatable tbody tr.claimed-by-you {
                    background-color: #4CAF50 !important; /* Green background for claimed by you */
                    color: white;
                }

                .velotaxi-datatable tbody tr.claimed-by-you {
                    background-color: #4CAF50 !important; /* Green background for claimed by you */
                    color: white;
                }
                
                .velotaxi-datatable tbody tr.claimed-by-others {
                    background-color: #FF0000 !important; /* Red background for claimed by others */
                    color: white;
                }
                
                .velotaxi-datatable tbody tr.unclaimed {
                    /* Styles for unclaimed rows */
                    background-color: #ddd; /* Default background color for unclaimed rows */
                    color: black; /* Default text color for unclaimed rows */
                }
                
                .velotaxi-datatable tbody tr.active {
                    background-color: #FF0000; /* Red background for claimed by others */
                    color: white;
                }
                
                .velotaxi-datatable tbody tr:nth-child(odd) {
                    background-color: #ddd; /* Gray background for unclaimed rows */
                }
                
                .velotaxi-datatable tbody tr:nth-child(even) {
                    background-color: #ffffff; /* White background for unclaimed rows */
                }

            </style>';

        echo '<div id="alert-container"></div>';

        // Start HTML for the datatable with added styles
        echo '<div class="velotaxi-datatable-container">
                <table class="velotaxi-datatable">
                    <thead>
                        <tr>
                            <th class="service-col">Service Type</th>
                            <th class="name-col">First name</th>
                            <th class="pickup-col">Pickup</th>
                            <th class="destination-col">Destination</th>
                            <th class="message-col">Message</th>
                        </tr>
                    </thead>
                    <tbody>';

        // Loop through data and display values
        foreach ($data as $entry) {
            $response = json_decode($entry['response'], true);

             // Determine if the row is claimed by the current user
            $claimed_by_user = $entry['claimed_by'] == $user_id;
                
            // Set classes based on claimed status
            $row_classes = $claimed_by_user ? 'claimed-by-you' : ($entry['claimed_by'] ? 'claimed-by-others' : 'unclaimed');

            $numeric_field = $response['input_text']; // Change this to the correct field
            $service_type = $response['service']; // Change this to the correct field
            $first_name = $response['names']['first_name']; // Change this to the correct field

            // Determine from and to places based on service type
            if ($service_type === "Single City Ride") {
                $address_1 = isset($response['from_place']) ? $response['from_place'] : ''; // Check if 'from_place' exists
                $address_2 = isset($response['to_place']) ? $response['to_place'] : ''; // Check if 'to_place' exists
            } elseif ($service_type === "Reserve timeslot") {
                $address_1 = isset($response['starting_place']) ? $response['starting_place'] : 'N/A'; // Check if 'starting_place' exists
                $address_2 = 'N/A';
            } else {
                // For other services, set to N/A
                $address_1 = 'N/A';
                $address_2 = 'N/A';
            }

            $message = $response['more_info']; // Change this to the correct field

            echo "<tr id='vt-row-test' class='" . esc_attr($row_classes) . "' data-details='" . esc_attr(json_encode($response)) . "'>
                    <td class='service-col'>$service_type</td>
                    <td class='name-col'>$first_name</td>
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

        return ob_get_clean(); // Return the buffered content
    }
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');