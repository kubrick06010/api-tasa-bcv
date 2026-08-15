<?php
/*
Plugin Name: Tasas BCV
Plugin URI: https://github.com/octaviotron/wordpress-tasa-bcv
Description: Extrae las tasas oficiales de divisas del BCV (Banco Central de Venezuela) y las muestra mediante el shortcode [tasa_bcv].
Version: 1.1.0
Author: Octavio Rossell Tabet
Author URI: https://github.com/octaviotron
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: tasas-bcv-automaticas
Domain Path: /languages
*/

if (!defined('ABSPATH')) exit;

/**
 * Cargar el dominio de texto para traducciones.
 */
function ob_tasas_bcv_load_textdomain() {
    load_plugin_textdomain('tasas-bcv-automaticas', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'ob_tasas_bcv_load_textdomain');

/**
 * Función principal para obtener las tasas.
 */
function ob_obtener_tasas_bcv() {
    $cached_output = get_transient('bcv_widget_cache');
    if ($cached_output !== false) {
        return $cached_output;
    }

    $response = wp_remote_get('https://www.bcv.org.ve', array(
        'timeout'    => 20,
        'sslverify'  => false,
        'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
    ));

    if (is_wp_error($response)) {
        return '<p>' . esc_html__('Tasas temporalmente no disponibles.', 'tasas-bcv-automaticas') . '</p>';
    }

    $html = wp_remote_retrieve_body($response);
    $dom = new DOMDocument();
    
    // Captura del estado previo de libxml para asegurar una restauración completa
    $previous_libxml_state = libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous_libxml_state);

    $xpath = new DOMXPath($dom);

    $monedas = array(
        'dolar' => esc_html__('USD', 'tasas-bcv-automaticas'),
        'euro'  => esc_html__('EUR', 'tasas-bcv-automaticas'),
        'yuan'  => esc_html__('CNY', 'tasas-bcv-automaticas'),
    );

    $output = '<div class="bcv-widget" style="padding:12px; background:#fdfdfd; border-radius:6px; border:1px solid #e0e0e0; font-family:sans-serif;">';
    $output .= '<div style="font-weight:bold; text-align:center; margin-bottom:8px; font-size:14px; color:#333;">' . esc_html__('Tasas Oficiales BCV', 'tasas-bcv-automaticas') . '</div>';
    
    $encontrados = false;
    foreach ($monedas as $id => $label) {
        $query = "//div[@id='$id']//strong";
        $nodes = $xpath->query($query);
        
        if ($nodes && $nodes->length > 0) {
            $valor_raw = trim(preg_replace('/\s+/', ' ', $nodes->item(0)->nodeValue));
            $valor_float = str_replace(',', '.', $valor_raw);
            $valor = number_format((float)$valor_float, 2, ',', '.');

            $output .= "<div style='display:flex; justify-content:space-between; margin-bottom:6px; padding-bottom:4px; border-bottom:1px solid #eee;'>
                            <span style='font-weight:600; color:#555;'>" . esc_html($label) . "</span>
                            <span style='color:#000; font-weight:bold;'>" . esc_html($valor) . "</span>
                        </div>";
            $encontrados = true;
        }
    }

    if (!$encontrados) {
        return '<p>' . esc_html__('No se pudieron extraer las tasas.', 'tasas-bcv-automaticas') . '</p>';
    }

    $fecha_node = $xpath->query("//span[contains(@class, 'date-display-single')]");
    if ($fecha_node && $fecha_node->length > 0) {
        $fecha_texto = trim(preg_replace('/\s+/', ' ', $fecha_node->item(0)->nodeValue));
        $output .= "<div style='text-align:right; font-size:11px; color:#776; margin-top:8px;'>" . sprintf(esc_html__('Fecha: %s', 'tasas-bcv-automaticas'), esc_html($fecha_texto)) . "</div>";
    }

    $output .= '</div>';

    set_transient('bcv_widget_cache', $output, 14400);

    return $output;
}

// Shortcode y filtros
add_shortcode('tasa_bcv', 'ob_obtener_tasas_bcv');
add_filter('widget_text', 'do_shortcode');
add_filter('widget_block_content', 'do_shortcode');

