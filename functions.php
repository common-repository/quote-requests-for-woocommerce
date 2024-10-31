<?php

use  DeepWebSolutions\WC_Plugins\QuoteRequests\Plugin ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Core\AbstractPluginFunctionality ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Actions\Initializable\InitializationFailureException ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Foundations\Exceptions\NotFoundException ;
use  DWS_QRWC_Deps\DeepWebSolutions\Framework\Utilities\Caching\CachingService ;
use  DWS_QRWC_Deps\DI\Container ;
use  DWS_QRWC_Deps\DI\ContainerBuilder ;
defined( 'ABSPATH' ) || exit;
// region DEPENDENCY INJECTION
/**
 * Returns a container singleton that enables one to setup unit testing by passing an environment file for class mapping in PHP-DI.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $environment    The environment rules that the container should be initialized on.
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Container
 */
function dws_qrwc_di_container( string $environment = 'prod' ) : Container
{
    static  $container = null ;
    
    if ( is_null( $container ) ) {
        $container_builder = new ContainerBuilder();
        $container_builder->addDefinitions( __DIR__ . "/config_{$environment}.php" );
        /* @noinspection PhpUnhandledExceptionInspection */
        $container = $container_builder->build();
    }
    
    return $container;
}

/**
 * Returns the plugin's main class instance.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @noinspection PhpDocMissingThrowsInspection
 *
 * @return  Plugin
 */
function dws_qrwc_instance() : Plugin
{
    /* @noinspection PhpUnhandledExceptionInspection */
    return dws_qrwc_di_container()->get( Plugin::class );
}

/**
 * Returns a plugin component by its container ID.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $component_id   The ID of the component as defined in the DI container.
 *
 * @return  AbstractPluginFunctionality|null
 */
function dws_qrwc_component( string $component_id ) : ?AbstractPluginFunctionality
{
    try {
        return dws_qrwc_di_container()->get( $component_id );
    } catch ( Exception $e ) {
        return null;
    }
}

// endregion
// region LIFECYCLE
/**
 * Initialization function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @return  InitializationFailureException|null
 */
function dws_qrwc_instance_initialize() : ?InitializationFailureException
{
    $result = dws_qrwc_instance()->initialize();
    
    if ( is_null( $result ) ) {
        do_action( 'dws_qrwc_initialized' );
    } else {
        do_action( 'dws_qrwc_initialization_failure', $result );
    }
    
    return $result;
}

/**
 * Activate function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_qrwc_plugin_activate()
{
    
    if ( is_null( dws_qrwc_instance_initialize() ) ) {
        dws_qrwc_instance()->activate();
        delete_option( 'rewrite_rules' );
    }

}

/**
 * Uninstall function shortcut.
 *
 * @since   1.0.0
 * @version 1.0.0
 */
function dws_qrwc_plugin_uninstall()
{
    
    if ( is_null( dws_qrwc_instance_initialize() ) ) {
        dws_qrwc_instance()->uninstall();
        delete_option( 'rewrite_rules' );
    }

}

add_action( 'fs_after_uninstall_quote-requests-for-woocommerce', 'dws_qrwc_plugin_uninstall' );
// endregion
// region HOOKS
/**
 * Shorthand for generating a plugin-level hook tag.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string              $name       The actual descriptor of the hook's purpose.
 * @param   string|string[]     $extra      Further descriptor of the hook's purpose.
 *
 * @return  string
 */
function dws_qrwc_get_hook_tag( string $name, $extra = array() ) : string
{
    return dws_qrwc_instance()->get_hook_tag( $name, $extra );
}

/**
 * Shorthand for generating a component-level hook tag.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string              $component_id   The ID of the component as defined in the DI container.
 * @param   string              $name           The actual descriptor of the hook's purpose.
 * @param   string|string[]     $extra          Further descriptor of the hook's purpose.
 *
 * @return  string|null
 */
function dws_qrwc_get_component_hook_tag( string $component_id, string $name, $extra = array() ) : ?string
{
    $component = dws_qrwc_component( $component_id );
    if ( is_null( $component ) ) {
        return null;
    }
    if ( !did_action( 'dws_qrwc_initialized' ) ) {
        $component->set_plugin( dws_qrwc_instance() );
    }
    return $component->get_hook_tag( $name, $extra );
}

// endregion
// region CACHING
/**
 * Returns a cache value.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string      $key            The key under which the cached contents are stored.
 * @param   string      $handler_id     The ID of the handler to use.
 *
 * @return  mixed|NotFoundException
 * @noinspection    PhpDocMissingThrowsInspection
 */
function dws_qrwc_get_cache_value( string $key, string $handler_id = 'object' )
{
    /* @noinspection PhpUnhandledExceptionInspection */
    return dws_qrwc_di_container()->get( CachingService::class )->get_value( $key, $handler_id );
}

/**
 * Adds data to the cache. If the key already exists, it overrides the existing data.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $key            The cache key to use for retrieval later.
 * @param   mixed   $data           The contents to store in the cache.
 * @param   int     $expire         When to expire the cache contents, in seconds. Default 0 (no expiration).
 * @param   string  $handler_id     The ID of the handler to use.
 *
 * @return  bool    True on success, false on failure.
 * @noinspection    PhpDocMissingThrowsInspection
 */
function dws_qrwc_set_cache_value(
    string $key,
    $data,
    int $expire = 0,
    string $handler_id = 'object'
) : bool
{
    /* @noinspection PhpUnhandledExceptionInspection */
    return dws_qrwc_di_container()->get( CachingService::class )->set_value(
        $key,
        $data,
        $expire,
        $handler_id
    );
}

/**
 * Removes a value from the cache.
 *
 * @since   1.0.0
 * @version 1.0.0
 *
 * @param   string  $key            The cache key to delete.
 * @param   string  $handler_id     The ID of the handler to use.
 *
 * @return  bool
 * @noinspection    PhpDocMissingThrowsInspection
 */
function dws_qrwc_delete_cache_value( string $key, string $handler_id = 'object' ) : bool
{
    /* @noinspection PhpUnhandledExceptionInspection */
    return dws_qrwc_di_container()->get( CachingService::class )->delete_value( $key, $handler_id );
}

// endregion
// region OTHERS
require_once plugin_dir_path( __FILE__ ) . 'src/functions/quotes.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/requests.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/request-list.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/template.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/account.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/tracking.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/settings.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/product-settings.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/misc.php';
require_once plugin_dir_path( __FILE__ ) . 'src/functions/woocommerce.php';
// endregion