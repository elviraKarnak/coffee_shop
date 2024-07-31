<?php
// style sheet & scripts
function coffee_shop_enqueue(){

	$uri = get_theme_file_uri();
  
	$ver = 1.0;
	$vert = time();
  
      wp_register_style( 'bootstrap',   $uri. '/asset/css/bootstrap.min.css', [], $ver);
	  wp_register_style( 'font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css', [], $ver);
	  wp_register_style( 'owl',  	   $uri. '/asset/css/owl.carousel.min.css', [], $ver);
	  wp_register_style( 'owl-theme',  $uri. '/asset/css/owl.theme.default.min.css', [], $ver);
	  wp_register_style( 'theme-css',  $uri. '/asset/css/custom.css', [], $vert);
	  wp_register_style( 'theme_stylesheet', $uri. '/style.css', [], $vert);
  
	  wp_enqueue_style( 'bootstrap');
	  wp_enqueue_style( 'font-awesome');
	  wp_enqueue_style( 'owl');
	  wp_enqueue_style( 'owl-theme');
	  wp_enqueue_style( 'theme-css');
	  wp_enqueue_style( 'theme_stylesheet');
	  
	  wp_register_script( 'bootstrap', $uri . '/asset/js/bootstrap.bundle.min.js', [], $ver, true );
	  wp_register_script( 'owl', $uri . '/asset/js/owl.carousel.min.js', [], $ver, true );
	  wp_register_script( 'custom-js', $uri . '/asset/js/custom.js', [], $vert, true );
  
	  wp_enqueue_script('jquery');
	  wp_enqueue_script('bootstrap');
	  wp_enqueue_script('owl');
	  wp_enqueue_script('custom-js');
  }
	   
  add_action( 'wp_enqueue_scripts', 'coffee_shop_enqueue' );

// register navs
register_nav_menus(
	array(
		'menu-1' => __( 'Primary', 'coffee_shop' ),
		'menu-2' => __( 'Header Right', 'coffee_shop' ),
		'menu-3' => __( 'Footer First Menu', 'coffee_shop' ),
		'menu-4' => __( 'Footer Second Menu', 'coffee_shop' ),
		'menu-5' => __( 'Dashboard', 'coffee_shop' ),
    )
);

	// theme support
		function coffee_shop_setup_theme(){
			add_theme_support( 'custom-logo' );
		    add_theme_support( 'post-thumbnails' );
			add_theme_support( 'title-tag' );
			}
		add_action( 'after_setup_theme', 'coffee_shop_setup_theme' );

require get_template_directory() . '/inc/custom_functions.php';


function add_mycred_points_on_order_complete($order_id) {
    // Get the order object
    $order = wc_get_order($order_id);

    // Get the user ID
    $user_id = $order->get_user_id();

    // Get the order total
    $order_total = $order->get_total();

    // Add points to the user's myCred account
    if ($user_id && $order_total) {
        $points = floatval($order_total); // Ensure the points are in numeric form
        $reference = 'order_completed'; // Reference for the transaction
        $log_entry = 'Points for order #' . $order_id; // Log entry
        mycred_add($reference, $user_id, $points, $log_entry, $order_id);
    }
}

add_action('woocommerce_order_status_completed', 'add_mycred_points_on_order_complete');