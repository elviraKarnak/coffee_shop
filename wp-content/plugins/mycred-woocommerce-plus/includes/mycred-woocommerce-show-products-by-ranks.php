<?php

if( !class_exists( 'myCred_WOO_DISPLAY_PRODUCTS_BY_RANK' ) ) {

    class myCred_WOO_DISPLAY_PRODUCTS_BY_RANK  {
        
        /**
         * mwdpbr
         */

        public function __construct() {
            add_action( 'admin_init', array( $this , 'mwdpbr_add_metabox'));   
            add_action( 'save_post', array( $this, 'mwdpbr_save_woo_cat_post' ), 10, 2 );
            add_action( 'admin_enqueue_scripts', array( $this, 'mwpbr_load_select2_styles' ) );
            add_action( 'woocommerce_product_query', array( $this, 'mwpbr_removing_products_from_shop_page' ), 10, 2 );
            add_action( 'wp', array($this, 'mwpbr_retrict_single_product_page'), 6 );
            //add_action( 'wp', array($this, 'mwpbr_retrict_product_category_page'), 7 );
        }

        public function mwpbr_load_select2_styles() {
            if ( get_post_type() == 'mycred_rank' ) {
                wp_enqueue_style( 'mycred_badge_select2_css', plugin_dir_url( __DIR__ ) . 'assets/css/select2.min.css', '', MYCRED_WOOPLUS_VERSION );
                wp_enqueue_script( 'mycred_badge_select2_js', plugin_dir_url( __DIR__ ) . 'assets/js/select2.min.js', array( 'jquery' ), MYCRED_WOOPLUS_VERSION );
            }
        }

        public function mwdpbr_add_metabox() {
            if ( 'yes' == get_option( 'mycred_wooplus_show_ranks' ) ) {
                add_meta_box(	
                    'mwdpbr_prod_by_ranks',
                    'Display products from categories by Ranks', 
                    array( $this, 'mwdpbr_prod_by_ranks_callback' ),
                    'mycred_rank',
                    'normal', 
                    'low'
                );
            }
        }

        public function mwdpbr_save_woo_cat_post( $post_ID, $post ) {
            if ( $post->post_type == 'mycred_rank' ) {
                if( isset( $_POST['woo_ranks_cats'] ) ) {
                    update_post_meta( $post_ID,'mycred_woo_ranks_cats', $_POST['woo_ranks_cats'] );
                } else {
                    update_post_meta( $post_ID,'mycred_woo_ranks_cats', null );
                }
            }
        }

        public function mwpbr_removing_products_from_shop_page( $meta_query, $query  ) {
            if( is_user_logged_in()) {
                $current_user_roles_array = wp_get_current_user()->roles;
                if(!in_array('administrator', $current_user_roles_array)) {
                    $categories = $this->mwpbr_restricted_categories_from_users_rank();

                    $tax_query = array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => $categories,
                            'operator' => 'NOT IN'
                        ),
                    );
                    $meta_query->set( 'tax_query', $tax_query );    
                }
            }
        }

        public function mwpbr_retrict_single_product_page() {
            global $wp_query;
            $categories = $this->mwpbr_restricted_categories_from_users_rank();
            if ( is_product() ){
                $product_id = $wp_query->post->ID;
                $term_object = wp_get_post_terms( $product_id, 'product_cat', array( 'field'=>'slug' ) );
                foreach( $term_object as $term ) {
                    $category = $term->slug;
                    if( is_array( $categories ) && in_array( $category, $categories ) ) {
                        wp_redirect( site_url() );
                    }
                }
            }
        }

        public function mwpbr_retrict_product_category_page() {
            $categories = $this->mwpbr_restricted_categories_from_users_rank();
            if( is_product_category( $categories ) ) {
                wp_redirect( site_url() );
            }
        }

        public function mwdpbr_prod_by_ranks_callback( $post ) { 
            $selected_cats = get_post_meta( $post->ID, 'mycred_woo_ranks_cats', true ); 
            ?>
            <table width="100%">
                <tr>
                    <td colspan="10" >
                        <p style="font-weight: 600;">
                            
                        <?php echo __( 'You can select the categories to hide the related products from users.', 'mycredpartwoo' ); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="width: 25%"><?php echo __( 'WooCommerce Product Categories', 'mycredpartwoo' ); ?></td>
                    <td>
                        <?php echo '<pre>'; print_r(get_post_meta($post)); echo '</pre>'; ?>
                        <select class="mwdpbr_categories_ranks" name="woo_ranks_cats[]" style="width: 69%" multiple="multiple">
                        <?php foreach( $this->mwdpbr_get_woocommerce_cats() as $key => $category ) : ?>
                            <option value="<?php echo esc_attr($category->slug); ?>" <?php echo in_array( $category->slug, $selected_cats ) ? 'selected' : ''; ?>><?php echo esc_attr($category->name); ?></option>
                        <?php echo esc_attr($category->name); ?>
                        <?php endforeach; ?>
                        </select>
                    </td>
                </tr>
            </table>
            <script>
                jQuery(document).ready(function($) {
                    $('.mwdpbr_categories_ranks').select2({
                        placeholder: 'Select categories'
                    });
                });

            </script>
            <?php 
        }

        private function mwdpbr_get_woocommerce_cats() {
            $orderby = 'name';
            $order = 'asc';
            $hide_empty = false ;
            $cat_args = array(
                'orderby'    => $orderby,
                'order'      => $order,
                'hide_empty' => $hide_empty,
            );
            
            $product_categories = get_terms( 'product_cat', $cat_args );
            return $product_categories;
        }

        private function mwpbr_restricted_categories_from_users_rank() {
            $user_id = get_current_user_id();
            $user_rank = get_user_meta( $user_id, 'mycred_rank', true );
            $user_rank_id = $user_rank;
            $categories = get_post_meta( $user_rank_id, 'mycred_woo_ranks_cats', true );

            return $categories;
        }
    }
    
}
$myCred_WOO_DISPLAY_PRODUCTS_BY_RANK = new myCred_WOO_DISPLAY_PRODUCTS_BY_RANK();