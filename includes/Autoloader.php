<?php
/**
 * Bundled PSR-4 autoloader.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal PSR-4 autoloader for the LT\ProcessCards namespace.
 *
 * Used only when Composer's autoloader is not present, so the plugin can be
 * shipped as a plain ZIP without a vendor directory.
 */
final class Autoloader {

	/**
	 * Namespace prefix handled by this autoloader.
	 */
	private const PREFIX = __NAMESPACE__ . '\\';

	/**
	 * Register the autoloader with SPL.
	 */
	public static function register(): void {
		spl_autoload_register( array( self::class, 'load' ) );
	}

	/**
	 * Resolve a fully qualified class name to a file and require it.
	 *
	 * @param string $class_name Fully qualified class name.
	 */
	public static function load( string $class_name ): void {
		if ( ! str_starts_with( $class_name, self::PREFIX ) ) {
			return;
		}

		$relative = substr( $class_name, strlen( self::PREFIX ) );
		$path     = __DIR__ . '/' . str_replace( '\\', '/', $relative ) . '.php';

		// Guard against path traversal via a malformed class name.
		$real_path = realpath( $path );
		$base_path = realpath( __DIR__ );

		if ( false === $real_path || false === $base_path || ! str_starts_with( $real_path, $base_path ) ) {
			return;
		}

		require_once $real_path;
	}
}
