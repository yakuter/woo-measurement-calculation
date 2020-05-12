<?php
/**
 * Functions.php
 *
 * @package  Theme_Customisations
 * @author   WooThemes
 * @since    1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// ADD TO CART CUSTOM FIELDS
add_action( 'woocommerce_before_add_to_cart_button', 'output_add_to_cart_custom_fields', 10 );
function output_add_to_cart_custom_fields() {
	global $product;

	$custom_fields = array(
		array(
			'label' => 'Area SQM',
			'key' => 'sqm_area',
			'type' => 'text',
			'classlist' => 'small-6',
			'min' => 1,
			'required' => true,
			'step' => ".01",
		),
	);
?>

	<table id="price_calculator" class="wc-measurement-price-calculator-price-table simple_price_calculator quantity-based-mode">
  		<tbody>

	<?php
		foreach ($custom_fields as $cf):
			$cf = (object)$cf;
			?>

			<tr class="price-table-row length-input">
				<td><label for="length_needed"><?php _e( $cf->label, 'atn' ); ?></label></td>
				<td style="text-align:right;"><input type="<?php echo $cf->type ?>" id="<?php echo $cf->key ?>" name="<?php echo $cf->key ?>" <?php if($cf->required) echo 'required="' . $cf->required . '"'; ?> <?php if($cf->min) echo 'required="' . $cf->min . '"'; ?> step="<?php echo $cf->step ?>"></td>
			</tr>

		<?php
		endforeach; ?>

		<?php 
			$price = wc_get_price_to_display( $product );
			$length = $product->get_length()/1000;
			$width = $product->get_width()/1000;
			$currency = get_woocommerce_currency();
			$base_area = $length * $width;
		?>
			<tr class="price-table-row length-input">
				<td><label for="sqm_wastage">Add 10% for cuts and wastage</label></td>
				<td style="text-align:right;"><input type="checkbox" id="sqm_wastage" name="sqm_wastage"></td>
			</tr>
			
		<tr class="price-table-row calculated-price">
			<td>Total Price</td>

			<td>
				<span class="total_price">
				<span class="amount">&pound;<span class="t_price"><?php echo $price; ?></span></span>
				</span>
				<input type="hidden" name="total_price_data" value="<?php echo $price; ?>">
			</td>
		</tr>
    </tbody>
  </table>

    <script>
    jQuery(function($){

		$("form.cart").keypress(function(e) {
		//Enter key
		if (e.which == 13) {
			return false;
		}
		});

		var b  = 'span.total_price span.amount span.t_price',
			area = 'input[name="sqm_area"]',
			quantity = 'input[name="quantity"]',
			click = 'input#sqm_wastage',
			base_area = <?php echo $base_area; ?>,
			p1 = <?php echo $price; ?>;

        $(area).on( 'change', function(){
			var hesap = $(area).val() / base_area;
			if ($(click).is(':checked')) {
				var hesap = hesap * 1.1;
			}
			$(quantity).val( parseFloat(Math.floor(hesap)));			
			$(b).html( parseFloat( $(quantity).val() * p1 ).toFixed(2) );
		});

		$(quantity).on( 'change', function(){
			$(area).val(
				parseFloat( $(quantity).val() * base_area )
				.toFixed(2)
			);
		});
		
		$(click).on( 'change', function(){
			var hesap = $(area).val() / base_area;
			if ($(click).is(':checked')) {
				var hesap = hesap * 1.1;
			}
			$(quantity).val( parseFloat(Math.floor(hesap)));			
			$(b).html( parseFloat( $(quantity).val() * p1 ).toFixed(2) );
        });

    });
    </script>
    <?php
}

// ADD sqm_area and sqm_quantity TO CART ITEM
add_filter( 'woocommerce_add_cart_item_data', 'iconic_add_custom_fields_text_to_cart_item', 10, 3 );
function iconic_add_custom_fields_text_to_cart_item( $cart_item_data, $product_id, $variation_id ) {
	$custom_fields = array(
		array(
			'label' => 'Area',
			'key' => 'sqm_area',
			'type' => 'number',
			'classlist' => 'small-6',
			'min' => 1,
			'required' => true,
		),
		array(
			'label' => 'Quantity',
			'key' => 'sqm_quantity',
			'type' => 'number',
			'classlist' => 'small-6',
			'min' => 0,
			'required' => true,
		),
	);

	foreach ($custom_fields as $cf):
		$cf = (object)$cf;

		$custom_input = filter_input( INPUT_POST, $cf->key );

		if ( !empty( $custom_input ) ) {
			$cart_item_data[ $cf->key ] = $custom_input;
		}

	endforeach;

	return $cart_item_data;
}


// ADD total_price_data TO CART ITEM
add_filter( 'woocommerce_add_cart_item_data', 'save_custom_product_field_in_cart', 10, 2 );
function save_custom_product_field_in_cart( $cart_item_data, $product_id ) {
    if( isset( $_POST['total_price_data'] ) )
        $cart_item_data[ 'total_price_data' ] = sanitize_text_field($_POST['total_price_data']);

    return $cart_item_data;
}


// SET PRICE WITH TOTAL PRICE DATA
add_action( 'woocommerce_before_calculate_totals', 'add_custom_price' );
function add_custom_price( $cart_object ) {
    foreach ( $cart_object->cart_contents as $key => $value ) {
		$value['data']->set_price($value['total_price_data']);
	}
}

// GET CUSTOM ITEMS FOR CART 
add_filter( 'woocommerce_get_item_data', 'iconic_display_custom_fields_text_cart', 10, 2 );
function iconic_display_custom_fields_text_cart( $item_data, $cart_item ) {
	$custom_fields = array(
		array(
			'label' => 'Area',
			'key' => 'sqm_area',
			'type' => 'number',
			'classlist' => 'small-6',
			'min' => 1,
			'required' => true,
		),
		array(
			'label' => 'Quantity',
			'key' => 'sqm_quantity',
			'type' => 'number',
			'classlist' => 'small-6',
			'min' => 0,
			'required' => true,
		),
	);

	foreach ($custom_fields as $cf):
		$cf = (object)$cf;

		if ( !empty( $cart_item[ $cf->key ] ) ) {
			$item_data[] = array(
				'key'     => __( $cf->label, 'atn' ),
				'value'   => wc_clean( $cart_item[ $cf->key ] ),
				'display' => '',
			);
		}


	endforeach;

	return $item_data;
}

// GET CUSTOM ITEMS FOR CHECKOUT
add_action( 'woocommerce_checkout_create_order_line_item', 'iconic_add_custom_fields_text_to_order_items', 10, 4 );
function iconic_add_custom_fields_text_to_order_items( $item, $cart_item_key, $values, $order ) {

	global $custom_fields;

	foreach ($custom_fields as $cf):
		$cf = (object)$cf;

		if ( !empty( $values[ $cf->key ] ) ) {
			$item->add_meta_data( __( $cf->label, 'atn' ), $values[ $cf->key ] );
		}

	endforeach;
}


// SHIPPING ******************** */

