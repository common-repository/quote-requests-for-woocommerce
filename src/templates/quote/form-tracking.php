<?php
/**
 * Quote tracking form.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/quotes/form-tracking.php.
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
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\quotes
 */

defined( 'ABSPATH' ) || exit;

global $post;

$quote_id    = sanitize_text_field( wp_unslash( $_REQUEST['quote_id'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
$quote_email = sanitize_email( wp_unslash( $_REQUEST['quote_email'] ?? '' ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

do_action( dws_qrwc_get_hook_tag( 'quote', array( 'tracking', 'before_form' ) ), $quote_id, $quote_email ); ?>

<form action="<?php echo esc_url( get_permalink( $post->ID ) ); ?>" method="post" class="woocommerce-form qrwc-form woocommerce-form-track-order qrwc-form-track-quote track_order">
	<p>
		<?php esc_html_e( 'To check the status of your quote request, please enter your Quote Request Number in the box below and press the "Check status" button. This was given to you on your receipt and in the confirmation email you should have received.', 'quote-requests-for-woocommerce' ); ?>
	</p>

	<p class="form-row form-row-first">
		<label for="quote_id">
			<?php esc_html_e( 'Quote request number', 'quote-requests-for-woocommerce' ); ?>
		</label>
		<input class="input-text" type="text" name="quote_id" id="quote_id" value="<?php echo esc_attr( $quote_id ); ?>" placeholder="<?php esc_attr_e( 'Found in your quote request confirmation email.', 'quote-requests-for-woocommerce' ); ?>" />
	</p>
	<p class="form-row form-row-last">
		<label for="quote_email">
			<?php esc_html_e( 'Billing email', 'quote-requests-for-woocommerce' ); ?>
		</label>
		<input class="input-text" type="text" name="quote_email" id="quote_email" value="<?php echo esc_attr( $quote_email ); ?>" placeholder="<?php esc_attr_e( 'Email you used during checkout.', 'quote-requests-for-woocommerce' ); ?>" />
	</p>
	<div class="clear"></div>

	<p class="form-row">
		<button type="submit" class="button" name="track" value="<?php esc_attr_e( 'Check status', 'quote-requests-for-woocommerce' ); ?>">
			<?php esc_html_e( 'Check status', 'quote-requests-for-woocommerce' ); ?>
		</button>
	</p>

	<?php wp_nonce_field( 'dws-qrwc-quote_tracking', 'dws-qrwc-quote-tracking-nonce' ); ?>
</form>

<?php do_action( dws_qrwc_get_hook_tag( 'quote', array( 'tracking', 'after_form' ) ), $quote_id, $quote_email ); ?>
