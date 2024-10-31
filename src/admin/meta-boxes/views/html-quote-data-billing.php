<?php
/**
 * Outputs the content of the second column of the quote data meta box.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\admin\meta-boxes
 *
 * @var     WP_Post_Type            $order_type_object
 * @var     DWS_Quote               $quote
 * @var     WP_Post                 $post
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="order_data_column">
	<h3>
		<?php esc_html_e( 'Billing', 'quote-requests-for-woocommerce' ); ?>
		<a href="#" class="edit_address"><?php esc_html_e( 'Edit', 'quote-requests-for-woocommerce' ); ?></a>
		<span>
			<a href="#" class="load_customer_billing" style="display:none;">
				<?php esc_html_e( 'Load billing address', 'quote-requests-for-woocommerce' ); ?>
			</a>
		</span>
	</h3>

	<div class="address">
		<?php

		// Display values.
		if ( $quote->get_formatted_billing_address() ) {
			echo '<p>' . wp_kses( $quote->get_formatted_billing_address(), array( 'br' => array() ) ) . '</p>';
		} else {
			echo '<p class="none_set"><strong>' . esc_html__( 'Address:', 'quote-requests-for-woocommerce' ) . '</strong> ' . esc_html__( 'No billing address set.', 'quote-requests-for-woocommerce' ) . '</p>';
		}

		foreach ( self::$billing_fields as $key => $field ) {
			if ( isset( $field['show'] ) && false === $field['show'] ) {
				continue;
			}

			$field_name = 'billing_' . $key;

			if ( isset( $field['value'] ) ) {
				$field_value = $field['value'];
			} elseif ( is_callable( array( $quote, 'get_' . $field_name ) ) ) {
				$field_value = $quote->{"get_$field_name"}( 'edit' );
			} else {
				$field_value = $quote->get_meta( '_' . $field_name );
			}

			if ( 'billing_phone' === $field_name ) {
				$field_value = wc_make_phone_clickable( $field_value );
			} else {
				$field_value = make_clickable( esc_html( $field_value ) );
			}

			if ( $field_value ) {
				echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . wp_kses_post( $field_value ) . '</p>';
			}
		}

		?>
	</div>

	<div class="edit_address">
		<?php

		// Display form.
		foreach ( self::$billing_fields as $key => $field ) {
			if ( ! isset( $field['type'] ) ) {
				$field['type'] = 'text';
			}
			if ( ! isset( $field['id'] ) ) {
				$field['id'] = '_billing_' . $key;
			}

			$field_name = 'billing_' . $key;

			if ( ! isset( $field['value'] ) ) {
				if ( is_callable( array( $quote, 'get_' . $field_name ) ) ) {
					$field['value'] = $quote->{"get_$field_name"}( 'edit' );
				} else {
					$field['value'] = $quote->get_meta( '_' . $field_name );
				}
			}

			switch ( $field['type'] ) {
				case 'select':
					woocommerce_wp_select( $field );
					break;
				default:
					woocommerce_wp_text_input( $field );
					break;
			}
		}

		?>
	</div>

	<?php

	/**
	 * Triggered after outputting the quote billing address.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	do_action( dws_qrwc_get_hook_tag( 'admin_quote_data', 'after_billing_address' ), $quote );

	?>
</div>
