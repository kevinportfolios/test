<?php
/**
 * @author lin <465382251@qq.com>
 * */
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';

$bybit=new BybitV5($key,$secret);

//You can set special needs
$bybit->setOptions([
    //Set the request timeout to 60 seconds by default
    'timeout'=>10,

    'headers'=>[
        //X-Referer or Referer - 經紀商用戶專用的頭參數
        //X-BAPI-RECV-WINDOW 默認值為5000
        //cdn-request-id
        'X-BAPI-RECV-WINDOW'=>'6000',
    ]
]);

$result=$bybit->order2()->postCreate([
    'category'=>'linear',
    'action'=>'open',
    // 'basePrice'=>'42838.8',
    'closeOnTrigger'=>false,
    'coin'=>'BTC',
    'leverage'=>'100',
    'leverageE2'=>'10000',
    'needGeneratePid'=>true,
    'orderType'=>'Market',
    'positionIdx'=> 0,
    'preCreateId'=>'',
    // 'price'=>'42838.8',
    'qty'=>'0.001',
    'qtyType'=>0,
    'qtyTypeValue'=>0,
    //'qtyX'=>"100000",
    'reduceOnly'=>false,
    'side'=>"Buy",
    // 'slOrderType'=>"Market",
    'slTriggerBy'=>"LastPrice",
    // 'stopLoss'=> (string)$stopLoss,
    'symbol'=>"BTCUSDT",
    'takeProfit'=>"",
    'timeInForce'=>"ImmediateOrCancel",
    // 'tpOrderType'=>"Market",
    // 'tpSlMode'=>"Full",
    'tpTriggerBy'=>"LastPrice",
    'triggerBy'=>"LastPrice",
    'triggerPrice'=>"",
    'type'=>"Activity",

]);

if($result["retCode"]!=0){
    $error = 'order 做多 error: '.$result["retCode"].';'.$result["retMsg"];
    logger2($error);
}else{
    $position="";
    $canbuy=0;
}


// try {
//     $result=$bybit->order()->postCreate([
//         'category'=>'spot',
//         'symbol'=>'BTCUSDT',
//         'side'=>'buy',
//         'orderType'=>'market',
//         'qty'=>'1',
//         // 'price'=>'38000',
//         // 'triggerPrice'=>'37000',
//         'timeInForce'=>'IOC',
//         'orderLinkId'=>'spot-kevin-09',
//         'isLeverage'=> 1,
//         // 'orderFilter'=>'tpslOrder',

//         //'orderLinkId'=>'xxxxxxxxxxx',
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }


// try {
//     $result=$bybit->order()->postCreate([
//         'category'=>'spot',
//         'symbol'=>'BTCUSDT',
//         'side'=>'buy',
//         'orderType'=>'limit',
//         'qty'=>'1',
//         // 'price'=>'1000',

//         //'orderLinkId'=>'xxxxxxxxxxx',
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }



// try {
//     $result=$bybit->position()->getList([
//         'category'=>'linear',
//         'symbol'=>'BTCUSDT',

//         // 'orderId'=>'xxxxxxxxxx',
//         //'orderLinkId'=>'xxxxxxxxxxx',
//     ]);
//     // print_r($result["result"]["list"][0]["avgPrice"]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }


// try {
//     $result=$bybit->order()->getRealTime([
//         'category'=>'linear',
//         'symbol'=>'BTCUSDT',

//         // 'orderId'=>'xxxxxxxxxx',
//         //'orderLinkId'=>'xxxxxxxxxxx',
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }


// try {
//     $result=$bybit->order()->postCancelAll([
//         'category'=>'linear',
//         'symbol'=>'BTCUSDT',
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }



// try {
//     $result=$bybit->order()->getSpotBorrowCheck([
//         'category'=>'spot',
//         'symbol'=>'BTCUSDT',
//         'side'=>'by'
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }








// try {
//     $getKline=$bybit->market()->getKline([
//         'category'=>'spot',
//         'symbol'=>'BTCUSDT',
//         'interval'=>'5',
//         'limit'=>'20',
//     ]);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }



// $closePrice10=array();
// $closePrice20=array();
// foreach ($getKline["result"]["list"] as $r) {
//     $closePrice20[]=$r["4"];
// }
// $closePrice10 = array_slice($closePrice20, 0, -10);

// $latestPrice = $closePrice20[0];
// echo $latestPrice;



