<?php
/*
Plugin Name: Tasas BCV
Plugin URI: https://github.com/kubrick06010/api-tasa-bcv
Description: Extrae las tasas oficiales de divisas del BCV (Banco Central de Venezuela), las expone por shortcode y REST API, y conserva el último valor válido cuando la fuente no está disponible.
Version: 1.2.0
Author: Octavio Rossell Tabet
Author URI: https://github.com/octaviotron
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tasas-bcv-automaticas
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

define('OB_BCV_FRESH_CACHE_KEY', 'bcv_rates_cache');
define('OB_BCV_STALE_CACHE_KEY', 'bcv_rates_last_valid');
define('OB_BCV_CACHE_TTL', 4 * HOUR_IN_SECONDS);
define('OB_BCV_STALE_TTL', 7 * DAY_IN_SECONDS);

function ob_tasas_bcv_load_textdomain() {
    load_plugin_textdomain('tasas-bcv-automaticas', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'ob_tasas_bcv_load_textdomain');

/**
 * Parse BCV HTML into a normalized data structure.
 *
 * @param string $html Raw HTML returned by BCV.
 * @return array|WP_Error
 */
function ob_parsear_tasas_bcv($html) {
    if (!is_string($html) || trim($html) === '') {
        return new WP_Error('bcv_empty_body', __('La respuesta del BCV está vacía.', 'tasas-bcv-automaticas'));
    }

    $dom = new DOMDocument();
    $previous_libxml_state = libxml_use_internal_errors(true);
    $loaded = $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous_libxml_state);

    if (!$loaded) {
        return new WP_Error('bcv_invalid_html', __('No se pudo interpretar la respuesta del BCV.', 'tasas-bcv-automaticas'));
    }

    $xpath = new DOMXPath($dom);
    $selectors = array(
        'USD' => 'dolar',
        'EUR' => 'euro',
        'CNY' => 'yuan',
    );
    $rates = array();

    foreach ($selectors as $currency => $id) {
        $nodes = $xpath->query("//div[@id='$id']//strong");
        if (!$nodes || $nodes->length === 0) {
            continue;
        }

        $raw = trim(preg_replace('/\s+/u', ' ', $nodes->item(0)->nodeValue));
        $normalized = str_replace(array('.', ','), array('', '.'), $raw);

        if (!is_numeric($normalized)) {
            continue;
        }

        $value = (float) $normalized;
        if ($value <= 0) {
            continue;
        }

        $rates[$currency] = $value;
    }

    if (count($rates) !== count($selectors)) {
        return new WP_Error('bcv_rates_incomplete', __('La respuesta del BCV no contiene todas las tasas esperadas.', 'tasas-bcv-automaticas'));
    }

    $date = null;
    $date_node = $xpath->query("//span[contains(concat(' ', normalize-space(@class), ' '), ' date-display-single ')]");
    if ($date_node && $date_node->length > 0) {
        $date = trim(preg_replace('/\s+/u', ' ', $date_node->item(0)->nodeValue));
    }

    return array(
        'base'       => 'VES',
        'rates'      => $rates,
        'date'       => $date,
        'fetched_at' => time(),
        'source'     => 'BCV',
        'stale'      => false,
    );
}

/**
 * Fetch and parse rates directly from BCV.
 *
 * @return array|WP_Error
 */
