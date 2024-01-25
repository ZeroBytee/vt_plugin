<?php
/**
 * Plugin Name: velotaxi
 * Plugin URI: https://concept24.x10.mx/
 * Description: A plugin specially designed to handle the back-end of the velotaxi website.
 * Version: 1.1.4
 * Author: Wout
 * Author URI: https://concept24.x10.mx/
 **/


// TODO:
// refresh datatable wnr er een nieuwe order is
// add functie om een rit te claimen
// -> rit naar de table "vt_rides_in_progress"
// pagina waar de driver zijn rit kan zien.
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
    wp_enqueue_style('velotaxi-datatable-styles', plugin_dir_url(__FILE__) . 'styles.css');
}
add_action('wp_enqueue_scripts', 'velotaxi_datatable_styles');

// Create the datatable
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

            .show-details-btn {
                background-color: #3498db; /* Blue button */
                color: #fff; /* White text */
                border: none;
                padding: 8px 12px;
                border-radius: 5px; /* Rounded corners for the button */
                cursor: pointer;
                transition: background-color 0.3s ease;
            }

            .show-details-btn:hover {
                background-color: #2980b9; /* Darker blue on hover */
            }

            /* Adjustments for rounded corners */
            .velotaxi-datatable th, .velotaxi-datatable td, .velotaxi-datatable tbody tr:hover {
                border-radius: 0; /* Remove default border-radius */
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

    // Include JavaScript for the popup and AJAX
    ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var rows = document.querySelectorAll('.velotaxi-datatable tbody tr');

            rows.forEach(function (row) {
                row.addEventListener('click', function () {
                    // Access data-details attribute from the clicked row
                    var details = JSON.parse(this.getAttribute('data-details'));
                    console.log('Clicked Row Data:', details);

                    // Add your logic here to handle the click event
                });
            });
        });
    </script>
    <?php

    return ob_get_clean(); // Return the buffered content
}

// Register the shortcode
add_shortcode('velotaxi_neworders', 'createDataTable');