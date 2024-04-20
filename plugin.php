<?php
/**
 * Plugin Name: Callback Info
 * Plugin URI: https://github.com/tommcfarlin/tm-callback-info
 * Description: Render contextual information about every function registered with all WordPress hooks.
 * Requires at least: 6.5
 * Requires PHP: 7.4.33
 * Author: Tom McFarlin
 * Author URI: https://tommcfarlin.com
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Version: 1.0.0
 *
 * @package TmCallbackInfo
 * @version 1.0.0
 * @since   1.0.0
 * @link    https://github.com/tommcfarlin/tm-callback-info
 * @license GPL-3.0-or-later
 */

namespace TmCallbackInfo;

use Closure;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionException;

defined( 'WPINC' ) || die;

// Pass `show-callback-info=true` to the query string to render callback information.
if ( 'true' !== filter_input( INPUT_GET, 'show-callback-info', FILTER_SANITIZE_STRING ) ) {
	return;
}

// Pass `use-sample-anonymous-function=true` to the query string to render a sample closure.
if ( 'true' === filter_input( INPUT_GET, 'use-sample-anonymous-function', FILTER_SANITIZE_STRING ) ) {
	add_action(
		'wp_enqueue_scripts',
		function () {
			echo <<<HTML
			<div style="border:1px outset gray; padding: 1em;background:#ccc;position:fixed;bottom:0;left:0;z-index:99; width: 100%;">
				This is a a sample anonymous function.
		</div>
		HTML;
		},
		0,
		10
	);
}

add_action( 'wp_head', __NAMESPACE__ . '\\list_registered_functions', 1000 );
/**
 * Lists all the registered functions.
 *
 * @return void
 */
function list_registered_functions() {
	global $wp_filter;

	foreach ( $wp_filter as $hook_name => $hook_object ) {
		echo '<pre>';
		echo esc_html( "Hook: $hook_name\n" );
		foreach ( $hook_object->callbacks as $priority => $callbacks ) {
			foreach ( $callbacks as $callback ) {
				if ( is_array( $callback['function'] ) ) {
					echo ( is_object( $callback['function'][0] ) ) ?
						esc_html( get_object_method_information( $callback ) ) :
						esc_html( get_static_method_information( $callback ) );
				} else {
					echo ( $callback['function'] instanceof Closure ) ?
						esc_html( get_anonymous_function_information( $callback ) ) :
						esc_html( get_function_information( $callback ) );
				}
			}
		}
		echo '</pre>';
	}

	echo esc_html( $output );
}

/**
 * Retrieves information about a given object method.
 *
 * @param Callable $callback The callback function or method.
 *
 * @return string $output The filename of the function, the start line, and the end line.
 */
function get_object_method_information( $callback ) {
	try {
		$reflection = new ReflectionMethod(
			$callback['function'][0],
			$callback['function'][1]
		);
	} catch ( ReflectionException $e ) {
		return "\n\t" . $e->getMessage() . "\n";
	}

	$output = <<<METHOD_INFO
		\n\tObject Method\n
		\tClass: {$reflection->class}
		\tMethod: {$reflection->name}
		\tFilename: {$reflection->getFileName()}
		\tStart line: {$reflection->getStartLine()}
		\tEnd line: {$reflection->getEndLine()}
	METHOD_INFO;

	return $output;
}


/**
 * Retrieves information about a static method callback.
 *
 * @param Callable $callback The static method callback.
 *
 * @return string $output The filename of the function, the start line, and the end line.
 */
function get_static_method_information( $callback ) {
	try {
		$reflection = new ReflectionMethod(
			$callback['function'][0],
			$callback['function'][1]
		);
	} catch ( ReflectionException $e ) {
		return "\n\t" . $e->getMessage() . "\n";
	}

	$output = <<<METHOD_INFO
		\n\tStatic Method\n
		\tClass: {$reflection->class}
		\tMethod: {$reflection->name}
		\tFilename: {$reflection->getFileName()}
		\tStart line: {$reflection->getStartLine()}
		\tEnd line: {$reflection->getEndLine()}
	METHOD_INFO;

	return $output;
}

/**
 * Renders information about an anonymous function.
 *
 * @param Callable $callback The anonymous function for which to render information.
 *
 * @return string $output The filename of the function, the start line, and the end line.
 */
function get_anonymous_function_information( $callback ) {
	$reflection = new ReflectionFunction( $callback['function'] );

	$output = <<<FUNCTION_INFO
		\n\tAnonymous Function\n
		\tFilename: {$reflection->getFileName()}
		\tStart line: {$reflection->getStartLine()}
		\tEnd line: {$reflection->getEndLine()}
	FUNCTION_INFO;

	return $output;
}

/**
 * Retrieves information about a given function.
 *
 * @param Callable $callback The callback function.
 *
 * @return string $output The filename of the function, the start line, and the end line.
 */
function get_function_information( $callback ) {
	try {
		$reflection = new ReflectionFunction( $callback['function'] );
	} catch ( ReflectionException $e ) {
		return "\n\t" . $e->getMessage() . "\n";
	}

	$output = <<<FUNCTION_INFO
		\n\tFunction\n
		\tFilename: {$reflection->getFileName()}
		\tStart line: {$reflection->getStartLine()}
		\tEnd line: {$reflection->getEndLine()}
	FUNCTION_INFO;

	return $output;
}
