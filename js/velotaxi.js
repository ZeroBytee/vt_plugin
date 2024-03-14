document.addEventListener('DOMContentLoaded', function () {
    var rows = document.querySelectorAll('.velotaxi-datatable tbody tr');

    rows.forEach(function (row) {
        row.addEventListener('click', function () {
            rows.forEach(function (r) {
                r.classList.remove('active');
            });

            if (this) {
                this.classList.add('active');
                var details = JSON.parse(this.getAttribute('data-details'));
                console.log('Clicked Row Data:', details);

                var claimedStatus = this.getAttribute('data-claimed');
                var entry = JSON.parse(this.getAttribute('entry'));
                var driver = this.getAttribute('driver');
                var admin = this.getAttribute('admin');
                console.log('claimed: ', claimedStatus)
                console.log('admin: ', admin)
                openModal(details, claimedStatus, entry, driver, admin);
            } else {
                console.error('Clicked row not found');
            }
        });
    });
});

// general driver modal
function openModal(details, claimedStatus, entry, driver, admin) {
    var modal = document.getElementById('confirmRide');
    var modalContentDetails = document.getElementById('modal-content-details');

    // driver buttons
    var claimButton = document.getElementById('claim-button');
    var unclaimButton = document.getElementById('unclaim-button');
    var fulfillButton = document.getElementById('fulfill-button');

    // manager buttons
    var manager_delete = document.getElementById('delete-button');
    var manager_edit = document.getElementById('edit-button');

    var phoneNumber = details['input_text'];
    var service = details['service'];
    var startingPlace = details['starting_place'] || details['from_place'];
    var startingPlace2 = details['starting_place_2'];
    var toPlace = details['to_place'] || "N/A";
    var fromPlaceLabel = service === 'Reserve timeslot' ? 'Starting Place' : 'From Place';
    var when = details['when']

    if (!startingPlace) {
        startingPlace = startingPlace2;
    }

    // Additional fields based on service type
    var additionalFields = '';
    if (service === 'Single City Ride') {
        additionalFields += '<p><strong>When:</strong> ' + details['when'] + '</p>';
    } else if (service === 'Reserve timeslot') {
        additionalFields += '<p><strong>From Date:</strong> ' + details['from_date'] + '</p>' +
                            '<p><strong>To Date:</strong> ' + details['to_date'] + '</p>';
    }
    if (when == "Future") {
        additionalFields += '<p><strong>Time:</strong> ' + details['when_time'] + '</p>';
    }

    modalContentDetails.innerHTML = 
        '<p><strong>Phone Number:</strong> ' + phoneNumber + '</p>' +
        '<p><strong>Service:</strong> ' + service + '</p>' +
        '<p><strong>' + fromPlaceLabel + ':</strong> ' + startingPlace + '</p>' +
        '<p><strong>To Place:</strong> ' + toPlace + '</p>' +
        additionalFields +
        '<p><strong>Message:</strong> ' + details['more_info'] + '</p>';

    // Determine whether to show "Claim" or "Unclaim" button
    
    // enables admin buttons
    if (admin) {
        manager_delete.style.display = 'block';
        manager_edit.style.display = 'block';

        unclaimButton.style.display = 'block';
        claimButton.style.display = 'block';
        fulfillButton.style.display = 'block';
    } else {
        if (claimedStatus === 'true' || claimedStatus === true) {
            unclaimButton.style.display = 'block';
            claimButton.style.display = 'none';
            fulfillButton.style.display = 'block';
        } else {
            unclaimButton.style.display = 'none';
            claimButton.style.display = 'block';
            fulfillButton.style.display = 'none';
        }
    }
    
    claimButton.onclick = function() { claimRide(details, entry, admin) };
    unclaimButton.onclick = function() { unclaimRide(details, entry, admin) };
    fulfillButton.onclick = function() {fulfillRide (entry)};

    manager_delete.onclick = function() { deleteRide(details, entry, admin)};
    manager_edit.onclick = function() { editRide(details)};

    modal.style.display = 'block';
}


function closeModal() {
    var modal = document.getElementById('confirmRide');
    modal.style.display = 'none';
}

function showNotification(message, type = "success") {
    createAlert(message, type);
}

function createAlert(message, type) {
    // Create a new div element
    var alertDiv = document.createElement("div");
  
    // Set class based on the alert type (e.g., 'success', 'info', 'warning', 'error')
    alertDiv.className = "alert " + type;
  
    // Set the alert message
    alertDiv.innerHTML = '<span class="closebtn" onclick="closeAlert(this)">&times;</span>' + message;
  
    // Append the alert to the container
    document.getElementById("alert-container").appendChild(alertDiv);
  
    // Automatically remove the alert after a few seconds (adjust as needed)
    setTimeout(function () {
      closeAlert(alertDiv.querySelector(".closebtn"));
    }, 8000);
}

