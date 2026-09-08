<?php
/**
 * Uninstall routine.
 *
 * Runs when the plugin is deleted from the WordPress admin. The plugin does not
 * currently create options, tables, post meta or scheduled events, so there is
 * nothing to remove. The file exists so that cleanup has a defined home when
 * persistent data is introduced.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
