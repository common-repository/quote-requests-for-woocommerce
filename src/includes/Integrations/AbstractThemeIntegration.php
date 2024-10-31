<?php

namespace DeepWebSolutions\WC_Plugins\QuoteRequests\Integrations;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\States\Disableable\DisabledLocalTrait;

\defined( 'ABSPATH' ) || exit;

/**
 * Template for encapsulating some most-often needed functionalities of a theme integration.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 */
abstract class AbstractThemeIntegration extends AbstractPluginFunctionality {
	// region TRAITS

	use DisabledLocalTrait;

	// endregion

	// region INHERITED METHODS

	/**
	 * {@inheritDoc}
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 */
	public function is_disabled_local(): bool {
		$theme = dws_qrwc_get_active_theme();
		return is_null( $theme ) || $this->get_theme_dependency_disabled() !== $theme->get_template();
	}

	/**
	 * Returns the theme dependency.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @return  string
	 */
	abstract protected function get_dependent_theme(): string;

	// endregion
}
