<?php
/**
 * Plugin Name:       LT Process Cards
 * Plugin URI:        https://example.com/lt-process-cards
 * Description:       Custom Elementor widgets for the three-step process card section (Test, Track, Transform).
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.2
 * Author:            Tairan Alam
 * Author URI:        https://example.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       lt-process-cards
 * Domain Path:       /languages
 *
 * Elementor tested up to: 3.25.0
 * Elementor Pro tested up to: 3.25.0
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards;

// Block direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Absolute path to this file. Used by the Plugin class to derive paths and URLs.
 * Scoped to the plugin namespace so it cannot collide with anything else.
 */
const PLUGIN_FILE = __FILE__;

/**
 * Load the autoloader.
 *
 * Prefers Composer's generated autoloader when the package has been installed
 * with `composer install`, and falls back to the bundled PSR-4 autoloader so
 * the plugin also works when distributed as a plain ZIP.
 */
if ( is_readable( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	require_once __DIR__ . '/includes/Autoloader.php';
	Autoloader::register();
}

/**
 * Boot the plugin.
 *
 * Runs on `plugins_loaded` so that Elementor (and any other dependency) has had
 * a chance to load first. Nothing in the plugin executes before this point, so
 * a missing dependency can never produce a fatal error.
 */
add_action(
	'plugins_loaded',
	static function (): void {
		Plugin::instance()->boot();
	}
);
