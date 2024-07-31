<?php

add_action('woocommerce_order_status_processing', 'save_points_to_custom_table', 10, 1);

function save_points_to_custom_table($order_id){
    global $wpdb;
    $order = wc_get_order($order_id);
    $current_user_id = get_current_user_id();
    $orderTotal = $order->get_total();
    
    $pointsValue = get_option('loyality_points_option');

    $points = $orderTotal*$pointsValue['points_per'];

    $targeted_table = $wpdb->prefix."loyality_points";

        $data = array(
            'created' => date('Y-m-d H:i:s'),
            'user_id' => $current_user_id,
            'order_id' => $order_id,
            'points_earned' => $points
        );
    
    $wpdb->insert( $targeted_table, $data);

    }

    add_action('rest_api_init', function() {

        register_rest_route('loyalty/v1', 'add_points', [
        'methods' => 'POST',
        'callback' => 'add_loyalty_points',
        ]);
        
        });

        function add_loyalty_points($data){

            global $wpdb;
          
            $order_id = $data['order_id'];

            $targeted_table = $wpdb->prefix."loyality_points";

            $pointsData = $wpdb->get_results("SELECT * FROM $targeted_table WHERE order_id = $order_id AND (points_status IS NULL OR points_status != 'added')");
           
           //var_dump($pointsData);
            if(!empty($pointsData)){
            $userId = $pointsData[0]->user_id;
            $points_earned = $pointsData[0]->points_earned;

            // var_dump($points_earned);
            // var_dump($userId);

            $existingPoints = get_user_meta($userId, 'mycred_default', true);

            $newPoints = $existingPoints+$points_earned;

             //echo $newPoints;

            //exit;

            update_user_meta($userId, 'mycred_default', $newPoints);

            $newdata= array( 
                'points_status' => 'added',
            );
             //print_r($newdata);
            $data_where = array('order_id' => $order_id);
            $wpdb->update( $targeted_table, $newdata, $data_where );

            echo "Points Added Sucessfully.";
        } else{
            echo "Order Not Found or Already Added.";
        }



        }




