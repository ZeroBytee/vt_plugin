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

function claimRide(details) {
    var ajaxurl = claim_ride_vars.ajax_url;

    var data = {
        action: 'claimRide_callback',
        details: JSON.stringify(details),
        user: claim_ride_vars.user_id
    };

    jQuery.post(ajaxurl, data, function(response) {
        console.log(response);

        if (response.success) {
            closeModal();
        } else {
            console.error('Claiming ride failed');
        }
    });
}