add_filter( 'woocommerce_package_rates', 'wc_ninja_find_all_available_shipping_rates', 998, 2 );
function wc_ninja_find_all_available_shipping_rates( $rates, $package ) {
	
	foreach ( $rates as $id => $rate ) {
		
		// echo $id . ' ';
	}

    return $rates;
}

add_filter( 'woocommerce_package_rates', 'wc_ninja_change_flat_rates_cost', 10, 2 );
function wc_ninja_change_flat_rates_cost( $rates, $package ) {


	// Make sure flat rate is available
	if ( isset( $rates['flat_rate:1'] ) ) {
		// Number of products in the cart
		$cart_number = WC()->cart->cart_contents_count;
		
		// Check if there are more than 10 products in the cart
		// if ( $cart_number > 10 ) {
			
			$rates['flat_rate:1']->cost = $rates['flat_rate:1']->cost + 22;
		// }
	}

	return $rates;
}





add_filter('woocommerce_checkout_update_order_review', 'clear_wc_shipping_rates_cache');
function clear_wc_shipping_rates_cache(){
    $packages = WC()->cart->get_shipping_packages();

    foreach ($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";

        unset(WC()->session->$shipping_session);
    }
}





// add_filter( 'woocommerce_package_rates', 'bbloomer_woocommerce_tiered_shipping', 999, 2 );
// function bbloomer_woocommerce_tiered_shipping( $rates, $package ) { 
// 	echo "erhan";
	
// 	foreach ( WC()->cart->get_cart() as $cart_item ) {
// 		$sqm_area = $cart_item['sqm_area'];
// 	}

// 	echo $sqm_area;

//     if ( isset( $rates['flat_rate:1'] ) ) {
// 		$rates['flat_rate:1']->cost = 10 * ceil ( $sqm_area/23.04 ); 
//    	}
	
// 	return $rates;
// }


/**
 * Customize product data tabs
 */
// add_filter( 'woocommerce_product_tabs', 'woo_custom_additional_information_tab', 98 );
// function woo_custom_additional_information_tab( $tabs ) {

// 	$tabs['additional_information']['callback'] = 'woo_custom_additional_information_tab_content';	// Custom description callback

// 	return $tabs;
// }

// function woo_custom_additional_information_tab_content() {
// 	echo '<h2>Custom Description</h2>';
// 	echo '<p>Here\'s a custom description</p>';
// }