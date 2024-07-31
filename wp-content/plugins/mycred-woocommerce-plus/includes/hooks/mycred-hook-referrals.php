<?php

if ( !class_exists('myCredWCPReferralHook') ):
class myCredWCPReferralHook
{
    private static $_instance;

    /**
     * Loads the hook
     * myCredWCPReferralHook constructor.
     * @since 1.7.3
     * @version 1.0
     */
    public function __construct()
    {
        add_action( 'mycred_after_referring_signups', array( $this, 'hook' ), 10, 2 );

        add_filter( 'mycred_hook_referrals', array( $this, 'hook_defaults' ) );

        add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

        add_action( 'mycred_after_signup_referred', array( $this, 'signup_referred' ) );

        add_action( 'woocommerce_order_status_completed', array( $this, 'award_referred' ) );
    }

    /**
     * Loads the class
     * @return myCredWCPReferralHook
     * @since 1.7.3
     * @version 1.0
     */
    public static function get_instance()
    {
        if ( self::$_instance == null )
            self::$_instance = new self();

        return self::$_instance;
    }

    /**
     * Overwrites the default settings of Referring Signups
     * @param $defaults
     * @return mixed
     * @since 1.7.3
     * @version 1.0
     */
    public function hook_defaults($defaults )
    {
        unset( $defaults['defaults']['signup'] );

        $defaults['defaults']['signup'] = array(
            'creds'             => 10,
            'log'               => '%plural% for referring a new member for order',
            'limit'             => 1,
            'limit_by'          => 'total',
            'condition'         => 'all_products',
            'products_setting'  => ''
        );

        return $defaults;
    }

    /**
     * @param $instance
     * @param $prefs
     * @since 1.7.3
     * @version 1.0
     */
    public function hook( $instance, $prefs )
    {
        $order_conditions = array(
            'all_products'      =>  'All Products',
            'products_except'   =>  'Products Except',
            'selected_products' =>  'Selected Products',
           //'order_amount'      =>  'Order Amount'
        );

        ?>
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group">
                <label for="<?php echo $instance->field_id( array( 'signup' => 'condition' ) ); ?>"><?php _e( 'Condition', 'mycred' ); ?></label>
                <select name="<?php echo $instance->field_name( array( 'signup' => 'condition' ) ); ?>" onchange="orderSignupCondition(this)" id="<?php echo $instance->field_id( array( 'signup' => 'condition' ) ); ?>" class="form-control">
                    <?php
                    foreach ( $order_conditions as $key => $value )
                    {
                        $selected = $key == $prefs['signup']['condition'] ? 'selected' : '';

                        echo "<option value='$key' $selected>$value</option>";
                    }
                    ?>
                </select>
            </div>
            <span class="description"></span>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group">
                <?php
                    $display = $prefs['signup']['condition'] == 'all_products' ? 'none' : 'block';
                ?>
                <label style="display: <?php echo $display ?>" for="<?php echo $instance->field_id( array( 'signup' => 'products_setting' ) ); ?>" id="<?php echo $instance->field_id( array( 'signup' => 'products_setting' ) ) . '-label'; ?>">Product ID's</label>
                <input type="text" style="display: <?php echo $display ?>" name="<?php echo $instance->field_name( array( 'signup' => 'products_setting' ) ); ?>" id="<?php echo $instance->field_id( array( 'signup' => 'products_setting' ) ); ?>" value="<?php echo esc_attr( $prefs['signup']['products_setting'] ); ?>" class="form-control" />
                <span style="display: <?php echo $display ?>" id="<?php echo $instance->field_id( array( 'signup' => 'products_setting' ) ) . '-desc'; ?>" class="description">Use comma separated Product ID's 1,2,3</span>
            </div>
        </div>
        <?php
    }

    /**
     * Enqueue Scripts
     * @since 1.7.3
     * @version 1.0
     */
    public function admin_enqueue_scripts()
    {
        wp_enqueue_script( 'mycred-hook-referral-js', plugins_url( 'assets/js/mycred-hook-referrals.js', MYCRED_WOOPLUS_THIS ), '', MYCRED_WOOPLUS_VERSION );
    }

    /**
     *
     * @param $user_log
     * @since 1.7.3
     * @version 1.0
     */
    public function signup_referred( $user_log )
    {
        extract( $user_log );

        update_user_meta( $referred , 'mycred_signup_order_referral', $user_log );
    }

    /**
     * Awards user on order completion
     * @param $order_id
     * @since 1.7.3
     * @version 1.0
     */
    public function award_referred( $order_id )
    {
        $order = wc_get_order( $order_id );

        $ref_user_data = $order->get_user();

        $ref_id = $ref_user_data->ID;

        $ref_log_data = get_user_meta( $ref_id, 'mycred_signup_order_referral', true );

        if ( empty( $ref_log_data ) ) return;

        extract( $ref_log_data );

        $hooks = mycred_get_option( 'mycred_pref_hooks', false );

        $prefs = $hooks['hook_prefs']['affiliate']['signup'];

        //Award if All Products
        if ( $prefs['condition'] == 'all_products' )
        {
            mycred_add(
                $reference,
                $referrer,
                $creds,
                $log,
                $referred,
                $IP,
                $point_type
            );

            delete_user_meta(  $ref_id, 'mycred_signup_order_referral' );
        }


        if ( $prefs['condition'] == 'products_except' || $prefs['condition'] == 'selected_products' )
        {
            $product_settings = array();

            //Product ID's set by admin not to reward
            $product_settings = explode( ',', $prefs['products_setting'] );

            //If not set
            if( empty( $product_settings ) ) return;

            $items = $order->get_items();

            //Checking product
            foreach ( $items as $item ) {

                $product_id = $item->get_product_id();

                //If product not except award
                if ( $prefs['condition'] == 'products_except' )
                {
                    if ( !in_array( $product_id , $product_settings ) )
                    {
                        mycred_add(
                            $reference,
                            $referrer,
                            $creds,
                            $log,
                            $new_user_id,
                            $IP,
                            $point_type
                        );

                        delete_user_meta(  $ref_id, 'mycred_signup_order_referral' );

                        return;
                    }
                }

                //If it's selected product
                if ( $prefs['condition'] == 'selected_products' )
                {
                    if ( in_array( $product_id , $product_settings ) )
                    {
                        mycred_add(
                            $reference,
                            $referrer,
                            $creds,
                            $log,
                            $new_user_id,
                            $IP,
                            $point_type
                        );

                        delete_user_meta(  $ref_id, 'mycred_signup_order_referral' );

                        return;
                    }
                }
            }
        }
    }
}
endif;

myCredWCPReferralHook::get_instance();