<?php

namespace DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\Actions;

use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializableExtensionTrait;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\DependencyInjection\ContainerAwareInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Hierarchy\ChildInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\PluginAwareInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationService;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareInterface;
use DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Validation\ValidationServiceAwareTrait;
use DWS_QRWC_Deps\Psr\Container\ContainerExceptionInterface;
use DWS_QRWC_Deps\Psr\Container\NotFoundExceptionInterface;
\defined( 'ABSPATH' ) || exit;
/**
 * Trait for setting the validation service on the using instance.
 *
 * @since   1.0.0
 * @version 1.0.0
 * @author  Antonius Hegyes <a.hegyes@deep-web-solutions.com>
 * @package DeepWebSolutions\WP-Framework\Utilities\Actions\Initializable
 */
trait InitializeValidationServiceTrait {

	// region TRAITS
	use ValidationServiceAwareTrait;
	use InitializableExtensionTrait;
	// endregion
	// region METHODS
	/**
	 * Try to automagically set a validation service on the instance.
	 *
	 * @since   1.0.0
	 * @version 1.0.0
	 *
	 * @throws  NotFoundExceptionInterface      Thrown if the container can't find an entry.
	 * @throws  ContainerExceptionInterface     Thrown if the container encounters some other error.
	 *
	 * @return  InitializationFailureException|null
	 */
	public function initialize_validation_service() : ?InitializationFailureException {
		if ( $this instanceof ChildInterface && $this->get_parent() instanceof ValidationServiceAwareInterface ) {
			/* @noinspection PhpUndefinedMethodInspection */
			$service = $this->get_parent()->get_validation_service();
		} elseif ( $this instanceof ContainerAwareInterface ) {
			$service = $this->get_container()->get( ValidationService::class );
		} elseif ( $this instanceof PluginAwareInterface && $this->get_plugin() instanceof ContainerAwareInterface ) {
			/* @noinspection PhpUndefinedMethodInspection */
			$service = $this->get_plugin()->get_container()->get( ValidationService::class );
		} else {
			return new InitializationFailureException( 'Validation service initialization scenario not supported' );
		}
		$this->set_validation_service( $service );
		return null;
	}
	// endregion
}
