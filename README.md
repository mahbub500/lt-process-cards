# LT Process Cards

A custom Elementor widget that reproduces a three-step "how it works" process
section — **Test / Track / Transform** by default — as one drag-and-drop
Elementor widget. It renders three cards side by side: a booking card with
selectable date and time chips, a results card with an SVG trend chart, and a
plan card with an icon list.

Everything that was originally a fixed value in a hand-coded HTML/CSS design
is now an Elementor control: text, icons, colors, typography, spacing,
borders, backgrounds, hover states and responsive behavior. An untouched,
freshly-dropped-in widget renders pixel-identical to the original design —
every control's default value was copied directly from the source stylesheet.

This document covers both **using the widget in Elementor** and
**maintaining the plugin's code**. Skip to whichever half you need.

---

## Table of contents

1. [Plugin name](#1-plugin-name)
2. [Plugin description](#2-plugin-description)
3. [Requirements](#3-requirements)
4. [Installation](#4-installation)
5. [Activation](#5-activation)
6. [Elementor usage](#6-elementor-usage)
7. [Widget location / category](#7-widget-location--category)
8. [Content controls](#8-content-controls)
9. [Style controls](#9-style-controls)
10. [Typography controls](#10-typography-controls)
11. [Responsive controls](#11-responsive-controls)
12. [Hover controls](#12-hover-controls)
13. [Advanced controls](#13-advanced-controls)
14. [Repeater usage](#14-repeater-usage)
15. [Multiple widget instances](#15-multiple-widget-instances)
16. [Custom CSS](#16-custom-css)
17. [Troubleshooting](#17-troubleshooting)
18. [Developer architecture](#18-developer-architecture)
19. [File structure](#19-file-structure)
20. [Security considerations](#20-security-considerations)
21. [Performance considerations](#21-performance-considerations)
22. [Developer notes](#22-developer-notes)

---

## 1. Plugin name

**LT Process Cards**

## 2. Plugin description

LT Process Cards adds a single custom Elementor widget, **LT Process Cards**,
that reproduces a three-panel "how it works" row:

| Card | Content |
|---|---|
| **Booking** | An eyebrow label, title, subtitle, a row of selectable **date** chips and a row of selectable **time** chips |
| **Results** | An eyebrow label, title, subtitle, and an SVG line chart with three reference-range labels and any number of data points |
| **Plan** | An eyebrow label, title, subtitle, and a list of icon + title + description rows |

The widget is a single unit — you drag in one widget and get all three cards
laid out together — not three separate widgets you assemble yourself.

## 3. Requirements

* WordPress **6.0** or later
* PHP **8.2** or later
* Elementor (free) **3.5.0** or later — no Elementor Pro features are required
* Composer is **optional**. It's only needed if you want to run the linter
  (`squizlabs/php_codesniffer` + WordPress Coding Standards) during
  development. The plugin ships with its own PSR-4 autoloader and runs
  correctly as a plain ZIP install with no `vendor/` directory.

## 4. Installation

1. Upload the `lt-process-cards` folder to `/wp-content/plugins/`, or install
   the ZIP through **Plugins → Add New → Upload Plugin** in wp-admin.
2. Activate **LT Process Cards** on the **Plugins** screen.
3. Make sure **Elementor** is installed and active (see
   [Requirements](#3-requirements)).

## 5. Activation

On activation, the plugin does nothing risky by default — it waits for the
`plugins_loaded` hook, then checks:

* PHP version ≥ 8.2
* WordPress version ≥ 6.0
* Elementor is loaded, and its version ≥ 3.5.0

If **any** of these checks fail, the plugin shows an admin notice explaining
what's missing (visible to users who can `activate_plugins`) and stops —
**no widget is registered, no Elementor class is ever referenced, and no
fatal error is possible.** Once the missing requirement is resolved (e.g. you
install/update Elementor), the widget becomes available with no further
action needed.

## 6. Elementor usage

1. Edit any page with Elementor.
2. Open the widget panel and search for **LT Process Cards**, or find it
   under the **LT Blocks** category (see [below](#7-widget-location--category)).
3. Drag it onto the page. It renders with realistic placeholder content out
   of the box — you don't need to fill in every field before it looks right.
4. Use the **Content** tab to edit each card's text and list items, and the
   **Style** tab to adjust colors, typography, spacing, borders and
   backgrounds. See [sections 8–13](#8-content-controls) below for the full
   control reference.

## 7. Widget location / category

* **Panel category:** *LT Blocks* (registered by this plugin specifically;
  slug `lt-blocks`, icon `eicon-parallax`).
* **Widget icon:** `eicon-gallery-grid`.
* **Search keywords:** `lt`, `process`, `card`, `booking`, `results`, `plan`,
  `test`, `track`, `transform` — searching any of these in the Elementor
  panel's search box will surface the widget.

## 8. Content controls

All Content-tab controls are grouped into per-card sections. Every default
value matches the original design (e.g. "01. TEST", "200+ advanced
diagnostics") — you're expected to replace this placeholder copy with your
own.

**Card 1: Booking**
* *Heading & Description* — Eyebrow label, Title (+ [HTML tag](#10-typography-controls)), Subtitle
* *Dates* — a [repeater](#14-repeater-usage) of date chips (day abbreviation, day number, an internal `data-num` value, and a Normal/Selected/Peek state)
* *Times* — a repeater of time chips (label, and a Normal/Selected/Disabled state)

**Card 2: Results**
* *Heading & Description* — Eyebrow label, Title, Subtitle
* *Chart Labels* — the three reference-range labels drawn on the chart (Above range / In range / Below range)
* *Data Points* — a repeater of chart points (a value label, X/Y position on the 260×145 chart canvas, and the Y position of its value label)

**Card 3: Plan**
* *Heading & Description* — Eyebrow label, Title, Subtitle
* *Plan Items* — a repeater of list rows, each with a **Preset Icon** (one of three built-in icons), an optional **Custom Icon** (any icon-library icon or your own uploaded SVG — see [Repeater usage](#14-repeater-usage)), an item title and an item description

## 9. Style controls

The Style tab is organized into focused sections rather than one long list:

| Section | Covers |
|---|---|
| *(per text role, see [§10](#10-typography-controls))* | Typography, alignment, text color — one section per distinct text role |
| **Colors** | Primary/accent color (drives the selected-chip background/border), accent text color |
| **Plan Item Icon** | Icon background + color, Normal and Hover |
| **Layout: Cards Row** | Max width, alignment, flex direction, gap, vertical alignment |
| **Layout: Dates & Times** | Gap between date chips, gap between time chips |
| **Layout: Plan List** | Gap between list rows, gap between icon and text, icon size |
| **Layout: Chart** | Horizontal alignment of the chart within its card |
| **Spacing: Card** | Card padding, and margin under the eyebrow/title/subtitle |
| **Spacing: Dates & Times** | Dates-wrapper margin, date chip padding, times-wrapper margin, time chip padding |
| **Spacing: Plan List** | Plan-item row padding |
| **Border: Card** | Card corner radius |
| **Border: Date & Time Chips** | Border type/width/color (Normal/Hover tabs) and corner radius, per chip type |
| **Border: Plan Item** | Border type/width/color and corner radius |
| **Background: Card** | Card background (native Classic background — color and/or image) |
| **Background: Date & Time Chips, Plan Item** | Shared background for all three (they're identical in the original design) |

Every default in every one of these sections reproduces the original
stylesheet's value exactly.

## 10. Typography controls

Ten distinct text roles each get their own Style-tab section, built from a
shared internal helper so they're all consistent:

* Eyebrow Label, Title, Subtitle *(shared across all three cards)*
* Date Chip: Day Label, Date Chip: Day Number
* Time Chip
* Chart: Zone Label, Chart: Value Label
* Plan Item: Title, Plan Item: Description

Each section provides:
* **Alignment** (left/center/right/justify) — omitted on roles where it
  wouldn't have any visible effect (e.g. content-sized chip labels, or the
  chart's SVG `<text>`, which is positioned by `x`/`text-anchor` attributes
  instead of CSS `text-align`)
* **Text Color**
* **Typography** — Elementor's native typography group: font family, size,
  weight, style, transform, decoration, line height, letter spacing

Additionally, the **Title** on each card has its own **Title HTML Tag**
control (Content tab, next to the title text field) — a `h1`–`h6`/`div`/`span`
choice, defaulting to `h3`. Use it to make the card title match whatever
heading level is correct for where you've placed the widget on the page (see
[§20](#20-security-considerations) for how the tag value itself is validated).

## 11. Responsive controls

Nearly every Style-tab control in this widget is responsive — it uses
Elementor's native Desktop / Tablet / Mobile switcher (the small device icons
next to the control label), so you can set a different value per breakpoint.
This includes: font sizes (via the typography group), spacing, gap, corner
radius, border width, layout alignment, and the row's own flex direction.

The one property the *original design itself* changes per breakpoint is the
row's direction: side-by-side on Desktop and Tablet, stacked on Mobile
(Elementor's default Mobile breakpoint, ≤767px, matching the original
stylesheet's own media query exactly). This is the **Flex Direction**
control's default behavior (*Layout: Cards Row* section) — it stacks
automatically on Mobile unless you override it, and you're free to set Tablet
independently since the original design never changes it there.

Elementor's own Advanced-tab **Responsive Visibility** switches (Hide On
Desktop / Tablet / Mobile) are also available — see [§13](#13-advanced-controls).

## 12. Hover controls

Two elements have genuine hover behavior in the original design: the date
chip and the time chip, both of which lighten their border to the accent
color on `:hover`. Their sections (*Border: Date & Time Chips*) present this
as a proper **Normal / Hover** tabbed control:

* **Normal tab** — the resting-state border (type, width, color)
* **Hover tab** — the hover-state border color, plus a **Transition
  Duration** slider (defaults to 150ms, matching the original CSS)

Keyboard users get identical feedback: focusing a chip with the keyboard
(`:focus-visible`) triggers the same border-color change as a mouse hover, on
top of — never instead of — the browser's own default focus outline, which
this widget never removes.

The **Plan Item Icon** section (see [§9](#9-style-controls)) also separates
Normal and Hover, even though the original design has no hover state there —
both default to the same color so nothing changes until you deliberately set
a different Hover value.

## 13. Advanced controls

The **Advanced** tab you see in the Elementor panel is Elementor's own
native tab — this plugin adds nothing to it and duplicates none of it. You
already have, for free, on every instance of this widget:

* **CSS ID** and **CSS Classes**
* **Margin** / **Padding** on the widget's own outer wrapper (distinct from
  this widget's own *Spacing* Style-tab sections, which target elements
  *inside* the widget)
* **Z-Index** and **Position** (type + offsets)
* **Responsive Visibility** (Hide On Desktop/Tablet/Mobile)
* **Motion Effects** (Entrance Animation in Elementor free; scroll/mouse
  effects if Elementor Pro is active)
* **Custom CSS** — **Elementor Pro only.** If Pro is active, Pro injects this
  section into the Advanced tab automatically; on Elementor free it doesn't
  appear. This plugin deliberately does not implement its own "paste raw
  CSS/JS" field — see [§20](#20-security-considerations).

## 14. Repeater usage

Four fields are Elementor repeaters — click **+ Add Item** to add a row,
drag the row's handle to reorder, and click the row to expand its fields:

| Repeater | Fields per row | What it's for |
|---|---|---|
| **Dates** (Card 1) | Day abbreviation, Day number, Calendar date, State | One row per date chip. **State** = Normal / Selected / Peek. Exactly one row should normally be Selected; a Peek row is a dimmed, non-interactive preview chip (used for the "one more date, partially visible" effect at the end of the row) |
| **Times** (Card 1) | Time, State | One row per time chip. **State** = Normal / Selected / Disabled |
| **Data Points** (Card 2) | Value label, Point X, Point Y, Value label Y | One row per point on the trend line. X/Y are pixel coordinates on the chart's 260×145 canvas; the polyline connecting the points is drawn automatically in that order |
| **Plan Items** (Card 3) | Preset Icon, Custom Icon, Item title, Item description | One row per plan-list entry. **Preset Icon** picks one of three built-in icons (Nutrition/Supplements/Activity); leave **Custom Icon** empty to use it. Set **Custom Icon** (any icon-library glyph, or upload your own SVG) to override the preset for that row only |

Deleting all rows from a repeater is safe — the card simply renders without
that section's contents (e.g. no chart line if there are no data points).

## 15. Multiple widget instances

Every Style-tab control this widget registers targets a selector prefixed
with `{{WRAPPER}}`, which Elementor replaces with a CSS class unique to that
specific widget *instance* (not shared across the widget type). That means:

* Three copies of this widget on the same page, each with completely
  different colors/spacing/content, never bleed into one another.
* Changing one instance's settings never changes another instance's
  rendered output.

This is Elementor's own standard mechanism, not something this plugin had to
build — but every one of this widget's ~90 controls was written to rely on
it correctly (see [§18](#18-developer-architecture)).

## 16. Custom CSS

This widget does not provide its own "Custom CSS" field. If you need to go
beyond what the Style tab exposes:

* **Elementor Pro** provides a native **Custom CSS** section in the Advanced
  tab automatically (see [§13](#13-advanced-controls)) — the safest option,
  since Elementor Pro handles the scoping and sanitization.
* Otherwise, use your theme's **Additional CSS** (Customizer) or a code
  snippet, targeting this widget's own BEM class names. Every class is
  prefixed `lt-process-cards__`, e.g. `.lt-process-cards__card`,
  `.lt-process-cards__title`, `.lt-process-cards__date--selected` — see the
  full list in `assets/css/lt-process-cards.css`. These names are stable and
  intended to be safe to target from your own CSS.

## 17. Troubleshooting

**The widget doesn't appear in the Elementor panel.**
Check for the admin notice described in [§5](#5-activation) — it tells you
exactly which requirement (PHP/WordPress/Elementor version, or Elementor not
installed) isn't met. Nothing else prevents the widget from registering.

**I changed a Style-tab control and nothing happened on the front end.**
Regenerate Elementor's CSS: **Elementor → Tools → Regenerate CSS** (or clear
your page cache / object cache if you use a caching plugin — this plugin
doesn't create any cache itself).

**The chart looks empty.**
The **Data Points** repeater (Card 2) has no rows, or all rows have blank
X/Y values. Add at least one point.

**A plan item's icon didn't change after I picked one.**
Check whether you set it in **Preset Icon** or **Custom Icon** — Custom Icon
always wins over Preset Icon when it has a value (see [§14](#14-repeater-usage)).
Clear the Custom Icon field to fall back to the preset.

**The row isn't stacking on mobile / is stacking when I don't want it to.**
Check the **Flex Direction** control's Mobile value (*Layout: Cards Row*
section) — see [§11](#11-responsive-controls).

**I get a PHP fatal error mentioning an Elementor class.**
This should not happen given the [Activation](#5-activation) safeguards. If
it does, it most likely means Elementor was active when the page loaded but
was deactivated/updated mid-request, or a very old Elementor build predates
an API this plugin uses. Confirm you're on Elementor ≥ 3.5.0 and file an
issue with the exact error text and stack trace.

## 18. Developer architecture

**Bootstrap chain** — `lt-process-cards.php` defines a `PLUGIN_FILE` const,
loads `vendor/autoload.php` if present (Composer), otherwise falls back to
the bundled PSR-4 `includes/Autoloader.php` (works as a plain ZIP with no
`vendor/` directory). Everything boots on `plugins_loaded` via
`Plugin::instance()->boot()`.

**Requirement gating** — `Plugin::boot()` checks `Requirements::are_met()`
*before* touching any Elementor class. `Requirements` separates cheap
detection (safe to run anytime) from translated message-building (only ever
called after `init`, to avoid WordPress's just-in-time translation-loading
warning). If requirements fail, `Admin\Notices` renders the failure instead,
and `Plugin::register_services()` never runs.

**Service registration** — `Plugin::register_services()` instantiates a
fixed array of `Contracts\Registrable` services (`Assets_Manager`,
`Widget_Category`, `Widgets_Manager`), each of which owns its own WordPress
hooks. `Widgets_Manager` holds a `const WIDGETS` array of widget class names
— adding a new widget to the plugin means adding one line there, nothing
else in the registration flow changes.

**Assets** are *registered*, not enqueued, on `wp_enqueue_scripts` priority 5
(`Assets_Manager`). Each widget's `get_style_depends()`/`get_script_depends()`
pulls the handles in — meaning the CSS/JS only loads on pages that actually
contain this widget (see [§21](#21-performance-considerations)).

**The widget class** (`includes/Widgets/Process_Cards.php`, ~2,300 lines) —
see its own class-level docblock for the exact `register_*_controls()` call
order and what each one covers; the short version is nine focused methods
(three per-card Content sections, then Typography / Colors / Layout /
Spacing / Border / Background on the Style tab), several of which are backed
by small shared helper methods (`add_spacing_control()`,
`add_border_control()`, `add_border_hover_control()`,
`add_border_radius_control()`, `add_background_control()`,
`register_text_style_section()`, `add_heading_tag_control()`) to keep ~90
individual controls from becoming ~90 near-duplicate blocks of registration
code.

**CSS architecture** — every class is a BEM name under one unique block,
`lt-process-cards__<element>` / `lt-process-cards__<element>--<modifier>`
(see `assets/css/lt-process-cards.css`'s own header comment). Nothing is a
bare generic name (`.card`, `.title`, …) that could collide with a theme or
another plugin. The stylesheet holds only the structural/box-model defaults
needed for the widget to look correct before any control is touched;
everything a user can change is generated per-instance by Elementor itself
from the registered controls.

## 19. File structure

```
lt-process-cards/
├── lt-process-cards.php        Main plugin file: header, autoload, boot
├── uninstall.php                Uninstall routine (no persistent data to clean up)
├── composer.json                Dev tooling only (PHPCS + WPCS); not required at runtime
├── phpcs.xml.dist                PHPCS ruleset for this plugin
├── readme.txt                   WordPress.org-format readme
├── README.md                    This file
│
├── includes/
│   ├── Plugin.php                Central plugin object: paths, version, service wiring
│   ├── Autoloader.php            Bundled PSR-4 autoloader (used when vendor/ is absent)
│   ├── Requirements.php          PHP/WP/Elementor version + availability checks
│   │
│   ├── Contracts/
│   │   └── Registrable.php       Interface every self-registering service implements
│   │
│   ├── Admin/
│   │   └── Notices.php           Renders the "requirements not met" admin notice
│   │
│   ├── Assets/
│   │   └── Assets_Manager.php    Registers (not enqueues) the front-end CSS/JS
│   │
│   └── Widgets/
│       ├── Widget_Category.php   Registers the "LT Blocks" Elementor panel category
│       ├── Widgets_Manager.php   Registers this plugin's widget(s) with Elementor
│       └── Process_Cards.php     The widget itself: controls + render()
│
├── assets/
│   ├── css/lt-process-cards.css  Structural/default styles (BEM-scoped)
│   └── js/lt-process-cards.js    Front-end JS (currently a scaffolded no-op)
│
└── languages/                    Generated .pot file for translations
```

## 20. Security considerations

* **Output escaping** — every dynamic value is escaped at the point it's
  echoed, using the function matched to its context: `esc_html()` for text
  content, `esc_attr()` for HTML/SVG attribute values. There is exactly one
  deliberate exception, clearly marked with a `phpcs:ignore` and a comment:
  the three built-in plan-icon SVGs, which are fixed, developer-authored
  markup from a closed enum (never derived from any user-facing control), so
  escaping them would just break the icon rendering.
* **No raw HTML/URL fields** — every text control is `TEXT`/`TEXTAREA`
  (plain text only), never `WYSIWYG`; there is no `Controls_Manager::URL` or
  `MEDIA` field anywhere in this widget, so `wp_kses_post()`/`esc_url()`
  simply have no application point here. `sanitize_text_field()` likewise
  has none — this plugin never reads `$_POST`/`$_GET`/`$_REQUEST` directly;
  all settings persistence goes through Elementor's own control-save
  pipeline (its own nonce + capability checks), and this plugin only ever
  performs *output* escaping of the already-saved values.
* **The "Custom Icon" and background-image controls** are rendered through
  Elementor's own APIs (`Icons_Manager::render_icon()`,
  `Group_Control_Background`) and deliberately **not** re-sanitized by this
  plugin — Elementor already validates and safely renders its own control
  types; re-implementing that would be redundant and error-prone.
* **The "Title HTML Tag" control** (`add_heading_tag_control()`) is a fixed
  `SELECT` in the editor, but its saved value is still validated
  server-side against a strict allow-list (`sanitize_heading_tag()`) before
  being used to build a raw `<tag>` name — defense in depth against a
  crafted save request, not because the SELECT UI itself is unsafe.
* **No custom Custom-CSS/JS field.** This plugin deliberately does not
  implement its own "paste raw CSS or JavaScript" control — see
  [§13](#13-advanced-controls) and [§16](#16-custom-css). That's exactly the
  kind of unsafe execution surface a professional widget should not invent
  when a safe, native alternative (Elementor Pro's own Custom CSS) already
  exists.
* **Direct file access** — every PHP file that contains real logic starts
  with `if ( ! defined( 'ABSPATH' ) ) { exit; }`; `uninstall.php` guards on
  `WP_UNINSTALL_PLUGIN` instead. Every directory also has an empty
  `// Silence is golden.` `index.php` to prevent directory listing on a
  misconfigured server.
* **No custom AJAX, forms, settings pages or REST endpoints** exist in this
  plugin, so there is no custom state-changing entry point that would need
  its own nonce or capability check. The one place a capability check *is*
  relevant — the admin "requirements not met" notice — is gated on
  `current_user_can( 'activate_plugins' )` before it renders anything.
* **Path traversal** — the bundled `Autoloader::load()` resolves the target
  file with `realpath()` and verifies it's still inside the plugin's
  `includes/` directory (`str_starts_with()`) before requiring it, guarding
  against a malformed/crafted class name escaping the intended directory.

## 21. Performance considerations

* **Assets load only where needed.** `Assets_Manager` *registers* the CSS/JS
  handles; only a widget instance's `get_style_depends()`/
  `get_script_depends()` actually enqueues them, on pages that use the
  widget (this correctly includes the Elementor editor's own preview
  iframe, which is itself a front-end request).
* **No external HTTP requests, no extra database queries.** This plugin
  reads only the settings Elementor already loaded for the widget instance
  being rendered (`get_settings_for_display()`) — nothing here issues its
  own `WP_Query`, REST call, or remote fetch.
* **Icons are inline SVG or a single Elementor-managed asset**, not
  additional per-icon HTTP requests — the three built-in plan icons are
  literal inline `<svg>` markup; a user's Custom Icon selection is rendered
  through Elementor's own Icons_Manager, using whatever asset-loading
  strategy Elementor/the active icon library already uses.
* **JavaScript is currently a no-op.** `assets/js/lt-process-cards.js` only
  registers an Elementor frontend hook (`frontend/element_ready/lt_process_cards.default`)
  with an empty handler — it is deliberately scaffolded for future
  interactive behavior (making the date/time chips actually selectable)
  rather than shipping unused logic today. Enqueuing it now costs one small,
  cached, per-page-with-the-widget request; it does no DOM work yet.
* **CSS is a single small stylesheet** (under 300 lines) with no
  `@import`, no web fonts loaded by this plugin (typography controls let you
  pick a font family, but loading that font is the same mechanism any other
  Elementor typography control uses — this plugin doesn't add its own font
  loader), and no per-instance `<style>` bloat beyond what Elementor itself
  generates from the controls a user actually changed away from their
  defaults.

## 22. Developer notes

**Conventions used throughout this codebase** (follow them when adding to
it):

* `declare( strict_types = 1 );` + the plugin's namespace + an
  `if ( ! defined( 'ABSPATH' ) ) { exit; }` guard at the top of every real
  PHP file.
* WordPress Coding Standards (`phpcs.xml.dist`, based on `WordPress` +
  `PHPCompatibilityWP`, testVersion `8.2-`). Run `composer lint` before
  committing; `composer lint:fix` auto-fixes what it can.
* Every control's **default value must match the original stylesheet
  exactly** — that invariant is what lets an untouched widget render
  correctly. If you add a control for a property, copy its default from
  `assets/css/lt-process-cards.css`, not from memory.
* **Escape once, at the point of output**, using the function matched to
  context (`esc_html()` for text, `esc_attr()` for attributes). Don't
  pre-escape a value you're about to concatenate into a larger string that
  gets escaped again later — that double-escapes entities. Build the raw
  string first, escape it exactly once right before `echo`.
* **Every Style-tab selector must be `{{WRAPPER}}`-prefixed.** This is the
  single thing that keeps multiple widget instances independent — see
  [§15](#15-multiple-widget-instances) and [§18](#18-developer-architecture).
  Never write a bare CSS selector into a control's `selectors` array.
* **CSS class names are BEM**, under the single block `lt-process-cards`
  (`lt-process-cards__<element>`, `lt-process-cards__<element>--<modifier>`).
  Never introduce a bare/generic class name.
* Before adding a *new* control, check whether it's already covered by
  Elementor's native Advanced tab (margin/padding/CSS ID/z-index/position/
  visibility/motion — see [§13](#13-advanced-controls)). Duplicating those is
  both wasted work and a source of conflicting-selector bugs.
* Where a shared helper already exists for a control shape (spacing,
  border+radius, border with Normal/Hover tabs, background, one text role's
  full typography section), use it rather than hand-rolling another
  near-identical block — see the "several of which are backed by small
  shared helper methods" list in [§18](#18-developer-architecture).

**How to add a new control:** find the `register_*_controls()` method for
the right Style-tab section (or Content-tab card section), add the control
inside its `start_controls_section()`/`end_controls_section()` pair, and set
`'default'` to whatever the original stylesheet already specifies for that
property. If it's a spacing/border/background control, prefer the matching
shared helper.

**How to add a new widget to the plugin:** create the class under
`includes/Widgets/`, extend `Elementor\Widget_Base`, implement
`get_name()`/`get_title()`/`get_icon()`/`get_categories()`/`get_keywords()`/
`register_controls()`/`render()`, then add its fully-qualified class name to
`Widgets_Manager::WIDGETS`. Nothing else in the registration flow needs to
change.

**Testing/verification workflow used while building this plugin** (no
automated test suite exists yet):
1. `php -l` on any changed file.
2. `composer lint` (or `./vendor/bin/phpcs --standard=phpcs.xml.dist <file>`)
   — keep new findings at zero; pre-existing findings in untouched code are
   tracked separately, not silently "fixed" as a side effect of an unrelated
   change.
3. A repo-wide grep for duplicate Elementor control IDs
   (`grep -oE "add_(responsive_)?control\(\s*['\"][a-z0-9_]+"` …) — Elementor
   silently lets a later `add_control()` call with a duplicate ID clobber an
   earlier one, so this has to be checked by hand.
4. Manual verification in a real WordPress + Elementor install (drag the
   widget in, exercise the controls, check the frontend) — this repository's
   automated checks stop at static analysis; nothing here can substitute for
   actually loading the editor.

**Known limitations / deliberately out of scope**, so a future contributor
doesn't "fix" them without re-reading the reasoning first:
* The date/time chips are marked up as a proper `radiogroup`/`radio` (see
  git history for the accessibility-audit commit) but have no click/keyboard
  selection *behavior* wired up yet — `assets/js/lt-process-cards.js` is an
  intentional placeholder for that.
* Several of the original design's default text colors (the muted
  accent/secondary tones) fall short of WCAG AA contrast against their
  default backgrounds. This was a deliberate reproduction of the original
  design rather than an oversight — every one of those colors is already
  user-adjustable via the Style tab. See the security/accessibility audit
  history for the exact ratios if you're deciding whether to change a
  default.
* No custom Custom-CSS or Custom-JS field exists, and none should be added
  — see [§20](#20-security-considerations).
