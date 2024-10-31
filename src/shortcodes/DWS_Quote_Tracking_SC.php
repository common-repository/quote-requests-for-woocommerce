<?php

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Outputtable\OutputFailureException;

defined( 'ABSPATH' ) || exit;

/**
 * Shortcode for enabling the tracking the status of a quote request without logging-in into an account.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Quote_Tracking_SC extends DWS_QRWC_Abstract_Shortcode {
	// region FIELDS AND CONSTANTS

	/**
	 * {@inheritdoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public const SHORTCODE = 'dws_qrwc_quote_tracking';

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	protected function output_helper( $attributes = '', ?string $content = null ): ?OutputFailureException {
		$nonce_value = wc_get_var( $_REQUEST['dws-qrwc-quote-tracking-nonce'], '' ); // phpcs:ignore WordPress.Security
		if ( isset( $_REQUEST['quote_id'] ) && wp_verify_nonce( $nonce_value, 'dws-qrwc-quote_tracking' ) ) {
			$quote_id    = empty( $_REQUEST['quote_id'] ) ? null : ltrim( wc_clean( wp_unslash( $_REQUEST['quote_id'] ) ), '#' ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$quote_email = empty( $_REQUEST['quote_email'] ) ? null : sanitize_email( wp_unslash( $_REQUEST['quote_email'] ) );

			if ( empty( $quote_id ) ) {
				wc_print_notice( __( 'Please enter a valid quote request number.', 'quote-requests-for-woocommerce' ), 'error' );
			} elseif ( empty( $quote_email ) ) {
				wc_print_notice( __( 'Please enter a valid email address.', 'quote-requests-for-woocommerce' ), 'error' );
			} else {
				$quote_id = apply_filters( dws_qrwc_get_hook_tag( 'quote_tracking_shortcode', 'quote_id' ), $quote_id );
				$quote    = dws_qrwc_get_quote( $quote_id );

				if ( $quote && $quote->get_id() && \strtolower( $quote->get_billing_email() ) === \strtolower( $quote_email ) ) {
					dws_qrwc_wc_get_template( 'quote/tracking.php', array( 'quote' => $quote ) );
					return null;
				} else {
					wc_print_notice( __( 'Sorry, the quote request could not be found. Please contact us if you are having difficulty finding your quote request details.', 'quote-requests-for-woocommerce' ), 'error' );
				}
			}
		}

		dws_qrwc_wc_get_template( 'quote/form-tracking.php' );
		return null;
	}

	// endregion
}
