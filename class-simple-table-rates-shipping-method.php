<?php

class WC_Simple_Table_Rates_Shipping_Method extends WC_Shipping_Method{

  	public function __construct( $instance_id = 0 ){
		$this->id = 'table_shipping_method';
	  	$this->method_title = __( 'Simple Table Rates Shipping Method', 'woocommerce' );
	  	$this->method_descriptions = __( 'Stephen will describe the plugin' );
	  	// Load the settings.
	  	$this->init_form_fields();
	  	$this->init_settings();

	  	// Define user set variables in init_form_fields
	  	$this->enabled	= $this->get_option( 'enabled' );
	  	$this->title 		= $this->get_option( 'title' );
//	  	$this->init();

	  	//Shipping Zone Support
	  	$this->instance_id = absint( $instance_id );
	  	$this->supports  = array(
       		'shipping-zones',
        	'instance-settings',
        	'instance-settings-modal',
     	);
	  	add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
	  	add_filter( 'woocommerce_cart_shipping_packages', array( &$this, 'th_woocommerce_cart_shipping_packages') );
  	}

	//WooCommerce function to define admin-input settings
  	public function init_form_fields(){
  		$this->instance_form_fields = array(
		    'enabled' => array(
		      'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
		      'type' 			=> 'checkbox',
		      'label' 		=> __( 'Enable Simple Table Rates Shipping', 'woocommerce' ),
		      'default' 		=> 'yes'
		    ),
		    'title' => array(
		      'title' 		=> __( 'Method Title', 'woocommerce' ),
		      'type' 			=> 'text',
		      'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
		      'default'		=> __( 'Table Rate Shipping', 'woocommerce' ),
		      
		    )
		);
  	}

  	public function is_available( $package ){
  		foreach ( WC()->cart->get_cart() as $item ) {
	      if($item['data']->get_shipping_class() == 'books' ){
	      	return true;
	      }
	  	}
	  	return false;
  	}

  	public function calculate_shipping($package = array() ){
  		
  		//count number of logbooks in cart
  		$logCount = 0;
	  	//Record present shipping rate
	  	//$previousShipping = $order->get_shipping_total();

  		//increment 
  		foreach ( WC()->cart->get_cart() as $item ) {
	      if($item['data']->get_shipping_class() == 'books' ){
	      	if($item['quantity'] > 0 ){
	      		$logCount = $logCount + $item['quantity'];
	      	}
	      }
	    
	    	if($logCount == 0){
	    		$cost=0;
	    	}
	    	if($logCount == 1){
	    		$cost=7;
	    	}
	    	if($logCount>=2){
	    		$cost=14;
	    	}
	    	//require("browserDebug.php");
	  		//browserDebug($logCount);

	  	}
	  	
	    // send the final rate to the user. 
	    $this->add_rate( array(
	      'id' 	=> $this->id,
	      'label' => 'Simple Table Shipping Rate',
	      'cost' 	=> $cost
	    ));
  	}//END function, calculate shipping

}//end class