<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class RTEC_Admin_Registrations {

	private $tab;

	private $settings = array();

	private $posts_per_page = 10;

	private $ids_on_page = array();

	public function build_admin_registrations( $tab, $settings = array() ) {
		$this->tab = $tab;
		$this->settings = $settings;
	}

	public function add_event_id_on_page( $id )
	{
		$this->ids_on_page[] = $id;
	}

	public function get_ids_on_page()
	{
		return $this->ids_on_page;
	}

	public function get_events()
	{
		global $rtec_options;
		$settings = $this->settings;

		if ( $settings['qtype'] === 'all' ) {
			$args = array(
				'posts_per_page' => $this->posts_per_page,
				'start_date' => date( '2000-1-1 0:0:0' ),
				'orderby' => 'date',
				'order' => 'DESC',
				'offset' => $settings['off']
			);
		} elseif ( $settings['qtype'] === 'start' ) {
			$args = array(
				'orderby' => '_EventStartDate',
				'order' => 'ASC',
				'posts_per_page' => $this->posts_per_page,
				'start_date' => $settings['start'],
				'offset' => $settings['off']
			);
		} else {
			$args = array(
				'orderby' => '_EventStartDate',
				'order' => 'ASC',
				'posts_per_page' => $this->posts_per_page,
				'start_date' => date( 'Y-m-d H:i:s' ),
				'offset' => $settings['off']
			);
		}
		if ( $this->settings['with'] === 'with' && ( isset( $rtec_options['disable_by_default'] ) && $rtec_options['disable_by_default'] === true ) ) {
			$args['meta_query'] = array(
				'key' => '_RTECregistrationsDisabled',
				'value' => '0',
				'compare' => '='
			);
		}

		return tribe_get_events( $args );

	}

	public function the_registrations_overview()
	{
		add_action( 'rtec_registrations_tab_after_the_title', array( $this, 'the_toolbar' ) );
		if ( $this->settings['v'] === 'list' ) {
			add_action( 'rtec_registrations_tab_events', array( $this, 'the_events_list' ) );
			add_action( 'rtec_registrations_tab_list_table_body', array( $this, 'the_events_list_table_body' ) );
		} else {
			add_action( 'rtec_registrations_tab_events', array( $this, 'the_events_overview' ) );
			add_action( 'rtec_registrations_tab_event_meta', array( $this, 'the_event_meta' ), 10, 1 );
			add_action( 'rtec_registrations_tab_hidden_event_options', array( $this, 'the_hidden_event_options' ), 10, 1 );
		}

		add_action( 'rtec_registrations_tab_pagination', array( $this, 'the_pagination' ) );
		add_action( 'rtec_registrations_tab_events_loaded', array( $this, 'update_status_for_event_ids' ), 10, 1 );
	}

	public function the_registrations_detailed_view()
	{
		add_action( 'rtec_registrations_tab_event_meta', array( $this, 'the_event_meta' ), 10, 1 );
		add_action( 'rtec_registrations_tab_events_loaded', array( $this, 'update_status_for_event_ids' ), 10, 1 );
	}

	public function the_toolbar() {
		require_once RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-toolbar.php';
	}

	public function the_events_list() {
		require_once RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-list-view.php';
	}

	public function the_events_list_table_body() {
		$events = $this->get_events();
		$settings = $this->settings;

		foreach ( $events as $event ) {
			$this->add_event_id_on_page( $event->ID );

			$event_obj = new RTEC_Admin_Event();
			$event_obj->build_admin_event( $event->ID, 'list', '' );
			$event_meta = $event_obj->event_meta;
			$venue = $event_meta['venue_title'];
			$row_class = 'class="rtec-highlight"';
			$num_registered = $event_obj->event_meta['max_registrations'];

			if ( rtec_should_show( $settings['with'], $event_meta['registrations_disabled'] ) ) {
				include RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-list-table-body.php';
			}

		}

	}

	public function the_events_overview() {
		$settings = $this->settings;
		$events   = $this->get_events();

		foreach ( $events as $event ) {
			$event_meta = rtec_get_event_meta( $event->ID );
			$this->add_event_id_on_page( $event->ID );

			if ( rtec_should_show( $settings['with'], $event_meta['registrations_disabled'] ) ) {
				$event_obj = new RTEC_Admin_Event();
				$event_obj->build_admin_event( $event->ID, 'grid', '' );
				if ( ! empty( $event_obj->mvt_fields ) ) {
					echo '<div class="rtec-single-mvt-pair-wrapper rtec-clear">';
				}
				include RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-overview-view.php';
			}

		}
	}

	public function the_event_meta( $event_obj ) {
		include RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-event-meta.php';
	}

	public function the_hidden_event_options( $event_obj ) {
		include RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-hidden-event-options.php';
	}

	public function the_pagination() {
		require_once RTEC_PLUGIN_DIR . 'inc/admin/templates/partials/registrations-pagination.php';
	}

	public function the_toolbar_href( $var, $value ) {
		$href = RTEC_ADMIN_URL;
		$settings = $this->settings;
		$settings['tab'] = $this->tab;
		$settings[ $var ] = $value;
		$query_args_array = $settings;

		$href = add_query_arg( $query_args_array, $href );

		echo $href;
	}

	public function the_pagination_href( $context ) {
		$href = RTEC_ADMIN_URL;
		$settings = $this->settings;
		$settings['tab'] = $this->tab;

		if ( $context === 'back' ) {
			$settings['off'] = (int)$this->settings['off'] - $this->posts_per_page;
		} else {
			$settings['off'] = (int)$this->settings['off'] + $this->posts_per_page;
		}

		$query_args_array = $settings;

		$href = add_query_arg( $query_args_array, $href );

		echo $href;
	}

	public function the_detailed_view_href( $id, $mvt = '' ) {
		$href = RTEC_ADMIN_URL;
		$settings = $this->settings;
		$settings['tab'] = 'single';
		$settings['id'] = $id;

		$query_args_array = $settings;

		$href = add_query_arg( $query_args_array, $href );

		echo $href;
	}

	public function out_of_posts() {

		if ( count( $this->get_ids_on_page() ) < $this->posts_per_page ) {
			return true;
		} else {
			return false;
		}

	}

	public function update_status_for_event_ids( $ids_on_page ) {

		if ( ! empty( $ids_on_page ) ) {
			$rtec = RTEC();
			$db = $rtec->db_frontend->instance();

			//$db->update_statuses( $ids_on_page );
		}

	}

	public function get_registrant_tr_classes( $status = 'c', $is_user = false ) {

		$classes = '';
		switch( $status ) {
			case 'c' :
				$classes .= '';
				break;
			case 'p' :
				$classes .= ' rtec-unconfirmed';
				break;
			case 'n' :
				$classes .= '';
				break;
			default :
				$classes .= '';
		}

		if ( $is_user ) {
			$classes .= ' rtec-is-user';;
		}

		return $classes;
	}

	public function get_registrant_icons( $status = 'c', $is_user = false ) {

		$html = '';
		switch( $status ) {
			case 'c' :
				$html .= '';
				break;
			case 'p' :
				$html .= '<span class="rtec-notice-new rtec-unconfirmed"><i class="fa fa-flag" aria-hidden="true"></i></span>';
				break;
			case 'n' :
				$html .= '<span class="rtec-notice-new"><i class="fa fa-tag" aria-hidden="true"></i></span>';
				break;
			default :
				$html .= '';
		}

		if ( $is_user && $status !== 'p' ) {
			$html .= '<span class="rtec-notice-new rtec-is-user"><i class="fa fa-user" aria-hidden="true"></i></span>';
		}

		return $html;
	}
}