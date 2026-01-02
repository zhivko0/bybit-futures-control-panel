<?php
require_once __DIR__ . '/config.php';

$orderId=$_POST['orderId'];

$api_key=rw_api_key;
$secret_key=rw_api_secret;
$url='https://api.bybit.com';
$curl = curl_init();
function http_req($endpoint,$method,$params){
    global $api_key, $secret_key, $url, $curl;
    $timestamp = time() * 1000;
    $params_for_signature= $timestamp . $api_key . "5000" . $params;
    $signature = hash_hmac('sha256', $params_for_signature, $secret_key);
    if($method=="GET") { $endpoint=$endpoint . "?" . $params; }
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
    if($method=="GET") { curl_setopt($curl, CURLOPT_HTTPGET, true); }
    $response = curl_exec($curl);
    echo $response;
}

$endpoint='/v5/order/cancel';
$method='POST';
$params='{"category":"linear", "symbol":"' . symbol . '", "orderId":"' . $orderId . '"}';
http_req("$endpoint","$method","$params");

curl_close($curl);
header('Content-Type: application/json');
?>
