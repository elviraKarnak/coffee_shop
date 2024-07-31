<?php

/**
 * Woo display current badges, ranks, balance, Level.
 * @version 1.1
 */

if ( ! class_exists( 'mycred_woo_my_account' ) ) :
class mycred_woo_my_account {
		
		public function __construct() {
			
			// Add woocommerce back end settings main menu link
			add_filter( 'mycred_woo_setting_sections', array( $this ,  'mycred_woo_setting_sections'), 10, 1 );	
			
			// Add woocommerce back end settings tab content for badges,Ranks,balance,Level 
			add_filter( 'wc_settings_wdvc_settings', array( $this ,  'mycred_settings_wdvc_settings'), 10, 2 );	
			
			// Enabled this features on front end
			if ( get_option( 'mycred_wooplus_tab_on' ) == 'yes' ) {
				
				// add tab css and icon
				add_action('wp_head', array( $this ,'my_custom_styles'), 100);
			
				// Add add rewrite endpoint
				add_action( 'init',  array( $this ,  'my_account_tab_endpoint') );
				
				//  Insert the new endpoint into the My Account menu
				add_filter( 'woocommerce_account_menu_items',  array( $this , 'mycred_link_my_account' ));
				
				// Add new query var
				add_filter( 'query_vars',  array( $this , 'mycred_query_vars'), 0 );
			
			}
			
		}
		
		public function my_account_tab_content() {
			?>
			<div class="mycred_my_account">
				<?php if(get_option( 'mycred_wooplus_show_my_balance' ) == 'yes'){ ?>
				<div class="mycred_tab_coloum balance">
					<h4><?php echo __( 'My Balance', 'mycredpartwoo' );?></h4>
					<hr>
					<div class="mybalance">
						<?php 
							$mycred_get_types = mycred_get_types();
							foreach($mycred_get_types as $mycred_get_type => $mycred_get_type_name){
							?>
								<div class="loop <?php echo $mycred_get_type; ?>"> 
								<div class="mycred_points_lable"><?php echo $mycred_get_type_name; ?></div>
								<div class="mycred_points"><?php echo do_shortcode( '[mycred_my_balance type="'.$mycred_get_type.'"]' ); ?></div>
								</div>
							<?php 
							}
							
						?>
					</div>
				</div>
				<?php } ?>
				<?php if(get_option( 'mycred_wooplus_show_earned_badges' ) == 'yes'){ ?>
				<div class="mycred_tab_coloum badges">
					<h4><?php echo __( 'My Badges', 'mycredpartwoo' );?></h4>
					<hr>
					<div class="mybadges">
						<?php echo do_shortcode( '[mycred_my_badges]' ); ?>
					</div>
				</div>
				<?php } ?>
				<?php if(get_option( 'mycred_wooplus_show_earned_ranks' ) == 'yes'){ ?>
				<div class="mycred_tab_coloum ranks">
					<h4><?php echo __( 'My Rank', 'mycredpartwoo' );?></h4>
					<hr>
					<div class="myranks">
						<?php echo do_shortcode( '[mycred_my_rank user_id="current" show_logo="1"]' ); ?>
					</div>
				</div>
				<?php } ?>
				<?php if(get_option( 'mycred_wooplus_show_my_level' ) == 'yes'){ ?>
				<div class="mycred_tab_coloum level">
					<h4><?php echo __( 'My Level', 'mycredpartwoo' );?></h4>
					<hr>
					<div id="mylevel_position" class="mylevel">
					<?php echo do_shortcode( '[mycred_leaderboard_position]' ); ?>
					</div>
				</div>
				<?php } ?>
			</div>
			
			 <?php 
			
			
			
		}
		
		public function my_custom_styles() {	
			//if (is_user_logged_in() && is_account_page()) { 
			?><style>
			.mycred_my_account .mycred_tab_coloum {
				border: 1px solid gainsboro;
				float: left;
				width: 45%;
				margin: 10px;
				padding: 5px;
				min-height: 160px;
				border-radius: 5px;
				-webkit-box-shadow: 12px 10px 16px -17px rgba(0,0,0,0.75);
				-moz-box-shadow: 12px 10px 16px -17px rgba(0,0,0,0.75);
				box-shadow: 12px 10px 16px -17px rgba(0,0,0,0.75);
			}
			.mycred_my_account .mycred_tab_coloum h4 {
				padding: 0px;
				margin: 0px;
				margin-left: 4px;
			}
			.mycred_my_account .mycred_tab_coloum hr {
				margin: 4px;
			}
			.mycred_points_lable {
				float: left;
				width: 50%;
			}
			.loop {
				border-bottom: 1px solid #e2e2e2;
				margin-left: 4px;
			}
			.mycred_my_account .mycred_tab_coloum.badges img{
				float: left;
				border: 1px solid #d4d4d4;
				margin: 2px;
			}
			.mycred_my_account .myranks img.attachment-post-thumbnail.size-post-thumbnail.wp-post-image {
				width: 100px;
				float: left;
				margin: 4px;
				border: 1px solid #d4d4d4;
			}
			.mycred-my-rank,.mycred_my_account .mylevel {
				line-height: 100px;
				font-size: 20px;
				font-weight: 400;
			}
			.mycred_my_account .mylevel {
				margin-left: 4px;
				text-align: center;
				font-size: 50px;
			}
			span.level_alphabet {
				font-size: 34px;
			}
			.loop:last-child { border-bottom: none; }
			</style>
			<script>
			jQuery( document ).ready(function() {
			element = document.getElementById("mylevel_position");
			if(typeof(element) != 'undefined' && element != null){	
			function nth(n){return["st","nd","rd"][((n+90)%100-10)%10-1]||"th"}
			mylevel = element.innerText;
			if (mylevel != "-"){
			document.getElementById("mylevel_position").innerHTML = 
			mylevel + '<span class="level_alphabet">' + nth(parseInt(mylevel)) + '</span>';
			}
			}
			});
			</script>
			<?php
			//}
		}
		
