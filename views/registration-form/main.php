<?php
/**
 * Created by PhpStorm.
 * User: Craig
 * Date: 2/10/2016
 * Time: 6:32 AM
 */
?>
<button type="button" id="form-toggle-button" class="registrationsTEC-register-button">Register<span class="tribe-bar-toggle-arrow"></span></button>

    <form method="post" action="" id="registrationsTEC-hidden-form" class="registration-form">
        <!-- Certain information about the event is submitted along with the form -->
        <input type="hidden" name="email_submission" value="1" />
        <input type="hidden" value="<?php the_title(); ?>" name="title">
        <input type="hidden" value="<?php echo tribe_get_start_date(); ?>" name="date">
        <input type="hidden" value="<?php echo ''; ?>" name="event_id">

        <div class="registrationsTEC-form-wrapper">
            <p>* required</p>

            <!-- Display options for payment if there is a cost for the event -->
            <?php if ( tribe_get_cost() ) : ?>

            <div class="registrationsTEC-cost-wrapper">
                <input type="hidden" value="<?php echo tribe_get_cost(); ?>" name="cost">
                <div class="attention-box">
                    <div class="attention-message">
                        <p> This event has a cost.

                <!-- The option to pay with PayPal is only displayed if WP Simple PayPal cart is installed -->
                <?php if( defined( 'WP_CART_VERSION') ) : ?>

                    You can pay at the event or pay now with PayPal.</p>
                    </div>
                    <input type="checkbox" name="payment" id="payment" class="payment-radio" value="now"><label for="payment">I would like to pay now with PayPal</label>

                <?php else: ?>

                    </p>
                    </div>

                <?php endif; // WP Simple PayPal cart is installed ?>

                </div> <!-- attention-box -->
            </div> <!-- cost-wrapper -->

            <?php endif; // event has a cost ?>

            <!-- Loop through the formfields in the $registrationsTEC object and display -->
            <div class="">

            </div> <!--  -->

            <div style="display: none;">

                <!-- the field named address is used as a spam honeypot
                it is hidden from users, and it must be left blank -->

                <label for="address">Address</label>
                <input type="text" name="address" id="address">
                <p>Leave this field blank. It is not needed.</p>

            </div>

            <input type="submit" id="registrationsTEC-submit" value="Sign me up">

        </div> <!-- form-wrapper -->

    </form>

<!-- The spinner is displayed while the form is being sent via AJAX -->
<div class="registrationsTEC-spinner">
    <img title="Tribe Loading Animation Image" alt="Tribe Loading Animation Image" class="tribe-events-spinner-medium" src=<?php echo plugins_url() . "/the-events-calendar/src/resources/images/tribe-loading.gif"; ?>>
</div>
    <p id="registrationsTEC-success-message" class="success-message tribe-events-notices">Success! Check your email for a confirmation message.</p>

    <!-- Show the "add to cart" button for users to pay with PayPal after registration
         only if WP Simple PayPal Cart is installed -->
    <?php if( defined('WP_CART_VERSION') ) : ?>
    <div id="registrationsTEC-payment-wrapper" class="tribe-events-notices">
        <p>Click the button below to pay your registration fee</p>
        <div>

            <?php
                    $product = "Registration fee for " . get_the_title() . " on " . tribe_get_start_date();
                    $cost = tribe_get_cost();
                    echo print_wp_cart_button_for_product( $product, $cost );
            ?>

        </div>
    </div>

    <?php  endif; // WP PayPal cart is installed ?>