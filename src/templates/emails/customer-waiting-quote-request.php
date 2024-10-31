<?php
/**
 * Customer waiting quote request email
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/emails/customer-waiting-quote-request.php.
 *
 * HOWEVER, on occasion Deep Web Solutions will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.deep-web-solutions.com/article-categories/quote-requests-for-woocommerce
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\emails
 *
 * @var     DWS_Quote                                   $quote
 * @var     string|null                                 $tracking_url
 * @var     string                                      $email_heading
 * @var     string                                      $additional_content
 * @var     bool                                        $sent_to_admin
 * @var     bool                                        $plain_text
 * @var     DWS_Email_Customer_Waiting_Quote_Request    $email
 */

defined( 'ABSPATH' ) || exit;

/*
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p>
	<?php
	/* translators: %s: Customer first name */
	printf( esc_html__( 'Hi %s,', 'quote-requests-for-woocommerce' ), esc_html( $quote->get_billing_first_name() ) );
	?>
</p>
<p>
	<?php
	/* translators: %s: Quote request number */
	printf( esc_html__( 'We\'ve finished your personalized quote for your request #%s.', 'quote-requests-for-woocommerce' ), esc_html( $quote->get_quote_number() ) );
	?>
</p>
<p>
	<?php esc_html_e( 'You can view your quote below. After you\'ve inspected the quote, please let us know if you decide to accept it.', 'quote-requests-for-woocommerce' ); ?>
</p>
<p>
	<?php
	if ( is_null( $tracking_url ) ) :
		esc_html_e( 'You can accept your quote by replying to this email.', 'quote-requests-for-woocommerce' );
	else :
		?>
		<a class="link" href="<?php echo esc_url( $tracking_url ); ?>">
			<?php esc_html_e( 'Click here to accept or reject your quote.', 'quote-requests-for-woocommerce' ); ?>
		</a>
	<?php endif; ?>
</p>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 */
do_action( 'woocommerce_email_order_details', $quote, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $quote, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $quote, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
