<?php
/**
 * Simple custom product
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
global $product;
do_action( 'woocommerce_before_add_to_cart_form' );  ?>

<form id="buy_lesson_form" class="cart" style="text-align:center" method="post" enctype='multipart/form-data'>	
	<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>
	<button type="submit" id="buy_lesson_button" name="add-to-cart" style="float:none" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>
	<input type="hidden" value="<?php echo esc_attr( $product->get_id() ); ?>" class="product_id">
	<input type="hidden" value="<?php echo esc_attr( $product->get_stock_quantity() ); ?>" class="product_quantity">
	<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
</form>

<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>