<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Quote request cancelled email.
 *
 * An email sent to the admin when a quote request is cancelled by a customer.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Email_Cancelled_Quote_Request extends WC_Email_Cancelled_Order {
	// region MAGIC METHODS

	/**
	 * DWS_Email_Cancelled_Quote_Request constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @noinspection PhpMissingParentConstructorInspection
	 */
	public function __construct() {
		$this->id          = 'dws_cancelled_quote_request';
		$this->title       = __( 'Cancelled quote request', 'quote-requests-for-woocommerce' );
		$this->description = __( 'Cancelled quote request emails are sent to chosen recipient(s) when a quote request is cancelled by the customer.', 'quote-requests-for-woocommerce' );

		$this->template_base  = Plugin::get_plugin_templates_path( true );
		$this->template_html  = 'emails/admin-cancelled-quote-request.php';
		$this->template_plain = 'emails/plain/admin-cancelled-quote-request.php';

		$this->placeholders = array(
			'{quote_date}'   => '',
			'{quote_number}' => '',
		);

		// Triggers for this email.
		add_action( 'woocommerce_order_status_quote-request_to_quote-cancelled_notification', array( $this, 'trigger' ), 10, 2 );

		// Call (grand-)parent constructor.
		WC_Email::__construct();

		// Other settings.
		$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
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
		return __( '[{site_title}]: Quote #{quote_number} has been cancelled', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_heading(): string {
		return __( 'Quote Cancelled: #{quote_number}', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_additional_content(): string {
		return '';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function trigger( $order_id, $order = false ) {
		$this->setup_locale();

		$quote = ( $order_id && ! is_a( $order, DWS_Quote::class ) )
			? dws_qrwc_get_quote( $order_id ) : $order;

		if ( is_a( $quote, DWS_Quote::class ) ) {
			$this->object                         = $quote;
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
				'sent_to_admin'      => true,
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
				'sent_to_admin'      => true,
				'plain_text'         => true,
				'email'              => $this,
			),
			'woocommerce/quote-requests/',
			$this->template_base
		);
	}

	// endregion
}
