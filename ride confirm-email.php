<?php
/*
Plugin Name: Fluent Forms Thank You Email
Description: Custom plugin for sending a "Thank You" email after Fluent Forms submission.
Version: 1.2
Author: Miro
*/

add_action('fluentform/submission_inserted', 'fluentforms_thank_you_email', 20, 3);

function fluentforms_thank_you_email($entryId, $formData, $form)
{
    // Check if the submitted form is the one with ID 6

    // Retrieve the submitted data from the form

    if ($form->id == 6){
        $submitted_fname = isset($formData['names']['first_name']) ? $formData['names']['first_name'] : '';
        $submitted_lname = isset($formData['names']['last_name']) ? $formData['names']['last_name'] : '';
        $submitted_email = isset($formData['email']) ? $formData['email'] : '';
        $subject = 'Thank You for Your Order';
        $message = 'Dear ' . $submitted_fname . ' ' . $submitted_lname . ',<br><br>';
        $message .= 'Thank you for your order at velotaxi. We will get in touch with shortly to tell you when your driver will arrive.<br>';
        $message .= 'If you have any questions or need further assistance, feel free to contact us.<br><br>';
        $message .= 'Kind regards,<br> Velotaxi<br><br>';
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM">';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo">';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        
    }
    elseif($form->id == 7){
        $submitted_fname = isset($formData['names']['first_name']) ? $formData['names']['first_name'] : '';
        $submitted_lname = isset($formData['names']['last_name']) ? $formData['names']['last_name'] : '';
        $submitted_email = isset($formData['email']) ? $formData['email'] : '';
        $subject = 'Thank you for your order';
        $message = 'Dear ' .$submitted_fname . ' ' . $submitted_lname . ',<br><br>';
        $message .= 'Thank you for your order at velotaxi. You ordered a special event tour, one of our staff members will get in touch with you shortly to discuss the details of your planned event. <br>';
        $message .= 'This includes, but is not limited to: pricing, duration and the amount of taxis required <br><br>';
        $message .= 'Kind regards,<br> VeloTaxi<br><br>';
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM Logo">';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo">';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        $submitted_email2 = 'miroschelkens4@gmail.com';
        $subject2 = 'Special event ordered';
        $message2 = 'Dear VT admin, <br><br>';
        $message2 .= 'A special event has just been ordered, it is now your task to discuss the details with the person orderering the event. <br><br>';
        $message2 .= 'Their email adress is ' . $submitted_email . '.' . '<br><br>';
        $message2 .= 'Kind Regards <br> Velotaxi <br><br>';
        $message .= '<img src="https://tsmmechelen.eu/wp-content/uploads/2020/11/Logo-TSM_RGB-367x367-1.png" alt="TSM Logo">';
        $message .= '<img src="https://velotaxi-mechelen.be/wp-content/uploads/2023/08/VelotaxiMechelen_Logo-01-1.png" alt="Velotaxi Logo">';
        $result = wp_mail($submitted_email2, $subject2, $message2, $headers);
    }

    $result = wp_mail($submitted_email, $subject, $message, $headers);
}
