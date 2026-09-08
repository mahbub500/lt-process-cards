<?php
/**
 * Plugin container and bootstrapper.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards;

use LT\ProcessCards\Admin\Notices;
use LT\ProcessCards\Assets\Assets_Manager;
use LT\ProcessCards\Contracts\Registrable;
use LT\ProcessCards\Widgets\Widget_Category;
use LT\ProcessCards\Widgets\Widgets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Central plugin object.
 *
 * Owns the plugin's paths, version and text domain, and wires the individual
 * service classes together. It performs no rendering, no registration logic and
 * no requirement checking of its own; each of those lives in its own class.
 */
final class Plugin {

	/**
	 * Plugin version. Also used for asset cache-busting.
	 */
	public const VERSION = '1.0.0';

	/**
	 * Translation text domain.
	 */
	public const TEXT_DOMAIN = 'lt-process-cards';

	/**
	 * Sole instance.
	 */
	private static ?self $instance = null;

	/**
	 * Absolute path to the plugin directory, with a trailing slash.
	 */
	private string $dir;

	/**
	 * URL to the plugin directory, with a trailing slash.
	 */
	private string $url;

	/**
	 * Absolute path to the main plugin file.
	 */
	private string $file;

	/**
	 * Whether boot() has already run.
	 */
	private bool $booted = false;

	/**
	 * Retrieve the single instance.
	 */
	public static function instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Private constructor. Use instance().
	 */
	private function __construct() {
		$this->file = PLUGIN_FILE;
		$this->dir  = plugin_dir_path( PLUGIN_FILE );
		$this->url  = plugin_dir_url( PLUGIN_FILE );
	}

	/**
	 * Prevent cloning of the instance.
	 */
	private function __clone() {}

	/**
	 * Prevent unserialization of the instance.
	 *
	 * @throws \LogicException Always.
	 */
	public function __wakeup(): void {
		throw new \LogicException( 'Cannot unserialize a singleton.' );
	}

	/**
	 * Start the plugin.
	 *
	 * If any requirement is unmet, the plugin registers an admin notice and
	 * stops. No Elementor class is ever touched in that path, so a missing or
	 * outdated Elementor cannot cause a fatal error.
	 */
	public function boot(): void {
		if ( $this->booted ) {
			return;
		}

		$this->booted = true;

		add_action( 'init', array( $this, 'load_textdomain' ) );

		$requirements = new Requirements();

		if ( ! $requirements->are_met() ) {
			$notices = new Notices(
				static fn (): array => $requirements->get_failures()
			);

			$notices->register();

			return;
		}

		$this->register_services();
	}

	/**
	 * Load the plugin translations.
	 *
	 * Hooked to `init` rather than `plugins_loaded` to satisfy the load order
	 * WordPress 6.7 expects for text domains.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			self::TEXT_DOMAIN,
			false,
			dirname( plugin_basename( $this->file ) ) . '/languages'
		);
	}

	/**
	 * Instantiate and register the plugin's services.
	 */
	private function register_services(): void {
		/**
		 * Services to boot.
		 *
		 * @var array<int, Registrable> $services
		 */
		$services = array(
			new Assets_Manager( $this ),
			new Widget_Category(),
			new Widgets_Manager(),
		);

		foreach ( $services as $service ) {
			$service->register();
		}
	}

	/**
	 * Get the plugin version.
	 */
	public function version(): string {
		return self::VERSION;
	}

	/**
	 * Build an absolute path inside the plugin directory.
	 *
	 * @param string $relative_path Path relative to the plugin root.
	 */
	public function path( string $relative_path = '' ): string {
		return $this->dir . ltrim( $relative_path, '/' );
	}

	/**
	 * Build a URL to a file inside the plugin directory.
	 *
	 * @param string $relative_path Path relative to the plugin root.
	 */
	public function url( string $relative_path = '' ): string {
		return $this->url . ltrim( $relative_path, '/' );
	}

	/**
	 * Get the absolute path to the main plugin file.
	 */
	public function file(): string {
		return $this->file;
	}
}
