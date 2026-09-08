<?php
/**
 * Booking card widget.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Widgets;

use Elementor\Controls_Manager;
use Elementor\Widget_Base;
use LT\ProcessCards\Assets\Assets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Step 01 - "Book a test" card.
 *
 * Skeleton implementation. Controls and the real markup are added in a later
 * phase; for now the widget only proves that registration, categories, assets
 * and rendering are wired correctly.
 */
final class Booking_Card extends Widget_Base {

	/**
	 * Unique widget name used by Elementor internally.
	 */
	public function get_name(): string {
		return 'lt_booking_card';
	}

	/**
	 * Widget label shown in the editor panel.
	 */
	public function get_title(): string {
		return esc_html__( 'LT Booking Card', 'lt-process-cards' );
	}

	/**
	 * Panel icon.
	 */
	public function get_icon(): string {
		return 'eicon-calendar';
	}

	/**
	 * Panel categories this widget belongs to.
	 *
	 * @return array<int, string>
	 */
	public function get_categories(): array {
		return array( Widget_Category::SLUG );
	}

	/**
	 * Search keywords for the editor panel.
	 *
	 * @return array<int, string>
	 */
	public function get_keywords(): array {
		return array( 'lt', 'booking', 'card', 'process', 'appointment', 'date', 'time' );
	}

	/**
	 * Stylesheets Elementor should enqueue when this widget is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( Assets_Manager::STYLE_HANDLE );
	}

	/**
	 * Scripts Elementor should enqueue when this widget is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( Assets_Manager::SCRIPT_HANDLE );
	}

	/**
	 * Register the widget controls.
	 *
	 * Intentionally minimal for now.
	 */
	protected function register_controls(): void {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Content', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'placeholder_notice',
			array(
				'type'            => Controls_Manager::RAW_HTML,
				'raw'             => esc_html__( 'Controls for this widget have not been built yet.', 'lt-process-cards' ),
				'content_classes' => 'elementor-descriptor',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the front end.
	 */
	protected function render(): void {
		?>
		<div class="lt-widget-placeholder">
			<?php echo esc_html__( 'Custom Widget', 'lt-process-cards' ); ?>
		</div>
		<?php
	}
}
