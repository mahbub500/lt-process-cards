<?php
/**
 * Widget registration.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Widgets;

use Elementor\Widget_Base;
use Elementor\Widgets_Manager as Elementor_Widgets_Manager;
use LT\ProcessCards\Contracts\Registrable;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers the plugin's widgets with Elementor.
 *
 * New widgets are added to the class list below. Nothing else in the plugin
 * needs to change, which keeps registration closed for modification and open
 * for extension.
 */
final class Widgets_Manager implements Registrable {

	/**
	 * Fully qualified widget class names to register.
	 *
	 * @var array<int, class-string<Widget_Base>>
	 */
	private const WIDGETS = array(
		Process_Cards::class,
		Image_Text_Slider::class,
	);

	/**
	 * Hook widget registration.
	 */
	public function register(): void {
		add_action( 'elementor/widgets/register', array( $this, 'register_widgets' ) );
	}

	/**
	 * Instantiate and register each widget.
	 *
	 * @param Elementor_Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_widgets( Elementor_Widgets_Manager $widgets_manager ): void {
		foreach ( self::WIDGETS as $widget_class ) {
			if ( ! class_exists( $widget_class ) ) {
				continue;
			}

			$widget = new $widget_class();

			if ( ! $widget instanceof Widget_Base ) {
				continue;
			}

			$widgets_manager->register( $widget );
		}
	}
}
