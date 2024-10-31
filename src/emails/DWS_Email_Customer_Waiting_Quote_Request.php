<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Customer Waiting Quotation email.
 *
 * An email sent to the customer when a new quote request is waiting for their approval/rejection.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Email_Customer_Waiting_Quote_Request extends WC_Email {
	// region MAGIC METHODS

	/**
	 * DWS_Email_Customer_Waiting_Quote_Request constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id             = 'dws_customer_waiting_quote_request';
		$this->customer_email = true;

		$this->title       = __( 'Waiting on quote approval', 'quote-requests-for-woocommerce' );
		$this->description = __( 'This is a quote notification sent to customers to inform them that the quote is ready for their approval.', 'quote-requests-for-woocommerce' );

		$this->template_base  = Plugin::get_plugin_templates_path( true );
		$this->template_html  = 'emails/customer-waiting-quote-request.php';
		$this->template_plain = 'emails/plain/customer-waiting-quote-request.php';

		$this->placeholders = array(
			'{quote_date}'   => '',
			'{quote_number}' => '',
		);

		// Triggers for this email.
		add_action( 'woocommerce_order_status_quote-request_to_quote-waiting_notification', array( $this, 'trigger' ), 10, 2 );
		add_action( 'woocommerce_order_status_quote-rejected_to_quote-waiting_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();
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
		return __( 'Your {site_title} quote is ready!', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_heading(): string {
		return __( 'Please review your personalized quote', 'quote-requests-for-woocommerce' );
	}

	/**
	 * Triggers the sending of this email.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @param   int                 $quote_id   The order ID.
	 * @param   DWS_Quote|false     $quote      Order object.
	 */
	public function trigger( int $quote_id, $quote = false ) {
		$this->setup_locale();

		$quote = ( $quote_id && ! is_a( $quote, DWS_Quote::class ) )
			? dws_qrwc_get_quote( $quote_id ) : $quote;

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
				'tracking_url'       => dws_qrwc_get_recommended_quote_tracking_url( $this->object ),
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
				'tracking_url'       => dws_qrwc_get_recommended_quote_tracking_url( $this->object ),
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
