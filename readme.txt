=== Tasas BCV ===
Contributors: octaviotron
Donate link: https://github.com/octaviotron/wordpress-tasa-bcv
Tags: bcv, tasas, divisas, venezuela, currency exchange
Requires at least: 5.0
Tested up to: 6.6
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically fetches and displays official currency exchange rates published by the Central Bank of Venezuela (Banco Central de Venezuela - BCV).

== Description ==

**Tasas BCV** is a lightweight, high-performance WordPress plugin designed to retrieve official currency exchange rates directly from the Central Bank of Venezuela (BCV) official portal and display them seamlessly anywhere on your website.

### Key Features

* **Official Exchange Rates**: Retrieves rates for USD, EUR, and CNY.
* **Transient Caching**: Caches responses for 4 hours (14,400 seconds) to ensure optimal site performance and avoid unnecessary external requests.
* **Easy Integration**: Use the `[tasa_bcv]` shortcode in posts, pages, sidebars, footer widgets, or custom Gutenberg blocks.
* **Clean & Responsive Design**: Pre-styled minimalist layout that fits into any WordPress theme.
* **Localization Ready**: Fully translatable (includes Spanish and English translations out of the box).

### Developer & Repository Info

* **Developer**: Octavio Rossell Tabet <octavio.rossell@gmail.com> (https://github.com/octaviotron)
* **GitHub Repository**: https://github.com/octaviotron/wordpress-tasa-bcv

== Installation ==

1. Upload the `tasas-bcv-automaticas` directory to your `/wp-content/plugins/` directory (or upload the `.zip` file via **Plugins > Add New > Upload Plugin**).
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Place the `[tasa_bcv]` shortcode in any page, post, widget, or template where you want the exchange rates to appear.

== Frequently Asked Questions ==

= How often are the exchange rates updated? =
The plugin caches the retrieved data using WordPress transients for 4 hours. Once expired, the next page request automatically fetches fresh rates from the official BCV site.

= Where can I place the shortcode? =
You can insert `[tasa_bcv]` into any post, page, block editor, HTML widget, sidebar, or via PHP in your theme template using `<?php echo do_shortcode('[tasa_bcv]'); ?>`.

= Is this plugin safe to use? =
Yes. It performs a read-only HTTP request to the public BCV portal and safely cleans XML parsing errors without side effects on your server environment.

== Changelog ==

= 1.1.0 =
* Initial stable release.
* Added shortcode `[tasa_bcv]` and transient caching.
* Full i18n support for English and Spanish.
* Robust libxml error state restoration.

