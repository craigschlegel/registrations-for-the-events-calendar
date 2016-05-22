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
    private $show = array();

    private $require = array();

    private $display = array();


    public function __construct( $fields )
    {
        // get form options from the db
        $options = get_option('rtec_general');

        foreach ( $fields as $field ) {
            // create an array of all to be shown
            if ( $options[$field . '_show'] == true ) {
                $this->show[] = $field;
            }
            // create an array of all to be required
            if ( $options[$field . '_require'] == true ) {
                $this->require[] = $field;
            }
        }

    }

    public function show_form()
    {
        include_once RTEC_URL . '/views/registration-form/main.php';
    }
}