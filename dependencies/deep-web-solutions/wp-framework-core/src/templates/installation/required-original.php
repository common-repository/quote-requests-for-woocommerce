<?php

namespace DWS_QRWC_Deps;

/**
 * A message displayed before the very first installation.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\templates\installation
 *
 * @var     \DeepWebSolutions\Framework\Core\Functionalities\InstallationFunctionality  $this   Instance of the installation functionality.
 */
\defined( 'ABSPATH' ) || exit;
?>

<p id="dws-install-
<?php
echo esc_attr( $this->get_plugin()->get_plugin_slug() );
?>
">
	<?php
	echo wp_kses_post(
		wp_sprintf(
		/* translators: 1. Plugin name, 2. Plugin version, 3. Name of the install button */
			__( '<strong>%1$s (v%2$s)</strong> needs to run its installation routine before it can be used. Please click the "%3$s" button to proceed:', 'quote-requests-for-woocommerce' ),
			$this->get_plugin()->get_plugin_name(),
			$this->get_plugin()->get_plugin_version(),
			/* translators: Name of the installation button */
			__( 'Install', 'quote-requests-for-woocommerce' )
		)
	);
	?>
</p>
<p>
	<button class="button button-primary button-large dws-install" aria-describedby="dws-install-
	<?php
	echo esc_attr( $this->get_plugin()->get_plugin_slug() );
	?>
	">
		<?php
		esc_html_e( 'Install', 'quote-requests-for-woocommerce' );
		?>
	</button>
</p>
<?php
