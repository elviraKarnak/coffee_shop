<?php 
 // mycred custom Hook

 add_filter( 'mycred_setup_hooks', 'coffee_shop_loyalty_program', 10, 2 );

 function coffee_shop_loyalty_program( $installed, $point_type ) {

        // Remove a specific hook
        //unset( $installed['site_visit'] );

        // Add a custom hook
        $installed['coffeeshop_loyalty'] = array(
            'title'        => 'Coffee Shop Loyalty',
            'description'  => 'Customized Loyalty Program.',
            'callback'     => array( 'coffeeshop_loyalty_Class' )
        );

        // // Replace an existing hook with our own
        // $installed['site_visit'] = array(
        // 	'title'        => 'Custom Site Visit',
        // 	'description'  => 'A custom version of the built-in site visit hook.',
        // 	'callback'     => array( 'my_Custom_Version_Hook_Class' )
        // );

        return $installed;

    }
    add_action( 'mycred_load_hooks', 'coffee_shop_loyalty_program_hook' );
    
    function coffee_shop_loyalty_program_hook() {
        class coffeeshop_loyalty_Class extends myCRED_Hook {

            /**
             * Construct
             * Used to set the hook id and default settings.
             */
            function __construct( $hook_prefs, $type ) {
        
                parent::__construct( array(
                    'id'       => 'coffeeshop_customer_loyalty',
                    'defaults' => array(
                        'creds'   => 1,
                        'log'     => 'points for something'
                    )
                ), $hook_prefs, $type );
        
            }
        
            /**
             * Run
             * Fires by myCRED when the hook is loaded.
             * Used to hook into any instance needed for this hook
             * to work.
             */
            public function run() {
        
            }
        
            /**
             * Hook Settings
             * Needs to be set if the hook has settings.
             */
            public function preferences() {
        
                // Our settings are available under $this->prefs
                $prefs = $this->prefs;
        
            }
        
            /**
             * Sanitize Preferences
             * If the hook has settings, this method must be used
             * to sanitize / parsing of settings.
             */
            public function sanitise_preferences( $data ) {
        
                return $data;
        
            }
        
        }
    }