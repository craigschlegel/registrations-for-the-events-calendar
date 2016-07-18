<?php

settings_errors(); ?>
<h2><?php _e( 'Email Settings', 'rtec' ); ?></h2>
<form method="post" action="options.php">
    <?php settings_fields( 'rtec_options' ); ?>
    <?php do_settings_sections( 'rtec_email_notification' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_email_confirmation' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>