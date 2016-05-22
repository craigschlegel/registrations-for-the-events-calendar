<?php

require_once RTEC_URL . '/RegistrationsTEC/Submission.php';

$submission = new RegistrationsTEC\Submission( $_POST );

$submission->validateSubmissionData();

if ( $submission->emailAddressGiven() ) {
    $email_options = get_option( 'rtec_email' );
    $email_message = '';
    $email_message = $submission->getConfirmationEmailMessage( $email_options );
}


echo '<pre>';
var_dump( $submission );

var_dump( $email_message );
echo '</pre>';