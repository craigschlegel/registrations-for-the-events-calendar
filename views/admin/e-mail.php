<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/15/2016
 * Time: 9:46 PM
 */

settings_errors(); ?>
<form method="post" action="options.php">
    <?php settings_fields('rtec-email'); ?>
    <?php do_settings_sections('rtec-notification-section'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <?php do_settings_sections('rtec-confirmation-section'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>