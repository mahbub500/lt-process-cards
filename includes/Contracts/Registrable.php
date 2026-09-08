<?php
/**
 * Registrable contract.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Contracts;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Contract for any service that attaches itself to WordPress hooks.
 *
 * Lets Plugin iterate over a collection of services without knowing what any of
 * them actually do.
 */
interface Registrable {

	/**
	 * Attach the service's hooks.
	 */
	public function register(): void;
}
