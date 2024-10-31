<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\DataTypes\Arrays;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\Actions\SetupHooksTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Hooks\HooksService;

\defined( 'ABSPATH' ) || exit;

/**
 * Handles the registration of custom quote emails and customization of existing emails.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
final class Emails extends AbstractPluginFunctionality {
	// region TRAITS

	use SetupHooksTrait;

	// endregion

	// region INHERITED FUNCTIONS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function register_hooks( HooksService $hooks_service ): void {
		$hooks_service->add_filter( 'woocommerce_email_classes', $this, 'register_new_emails' );
		$hooks_service->add_filter( 'woocommerce_email_actions', $this, 'filter_email_actions' );
		$hooks_service->add_filter( 'woocommerce_template_directory', $this, 'filter_template_directory', 99, 2 );

		$hooks_service->add_action( 'woocommerce_email_order_details', $this, 'maybe_set_quote_as_global', 1 );
		$hooks_service->add_filter( 'gettext_woocommerce', $this, 'maybe_filter_translations', 99, 2 );
		$hooks_service->add_action( 'woocommerce_email_customer_details', $this, 'unset_quote_as_global', 999 );
	}

	// endregion

	// region HOOKS

	/**
	 * Registers new emails with WC.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $emails     The WC emails already registered.
	 *
	 * @return  array
	 */
	public function register_new_emails( array $emails ): array {
		$emails = Arrays::insert_after(
			$emails,
			'WC_Email_New_Order',
			array(
				'DWS_Email_New_Quote_Request' => new \DWS_Email_New_Quote_Request(),
			)
		);
		$emails = Arrays::insert_after(
			$emails,
			'WC_Email_Cancelled_Order',
			array(
				'DWS_Email_Cancelled_Quote_Request' => new \DWS_Email_Cancelled_Quote_Request(),
			)
		);
		$emails = Arrays::insert_after(
			$emails,
			'WC_Email_Customer_Processing_Order',
			array(
				'DWS_Email_Customer_Processing_Quote_Request' => new \DWS_Email_Customer_Processing_Quote_Request(),
				'DWS_Email_Customer_Waiting_Quote_Request' => new \DWS_Email_Customer_Waiting_Quote_Request(),
				'DWS_Email_Rejected_Quote'                 => new \DWS_Email_Rejected_Quote(),
			)
		);

		return apply_filters( $this->get_hook_tag( 'register_email_classes' ), $emails );
	}

	/**
	 * Appends quotes transactional emails actions.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   array   $actions    Actions that trigger email notifications.
	 *
	 * @return  array
	 */
	public function filter_email_actions( array $actions ): array {
		$actions[] = 'woocommerce_order_status_pending_to_quote-request';
		$actions[] = 'woocommerce_order_status_quote-request_to_quote-cancelled';
		$actions[] = 'woocommerce_order_status_quote-request_to_quote-waiting';
		$actions[] = 'woocommerce_order_status_quote-rejected_to_quote-waiting';
		$actions[] = 'woocommerce_order_status_quote-waiting_to_quote-rejected';

		return \apply_filters( $this->get_hook_tag( 'register_email_actions' ), $actions );
	}

	/**
	 * Filters the WC admin message about where to copy the email template in the stylesheet directory.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $template_dir   The stylesheet directory template dir.
	 * @param   string  $template       The template the filter is for.
	 *
	 * @return  string
	 */
	public function filter_template_directory( string $template_dir, string $template ): string {
		if ( false !== \strpos( $template, 'quote' ) ) {
			$template_dir = 'woocommerce/quote-requests';
		}

		return $template_dir;
	}

	/**
	 * If the email being sent out is for a quote, store that quote in a global so other filters know to act.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   \WC_Order   $order      The order object.
	 */
	public function maybe_set_quote_as_global( \WC_Order $order ): void {
		if ( true === dws_qrwc_is_quote( $order ) ) {
			$GLOBALS['dws_quote'] = $order;
		}
	}

	/**
	 * Replace certain WC translations inside transactional WC emails.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   string  $translation    Translated text.
	 * @param   string  $text           Original text.
	 *
	 * @return  string
	 */
	public function maybe_filter_translations( string $translation, string $text ): string {
		if ( isset( $GLOBALS['dws_quote'] ) && true === dws_qrwc_is_quote( $GLOBALS['dws_quote'] ) ) {
			$translation = dws_qrwc_get_wc_order_email_translation( $text, $GLOBALS['dws_quote'] ) ?? $translation;
		}

		return $translation;
	}

	/**
	 * Unset the quote global after email content generation to avoid potential conflicts.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function unset_quote_as_global() {
		unset( $GLOBALS['dws_quote'] );
	}

	// endregion
}
