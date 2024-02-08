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

function showNotification(message, type = "success") {
    createAlert(message, type);
}

function claimRide(details) {
    var ajaxurl = claim_ride_vars.ajax_url;

    var data = {
        action: 'claimRide_callback',
        details: JSON.stringify(details),
        user: claim_ride_vars.user_id,
        nonce: claim_ride_vars.nonce
    };

    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);

        if (response.success) {
            closeModal();
            createAlert("Succesfully claimed the ride!", "success");
        } else {
            console.error(response.data['message']);
        }
    });
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
