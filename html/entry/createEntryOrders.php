<?php
require_once __DIR__ . '/../config.php';

$entrySide   = $_POST['orderSide'];
$entryQty    = filter_input(INPUT_POST, 'entryQty',   FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$entryPrice  = filter_input(INPUT_POST, 'entryPrice', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$entryStep   = filter_input(INPUT_POST, 'entryStep',  FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$reduceOnly  = isset($_POST['reduceOnly']) ? true : false;

$api_key     = rw_api_key;
$secret_key  = rw_api_secret;
$url         = 'https://api.bybit.com';

$min_qty     = 0.001;
$num_slices  = (int) ceil($entryQty / $min_qty);

function http_req($endpoint, $body) {
    global $api_key, $secret_key, $url;
    $timestamp   = time() * 1000;
    $recv_window = 5000;
    $payload   = $timestamp . $api_key . $recv_window . $body;
    $signature = hash_hmac('sha256', $payload, $secret_key);

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

$endpoint = '/v5/order/create';

for ($i = 0; $i < $num_slices; $i++) {
    if ($i === $num_slices - 1) {
        $sliceQty = round($entryQty - $min_qty * ($num_slices - 1), 8);
    } else {
        $sliceQty = $min_qty;
    }

    if (strtolower($entrySide) === 'buy') {
        $price = $entryPrice - $entryStep * $i;
    } else if (strtolower($entrySide) === 'sell'){
        $price = $entryPrice + $entryStep * $i;
    }
    $price = number_format($price, 2, '.', '');

    $body = json_encode([
        "category"    => "linear",
        "symbol"      => symbol,
        "side"        => ucfirst(strtolower($entrySide)),
        "positionIdx" => 0,
        "orderType"   => "Limit",
        "qty"         => (string)$sliceQty,
        "price"       => (string)$price,
        "triggerBy"   => "LastPrice",
        "timeInForce" => "GTC",
        "reduceOnly"  => $reduceOnly
    ]);

    $resp = http_req($endpoint, $body);
    echo "Order {$i}: qty={$sliceQty} @ price={$price} (reduceOnly=" . ($reduceOnly ? 'true' : 'false') . ") → {$resp}\n";
}
header('Content-Type: application/json');
?>
