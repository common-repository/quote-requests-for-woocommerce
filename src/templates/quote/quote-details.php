<?php
/**
 * Quote details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/quotes/quote-details.php.
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
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\quotes
 *
 * @var     DWS_Quote   $quote
 */

defined( 'ABSPATH' ) || exit;

$quote_items           = $quote->get_items( dws_qrwc_wc_purchase_order_item_types() );
$show_customer_details = is_user_logged_in() && $quote->get_user_id() === get_current_user_id();

do_action( dws_qrwc_get_hook_tag( 'before_quote_details' ), $quote ); ?>

<section class="woocommerce-order-details qrwc-quote-details">
	<?php do_action( dws_qrwc_get_hook_tag( 'quote_details', 'before_quote_table' ), $quote ); ?>

	<h2 class="woocommerce-order-details__title">
		<?php esc_html_e( 'Quote details', 'quote-requests-for-woocommerce' ); ?>
	</h2>

	<table class="woocommerce-table qrwc-table woocommerce-table--order-details qrwc-table--quote-details shop_table order_details">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name">
					<?php esc_html_e( 'Product', 'quote-requests-for-woocommerce' ); ?>
				</th>
				<th class="woocommerce-table__product-table product-total">
					<?php esc_html_e( 'Total', 'quote-requests-for-woocommerce' ); ?>
				</th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( dws_qrwc_get_hook_tag( 'quote_details', 'before_quote_table_items' ), $quote );

			foreach ( $quote_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $quote,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => false,
						'purchase_note'      => '',
						'product'            => $product,
					)
				);
			}

			do_action( dws_qrwc_get_hook_tag( 'quote_details', 'after_quote_table_items' ), $quote );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $quote->get_order_item_totals() as $key => $total ) {
				?>
					<tr>
						<th scope="row">
							<?php echo esc_html( $total['label'] ); ?>
						</th>
						<td>
							<?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</td>
					</tr>
					<?php
			}
			?>
			<?php if ( $quote->get_customer_note() ) : ?>
				<tr>
					<th>
						<?php esc_html_e( 'Note:', 'quote-requests-for-woocommerce' ); ?>
					</th>
					<td>
						<?php echo wp_kses_post( nl2br( wptexturize( $quote->get_customer_note() ) ) ); ?>
					</td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( dws_qrwc_get_hook_tag( 'quote_details', 'after_quote_table' ), $quote ); ?>
</section>

<?php
do_action( dws_qrwc_get_hook_tag( 'after_quote_details' ), $quote );

if ( $show_customer_details ) {
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $quote ) );
}
