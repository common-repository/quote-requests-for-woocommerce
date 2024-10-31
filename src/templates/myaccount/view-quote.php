<?php
/**
 * Shows the details of a particular quote on the account page.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/myaccount/view-quote.php.
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
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\myaccount
 *
 * @var     DWS_Quote   $quote
 */

defined( 'ABSPATH' ) || exit;

$notes = $quote->get_customer_order_notes();

do_action( dws_qrwc_get_hook_tag( 'account', array( 'view_quote', 'before_quote_info' ) ), $quote ); ?>

<p class="order-info quote-info">
	<?php
	echo wp_kses_post(
		apply_filters(
			dws_qrwc_get_hook_tag( 'account', array( 'view_quote', 'quote_info' ) ),
			sprintf(
				/* translators: 1: order number 2: order date 3: order status */
				esc_html__( 'Quote #%1$s was created on %2$s and is currently %3$s.', 'quote-requests-for-woocommerce' ),
				'<mark class="order-number">' . $quote->get_quote_number() . '</mark>',
				'<mark class="order-date">' . wc_format_datetime( $quote->get_date_created() ) . '</mark>',
				'<mark class="order-status">' . dws_qrwc_get_quote_status_name( $quote->get_status() ) . '</mark>'
			)
		)
	);
	?>
</p>

<?php do_action( dws_qrwc_get_hook_tag( 'account', array( 'view_quote', 'before_notes' ) ), $quote ); ?>

<?php if ( $notes ) : ?>
	<h2>
		<?php esc_html_e( 'Quote updates', 'quote-requests-for-woocommerce' ); ?>
	</h2>
	<ol class="woocommerce-OrderUpdates qrwc-QuoteUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
			<li class="woocommerce-OrderUpdate comment note">
				<div class="woocommerce-OrderUpdate-inner comment_container">
					<div class="woocommerce-OrderUpdate-text comment-text">
						<p class="woocommerce-OrderUpdate-meta meta">
							<?php echo date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'quote-requests-for-woocommerce' ), strtotime( $note->comment_date ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</p>
						<div class="woocommerce-OrderUpdate-description description">
							<?php echo wpautop( wptexturize( $note->comment_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
				</div>
			</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<?php do_action( dws_qrwc_get_hook_tag( 'account', 'view_quote' ), $quote ); ?>
