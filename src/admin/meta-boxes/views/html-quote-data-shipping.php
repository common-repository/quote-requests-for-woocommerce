<?php
/**
 * Outputs the content of the third column of the quote data meta box.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\admin\meta-boxes
 *
 * @var     WP_Post_Type    $order_type_object
 * @var     DWS_Quote       $quote
 * @var     WP_Post         $post
 */

defined( 'ABSPATH' ) || exit; ?>

<div class="order_data_column">
	<h3>
		<?php esc_html_e( 'Shipping', 'quote-requests-for-woocommerce' ); ?>
		<a href="#" class="edit_address"><?php esc_html_e( 'Edit', 'quote-requests-for-woocommerce' ); ?></a>
		<span>
			<a href="#" class="load_customer_shipping" style="display:none;"><?php esc_html_e( 'Load shipping address', 'quote-requests-for-woocommerce' ); ?></a>
			<a href="#" class="billing-same-as-shipping" style="display:none;"><?php esc_html_e( 'Copy billing address', 'quote-requests-for-woocommerce' ); ?></a>
		</span>
	</h3>

	<div class="address">
		<?php

		// Display values.
		if ( $quote->get_formatted_shipping_address() ) {
			echo '<p>' . wp_kses( $quote->get_formatted_shipping_address(), array( 'br' => array() ) ) . '</p>';
		} else {
			echo '<p class="none_set"><strong>' . esc_html__( 'Address:', 'quote-requests-for-woocommerce' ) . '</strong> ' . esc_html__( 'No shipping address set.', 'quote-requests-for-woocommerce' ) . '</p>';
		}

		if ( ! empty( self::$shipping_fields ) ) {
			foreach ( self::$shipping_fields as $key => $field ) {
				if ( isset( $field['show'] ) && false === $field['show'] ) {
					continue;
				}

				$field_name = 'shipping_' . $key;

				if ( is_callable( array( $quote, 'get_' . $field_name ) ) ) {
					$field_value = $quote->{"get_$field_name"}( 'edit' );
				} else {
					$field_value = $quote->get_meta( '_' . $field_name );
				}

				if ( $field_value ) {
					echo '<p><strong>' . esc_html( $field['label'] ) . ':</strong> ' . wp_kses_post( $field_value ) . '</p>';
				}
			}
		}

		if ( dws_qrwc_wc_enable_order_notes_field() && $post->post_excerpt ) {
			echo '<p class="order_note"><strong>' . esc_html__( 'Customer provided note:', 'quote-requests-for-woocommerce' ) . '</strong> ' . nl2br( esc_html( $post->post_excerpt ) ) . '</p>';
		}

		?>
	</div>

	<div class="edit_address">
		<?php

		// Display form.
		if ( ! empty( self::$shipping_fields ) ) {
			foreach ( self::$shipping_fields as $key => $field ) {
				if ( ! isset( $field['type'] ) ) {
					$field['type'] = 'text';
				}
				if ( ! isset( $field['id'] ) ) {
					$field['id'] = '_shipping_' . $key;
				}

				$field_name = 'shipping_' . $key;

				if ( is_callable( array( $quote, 'get_' . $field_name ) ) ) {
					$field['value'] = $quote->{"get_$field_name"}( 'edit' );
				} else {
					$field['value'] = $quote->get_meta( '_' . $field_name );
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
		}

		if ( dws_qrwc_wc_enable_order_notes_field() ) :
			?>
			<p class="form-field form-field-wide">
				<label for="excerpt"><?php esc_html_e( 'Customer provided note', 'quote-requests-for-woocommerce' ); ?>:</label>
				<textarea rows="1" cols="40" name="excerpt" tabindex="6" id="excerpt" placeholder="<?php esc_attr_e( 'Customer notes about the order', 'quote-requests-for-woocommerce' ); ?>"><?php echo wp_kses_post( $post->post_excerpt ); ?></textarea>
			</p>
		<?php endif; ?>
	</div>

	<?php

	/**
	 * Triggered after outputting the quote shipping address.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	do_action( dws_qrwc_get_hook_tag( 'admin_quote_data', 'after_shipping_address' ), $quote );

	?>
</div>