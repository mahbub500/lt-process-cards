# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

An Elementor widget plugin (`LT\ProcessCards` namespace, `lt-process-cards` text domain) that reproduces a
three-step "how it works" section (Test / Track / Transform) as one widget: a booking card with date/time
chips, a results card with an SVG range chart, and a plan card with an icon list, laid out in one row.
Requires PHP 8.2+, WordPress 6.0+, Elementor 3.5.0+.

## Commands

```
composer install          # installs Composer autoloader + dev tooling (PHPCS, WPCS, PHPCompatibilityWP)
composer lint              # phpcs --standard=phpcs.xml.dist
composer lint:fix          # phpcbf --standard=phpcs.xml.dist
```

There is no test suite and no JS/CSS build step — `assets/js/lt-process-cards.js` and
`assets/css/lt-process-cards.css` are hand-authored and enqueued as-is (no bundler, no npm package).

Linting note: `WordPress.Files.FileName` is excluded (PSR-4 requires StudlyCaps filenames), and
`WordPress.NamingConventions.PrefixAllGlobals` is configured to accept both the `LT\ProcessCards`
namespace and an `lt_process_cards` global prefix.

## Architecture

**Bootstrap chain**: `lt-process-cards.php` defines the `PLUGIN_FILE` const, loads `vendor/autoload.php`
if present (Composer), otherwise falls back to the bundled `includes/Autoloader.php` (a minimal PSR-4
loader with a path-traversal guard) so the plugin still works when shipped as a plain ZIP with no
`vendor/` directory. Everything boots on `plugins_loaded` via `Plugin::instance()->boot()` — nothing
touches an Elementor class before that hook fires, so a missing/outdated Elementor can never cause a
fatal error.

**Requirement gating**: `Plugin::boot()` constructs a `Requirements` object and checks `are_met()`
*before* registering any service. `Requirements` separates detection (runs early, safe to call anytime)
from message building (`get_failures()`, which calls `esc_html__()` and must only run after `init` —
calling it earlier trips WordPress's just-in-time translation warning). If requirements fail, `Notices`
is registered instead of the real services and nothing else runs.

**Service registration**: `Plugin::register_services()` instantiates a fixed array of `Registrable`
services (`Assets_Manager`, `Widget_Category`, `Widgets_Manager`) and calls `register()` on each. Every
service is self-contained and only knows how to attach its own WordPress hooks — `Plugin` never reaches
into a service's internals. Adding a new top-level service means adding one line to that array.

**Widget registration**: `Widgets_Manager` holds a `const WIDGETS` array of fully-qualified widget class
names (currently just `Process_Cards::class`) and registers each on `elementor/widgets/register`. Adding
a new widget means adding its class to that array — nothing else in the registration flow changes.

**Assets are registered, not enqueued**: `Assets_Manager` calls `wp_register_style`/`wp_register_script`
(handle `lt-process-cards` for both) on `wp_enqueue_scripts` priority 5, early enough that the handles
exist before Elementor resolves widget dependencies. Widgets pull them in via `get_style_depends()` /
`get_script_depends()` on `Widget_Base`, so the CSS/JS only loads on pages that actually contain the
widget (including the Elementor editor canvas, which is itself a front-end request).

**The widget itself** (`includes/Widgets/Process_Cards.php`, ~1600 lines) renders all three cards from a
single `render()` call producing one `.lt-cards-row` wrapper around three `.lt-card` panels. Controls are
organized by concern, each in its own `private function register_*_controls()` called from
`register_controls()`:
- `register_card1_booking_controls()` / `register_card2_results_controls()` /
  `register_card3_plan_controls()` — per-card content controls (text fields + `Repeater` fields for
  dates, times, chart points, plan items). Control IDs are prefixed `card1_`, `card2_`, `card3_` to keep
  the three cards' settings from colliding in the flat Elementor settings array.
- `register_typography_controls()` (via the shared `register_text_style_section()` helper) — Style-tab
  typography groups.
- `register_color_controls()` — Style-tab color controls, including plan-icon and chip-hover colors.
- `register_layout_controls()` — Style-tab spacing/sizing controls for the row, dates/times, plan, and
  chart.

  Rendering is split into `render_booking_card()`, `render_results_card()`, `render_plan_card()`, each
taking the full `$settings` array from `get_settings_for_display()`. Plan-item icons come from a fixed,
developer-authored `const PLAN_ICONS` SVG map (not user-editable markup) — icons use `stroke="currentColor"`
specifically so the Style-tab "Plan Item Icon" color control can drive them via CSS `color` on the
`.lt-plan-icon` wrapper. Repeater sub-arrays (`card1_dates`, `card1_times`, `card2_chart_points`,
`card3_plan_items`) are always defensively cast with `is_array(...) ? ... : array()` before iterating,
since Elementor's settings array shape isn't statically guaranteed.

**Panel category**: the widget lives under a plugin-owned "LT Blocks" Elementor panel category
(`Widget_Category::SLUG = 'lt-blocks'`), registered separately from the widget itself on
`elementor/elements/categories_registered`.

## Conventions to follow

- `declare( strict_types = 1 );` and `namespace LT\ProcessCards...` at the top of every PHP file, plus
  the `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard immediately after.
- Services that hook into WordPress implement `LT\ProcessCards\Contracts\Registrable` (a single
  `register(): void` method) so `Plugin` can iterate over them uniformly.
- All output is escaped (`esc_html`, `esc_attr`, `esc_html__`, `esc_attr_e`) except the one deliberate,
  commented `phpcs:ignore` for the fixed SVG icon map — don't add new unescaped output without the same
  justification.
- New per-card settings keys must keep the `card1_`/`card2_`/`card3_` prefix convention.
