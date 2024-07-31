<?php
/**
 * Partial Payment Template
 * @since 1.0
 * @version 1.0
 */

if ( ! WC()->cart->needs_payment() ) {

	// There is no need for partial payments of orders that have no cost.
	return;

}

global $mycred_remove_partial_payment;

// Make sure we can make a partial payment
if ( ! mycred_partial_payment_possible() ) {

	// If you prefer, you could display some sort of information to those who can
	// not make a partial payment before returning.

	return;

}

// The <div> element with the ID "mycred-partial-payment-woo" MUST REMAIN!
// Any changes you make must be made inside this div element!
global $mycred_partial_payment; 

$change_position = $mycred_partial_payment['change_position'];

if ( ( $change_position == 'both' ) 
	|| ( $change_position == 'cart' && is_cart() ) 
	|| ( $change_position == 'checkout' && is_checkout() ) 
){ 

	$setting = get_option( 'mycred_partial_payment_switch' );

	if( $setting == 'enable_partial_payment' )
		{
			?>
		<div id="mycred-partial-payment-woo">
			<h3><?php mycred_partial_payment_title(); ?></h3>
			<p><?php mycred_partial_payment_desc(); ?></p>

			<?php mycred_partial_payment_selector(); ?>

		</div>
		<?php 
	} 
}			