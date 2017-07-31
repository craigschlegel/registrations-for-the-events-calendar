<?php
$settings = $this->settings;
?>
<div class="rtec-toolbar wp-filter">
	<div class="rtec-toolbar-secondary">
		<form id="rtec-toolbar-form" action="" method="get" style="margin-bottom: 0;">
			<input type="hidden" name="post_type" value="tribe_events">
			<input type="hidden" name="page" value="registrations-for-the-events-calendar/_settings">
			<input type="hidden" name="v" value="<?php echo esc_attr( $settings['v'] ); ?>">
			<div class="view-switch rtec-grid-view-switch">
				<a href="<?php $this->the_toolbar_href( 'v', 'list' ); ?>" class="view-list<?php if( $settings['v'] === 'list' ) echo ' current'; ?>">
					<span class="screen-reader-text">List View</span>
				</a>
				<a href="<?php $this->the_toolbar_href( 'v', 'grid' ); ?>" class="view-grid<?php if( $settings['v'] === 'grid' ) echo ' current'; ?>">
					<span class="screen-reader-text">Grid View</span>
				</a>
			</div>
			<label for="rtec-registrations-date" class="screen-reader-text">Filter by start date</label>
			<select id="rtec-registrations-date" name="qtype" class="registrations-filters">
				<option value="upcoming" <?php if ( $settings['qtype'] === 'upcoming' ) echo 'selected'; ?>>View Upcoming</option>
				<option value="start" <?php if ( $settings['qtype'] === 'start' ) echo 'selected'; ?>>Select Start Date</option>
				<option value="all" <?php if ( $settings['qtype'] === 'all' ) echo 'selected'; ?>>View All</option>
			</select>
			<label for="rtec-registrations-start" class="screen-reader-text">Filter by event start date</label>
			<input type="text" id="rtec-date-picker" name="start" value="<?php echo date( "m/d/Y", strtotime( $settings['start'] ) ); ?>" class="rtec-date-picker" style="vertical-align: middle;<?php if ( $settings['qtype'] !== 'start' ) echo 'display: none;'; ?>"/>
			<label for="rtec-registrations-reg" class="screen-reader-text">Filter by registrations</label>
			<select id="rtec-registrations-reg" name="with" class="registrations-filters">
				<option value="with" <?php if ( $settings['with'] === 'with' ) echo 'selected'; ?>>With registrations enabled</option>
				<option value="either" <?php if ( $settings['with'] === 'either' ) echo 'selected'; ?>>With/without registrations</option>
			</select>
			<button id="rtec-filter-go" type="button" class="button rtec-toolbar-button" data-rtec-view-settings="<?php echo esc_attr( json_encode( $settings ) ); ?>">Go</button>
		</form>
	</div>
	<div class="rtec-toolbar-primary search-form"><label for="rtec-search-input" class="screen-reader-text">Search Registrants</label>
		<input type="search" placeholder="Search Registrants" id="rtec-search-input" class="search">
	</div>
</div>