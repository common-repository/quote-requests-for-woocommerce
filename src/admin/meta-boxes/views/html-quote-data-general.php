<?php
/**
 * Outputs the content of the first column of the quote data meta box.
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
		<?php echo esc_html_x( 'General', 'quote data meta-box column', 'quote-requests-for-woocommerce' ); ?>
	</h3>

	<p class="form-field form-field-wide">
		<label for="order_date">
			<?php esc_html_e( 'Date created:', 'quote-requests-for-woocommerce' ); ?>
		</label>
		<input type="text" class="date-picker" name="order_date" maxlength="10" value="<?php echo esc_attr( date_i18n( 'Y-m-d', strtotime( $post->post_date ) ) ); ?>" pattern="<?php echo esc_attr( dws_qrwc_wc_date_input_html_pattern() ); ?>" />@
		&lrm;
		<input type="number" class="hour" placeholder="<?php esc_attr_e( 'h', 'quote-requests-for-woocommerce' ); ?>" name="order_date_hour" min="0" max="23" step="1" value="<?php echo esc_attr( date_i18n( 'H', strtotime( $post->post_date ) ) ); ?>" pattern="([01]?[0-9]{1}|2[0-3]{1})" />:
		<input type="number" class="minute" placeholder="<?php esc_attr_e( 'm', 'quote-requests-for-woocommerce' ); ?>" name="order_date_minute" min="0" max="59" step="1" value="<?php echo esc_attr( date_i18n( 'i', strtotime( $post->post_date ) ) ); ?>" pattern="[0-5]{1}[0-9]{1}" />
		<input type="hidden" name="order_date_second" value="<?php echo esc_attr( date_i18n( 's', strtotime( $post->post_date ) ) ); ?>" />
	</p>

	<p class="form-field form-field-wide wc-order-status">
		<label for="order_status">
			<?php

			esc_html_e( 'Status:', 'quote-requests-for-woocommerce' );
			if ( $quote->has_status( 'quote-accepted' ) ) {
				$accepted_order = $quote->get_accepted_order();
				if ( ! is_null( $accepted_order ) ) {
					printf(
						'<a href="%s">%s</a>',
						esc_url( $accepted_order->get_edit_order_url() ),
						' ' . esc_html__( 'View order &rarr;', 'quote-requests-for-woocommerce' )
					);
				}
			}

			?>
		</label>
		<?php if ( $quote->has_status( dws_qrwc_get_quote_finalized_statuses( $quote ) ) ) : ?>
			<strong>
				<?php echo esc_html( dws_qrwc_get_quote_status_name( $quote->get_status() ) ); ?>
			</strong>
			<input type="hidden" name="order_status" value="<?php echo esc_attr( 'wc-' . $quote->get_status() ); ?>"/>
		<?php else : ?>
			<select id="order_status" name="order_status" class="wc-enhanced-select">
				<?php
				$statuses = dws_qrwc_get_quote_statuses();
				foreach ( $statuses as $slug => $name ) {
					echo '<option value="' . esc_attr( $slug ) . '" ' . selected( $slug, 'wc-' . $quote->get_status( 'edit' ), false ) . '>' . esc_html( $name ) . '</option>';
				}
				?>
			</select>
		<?php endif; ?>
	</p>

	<p class="form-field form-field-wide wc-customer-user">
		<!--email_off--> <!-- Disable CloudFlare email obfuscation -->
		<label for="customer_user">
			<?php
			esc_html_e( 'Customer:', 'quote-requests-for-woocommerce' );
			if ( $quote->get_user_id( 'edit' ) ) {
				$args = array(
					'post_status'    => 'all',
					'post_type'      => 'dws_shop_quote',
					'_customer_user' => absint( $quote->get_user_id( 'edit' ) ),
				);
				printf(
					'<a href="%s">%s</a>',
					esc_url( add_query_arg( $args, admin_url( 'edit.php' ) ) ),
					' ' . esc_html__( 'View other quotes &rarr;', 'quote-requests-for-woocommerce' )
				);
				printf(
					'<a href="%s">%s</a>',
					esc_url( add_query_arg( 'user_id', $quote->get_user_id( 'edit' ), admin_url( 'user-edit.php' ) ) ),
					' ' . esc_html__( 'Profile &rarr;', 'quote-requests-for-woocommerce' )
				);
			}
			?>
		</label>
		<?php
		$user_string = '';
		$user_id     = '';
		if ( $quote->get_user_id() && ( false !== get_userdata( $quote->get_user_id() ) ) ) {
			$user_id     = absint( $quote->get_user_id() );
			$user        = get_user_by( 'id', $user_id );
			$user_string = sprintf(
				/* translators: 1: user display name 2: user ID 3: user email */
				esc_html__( '%1$s (#%2$s &ndash; %3$s)', 'quote-requests-for-woocommerce' ),
				$user->display_name,
				absint( $user->ID ),
				$user->user_email
			);
		}
		?>
		<select class="wc-customer-search" id="customer_user" name="customer_user" data-placeholder="<?php esc_attr_e( 'Guest', 'quote-requests-for-woocommerce' ); ?>" data-allow_clear="true">
			<option value="<?php echo esc_attr( $user_id ); ?>" selected="selected">
				<?php echo htmlspecialchars( wp_kses_post( $user_string ) ); // phpcs:ignore ?>
			</option>
		</select>
		<!--/email_off-->
	</p>

	<?php

	/**
	 * Triggered after outputting the quote details.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	do_action( dws_qrwc_get_hook_tag( 'admin_quote_data', 'after_quote_details' ), $quote );

	?>
</div>
