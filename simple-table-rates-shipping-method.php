<?php 
/**
  *	@package	SimpleTableRates
  */

/*
Plugin Name: WooCommerce Simple Table Rates
Plugin URI: http://www.creativeclickmedia.com
Description: WooCommerce - enable simple table rates for select products
Version: 1.0.0
Author: Stephen Opet III | Creative Click Media
Author URI: https://creativeclickmedia.com
License: GPLv2 or later
Text Domain: simple-table-rates-shipping-method
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

//Security Precaution
defined( 'ABSPATH' ) or die( 'You don\t have to go home, but you can\t stay here' ); //If user access page from outside WP, plugin commits  suicide

//MULTIPACK
add_action('plugins_loaded', 'woocommerce_multipack_init', 106);//links multipack init to plugins_loaded 
function woocommerce_multipack_init() {//function to be run after all plugins load page
	if ( class_exists( 'woocommerce' ) || class_exists( 'WooCommerce' ) ) {//if woocommerce is detected...
		if ( !class_exists( 'BE_Multiple_Packages' ) ) {//if our class does not already exist...
			require_once('class-settings.php');//add multipack class library
			class BE_Multiple_Packages {//define a new class
				public $settings_class;//declares variable
			
				//Woocommerce shipping method construct
				public function __construct(){ 
					$this->settings_class = new BE_Multiple_Packages_Settings();
					$this->settings_class->get_package_restrictions();
					$this->package_restrictions = $this->settings_class->package_restrictions;					
					add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'generate_packages' ) );
				}//END construct function


				/**
				 * Get Settings for Restrictions Table
				 *
				 * @access public
				 * @return void
				 */
				function generate_packages( $packages ) {
					if( get_option( 'multi_packages_enabled' ) ) {
						// Reset the packages
	    				$packages = array();
	    				//$settings_class = new BE_Multiple_Packages_Settings();
	    				$package_restrictions = $this->settings_class->package_restrictions;
	    				$free_classes = get_option( 'multi_packages_free_shipping' );

	    				// Determine Type of Grouping
	    				if( get_option( 'multi_packages_type' ) == 'per-product' ) :
						    // separate each item into a package
						    $n = 0;
						    foreach ( WC()->cart->get_cart() as $item ) {
						        if ( $item['data']->needs_shipping() ) {
						            // Put inside packages
						            $packages[ $n ] = array(
						                'contents' => array($item),
						                'contents_cost' => array_sum( wp_list_pluck( array($item), 'line_total' ) ),
						                'applied_coupons' => WC()->cart->applied_coupons,
						                'destination' => array(
						                    'country' => WC()->customer->get_shipping_country(),
						                    'state' => WC()->customer->get_shipping_state(),
						                    'postcode' => WC()->customer->get_shipping_postcode(),
						                    'city' => WC()->customer->get_shipping_city(),
						                    'address' => WC()->customer->get_shipping_address(),
						                    'address_2' => WC()->customer->get_shipping_address_2()
						                )
						            );
							    	
							    	// Determine if 'ship_via' applies
							    	$key = $item['data']->get_shipping_class_id();
							    	if( $free_classes && in_array( $key, $free_classes ) ) {
							    		$packages[ $n ]['ship_via'] = array('free_shipping');
							    	} elseif( count( $package_restrictions ) && isset( $package_restrictions[ $key ] ) ) {
							        	$packages[ $n ]['ship_via'] = $package_restrictions[ $key ];
							    	}
							    	$n++;
						        }
						    }
						    
	    				else :
	    					// Create arrays for each shipping class
							$shipping_classes = array( '' => 'other' );
							$other = array();
							$get_classes = WC()->shipping->get_shipping_classes();
							foreach ( $get_classes as $key => $class ) {
								$shipping_classes[ $class->term_id ] = $class->slug;
								$array_name = $class->slug;
								$$array_name = array();
							}

							// Sort bulky from regular
							foreach ( WC()->cart->get_cart() as $item ) {
	        					if ( $item['data']->needs_shipping() ) {
	        						$item_class = $item['data']->get_shipping_class();
	        						if( isset( $item_class ) && $item_class != '' ) {
			        					foreach ($shipping_classes as $class_id => $class_slug) {
			            					if ( $item_class == $class_slug ) {
			                					array_push( $$class_slug, $item );
			            					}
		        						}
		        					} else {
	                					$other[] = $item;
					            	}
						        }
						    }

						    // Put inside packages
						    $n = 0;
						    foreach ($shipping_classes as $key => $value) {
							    if ( count( $$value ) ) {
							        $packages[ $n ] = array(
							            'contents' => $$value,
							            'contents_cost' => array_sum( wp_list_pluck( $$value, 'line_total' ) ),
							            'applied_coupons' => WC()->cart->applied_coupons,
							            'destination' => array(
							                'country' => WC()->customer->get_shipping_country(),
							                'state' => WC()->customer->get_shipping_state(),
							                'postcode' => WC()->customer->get_shipping_postcode(),
							                'city' => WC()->customer->get_shipping_city(),
							                'address' => WC()->customer->get_shipping_address(),
							                'address_2' => WC()->customer->get_shipping_address_2()
							            )
							        );
							    	
							    	// Determine if 'ship_via' applies
							    	if( $free_classes && in_array( $key, $free_classes ) ) {
							    		$packages[ $n ]['ship_via'] = array('free_shipping');
							    	} elseif( count( $package_restrictions ) && isset( $package_restrictions[ $key ] ) ) {
							        	$packages[ $n ]['ship_via'] = $package_restrictions[ $key ];
							    	}
							    	$n++;
							    }
							}
    
	    				endif;

    					return $packages;
	    			}
				}

			} // end class BE_Multiple_Packages

			return new BE_Multiple_Packages();

        } // end IF class 'BE_Multiple_Packages' exists

    } // end IF woocommerce exists

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'be_multiple_packages_plugin_action_links' );
	function be_multiple_packages_plugin_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . get_bloginfo( 'wpurl' ) . '/wp-admin/admin.php?page=wc-settings&tab=multiple_packages">Settings</a>',
				'support' => '<a href="http://bolderelements.net/" target="_blank">Bolder Elements</a>'
			),
			$links
		);
	}
}


/**
 * Check if WooCommerce is active
 */
$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
if ( in_array( 'woocommerce/woocommerce.php', $active_plugins) ) {
	//add method to available Shipping Methods
	function add_table_shipping_method( $methods ) {
		$methods['table_shipping_method'] = 'WC_Simple_Table_Rates_Shipping_Method';
   		return $methods;
 	}
 	add_filter( 'woocommerce_shipping_methods', 'add_table_shipping_method' );

	//link init function to woocommerce initialization
	function table_shipping_method_init(){
   		require_once 'class-simple-table-rates-shipping-method.php';
 	}
 	add_action( 'woocommerce_shipping_init', 'table_shipping_method_init' );
}