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


try {
 

//做多
    // $result=$bybit->order2()->postCreate([
    //     'category'=>'linear',
    //     'action'=>'open',
    //     // 'basePrice'=>'42742.2',
    //     'closeOnTrigger'=>false,
    //     'coin'=>'BTC',
    //     'leverage'=>'95',
    //     'leverageE2'=>'9500',
    //     'needGeneratePid'=>true,
    //     'orderType'=>'Market',
    //     'positionIdx'=> 0,
    //     'preCreateId'=>'',
    //     // 'price'=>'42742.2',
    //     'qty'=>'0.002',
    //     'qtyType'=>0,
    //     'qtyTypeValue'=>0,
    //     'qtyX'=>"200000",
    //     'reduceOnly'=>false,
    //     'side'=>"Buy",
    //     // 'slOrderType'=>"Market",
    //     'slTriggerBy'=>"LastPrice",
    //     // 'stopLoss'=>"42600.1",
    //     'symbol'=>"BTCUSDT",
    //     'takeProfit'=>"",
    //     'timeInForce'=>"ImmediateOrCancel",
    //     // 'tpOrderType'=>"Market",
    //     // 'tpSlMode'=>"Full",
    //     'tpTriggerBy'=>"LastPrice",
    //     'triggerBy'=>"LastPrice",
    //     'triggerPrice'=>"",
    //     'type'=>"Activity",

    // ]);



//做空
    // $result=$bybit->order2()->postCreate([
    //     'category'=>'linear',
    //     'action'=>'open',
    //     'basePrice'=>'42803.2',
    //     'closeOnTrigger'=>false,
    //     'coin'=>'BTC',
    //     'leverage'=>'95',
    //     'leverageE2'=>'9500',
    //     'needGeneratePid'=>true,
    //     'orderType'=>'Market',
    //     'positionIdx'=> 0,
    //     'preCreateId'=>'',
    //     'price'=>'42803.2',
    //     'qty'=>'0.002',
    //     'qtyType'=>0,
    //     'qtyTypeValue'=>0,
    //     'qtyX'=>"200000",
    //     'reduceOnly'=>false,
    //     'side'=>"Sell",
    //     // 'slOrderType'=>"Market",
    //     'slTriggerBy'=>"LastPrice",
    //     'stopLoss'=>"42900.6",
    //     'symbol'=>"BTCUSDT",
    //     'takeProfit'=>"",
    //     'timeInForce'=>"ImmediateOrCancel",
    //     // 'tpOrderType'=>"Market",
    //     // 'tpSlMode'=>"Full",
    //     'tpTriggerBy'=>"LastPrice",
    //     'triggerBy'=>"LastPrice",
    //     'triggerPrice'=>"",
    //     'type'=>"Activity",

    // ]);


//=============================================
//close 做多
    // $result=$bybit->cancel()->postCancel([
    //     'category'=>'linear',
    //     'action'=>'PositionClose',
    //     'closeOnTrigger'=>true,
    //     'createType'=>'CreateByClosing',
    //     'leverage'=>'95',
    //     'leverageE2'=>'9500',
    //     'orderType'=>'Market',
    //     'positionIdx'=> '0',
    //     'price'=>'0',
    //     'qty'=>'0.002',
    //     'qtyX'=>"200000",
    //     'side'=>"Sell",
    //     'symbol'=>"BTCUSDT",
    //     'timeInForce'=>"GoodTillCancel",
    //     'type'=>"Activity",
    // ]);

//close 做空
    // $result=$bybit->cancel()->postCancel([
    //     'category'=>'linear',
    //     'action'=>'PositionClose',
    //     'closeOnTrigger'=>true,
    //     'createType'=>'CreateByClosing',
    //     'leverage'=>'95',
    //     'leverageE2'=>'9500',
    //     'orderType'=>'Market',
    //     'positionIdx'=> '0',
    //     'price'=>'0',
    //     'qty'=>'0.002',
    //     'qtyX'=>"200000",
    //     'side'=>"Buy",
    //     'symbol'=>"BTCUSDT",
    //     'timeInForce'=>"GoodTillCancel",
    //     'type'=>"Activity",
    // ]);













    $stopLoss="42237";
   
        $result=$bybit->order2()->postCreate([
            'category'=>'linear',
            'action'=>'open',
            // 'basePrice'=>'42803.2',
            'closeOnTrigger'=>false,
            'coin'=>'BTC',
            'leverage'=>'100',
            'leverageE2'=>'10000',
            'needGeneratePid'=>true,
            'orderType'=>'Market',
            'positionIdx'=> 0,
            'preCreateId'=>'',
            // 'price'=>'42803.2',
            'qty'=>'0.001',
            'qtyType'=>0,
            'qtyTypeValue'=>0,
            'qtyX'=>"100000",
            'reduceOnly'=>false,
            'side'=>"Sell",
            // 'slOrderType'=>"Market",
            'slTriggerBy'=>"LastPrice",
            'stopLoss'=>(string)$stopLoss,
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



    print_r($result);
}catch (\Exception $e){
    print_r($e->getMessage());
}

