<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Quotes;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Actions\InitializeAdminNoticesServiceTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticesServiceAwareInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\AdminNoticeTypesEnum;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Helpers\AdminNoticesHelpersTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\AdminNotices\Notices\DismissibleAdminNotice;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the automatic expiration of quotes.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class Actions extends AbstractPluginFunctionality implements AdminNoticesServiceAwareInterface {
	// region TRAITS

	use AdminNoticesHelpersTrait;
	use InitializeAdminNoticesServiceTrait;
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
		$hooks_service->add_filter( 'woocommerce_order_actions', $this, 'register_meta_box_actions', 999 );
		$hooks_service->add_filter( 'woocommerce_admin_order_actions', $this, 'register_list_table_actions', 999, 2 );

		$hooks_service->add_action( 'woocommerce_order_action_send_quote_details', $this, 'action_send_quote_details' );
		$hooks_service->add_action( 'woocommerce_order_action_send_quote_details_admin', $this, 'action_send_quote_details_admin' );
		$hooks_service->add_action( 'woocommerce_order_action_accept_quote', $this, 'action_accept_quote' );
		$hooks_service->add_action( 'woocommerce_order_action_reject_quote', $this, 'action_reject_quote' );
	}

	// endregion

	// region HOOKS

	/**
	 * Removes default actions in the order-action-meta-box and adds new ones for quotes.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $actions    Actions currently registered.
	 *
	 * @return  array
	 */
	public function register_meta_box_actions( array $actions ): array {
		global $theorder;

		if ( true === dws_qrwc_is_quote( $theorder ) ) {
			$quote = dws_qrwc_get_quote( $theorder );

			$actions = array(
				'send_quote_details_admin' => \__( 'Resend new quote request notification', 'quote-requests-for-woocommerce' ),
			);

			if ( $quote->has_status( 'quote-request' ) ) {
				$actions['send_quote_details'] = \__( 'Email quote details to customer', 'quote-requests-for-woocommerce' );
			}
			if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_acceptance( $quote ) ) ) {
				$actions['accept_quote'] = \__( 'Accept on behalf of customer', 'quote-requests-for-woocommerce' );
			}
			if ( $quote->has_status( dws_qrwc_get_valid_quote_statuses_for_rejection( $quote ) ) ) {
				$actions['reject_quote'] = \__( 'Reject on behalf of customer', 'quote-requests-for-woocommerce' );
			}

			$actions = \apply_filters( $this->get_hook_tag( 'meta_box_actions' ), $actions, $quote );
		}

		return $actions;
	}

	/**
	 * Removes default actions in the list table and adds new ones for quotes.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array       $actions    Actions currently registered.
	 * @param   \WC_Order   $object     The order/quote that the row is being generated.
	 *
	 * @return  array
	 */
	public function register_list_table_actions( array $actions, \WC_Order $object ): array {
		if ( true === dws_qrwc_is_quote( $object ) ) {
			$quote = dws_qrwc_get_quote( $object );

			$actions = \apply_filters( $this->get_hook_tag( 'reset_list_table_actions' ), array(), $actions, $quote );

			$order = $quote->get_accepted_order();
			if ( ! \is_null( $order ) && $quote->has_status( 'quote-accepted' ) ) {
				$actions['view_accepted_order'] = array(
					'url'    => $order->get_edit_order_url(),
					'name'   => \__( 'View accepted order', 'quote-requests-for-woocommerce' ),
					'action' => 'view-accepted-order _blank',
				);
			}

			$actions = \apply_filters( $this->get_hook_tag( 'list_table_actions' ), $actions, $quote );
		}

		return $actions;
	}

	// endregion

	// region META BOX ACTIONS

	/**
	 * Triggers the resending of the customer new quote request email.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Quote  $quote  The quote object.
	 */
	public function action_send_quote_details( \DWS_Quote $quote ): void {
		/**
		 * Triggered before sending out the quote details email to customers.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		do_action( $this->get_hook_tag( 'before_resend_quote_emails' ), $quote, 'processing_quote_request' );

		WC()->mailer()->emails['DWS_Email_Customer_Processing_Quote_Request']->trigger( $quote->get_id(), $quote );
		$quote->add_order_note( \__( 'Quote request details manually sent to customer.', 'quote-requests-for-woocommerce' ), false, true );

		/**
		 * Triggered after sending out the quote details email to customers.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		do_action( $this->get_hook_tag( 'after_resend_quote_emails' ), $quote, 'processing_quote_request' );
	}

	/**
	 * Triggers the resending of the admin new quote request email.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Quote  $quote  The quote object.
	 */
	public function action_send_quote_details_admin( \DWS_Quote $quote ): void {
		/**
		 * Triggered before sending out the quote details email to the admin.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		do_action( $this->get_hook_tag( 'before_resend_quote_emails' ), $quote, 'new_quote_request' );

		WC()->mailer()->emails['DWS_Email_New_Quote_Request']->trigger( $quote->get_id(), $quote );

		/**
		 * Triggered after sending out the quote details email to the admin.
		 *
		 * @since   1.0.0
		 * @version 1.0.0
		 */
		do_action( $this->get_hook_tag( 'after_resend_quote_emails' ), $quote, 'new_quote_request' );
	}

	/**
	 * Accepts the quote on behalf of the customer.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Quote  $quote  The quote object.
	 */
	public function action_accept_quote( \DWS_Quote $quote ): void {
		$result = dws_qrwc_accept_quote( $quote, false );
		if ( \is_wp_error( $result ) ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleAdminNotice(
					$this->get_admin_notice_handle( 'acceptance-error' ),
					\sprintf(
						/* translators: %1$s: quote number; %2$s: error message contents. */
						\__( 'Failed to accept quote #%1$s. Error message: %2$s', 'quote-requests-for-woocommerce' ),
						$quote->get_quote_number(),
						$result->get_error_message()
					),
					AdminNoticeTypesEnum::ERROR
				),
				'user-meta'
			);
		}
	}

	/**
	 * Rejects the quote on behalf of the customer.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \DWS_Quote  $quote  The quote object.
	 */
	public function action_reject_quote( \DWS_Quote $quote ): void {
		$result = dws_qrwc_reject_quote( $quote, false );
		if ( \is_wp_error( $result ) ) {
			$this->get_admin_notices_service()->add_notice(
				new DismissibleAdminNotice(
					$this->get_admin_notice_handle( 'rejection-error' ),
					\sprintf(
						/* translators: %1$s: quote number; %2$s: error message contents. */
						\__( 'Failed to reject quote #%1$s. Error message: %2$s', 'quote-requests-for-woocommerce' ),
						$quote->get_quote_number(),
						$result->get_error_message()
					),
					AdminNoticeTypesEnum::ERROR
				),
				'user-meta'
			);
		}
	}

	// endregion
}
