<?php
/**
 * Add simple product to quote request list button.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/single-product/add-to-list/simple.php.
 *
 * HOWEVER, on occasion Deep Web Solutions will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.deep-web-solutions.com/article-categories/quote-requests-for-woocommerce
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\single-product
 *
 * @var     WC_Product      $product
 * @var     string          $button_text
 */

defined( 'ABSPATH' ) || exit; ?>

<button type="submit" name="add-to-qrwc-list" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_quote_request_list_button button alt">
	<?php echo esc_html( $button_text ); ?>
</button>
<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>">
