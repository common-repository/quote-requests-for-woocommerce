<?php
/**
 * Shows quotes on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/myaccount/quotes.php.
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
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\myaccount
 *
 * @var     int             $current_page
 * @var     DWS_Quote[]     $customer_quotes
 * @var     bool            $has_quotes
 */

defined( 'ABSPATH' ) || exit;

do_action( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'before_list_table' ) ), $has_quotes ); ?>

<?php if ( $has_quotes ) : ?>

	<table class="woocommerce-orders-table qrwc-quotes-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( dws_qrwc_get_quotes_account_columns() as $column => $label ) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column ); ?>">
						<span class="nobr">
							<?php echo esc_html( $label ); ?>
						</span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ( $customer_quotes->orders as $customer_quote ) :
			$quote = dws_qrwc_get_quote( $customer_quote );
			?>

			<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $quote->get_status() ); ?> order">
				<?php foreach ( dws_qrwc_get_quotes_account_columns() as $column => $label ) : ?>
					<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column ); ?>" data-title="<?php echo esc_attr( $label ); ?>">
						<?php
						switch ( $column ) {
							case 'order-number':
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', 'quote_number' ) ),
									sprintf(
										'<a href="%s">%s</a>',
										esc_url( $quote->get_view_quote_url() ),
										esc_html( _x( '#', 'hash before quote number', 'quote-requests-for-woocommerce' ) . $quote->get_quote_number() )
									),
									$quote
								);
								break;
							case 'order-date':
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', 'quote_date' ) ),
									sprintf(
										'<time datetime="%s">%s</time>',
										esc_attr( $quote->get_date_created()->date( 'c' ) ),
										esc_html( wc_format_datetime( $quote->get_date_created() ) )
									),
									$quote
								);
								break;
							case 'order-status':
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', 'quote_status' ) ),
									esc_html( dws_qrwc_get_quote_status_name( $quote->get_status() ) ),
									$quote
								);
								break;
							case 'order-total':
								$item_count = $quote->get_item_count() - $quote->get_item_count_refunded();
								echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', 'quote_total' ) ),
									wp_kses_post(
										sprintf(
											/* translators: 1: formatted order total 2: total order items */
											_n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'quote-requests-for-woocommerce' ),
											$quote->get_formatted_order_total(),
											$item_count
										)
									),
									$quote,
									$item_count
								);
								break;
							case 'order-actions':
								$actions = dws_qrwc_get_quote_account_actions( $quote );
								foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
									echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button button ' . sanitize_html_class( $key ) . '">' . esc_html( $action['name'] ) . '</a>';
								}
								break;
							default:
								do_action( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'columns', $column ) ), $quote );
						}
						?>
					</td>
				<?php endforeach; ?>
			</tr>

			<?php endforeach; ?>
		</tbody>
	</table>

	<?php do_action( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'before_pagination' ) ) ); ?>

	<?php if ( 1 < $customer_quotes->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_endpoint_url( 'quotes', $current_page - 1 ) ); ?>">
					<?php esc_html_e( 'Previous', 'quote-requests-for-woocommerce' ); ?>
				</a>
			<?php endif; ?>

			<?php if ( intval( $customer_quotes->max_num_pages ) !== $current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_endpoint_url( 'quotes', $current_page + 1 ) ); ?>">
					<?php esc_html_e( 'Next', 'quote-requests-for-woocommerce' ); ?>
				</a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
		<?php if ( true === dws_qrwc_are_requests_enabled() ) : ?>
			<a class="woocommerce-Button button" href="<?php echo esc_url( dws_qrwc_wc_return_to_shop_redirect() ); ?>">
				<?php esc_html_e( 'Browse products', 'quote-requests-for-woocommerce' ); ?>
			</a>
			<?php esc_html_e( 'You have not submitted any quote requests yet.', 'quote-requests-for-woocommerce' ); ?>
		<?php else : ?>
			<?php esc_html_e( 'No quotes have been created for you yet.', 'quote-requests-for-woocommerce' ); ?>
		<?php endif; ?>
	</div>

<?php endif; ?>

<?php do_action( dws_qrwc_get_hook_tag( 'account', array( 'quotes_list', 'after_list_table' ) ), $has_quotes ); ?>
