jQuery(document).ready(function() {
    
    hide_setting();

    jQuery( document ).on( 'change','input:radio[name=mycred_partial_payment_switch] ', function(){
        
        hide_setting();
    
    });
});

function hide_setting(){

    if ( jQuery('input[name="mycred_partial_payment_switch"]:checked').val() == 'enable_coupons' ) {

        jQuery('.partial').closest('tr').hide();   
    
    }else{
        
        jQuery('.partial').closest('tr').show();
    
    }

    if (jQuery('input[name="mycred_partial_payment_switch"]:checked').val() == 'enable_partial_payment') {
        
        jQuery('.coupon').closest('tr').hide();
    
    }else{
        
        jQuery('.coupon').closest('tr').show();
    }
}