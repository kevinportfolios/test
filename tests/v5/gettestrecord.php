<?php
/**
 * @author lin <465382251@qq.com>
 * */
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';

$bybit=new BybitV5();

//You can set special needs
// $bybit->setOptions([
//     //Set the request timeout to 60 seconds by default
//     'timeout'=>10,

//     'headers'=>[
//         //X-Referer or Referer - 經紀商用戶專用的頭參數
//         //X-BAPI-RECV-WINDOW 默認值為5000
//         //cdn-request-id
//         'X-BAPI-RECV-WINDOW'=>'6000',
//     ]
// ]);


try {
    $result=$bybit->market()->getKline([
        'category'=>'linear',
        'symbol'=>'BTCUSDT',
        'interval'=>'1',
        'start'=>'1710522000000',
        'end'=>'1710532800000',
        // 'limit'=>'100',
    ]);
}catch (\Exception $e){
    print_r($e->getMessage());
}


$volume =array();
   
foreach ($result["result"]["list"] as $r) {
    $timestamp_sec = $r["0"] / 1000;
    // Set the timezone to GMT+08:00
    date_default_timezone_set('Asia/Singapore');
    
    $volume[date("Y-m-d H:i:s", $timestamp_sec)]=$r["5"];
}

// testlogger(json_encode($volume));

foreach ($volume as $x => $y) {
    testlogger($x."---".$y."\r");
}

// testlogger(json_encode($getKline));
// echo json_encode($getKline);
// $result=array();
// foreach ($getKline["result"]["list"] as $r) {
//     $result[]=$r["4"];
// }

// $data = array_reverse($result);

// foreach ($data  as $d) {


//     $average10 = calculateEMA($closePrice20, 10);
//     $average20 = calculateEMA($closePrice20, 50);

//     $finalema10=$average10[0];
//     $finalema20=$average20[0];

//     testlogger("[".$finalema10.",".$finalema20."]");
// }
// [1, 2, 3],