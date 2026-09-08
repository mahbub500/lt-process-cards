=== LT Process Cards ===
Contributors: tairanalam
Tags: elementor, widget, cards, process, booking
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A fully customizable Elementor widget for a three-step "how it works" process section: booking, results and plan cards in one row.

== Description ==

LT Process Cards adds a single custom Elementor widget that reproduces a
three-step "how it works" section:

* A **booking card** with an eyebrow label, title, subtitle, and selectable
  date and time chips.
* A **results card** with an eyebrow label, title, subtitle, and an SVG
  trend chart with labeled reference ranges and any number of data points.
* A **plan card** with an eyebrow label, title, subtitle, and a list of
  icon + title + description rows.

All three cards are laid out together in one row by a single widget — you
don't assemble them from separate pieces.

Every part of the design is editable from the Elementor panel: text and list
items (via repeaters), icons (built-in presets or your own via Elementor's
native icon control), typography, colors, hover states, spacing, borders,
border radius and backgrounds — all with responsive (Desktop/Tablet/Mobile)
controls where it makes sense. A freshly-added, untouched widget renders
identically to the original hand-coded design; every control's default value
was copied directly from the source stylesheet.

The widget appears in the Elementor panel under the **LT Blocks** category.

No Elementor Pro features are required.

See this plugin's `README.md` for the complete control reference and
developer documentation.

= Requirements =

* WordPress 6.0 or greater
* PHP 8.2 or greater
* Elementor 3.5.0 or greater (free version)

== Installation ==

1. Upload the `lt-process-cards` folder to `/wp-content/plugins/`, or install
   the ZIP through **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Edit any page with Elementor and search for **LT Process Cards** in the
   widget panel, or find it under the **LT Blocks** category.

== Frequently Asked Questions ==

= What happens if Elementor is not active, or is too old? =

The plugin loads safely and shows an admin notice explaining exactly what's
missing (PHP version, WordPress version, Elementor itself, or Elementor's
version). No widget is registered and no Elementor class is ever referenced,
so there is no fatal error either way.

= Does it work without running Composer? =

Yes. A bundled PSR-4 autoloader is used automatically when
`vendor/autoload.php` is absent, so the plugin works correctly installed
from a plain ZIP.

= Does this require Elementor Pro? =

No. Every control uses Elementor's free-tier APIs.

= Can I change the icons in the plan list? =

Yes. Each plan item has a Preset Icon (one of three built-in icons) and an
optional Custom Icon field — set Custom Icon to any icon-library glyph or
your own uploaded SVG to override the preset for that row.

= I changed a style setting and the front end didn't update. =

Regenerate Elementor's CSS (Elementor > Tools > Regenerate CSS) and clear
any page/object cache. This plugin doesn't cache anything itself.

= Is it safe to run multiple instances of this widget on the same page? =

Yes. Every style control this widget registers is scoped to that specific
widget instance by Elementor's own `{{WRAPPER}}` mechanism, so different
instances never affect each other's appearance.

== Changelog ==

= 1.0.0 =
* Full three-card widget: booking (date/time chips), results (SVG trend
  chart) and plan (icon list), each fully backed by repeater-driven content
  controls.
* Complete Style-tab control set: typography (per text role, responsive),
  colors (including Normal/Hover states), layout, spacing (margin/padding),
  borders (type/width/color/radius, with Normal/Hover tabs on the
  interactive chips), and backgrounds.
* Native Elementor icon control for plan-item icons (preset or custom,
  including SVG upload), with the built-in icons kept as the default so an
  untouched widget is unchanged.
* Full keyboard/screen-reader accessibility pass: correct ARIA roles on the
  date/time chip groups, a text alternative for the results chart's data,
  configurable heading levels, and visible keyboard focus states.
* Security-reviewed output escaping throughout; no direct file access, no
  unsafe HTML/URL fields, no custom AJAX/forms requiring a nonce.
* Bootstrap that safely no-ops (with an admin notice) if WordPress,
  PHP or Elementor's version requirements aren't met - never a fatal error.
