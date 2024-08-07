<?php

/**
 * Woo Point Rewards by Order Total
 * Reward store purchases by paying a percentage of the order total
 * as points to the buyer.
 * @version 1.1
 */

 
if ( ! class_exists( 'mycred_woo_reward_product' ) ) :
class mycred_woo_reward_product {
		
		public function __construct() {
			add_action( 'woocommerce_before_add_to_cart_form', array( $this , 'woocommerce_before_add_to_cart_button') );

			add_action( 'woocommerce_order_status_completed',  array( $this , 'mycred_pro_reward_order_percentage' ));

			add_action( 'woocommerce_checkout_before_customer_details',  array( $this , 'woocommerce_review_order_before_order_total' ), 10);

			add_action( 'woocommerce_before_cart_table',  array( $this , 'woocommerce_review_order_before_order_total' ), 10);

			add_filter( 'woocommerce_get_item_data', array( $this ,  'woocommerce_get_item_data'), 10, 2 );	
			
			add_action( 'wp_head', array( $this ,  'wp_head') );	
			
			add_action( 'woocommerce_before_add_to_cart_quantity',  array( $this ,'display_dropdown_variation_add_cart' ));

		}
 
		public function display_dropdown_variation_add_cart() {

		global $product;

		if ( $product->is_type('variable') && get_option( 'reward_single_page_product' ) == 'yes' ) {
		   
		  ?>
		  <script>
		  jQuery(document).ready(function($) {
			  
			function call_rewards_points(){
				if( '' != jQuery('input.variation_id').val() && 0 != jQuery('input.variation_id').val() ) {
					var var_id = jQuery('input.variation_id').val();
					template = '';
					if(typeof(mycred_variable_rewards[var_id]) != 'undefined' && mycred_variable_rewards[var_id] != null) {	
					 
					jQuery.each( mycred_variable_rewards[var_id], function( index, value ) {
					
					template += '<span class="rewards_span"> '+ label_Earn +' ' + value + ' ' + mycred_point_types[index] + '</span>';
 
					});
 
					document.getElementById("rewards_points_wrap").innerHTML = template;
					} else {
						document.getElementById("rewards_points_wrap").innerHTML = '';
					}
				}
			}
			call_rewards_points();			
			jQuery('input.variation_id').change( function(){ 
				call_rewards_points()
			});
			
		  });
		  </script>
		  <?php

		}

		}

		public function wp_head() {
		 
			if ( is_product() ) {
				
			$mycred_rewards_array = array();	
				
			$product = wc_get_product( get_the_ID() );
			if( $product->is_type( 'variable' ) ) {	
			$available_variations = $product->get_available_variations();	
			$mycred = mycred_get_types();
				foreach ($available_variations as $variation) {
					$variation_id = $variation['variation_id'];
					$mycred_rewards = get_post_meta( $variation_id, '_mycred_reward', true ); 
					$parent_reward = (array) get_post_meta( get_the_ID() , 'mycred_reward', true );
					if (!empty($mycred_rewards )){
						$mycred_rewards_array[$variation_id] = $mycred_rewards;
					} elseif (!empty($parent_reward )) {
						$mycred_rewards_array[$variation_id] = $parent_reward;
					}
				}
			}
				
				if ( !empty($mycred_rewards_array ) ) { 
				?>
				<script type="text/javascript">
					var mycred_variable_rewards = <?php echo json_encode( $mycred_rewards_array ); ?>;
					var mycred_point_types = <?php echo json_encode( $mycred ); ?>;
					var label_Earn = <?php echo "'" . __( "Earn ", 'mycredpartwoo' ) . "'"; ?>;
					
				</script>
				<?php 
				}
			}
			
		}
		
		public function woocommerce_get_item_data( $item_data, $cart_item ) {
			
			$product = wc_get_product( $cart_item['product_id'] );
			if( $product->is_type( 'variable' ) ) {
				$mycred_rewards = get_post_meta( $cart_item['variation_id'], '_mycred_reward', true ); 
			} else {
				$mycred_rewards = get_post_meta( $cart_item['product_id'], 'mycred_reward', true ); 
			}
			 
			if($mycred_rewards){

				if ( (is_cart() && 'yes'==get_option('reward_cart_product_meta')) || (is_checkout() && 'yes'==get_option('reward_checkout_product_meta')) ) {
					foreach( $mycred_rewards as $mycred_reward_key => $mycred_reward_value ) {	
						
						$is_plural_reward = ( $mycred_reward_value < 2 );

						$value = '<span class="reward_span">'. $mycred_reward_value .' ' .mycred_get_point_type_name($mycred_reward_key, $is_plural_reward) .'</span>'	;
	
						$item_data[] = array(
							'key'     => __( '<span style="reward_span">Earn</span>', 'mycredpartwoo' ),
							'value'   => __( $value, 'mycredpartwoo' ),
							'display' => '',
						);

					}
				} 

			}

			return $item_data;
		}
 
