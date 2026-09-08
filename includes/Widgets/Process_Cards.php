<?php
/**
 * Process cards widget.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Widgets;

use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Base;
use LT\ProcessCards\Assets\Assets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The full three-step "Test / Track / Transform" row as a single widget.
 *
 * Reproduces the original markup as one unit: a `.lt-cards-row` flex wrapper
 * containing the three `.lt-card` panels (booking, results, plan). Content
 * controls are grouped into three sections, one per card, with control IDs
 * prefixed `card1_`, `card2_`, `card3_` to keep them from colliding.
 */
final class Process_Cards extends Widget_Base {

	/**
	 * Fixed, developer-authored SVG icons for the plan list.
	 *
	 * @var array<string, string>
	 */
	private const PLAN_ICONS = array(
		'nutrition'   => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 12a8 8 0 0 0 16 0H4Z" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 12V5" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/><path d="M9 7c0-1.5 1-3 3-3s3 1.5 3 3" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>',
		'supplements' => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><rect x="4.5" y="8.5" width="15" height="7" rx="3.5" transform="rotate(-45 12 12)" stroke="#fff" stroke-width="1.6"/><path d="M9.5 14.5 14.5 9.5" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>',
		'activity'    => '<svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M3 12h3.5l1.8-4 3 8 1.8-4H21" stroke="#fff" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg>',
	);

	/**
	 * Unique widget name used by Elementor internally.
	 */
	public function get_name(): string {
		return 'lt_process_cards';
	}

	/**
	 * Widget label shown in the editor panel.
	 */
	public function get_title(): string {
		return esc_html__( 'LT Process Cards', 'lt-process-cards' );
	}

	/**
	 * Panel icon.
	 */
	public function get_icon(): string {
		return 'eicon-gallery-grid';
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
		return array( 'lt', 'process', 'card', 'booking', 'results', 'plan', 'test', 'track', 'transform' );
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
	 */
	protected function register_controls(): void {
		$this->register_card1_booking_controls();
		$this->register_card2_results_controls();
		$this->register_card3_plan_controls();
	}

	/**
	 * Card 1 - booking (dates & times) controls.
	 */
	private function register_card1_booking_controls(): void {
		$this->start_controls_section(
			'section_card1',
			array(
				'label' => esc_html__( 'Card 1: Booking', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'card1_step_label',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Eyebrow label', 'lt-process-cards' ),
				'default' => esc_html__( '01. TEST', 'lt-process-cards' ),
			)
		);

		$this->add_control(
			'card1_title',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Title', 'lt-process-cards' ),
				'default' => esc_html__( '200+ advanced diagnostics', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$this->add_control(
			'card1_subtitle',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Subtitle', 'lt-process-cards' ),
				'default' => esc_html__( 'Test at home or at clinic locations.', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$dates_repeater = new Repeater();

		$dates_repeater->add_control(
			'day_label',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Day abbreviation', 'lt-process-cards' ),
				'default' => 'Mon',
			)
		);

		$dates_repeater->add_control(
			'day_number',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Displayed day number', 'lt-process-cards' ),
				'default' => '1',
			)
		);

		$dates_repeater->add_control(
			'data_num',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Calendar date (data-num attribute)', 'lt-process-cards' ),
				'default' => '1',
			)
		);

		$dates_repeater->add_control(
			'state',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'State', 'lt-process-cards' ),
				'options' => array(
					'normal'   => esc_html__( 'Normal', 'lt-process-cards' ),
					'selected' => esc_html__( 'Selected', 'lt-process-cards' ),
					'peek'     => esc_html__( 'Peek (disabled preview)', 'lt-process-cards' ),
				),
				'default' => 'normal',
			)
		);

		$this->add_control(
			'card1_dates',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $dates_repeater->get_controls(),
				'title_field' => '{{{ day_label }}} {{{ day_number }}}',
				'default'     => array(
					array(
						'day_label'  => 'Tue',
						'day_number' => '1',
						'data_num'   => '5',
						'state'      => 'normal',
					),
					array(
						'day_label'  => 'Wed',
						'day_number' => '2',
						'data_num'   => '6',
						'state'      => 'normal',
					),
					array(
						'day_label'  => 'Thu',
						'day_number' => '3',
						'data_num'   => '7',
						'state'      => 'selected',
					),
					array(
						'day_label'  => 'Fri',
						'day_number' => '4',
						'data_num'   => '8',
						'state'      => 'normal',
					),
					array(
						'day_label'  => 'Sat',
						'day_number' => '5',
						'data_num'   => '9',
						'state'      => 'normal',
					),
					array(
						'day_label'  => 'Sun',
						'day_number' => '6',
						'data_num'   => '10',
						'state'      => 'peek',
					),
				),
			)
		);

		$times_repeater = new Repeater();

		$times_repeater->add_control(
			'time_label',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Time', 'lt-process-cards' ),
				'default' => '9:00',
			)
		);

