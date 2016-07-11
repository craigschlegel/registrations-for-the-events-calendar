<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/15/2016
 * Time: 9:46 PM
 */

settings_errors(); ?>
<form method="post" action="options.php">
    <?php settings_fields( 'rtec_options' ); ?>
    <?php //echo'<pre>';var_dump(get_option('rtec_options'));echo'</pre>'; ?>
    <?php do_settings_sections( 'rtec_form_form_fields' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_form_custom_text' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_form_registration_availability' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php do_settings_sections( 'rtec_form_styles' ); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>