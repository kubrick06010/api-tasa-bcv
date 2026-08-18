<?php

const HOUR_IN_SECONDS = 3600;
const DAY_IN_SECONDS = 86400;
define('ABSPATH', __DIR__ . '/');

class WP_Error {
    private $code;
    private $message;

    public function __construct($code = '', $message = '', $data = null) {
        $this->code = $code;
        $this->message = $message;
    }

    public function get_error_code() {
        return $this->code;
    }

    public function get_error_message() {
        return $this->message;
    }
}

class WP_REST_Server {
    const READABLE = 'GET';
}

function __($value, $domain = null) { return $value; }
function esc_html__($value, $domain = null) { return $value; }
function add_action() {}
function add_shortcode() {}
function register_rest_route() {}
function load_plugin_textdomain() {}
function plugin_basename($path) { return basename($path); }
function is_wp_error($value) { return $value instanceof WP_Error; }
function sanitize_text_field($value) { return strip_tags((string) $value); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
function shortcode_atts($pairs, $atts, $shortcode = '') { return array_merge($pairs, (array) $atts); }
function rest_ensure_response($value) { return $value; }

function get_transient($key) {
    return false;
}

function set_transient($key, $value, $ttl) {
    return true;
}

function wp_remote_get($url, $args = []) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => $args['timeout'] ?? 20,
        CURLOPT_USERAGENT => $args['user-agent'] ?? 'WordPress/Tasas-BCV',
        CURLOPT_SSL_VERIFYPEER => $args['sslverify'] ?? true,
        CURLOPT_SSL_VERIFYHOST => ($args['sslverify'] ?? true) ? 2 : 0,
    ]);
    $body = curl_exec($ch);
    $errno = curl_errno($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $bytes = is_string($body) ? strlen($body) : 0;
    unset($ch);

    if ($body === false) {
        return new WP_Error('http_request_failed', sprintf('cURL errno %d: %s (HTTP %d, %d bytes)', $errno, $error, $status, $bytes));
    }

    return [
        'response' => ['code' => $status],
        'body' => $body,
    ];
}

function wp_remote_retrieve_response_code($response) {
    return $response['response']['code'] ?? 0;
}

function wp_remote_retrieve_body($response) {
    return $response['body'] ?? '';
}

require dirname(__DIR__) . '/tasas-bcv-automaticas.php';

$result = ob_fetch_tasas_bcv();

if (is_wp_error($result)) {
    fwrite(STDERR, "ERROR: BCV live test\n");
    fwrite(STDERR, $result->get_error_code() . ': ' . $result->get_error_message() . PHP_EOL);

    exit(1);
}

$expected = array_keys(ob_bcv_supported_selectors());
foreach ($expected as $currency) {
    if (!isset($result['rates'][$currency]) || !is_numeric($result['rates'][$currency]) || (float) $result['rates'][$currency] <= 0) {
        fwrite(STDERR, "ERROR: missing or invalid {$currency}\n");
        exit(1);
    }
}

echo "OK: BCV live test\n";
echo 'USD: ' . $result['rates']['USD'] . PHP_EOL;
echo 'EUR: ' . $result['rates']['EUR'] . PHP_EOL;
echo 'CNY: ' . $result['rates']['CNY'] . PHP_EOL;
echo 'TRY: ' . $result['rates']['TRY'] . PHP_EOL;
echo 'RUB: ' . $result['rates']['RUB'] . PHP_EOL;
echo 'Date: ' . ($result['date'] ?? '') . PHP_EOL;
echo "HTTP: 200\n";
