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
    private $form_fields = array();

    public $html;

    public function __construct()
    {
        // get post object
        $obj = get_post();

        $this->event_post_ID = $obj->ID;
        $this->even_title = get_the_title();
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

    public function set_form_field( $args )
    {
        $this->form_fields[] = array(
            'priority' => $args['priority'],
            'type' => $args['type'],
            'name' => $args['name'],
            'label' => $args['label'],
            'placeholder' => $args['placeholder'],
            'class' => $args['class'],
            'min-length' => $args['min-length'],
            'max-length' => $args['max-length'],
            'validation-type' => $args['validation-type']
        );
    }

    private function get_default_field_html( $form_field )
    {
        $placeholder = ( isset( $args['placeholder'] ) ) ? esc_attr( ' placeholder="'.$args['placeholder'].'"' ) : '';

        $html = '';
        $html .= '<div id="rtec-'.$form_field['name'].'" class="rtec-'.$form_field['class'].'">';
        $html .=    '<label for="rtec-'.$form_field['name'].'">'.$form_field['label'].'</label>';
        $html .=    '<input id="rtec-'.$form_field['name'].'" type="'.$form_field['type'].'" name="'.$form_field['name'].'"'.$placeholder.'>';
        $html .= '</div>';

        return $html;
    }


    private function get_checkbox_field_html( $form_field )
    {
        return 'checkbox';
    }

    public function set_form_fields_html()
    {
        $priority = array();
        foreach( $this->form_fields as $key => $row )
        {
            $priority[$key] = $row['priority'];
        }
        array_multisort( $priority, SORT_ASC, $this->form_fields );

        $this->html = '';
        foreach( $this->form_fields as $form_field ) {
            switch( $form_field['type'] ) {
                case 'checkbox':
                    $this->html .= $this->get_checkbox_field_html( $form_field );
                    break;
                default:
                    $this->html .= $this->get_default_field_html( $form_field );
                    break;
            }
        }
    }

    public function show_form()
    { ?>
        <button type="button" id="rtec-form-action-button" class="rtec-button"><?php esc_html_e( 'Register', 'rtec' ); ?><span class="tribe-bar-toggle-arrow"></span></button>
        <div id="rtec" class="rtec">
            <form method="post" action="" id="rtec-registration-form" class="rtec-registration-form">

                <?php echo $this->html; ?>

            </form>
        </div>

    <?php
    }
}