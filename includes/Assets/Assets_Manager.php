<?php
/**
 * Front-end asset registration.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Assets;

use LT\ProcessCards\Contracts\Registrable;
use LT\ProcessCards\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers (but does not enqueue) the plugin's front-end assets.
 *
 * Widgets declare these handles through `get_style_depends()` and
 * `get_script_depends()`, so Elementor enqueues them only on pages that
 * actually contain one of our widgets.
 */
final class Assets_Manager implements Registrable {

	/**
	 * Stylesheet handle.
	 */
	public const STYLE_HANDLE = 'lt-process-cards';

	/**
	 * Script handle.
	 */
	public const SCRIPT_HANDLE = 'lt-process-cards';

	/**
	 * Plugin instance used to resolve URLs and the version string.
	 */
	private Plugin $plugin;

	/**
	 * Constructor.
	 *
	 * @param Plugin $plugin Plugin instance.
	 */
	public function __construct( Plugin $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Hook asset registration.
	 *
	 * Priority 5 guarantees the handles exist before Elementor resolves widget
	 * dependencies. The Elementor editor preview is a front-end context, so this
	 * single hook covers both the live site and the editor canvas.
	 */
	public function register(): void {
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ), 5 );
	}

	/**
	 * Register the stylesheet and script.
	 */
	public function register_assets(): void {
		wp_register_style(
			self::STYLE_HANDLE,
			$this->plugin->url( 'assets/css/lt-process-cards.css' ),
			array(),
			$this->plugin->version()
		);

		wp_register_script(
			self::SCRIPT_HANDLE,
			$this->plugin->url( 'assets/js/lt-process-cards.js' ),
			array( 'jquery' ),
			$this->plugin->version(),
			true
		);
	}
}
