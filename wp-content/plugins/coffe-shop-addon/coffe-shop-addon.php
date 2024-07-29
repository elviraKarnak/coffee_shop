<?php
/**
 * Coffe Shop Addon
 *
 * @package       COFFESHOPA
 * @author        Rehain Reza
 * @license       gplv2
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   Coffe Shop Addon
 * Plugin URI:    https://elvirainfotech.live/
 * Description:   This is some demo short description...
 * Version:       1.0.0
 * Author:        Rehain Reza
 * Author URI:    https://elvirainfotech.live/
 * Text Domain:   coffe-shop-addon
 * Domain Path:   /languages
 * License:       GPLv2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 *
 * You should have received a copy of the GNU General Public License
 * along with Coffe Shop Addon. If not, see <https://www.gnu.org/licenses/gpl-2.0.html/>.
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
// Plugin name
define( 'COFFESHOPA_NAME',			'Coffe Shop Addon' );

// Plugin version
define( 'COFFESHOP_VERSION',		'1.0.0' );

// Plugin Root File
define( 'COFFESHOP_PLUGIN_FILE',	__FILE__ );

// Plugin base
define( 'COFFESHOP_PLUGIN_BASE',	plugin_basename( COFFESHOP_PLUGIN_FILE ) );

// Plugin Folder Path
define( 'COFFESHOP_PLUGIN_DIR',	plugin_dir_path( COFFESHOP_PLUGIN_FILE ) );

// Plugin Folder URL
define( 'COFFESHOP_PLUGIN_URL',	plugin_dir_url( COFFESHOP_PLUGIN_FILE ) );

class LoyalityPoints {
	private $loyality_points_options;

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'loyality_points_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'loyality_points_page_init' ) );
		add_action('init', array( $this,'custom_add_loyalty_rewards_endpoint'));
		add_action('woocommerce_account_loyalty-rewards_endpoint', array( $this,'custom_loyalty_rewards_content'));
		add_filter('woocommerce_account_menu_items', array( $this,'custom_add_loyalty_rewards_link_my_account'));
		add_action('after_switch_theme',array( $this, 'custom_flush_rewrite_rules'));
		register_activation_hook(__FILE__, array( $this,'create_loyality_points_table'));
		add_action('woocommerce_thankyou', array( $this,'loyality_transaction'), 10, 1);
		add_action('add_meta_boxes',array( $this, 'add_coffee_metabox'));
		add_action('save_post', array( $this, 'save_coffee_metabox'));
	}

		function add_coffee_metabox() {
		    add_meta_box(
		        'coffee_metabox',          // Metabox ID
		        'Is Coffee Product',       // Metabox title
		        array($this,'coffee_metabox_callback'), // Callback function
		        'product',                 // Post type
		        'side',                    // Context (side column)
		        'high'                     // Priority
		    );
		}


		function coffee_metabox_callback($post) {
	    	// Retrieve the current value from the database
		    $is_coffee = get_post_meta($post->ID, '_is_coffee', true);
		    wp_nonce_field('save_coffee_metabox', 'coffee_metabox_nonce');
		    ?>
		    <p>
		        <label for="is_coffee_checkbox">
		            <input type="checkbox" id="is_coffee_checkbox" name="is_coffee_checkbox" value="yes" <?php checked($is_coffee, 'yes'); ?> />
		            This is a coffee product
		        </label>
		    </p>
		    <?php
		}

		function save_coffee_metabox($post_id) {
		    // Check if our nonce is set.
		    if (!isset($_POST['coffee_metabox_nonce'])) {
		        return $post_id;
		    }

		    $nonce = $_POST['coffee_metabox_nonce'];

		    // Verify that the nonce is valid.
		    if (!wp_verify_nonce($nonce, 'save_coffee_metabox')) {
		        return $post_id;
		    }

		    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
		    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		        return $post_id;
		    }

		    // Check the user's permissions.
		    if ('product' == $_POST['post_type']) {
		        if (!current_user_can('edit_post', $post_id)) {
		            return $post_id;
		        }
		    }

		    // Sanitize the user input.
		    $is_coffee = isset($_POST['is_coffee_checkbox']) && $_POST['is_coffee_checkbox'] === 'yes' ? 'yes' : '';

		    // Update the meta field in the database.
		    update_post_meta($post_id, '_is_coffee', $is_coffee);
		}

		function loyality_transaction( $order_id ) {
		    if ( ! $order_id )
		        return;

		     global $wpdb;

		    // Allow code execution only once 
		    if( ! get_post_meta( $order_id, '_loyality_transaction', true ) ) {

		        	// Get an instance of the WC_Order object
		        	$order = wc_get_order( $order_id );

		        	// Get the order key
			        $user_id = $order->get_user_id();
			        $stamp_earned=0;
			        $stamps_collected_tbl = $wpdb->prefix . 'stamps_collected';
			        $query = $wpdb->prepare("SELECT stamps_collected FROM $stamps_collected_tbl WHERE user_id = %d", $user_id);
			        $get_stamps_collected = $wpdb->get_var($query);

			        if ($user_id > 0) {

			        	 foreach ( $order->get_items() as $item_id => $item ) {

				            // Get the product object
				            $product = $item->get_product();
				            // Get the product Id
				            $product_id = $product->get_id();
				            $coffee_product=get_post_meta($product_id, '_is_coffee', true);
				            if($coffee_product){
				            	$stamp_earned=$stamp_earned+1;
				            }
        				}

        				if($stamp_earned>0){
        					$total=$stamp_earned+$get_stamps_collected;

        					if($get_stamps_collected){

        						$updated = $wpdb->update(
							        $stamps_collected_tbl,
							        array(
							            'stamps_collected' => $total // Data to update
							        ),
							        array(
							            'user_id' => $user_id // Where clause
							        ),
							        array(
							            '%d' // Data format (integer)
							        ),
							        array(
							            '%d' // Where clause format (integer)
							        )
							    );

        					}else{

        						$wpdb->insert($stamps_collected_tbl, array(
						            'user_id' => $user_id,
						            'stamps_collected' => $total,
						            'status' => 1
				        		));



        					}

        					
        				}



			        // Calculate the points earned based on the order total (example: 1 point per $10 spent)
				        $order_total = $order->get_total() - $order->get_shipping_total();        
				        $points_earned = floor($order_total / 1); // 1 point per $1

				        // Add points to the custom loyalty points table
				        $table_name = $wpdb->prefix . 'loyality_points';
				        $wpdb->insert($table_name, array(
				            'user_id' => $user_id,
				            'points_earned' => $points_earned,
				            'points_redeemed' => 0,
				            'status' => 1
				        ));
			    	}

			    	update_post_meta( $order_id,'_loyality_transaction', true);
		      
		    
		    }
		}

	function create_loyality_points_table() {
	    global $wpdb;
	    $table_name = $wpdb->prefix . 'loyality_points';

	    // SQL to create the table
	    $charset_collate = $wpdb->get_charset_collate();

	    $sql = "CREATE TABLE $table_name (
	        id bigint(20) NOT NULL AUTO_INCREMENT,
	        user_id bigint(20) NOT NULL,
	        points_earned bigint(20) NOT NULL default 0,
	        points_redeemed bigint(20) NOT NULL default 0,
	        status tinyint(1) NOT NULL DEFAULT 1,
	        created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
	        PRIMARY KEY  (id)
	    ) $charset_collate;";
	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql);

	    $table_name2 = $wpdb->prefix . 'stamps_collected';

	    // SQL to create the table
	    $charset_collate = $wpdb->get_charset_collate();

	    $sql2 = "CREATE TABLE $table_name2 (
	        id bigint(20) NOT NULL AUTO_INCREMENT,
	        user_id bigint(20) NOT NULL,
	        stamps_collected bigint(20) NOT NULL default 0,	       
	        status tinyint(1) NOT NULL DEFAULT 1,
	        created datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
	        PRIMARY KEY  (id)
	    ) $charset_collate;";
	    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	    dbDelta($sql2);

	    
	}

	function custom_flush_rewrite_rules() {
    	flush_rewrite_rules();
	}

	function custom_add_loyalty_rewards_link_my_account($items) {
		unset($items['customer-logout']);
	    $items['loyalty-rewards'] = 'Loyalty Rewards';
	     $items['customer-logout'] = 'Logout';
	    return $items;
	}

	function custom_loyalty_rewards_content() {
	    echo '<h3>Loyalty Rewards</h3>';
	    echo '<p>Here you can see your loyalty rewards.</p>';
	    global $wpdb;
	    $user_id = get_current_user_id();
		$stamps_collected_tbl = $wpdb->prefix . 'stamps_collected';
		$query = $wpdb->prepare("SELECT stamps_collected FROM $stamps_collected_tbl WHERE user_id = %d", $user_id);
		$get_stamps_collected = $wpdb->get_var($query);
		if(!$get_stamps_collected):
			$get_stamps_collected=0;
		endif;

		$points_tbl = $wpdb->prefix . 'loyality_points';
		$query2 = $wpdb->prepare("SELECT SUM(points_earned) as points_earned FROM $points_tbl WHERE user_id = %d", $user_id);

    	// Prepare and execute the SQL query to get the total
    	$total = $wpdb->get_var($query2);
    	if(!$total):
			$total=0;
		endif;
		?>

		<p> Total coffee stamps: <?php echo $get_stamps_collected;?></p>

		<p> Total Points : <?php echo $total;?></p>


		<?php 

	    // Add your custom content here, e.g., displaying loyalty points, rewards, etc.
	}
	// Register the new endpoint
	function custom_add_loyalty_rewards_endpoint() {
    	add_rewrite_endpoint('loyalty-rewards', EP_ROOT | EP_PAGES);
	}

	public function loyality_points_add_plugin_page() {
		add_menu_page(
			'Loyality Points', // page_title
			'Loyality Points', // menu_title
			'manage_options', // capability
			'loyality-points', // menu_slug
			array( $this, 'loyality_points_create_admin_page' ), // function
			'dashicons-admin-generic', // icon_url
			2 // position
		);
	}

	public function loyality_points_create_admin_page() {
		$this->loyality_points_options = get_option( 'loyality_points_option' ); ?>

		<div class="wrap">
			<h2>Loyality Points</h2>
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
	<?php }

	public function loyality_points_page_init() {
		register_setting(
			'loyality_points_option_group', // option_group
			'loyality_points_option', // option_name
			array( $this, 'loyality_points_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'loyality_points_setting_section', // id
			'Settings', // title
			array( $this, 'loyality_points_section_info' ), // callback
			'loyality-points-admin' // page
		);

		add_settings_field(
			'points_per', // id
			'Points per €', // title
			array( $this, 'points_per_callback' ), // callback
			'loyality-points-admin', // page
			'loyality_points_setting_section' // section
		);
	}

	public function loyality_points_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['points_per'] ) ) {
			$sanitary_values['points_per'] = sanitize_text_field( $input['points_per'] );
		}

		return $sanitary_values;
	}

	public function loyality_points_section_info() {
		
	}

	public function points_per_callback() {
		printf(
			'<input class="regular-text" type="text" name="loyality_points_option[points_per]" id="points_per" value="%s">',
			isset( $this->loyality_points_options['points_per'] ) ? esc_attr( $this->loyality_points_options['points_per']) : ''
		);
	}

}
$loyality_points = new LoyalityPoints();