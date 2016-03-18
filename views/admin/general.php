<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/15/2016
 * Time: 9:46 PM
 */

settings_errors(); ?>
<form method="post" action="options.php">
    <?php settings_fields('rtec_general'); ?>
    <?php echo'<pre>';var_dump(get_option('rtec_general'));echo'</pre>'; ?>
    <?php do_settings_sections('rtec_general_general'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <?php do_settings_sections('rtec_general_form_fields'); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>