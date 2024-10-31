<?php
/**
 * Outputs the content of the quote data meta box.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\admin\meta-boxes
 *
 * @var     WP_Post_Type    $order_type_object
 * @var     DWS_Quote       $quote
 */

defined( 'ABSPATH' ) || exit; ?>

<style type="text/css">
	#post-body-content, #titlediv { display:none }
</style>

<div class="panel-wrap woocommerce">
	<input name="post_title" type="hidden" value="<?php echo empty( $post->post_title ) ? esc_attr( $order_type_object->labels->singular_name ) : esc_attr( $post->post_title ); ?>" />
	<input name="post_status" type="hidden" value="<?php echo esc_attr( $post->post_status ); ?>" />
	<div id="order_data" class="panel woocommerce-order-data">
		<h2 class="woocommerce-order-data__heading">
			<?php

			printf(
				/* translators: 1: order type 2: order number */
				esc_html_x( '%1$s #%2$s details', 'edit quote header', 'quote-requests-for-woocommerce' ),
				esc_html( $order_type_object->labels->singular_name ),
				esc_html( $quote->get_order_number() )
			);

			?>
		</h2>
		<p class="woocommerce-order-data__meta order_number">
			<?php

			$meta_list = array();

			if ( dws_qrwc_should_display_quote_date_type( 'expired', $quote ) ) {
				$meta_list[] = sprintf(
					/* translators: 1: date 2: time */
					__( 'Expired on %1$s @ %2$s', 'quote-requests-for-woocommerce' ),
					wc_format_datetime( $quote->get_date_expired() ),
					wc_format_datetime( $quote->get_date_expired(), get_option( 'time_format' ) )
				);
			} elseif ( dws_qrwc_should_display_quote_date_type( 'cancelled', $quote ) ) {
				$meta_list[] = sprintf(
					/* translators: 1: date 2: time */
					__( 'Cancelled on %1$s @ %2$s', 'quote-requests-for-woocommerce' ),
					wc_format_datetime( $quote->get_date_cancelled() ),
					wc_format_datetime( $quote->get_date_cancelled(), get_option( 'time_format' ) )
				);
			} elseif ( dws_qrwc_should_display_quote_date_type( 'accepted', $quote ) ) {
				$meta_list[] = sprintf(
					/* translators: 1: date 2: time */
					__( 'Accepted on %1$s @ %2$s', 'quote-requests-for-woocommerce' ),
					wc_format_datetime( $quote->get_date_accepted() ),
					wc_format_datetime( $quote->get_date_accepted(), get_option( 'time_format' ) )
				);
			} elseif ( dws_qrwc_should_display_quote_date_type( 'rejected', $quote ) ) {
				$meta_list[] = sprintf(
					/* translators: 1: date 2: time */
					__( 'Rejected on %1$s @ %2$s', 'quote-requests-for-woocommerce' ),
					wc_format_datetime( $quote->get_date_rejected() ),
					wc_format_datetime( $quote->get_date_rejected(), get_option( 'time_format' ) )
				);
			}

			$ip_address = $quote->get_customer_ip_address();
			if ( ! empty( $ip_address ) ) {
				$meta_list[] = sprintf(
					/* translators: %s: IP address */
					__( 'Customer IP: %s', 'quote-requests-for-woocommerce' ),
					'<span class="woocommerce-Order-customerIP">' . esc_html( $ip_address ) . '</span>'
				);
			}

			$meta_list = apply_filters( dws_qrwc_get_hook_tag( 'admin_quote_data', 'meta_list' ), $meta_list, $quote );
			echo wp_kses_post( implode( '. ', $meta_list ) );

			?>
		</p>

		<div class="order_data_column_container">
			<?php
			require 'html-quote-data-general.php';
			require 'html-quote-data-billing.php';
			require 'html-quote-data-shipping.php';
			?>
		</div>

		<div class="clear"></div>
	</div>
</div>
