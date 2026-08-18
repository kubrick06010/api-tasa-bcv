<?php
/**
 * Lightweight smoke tests for the plugin without booting WordPress.
 * Run with: php tests/smoke.php
 */
const HOUR_IN_SECONDS = 3600;
const DAY_IN_SECONDS = 86400;
define('ABSPATH', __DIR__ . '/');

class WP_Error {
    private $code;
    private $data;
    public function __construct($code = '', $message = '', $data = null) { $this->code = $code; $this->data = $data; }
    public function get_error_code() { return $this->code; }
    public function get_error_data() { return $this->data; }
}
class WP_REST_Server { const READABLE = 'GET'; }

$GLOBALS['ob_test_transients'] = array();
$GLOBALS['ob_test_remote_response'] = null;
function __($value, $domain = null) { return $value; }
function esc_html__($value, $domain = null) { return $value; }
function add_action() {}
function add_shortcode() {}
function register_rest_route() {}
function load_plugin_textdomain() {}
function plugin_basename($path) { return basename($path); }
function home_url($path = '/') { return 'https://example.test' . $path; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function sanitize_text_field($value) { return strip_tags((string) $value); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function shortcode_atts($pairs, $atts, $shortcode = '') { return array_merge($pairs, (array) $atts); }
function get_transient($key) { return $GLOBALS['ob_test_transients'][$key] ?? false; }
function set_transient($key, $value, $ttl) { $GLOBALS['ob_test_transients'][$key] = $value; return true; }
function wp_remote_get($url, $args) { $GLOBALS['ob_test_last_remote_args'] = $args; return $GLOBALS['ob_test_remote_response']; }
function wp_remote_retrieve_response_code($response) { return $response['response']['code'] ?? 0; }
function wp_remote_retrieve_body($response) { return $response['body'] ?? ''; }
function rest_ensure_response($value) { return $value; }

require dirname(__DIR__) . '/tasas-bcv-automaticas.php';

function ob_test_assert($condition, $message) {
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

ob_test_assert(ob_formatear_tasa_bcv(36.1234, 4) === '36,1234', 'number formatting');

$fresh = array(
    'base' => 'VES',
    'rates' => array('USD' => 36.1234, 'EUR' => 39.5012, 'CNY' => 4.995, 'TRY' => 16.1446144, 'RUB' => 9.10428493),
    'date' => '16/08/2026',
    'fetched_at' => 123,
    'source' => 'BCV',
    'stale' => false,
);
$GLOBALS['ob_test_transients'][OB_BCV_FRESH_CACHE_KEY] = $fresh;
$data = ob_obtener_datos_bcv();
ob_test_assert($data['rates']['USD'] === 36.1234 && $data['rates']['EUR'] === 39.5012 && $data['rates']['CNY'] === 4.995 && $data['rates']['TRY'] === 16.1446144 && $data['rates']['RUB'] === 9.10428493 && $data['stale'] === false, 'fresh cache contains all five currencies');

$rest = ob_rest_tasas_bcv();
ob_test_assert(is_array($rest) && $rest['rates']['EUR'] === 39.5012, 'REST returns valid rates');
ob_test_assert($rest['rates']['TRY'] === 16.1446144 && $rest['rates']['RUB'] === 9.10428493, 'REST returns TRY and RUB');

$html = ob_obtener_tasas_bcv(array('moneda' => 'USD', 'decimales' => 4));
ob_test_assert(strpos($html, '36,1234') !== false && strpos($html, 'USD') !== false, 'shortcode currency and decimals');
ob_test_assert(strpos(ob_obtener_tasas_bcv(array('moneda' => 'USD')), 'USD') !== false, 'shortcode supports USD');
$all_html = ob_obtener_tasas_bcv();
foreach (array('USD', 'EUR', 'CNY', 'TRY', 'RUB') as $currency) {
    ob_test_assert(strpos($all_html, $currency) !== false, "default shortcode includes {$currency}");
}
ob_test_assert(strpos(ob_obtener_tasas_bcv(array('moneda' => 'TRY')), 'TRY') !== false, 'shortcode supports TRY');
ob_test_assert(strpos(ob_obtener_tasas_bcv(array('moneda' => 'RUB')), 'RUB') !== false, 'shortcode supports RUB');

unset($GLOBALS['ob_test_transients'][OB_BCV_FRESH_CACHE_KEY]);
$GLOBALS['ob_test_transients'][OB_BCV_STALE_CACHE_KEY] = $fresh;
$GLOBALS['ob_test_remote_response'] = new WP_Error('timeout');
$data = ob_obtener_datos_bcv();
ob_test_assert(!empty($data['stale']) && $data['refresh_error'] === 'timeout', 'stale fallback');
ob_test_assert(isset($GLOBALS['ob_test_last_remote_args']['sslverify']) && $GLOBALS['ob_test_last_remote_args']['sslverify'] === false, 'BCV-only temporary TLS workaround');

$GLOBALS['ob_test_transients'] = array();
$GLOBALS['ob_test_remote_response'] = new WP_Error('timeout');
$rest_error = ob_rest_tasas_bcv();
ob_test_assert(is_wp_error($rest_error) && $rest_error->get_error_code() === 'bcv_unavailable', 'REST returns 503 when no valid data exists');
ob_test_assert($rest_error->get_error_data()['status'] === 503, 'REST unavailable status');

$GLOBALS['ob_test_transients'] = array();
$GLOBALS['ob_test_remote_response'] = array('response' => array('code' => 503), 'body' => 'unavailable');
$error = ob_fetch_tasas_bcv();
ob_test_assert(is_wp_error($error) && $error->get_error_code() === 'bcv_http_error', 'non-200 HTTP validation');

if (class_exists('DOMDocument')) {
    $sample = '<html><body>'
        . '<div id="dolar"><strong>36,12340000</strong></div>'
        . '<div id="euro"><strong>39,50120000</strong></div>'
        . '<div id="yuan"><strong>4,99500000</strong></div>'
        . '<div id="lira"><strong>16,14461440</strong></div>'
        . '<div id="rublo"><strong>9,10428493</strong></div>'
        . '<span class="date-display-single">16/08/2026</span>'
        . '</body></html>';
    $parsed = ob_parsear_tasas_bcv($sample);
    ob_test_assert(!is_wp_error($parsed), 'DOM parser returns rates');
    ob_test_assert(abs($parsed['rates']['USD'] - 36.1234) < 0.000001, 'USD parser normalization');
    ob_test_assert(abs($parsed['rates']['EUR'] - 39.5012) < 0.000001, 'EUR parser normalization');
    ob_test_assert(abs($parsed['rates']['CNY'] - 4.995) < 0.000001, 'CNY parser normalization');
    ob_test_assert(abs($parsed['rates']['TRY'] - 16.1446144) < 0.000001, 'TRY parser normalization');
    ob_test_assert(abs($parsed['rates']['RUB'] - 9.10428493) < 0.000001, 'RUB parser normalization');

    $real_shape = '<div id="euro"><div><strong>894,49018618</strong></div></div>'
        . '<div id="yuan"><div><strong>114,60548294</strong></div></div>'
        . '<div id="dolar"><div><strong>772,54410000</strong></div></div>';
    $real_shape .= '<div id="lira"><div><strong>16,14461440</strong></div></div>'
        . '<div id="rublo"><div><strong>9,10428493</strong></div></div>';
    $real_shape_result = ob_parsear_tasas_bcv($real_shape);
    ob_test_assert(!is_wp_error($real_shape_result), 'current BCV HTML shape');
    ob_test_assert(abs($real_shape_result['rates']['USD'] - 772.5441) < 0.000001, 'current BCV USD normalization');
    ob_test_assert(abs($real_shape_result['rates']['EUR'] - 894.49018618) < 0.000001, 'current BCV EUR normalization');
    ob_test_assert(abs($real_shape_result['rates']['CNY'] - 114.60548294) < 0.000001, 'current BCV CNY normalization');
    ob_test_assert(abs($real_shape_result['rates']['TRY'] - 16.1446144) < 0.000001, 'current BCV TRY normalization');
    ob_test_assert(abs($real_shape_result['rates']['RUB'] - 9.10428493) < 0.000001, 'current BCV RUB normalization');

    ob_test_assert(ob_normalizar_tasa_bcv('1.234,5678') === '1234.5678', 'mixed thousands and decimal normalization');

    $partial = '<html><body>'
        . '<div id="dolar"><strong>36,12340000</strong></div>'
        . '<div id="yuan"><strong>4,99500000</strong></div>'
        . '<div id="lira"><strong>16,14461440</strong></div>'
        . '<div id="rublo"><strong>9,10428493</strong></div>'
        . '</body></html>';
    $partial_result = ob_parsear_tasas_bcv($partial);
    ob_test_assert(is_wp_error($partial_result), 'partial BCV response returns WP_Error');
    ob_test_assert($partial_result->get_error_code() === 'bcv_rates_incomplete', 'partial BCV response error code');

    foreach (array_keys(ob_bcv_supported_selectors()) as $missing_currency) {
        $incomplete_rates = $fresh['rates'];
        unset($incomplete_rates[$missing_currency]);
        ob_test_assert(!ob_bcv_rates_are_complete(array('rates' => $incomplete_rates)), "validation rejects missing {$missing_currency}");
    }
} else {
    fwrite(STDOUT, "SKIP: DOM parser test (ext-dom not installed)\n");
}

fwrite(STDOUT, "OK: BCV plugin smoke tests\n");
