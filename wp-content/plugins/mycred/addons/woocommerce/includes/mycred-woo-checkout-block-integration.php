<?php

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\WooCommerce\Blocks\Integrations\IntegrationInterface;

class myCred_Woo_Checkout_Blocks_Integration implements IntegrationInterface {

    /**
	 * The name of the integration.
	 *
	 * @return string
	 */
	public function get_name() {
		return 'mycredwoo';
	}

	/**
	 * When called invokes any initialization/setup for the integration.
	 */
	public function initialize() {
		$this->register_block_frontend_scripts();
		$this->register_block_editor_scripts();
	}

	/**
	 * Returns an array of script handles to enqueue in the frontend context.
	 *
	 * @return string[]
	 */
	public function get_script_handles() {
		return array( 'mycred-woo-checkout-block' );
	}

	/**
	 * Returns an array of script handles to enqueue in the editor context.
	 *
	 * @return string[]
	 */
	public function get_editor_script_handles() {
		return array( 'mycred-woo-editor-checkout-block' );
	}

	/**
	 * An array of key, value pairs of data made available to the block on the client side.
	 *
	 * @return array
	 */
	public function get_script_data() {
		global $woocommerce;

		$available_gateways = $woocommerce->payment_gateways->get_available_payment_gateways();
		if ( ! isset( $available_gateways['mycred'] ) ) { 
			return array();
		}
		$show_total = $available_gateways['mycred']->get_option( 'show_total' );
		
		return array(
			'show_total' => $show_total,
		);
	}

	/**
	 * Register scripts for delivery date block editor.
	 *
	 * @return void
	 */
	public function register_block_editor_scripts() {
        $script_asset_path = plugins_url( '/build/checkout/index.asset.php', MYCRED_WOO_THIS );
		$script_asset      = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);

        $script_url = plugins_url( '/build/checkout/index.js', MYCRED_WOO_THIS );

		wp_register_script(
			'mycred-woo-editor-checkout-block',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
       
	}

	/**
	 * Register scripts for frontend block.
	 *
	 * @return void
	 */
	public function register_block_frontend_scripts() {

        /**
         * Register JS for Adding field to Checkout Block
         */
        $script_asset_path  = plugins_url( '/build/checkout/checkout-block-frontend.asset.php', MYCRED_WOO_THIS );
		$script_asset       = file_exists( $script_asset_path )
			? require $script_asset_path
			: array(
				'dependencies' => array(),
				'version'      => $this->get_file_version( $script_asset_path ),
			);
        
        $script_url = plugins_url( '/build/checkout/checkout-block-frontend.js', MYCRED_WOO_THIS );
		wp_register_script(
			'mycred-woo-checkout-block',
			$script_url,
			$script_asset['dependencies'],
			$script_asset['version'],
			true
		);
	}

	/**
	 * Get the file modified time as a cache buster if we're in dev mode.
	 *
	 * @param string $file Local path to the file.
	 * @return string The cache buster value to use for the given file.
	 */
	protected function get_file_version( $file ) {
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && file_exists( $file ) ) {
			return filemtime( $file );
		}
		return MYCRED_WOO_VERSION;
	}

}