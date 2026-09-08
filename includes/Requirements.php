<?php
/**
 * Environment requirement checks.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validates that the runtime environment can support the plugin.
 *
 * Detection and message building are deliberately separate. Detection runs
 * early, during `plugins_loaded`; the translated messages are only built when
 * something actually asks for them, which happens on `admin_notices` - well
 * after the text domain has loaded on `init`. Calling __() any earlier would
 * return untranslated strings and trigger WordPress 6.7's just-in-time
 * translation warning.
 */
final class Requirements {

	/**
	 * Minimum supported PHP version.
	 */
	public const MIN_PHP = '8.2';

	/**
	 * Minimum supported WordPress version.
	 */
	public const MIN_WP = '6.0';

	/**
	 * Minimum supported Elementor version.
	 *
	 * 3.5.0 introduced the `elementor/widgets/register` hook and the modern
	 * widget registration API used by this plugin.
	 */
	public const MIN_ELEMENTOR = '3.5.0';

	/**
	 * Failure codes mapped to their substitution values.
	 *
	 * @var array<string, array<int, string>>
	 */
	private array $codes = array();

	/**
	 * Whether the checks have already run.
	 */
	private bool $checked = false;

	/**
	 * Determine whether every requirement is satisfied.
	 */
	public function are_met(): bool {
		return array() === $this->get_codes();
	}

	/**
	 * Get the raw failure codes. Safe to call at any point in the request.
	 *
	 * @return array<string, array<int, string>>
	 */
	public function get_codes(): array {
		if ( ! $this->checked ) {
			$this->run_checks();
			$this->checked = true;
		}

		return $this->codes;
	}

	/**
	 * Get the unmet requirements as translated messages.
	 *
	 * Only call this after `init`.
	 *
	 * @return array<string, string>
	 */
	public function get_failures(): array {
		$messages = array();

		foreach ( $this->get_codes() as $code => $values ) {
			$messages[ $code ] = $this->message_for( $code, $values );
		}

		return $messages;
	}

	/**
	 * Execute all environment checks.
	 */
	private function run_checks(): void {
		if ( version_compare( PHP_VERSION, self::MIN_PHP, '<' ) ) {
			$this->codes['php'] = array( self::MIN_PHP, PHP_VERSION );
		}

		if ( version_compare( (string) get_bloginfo( 'version' ), self::MIN_WP, '<' ) ) {
			$this->codes['wordpress'] = array( self::MIN_WP );
		}

		if ( ! did_action( 'elementor/loaded' ) ) {
			$this->codes['elementor'] = array();

			return;
		}

		if ( ! defined( 'ELEMENTOR_VERSION' ) || version_compare( (string) ELEMENTOR_VERSION, self::MIN_ELEMENTOR, '<' ) ) {
			$this->codes['elementor_version'] = array( self::MIN_ELEMENTOR );
		}
	}

	/**
	 * Build the translated message for a failure code.
	 *
	 * @param string            $code   Failure code.
	 * @param array<int,string> $values Substitution values.
	 */
	private function message_for( string $code, array $values ): string {
		return match ( $code ) {
			'php' => sprintf(
				/* translators: 1: required PHP version, 2: current PHP version. */
				esc_html__( 'LT Process Cards requires PHP %1$s or greater. You are running PHP %2$s.', 'lt-process-cards' ),
				$values[0] ?? '',
				$values[1] ?? ''
			),
			'wordpress' => sprintf(
				/* translators: %s: required WordPress version. */
				esc_html__( 'LT Process Cards requires WordPress %s or greater. Please update WordPress.', 'lt-process-cards' ),
				$values[0] ?? ''
			),
			'elementor' => esc_html__( 'LT Process Cards requires Elementor to be installed and activated.', 'lt-process-cards' ),
			'elementor_version' => sprintf(
				/* translators: %s: required Elementor version. */
				esc_html__( 'LT Process Cards requires Elementor %s or greater. Please update Elementor.', 'lt-process-cards' ),
				$values[0] ?? ''
			),
			default => '',
		};
	}
}
