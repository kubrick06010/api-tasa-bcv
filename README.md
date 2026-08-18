[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)](https://wordpress.org)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)

# Tasas BCV - WordPress Plugin & API

Official BCV exchange rates for USD, EUR, CNY, TRY and RUB with WordPress shortcode, REST API, caching and live-tested parsing.

## Features

- Official BCV rates for USD, EUR, CNY, TRY and RUB.
- Strict HTTP, completeness, numeric and positive-value validation before caching.
- Fresh cache for 4 hours plus a 7-day last-known-good fallback when BCV is temporarily unavailable.
- `[tasa_bcv]` shortcode with optional currency and decimal parameters.
- Public read-only REST endpoint at `/wp-json/tasas-bcv/v1/rates`.
- No Composer or third-party PHP dependencies.

## Supported currencies

`USD`, `EUR`, `CNY`, `TRY`, `RUB`, mapped to the current BCV HTML IDs `dolar`, `euro`, `yuan`, `lira` and `rublo`.

## Installation

Copy the plugin directory into `wp-content/plugins/tasas-bcv-automaticas/` or build the installable ZIP with:

```bash
./build-release.sh
```

Then activate **Tasas BCV** in WordPress.

## Shortcode

Display all five rates with `[tasa_bcv]`. Display one currency with:

```text
[tasa_bcv moneda="TRY"]
[tasa_bcv moneda="RUB"]
[tasa_bcv moneda="USD" decimales="4"]
```

Decimal precision is clamped between 0 and 6.

## REST API

```text
GET /wp-json/tasas-bcv/v1/rates
```

Example response:

```json
{
  "base": "VES",
  "rates": {
    "USD": 36.1234,
    "EUR": 39.5012,
    "CNY": 4.995,
    "TRY": 16.1446144,
    "RUB": 9.10428493
  },
  "date": "16/08/2026",
  "fetched_at": 1786914000,
  "source": "BCV",
  "stale": false
}
```

The endpoint returns HTTP 200 with `"stale": true` when refresh fails but last-known-good data exists, and HTTP 503 when no valid data exists.

## Tests

```bash
php -l tasas-bcv-automaticas.php
php -l tests/smoke.php
php -l tests/live.php
php tests/smoke.php
php tests/live.php
```

The smoke test runs without WordPress. The live test downloads the current BCV page and fails unless all five currencies are present and valid.

## TLS note

The BCV request currently uses the temporary `sslverify => false` workaround because a certificate-chain problem was observed with certain OpenSSL clients. TLS is not disabled globally and this setting does not affect any other URL. Remove the workaround when BCV fixes its certificate chain; it is not an ideal permanent solution.

## Upstream

This project is a fork of [octaviotron/wordpress-tasa-bcv](https://github.com/octaviotron/wordpress-tasa-bcv). This fork adds a hardened parser, live tests, stale fallback, a read-only REST API, and support for USD, EUR, CNY, TRY and RUB.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- PHP DOM extension (`ext-dom`) in production

## License

GPL-2.0-or-later. See `LICENSE`.
