<?php
// Create the datatable for new ASAP orders
function createDataTable() {
    global $wpdb;

    // Get table name
    $table_name = $wpdb->prefix . 'fluentform_submissions';

    // Get data from the database
    $data = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

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

    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');