		public function my_account_tab_endpoint() {
			
			$this->tab_name = get_option( 'mycred_myaccount_tab_name' );
			$this->tab_endpoint_text = str_replace(' ', '-', strtolower($this->tab_name));
			
			// Note: add_action must follow 'woocommerce_account_{your-endpoint-slug}_endpoint' format
			add_action( 'woocommerce_account_'.$this->tab_endpoint_text.'_endpoint', array( $this ,  'my_account_tab_content' ));
			
			add_rewrite_endpoint( $this->tab_endpoint_text, EP_ROOT | EP_PAGES );
		}

		public function mycred_query_vars( $vars ) {
			
			$vars[] = $this->tab_endpoint_text;
			return $vars;
			
		}

		public function mycred_link_my_account( $items ) {
			
			$items[$this->tab_endpoint_text] = $this->tab_name;
			return $items;
			
		}

		public function mycred_woo_setting_sections( $sections ) {
			
			$sections[ 'mycred_myaccount' ] = __( 'My Account', 'mycredpartwoo' );
			return $sections;
			
		}
		
		public function mycred_settings_wdvc_settings(  $settings , $section ) {
			
			global $current_section ;
			if ( $current_section == 'mycred_myaccount' ) {
				$settings = array(
						'section_title' => array(
							'name'     => __( 'My Account Tab Settings', 'mycredpartwoo' ),
							'type'     => 'title',
							'desc'     => '',
							'id'       => 'wdvc_tab_demo_section_title',
							'desc_tip' => true,
						),
						'mycred_wooplus_tab_on' => array(	
							'name'     => __( 'Tab Enable', 'mycredpartwoo' ),
							'type'     => 'checkbox',
							'desc'     => __( 'Enable this option to show the tab in my account tab list.', 'mycredpartwoo' ),
							'id'       => 'mycred_wooplus_tab_on',
							'desc_tip' => true,
						),
						array(
							'title'       => __( 'Tab Name', 'mycredpartwoo' ),
							'id'          => 'mycred_myaccount_tab_name',
							'type'        => 'text',
							'placeholder' => __( 'Required', 'mycredpartwoo' ),
							'default'     => 'MyCred',
							'css'         => 'min-width:300px;'
						),
						array( 'type'     => 'separator' ),
						'section_title' => array(
							'name'     => __( 'My Account Tab Settings', 'mycredpartwoo' ),
							'type'     => 'title',
							'desc'     => '',
							'id'       => 'wdvc_tab_demo_section_title_section',
							'desc_tip' => true,
						),
						'mycred_wooplus_show_earned_badges' => array(	
							'name'     => __( 'My Badges', 'mycredpartwoo' ),
							'type'     => 'checkbox',
							'desc'     => __( 'Enable this option to reward users on achieving Badges on display my account tab section', 'mycredpartwoo' ),
							'id'       => 'mycred_wooplus_show_earned_badges',
							'desc_tip' => true,
						),
						'mycred_wooplus_show_earned_ranks' => array(	
							'name'     => __( 'My Ranks', 'mycredpartwoo' ),
							'type'     => 'checkbox',
							'desc'     => __( 'Enable this option to reward users on achieving ranks on display my account tab section.', 'mycredpartwoo' ),
							'id'       => 'mycred_wooplus_show_earned_ranks',
							'desc_tip' => true,
						),
						'mycred_wooplus_show_my_balance' => array(	
							'name'     => __( 'My Balance', 'mycredpartwoo' ),
							'type'     => 'checkbox',
							'desc'     => __( 'Enable this option to show user current balance on my account tab section.', 'mycredpartwoo' ),
							'id'       => 'mycred_wooplus_show_my_balance',
							'desc_tip' => true,
						),
						'mycred_wooplus_show_my_level' => array(	
							'name'     => __( 'My Level', 'mycredpartwoo' ),
							'type'     => 'checkbox',
							'desc'     => __( 'Enable this option to show user current level on my account tab section.', 'mycredpartwoo' ),
							'id'       => 'mycred_wooplus_show_my_level',
							'desc_tip' => true,
						),
						'section_end' => array(
							 'type'   => 'sectionend',
							 'id'     => 'wooplus_section_end',
						) ,
					);
			}
			return $settings;
			 
		}
		
}

$mycred_woo_my_account = new mycred_woo_my_account();

endif;
?>