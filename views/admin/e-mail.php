<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/15/2016
 * Time: 9:46 PM
 */

settings_errors(); ?>
<form method="post" action="options.php">
    <?php echo'<pre>';var_dump(get_option('rtec_email'));echo'</pre>'; ?>

    <?php settings_fields('rtec_email'); ?>
    <?php do_settings_sections('rtec_email_notification'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <?php do_settings_sections('rtec_email_confirmation'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>