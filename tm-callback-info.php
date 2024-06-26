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

/**
 * Determines if the example closure should be rendered.
 *
 * @param string $key The key to check.
 * @param int    $filter The filter to apply (optional). Defaults to FILTER_UNSAFE_RAW.
 * @return string The rendered output.
 */
function should_render_example_closure( string $key, int $filter = FILTER_UNSAFE_RAW ) {
	$user_input = filter_input( INPUT_GET, $key, $filter );
	if ( isset( $user_input ) ) {
		return strtolower( htmlspecialchars( $user_input, ENT_QUOTES ) );
	} else {
		return '';
	}
}

/**
 * Determines whether the callback info should be shown.
 *
 * This function checks if the callback info should be displayed or not.
 *
 * @return bool Whether the callback info should be shown.
 */
function should_show_callback_info() {
	$query_string_value = filter_input( INPUT_GET, 'show-callback-info', FILTER_UNSAFE_RAW );
	return (
		isset( $query_string_value ) &&
		'true' === strtolower( $query_string_value )
	);
}

/**
 * This code checks if the result of the function `should_render_example_closure`
 * is equal to the string 'true'.
 *
 * If it is, it adds an action to enqueue scripts on the 'wp_enqueue_scripts'
 * hook.
 *
 * The action is an anonymous function that generates HTML output for a fixed
 * positioned div. The div contains a sample message.
 */
if ( 'true' === should_render_example_closure( 'use-sample-anonymous-function' ) ) {
	add_action(
		'wp_enqueue_scripts',
		function () {
			$output  = '<div style="border:1px outset gray; padding: 1em;background:#ccc;position:fixed;bottom:0;left:0;z-index:99; width: 100%;">';
			$output .= 'This is a sample anonymous function.';
			$output .= '</div>';
		},
		0,
		10
	);
}

add_action( 'admin_bar_menu', __NAMESPACE__ . '\\add_admin_bar_menu', 99 );
/**
 * Adds a menu item to the WordPress admin bar.
 *
 * This function is responsible for adding a menu item to the WordPress admin bar.
 * It takes the `$wp_admin_bar` object as a parameter and modifies it to include the new menu item.
 *
 * @param WP_Admin_Bar $wp_admin_bar The WordPress admin bar object.
 */
function add_admin_bar_menu( $wp_admin_bar ) {
	if ( is_admin() ) {
		return;
	}

	$wp_admin_bar->add_menu(
		array(
			'id'    => 'callback-info',
			'title' => __( 'Callback Info', 'tm-callback-info' ),
			'href'  => add_query_arg( 'show-callback-info', 'true' ),
		)
	);
}

add_action( 'wp_head', __NAMESPACE__ . '\\list_registered_functions', 1000 );
/**
 * Lists all the registered functions.
 *
 * @return void
 */
function list_registered_functions() {
	if ( ! should_show_callback_info() ) {
		return;
	}

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

	$output  = "\n\tObject Method\n";
	$output .= "\tClass: {$reflection->class}\n";
	$output .= "\tMethod: {$reflection->name}\n";
	$output .= "\tFilename: {$reflection->getFileName()}\n";
	$output .= "\tStart line: {$reflection->getStartLine()}\n";
	$output .= "\tEnd line: {$reflection->getEndLine()}\n";

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

	$output  = "\n\tStatic Method\n";
	$output .= "\tClass: {$reflection->class}\n";
	$output .= "\tMethod: {$reflection->name}\n";
	$output .= "\tFilename: {$reflection->getFileName()}\n";
	$output .= "\tStart line: {$reflection->getStartLine()}\n";
	$output .= "\tEnd line: {$reflection->getEndLine()}\n";

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

	$output  = "\n\tAnonymous Function\n";
	$output .= "\tFilename: {$reflection->getFileName()}\n";
	$output .= "\tStart line: {$reflection->getStartLine()}\n";
	$output .= "\tEnd line: {$reflection->getEndLine()}\n";

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

	$output  = "\n\tFunction\n";
	$output .= "\tFilename: {$reflection->getFileName()}\n";
	$output .= "\tStart line: {$reflection->getStartLine()}\n";
	$output .= "\tEnd line: {$reflection->getEndLine()}\n";

	return $output;
}
