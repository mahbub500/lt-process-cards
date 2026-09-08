<?php
/**
 * Elementor panel category.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Widgets;

use Elementor\Elements_Manager;
use LT\ProcessCards\Contracts\Registrable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's own category in the Elementor widget panel.
 */
final class Widget_Category implements Registrable {

	/**
	 * Category slug referenced by every widget's get_categories().
	 */
	public const SLUG = 'lt-blocks';

	/**
	 * Hook the category registration.
	 */
	public function register(): void {
		add_action( 'elementor/elements/categories_registered', array( $this, 'register_category' ) );
	}

	/**
	 * Add the category to Elementor's elements manager.
	 *
	 * @param Elements_Manager $elements_manager Elementor elements manager.
	 */
	public function register_category( Elements_Manager $elements_manager ): void {
		$elements_manager->add_category(
			self::SLUG,
			array(
				'title' => esc_html__( 'LT Blocks', 'lt-process-cards' ),
				'icon'  => 'eicon-parallax',
			)
		);
	}
}