		$times_repeater->add_control(
			'state',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'State', 'lt-process-cards' ),
				'options' => array(
					'normal'   => esc_html__( 'Normal', 'lt-process-cards' ),
					'selected' => esc_html__( 'Selected', 'lt-process-cards' ),
					'disabled' => esc_html__( 'Disabled', 'lt-process-cards' ),
				),
				'default' => 'normal',
			)
		);

		$this->add_control(
			'card1_times',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $times_repeater->get_controls(),
				'title_field' => '{{{ time_label }}}',
				'default'     => array(
					array(
						'time_label' => '8:30',
						'state'      => 'disabled',
					),
					array(
						'time_label' => '9:00',
						'state'      => 'selected',
					),
					array(
						'time_label' => '9:30',
						'state'      => 'normal',
					),
					array(
						'time_label' => '10:00',
						'state'      => 'normal',
					),
					array(
						'time_label' => '10:30',
						'state'      => 'normal',
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card 2 - results (range chart) controls.
	 */
	private function register_card2_results_controls(): void {
		$this->start_controls_section(
			'section_card2',
			array(
				'label' => esc_html__( 'Card 2: Results', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'card2_step_label',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Eyebrow label', 'lt-process-cards' ),
				'default' => esc_html__( '02. TRACK', 'lt-process-cards' ),
			)
		);

		$this->add_control(
			'card2_title',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Title', 'lt-process-cards' ),
				'default' => esc_html__( 'Know your results', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$this->add_control(
			'card2_subtitle',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Subtitle', 'lt-process-cards' ),
				'default' => esc_html__( 'Explained by top doctors, scientists, coaches', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$this->add_control(
			'card2_label_above',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Above-range zone label', 'lt-process-cards' ),
				'default' => esc_html__( 'ABOVE RANGE', 'lt-process-cards' ),
			)
		);

		$this->add_control(
			'card2_label_in_range',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'In-range zone label', 'lt-process-cards' ),
				'default' => esc_html__( 'IN RANGE', 'lt-process-cards' ),
			)
		);

		$this->add_control(
			'card2_label_below',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Below-range zone label', 'lt-process-cards' ),
				'default' => esc_html__( 'BELOW RANGE', 'lt-process-cards' ),
			)
		);

		$points_repeater = new Repeater();

		$points_repeater->add_control(
			'point_value',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Value label', 'lt-process-cards' ),
				'default' => '0.0',
			)
		);

		$points_repeater->add_control(
			'point_x',
			array(
				'type'    => Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Point X (0-260)', 'lt-process-cards' ),
				'default' => 110,
			)
		);

		$points_repeater->add_control(
			'point_y',
			array(
				'type'    => Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Point Y (0-145)', 'lt-process-cards' ),
				'default' => 44,
			)
		);

		$points_repeater->add_control(
			'point_label_y',
			array(
				'type'    => Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Value label Y', 'lt-process-cards' ),
				'default' => 32,
			)
		);

		$this->add_control(
			'card2_chart_points',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $points_repeater->get_controls(),
				'title_field' => '{{{ point_value }}}',
				'default'     => array(
					array(
						'point_value'   => '16.7',
						'point_x'       => 110,
						'point_y'       => 44,
						'point_label_y' => 32,
					),
					array(
						'point_value'   => '10.0',
						'point_x'       => 168,
						'point_y'       => 94,
						'point_label_y' => 110,
					),
					array(
						'point_value'   => '12.5',
						'point_x'       => 224,
						'point_y'       => 82,
						'point_label_y' => 72,
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Card 3 - plan (icon list) controls.
	 */
	private function register_card3_plan_controls(): void {
		$this->start_controls_section(
			'section_card3',
			array(
				'label' => esc_html__( 'Card 3: Plan', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'card3_step_label',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Eyebrow label', 'lt-process-cards' ),
				'default' => esc_html__( '03. TRANSFORM', 'lt-process-cards' ),
			)
		);

		$this->add_control(
			'card3_title',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Title', 'lt-process-cards' ),
				'default' => esc_html__( 'Follow your plan', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$this->add_control(
			'card3_subtitle',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Subtitle', 'lt-process-cards' ),
				'default' => esc_html__( 'Take action. Re-test. Adjust plan. Repeat.', 'lt-process-cards' ),
				'rows'    => 2,
			)
		);

		$plan_repeater = new Repeater();

		$plan_repeater->add_control(
			'icon_key',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Icon', 'lt-process-cards' ),
				'options' => array(
					'nutrition'   => esc_html__( 'Nutrition', 'lt-process-cards' ),
					'supplements' => esc_html__( 'Supplements', 'lt-process-cards' ),
					'activity'    => esc_html__( 'Activity', 'lt-process-cards' ),
				),
				'default' => 'nutrition',
			)
		);

		$plan_repeater->add_control(
			'item_title',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Item title', 'lt-process-cards' ),
				'default' => esc_html__( 'Nutrition', 'lt-process-cards' ),
			)
		);

		$plan_repeater->add_control(
			'item_desc',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Item description', 'lt-process-cards' ),
				'default' => '',
			)
		);

		$this->add_control(
			'card3_plan_items',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $plan_repeater->get_controls(),
				'title_field' => '{{{ item_title }}}',
				'default'     => array(
					array(
						'icon_key'   => 'nutrition',
						'item_title' => esc_html__( 'Nutrition', 'lt-process-cards' ),
						'item_desc'  => esc_html__( 'Grilled chicken, quinoa, broccoli sprout…', 'lt-process-cards' ),
					),
					array(
						'icon_key'   => 'supplements',
						'item_title' => esc_html__( 'Supplements', 'lt-process-cards' ),
						'item_desc'  => esc_html__( 'Vit. D3+K2 2000 IU daily, 100mg CoQ10…', 'lt-process-cards' ),
					),
					array(
						'icon_key'   => 'activity',
						'item_title' => esc_html__( 'Activity', 'lt-process-cards' ),
						'item_desc'  => esc_html__( 'Stretching, strength training, sleep…', 'lt-process-cards' ),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render the widget output on the front end.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		?>
		<div class="lt-cards-row">
			<?php $this->render_booking_card( $settings ); ?>
			<?php $this->render_results_card( $settings ); ?>
			<?php $this->render_plan_card( $settings ); ?>
		</div>
		<?php
	}

	/**
	 * Render Card 1 - booking.
	 *
	 * @param array<string, mixed> $settings Widget settings for display.
	 */
	private function render_booking_card( array $settings ): void {
		$dates = is_array( $settings['card1_dates'] ?? null ) ? $settings['card1_dates'] : array();
		$times = is_array( $settings['card1_times'] ?? null ) ? $settings['card1_times'] : array();
		?>
		<div class="lt-card lt-process-card lt-process-card--booking">
			<p class="lt-num"><?php echo esc_html( (string) $settings['card1_step_label'] ); ?></p>
			<h3 class="lt-title"><?php echo esc_html( (string) $settings['card1_title'] ); ?></h3>
			<p class="lt-sub"><?php echo esc_html( (string) $settings['card1_subtitle'] ); ?></p>

			<div class="lt-dates-wrap">
				<div class="lt-dates" role="tablist" aria-label="<?php esc_attr_e( 'Select a date', 'lt-process-cards' ); ?>">
					<?php foreach ( $dates as $date ) : ?>
						<?php
						$state       = (string) ( $date['state'] ?? 'normal' );
						$is_selected = 'selected' === $state;
						$is_peek     = 'peek' === $state;

						$classes = array( 'lt-date' );
						if ( $is_selected ) {
							$classes[] = 'is-selected';
						}
						if ( $is_peek ) {
							$classes[] = 'lt-date-peek';
						}
						?>
						<div
							class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
							role="button"
							tabindex="<?php echo esc_attr( $is_peek ? '-1' : '0' ); ?>"
							data-day="<?php echo esc_attr( (string) ( $date['day_label'] ?? '' ) ); ?>"
							data-num="<?php echo esc_attr( (string) ( $date['data_num'] ?? '' ) ); ?>"
						>
							<span class="lt-day"><?php echo esc_html( (string) ( $date['day_label'] ?? '' ) ); ?></span><span class="lt-day-num"><?php echo esc_html( (string) ( $date['day_number'] ?? '' ) ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="lt-times-wrap">
				<div class="lt-times" role="tablist" aria-label="<?php esc_attr_e( 'Select a time', 'lt-process-cards' ); ?>">
					<?php foreach ( $times as $time ) : ?>
						<?php
						$state       = (string) ( $time['state'] ?? 'normal' );
						$is_selected = 'selected' === $state;
						$is_disabled = 'disabled' === $state;

						$classes = array( 'lt-time' );
						if ( $is_selected ) {
							$classes[] = 'is-selected';
						}
						if ( $is_disabled ) {
							$classes[] = 'is-disabled';
						}
						?>
						<?php if ( $is_disabled ) : ?>
						<div
							class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
							role="button"
							aria-disabled="true"
						>
							<?php echo esc_html( (string) ( $time['time_label'] ?? '' ) ); ?>
						</div>
						<?php else : ?>
						<div
							class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
							role="button"
							tabindex="0"
						>
							<?php echo esc_html( (string) ( $time['time_label'] ?? '' ) ); ?>
						</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Card 2 - results.
	 *
	 * @param array<string, mixed> $settings Widget settings for display.
	 */
	private function render_results_card( array $settings ): void {
		$points = is_array( $settings['card2_chart_points'] ?? null ) ? $settings['card2_chart_points'] : array();
		?>
		<div class="lt-card lt-process-card lt-process-card--results">
			<p class="lt-num"><?php echo esc_html( (string) $settings['card2_step_label'] ); ?></p>
			<h3 class="lt-title"><?php echo esc_html( (string) $settings['card2_title'] ); ?></h3>
			<p class="lt-sub"><?php echo esc_html( (string) $settings['card2_subtitle'] ); ?></p>

			<div class="lt-chart">
				<svg viewBox="0 0 260 145" class="lt-chart-svg" preserveAspectRatio="xMinYMid meet" role="img" aria-label="<?php echo esc_attr( (string) $settings['card2_title'] ); ?>">
					<rect x="4" y="18" width="9" height="30" rx="3" fill="#EDE3D3" stroke="#E7DDCC" stroke-width="1"></rect>
					<rect x="4" y="52" width="9" height="52" rx="3" fill="#AD9771"></rect>
					<rect x="4" y="108" width="9" height="30" rx="3" fill="#EDE3D3" stroke="#E7DDCC" stroke-width="1"></rect>

					<text x="26" y="36" class="lt-chart-zone"><?php echo esc_html( (string) $settings['card2_label_above'] ); ?></text>
					<text x="26" y="81" class="lt-chart-zone"><?php echo esc_html( (string) $settings['card2_label_in_range'] ); ?></text>
					<text x="26" y="126" class="lt-chart-zone"><?php echo esc_html( (string) $settings['card2_label_below'] ); ?></text>

					<?php if ( ! empty( $points ) ) : ?>
						<?php
						$polyline_points = array();
						foreach ( $points as $point ) {
							$polyline_points[] = absint( $point['point_x'] ?? 0 ) . ',' . absint( $point['point_y'] ?? 0 );
						}
						?>
						<polyline points="<?php echo esc_attr( implode( ' ', $polyline_points ) ); ?>" fill="none" stroke="#C08457" stroke-width="1.5"></polyline>

						<?php foreach ( $points as $point ) : ?>
							<circle cx="<?php echo absint( $point['point_x'] ?? 0 ); ?>" cy="<?php echo absint( $point['point_y'] ?? 0 ); ?>" r="4.5" fill="#C08457"></circle>
						<?php endforeach; ?>

						<?php foreach ( $points as $point ) : ?>
							<text x="<?php echo absint( $point['point_x'] ?? 0 ); ?>" y="<?php echo absint( $point['point_label_y'] ?? 0 ); ?>" text-anchor="middle" class="lt-chart-value"><?php echo esc_html( (string) ( $point['point_value'] ?? '' ) ); ?></text>
						<?php endforeach; ?>
					<?php endif; ?>
				</svg>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Card 3 - plan.
	 *
	 * @param array<string, mixed> $settings Widget settings for display.
	 */
	private function render_plan_card( array $settings ): void {
		$plan_items = is_array( $settings['card3_plan_items'] ?? null ) ? $settings['card3_plan_items'] : array();
		$icon_keys  = array_keys( self::PLAN_ICONS );
		?>
		<div class="lt-card lt-process-card lt-process-card--plan">
			<p class="lt-num"><?php echo esc_html( (string) $settings['card3_step_label'] ); ?></p>
			<h3 class="lt-title"><?php echo esc_html( (string) $settings['card3_title'] ); ?></h3>
			<p class="lt-sub"><?php echo esc_html( (string) $settings['card3_subtitle'] ); ?></p>

			<ul class="lt-plan-list">
				<?php foreach ( $plan_items as $item ) : ?>
					<?php
					$icon_key = (string) ( $item['icon_key'] ?? 'nutrition' );
					if ( ! in_array( $icon_key, $icon_keys, true ) ) {
						$icon_key = 'nutrition';
					}
					?>
					<li class="lt-plan-item">
						<span class="lt-plan-icon" aria-hidden="true">
							<?php echo self::PLAN_ICONS[ $icon_key ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- fixed, developer-authored SVG chosen from a closed enum; no user-supplied markup reaches this string. ?>
						</span>
						<span class="lt-plan-text">
							<span class="lt-plan-title"><?php echo esc_html( (string) ( $item['item_title'] ?? '' ) ); ?></span>
							<span class="lt-plan-desc"><?php echo esc_html( (string) ( $item['item_desc'] ?? '' ) ); ?></span>
						</span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php
	}
}