function ob_fetch_tasas_bcv() {
    $response = wp_remote_get('https://www.bcv.org.ve', array(
        'timeout'    => 20,
        'user-agent' => 'WordPress/Tasas-BCV 1.2.0',
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $status = (int) wp_remote_retrieve_response_code($response);
    if ($status !== 200) {
        return new WP_Error(
            'bcv_http_error',
            sprintf(__('El BCV respondió con HTTP %d.', 'tasas-bcv-automaticas'), $status),
            array('status' => $status)
        );
    }

    return ob_parsear_tasas_bcv(wp_remote_retrieve_body($response));
}

/**
 * Return fresh data when available, otherwise refresh and fall back to last valid data.
 *
 * @param bool $force_refresh Ignore the fresh cache.
 * @return array|WP_Error
 */
function ob_obtener_datos_bcv($force_refresh = false) {
    if (!$force_refresh) {
        $cached = get_transient(OB_BCV_FRESH_CACHE_KEY);
        if (is_array($cached) && !empty($cached['rates'])) {
            $cached['stale'] = false;
            return $cached;
        }
    }

    $fresh = ob_fetch_tasas_bcv();
    if (!is_wp_error($fresh)) {
        set_transient(OB_BCV_FRESH_CACHE_KEY, $fresh, OB_BCV_CACHE_TTL);
        set_transient(OB_BCV_STALE_CACHE_KEY, $fresh, OB_BCV_STALE_TTL);
        return $fresh;
    }

    $last_valid = get_transient(OB_BCV_STALE_CACHE_KEY);
    if (is_array($last_valid) && !empty($last_valid['rates'])) {
        $last_valid['stale'] = true;
        $last_valid['refresh_error'] = $fresh->get_error_code();
        return $last_valid;
    }

    return $fresh;
}

function ob_formatear_tasa_bcv($value, $decimals = 2) {
    return number_format((float) $value, (int) $decimals, ',', '.');
}

/**
 * Render the BCV shortcode.
 * Usage: [tasa_bcv] or [tasa_bcv moneda="USD" decimales="4"]
 */
function ob_obtener_tasas_bcv($atts = array()) {
    $atts = shortcode_atts(array(
        'moneda'    => '',
        'decimales' => 2,
    ), $atts, 'tasa_bcv');

    $currency = strtoupper(sanitize_text_field($atts['moneda']));
    $decimals = max(0, min(6, (int) $atts['decimales']));
    $data = ob_obtener_datos_bcv();

    if (is_wp_error($data)) {
        return '<p>' . esc_html__('Tasas temporalmente no disponibles.', 'tasas-bcv-automaticas') . '</p>';
    }

    $rates = $data['rates'];
    if ($currency !== '') {
        if (!isset($rates[$currency])) {
            return '<p>' . esc_html__('Moneda no disponible.', 'tasas-bcv-automaticas') . '</p>';
        }
        $rates = array($currency => $rates[$currency]);
    }

    $output = '<div class="bcv-widget" style="padding:12px; background:#fdfdfd; border-radius:6px; border:1px solid #e0e0e0; font-family:sans-serif;">';
    $output .= '<div style="font-weight:bold; text-align:center; margin-bottom:8px; font-size:14px; color:#333;">' . esc_html__('Tasas Oficiales BCV', 'tasas-bcv-automaticas') . '</div>';

    foreach ($rates as $code => $value) {
        $output .= "<div style='display:flex; justify-content:space-between; margin-bottom:6px; padding-bottom:4px; border-bottom:1px solid #eee;'>";
        $output .= "<span style='font-weight:600; color:#555;'>" . esc_html($code) . "</span>";
        $output .= "<span style='color:#000; font-weight:bold;'>" . esc_html(ob_formatear_tasa_bcv($value, $decimals)) . "</span>";
        $output .= '</div>';
    }

    if (!empty($data['date'])) {
        $output .= "<div style='text-align:right; font-size:11px; color:#776; margin-top:8px;'>" . sprintf(esc_html__('Fecha: %s', 'tasas-bcv-automaticas'), esc_html($data['date'])) . '</div>';
    }

    if (!empty($data['stale'])) {
        $output .= "<div style='text-align:right; font-size:11px; color:#776; margin-top:4px;'>" . esc_html__('Mostrando la última tasa válida disponible.', 'tasas-bcv-automaticas') . '</div>';
    }

    $output .= '</div>';
    return $output;
}
add_shortcode('tasa_bcv', 'ob_obtener_tasas_bcv');

/**
 * Public read-only REST endpoint: /wp-json/tasas-bcv/v1/rates
 */
function ob_registrar_rest_tasas_bcv() {
    register_rest_route('tasas-bcv/v1', '/rates', array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => 'ob_rest_tasas_bcv',
        'permission_callback' => '__return_true',
    ));
}
add_action('rest_api_init', 'ob_registrar_rest_tasas_bcv');

function ob_rest_tasas_bcv() {
    $data = ob_obtener_datos_bcv();
    if (is_wp_error($data)) {
        return new WP_Error(
            'bcv_unavailable',
            __('Tasas temporalmente no disponibles.', 'tasas-bcv-automaticas'),
            array('status' => 503)
        );
    }

    return rest_ensure_response($data);
}
