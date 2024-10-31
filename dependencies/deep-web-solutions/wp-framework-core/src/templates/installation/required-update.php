<?php

namespace DWS_QRWC_Deps;

/**
 * A message displayed when an installation update is available.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @package DeepWebSolutions\WP-Framework\Core\templates\installation
 *
 * @var     \DeepWebSolutions\Framework\Core\Functionalities\InstallationFunctionality  $this   Instance of the installation functionality.
 */
\defined( 'ABSPATH' ) || exit;
?>

<p id="dws-update-
<?php
echo esc_attr( $this->get_plugin()->get_plugin_slug() );
?>
">
	<?php
	echo wp_kses_post(
		wp_sprintf(
		/* translators: 1. Plugin name, 2. Plugin version, 3. Name of the update button */
			__( 'A data update is available for <strong>%1$s (v%2$s)</strong>. It is recommended to back up your database before proceeding. Please click the "%3$s" button when ready:', 'quote-requests-for-woocommerce' ),
			$this->get_plugin()->get_plugin_name(),
			$this->get_plugin()->get_plugin_version(),
			/* translators: Name of the update button */
			__( 'Update', 'quote-requests-for-woocommerce' )
		)
	);
	?>
</p>
<p>
	<button
		class="button button-primary button-large dws-update"
		aria-describedby="dws-update-
		<?php
		echo esc_attr( $this->get_plugin()->get_plugin_slug() );
		?>
		">
		<?php
		esc_html_e( 'Update', 'quote-requests-for-woocommerce' );
		?>
	</button>
</p>
<?php
