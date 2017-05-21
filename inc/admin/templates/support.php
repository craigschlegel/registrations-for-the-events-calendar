<h1><?php _e( 'Support', 'registrations-for-the-events-calendar' ); ?></h1>

<p><?php _e( 'Need help setting things up? Check out our set up directions', 'registrations-for-the-events-calendar' ); ?>.</p>
<div class="rtec-button-wrapper">
	<a href="http://roundupwp.com/products/registrations-for-the-events-calendar/setup" class="rtec-support-button" target="_blank"><?php _e( 'Get directions', 'registrations-for-the-events-calendar' ); ?></a>
</div>

<p><?php _e( 'Have a problem? Submit a support ticket on our website. Please include your <strong>System Info</strong> below with support requests.', 'registrations-for-the-events-calendar' ); ?></p>
<div class="rtec-button-wrapper">
	<a href="http://roundupwp.com/products/registrations-for-the-events-calendar/support" class="rtec-support-button" target="_blank"><?php _e( 'Submit a Ticket', 'registrations-for-the-events-calendar' ); ?></a>
</div>

<p><?php _e( 'Have a suggestion or a request for a feature? Great! We are looking to expand the plugin and offer a "Pro" version', 'registrations-for-the-events-calendar' ); ?></p>
<div class="rtec-button-wrapper">
	<a href="http://roundupwp.com/products/registrations-for-the-events-calendar/support" class="rtec-support-button" target="_blank"><?php _e( 'Request a Feature', 'registrations-for-the-events-calendar' ); ?></a>
</div>
<br />
<h2><?php _e( 'System Info', 'registrations-for-the-events-calendar' ); ?></h2>
<p><?php _e( 'Click the text below to select all', 'registrations-for-the-events-calendar' ); ?></p>

<textarea readonly="readonly" onclick="this.focus();this.select()" title="To copy, click the field then press Ctrl + C (PC) or Cmd + C (Mac)." style="width: 70%; height: 500px; white-space: pre; font-family: Menlo,Monaco,monospace;">
## SITE/SERVER INFO: ##
Plugin Version:           <?php echo RTEC_TITLE . ' v' . RTEC_VERSION. "\n"; ?>
Site URL:                 <?php echo site_url() . "\n"; ?>
Home URL:                 <?php echo home_url() . "\n"; ?>
WordPress Version:        <?php echo get_bloginfo( 'version' ) . "\n"; ?>
PHP Version:              <?php echo PHP_VERSION . "\n"; ?>
Web Server Info:          <?php echo $_SERVER['SERVER_SOFTWARE'] . "\n"; ?>
JSON:                     <?php echo function_exists( "json_decode" ) ? "Yes" . "\n" : "No" . "\n" ?>

## ACTIVE PLUGINS: ##
<?php
$plugins = get_plugins();
$active_plugins = get_option( 'active_plugins', array() );

foreach ( $plugins as $plugin_path => $plugin ) {
    // If the plugin isn't active, don't show it.
    if ( in_array( $plugin_path, $active_plugins ) ) {
        echo $plugin['Name'] . ': ' . $plugin['Version'] ."\n";
    }
}
?>

## OPTIONS: ##
<?php
$db_options = get_option( 'rtec_db_version', '' );
echo str_pad( 'database version:', 28 ) . $db_options ."\n";
$options = get_option( 'rtec_options' );

foreach ( $options as $key => $val ) {
    $label = esc_html( $key ) . ':';
    $value = isset( $val ) ? esc_html( $val ) : 'unset';
    echo str_pad( $label, 28 ) . $value ."\n";
}

// DB troubleshooting
global $wpdb;
$table_name = esc_sql( $wpdb->prefix . RTEC_TABLENAME );

$column_descriptions = $wpdb->get_results( "DESCRIBE $table_name" );

echo "\n";

foreach ( $column_descriptions as $column ) {
	echo 'Field: ' . $column->Field . ', Type: ' . $column->Type . ', Key: ' . $column->Key . ', Extra: ' . $column->Extra . "\n";
}

$last_result = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY id DESC LIMIT 1;" );

echo "\n";

if ( is_array( $last_result ) ) {
	
	foreach ( $last_result as $column ) {

		foreach ( $column as $key => $value ) {

			if ( $key != 'first_name' && $key != 'last_name' && $key != 'first_name' && $key != 'custom' && $key != 'phone' && $key != 'email' ) {
				echo $key . ': ' . $value;
			} else {
				echo $key . ': ' . substr( $value, 0, 3 );
			}

			echo "\n";
		}
	}

}
$event = tribe_get_events( array(
	'posts_per_page' => 1,
	'start_date' => date( '2000-1-1 0:0:0' ),
	'orderby' => 'date',
	'order' => 'DESC'
) );

$meta = rtec_get_event_meta( $event[0]->ID );
echo "\n";

if ( is_array( $meta ) ) {

	foreach ( $meta as $key => $value ) {
		echo $key.': ' . $value;
		echo "\n";
	}

}

?>

</textarea>