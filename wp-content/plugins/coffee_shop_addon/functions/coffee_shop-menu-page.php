<?php 
$locality_points_options = [];

add_action("admin_menu", "setup_theme_admin_menus");
add_action('admin_init', 'loyalty_points_page_init') ;


function setup_theme_admin_menus() {
    $menu_form = add_menu_page(
      __( 'Loyalty Settings', 'coffee_shop' ),
      __( 'Loyalty Settings', 'coffee_shop' ),
      'manage_options', 
      'loyalty_settings', 'loyalty_settings_cb','dashicons-star-filled',10
    );
}


function loyalty_settings_cb() {
    $locality_points_options = get_option( 'loyality_points_option' ); ?>

        <div class="wrap">
            <h2>Loyalty Points</h2>
            <p></p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                    settings_fields( 'loyality_points_option_group' );
                    do_settings_sections( 'loyality-points-admin' );
                    submit_button();
                ?>
            </form>
        </div>
    <?php 
    }

	function loyalty_points_page_init() {
		register_setting(
			'loyality_points_option_group', // option_group
			'loyality_points_option', // option_name
			 'loyality_points_sanitize' // sanitize_callback
		);

		add_settings_section(
			'loyality_points_setting_section', // id
			'Settings', // title
			'loyality_points_section_info', // callback
			'loyality-points-admin' // page
		);
        $currency = get_woocommerce_currency_symbol();
		add_settings_field(
			'points_per', // id
			'Points per '.$currency, // title
			'points_per_callback', // callback
			'loyality-points-admin', // page
			'loyality_points_setting_section' // section
		);
	}

    function loyality_points_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['points_per'] ) ) {
			$sanitary_values['points_per'] = sanitize_text_field( $input['points_per'] );
		}

		return $sanitary_values;
	}

	function loyality_points_section_info() {
		
	}

	function points_per_callback() {
        $locality_points_options = get_option('loyality_points_option');
		printf(
			'<input class="regular-text" type="text" name="loyality_points_option[points_per]" id="points_per" value="%s">',
			isset( $locality_points_options['points_per'] ) ? esc_attr($locality_points_options['points_per']) : ''
		);
	}