<?php
/**
 * Image + text slider widget.
 *
 * @package LT\ProcessCards
 */

declare( strict_types = 1 );

namespace LT\ProcessCards\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use Elementor\Repeater;
use Elementor\Utils;
use Elementor\Widget_Base;
use LT\ProcessCards\Assets\Assets_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A responsive, repeater-driven image + text slider (one slide = one image
 * with a badge/title/description overlay, optionally linked).
 *
 * Content tab: `register_slides_controls()` holds the `slides` Repeater
 * (image, badge icon/text, title, description, link, active flag);
 * `register_settings_controls()` holds every behavioural option (slides to
 * show/scroll — responsive, transition effect/speed, autoplay, loop, drag,
 * arrows, dots).
 *
 * Style tab is one `register_style_*_controls()` per visual concern: layout
 * (gap/width/height/vertical align), image (height/fit/radius/overlay), badge,
 * title, description, the active-slide highlight, the card box
 * (background/radius/padding/border/shadow), arrows and dots.
 *
 * `render()` prints one data-attribute per behavioural setting on the
 * `.lt-image-text-slider` wrapper; `assets/js/lt-image-text-slider.js` reads
 * those attributes to drive the actual slide mechanics (translate-based
 * slide, or opacity-based fade), so no behavioural setting requires a PHP
 * loop over slide "pages" — the JS computes visible-slide math itself from
 * the viewport width and the CSS `--lt-its-gap` custom property written by
 * the Layout style section.
 */
final class Image_Text_Slider extends Widget_Base {

	/**
	 * Unique widget name used by Elementor internally.
	 */
	public function get_name(): string {
		return 'lt_image_text_slider';
	}

	/**
	 * Widget label shown in the editor panel.
	 */
	public function get_title(): string {
		return esc_html__( 'LT Image Text Slider', 'lt-process-cards' );
	}

	/**
	 * Panel icon.
	 */
	public function get_icon(): string {
		return 'eicon-slider-push';
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
		return array( 'lt', 'slider', 'carousel', 'image', 'text', 'gallery', 'cards' );
	}

	/**
	 * Stylesheets Elementor should enqueue when this widget is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_style_depends(): array {
		return array( Assets_Manager::SLIDER_STYLE_HANDLE );
	}

	/**
	 * Scripts Elementor should enqueue when this widget is on the page.
	 *
	 * @return array<int, string>
	 */
	public function get_script_depends(): array {
		return array( Assets_Manager::SLIDER_SCRIPT_HANDLE );
	}

	/**
	 * Register the widget controls.
	 */
	protected function register_controls(): void {
		$this->register_slides_controls();
		$this->register_settings_controls();
		$this->register_detail_panel_controls();
		$this->register_style_layout_controls();
		$this->register_style_image_controls();
		$this->register_style_badge_controls();
		$this->register_style_title_controls();
		$this->register_style_active_controls();
		$this->register_style_box_controls();
		$this->register_style_arrows_controls();
		$this->register_style_dots_controls();
		$this->register_style_detail_controls();
	}

