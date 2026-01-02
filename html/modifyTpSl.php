<?php
require_once __DIR__ . '/config.php';

$tpPrice=filter_input(INPUT_POST, 'tpPrice', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$slPrice=filter_input(INPUT_POST, 'slPrice', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$trailingStop=filter_input(INPUT_POST, 'trailingStop', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;
$tsActivePrice=filter_input(INPUT_POST, 'tsActivePrice', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;

$api_key=rw_api_key;
$secret_key=rw_api_secret;
$url='https://api.bybit.com';
$curl = curl_init();
function http_req($endpoint,$method,$params){
    global $api_key, $secret_key, $url, $curl;
    $timestamp = time() * 1000;
    $params_for_signature= $timestamp . $api_key . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $secret_key);
    if($method=='GET') { $endpoint=$endpoint . "?" . $params; }
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url . $endpoint,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_HTTPHEADER => array(
          "X-BAPI-API-KEY: $api_key",
          "X-BAPI-SIGN: $signature",
          "X-BAPI-SIGN-TYPE: 2",
          "X-BAPI-TIMESTAMP: $timestamp",
          "X-BAPI-RECV-WINDOW: 5000",
          "Content-Type: application/json"
        ),
      ));
    if($method=='GET') { curl_setopt($curl, CURLOPT_HTTPGET, true); }
    $response = curl_exec($curl);
    echo $response;
}

if (isset($tpPrice) && $tpPrice !== null) {
  $endpoint='/v5/position/trading-stop';
  $method='POST';
  $params='{"category":"linear", "symbol":"' . symbol . '", "positionIdx":"0", "takeProfit":"' . $tpPrice . '", "tpTriggerBy":"LastPrice"}';
  http_req("$endpoint","$method","$params");
}

if (isset($slPrice) && $slPrice !== null) {
  $endpoint='/v5/position/trading-stop';
  $method='POST';
  $params='{"category":"linear", "symbol":"' . symbol . '", "positionIdx":"0", "stopLoss":"' . $slPrice . '", "slTriggerBy":"LastPrice"}';
  http_req("$endpoint","$method","$params");
}

if (isset($trailingStop) && $trailingStop !== null) {
  $endpoint='/v5/position/trading-stop';
  $method='POST';
  $params='{"category":"linear", "symbol":"' . symbol . '", "positionIdx":"0", "trailingStop":"' . $trailingStop . '", "activePrice":"' . $tsActivePrice . '"}';
  http_req("$endpoint","$method","$params");
}

curl_close($curl);
header('Content-Type: application/json');
?>
