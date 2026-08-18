=== Tasas BCV ===
Contributors: octaviotron
Donate link: https://github.com/octaviotron/wordpress-tasa-bcv
Tags: bcv, venezuela, exchange-rates, currency, ves, wordpress, wordpress-plugin, rest-api, php, web-scraping, usd, eur, cny, try, rub
Requires at least: 5.0
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Automatically fetches and displays official currency exchange rates published by the Central Bank of Venezuela (Banco Central de Venezuela - BCV).

== Description ==

**Tasas BCV** is a lightweight, high-performance WordPress plugin designed to retrieve official currency exchange rates directly from the Central Bank of Venezuela (BCV) official portal and display them seamlessly anywhere on your website.

### Key Features

* **Official Exchange Rates**: Retrieves rates for USD, EUR, CNY, TRY, and RUB.
* **Transient Caching**: Caches responses for 4 hours (14,400 seconds) to ensure optimal site performance and avoid unnecessary external requests.
* **Easy Integration**: Use the `[tasa_bcv]` shortcode in posts, pages, sidebars, footer widgets, or custom Gutenberg blocks.
* **Currency Selection**: Use `[tasa_bcv moneda="USD"]`, `[tasa_bcv moneda="EUR"]`, `[tasa_bcv moneda="CNY"]`, `[tasa_bcv moneda="TRY"]`, or `[tasa_bcv moneda="RUB"]`; the `decimales` attribute controls precision from 0 to 6.
* **Clean & Responsive Design**: Pre-styled minimalist layout that fits into any WordPress theme.
* **Localization Ready**: Fully translatable (includes Spanish and English translations out of the box).
* **REST API**: Exposes read-only rates at `/wp-json/tasas-bcv/v1/rates`.
* **Resilience**: Uses a fresh cache for 4 hours and a last-known-good fallback for up to 7 days during temporary BCV failures; fallback responses are marked stale.
* **Testing**: Includes offline smoke tests and a live test against the BCV site for all five currencies.

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

= Does it provide a REST API? =
Yes. `GET /wp-json/tasas-bcv/v1/rates` is read-only and returns the five rates and a `stale` flag. It returns HTTP 200 for current or last-known-good data, and HTTP 503 when no valid data exists.

= How can I run the tests? =
Run `php -l tasas-bcv-automaticas.php`, `php -l tests/smoke.php`, `php -l tests/live.php`, `php tests/smoke.php`, and `php tests/live.php`. The smoke test uses fixtures without WordPress; the live test makes a real BCV request.

= Why is TLS verification disabled for the BCV request? =
A certificate-chain validation issue has been observed with certain PHP/OpenSSL clients connecting to BCV (`unable to get local issuer certificate`), while the system `curl` client may work correctly. This temporary workaround applies only to `https://www.bcv.org.ve`, is documented in the source, and should be removed when the BCV chain works correctly with affected clients; verification is not disabled globally.

== Changelog ==

= 1.1.0 =
* Initial stable release.
* Added shortcode `[tasa_bcv]` and transient caching.
* Full i18n support for English and Spanish.
* Robust libxml error state restoration.

= 1.2.0 =
* Added hardened BCV parsing, HTTP 200 and complete-rate validation, live and smoke tests, stale fallback, and a read-only REST API.
* Added TRY and RUB support; USD, EUR, CNY, TRY, and RUB are validated as numeric positive rates.
* Added currency-specific shortcode output and configurable decimal precision.
* Documented the temporary BCV-only TLS verification workaround.
