<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 3/18/2016
 * Time: 3:28 PM
 */

namespace RegistrationsTEC;

// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
    die( '-1' );
}

class EventData
{
    /**
     * @var array|null|\WP_Post
     */
    private $obj;

    /**
     * @var mixed
     */
    private $meta;

    /**
     * @var mixed
     */
    private $venue_meta;

    /**
     * @var int
     */
    private $post_id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array|mixed
     */
    private $start_date;

    /**
     * @var array|mixed
     */
    private $end_date;

    /**
     * @var
     */
    private $venue_id;

    /**
     * @var
     */
    private $currency_symbol;

    /**
     * @var
     */
    private $cost;

    /**
     * @var
     */
    private $venue_title;

    public function __construct( $id = '' )
    {
        // construct post object
        if ( isset( $id ) ) {
            $this->obj = get_post( $id );
        } else {
            $this->obj = get_post();
        }

        // set post meta
        $this->meta = get_post_meta( $this->obj->ID );

        // set venue meta
        $this->venue_meta = get_post_meta( $this->meta['_EventVenueID'][0] );

        $this->post_id = $this->obj->ID;
        $this->title = get_the_title();
        $this->start_date = $this->obj->EventStartDate;
        $this->end_date = $this->obj->EventEndDate;
        $this->venue_id = $this->meta['_EventVenueID'][0];
        $this->currency_symbol = $this->meta['_EventCurrencySymbol'][0];
        $this->cost = $this->meta['_EventCost'][0];
        $this->venue_title = $this->venue_meta["_VenueVenue"][0];
    }

    /**
     * @return mixed
     */
    public function getPostId()
    {
        return $this->post_id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getStartDate()
    {
        return $this->start_date;
    }

    /**
     * @return mixed
     */
    public function getEndDate()
    {
        return $this->end_date;
    }

    /**
     * @return mixed
     */
    public function getVenueId()
    {
        return $this->venue_id;
    }

    /**
     * @return mixed
     */
    public function getCurrencySymbol()
    {
        return $this->currency_symbol;
    }

    /**
     * @return mixed
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @return mixed
     */
    public function getVenueTitle()
    {
        return $this->venue_title;
    }




}