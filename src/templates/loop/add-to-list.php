<?php
/**
 * Loop add simple product to quote request list button.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/quote-requests/loop/add-to-list/simple.php.
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
 * @package DeepWebSolutions\WC-Plugins\QuoteRequests\templates\loop
 *
 * @var     string          $add_to_list_url
 * @var     string          $button_text
 */

defined( 'ABSPATH' ) || exit; ?>

<a href="<?php echo esc_url( $add_to_list_url ); ?>" data-quantity="<?php echo esc_attr( $args['quantity'] ?? 1 ); ?>" class="<?php echo esc_attr( $args['class'] ?? 'button' ); ?>" <?php echo wc_implode_html_attributes( $args['attributes'] ?? array() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
	<?php echo esc_html( $button_text ); ?>
</a>
