<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 1/16/2016
 * Time: 1:43 PM
 */

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class Form
{

    public function __construct()
    {
        // get post object
        $obj = get_post();

        $this->event_post_ID = $obj->ID;
        $this->start_date = $obj->EventStartDate;
        $this->end_date = $obj->EventEndDate;

        // get post meta
        $meta = get_post_meta( $this->event_post_ID );

        $this->venue_ID = $meta['_EventVenueID'][0];
        $this->currency_symbol = $meta['_EventCurrencySymbol'][0];
        $this->cost = $meta['_EventCost'][0];

        // get venue meta
        $venue_meta = get_post_meta( $this->venue_ID );

        $this->venue_title = $venue_meta["_VenueVenue"][0];
    }


    public function show_form()
    {
        echo '<pre>';var_dump($this);echo'</pre><br><br>';
        //echo '<pre>';var_dump(get_post_meta($this->event_post_ID));echo'</pre>';
        //include_once RTEC_URL . 'views/registration-form/main.php';
    }
}