<?php

use DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin;

defined( 'ABSPATH' ) || exit;

/**
 * Quote rejected email.
 *
 * An email sent to the admin when a quote is rejected by a customer.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
class DWS_Email_Rejected_Quote extends WC_Email {
	// region MAGIC METHODS

	/**
	 * DWS_Email_Rejected_Quote constructor.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function __construct() {
		$this->id          = 'dws_rejected_quote';
		$this->title       = __( 'Rejected quote', 'quote-requests-for-woocommerce' );
		$this->description = __( 'Rejected quote emails are sent to chosen recipient(s) when a quote is rejected by the customer.', 'quote-requests-for-woocommerce' );

		$this->template_base  = Plugin::get_plugin_templates_path( true );
		$this->template_html  = 'emails/admin-rejected-quote.php';
		$this->template_plain = 'emails/plain/admin-rejected-quote.php';

		$this->placeholders = array(
			'{quote_date}'   => '',
			'{quote_number}' => '',
		);

		// Triggers for this email.
		add_action( 'woocommerce_order_status_quote-waiting_to_quote-rejected_notification', array( $this, 'trigger' ), 10, 2 );

		// Call parent constructor.
		parent::__construct();

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
		return __( '[{site_title}]: Quote #{quote_number} has been rejected', 'quote-requests-for-woocommerce' );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function get_default_heading(): string {
		return __( 'Quote Rejected: #{quote_number}', 'quote-requests-for-woocommerce' );
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
