//dynamic regular price function
add_filter( 'woocommerce_product_get_regular_price', 'custom_dynamic_regular_price', 10, 2 );
add_filter( 'woocommerce_product_variation_get_regular_price', 'custom_dynamic_regular_price', 10, 2 );
function custom_dynamic_regular_price( $regular_price, $product ) {
    if( empty($regular_price) || $regular_price == 0 )
        return $product->get_price();
    else
        return $regular_price;
}

// dynamic sale price function - you will make your discounts based on categories here
add_filter( 'woocommerce_product_get_sale_price', 'custom_dynamic_sale_price', 10, 2 );
add_filter( 'woocommerce_product_variation_get_sale_price', 'custom_dynamic_sale_price', 10, 2 );
function custom_dynamic_sale_price( $sale_price, $product ) {
    //get categories of woocommerce
    $categories = wp_get_post_terms($product->id, 'product_cat');    
    $categories = wp_list_pluck($categories, 'name');
    //set discounts rates for category 1, 2 and 3 respectively
    $rate1 = 0.85;
	  $rate2 = 0.9;
	  $rate3 = 0.95;
		if( (empty($sale_price) || $sale_price == 0) && (in_array("Example Category 1", $categories)) )
        	return $product->get_regular_price() * $rate1 ;
		elseif( (empty($sale_price) || $sale_price == 0) && (in_array("Example Category 2", $categories)) )
			return $product->get_regular_price() * $rate2 ;
		elseif( (empty($sale_price) || $sale_price == 0) && (in_array("Example Category 3", $categories)) )
			return $product->get_regular_price() * $rate3 ;
		else return;
};

//show regular prices and sale prices 
add_filter( 'woocommerce_get_price_html', 'custom_dynamic_sale_price_html', 20, 2 );
function custom_dynamic_sale_price_html( $price_html, $product ) {
    if( $product->is_type('variable') ) return $price_html;
	if($product->is_on_sale())
	{
		$price_html = wc_format_sale_price( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ), wc_get_price_to_display(  $product, array( 'price' => $product->get_sale_price() ) ) ) . $product->get_price_suffix();
	}

    return $price_html;
}
//a helper function to ensure that dynamic pricing is working compatible with your theme

add_action( 'woocommerce_before_calculate_totals', 'set_cart_item_sale_price', 20, 1 );
function set_cart_item_sale_price( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;
   
        foreach( $cart->get_cart() as $cart_item ) {
		if($cart_item['data']->get_sale_price() <= $cart_item['data']->get_regular_price() && $cart_item['data']->get_sale_price() > 0)
		{
			$price = $cart_item['data']->get_sale_price(); // get sale price
			$cart_item['data']->set_price( $price ); // Set the sale price
		}
		else
		{
			$price = $cart_item['data']->get_regular_price();
			$cart_item['data']->set_price( $price );
		}
    }
    /*the function named generateCoupon() is another code that programmatically generates a coupon based on cart products 
     *with this function you can generate a dynamic coupon
     *example: 
     * coupon name: testcoupon
     * amount: will be calculated for every cart click
     * assume you have 15% discount for products in category A, so sale prices are calculated for those products
     * and you have 10% discount for products in category B, so sale prices are also calculated for those products 
     * and you have some other categories or products with no discounts
     * but you also want that some special customers to use a coupon with 25% discount from the regular price of all products
    //generateCoupon();
}
