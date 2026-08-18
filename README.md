# Tasas BCV - WordPress Plugin

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)](https://wordpress.org)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4)

**Tasas BCV** is a lightweight, efficient WordPress plugin that automatically fetches official exchange rates (USD, EUR, CNY, TRY and RUB) from the Central Bank of Venezuela (Banco Central de Venezuela - BCV) and displays them via a clean shortcode.

---

## Description

The plugin parses official rates published on the BCV website (`https://www.bcv.org.ve`) and caches the result using WordPress transients for 4 hours. If BCV is temporarily unavailable, the plugin can use the last-known-good set of rates for up to 7 days and marks that response as stale. This minimizes external requests and ensures your WordPress site loads fast.

### Features

- 💵 **Currencies Supported**: US Dollar (USD), Euro (EUR), Chinese Yuan (CNY), Turkish Lira (TRY), and Russian Ruble (RUB).
- ⚡ **Performance Optimized**: Uses 4-hour fresh caching and a 7-day last-known-good fallback.
- 🧩 **Shortcode Ready**: Display exchange rates anywhere using `[tasa_bcv]`.
- 🌍 **Internationalization (i18n)**: Fully translated into English (`en_US`) and Spanish (`es_ES`).
- 🛡️ **Safe XML Parsing**: Implements strict `libxml` state restoration and error clearing.
- 🔌 **Read-only REST API**: Exposes the five rates at `/wp-json/tasas-bcv/v1/rates`.

---

## Install

### Manual Installation via WordPress Admin

1. Download the latest `tasas-bcv-automaticas.zip` release from the [Releases](https://github.com/octaviotron/wordpress-tasa-bcv/releases) section.
2. In your WordPress Admin Dashboard, navigate to **Plugins > Add New > Upload Plugin**.
3. Choose the `.zip` file and click **Install Now**.
4. Activate the plugin.

### Manual Installation via FTP / File System

1. Clone or extract this repository into your plugins directory:
   ```bash
   wp-content/plugins/tasas-bcv-automaticas/
   ```
2. Go to **Plugins > Installed Plugins** in WordPress admin and click **Activate**.

---

## Usage

### Using the Shortcode

Place the `[tasa_bcv]` shortcode anywhere in your content to display all five rates:

- **Gutenberg Block Editor**: Add a Shortcode block and enter `[tasa_bcv]`.
- **Classic Editor**: Paste `[tasa_bcv]` directly into the text editor.
- **Widgets**: Add a Text or Custom HTML widget in your sidebar/footer with `[tasa_bcv]`.

To display one currency, use the BCV HTML mapping below:

```text
USD -> dolar
EUR -> euro
CNY -> yuan
TRY -> lira
RUB -> rublo
```

```text
[tasa_bcv moneda="USD"]
[tasa_bcv moneda="EUR"]
[tasa_bcv moneda="CNY"]
[tasa_bcv moneda="TRY"]
[tasa_bcv moneda="RUB"]
[tasa_bcv moneda="USD" decimales="4"]
```

The `decimales` attribute accepts values from 0 to 6.

### Theme PHP Template Integration

To embed the exchange rates directly inside your WordPress theme templates:

```php
<?php
if ( shortcode_exists( 'tasa_bcv' ) ) {
    echo do_shortcode( '[tasa_bcv]' );
}
?>
```

---

## REST API

The read-only endpoint is:

```text
GET /wp-json/tasas-bcv/v1/rates
```

It returns the five currencies. A current response has `"stale": false`; a last-known-good fallback has `"stale": true`. The endpoint returns HTTP 503 when no valid data exists.

```json
{
  "base": "VES",
  "rates": {
    "USD": 773.3125,
    "EUR": 896.02946062,
    "CNY": 114.750115,
    "TRY": 16.1446144,
    "RUB": 9.10428493
  },
  "stale": false
}
```

---

## Tests

Run the syntax checks and tests with:

```bash
php -l tasas-bcv-automaticas.php
php -l tests/smoke.php
php -l tests/live.php
php tests/smoke.php
php tests/live.php
```

The smoke test runs without a WordPress installation using fixtures and checks plugin behavior. The live test makes a real request to BCV and requires all five currencies to be present and valid.

---

## TLS note

A certificate-chain validation issue has been observed with certain PHP/OpenSSL clients connecting to BCV (`unable to get local issuer certificate`), while the system `curl` client may work correctly. The temporary `sslverify => false` workaround is scoped exclusively to the request to `https://www.bcv.org.ve`; TLS verification is not disabled globally or for any other URL. Remove the workaround when the BCV certificate chain works correctly with the affected clients.

---

## Folder Structure

```
wordpress-tasa-bcv/
├── build-release.sh            # Script to build release zip package
├── dist/
│   └── tasas-bcv-automaticas.zip
├── languages/
│   ├── tasas-bcv-automaticas.pot
│   ├── tasas-bcv-automaticas-en_US.po
│   ├── tasas-bcv-automaticas-en_US.mo
│   ├── tasas-bcv-automaticas-es_ES.po
│   └── tasas-bcv-automaticas-es_ES.mo
├── tests/
│   ├── live.php
│   └── smoke.php
├── LICENSE
├── README.md                   # Repository README (English)
├── readme.txt                  # WordPress.org standard plugin documentation
└── tasas-bcv-automaticas.php   # Main plugin file
```

---

## Contribute

Contributions, bug reports, and feature requests are welcome!

1. Fork the repository on GitHub: [github.com/octaviotron/wordpress-tasa-bcv](https://github.com/octaviotron/wordpress-tasa-bcv)
2. Create a new topic branch (`git checkout -b feature/my-new-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/my-new-feature`).
5. Open a Pull Request.

### Developer Info

- **Author**: Octavio Rossell Tabet
- **Email**: [octavio.rossell@gmail.com](mailto:octavio.rossell@gmail.com)
- **GitHub**: [@octaviotron](https://github.com/octaviotron)

---

## License

This project is licensed under the GPL-2.0-or-later License - see the [LICENSE](LICENSE) file for details.
