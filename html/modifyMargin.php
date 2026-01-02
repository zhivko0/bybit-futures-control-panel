<?php
require_once __DIR__ . '/config.php';

$marginAmount=filter_input(INPUT_POST, 'marginAmount', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) ?? null;

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

if (isset($marginAmount)) {
  $endpoint='/v5/position/add-margin';
  $method='POST';
  $params='{"category":"linear", "symbol":"' . symbol . '", "positionIdx":"0", "margin":"' . $marginAmount . '"}';
  http_req("$endpoint","$method","$params");
}

curl_close($curl);
header('Content-Type: application/json');
?>
