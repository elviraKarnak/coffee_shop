<?php
if ( ! function_exists( 'mycred_render_woo_product_referral_link' ) ) :
	function mycred_render_woo_product_referral_link( $atts, $content = '' ) {
        extract( shortcode_atts( array(
            'type' => MYCRED_DEFAULT_TYPE_KEY
        ), $atts,   'mycred_woocommerce_referral' ) );

        $login_url =  !isset($atts['login_url']) ? wp_login_url() : $atts['login_url'];
        $registration_url =  !isset($atts['registration_url']) ? wp_registration_url() : $atts['registration_url'];
        if(!is_user_logged_in())
        {
            echo "<a href='".sanitize_textarea_field($login_url)."'>Login</a> or <a href='".sanitize_textarea_field($registration_url)."'>Register</a>";
            return;
        }




		return apply_filters( 'mycred_woocommerce_referral_' . $type, '', $atts, $content );

	}
endif;
add_shortcode('mycred_woocommerce_referral', 'mycred_render_woo_product_referral_link' );

