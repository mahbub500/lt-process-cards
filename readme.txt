=== LT Process Cards ===
Contributors: tairanalam
Tags: elementor, widget, cards, process, booking
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 8.2
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Custom Elementor widgets for a three-step process card section (Test, Track, Transform).

== Description ==

LT Process Cards adds custom Elementor widgets that reproduce a three-step
"how it works" section: a booking card with date and time chips, a results card
with a range chart, and a plan card with an icon list.

The widgets appear in the Elementor panel under the **LT Blocks** category.

This release is a development skeleton. Widget controls and the final markup are
implemented in subsequent phases.

= Requirements =

* WordPress 6.0 or greater
* PHP 8.2 or greater
* Elementor 3.5.0 or greater

== Installation ==

1. Upload the `lt-process-cards` folder to `/wp-content/plugins/`, or install the
   ZIP through **Plugins > Add New > Upload Plugin**.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. Edit any page with Elementor and search for **LT Booking Card** in the widget
   panel.

== Frequently Asked Questions ==

= What happens if Elementor is not active? =

The plugin loads safely and shows an admin notice. No widgets are registered and
no Elementor classes are referenced, so there is no fatal error.

= Does it work without running Composer? =

Yes. A bundled PSR-4 autoloader is used when `vendor/autoload.php` is absent.

== Changelog ==

= 1.0.0 =
* Initial plugin skeleton: bootstrap, requirement checks, admin notices, asset
  registration, widget category and a placeholder Booking Card widget.
