<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Helpers\Request;

defined( 'ABSPATH' ) || exit;

/**
 * Customer Processing Quotation email.
 *
 * An email sent to the customer when a new quote request is received.
 *
 * @SuppressWarnings(PHPMD.LongClassName)
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Email_Customer_Processing_Quote_Request extends WC_Email_Customer_Processing_Order {
	// region MAGIC METHODS

	/**
	 * DWS_Email_Customer_Processing_Quote constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {
		$this->id             = 'dws_customer_processing_quote_request';
		$this->customer_email = true;

		$this->title       = __( 'Processing quote request', 'quote-requests-for-woocommerce' );
		$this->description = __( 'This is a quote notification sent to customers containing quote details after submitting a request.', 'quote-requests-for-woocommerce' );

		$this->template_base  = Plugin::get_plugin_templates_path( true );
		$this->template_html  = 'emails/customer-processing-quote-request.php';
		$this->template_plain = 'emails/plain/customer-processing-quote-request.php';

		$this->placeholders = array(
			'{quote_date}'   => '',
			'{quote_number}' => '',
		);

		// Triggers for this email.
		if ( Request::is_type( 'front' ) ) {
			add_action( 'woocommerce_order_status_pending_to_quote-request_notification', array( $this, 'trigger' ), 10, 2 );
		}

		// Call (grand-)parent constructor.
		WC_Email::__construct();
	}

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_subject(): string {
		return __( 'Your {site_title} quote request has been received!', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_heading(): string {
		return __( 'Thank you for your quote request', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function trigger( $order_id, $order = null ) {
		$this->setup_locale();

		$quote = ( $order_id && ! is_a( $order, DWS_Quote::class ) )
			? dws_qrwc_get_quote( $order_id ) : $order;

		if ( is_a( $quote, DWS_Quote::class ) ) {
			$this->object                         = $quote;
			$this->recipient                      = $this->object->get_billing_email();
			$this->placeholders['{quote_date}']   = wc_format_datetime( $this->object->get_date_created() );
			$this->placeholders['{quote_number}'] = $this->object->get_order_number();
		}

		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		}

		$this->restore_locale();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_content_html(): string {
		return wc_get_template_html(
			$this->template_html,
			array(
				'quote'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => false,
				'email'              => $this,
			),
			'woocommerce/quote-requests/',
			$this->template_base
		);
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_content_plain(): string {
		return wc_get_template_html(
			$this->template_plain,
			array(
				'quote'              => $this->object,
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'sent_to_admin'      => false,
				'plain_text'         => true,
				'email'              => $this,
			),
			'woocommerce/quote-requests/',
			$this->template_base
		);
	}

	// endregion
}