	/**
	 * Content tab — the slides repeater (image + text + link).
	 */
	private function register_slides_controls(): void {
		$this->start_controls_section(
			'section_slides',
			array(
				'label' => esc_html__( 'Slides', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'slide_image',
			array(
				'type'    => Controls_Manager::MEDIA,
				'label'   => esc_html__( 'Image', 'lt-process-cards' ),
				'default' => array(
					'url' => Utils::get_placeholder_image_src(),
				),
			)
		);

		$repeater->add_group_control(
			Group_Control_Image_Size::get_type(),
			array(
				'name'    => 'slide_image',
				'label'   => esc_html__( 'Detail Panel Image Size', 'lt-process-cards' ),
				'default' => 'medium_large',
			)
		);

		$repeater->add_control(
			'slide_thumb_size',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => esc_html__( 'Slider Thumbnail Size', 'lt-process-cards' ),
				'description' => esc_html__( 'The slider card is small, so it loads a cropped thumbnail instead of the full image above.', 'lt-process-cards' ),
				'options'     => $this->get_image_size_options(),
				'default'     => 'thumbnail',
			)
		);

		$repeater->add_control(
			'slide_image_position',
			array(
				'type'        => Controls_Manager::SELECT,
				'label'       => esc_html__( 'Thumbnail Focal Position', 'lt-process-cards' ),
				'description' => esc_html__( 'Which part of the image stays visible once it is cropped to fit the small slider card.', 'lt-process-cards' ),
				'options'     => array(
					'top left'      => esc_html__( 'Top Left', 'lt-process-cards' ),
					'top center'    => esc_html__( 'Top Center', 'lt-process-cards' ),
					'top right'     => esc_html__( 'Top Right', 'lt-process-cards' ),
					'center left'   => esc_html__( 'Center Left', 'lt-process-cards' ),
					'center center' => esc_html__( 'Center (Default)', 'lt-process-cards' ),
					'center right'  => esc_html__( 'Center Right', 'lt-process-cards' ),
					'bottom left'   => esc_html__( 'Bottom Left', 'lt-process-cards' ),
					'bottom center' => esc_html__( 'Bottom Center', 'lt-process-cards' ),
					'bottom right'  => esc_html__( 'Bottom Right', 'lt-process-cards' ),
				),
				'default'     => 'center center',
			)
		);

		$repeater->add_control(
			'slide_badge_icon',
			array(
				'type'        => Controls_Manager::ICONS,
				'label'       => esc_html__( 'Badge Icon', 'lt-process-cards' ),
				'description' => esc_html__( 'Optional. Shown next to the badge text.', 'lt-process-cards' ),
				'default'     => array(
					'value'   => '',
					'library' => '',
				),
			)
		);

		$repeater->add_control(
			'slide_badge_text',
			array(
				'type'    => Controls_Manager::TEXT,
				'label'   => esc_html__( 'Badge Text', 'lt-process-cards' ),
				'default' => esc_html__( 'Category', 'lt-process-cards' ),
			)
		);

		$repeater->add_control(
			'slide_title',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Title', 'lt-process-cards' ),
				'rows'    => 2,
				'default' => esc_html__( 'Slide title.', 'lt-process-cards' ),
			)
		);

		$repeater->add_control(
			'slide_description',
			array(
				'type'    => Controls_Manager::TEXTAREA,
				'label'   => esc_html__( 'Description', 'lt-process-cards' ),
				'rows'    => 3,
				'default' => '',
			)
		);

		$repeater->add_control(
			'slide_link',
			array(
				'type'        => Controls_Manager::URL,
				'label'       => esc_html__( 'Link', 'lt-process-cards' ),
				'description' => esc_html__( 'Optional. Makes the whole slide clickable.', 'lt-process-cards' ),
				'default'     => array(
					'url' => '',
				),
				'placeholder' => 'https://your-link.com',
			)
		);

		$repeater->add_control(
			'slide_active',
			array(
				'type'        => Controls_Manager::SWITCHER,
				'label'       => esc_html__( 'Mark as Active/Selected', 'lt-process-cards' ),
				'description' => esc_html__( 'Applies the Active State style (Style tab) to this slide, e.g. to highlight a default selection.', 'lt-process-cards' ),
				'default'     => '',
			)
		);

		$this->add_control(
			'slides',
			array(
				'type'        => Controls_Manager::REPEATER,
				'fields'      => $repeater->get_controls(),
				'title_field' => '{{{ slide_title }}}',
				'default'     => array(
					array(
						'slide_badge_text' => esc_html__( 'Metabolic', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Stop crashing at 3pm.', 'lt-process-cards' ),
					),
					array(
						'slide_badge_text' => esc_html__( 'Nervous System', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Sleep better. Recover faster. Handle more.', 'lt-process-cards' ),
						'slide_active'     => 'yes',
					),
					array(
						'slide_badge_text' => esc_html__( 'Sex & Hormones', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Get your mood, your cycles, and your energy back.', 'lt-process-cards' ),
					),
					array(
						'slide_badge_text' => esc_html__( 'Cardiovascular', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Measure your heart now. Not after an alert.', 'lt-process-cards' ),
					),
					array(
						'slide_badge_text' => esc_html__( 'Bones & Muscles', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Feel younger for longer. Start with what\'s invisible.', 'lt-process-cards' ),
					),
					array(
						'slide_badge_text' => esc_html__( 'Immunity', 'lt-process-cards' ),
						'slide_title'      => esc_html__( 'Eat well and actually feel the difference.', 'lt-process-cards' ),
					),
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab — every behavioural (non-visual) slider setting.
	 */
	private function register_settings_controls(): void {
		$this->start_controls_section(
			'section_settings',
			array(
				'label' => esc_html__( 'Slider Settings', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$slide_count_options = array(
			'1' => '1',
			'2' => '2',
			'3' => '3',
			'4' => '4',
			'5' => '5',
			'6' => '6',
		);

		$this->add_responsive_control(
			'slides_to_show',
			array(
				'type'           => Controls_Manager::SELECT,
				'label'          => esc_html__( 'Slides to Show', 'lt-process-cards' ),
				'options'        => $slide_count_options,
				'default'        => '4',
				'tablet_default' => '2',
				'mobile_default' => '1',
			)
		);

		$this->add_control(
			'slides_to_scroll',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Slides to Scroll', 'lt-process-cards' ),
				'options' => $slide_count_options,
				'default' => '1',
			)
		);

		$this->add_control(
			'initial_slide',
			array(
				'type'    => Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Initial Slide', 'lt-process-cards' ),
				'min'     => 1,
				'default' => 1,
			)
		);

		$this->add_control(
			'transition_effect',
			array(
				'type'    => Controls_Manager::SELECT,
				'label'   => esc_html__( 'Transition Effect', 'lt-process-cards' ),
				'options' => array(
					'slide' => esc_html__( 'Slide', 'lt-process-cards' ),
					'fade'  => esc_html__( 'Fade', 'lt-process-cards' ),
				),
				'default' => 'slide',
			)
		);

		$this->add_control(
			'transition_speed',
			array(
				'type'    => Controls_Manager::NUMBER,
				'label'   => esc_html__( 'Transition Speed (ms)', 'lt-process-cards' ),
				'min'     => 100,
				'max'     => 3000,
				'step'    => 50,
				'default' => 400,
			)
		);

		$this->add_control(
			'infinite_loop',
			array(
				'type'    => Controls_Manager::SWITCHER,
				'label'   => esc_html__( 'Infinite Loop', 'lt-process-cards' ),
				'default' => 'yes',
			)
		);

		$this->add_control(
			'enable_drag',
			array(
				'type'    => Controls_Manager::SWITCHER,
				'label'   => esc_html__( 'Mouse/Touch Drag', 'lt-process-cards' ),
				'default' => 'yes',
			)
		);

		$this->add_control(
			'autoplay',
			array(
				'type'    => Controls_Manager::SWITCHER,
				'label'   => esc_html__( 'Autoplay', 'lt-process-cards' ),
				'default' => '',
			)
		);

		$this->add_control(
			'autoplay_speed',
			array(
				'type'      => Controls_Manager::NUMBER,
				'label'     => esc_html__( 'Autoplay Speed (ms)', 'lt-process-cards' ),
				'min'       => 500,
				'max'       => 10000,
				'step'      => 100,
				'default'   => 4000,
				'condition' => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'pause_on_hover',
			array(
				'type'      => Controls_Manager::SWITCHER,
				'label'     => esc_html__( 'Pause on Hover', 'lt-process-cards' ),
				'default'   => 'yes',
				'condition' => array(
					'autoplay' => 'yes',
				),
			)
		);

		$this->add_control(
			'show_arrows',
			array(
				'type'    => Controls_Manager::SWITCHER,
				'label'   => esc_html__( 'Show Arrows', 'lt-process-cards' ),
				'default' => 'yes',
			)
		);

		$this->add_control(
			'show_dots',
			array(
				'type'    => Controls_Manager::SWITCHER,
				'label'   => esc_html__( 'Show Dots', 'lt-process-cards' ),
				'default' => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Content tab — the full-size "Detail Panel" rendered under the slider:
	 * a full-box image with the badge/title/description overlaid on top of
	 * it (left or right side), showing whichever slide is currently
	 * selected (starts on the slide with its "Mark as Active/Selected"
	 * switcher on, or the first slide, and updates when a slide without its
	 * own Link is clicked — see
	 * assets/js/lt-image-text-slider.js).
	 */
	private function register_detail_panel_controls(): void {
		$this->start_controls_section(
			'section_detail_panel',
			array(
				'label' => esc_html__( 'Detail Panel', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'show_detail_panel',
			array(
				'type'        => Controls_Manager::SWITCHER,
				'label'       => esc_html__( 'Show Detail Panel', 'lt-process-cards' ),
				'description' => esc_html__( 'A full-size image + text panel under the slider, in sync with whichever slide is selected.', 'lt-process-cards' ),
				'default'     => 'yes',
			)
		);

		$this->add_control(
			'detail_text_position',
			array(
				'type'        => Controls_Manager::CHOOSE,
				'label'       => esc_html__( 'Text Position', 'lt-process-cards' ),
				'description' => esc_html__( 'The image always fills the full panel; this controls which side the badge/title/description overlay sits on.', 'lt-process-cards' ),
				'options'     => array(
					'left'  => array(
						'title' => esc_html__( 'Left', 'lt-process-cards' ),
						'icon'  => 'eicon-h-align-left',
					),
					'right' => array(
						'title' => esc_html__( 'Right', 'lt-process-cards' ),
						'icon'  => 'eicon-h-align-right',
					),
				),
				'default'     => 'left',
				'condition'   => array(
					'show_detail_panel' => 'yes',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — track gap, and every slide's box size and vertical
	 * alignment.
	 *
	 * The gap is written to a `--lt-its-gap` custom property (rather than
	 * only a `gap` declaration) because assets/js/lt-image-text-slider.js
	 * reads it to compute pixel-accurate slide offsets - the one control
	 * drives both the visual gap and the JS math, so they can never
	 * disagree.
	 *
	 * Every slide - selected or not - gets the exact same literal
	 * width/height from Slide Width/Height below (not "however many fit the
	 * viewport" auto-division): `.lt-image-text-slider__slide` is
	 * `flex: 0 0 auto`, so it sizes itself to its card's explicit
	 * width/height. Clicking a slide never changes its box size - only its
	 * border/shadow (Active State section) and the Detail Panel's content
	 * (a crossfade, see swapDetailContent() in assets/js/lt-image-text-slider.js).
	 */
	private function register_style_layout_controls(): void {
		$this->start_controls_section(
			'section_style_layout',
			array(
				'label' => esc_html__( 'Layout', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'slide_gap',
			array(
				'label'      => esc_html__( 'Gap Between Slides', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 20,
				),
				'selectors'  => array(
					'{{WRAPPER}}' => '--lt-its-gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'slide_width',
			array(
				'label'       => esc_html__( 'Slide Width', 'lt-process-cards' ),
				'description' => esc_html__( 'Fixed box width for every slide, including the selected one.', 'lt-process-cards' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 80,
						'max' => 600,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 220,
				),
				'selectors'   => array(
					'{{WRAPPER}} .lt-image-text-slider__card' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'slide_height',
			array(
				'label'       => esc_html__( 'Slide Height', 'lt-process-cards' ),
				'description' => esc_html__( 'Fixed box height for every slide, including the selected one.', 'lt-process-cards' ),
				'type'        => Controls_Manager::SLIDER,
				'size_units'  => array( 'px' ),
				'range'       => array(
					'px' => array(
						'min' => 80,
						'max' => 600,
					),
				),
				'default'     => array(
					'unit' => 'px',
					'size' => 150,
				),
				'selectors'   => array(
					'{{WRAPPER}} .lt-image-text-slider__card' => 'height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'slide_vertical_align',
			array(
				'label'     => esc_html__( 'Vertical Alignment', 'lt-process-cards' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'stretch'    => array(
						'title' => esc_html__( 'Stretch', 'lt-process-cards' ),
						'icon'  => 'eicon-v-align-stretch',
					),
					'flex-start' => array(
						'title' => esc_html__( 'Top', 'lt-process-cards' ),
						'icon'  => 'eicon-v-align-top',
					),
					'center'     => array(
						'title' => esc_html__( 'Middle', 'lt-process-cards' ),
						'icon'  => 'eicon-v-align-middle',
					),
					'flex-end'   => array(
						'title' => esc_html__( 'Bottom', 'lt-process-cards' ),
						'icon'  => 'eicon-v-align-bottom',
					),
				),
				'default'   => 'stretch',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__track' => 'align-items: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the slide's background image and its overlay.
	 */
	private function register_style_image_controls(): void {
		$this->start_controls_section(
			'section_style_image',
			array(
				'label' => esc_html__( 'Image', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'image_object_fit',
			array(
				'label'     => esc_html__( 'Object Fit', 'lt-process-cards' ),
				'type'      => Controls_Manager::SELECT,
				'options'   => array(
					'cover'   => esc_html__( 'Cover', 'lt-process-cards' ),
					'contain' => esc_html__( 'Contain', 'lt-process-cards' ),
					'fill'    => esc_html__( 'Fill', 'lt-process-cards' ),
				),
				'default'   => 'cover',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__image img' => 'object-fit: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'overlay_color',
			array(
				'label'     => esc_html__( 'Overlay Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'linear-gradient(180deg, rgba(0,0,0,0) 25%, rgba(0,0,0,.65) 100%)',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__overlay' => 'background: {{VALUE}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the small category badge (icon + text) at the top of each slide.
	 */
	private function register_style_badge_controls(): void {
		$this->start_controls_section(
			'section_style_badge',
			array(
				'label' => esc_html__( 'Badge', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'badge_color',
			array(
				'label'     => esc_html__( 'Text/Icon Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'badge_background_color',
			array(
				'label'     => esc_html__( 'Background', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.18)',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'badge_icon_size',
			array(
				'label'      => esc_html__( 'Icon Size', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 40,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 14,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__badge-icon svg' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'badge_typography',
				'label'    => esc_html__( 'Typography', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__badge-text',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the slide title.
	 */
	private function register_style_title_controls(): void {
		$this->start_controls_section(
			'section_style_title',
			array(
				'label' => esc_html__( 'Title', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'title_typography',
				'label'    => esc_html__( 'Typography', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__title',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the highlight applied to whichever slide is currently
	 * selected (either flagged "Mark as Active/Selected" on load, or
	 * clicked): the blue border/shadow only. Every slide - active or not -
	 * keeps the exact same box size (the Layout section's Slide
	 * Width/Height), so nothing shifts size on click; only the Detail Panel
	 * content changes (crossfade, see swapDetailContent() in
	 * assets/js/lt-image-text-slider.js).
	 */
	private function register_style_active_controls(): void {
		$this->start_controls_section(
			'section_style_active',
			array(
				'label' => esc_html__( 'Active State', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'active_border_color',
			array(
				'label'     => esc_html__( 'Border Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3B82F6',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__slide--active .lt-image-text-slider__card' => 'box-shadow: 0 0 0 var(--lt-its-active-border-width, 2px) {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'active_border_width',
			array(
				'label'      => esc_html__( 'Border Width', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 10,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 2,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__slide--active .lt-image-text-slider__card' => '--lt-its-active-border-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'active_shadow',
				'label'    => esc_html__( 'Box Shadow', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__slide--active .lt-image-text-slider__card',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the card box itself (background, radius, padding, border, shadow).
	 */
	private function register_style_box_controls(): void {
		$this->start_controls_section(
			'section_style_box',
			array(
				'label' => esc_html__( 'Slide Box', 'lt-process-cards' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			array(
				'name'           => 'card_background',
				'types'          => array( 'classic', 'gradient' ),
				'selector'       => '{{WRAPPER}} .lt-image-text-slider__card',
				'fields_options' => array(
					'background' => array(
						'default' => 'classic',
					),
					'color'      => array(
						'default' => '#1c1c1c',
					),
				),
			)
		);

		$this->add_responsive_control(
			'card_radius',
			array(
				'label'      => esc_html__( 'Radius', 'lt-process-cards' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => '16',
					'right'    => '16',
					'bottom'   => '16',
					'left'     => '16',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				),
			)
		);

		$this->add_responsive_control(
			'card_padding',
			array(
				'label'      => esc_html__( 'Content Padding', 'lt-process-cards' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'      => '14',
					'right'    => '14',
					'bottom'   => '14',
					'left'     => '14',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'card_border',
				'selector' => '{{WRAPPER}} .lt-image-text-slider__card',
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'card_shadow',
				'selector' => '{{WRAPPER}} .lt-image-text-slider__card',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the previous/next arrow buttons.
	 */
	private function register_style_arrows_controls(): void {
		$this->start_controls_section(
			'section_style_arrows',
			array(
				'label'     => esc_html__( 'Arrows', 'lt-process-cards' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_arrows' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_size',
			array(
				'label'      => esc_html__( 'Size', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 20,
						'max' => 80,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 36,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'arrow_color',
			array(
				'label'     => esc_html__( 'Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1c1c1c',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_background_color',
			array(
				'label'     => esc_html__( 'Background', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.9)',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_hover_color',
			array(
				'label'     => esc_html__( 'Hover Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1c1c1c',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow:hover' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'arrow_hover_background_color',
			array(
				'label'     => esc_html__( 'Hover Background', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow:hover' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'arrow_horizontal_offset',
			array(
				'label'      => esc_html__( 'Horizontal Offset', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => -40,
						'max' => 60,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__arrow--prev' => 'left: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .lt-image-text-slider__arrow--next' => 'right: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the dot pagination.
	 */
	private function register_style_dots_controls(): void {
		$this->start_controls_section(
			'section_style_dots',
			array(
				'label'     => esc_html__( 'Dots', 'lt-process-cards' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_dots' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'dot_size',
			array(
				'label'      => esc_html__( 'Size', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 4,
						'max' => 24,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__dot' => 'width: {{SIZE}}{{UNIT}}; height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'dot_color',
			array(
				'label'     => esc_html__( 'Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#d9d9d9',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__dot' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'dot_active_color',
			array(
				'label'     => esc_html__( 'Active Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#1c1c1c',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__dot--active' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'dot_gap',
			array(
				'label'      => esc_html__( 'Gap', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 30,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 8,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__dots' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'dot_margin_top',
			array(
				'label'      => esc_html__( 'Top Spacing', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 60,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 16,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__dots' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Style tab — the Detail Panel (image column + badge/title/description column).
	 */
	private function register_style_detail_controls(): void {
		$this->start_controls_section(
			'section_style_detail',
			array(
				'label'     => esc_html__( 'Detail Panel', 'lt-process-cards' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => array(
					'show_detail_panel' => 'yes',
				),
			)
		);

		$this->add_responsive_control(
			'detail_top_spacing',
			array(
				'label'      => esc_html__( 'Top Spacing', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 24,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__detail' => 'margin-top: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'detail_panel_min_height',
			array(
				'label'      => esc_html__( 'Panel Height', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 800,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 460,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__detail' => 'min-height: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'detail_panel_radius',
			array(
				'label'      => esc_html__( 'Panel Radius', 'lt-process-cards' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%' ),
				'default'    => array(
					'top'      => '20',
					'right'    => '20',
					'bottom'   => '20',
					'left'     => '20',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__detail' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; overflow: hidden;',
				),
			)
		);

		$this->add_control(
			'detail_overlay_color',
			array(
				'label'       => esc_html__( 'Image Overlay', 'lt-process-cards' ),
				'description' => esc_html__( 'Darkens the side of the image the text sits on so it stays readable over any photo.', 'lt-process-cards' ),
				'type'        => Controls_Manager::COLOR,
				'default'     => 'linear-gradient(90deg, rgba(0,0,0,.65) 0%, rgba(0,0,0,.15) 55%, rgba(0,0,0,0) 80%)',
				'selectors'   => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-overlay' => 'background: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'detail_content_max_width',
			array(
				'label'      => esc_html__( 'Text Max Width', 'lt-process-cards' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 200,
						'max' => 900,
					),
					'%'  => array(
						'min' => 20,
						'max' => 100,
					),
				),
				'default'    => array(
					'unit' => 'px',
					'size' => 560,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-content' => 'max-width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'detail_content_padding',
			array(
				'label'      => esc_html__( 'Text Padding', 'lt-process-cards' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em' ),
				'default'    => array(
					'top'      => '48',
					'right'    => '48',
					'bottom'   => '48',
					'left'     => '48',
					'unit'     => 'px',
					'isLinked' => true,
				),
				'selectors'  => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'heading_detail_badge',
			array(
				'label'     => esc_html__( 'Badge', 'lt-process-cards' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'detail_badge_color',
			array(
				'label'     => esc_html__( 'Text/Icon Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-badge' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'detail_badge_background_color',
			array(
				'label'     => esc_html__( 'Background', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.15)',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-badge' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'detail_badge_typography',
				'label'    => esc_html__( 'Typography', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__detail-badge-text',
			)
		);

		$this->add_control(
			'heading_detail_title',
			array(
				'label'     => esc_html__( 'Title', 'lt-process-cards' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'detail_title_color',
			array(
				'label'     => esc_html__( 'Text Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-title' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'detail_title_typography',
				'label'    => esc_html__( 'Typography', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__detail-title',
			)
		);

		$this->add_control(
			'heading_detail_desc',
			array(
				'label'     => esc_html__( 'Description', 'lt-process-cards' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'detail_desc_color',
			array(
				'label'     => esc_html__( 'Text Color', 'lt-process-cards' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => 'rgba(255,255,255,0.85)',
				'selectors' => array(
					'{{WRAPPER}} .lt-image-text-slider__detail-desc' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'detail_desc_typography',
				'label'    => esc_html__( 'Typography', 'lt-process-cards' ),
				'selector' => '{{WRAPPER}} .lt-image-text-slider__detail-desc',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Clamp a raw "slides to show/scroll" setting to the 1-6 range the
	 * SELECT controls offer, falling back to a safe default for anything
	 * else (missing responsive value, malformed settings, etc.).
	 *
	 * @param mixed $value    Raw setting value.
	 * @param int   $fallback Fallback when the value is out of range.
	 */
	private function sanitize_slide_count( $value, int $fallback ): int {
		$value = absint( $value );

		return ( $value >= 1 && $value <= 6 ) ? $value : $fallback;
	}

	/**
	 * WordPress's registered image sizes ("thumbnail", "medium", any
	 * theme/plugin-added size, …) plus "full", as a `SELECT` options array.
	 * Built fresh per call rather than cached: `get_intermediate_image_sizes()`
	 * already reads a filtered global, and image sizes can only change on a
	 * page (re)load, never mid-request.
	 *
	 * @return array<string, string>
	 */
	private function get_image_size_options(): array {
		$options = array();

		foreach ( get_intermediate_image_sizes() as $size ) {
			$options[ $size ] = ucwords( str_replace( array( '-', '_' ), ' ', $size ) );
		}

		$options['full'] = esc_html__( 'Full Size', 'lt-process-cards' );

		return $options;
	}

	/**
	 * Validate a raw "Slider Thumbnail Size" setting against the registered
	 * image sizes before it's used to request an attachment image - a
	 * SELECT control already constrains the editor UI, but the same
	 * defense-in-depth reasoning as sanitize_heading_tag() applies to any
	 * value used outside plain escaped text output.
	 *
	 * @param mixed $size Raw setting value.
	 */
	private function sanitize_image_size( $size ): string {
		$allowed = array_merge( get_intermediate_image_sizes(), array( 'full' ) );
		$size    = is_string( $size ) ? $size : '';

		return in_array( $size, $allowed, true ) ? $size : 'thumbnail';
	}

	/**
	 * Validate a raw "Thumbnail Focal Position" setting against the fixed
	 * nine-value enum the SELECT control offers before it's used to build
	 * an inline `object-position` style.
	 *
	 * @param mixed $position Raw setting value.
	 */
	private function sanitize_image_position( $position ): string {
		$allowed  = array(
			'top left',
			'top center',
			'top right',
			'center left',
			'center center',
			'center right',
			'bottom left',
			'bottom center',
			'bottom right',
		);
		$position = is_string( $position ) ? $position : '';

		return in_array( $position, $allowed, true ) ? $position : 'center center';
	}

	/**
	 * Render the slider card's `<img>` - built directly with
	 * `wp_get_attachment_image()` (rather than the
	 * `Group_Control_Image_Size::get_attachment_image_html()` helper used
	 * for the Detail Panel) because this is the one image on the slide that
	 * needs two things helper can't give it together: an independent
	 * "Slider Thumbnail Size" (so the small card loads a small cropped
	 * image, not the full one) and a per-slide `object-position` for the
	 * "Thumbnail Focal Position" control.
	 *
	 * @param array<string, mixed> $slide Repeater item settings.
	 */
	private function render_slide_image( array $slide ): void {
		$image         = is_array( $slide['slide_image'] ?? null ) ? $slide['slide_image'] : array();
		$attachment_id = absint( $image['id'] ?? 0 );
		$size          = $this->sanitize_image_size( $slide['slide_thumb_size'] ?? 'thumbnail' );
		$position      = $this->sanitize_image_position( $slide['slide_image_position'] ?? 'center center' );
		$style         = sprintf( 'object-position: %s;', $position );

		if ( $attachment_id ) {
			echo wp_get_attachment_image(
				$attachment_id,
				$size,
				false,
				array(
					'style' => $style,
					'alt'   => '',
				)
			); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() builds and escapes its own <img> tag; $style is a fixed 'object-position: <keyword pair>;' string built from sanitize_image_position()'s closed enum, never raw text.

			return;
		}

		if ( ! empty( $image['url'] ) ) {
			printf( '<img src="%s" style="%s" alt="" />', esc_url( $image['url'] ), esc_attr( $style ) );
		}
	}

	/**
	 * Echo an HTML attribute list, escaping each value with the function
	 * appropriate to its name (`esc_url()` for href, `esc_attr()` for
	 * everything else).
	 *
	 * @param array<string, string> $attrs Attribute name => raw value pairs.
	 */
	private function render_attributes( array $attrs ): void {
		foreach ( $attrs as $name => $value ) {
			$escaped_value = ( 'href' === $name ) ? esc_url( $value ) : esc_attr( $value );
			printf( ' %s="%s"', esc_attr( $name ), $escaped_value ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- $escaped_value is already escaped above with the function matching its attribute.
		}
	}

	/**
	 * Render the widget output on the front end.
	 */
	protected function render(): void {
		$settings = $this->get_settings_for_display();
		$slides   = is_array( $settings['slides'] ?? null ) ? $settings['slides'] : array();

		if ( empty( $slides ) ) {
			return;
		}

		$slides_desktop = $this->sanitize_slide_count( $settings['slides_to_show'] ?? '4', 4 );
		$slides_tablet  = $this->sanitize_slide_count( $settings['slides_to_show_tablet'] ?? $slides_desktop, $slides_desktop );
		$slides_mobile  = $this->sanitize_slide_count( $settings['slides_to_show_mobile'] ?? '1', 1 );
		$slides_scroll  = $this->sanitize_slide_count( $settings['slides_to_scroll'] ?? '1', 1 );
		$initial_slide  = max( 1, absint( $settings['initial_slide'] ?? 1 ) ) - 1;
		$effect         = ( 'fade' === ( $settings['transition_effect'] ?? 'slide' ) ) ? 'fade' : 'slide';
		$speed          = absint( $settings['transition_speed'] ?? 400 );
		$autoplay       = 'yes' === ( $settings['autoplay'] ?? '' );
		$autoplay_speed = absint( $settings['autoplay_speed'] ?? 4000 );
		$pause_on_hover = 'yes' === ( $settings['pause_on_hover'] ?? 'yes' );
		$loop           = 'yes' === ( $settings['infinite_loop'] ?? 'yes' );
		$drag           = 'yes' === ( $settings['enable_drag'] ?? 'yes' );
		$show_arrows    = 'yes' === ( $settings['show_arrows'] ?? 'yes' ) && count( $slides ) > 1;
		$show_dots      = 'yes' === ( $settings['show_dots'] ?? 'yes' ) && count( $slides ) > 1;

		$wrapper_attrs = array(
			'class'                 => 'lt-image-text-slider',
			'data-slides-desktop'   => (string) $slides_desktop,
			'data-slides-tablet'    => (string) $slides_tablet,
			'data-slides-mobile'    => (string) $slides_mobile,
			'data-slides-to-scroll' => (string) $slides_scroll,
			'data-initial-slide'    => (string) min( $initial_slide, count( $slides ) - 1 ),
			'data-effect'           => $effect,
			'data-speed'            => (string) $speed,
			'data-autoplay'         => $autoplay ? 'true' : 'false',
			'data-autoplay-speed'   => (string) $autoplay_speed,
			'data-pause-on-hover'   => $pause_on_hover ? 'true' : 'false',
			'data-loop'             => $loop ? 'true' : 'false',
			'data-drag'             => $drag ? 'true' : 'false',
		);
		?>
		<div<?php $this->render_attributes( $wrapper_attrs ); ?>>
			<div class="lt-image-text-slider__viewport">
				<div class="lt-image-text-slider__track" role="list">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<?php $this->render_slide( $slide, (int) $index ); ?>
					<?php endforeach; ?>
				</div>
			</div>

			<?php if ( $show_arrows ) : ?>
				<button type="button" class="lt-image-text-slider__arrow lt-image-text-slider__arrow--prev" aria-label="<?php esc_attr_e( 'Previous slide', 'lt-process-cards' ); ?>">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M15 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
				<button type="button" class="lt-image-text-slider__arrow lt-image-text-slider__arrow--next" aria-label="<?php esc_attr_e( 'Next slide', 'lt-process-cards' ); ?>">
					<svg viewBox="0 0 24 24" width="18" height="18" fill="none" aria-hidden="true"><path d="M9 5l7 7-7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
				</button>
			<?php endif; ?>

			<?php if ( $show_dots ) : ?>
				<div class="lt-image-text-slider__dots" role="tablist" aria-label="<?php esc_attr_e( 'Slides', 'lt-process-cards' ); ?>">
					<?php foreach ( $slides as $index => $slide ) : ?>
						<button
							type="button"
							class="lt-image-text-slider__dot"
							role="tab"
							data-index="<?php echo esc_attr( (string) $index ); ?>"
							aria-label="<?php echo esc_attr( sprintf( /* translators: %d: slide number, one-based. */ __( 'Go to slide %d', 'lt-process-cards' ), (int) $index + 1 ) ); ?>"
						></button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<?php $this->render_detail_panel( $slides, $settings ); ?>
		</div>
		<?php
	}

	/**
	 * Determine which slide the Detail Panel starts on: the first slide
	 * with its "Mark as Active/Selected" switcher on, or slide 0 if none is
	 * marked.
	 *
	 * @param array<int, array<string, mixed>> $slides Repeater items.
	 */
	private function get_initial_active_index( array $slides ): int {
		foreach ( $slides as $index => $slide ) {
			if ( 'yes' === ( $slide['slide_active'] ?? '' ) ) {
				return (int) $index;
			}
		}

		return 0;
	}

	/**
	 * Render the Detail Panel: one full-box image with the badge/title/
	 * description overlaid on top of it (left or right side, via the
	 * "Text Position" control), for whichever slide is currently selected.
	 * Starts on get_initial_active_index() so the panel is correct even
	 * before JS runs. Also renders one hidden `<template>` per slide (via
	 * the same render_detail_media_inner() / render_detail_content_inner()
	 * helpers used for the initial content) so
	 * assets/js/lt-image-text-slider.js can swap the panel on slide click by
	 * cloning already-escaped, server-rendered markup — it never builds HTML
	 * from data-* attribute strings.
	 *
	 * @param array<int, array<string, mixed>> $slides   Repeater items.
	 * @param array<string, mixed>             $settings Widget settings for display.
	 */
	private function render_detail_panel( array $slides, array $settings ): void {
		if ( 'yes' !== ( $settings['show_detail_panel'] ?? 'yes' ) ) {
			return;
		}

		$active_index = $this->get_initial_active_index( $slides );
		$active_slide = $slides[ $active_index ] ?? array();
		$text_right   = 'right' === ( $settings['detail_text_position'] ?? 'left' );

		$detail_classes = array( 'lt-image-text-slider__detail' );
		if ( $text_right ) {
			$detail_classes[] = 'lt-image-text-slider__detail--text-right';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $detail_classes ) ); ?>" data-active-index="<?php echo esc_attr( (string) $active_index ); ?>">
			<div class="lt-image-text-slider__detail-media">
				<?php $this->render_detail_media_inner( $active_slide ); ?>
				<span class="lt-image-text-slider__detail-overlay" aria-hidden="true"></span>
			</div>
			<div class="lt-image-text-slider__detail-content">
				<?php $this->render_detail_content_inner( $active_slide ); ?>
			</div>
		</div>

		<div class="lt-image-text-slider__detail-templates" hidden>
			<?php foreach ( $slides as $index => $slide ) : ?>
				<template class="lt-image-text-slider__detail-media-template" data-index="<?php echo esc_attr( (string) $index ); ?>">
					<?php $this->render_detail_media_inner( $slide ); ?>
				</template>
				<template class="lt-image-text-slider__detail-content-template" data-index="<?php echo esc_attr( (string) $index ); ?>">
					<?php $this->render_detail_content_inner( $slide ); ?>
				</template>
			<?php endforeach; ?>
		</div>
		<?php
	}

	/**
	 * Render just the Detail Panel's `<img>` for one slide — the caller
	 * supplies the `.lt-image-text-slider__detail-media` wrapper and its overlay.
	 *
	 * @param array<string, mixed> $slide Repeater item settings.
	 */
	private function render_detail_media_inner( array $slide ): void {
		if ( empty( $slide['slide_image']['url'] ) ) {
			return;
		}

		echo Group_Control_Image_Size::get_attachment_image_html( $slide, 'slide_image' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Elementor core helper builds and escapes its own <img> tag.
	}

	/**
	 * Render the Detail Panel's badge/title/description for one slide — the
	 * caller supplies the `.lt-image-text-slider__detail-content` wrapper.
	 *
	 * @param array<string, mixed> $slide Repeater item settings.
	 */
	private function render_detail_content_inner( array $slide ): void {
		$title       = (string) ( $slide['slide_title'] ?? '' );
		$description = (string) ( $slide['slide_description'] ?? '' );
		$badge_text  = (string) ( $slide['slide_badge_text'] ?? '' );
		$badge_icon  = is_array( $slide['slide_badge_icon'] ?? null ) ? $slide['slide_badge_icon'] : array();
		?>
		<?php if ( '' !== $badge_text || ! empty( $badge_icon['value'] ) ) : ?>
			<span class="lt-image-text-slider__detail-badge">
				<?php if ( ! empty( $badge_icon['value'] ) ) : ?>
					<span class="lt-image-text-slider__detail-badge-icon" aria-hidden="true">
						<?php Icons_Manager::render_icon( $badge_icon, array( 'aria-hidden' => 'true' ) ); ?>
					</span>
				<?php endif; ?>
				<?php if ( '' !== $badge_text ) : ?>
					<span class="lt-image-text-slider__detail-badge-text"><?php echo esc_html( $badge_text ); ?></span>
				<?php endif; ?>
			</span>
		<?php endif; ?>

		<?php if ( '' !== $title ) : ?>
			<span class="lt-image-text-slider__detail-title"><?php echo esc_html( $title ); ?></span>
		<?php endif; ?>

		<?php if ( '' !== $description ) : ?>
			<span class="lt-image-text-slider__detail-desc"><?php echo esc_html( $description ); ?></span>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render a single slide.
	 *
	 * @param array<string, mixed> $slide Repeater item settings.
	 * @param int                  $index Zero-based slide index.
	 */
	private function render_slide( array $slide, int $index ): void {
		$title      = (string) ( $slide['slide_title'] ?? '' );
		$badge_text = (string) ( $slide['slide_badge_text'] ?? '' );
		$badge_icon = is_array( $slide['slide_badge_icon'] ?? null ) ? $slide['slide_badge_icon'] : array();
		$is_active  = 'yes' === ( $slide['slide_active'] ?? '' );
		$link       = is_array( $slide['slide_link'] ?? null ) ? $slide['slide_link'] : array();
		$url        = (string) ( $link['url'] ?? '' );
		$has_link   = '' !== $url;
		$tag        = $has_link ? 'a' : 'div';

		$slide_classes = array( 'lt-image-text-slider__slide' );
		if ( $is_active ) {
			$slide_classes[] = 'lt-image-text-slider__slide--active';
		}

		$card_attrs = array( 'class' => 'lt-image-text-slider__card' );
		if ( $has_link ) {
			$card_attrs['href'] = $url;
			if ( ! empty( $link['is_external'] ) ) {
				$card_attrs['target'] = '_blank';
			}
			if ( ! empty( $link['nofollow'] ) ) {
				$card_attrs['rel'] = 'nofollow';
			}
		} else {
			// No link: the card is a click/keyboard target that selects this
			// slide for the Detail Panel instead of navigating anywhere.
			$card_attrs['role']     = 'button';
			$card_attrs['tabindex'] = '0';
		}
		?>
		<div class="<?php echo esc_attr( implode( ' ', $slide_classes ) ); ?>" role="listitem" data-index="<?php echo esc_attr( (string) $index ); ?>">
			<<?php echo esc_html( $tag ); ?><?php $this->render_attributes( $card_attrs ); ?>>
				<span class="lt-image-text-slider__image">
					<?php $this->render_slide_image( $slide ); ?>
					<span class="lt-image-text-slider__overlay" aria-hidden="true"></span>
				</span>

				<span class="lt-image-text-slider__content">
					<?php if ( '' !== $badge_text || ! empty( $badge_icon['value'] ) ) : ?>
						<span class="lt-image-text-slider__badge">
							<?php if ( ! empty( $badge_icon['value'] ) ) : ?>
								<span class="lt-image-text-slider__badge-icon" aria-hidden="true">
									<?php Icons_Manager::render_icon( $badge_icon, array( 'aria-hidden' => 'true' ) ); ?>
								</span>
							<?php endif; ?>
							<?php if ( '' !== $badge_text ) : ?>
								<span class="lt-image-text-slider__badge-text"><?php echo esc_html( $badge_text ); ?></span>
							<?php endif; ?>
						</span>
					<?php endif; ?>

					<?php if ( '' !== $title ) : ?>
						<span class="lt-image-text-slider__title"><?php echo esc_html( $title ); ?></span>
					<?php endif; ?>
				</span>
			</<?php echo esc_html( $tag ); ?>>
		</div>
		<?php
	}
}
