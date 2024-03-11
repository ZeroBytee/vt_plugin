<?php

function createDataTable() {
    global $wpdb;

    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    $is_admin = current_user_can('administrator'); 
    
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
            }
        
            .modal-content {
                background-color: #fefefe;
                margin: 10% auto;
                padding: 20px;
                border: 1px solid #888;
                width: 80%; /* Adjusted width for better responsiveness */
                max-width: calc(100% - 20%); /* Ensure 10% gap on both sides */
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                border-radius: 10px;
                text-align: center;
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
                background-color: #82bb60 !important; /* Green background for claimed by you */
                color: white;
            }
            
            .velotaxi-datatable tbody tr.claimed-by-others {
                background-color: #e74747 !important; /* Red background for claimed by others */
                color: white;
            }
            
            .velotaxi-datatable tbody tr.unclaimed {
                /* Styles for unclaimed rows */
                background-color: #ddd; /* Default background color for unclaimed rows */
                color: black; /* Default text color for unclaimed rows */
            }
            
            .velotaxi-datatable tbody tr.active {
                background-color: #fff;
                color: black;
            }
            
            /* .velotaxi-datatable tbody tr:nth-child(odd) {
                background-color: #ddd; /* Gray background for unclaimed rows */
            }
            
            .velotaxi-datatable tbody tr:nth-child(even) {
                background-color: #fff; /* White background for unclaimed rows */
                color: black;
            } */
        </style>';
    echo '<div id="alert-container"></div>';
    // Modal HTML
    echo '<div id="confirmRide" class="modal" style="display: none; position: fixed; z-index: 2; left: 50%; top: 50%; transform: translate(-50%, -50%);">
    <div class="modal-content" style="border-radius: 10px; text-align: center; max-width: 400px; margin: 10% auto; padding: 20px; background-color: #fefefe; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
        <span class="close" onclick="closeModal()" style="float: right; font-size: 20px; font-weight: bold; cursor: pointer;">&times;</span>
        <h2 style="font-family: \'Arial\', sans-serif;">Claim Ride?</h2>
        <div id="modal-content-details"></div>
        
        <label for="timeframe" style="font-family: \'Arial\', sans-serif; margin-top: 10px;">Select Timeframe:</label>
        <select id="timeframe" name="timeframe" style="font-family: \'Arial\', sans-serif; padding: 8px; border-radius: 5px;">
            <option value="5">5 min</option>
            <option value="10">10 min</option>
            <option value="15">15 min</option>
            <option value="20">20 min</option>
            <option value="30">30 min</option>
        </select>
        <!-- driver buttons -->
        
        <button id="claim-button" onclick="claimRide()" style="width: 100%; background-color: #4CAF50; color: white; border: none; padding: 10px; border-radius: 5px; font-family: \'Arial\', sans-serif; margin-top: 15px;">Claim</button>
        <button id="unclaim-button" onclick="unclaimRide()" style="display: none; width: 100%; background-color: #FF0000; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 5px; font-family: \'Arial\', sans-serif;">Unclaim</button>
        <button id="fulfill-button" onclick="fulfillRide()" style="display: none; width: 100%; background-color: #abef89; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 5px; font-family: \'Arial\', sans-serif;">I have fulfilled this request.</button>
        <!-- manager buttons -->
        <hr style="border: 1px solid #ddd; margin: 15px 0;">

        <h3 style="font-family: \'Arial\', sans-serif; color: #333;">Manager Buttons</h3>
        <button id="delete-button" onclick="deleteRide()" style="display: none; width: 100%; background-color: #FF0000; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 5px; font-family: \'Arial\', sans-serif;">Delete</button>
        <button id="edit-button" onclick="editRide()" style="display: none; width: 100%; background-color: #3498db; color: white; border: none; padding: 10px; margin-top: 10px; border-radius: 5px; font-family: \'Arial\', sans-serif;">Edit</button>
    </div>
    </div>';
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
         // Check if 'more_info' key exists before accessing it
        $message = isset($response['more_info']) ? $response['more_info'] : "";
         // Pass the claimed status along with details when calling openModal
        echo "<tr id='vt-row-test' class='" . esc_attr($row_classes) . "' data-details='" . esc_attr(json_encode($response)) . "' data-claimed='" . esc_attr($claimed_by_user ? 'true' : 'false') . "' entry ='" . esc_attr(json_encode($entry)) . "' driver ='" . esc_attr($user_id) . "' admin ='" . esc_attr($is_admin ? 'true' : 'false') . "'>
            <td class='service-col'>$service_type</td>
            <td class='name-col'>$first_name</td>
            <td class='pickup-col'>$address_1</td>
            <td class='destination-col'>$address_2</td>
            <td class='message-col'>$message</td>
        </tr>";
    }
    // Close the table
    echo '</tbody></table></div>';
    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');