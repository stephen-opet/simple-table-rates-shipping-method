#Simple Table Rates Shipping Method for WooCommerce

A shipping method for WooCommerce that allows me to define an arbitrarily complex shipping rate algorithm for specific classes of products. Contains two components:
  1) Splits the WooCommerce cart Package into individual items - allows independent shipping calculations
  2) Allows the developer to add custom rules for specific shipping classes in the public function calculate_shipping($package = array() ), found in the class-simple-table-rates-shipping-method.php
