# Tasas BCV - WordPress Plugin

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue)](https://wordpress.org)

**Tasas BCV** is a lightweight, efficient WordPress plugin that automatically fetches official exchange rates (USD, EUR, CNY) from the Central Bank of Venezuela (Banco Central de Venezuela - BCV) and displays them via a clean shortcode.

---

## Description

The plugin parses official rates published on the BCV website (`https://www.bcv.org.ve`) and caches the formatted output using WordPress transients for 4 hours. This minimizes external requests and ensures your WordPress site loads fast.

### Features

- 💵 **Currencies Supported**: US Dollar (USD), Euro (EUR), and Chinese Yuan (CNY).
- ⚡ **Performance Optimized**: Uses 4-hour transient caching (`bcv_widget_cache`).
- 🧩 **Shortcode Ready**: Display exchange rates anywhere using `[tasa_bcv]`.
- 🌍 **Internationalization (i18n)**: Fully translated into English (`en_US`) and Spanish (`es_ES`).
- 🛡️ **Safe XML Parsing**: Implements strict `libxml` state restoration and error clearing.

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

Place the `[tasa_bcv]` shortcode anywhere in your content:

- **Gutenberg Block Editor**: Add a Shortcode block and enter `[tasa_bcv]`.
- **Classic Editor**: Paste `[tasa_bcv]` directly into the text editor.
- **Widgets**: Add a Text or Custom HTML widget in your sidebar/footer with `[tasa_bcv]`.

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

## Folder Structure

```
wordpress-tasa-bcv/
├── build-release.sh            # Script to build release zip package
├── languages/
│   ├── tasas-bcv-automaticas.pot
│   ├── tasas-bcv-automaticas-en_US.po
│   ├── tasas-bcv-automaticas-en_US.mo
│   ├── tasas-bcv-automaticas-es_ES.po
│   └── tasas-bcv-automaticas-es_ES.mo
├── LICENSE
├── README.md                   # Repository README (English)
├── readme.txt                  # WordPress.org standard plugin documentation
└── tasas-bcv-automaticas.php  # Main plugin file
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