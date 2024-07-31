<?php
/**
 * Plugin Name: Coffee Shop Addon
 * Description: It's a Business Management Plguin
 * Author: Raihan Reza
 * Version: 1.0.3
 * Requires at least: 5.6
 * Tested up to: 6.5.3
 * Requires PHP: 7.0
 * Text Domain: coffee_shop
 * License: GPL v2 or later
 * License URI:https://www.gnu.org/licenses/gpl-2.0.html
 * @author    Raihan Reza
 * @category  Genarel
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL v2 or later
 * 
 */

/*
Coffee Shop Addon is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

Coffee Shop Addon is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Coffee Shop Addon. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

defined( 'ABSPATH' ) or exit;

if( !class_exists( 'Coffee_Shop_Addon' )){
    class Coffee_Shop_Addon {
        function __construct() {
            $this->define_constants(); 

            require_once( Coffee_Shop_Addon_PATH . 'functions/coffee_shop-functions.php' );
        

        }

        // define Constants
        protected function define_constants(){
            define( 'Coffee_Shop_Addon_PATH', esc_url_raw(plugin_dir_path( __FILE__ )) );
            define( 'Coffee_Shop_Addon_URL', esc_url(plugin_dir_url( __FILE__ ) ));
            define( 'Coffee_Shop_Addon_VERSION', '1.0.0' );
        }

        public static function activate(){

            global $wpdb;
            $table_name = $wpdb->prefix . 'loyality_points';
    
            // SQL to create the table
            $charset_collate = $wpdb->get_charset_collate();
    
            $sql = "CREATE TABLE $table_name (
                id bigint(20) NOT NULL AUTO_INCREMENT,
                user_id bigint(20) NOT NULL,
                order_id bigint(20) NOT NULL,
                points_earned bigint(20) NOT NULL default 0,
                points_status varchar(255),
                status tinyint(1) NOT NULL DEFAULT 1,
                created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY  (id)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }

        public static function deactivate(){
            flush_rewrite_rules();
        }

        public static function uninstall(){
        }

     
    }
}

if( class_exists( 'Coffee_Shop_Addon' ) ){
    register_activation_hook( __FILE__, array( 'Coffee_Shop_Addon', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'Coffee_Shop_Addon', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'Coffee_Shop_Addon', 'uninstall' ) );

    $Coffee_Shop_Addon = new Coffee_Shop_Addon();
}