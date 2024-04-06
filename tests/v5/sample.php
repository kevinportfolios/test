<?php
/**
 * using volume
 * 5minutes
 * */
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';

$bybit=new BybitV5($key,$secret);

//You can set special needs
$bybit->setOptions([
    //Set the request timeout to 60 seconds by default
    'timeout'=>5,

    'headers'=>[
        //X-Referer or Referer - 經紀商用戶專用的頭參數
        //X-BAPI-RECV-WINDOW 默認值為5000
        //cdn-request-id
        'X-BAPI-RECV-WINDOW'=>'6000',
    ]
]);

$currentprice=0;//当前市场价钱
$closePrice20=array();

try {
    $getKline=$bybit->market()->getKline([
        'category'=>'linear',
        'symbol'=>'BTCUSDT',
        'interval'=>'1',
        'limit'=>'100',
    ]);
}catch (\Exception $e){
    $error = 'getKline error: '.$e->getMessage();
    logger2($error);
}
   
foreach ($getKline["result"]["list"] as $r) {
    $closePrice20[]=$r["4"];
}

$currentprice=$closePrice20[0];

  
     //做多

    //  $stopLoss=$currentprice-60;

    //  try {
    //      $result=$bybit->order2()->postCreate([
    //         'category'=>'linear',
    //         'action'=>'open',
    //         // 'basePrice'=>'42838.8',
    //         'closeOnTrigger'=>false,
    //         'coin'=>'BTC',
    //         'leverage'=>'100',
    //         'leverageE2'=>'10000',
    //         'needGeneratePid'=>true,
    //         'orderType'=>'Market',
    //         'positionIdx'=> 0,
    //         'preCreateId'=>'',
    //         // 'price'=>'42838.8',
    //         'qty'=>'0.001',
    //         'qtyType'=>0,
    //         'qtyTypeValue'=>0,
    //         //'qtyX'=>"100000",
    //         'reduceOnly'=>false,
    //         'side'=>"Buy",
    //         // 'slOrderType'=>"Market",
    //         'slTriggerBy'=>"LastPrice",
    //         'stopLoss'=> (string)$stopLoss,
    //         'symbol'=>"BTCUSDT",
    //         'takeProfit'=>"",
    //         'timeInForce'=>"ImmediateOrCancel",
    //         // 'tpOrderType'=>"Market",
    //         // 'tpSlMode'=>"Full",
    //         'tpTriggerBy'=>"LastPrice",
    //         'triggerBy'=>"LastPrice",
    //         'triggerPrice'=>"",
    //         'type'=>"Activity",

    //     ]);

    //     if($result["retCode"]!=0){
    //         $error = 'order 做多 error: '.$result["retCode"].';'.$result["retMsg"];
    //         echo($error);
    //     }else{
    //         $position="";
    //         // $canbuy=0;
    //     }

     
    //     // orderlogger2('overprice:'.$overprice);

    // }catch (\Exception $e){
    //     $error = 'order 做多 error: '.$e->getMessage();
    //     echo($error);
    // }

    



    //做空
    // $stopLoss=$currentprice+60;
    // try {
    //     $result=$bybit->order2()->postCreate([
    //         'category'=>'linear',
    //         'action'=>'open',
    //         // 'basePrice'=>'42803.2',
    //         'closeOnTrigger'=>false,
    //         'coin'=>'BTC',
    //         'leverage'=>'100',
    //         'leverageE2'=>'10000',
    //         'needGeneratePid'=>true,
    //         'orderType'=>'Market',
    //         'positionIdx'=> 0,
    //         'preCreateId'=>'',
    //         // 'price'=>'42803.2',
    //         'qty'=>'0.001',
    //         'qtyType'=>0,
    //         'qtyTypeValue'=>0,
    //         //'qtyX'=>"100000",
    //         'reduceOnly'=>false,
    //         'side'=>"Sell",
    //         // 'slOrderType'=>"Market",
    //         'slTriggerBy'=>"LastPrice",
    //         'stopLoss'=>(string)$stopLoss,
    //         'symbol'=>"BTCUSDT",
    //         'takeProfit'=>"",
    //         'timeInForce'=>"ImmediateOrCancel",
    //         // 'tpOrderType'=>"Market",
    //         // 'tpSlMode'=>"Full",
    //         'tpTriggerBy'=>"LastPrice",
    //         'triggerBy'=>"LastPrice",
    //         'triggerPrice'=>"",
    //         'type'=>"Activity",

    //     ]);

    //     if($result["retCode"]!=0){
    //         $error = 'order 做空 error: '.$result["retCode"].';'.$result["retMsg"];
    //         echo($error);
    //     }

    // }catch (\Exception $e){
    //     $error = 'order 做空 error: '.$e->getMessage();
    //     echo($error);
       
    // }







        // close 做多
        // try {
        //     $result=$bybit->cancel()->postCancel([
        //         'category'=>'linear',
        //         'action'=>'PositionClose',
        //         'closeOnTrigger'=>true,
        //         'createType'=>'CreateByClosing',
        //         'leverage'=>'100',
        //         'leverageE2'=>'10000',
        //         'orderType'=>'Market',
        //         'positionIdx'=> '0',
        //         'price'=>'0',
        //         'qty'=>'0.001',
        //         //'qtyX'=>"100000",
        //         'side'=>"Sell",
        //         'symbol'=>"BTCUSDT",
        //         'timeInForce'=>"GoodTillCancel",
        //         'type'=>"Activity",
        //     ]);


        //     if($result["retCode"]!=0){
        //         $error = 'close 做多 error: '.$result["retCode"].';'.$result["retMsg"];
        //         echo($error);
        //     }
        // }catch (\Exception $e){
        //     $error = 'close 做多 error: '.$e->getMessage();
        //     echo($error);
        // }


    






        //close 做空
        // try {
        //     $result=$bybit->cancel()->postCancel([
        //         'category'=>'linear',
        //         'action'=>'PositionClose',
        //         'closeOnTrigger'=>true,
        //         'createType'=>'CreateByClosing',
        //         'leverage'=>'100',
        //         'leverageE2'=>'10000',
        //         'orderType'=>'Market',
        //         'positionIdx'=> '0',
        //         'price'=>'0',
        //         'qty'=>'0.001',
        //         //'qtyX'=>"100000",
        //         'side'=>"Buy",
        //         'symbol'=>"BTCUSDT",
        //         'timeInForce'=>"GoodTillCancel",
        //         'type'=>"Activity",
        //     ]);

        //     if($result["retCode"]!=0){
        //         $error = 'close 做空 error: '.$result["retCode"].';'.$result["retMsg"];
        //         echo($error);
        //     }
        // }catch (\Exception $e){
        //     $error = 'close 做空 error: '.$e->getMessage();
        //     echo($error);
            
        // }

    