		public function woocommerce_review_order_before_order_total() {  

			do_action( 'woocommerce_set_cart_cookies',  true );
			$mycred = new myCRED_Settings();
            $decimal_format = $mycred->format['decimals'];
				
			$total_reward_point = array();
			$message = '';
			
			foreach ( WC()->cart->get_cart() as $cart_item ) {
				// var_dump($cart_item);
				
				
			$product = wc_get_product( $cart_item['product_id'] );
			if( $product->is_type( 'variable' ) ) {
				$mycred_rewards = get_post_meta( $cart_item['variation_id'], '_mycred_reward', true ); 
			} else {
				$mycred_rewards = get_post_meta( $cart_item['product_id'], 'mycred_reward', true ); 
			}
				if($mycred_rewards){
					
					foreach( $mycred_rewards as $mycred_reward_key => $mycred_reward_value ){ 
						
						if (isset($total_reward_point[$mycred_reward_key])) {
							
							$total_reward_point[$mycred_reward_key]['total'] = $total_reward_point[$mycred_reward_key]['total'] + $mycred_reward_value * $cart_item['quantity'];
							
						}else{
							
							$total_reward_point[$mycred_reward_key] = array( 'name' => $mycred_reward_key ,'total' => $mycred_reward_value * $cart_item['quantity']);
						}
					}
				}	
			}

			$message .= __( "Earn ", 'mycredpartwoo' );
			$i = 1;
			$count = count($total_reward_point);
			
			if ( ! empty($total_reward_point) ) {
				foreach( $total_reward_point as $mycred_reward_key => $mycred_reward_value ){
				
					$mycred = mycred( $mycred_reward_key );

					if(1==$count) {
						$message .= $mycred->format_creds( $mycred_reward_value['total'] ) .' '. $mycred->plural();
					}else {
						if($i<$count) {
							$message .= $mycred->format_creds( $mycred_reward_value['total'] ) .' '. $mycred->plural() .', ';
						} else {
							$message .= ' and ' . $mycred->format_creds( $mycred_reward_value['total'] ) .' '. $mycred->plural();
						}
					}

					$i++;
					
				}
			}
					
			wc_clear_notices();

			$reward_points_global = get_option('reward_points_global', true);

			//wp_die(WC()->cart->get_subtotal());

			if ( 'yes'===$reward_points_global ) {
				/*** mufaddal start work from here */
				$type = get_option('mycred_point_type', true);
				$reward_points_global_type = get_option('reward_points_global_type', true);
				$exchange_rate = get_option('reward_points_exchange_rate', true);
				$reward_points_global_message = get_option('reward_points_global_message', true);
				$reward_points_global_type_val = get_option('reward_points_global_type_val', true);
				$reward_points_global_type_val = (float) $reward_points_global_type_val;
				$cost = WC()->cart->get_subtotal();
				//wp_die($type);

				if ('fixed'===$reward_points_global_type) {

					$reward = number_format($reward_points_global_type_val, $decimal_format, '.', '');

				}

				if ('percentage'===$reward_points_global_type) {
					$reward = $cost * ( $reward_points_global_type_val / 100 );
					$reward = number_format($reward, $decimal_format, '.', '');
				}

				if ('exchange'===$reward_points_global_type) {


					
					$reward = ( $cost/$exchange_rate );
					$reward = number_format($reward, $decimal_format, '.', '');

				}
				
				
				$message = str_replace("{points}", $reward, $reward_points_global_message);
				$message = str_replace("{type}", $type, $message);
				$message = str_replace("mycred_default", "Points", $message);
				if ($cost > 0 && !empty($reward_points_global_message)) {
					wc_print_notice( __( $message, 'mycredpartwoo' ) ,  $notice_type = 'notice' ); 
				}				

			} else {

				if ( (is_cart() && 'yes'==get_option('reward_cart_product_total')) || (is_checkout() && 'yes'==get_option('reward_checkout_product_total')) ) {
					if ( ! empty($total_reward_point) ) {
						wc_print_notice(  __( $message, 'mycredpartwoo' ) ,  $notice_type = 'notice' ); 
					}
				}

			}

		}
		
