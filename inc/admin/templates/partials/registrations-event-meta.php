<?php
$date_format = 'F jS, ' . rtec_get_time_format();
?>

<div class="rtec-outline">
	<?php if ( $event_obj->view_type !== 'single' ) : ?>
	<a href="<?php $this->the_detailed_view_href( $event_obj->event_meta['post_id'], '' ); ?>"><h3><?php echo esc_html( $event_obj->event_meta['title'] ); ?></h3></a>
	<?php else : ?>
	<h3><?php echo esc_html( $event_obj->event_meta['title'] ); ?></h3>
	<?php endif; ?>
	<p class="rtec-event-date"><?php echo sprintf( __( '%s to %s', 'registrations-for-the-events-calendar' ), date_i18n( $date_format, strtotime( $event_obj->event_meta['start_date'] ) ), '<span class="rtec-end-time">' . date_i18n( $date_format, strtotime( $event_obj->event_meta['end_date'] ) ) . '</span>' ); ?></p>
</div>
<p class="rtec-venue-highlight"><?php echo esc_html( $event_obj->event_meta['venue_title'] ); ?></p>
<div class="rtec-reg-info rtec-border-sides">
	<p><?php echo $event_obj->get_registration_text( array(), $event_obj->event_meta['num_registered'] ); ?></p>
</div>