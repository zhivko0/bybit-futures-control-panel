<?php
require_once __DIR__ . '/../config.php';

$api_key    = rw_api_key;
$secret_key = rw_api_secret;
$url        = 'https://api.bybit.com';

function http_req($endpoint, $body) {
    global $api_key, $secret_key, $url;
    $timestamp   = time() * 1000;
    $recv_window = 5000;
    $payload     = $timestamp . $api_key . $recv_window . $body;
    $signature   = hash_hmac('sha256', $payload, $secret_key);

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL            => $url . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            "Content-Type: application/json",
            "X-BAPI-API-KEY: {$api_key}",
            "X-BAPI-SIGN: {$signature}",
            "X-BAPI-SIGN-TYPE: 2",
            "X-BAPI-TIMESTAMP: {$timestamp}",
            "X-BAPI-RECV-WINDOW: {$recv_window}",
        ],
        CURLOPT_CUSTOMREQUEST  => 'POST',
        CURLOPT_POSTFIELDS     => $body,
    ]);

    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

$endpoint = '/v5/order/cancel-all';
$body     = json_encode([
    "category" => "linear",
    "symbol"   => symbol
]);
$resp = http_req($endpoint, $body);
echo $resp;
header('Content-Type: application/json');
?>
