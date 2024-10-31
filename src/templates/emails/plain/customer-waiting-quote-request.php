<?php
/**
 * Customer waiting quote request email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/emails/plain/customer-waiting-quote-request.php.
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

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer first name */
echo sprintf( esc_html__( 'Hi %s,', 'quote-requests-for-woocommerce' ), esc_html( $quote->get_billing_first_name() ) ) . "\n\n";
/* translators: %s: Quote number */
echo sprintf( esc_html__( 'We\'ve finished your personalized quote for your request #%s.', 'quote-requests-for-woocommerce' ), esc_html( $quote->get_quote_number() ) ) . "\n\n";
echo esc_html__( 'You can view your quote below. After you\'ve inspected the quote, please let us know if you decide to accept it.', 'quote-requests-for-woocommerce' ) . "\n\n";

if ( is_null( $tracking_url ) ) {
	echo esc_html__( 'You can accept your quote by replying to this email.', 'quote-requests-for-woocommerce' ) . "\n\n";
} else {
	/* translators: %s: URL to the recommended quote status checking page. */
	echo sprintf( esc_html__( 'You can accept or reject your quote here: %s', 'quote-requests-for-woocommerce' ), esc_url( $tracking_url ) ) . "\n\n";
}

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 */
do_action( 'woocommerce_email_order_details', $quote, $sent_to_admin, $plain_text, $email );

echo "\n----------------------------------------\n\n";

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $quote, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $quote, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