function closeAlert(closeButton) {
    // Get the parent of <span class="closebtn"> (<div class="alert">)
    var alertDiv = closeButton.parentElement;
  
    // Set the opacity of div to 0 (transparent)
    alertDiv.style.opacity = "0";
  
    // Hide the div after 600ms (the same amount of milliseconds it takes to fade out)
    setTimeout(function () {
      alertDiv.style.display = "none";
    }, 1000);
}



// driver operations
function claimRide(details, entry, admin) {
    var ajaxurl = claim_ride_vars.ajax_url;
    var selectedTime = document.getElementById("timeframe").value;

    var claimed_by = entry['claimed_by']

    if (!claimed_by) {
        var data = {
            action: 'claimRide_callback',
            details: JSON.stringify(details),
            user: claim_ride_vars.user_id,
            entry: entry,
            time: selectedTime,
            nonce: claim_ride_vars.nonce
        };
    
        jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
    
            if (response.success) {
                closeModal();
                createAlert("Successfully claimed the ride!", "success");
                // Change the color of the claimed row to green
                document.querySelector('.velotaxi-datatable tbody tr.active').classList.add('claimed-by-you');
            } else {
                console.error(response.data['message']);
            }
        });
    } else {
        console.error('Ride already claimed by someone else');
    } 
}

function unclaimRide(details, entry, admin) {
    var ajaxurl = claim_ride_vars.ajax_url;

    var claimed_by = entry['claimed_by'];
    var user_id = claim_ride_vars.user_id;

    if (claimed_by == user_id || admin) {
        var data = {
            action: 'unclaimRide_callback',
            details: JSON.stringify(details),
            user: user_id,
            ride_id: entry['id'], // Pass the ride ID to the server
            entry: JSON.stringify(entry), // full entry of the row
            nonce: claim_ride_vars.nonce
        };
    
        jQuery.post(ajaxurl, data, function(response) {
            if (response.success) {
                closeModal();
                createAlert("Successfully unclaimed the ride!", "success");
                // Change the color of the claimed row back to original color
                document.querySelector('.velotaxi-datatable tbody tr.active').classList.remove('claimed-by-you');
                document.querySelector('.velotaxi-datatable tbody tr.active').classList.remove('claimed-by-others');
            } else {
                console.error(response.data['message']);
            }
        });
        closeModal();
    } else {
        console.error("You can't unclaim a ride of another driver!");
        createAlert("Error with unclaiming the ride!", "error");
    }    
}

function fulfillRide(entry){
    var ajaxurl = claim_ride_vars.ajax_url;

    var claimed_by = entry['claimed_by'];
    var user_id = claim_ride_vars.user_id;

    if (claimed_by == user_id || admin) {
        var data = {
            action: 'fulfill_callback',
            user: claim_ride_vars.user_id,
            entry: JSON.stringify(entry),
            nonce: claim_ride_vars.nonce
        };
    
        jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
    
            if (response.success) {
                closeModal();
                createAlert("Successfully claimed the ride!", "success");
                // Change the color of the claimed row to green
                document.querySelector('.velotaxi-datatable tbody tr.active').classList.add('claimed-by-you');
            } else {
                console.error('Unknown error occurred.');
                console.error(response);
            }
        });
    }
}


// admin operations & modal
function deleteRide(details, entry, admin) {
    var ajaxurl = claim_ride_vars.ajax_url;

    if (admin) {
        var data = {
            action: 'managerDeleteRide_callback',
            details: JSON.stringify(details),
            entry: entry,
            ride_id: entry['id'], // Pass the ride ID to the server,
            nonce: claim_ride_vars.nonce
        };
        
        jQuery.post(ajaxurl, data, function(response) {
            console.log(response);
            if (response.success) {
                closeModal();
                createAlert("Successfully removed the ride!", "success");
                //document.querySelector('.velotaxi-datatable tbody tr.active').style.display = 'none';
            } else {
                console.error(response);
            }
        });
        closeModal();
    } else {
        console.error("You are not allowed to do this!", response);
        createAlert("You are not allowed to do this!", "error");
    } 
}

function editRide(details) {

    // Create input fields for each variable in the response field
    var adminEditForm = document.getElementById('admin-edit-form');
    adminEditForm.innerHTML = ''; // Clear previous content

    for (var key in details) {
        if (details.hasOwnProperty(key)) {
            var label = document.createElement('label');
            label.innerHTML = key + ': ';

            var input = document.createElement('input');
            input.type = 'text';
            input.name = key;
            input.value = details[key];

            adminEditForm.appendChild(label);
            adminEditForm.appendChild(input);
            adminEditForm.appendChild(document.createElement('br'));
        }
    }

    // Display the admin edit modal
    document.getElementById('adminEditModal').style.display = 'block';
}

function closeAdminEditModal() {
    // Close the admin edit modal
    document.getElementById('adminEditModal').style.display = 'none';
}

function saveAdminEdit() {
    // Get the modified data from the admin edit form
    var adminEditForm = document.getElementById('admin-edit-form');
    var formData = new FormData(adminEditForm);

    // Update the response in the database (you need to implement this part)
    // Use AJAX or form submission to send formData to the server and update the database

    // Close the admin edit modal after saving changes
    closeAdminEditModal();
}
