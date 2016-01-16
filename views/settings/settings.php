<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/15/2016
 * Time: 9:46 PM
 */

settings_errors(); ?>
<form method="post" action="options.php">
    <?php settings_fields(RTEC_OPTION_NAME_GENERAL); ?>
    <?php do_settings_sections(RTEC_OPTION_SECTION_GENERAL); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
    <hr>
    <?php settings_fields(RTEC_OPTION_NAME_CONFIRMATION); ?>
    <?php do_settings_sections(RTEC_OPTION_SECTION_CONFIRMATION); ?>
    <input class="button-primary" type="submit" name="save" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
</form>