		public function woocommerce_before_add_to_cart_button(){
			
			$product = wc_get_product( get_the_ID() );
			
			
			 	
			if( get_option( 'reward_single_page_product' ) == 'yes' ) {
				if( $product->is_type( 'simple' ) ) {		
				$mycred_rewards = get_post_meta( get_the_ID(), 'mycred_reward', true );
					
					$i = 1;

					if(!empty($mycred_rewards)) {
						$count = count($mycred_rewards);
					}

					if($mycred_rewards){

						echo '<div id="rewards_points_wrap">';
						foreach($mycred_rewards as $mycred_reward_key => $mycred_reward_value) {
                           
							$is_plural_reward = ( $mycred_reward_value < 2 );
							
							$mycred_point_type_name = mycred_get_point_type_name($mycred_reward_key, $is_plural_reward);

							echo '<span class="rewards_span"> ' . sprintf( __( 'Earn %s %s', 'mycredpartwoo' ), $mycred_reward_value, $mycred_point_type_name ) . '</span>';
						}
						echo'</div>';
					}
					
				} else {
					echo '<div id="rewards_points_wrap"></div>';
				}
			}
		
			
		}
		
		public function mycred_pro_reward_order_percentage( $order_id ) {

            if ( ! function_exists( 'mycred' ) ) return;

            // Load myCRED
            $mycred = mycred();

			$reward_points_global = get_option('reward_points_global', true);

			if ( 'yes'===$reward_points_global ) {
				//wp_die('pls stop');
				$reward_points_global_type = get_option('reward_points_global_type', true);
				$reward_points_global_type_val = get_option('reward_points_global_type_val', true);
				$exchange_rate = get_option('reward_points_exchange_rate', true);
				$reward_points_global_message = get_option('reward_points_global_message', true);
				$type = get_option('mycred_point_type', true);
			}


			// Get Order
			$order   = new WC_Order( $order_id );
			$cost    = $order->get_subtotal();
			$user_id = get_post_meta($order_id, '_customer_user', true);
			$payment_method = get_post_meta( $order_id, '_payment_method', true );

			// Do not payout if order was paid using points
			if ( $payment_method == 'mycred' ) return;

			// Make sure user only gets points once per order
			if ( $mycred->has_entry( 'reward', $order_id, $user_id ) ) return;

            // percentage based point
			if ( isset($reward_points_global_type) && 'percentage'===$reward_points_global_type ) {

				// Reward example 25% in points.
				$points = (float) $reward_points_global_type_val;
				$reward  = $cost * ( $points / 100 );
				$reward = number_format($reward, 2, '.', '');

			}

			// fixed point
			if ( isset($reward_points_global_type) && 'fixed'===$reward_points_global_type ) {

				// Reward example 25% in points.
				$points = (float) $reward_points_global_type_val;
				$reward = number_format($points, 2, '.', '');

			}

			// exchange rate based points
			if ( isset($reward_points_global_type) && 'exchange'===$reward_points_global_type ) {

				// Reward example 25% in points.
				$points = (float) $exchange_rate;
				$reward  = ($cost/$points);
				$reward = number_format($reward, 2, '.', '');
				//wp_die('rewards in exchange rate '. $reward);

				if($payment_method == 'fmi_ach_subscribtion'){
					$reward = ($reward*2);
					$reward = number_format($reward, 2, '.', '');
				}

			}


			// Add reward
            $mycred->add_creds('reward', $user_id, $reward, __( 'Reward for store purchase', 'mycredpartwoo' ), $order_id, array( 'ref_type' => 'post' ), $type );


			if ( 'yes'===$reward_points_global ) {
			    if ( isset( $_GET['post_type'] ) && isset( $_GET['bulk_action'] ) && $_GET['post_type'] != 'shop_order' && $_GET['bulk_action'] == 'marked_completed' )
				    add_filter('mycred_exclude_user', array($this, 'stop_points_for_single_product'), 10, 3);
			}

		}

		public function stop_points_for_single_product( $false, $user_id, $obj) {
			return true;
		}
		
}

$mycred_woo_reward_product = new mycred_woo_reward_product();

endif;
?>