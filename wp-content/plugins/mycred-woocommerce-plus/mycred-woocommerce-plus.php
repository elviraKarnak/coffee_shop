<?php
/**
 * Plugin Name: myCRED WooCommerce Plus - WooCommerce
 * Description: Allows WooCommerce Plus of a WooCommerce orders using Coupons , Restrict Products , Points History , Partial Payments.
 * Version: 1.7.5
 * Tags: points history, ristrict products, Coupons, woocommerce
 * Author: myCRED
 * Author URI: http://mycred.me
 * Author Email: support@mycred.me
 * Requires at least: WP 4.8
 * Tested up to: WP 5.8.1
 * Text Domain: mycredpartwoo
 * Domain Path: /lang
 */
if ( ! class_exists( 'myCRED_WooCommerce_Plus' ) ) :
	final class myCRED_WooCommerce_Plus {

		// Plugin Version
		public $version             = '1.7.5';

		public $slug                = 'mycred-woocommerce-plus';

		public $plugin               = NULL;

		// Instnace
		protected static $_instance = NULL;

		// Current session
		public $session             = NULL;

		public $domain              = 'mycredpartwoo';

		/**
		 * Setup Instance
		 * @since 1.0
		 * @version 1.0
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __clone() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Not allowed
		 * @since 1.0
		 * @version 1.0
		 */
		public function __wakeup() { _doing_it_wrong( __FUNCTION__, 'Cheatin&#8217; huh?', $this->version ); }

		/**
		 * Define
		 * @since 1.0
		 * @version 1.0
		 */
		private function define( $name, $value, $definable = true ) {
			if ( ! defined( $name ) )
				define( $name, $value );
			elseif ( ! $definable && defined( $name ) )
				_doing_it_wrong( 'myCRED_WooCommerce_Plus->define()', 'Could not define: ' . $name . ' as it is already defined somewhere else!', $this->version );
		}

		/**
		 * Require File
		 * @since 1.0
		 * @version 1.0
		 */
		public function file( $required_file ) {
			if ( file_exists( $required_file ) )
				require_once $required_file;
			else
				_doing_it_wrong( 'myCRED_WooCommerce_Plus->file()', 'Requested file ' . $required_file . ' not found.', $this->version );
		}

		/**
		 * Construct
		 * @since 1.0
		 * @version 1.0
		 */
		public function __construct() {

			add_action( 'admin_notices', array( $this, 'meet_requirements' ) );
				
			if ( ! defined( 'myCRED_VERSION' ) ) return;
				
			add_action( 'admin_init', array( $this,'deactivate_previous_partial_payment_plugin' ))	;

			$this->plugin = plugin_basename( __FILE__ );
			$this->define_constants();
			$this->includes();		

			add_action( 'wp_enqueue_scripts',    array( $this, 'enqueue_styles' ),20 );
			add_action( 'mycred_init',           array( $this, 'load_textdomain' ), 5 );
			add_filter( 'plugin_action_links',   array( $this, 'disable_plugin_deactivation' ), 10, 4 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ),20 );
		 		
		}
		
		public function deactivate_previous_partial_payment_plugin() {

		    if ( is_plugin_active( 'mycred-partial-woo/mycred-partial-woo.php' ) ) {

				deactivate_plugins( 'mycred-partial-woo/mycred-partial-woo.php' );

			} 

		}

		 /**
         * Meet Requirements
         * Check if meet requirements or not
         * @since 1.0
         * @version 1.0
         */
        public function meet_requirements()
        {
            if ( !is_plugin_active( 'woocommerce/woocommerce.php' ) )
            {
                    ?>
                    <div class="notice notice-error is-dismissible">
                        <p><?php _e( 'In order to use myCRED WooCommerce Plus, Install and activate <a href="https://wordpress.org/plugins/woocommerce/">WooCommerce</a>', 'mycredpartwoo' ); ?></p>
                    </div>
                    <?php
            }
            if ( !is_plugin_active( 'mycred/mycred.php' ) )
            {
                ?>
                <div class="notice notice-error is-dismissible">
                    <p><?php _e( 'In order to use myCRED WooCommerce Plus, Install and activate <a href="https://wordpress.org/plugins/mycred/">myCRED</a>', 'mycredpartwoo' ); ?></p>
                </div>
                <?php
            }
        }
		
		
		public function disable_plugin_deactivation( $actions, $plugin_file, $plugin_data, $context ) {

			if ( array_key_exists( 'activate', $actions ) && in_array( $plugin_file, array( 'mycred-partial-woo/mycred-partial-woo.php' ) ) )
				unset( $actions['activate'] );
			return $actions;

		}
		 
		 /**
		 * Define Constants
		 * First, we start with defining all requires constants if they are not defined already.
		 * @since 1.0
		 * @version 1.0
		 */
		private function define_constants() {

			$this->define( 'MYCRED_WOOPLUS_VERSION',        $this->version );
			$this->define( 'MYCRED_WOOPLUS_SLUG',           $this->slug );
			$this->define( 'MYCRED_WOOPLUS_THIS',           __FILE__ );
			$this->define( 'MYCRED_WOOPLUS_ROOT_DIR',       plugin_dir_path( MYCRED_WOOPLUS_THIS ) );
			$this->define( 'MYCRED_WOOPLUS_INCLUDES_DIR',   MYCRED_WOOPLUS_ROOT_DIR . 'includes/' );
			$this->define( 'MYCRED_WOOPLUS_TEMPLATES_DIR',  MYCRED_WOOPLUS_ROOT_DIR . 'templates/' );
			$this->define( 'MYCRED_WOOPLUS_LICENSING_DIR',  MYCRED_WOOPLUS_ROOT_DIR . 'Licensing/' );
            $this->define( 'myCRED_WOOPLUS_SHORTCODES_DIR', MYCRED_WOOPLUS_INCLUDES_DIR . 'shortcodes/');

		}

		/**
		 * Include Plugin Files
		 * @since 1.0
		 * @version 1.1
		 */
		public function includes() {
			
			// add woocommerce tab settings 
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-wooplus-settings.php' );
			
			// add product ristrict code
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-ristrict-product.php' );

		 	//Adding Hooks
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'hooks/mycred-hook-referrals.php' );

			// add badge rank coupons code
			 $this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-badge-rank-coupons.php' );
			 
			 // add product discount for rank
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-rank-product-discount.php' );

			// show products by ranks
			$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-woocommerce-show-products-by-ranks.php' );

			// add points history code			
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-points-history.php' );
	
			
			// add partial payment code			
			$this->file( MYCRED_WOOPLUS_ROOT_DIR . 'mycred-partial-woo.php' );
			
			// Reward points product and checkout and global reward option		
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-reward-product.php' );	

            //wp-experts-product-refferal
            $this->file(myCRED_WOOPLUS_SHORTCODES_DIR . 'mycred-woocommerce-referrals-shortcode.php');
            $this->file(MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-product-referral-hook.php');

			//Add WooCommerce Subscription Gateway Support
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-woocommerce-subscription-gateway.php' );
			
			//Add WooCommerce MY Account tab for badges,Ranks,Blance,Level 
		 	$this->file( MYCRED_WOOPLUS_INCLUDES_DIR . 'mycred-my-account.php' );
			
		}

			/**
		 * Enqueue Scripts
		 * @since 1.0
		 * @version 1.0
		 */

		public static function enqueue_styles() {

			wp_register_style(
				'woo-plus',
				plugins_url( 'assets/css/woo-plus.css', MYCRED_WOOPLUS_THIS ),
                '',
                MYCRED_WOOPLUS_VERSION
			);

			wp_enqueue_style( 'woo-plus', '', '', MYCRED_WOOPLUS_VERSION );

		}

		public static function enqueue_scripts() {

			wp_register_script(
				'partial_payment_setting',
				plugins_url( 'assets/js/partial_payment_setting.js', MYCRED_WOOPLUS_THIS ),
                '',
                MYCRED_WOOPLUS_VERSION
			);
			if( 
				isset( $_GET['page'] ) && $_GET['page'] == 'wc-settings' 
				&& 
				isset( $_GET['section'] ) && $_GET['section'] == 'partial_payments'
			)
			{
				wp_enqueue_script( 'partial_payment_setting', '', '', MYCRED_WOOPLUS_VERSION );
			}
		}

		/**
		 * Load Textdomain
		 * @since 1.0
		 * @version 1.2
		 */
		public function load_textdomain() {

			if ( class_exists('myCRED_License') ) {
				
				new myCRED_License( 
					array(
						'version' => $this->version,
						'slug'    => $this->slug,
						'base'    => __FILE__
					)
				);

			}
			else {

				add_action( 'admin_notices', array( $this, 'license_admin_notice' ) );

			}

			// Load Translation
			$locale = apply_filters( 'plugin_locale', get_locale(), $this->domain );

			load_textdomain( $this->domain, WP_LANG_DIR . '/' . $this->domain . '-' . $locale . '.mo' );
			load_plugin_textdomain( $this->domain, false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		}

		public function license_admin_notice() {

			echo '<div class="notice notice-error is-dismissible"><p>myCRED WooCommerce Plus - WooCommerce requires myCred 2.3.1 or greater version to work your license properly.</p></div>';

		}
 
	}
endif;

add_action( 'plugins_loaded', 'myCRED_WooCommerce_Plus_RUN' );

function myCRED_WooCommerce_Plus_RUN() {
	return myCRED_WooCommerce_Plus::instance();
}
 