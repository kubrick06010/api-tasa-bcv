# Tasas BCV - WordPress Plugin & API

**Tasas BCV** fetches official USD, EUR and CNY exchange rates from the Banco Central de Venezuela (BCV), caches normalized data in WordPress, renders it through a shortcode, and exposes a read-only REST endpoint.

## Features

- Official BCV rates for USD, EUR and CNY.
- HTTPS certificate verification uses WordPress defaults; TLS verification is never disabled.
- HTTP status and parsed values are validated before entering the cache.
- Fresh cache for 4 hours plus a 7-day last-known-good fallback when BCV is temporarily unavailable.
- `[tasa_bcv]` shortcode with optional currency and decimal parameters.
- Public read-only REST endpoint at `/wp-json/tasas-bcv/v1/rates`.
- No Composer or third-party PHP dependencies.

## Installation

Copy the plugin directory into `wp-content/plugins/tasas-bcv-automaticas/` or build the installable ZIP with:

```bash
./build-release.sh
```

Then activate **Tasas BCV** in WordPress.

## Shortcode

Display all rates:

```text
[tasa_bcv]
```

Display one currency with four decimals:

```text
[tasa_bcv moneda="USD" decimales="4"]
```

Supported currencies are `USD`, `EUR` and `CNY`. Decimal precision is clamped between 0 and 6.

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
    "CNY": 4.995
  },
  "date": "16/08/2026",
  "fetched_at": 1786914000,
  "source": "BCV",
  "stale": false
}
```

When BCV cannot be refreshed but a last-known-good value exists, the endpoint still returns HTTP 200 with `"stale": true` and a `refresh_error` field. If no valid data exists at all, it returns HTTP 503.

## Cache behaviour

The plugin stores normalized data rather than rendered HTML:

- `bcv_rates_cache`: fresh data, 4-hour TTL.
- `bcv_rates_last_valid`: last successful response, 7-day TTL.

This lets the shortcode and REST endpoint share one source of truth and keeps the site usable during short BCV outages.

## Tests

Run:

```bash
php -l tasas-bcv-automaticas.php
php tests/smoke.php
```

The smoke test runs without WordPress by providing minimal stubs. If PHP `ext-dom` is installed it also exercises the XPath parser; otherwise that parser-specific assertion is explicitly skipped.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- PHP DOM extension (`ext-dom`) in production, as required by `DOMDocument`/`DOMXPath`.

## License

GPL-2.0-or-later. See `LICENSE`.
