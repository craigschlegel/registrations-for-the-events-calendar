<?php

require_once RTEC_URL . '/RegistrationsTEC/Submission.php';

$submission = new RegistrationsTEC\Submission( $_POST );

$submission->validateSubmissionData();

if ( empty( $submission->errors ) ) {

    $submission->sanitizeSubmissionData();
    
    $email_options = get_option( 'rtec_options' );

    if ( $submission->emailAddressGiven() ) {
        $confirmation_header = $submission->getConfirmationEmailHeader( $email_options );
        $confirmation_message = $submission->getConfirmationEmailMessage( $email_options );
        $confirmation_recipient = $submission->getConfirmationEmailRecipient();
        $confirmation_subject = $submission->getConfirmationEmailSubject( $email_options );

        //$confirmation_success = $submission->sendEmail( $confirmation_recipient, $confirmation_subject, $confirmation_message, $confirmation_header );
    }

    $notification_header = $submission->getNotificationEmailHeader( $email_options );
    $notification_message = $submission->getNotificationEmailMessage();
    $notification_recipient = $submission->getNotificationEmailRecipient( $email_options );
    $notification_subject = $submission->getNotificationEmailSubject();

//$notification_success = $submission->sendEmail( $notification_recipient, $notification_subject, $notification_message, $notification_header );

    $data = $submission->getDbData();

    require_once RTEC_URL . '/RegistrationsTEC/Database.php';

    $db = new RegistrationsTEC\Database();
    $db->insertEntry( $data );

    if ( ! empty( $data['rtec_event_id'] ) ) {
        $change = 1;
        $db->updateNumRegisteredMeta( $data['rtec_event_id'], $change );
    }
    $conf = $submission->getConfirmationEmailMessage( get_option('rtec_options') );

    echo $conf;
}

//echo '<pre>';
//var_dump( $submission );

//echo '</pre>';