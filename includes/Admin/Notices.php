<?php
/**
 * Admin notices.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Admin;

use LT\ProcessCards\Contracts\Registrable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Renders admin notices.
 *
 * Presentation only. It receives a provider callable rather than a finished
 * array so the messages are built at render time, on `admin_notices`, and never
 * before the text domain is available.
 */
final class Notices implements Registrable {

	/**
	 * Returns the messages to display.
	 *
	 * @var callable(): array<string, string>
	 */
	private $provider;

	/**
	 * Constructor.
	 *
	 * @param callable(): array<string, string> $provider Message provider.
	 */
	public function __construct( callable $provider ) {
		$this->provider = $provider;
	}

	/**
	 * Attach the notice output to the admin.
	 */
	public function register(): void {
		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Output the notices.
	 */
	public function render(): void {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$messages = ( $this->provider )();

		foreach ( $messages as $message ) {
			if ( '' === $message ) {
				continue;
			}

			printf(
				'<div class="notice notice-warning"><p>%s</p></div>',
				esc_html( $message )
			);
		}
	}
}
