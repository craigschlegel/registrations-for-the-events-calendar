<?php
settings_errors(); ?>
<h1><?php _e( 'Email Settings', 'rtec' ); ?></h1>
<p><strong><span class="rtec-individual-available">&#42;</span>Can also be set for each event separately on the Events->Edit page</strong></p>

<?php
$new_status = get_transient( 'rtec_new_messages' );
if ( $new_status === 'yes' ) : ?>
<div class="rtec-em-alert">
<p><?php _e( 'For best results with email delivery, check out our related <a href="https://roundupwp.com/faq/my-confirmationnotification-emails-are-missing/" target="_blank">article</a> on our website', 'registrations-for-the-events-calendar' ); ?></p>
</div>
<?php endif; ?>

<form method="post" action="options.php">
    <?php settings_fields( 'rtec_options' ); ?>
    <?php do_settings_sections( 'rtec_email_all' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_email_notification' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_email_confirmation' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>