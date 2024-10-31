<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Account;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Integers;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Strings;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Users;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the execution of quote actions.
 *
 * @SuppressWarnings(PHPMD.ExitExpression)
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Actions extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_action( 'wp_loaded', $this, 'handle_accept_action', 20 );
		$hooks_service->add_action( 'wp_loaded', $this, 'handle_reject_action', 20 );
		$hooks_service->add_action( 'wp_loaded', $this, 'handle_cancel_action', 20 );
	}

	// endregion

	// region ACTIONS

	/**
	 * Handles a customer request to accept a quote.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_accept_action(): void {
		$request = $this->validate_request( 'accept-quote' );
		if ( \is_null( $request ) ) {
			return;
		}

		\wc_nocache_headers();
		list( $quote, $redirect ) = $request;

		$user_can_accept = Users::has_capabilities( 'accept_dws_quote', array( $quote->get_id() ) );
		if ( $user_can_accept ) {
			$result = dws_qrwc_accept_quote( $quote, true, \apply_filters( $this->get_hook_tag( 'acceptance_note' ), null, $quote ) );
			if ( \is_wp_error( $result ) ) {
				if ( 'dws_invalid_status' === $result->get_error_code() ) {
					\wc_add_notice( __( 'Your quote can no longer be accepted. Please contact us if you need assistance.', 'quote-requests-for-woocommerce' ), 'error' );
				} else {
					/* translators: %s: error message */
					\wc_add_notice( \sprintf( \__( 'Your quote could not be accepted due to an error: %s', 'quote-requests-for-woocommerce' ), $result->get_error_message() ), 'error' );
				}
			} else {
				$redirect = empty( $redirect ) ? $result->get_checkout_payment_url() : $redirect;

				\wc_add_notice(
					\apply_filters( $this->get_hook_tag( 'accepted_quote', 'notice' ), \sprintf( /* translators: quote number */ \__( 'You have accepted quote #%s.', 'quote-requests-for-woocommerce' ), $quote->get_quote_number() ) ),
					\apply_filters( $this->get_hook_tag( 'accepted_quote', 'notice_type' ), 'notice' )
				);

				/**
				 * Triggered after a customer accepted a quote.
				 *
				 * @since   1.0.0
				 * @version 1.0.0
				 */
				\do_action( $this->get_hook_tag( 'accepted_quote' ), $quote, $redirect );
			}
		} else {
			\wc_add_notice( __( 'Invalid quote request.', 'quote-requests-for-woocommerce' ), 'error' );
		}

		if ( ! empty( $redirect ) ) {
			\wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Handles a customer request to reject a quote.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_reject_action(): void {
		$request = $this->validate_request( 'reject-quote' );
		if ( \is_null( $request ) ) {
			return;
		}

		\wc_nocache_headers();
		list( $quote, $redirect ) = $request;

		$user_can_reject = Users::has_capabilities( 'reject_dws_quote', array( $quote->get_id() ) );
		if ( $user_can_reject ) {
			$result = dws_qrwc_reject_quote( $quote, true, \apply_filters( $this->get_hook_tag( 'rejection_note' ), null, $quote ) );
			if ( \is_wp_error( $result ) ) {
				if ( 'dws_invalid_status' === $result->get_error_code() ) {
					\wc_add_notice( __( 'Your quote can no longer be rejected. Please contact us if you need assistance.', 'quote-requests-for-woocommerce' ), 'error' );
				} else {
					/* translators: %s: error message */
					\wc_add_notice( \sprintf( \__( 'Your quote could not be rejected due to an error: %s', 'quote-requests-for-woocommerce' ), $result->get_error_message() ), 'error' );
				}
			} else {
				\wc_add_notice(
					\apply_filters( $this->get_hook_tag( 'rejected_quote', 'notice' ), \sprintf( /* translators: quote number */ \__( 'You have rejected quote #%s.', 'quote-requests-for-woocommerce' ), $quote->get_quote_number() ) ),
					\apply_filters( $this->get_hook_tag( 'rejected_quote', 'notice_type' ), 'notice' )
				);

				/**
				 * Triggered after a customer rejected a quote.
				 *
				 * @since   1.0.0
				 * @version 1.0.0
				 */
				\do_action( $this->get_hook_tag( 'rejected_quote' ), $quote );
			}
		} else {
			\wc_add_notice( __( 'Invalid quote request.', 'quote-requests-for-woocommerce' ), 'error' );
		}

		if ( ! empty( $redirect ) ) {
			\wp_safe_redirect( $redirect );
			exit;
		}
	}

	/**
	 * Handles a customer request to cancel a quote request.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function handle_cancel_action(): void {
		$request = $this->validate_request( 'cancel-quote' );
		if ( \is_null( $request ) ) {
			return;
		}

		\wc_nocache_headers();
		list( $quote, $redirect ) = $request;

		$user_can_cancel = Users::has_capabilities( 'cancel_dws_quote', array( $quote->get_id() ) );
		if ( $user_can_cancel ) {
			$result = dws_qrwc_cancel_quote( $quote, true, \apply_filters( $this->get_hook_tag( 'cancellation_note' ), null, $quote ) );
			if ( \is_wp_error( $result ) ) {
				if ( 'dws_invalid_status' === $result->get_error_code() ) {
					\wc_add_notice( \__( 'Your quote request can no longer be cancelled. Please contact us if you need assistance.', 'quote-requests-for-woocommerce' ), 'error' );
				} else {
					/* translators: %s: error message */
					\wc_add_notice( \sprintf( \__( 'Your quote request could not be cancelled due to an error: %s', 'quote-requests-for-woocommerce' ), $result->get_error_message() ), 'error' );
				}
			} else {
				\wc_add_notice(
					\apply_filters( $this->get_hook_tag( 'cancelled_quote', 'notice' ), /* translators: quote number */ \__( 'You have cancelled quote request #%s.', 'quote-requests-for-woocommerce' ) ),
					\apply_filters( $this->get_hook_tag( 'cancelled_quote', 'notice_type' ), 'notice' )
				);

				/**
				 * Triggered after a customer cancelled a quote.
				 *
				 * @since   1.0.0
				 * @version 1.0.0
				 */
				\do_action( $this->get_hook_tag( 'cancelled_quote' ), $quote );
			}
		} else {
			\wc_add_notice( \__( 'Invalid quote request.', 'quote-requests-for-woocommerce' ), 'error' );
		}

		if ( ! empty( $redirect ) ) {
			\wp_safe_redirect( $redirect );
			exit;
		}
	}

	// endregion

	// region HELPERS

	/**
	 * Performs a series of blanket validations for a given quote action.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $action     The action to perform the validations for.
	 *
	 * @return  array|null
	 */
	protected function validate_request( string $action ): ?array {
		if ( ! isset( $_GET[ $action ], $_GET['quote-key'], $_GET['quote-id'], $_GET['_wpnonce'] ) || ! \wp_verify_nonce( Strings::maybe_cast_input( INPUT_GET, '_wpnonce' ), "dws-qrwc-$action" ) ) {
			return null;
		}

		$quote_key = Strings::maybe_cast_input( INPUT_GET, 'quote-key' );
		$quote_id  = Integers::maybe_cast_input( INPUT_GET, 'quote-id' );
		$redirect  = \wp_sanitize_redirect( Strings::maybe_cast_input( INPUT_GET, 'redirect', '' ) );

		$quote = dws_qrwc_get_quote( $quote_id );
		if ( \is_null( $quote ) ) {
			\wc_add_notice( \__( 'Invalid quote ID.', 'quote-requests-for-woocommerce' ), 'error' );
			return null;
		} elseif ( $quote->get_id() !== $quote_id || ! \hash_equals( $quote->get_quote_key(), $quote_key ) ) {
			\wc_add_notice( __( 'Invalid quote request.', 'quote-requests-for-woocommerce' ), 'error' );
			return null;
		}

		return array( $quote, $redirect );
	}

	// endregion
}
