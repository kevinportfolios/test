<?php
/**
 * @author lin <465382251@qq.com>
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
    'timeout'=>10,

    'headers'=>[
        //X-Referer or Referer - 經紀商用戶專用的頭參數
        //X-BAPI-RECV-WINDOW 默認值為5000
        //cdn-request-id
        'X-BAPI-RECV-WINDOW'=>'6000',
    ]
]);



$position ="";

while(1){
$time=time()+(7*3600);
$start=1596446400;





if($time>$start){
    $t=$time-$start;
    //如果要24小时执行
    //if(is_int($t/86400)){

    //方便测试每10秒执行一次
    if(is_int($t/5)){
        // echo 'Process Time:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        $processtime= 'Process Time:'.date('Y-m-d H:i:s',$time);
        logger($processtime);













try {
    //ema 10 is blue color
    //ema 20 is white color

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'spot',
            'symbol'=>'BTCUSDT',
            'interval'=>'5',
            'limit'=>'20',
        ]);
    }catch (\Exception $e){
        // print_r($e->getMessage());
        $error = 'getKline error: '.$e->getMessage();
        logger($error);
        break;
    }
   


    $closePrice10=array();
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }
    $closePrice10 = array_slice($closePrice20, 0, -10);

    // $typeMA = "SMA";
    // $smoothingLength = 5;

    // $ema10 = ema($closePrice10, 10);
    // $smoothingLine10 = ma($ema10, $smoothingLength, $typeMA);
    // $average10 = array_sum($closePrice10) / count($closePrice10);
    // echo "Average Smoothing Line 10: " . $average10;
    // echo PHP_EOL;
    ///////////////
    // $ema20 = ema($closePrice20, 20);
    // $smoothingLine20 = ma($ema20, $smoothingLength, $typeMA);
    // $average20 = array_sum($closePrice20) / count($closePrice20);
    // echo "Average Smoothing Line 20: " . $average20;
    // echo PHP_EOL;


    $average10 = calculateEMA($closePrice10, 10);
    $average20 = calculateEMA($closePrice20, 20);




    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
    // echo "finalema10: " . $finalema10;
    // echo PHP_EOL;
    // echo "finalema20: " . $finalema20;
    // echo PHP_EOL;
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema20: '.$finalema20;
    logger($finalema);

    
   
    if($finalema10>$finalema20){
        //做多
        //check got order running or not
        try {
            $getRealTime=$bybit->position()->getList([
                'category'=>'linear',
                'symbol'=>'BTCUSDT',
        
                // 'orderId'=>'xxxxxxxxxx',
                //'orderLinkId'=>'xxxxxxxxxxx',
            ]);
            // print_r($getRealTime);
        }catch (\Exception $e){
            // print_r($e->getMessage());
            $error = 'getRealTime error: '.$e->getMessage();
            logger($error);
            break;
        }

        $is_valid=1;
        if(!empty($getRealTime["result"]["list"])){
            if($getRealTime["result"]["list"][0]["avgPrice"]!=0){
                $is_valid=1;
            }else{
                $is_valid=0;
            }
        }







        if($is_valid==1){
            
            $diffema=$finalema10-$finalema20;
            if($diffema<5){
                // echo 'close 做多:'.date('Y-m-d H:i:s',$time).PHP_EOL;
                logger('close 做多:'.date('Y-m-d H:i:s',$time));


                //if got order
                //close 做多
                try {
                    $result=$bybit->cancel()->postCancel([
                        'category'=>'linear',
                        'action'=>'PositionClose',
                        'closeOnTrigger'=>true,
                        'createType'=>'CreateByClosing',
                        'leverage'=>'95',
                        'leverageE2'=>'9500',
                        'orderType'=>'Market',
                        'positionIdx'=> '0',
                        'price'=>'0',
                        'qty'=>'0.001',
                        'qtyX'=>"100000",
                        'side'=>"Sell",
                        'symbol'=>"BTCUSDT",
                        'timeInForce'=>"GoodTillCancel",
                        'type'=>"Activity",
                    ]);
                }catch (\Exception $e){
                    // print_r($e->getMessage());
                    $error = 'close 做多 error: '.$e->getMessage();
                    logger($error);
                    break;
                }
    

                $position ="long to short";

            }
          
        }else if($position =="short to long"){
            //if not,open order
             //做多
            //  echo 'order 做多:'.date('Y-m-d H:i:s',$time).PHP_EOL;
             logger('order 做多:'.date('Y-m-d H:i:s',$time));

             $stopLoss=$finalema20;

             try {
                 $result=$bybit->order2()->postCreate([
                    'category'=>'linear',
                    'action'=>'open',
                    // 'basePrice'=>'42838.8',
                    'closeOnTrigger'=>false,
                    'coin'=>'BTC',
                    'leverage'=>'95',
                    'leverageE2'=>'9500',
                    'needGeneratePid'=>true,
                    'orderType'=>'Market',
                    'positionIdx'=> 0,
                    'preCreateId'=>'',
                    // 'price'=>'42838.8',
                    'qty'=>'0.001',
                    'qtyType'=>0,
                    'qtyTypeValue'=>0,
                    'qtyX'=>"100000",
                    'reduceOnly'=>false,
                    'side'=>"Buy",
                    // 'slOrderType'=>"Market",
                    'slTriggerBy'=>"LastPrice",
                    'stopLoss'=> (string)$stopLoss,
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

            }catch (\Exception $e){
                // print_r($e->getMessage());
                $error = 'order 做多 error: '.$e->getMessage();
                logger($error);
                break;
            }

            $position="";

        }
        // echo 'running 做多:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        logger('running 做多:'.date('Y-m-d H:i:s',$time));

    }else if($finalema10<$finalema20){
        //做空
        //check got order running or not
        try {
            $getRealTime=$bybit->position()->getList([
                'category'=>'linear',
                'symbol'=>'BTCUSDT',
        
                // 'orderId'=>'xxxxxxxxxx',
                //'orderLinkId'=>'xxxxxxxxxxx',
            ]);
            // print_r($getRealTime);
        }catch (\Exception $e){
            print_r($e->getMessage());
        }
        
        $is_valid=1;
        if(!empty($getRealTime["result"]["list"])){
            if($getRealTime["result"]["list"][0]["avgPrice"]!=0){
                $is_valid=1;
            }else{
                $is_valid=0;
            }
        }

        if($is_valid==1){
            
            $diffema=$finalema20-$finalema10;
            if($diffema<5){
                // echo 'close 做空:'.date('Y-m-d H:i:s',$time).PHP_EOL;
                logger('close 做空:'.date('Y-m-d H:i:s',$time));
               //if got order
               //close 做空
                try {
                    $result=$bybit->cancel()->postCancel([
                        'category'=>'linear',
                        'action'=>'PositionClose',
                        'closeOnTrigger'=>true,
                        'createType'=>'CreateByClosing',
                        'leverage'=>'95',
                        'leverageE2'=>'9500',
                        'orderType'=>'Market',
                        'positionIdx'=> '0',
                        'price'=>'0',
                        'qty'=>'0.001',
                        'qtyX'=>"100000",
                        'side'=>"Buy",
                        'symbol'=>"BTCUSDT",
                        'timeInForce'=>"GoodTillCancel",
                        'type'=>"Activity",
                    ]);
                }catch (\Exception $e){
                    // print_r($e->getMessage());
                    $error = 'close 做空 error: '.$e->getMessage();
                    logger($error);
                    break;
                }

                $position ="short to long";
            }
           
        }else if($position =="long to short"){
            //if not,open order
            //做空
            // echo 'order 做空:'.date('Y-m-d H:i:s',$time).PHP_EOL;
            logger('order 做空:'.date('Y-m-d H:i:s',$time));
            
            $stopLoss=$finalema10;
            try {
                $result=$bybit->order2()->postCreate([
                    'category'=>'linear',
                    'action'=>'open',
                    // 'basePrice'=>'42803.2',
                    'closeOnTrigger'=>false,
                    'coin'=>'BTC',
                    'leverage'=>'95',
                    'leverageE2'=>'9500',
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
            }catch (\Exception $e){
                // print_r($e->getMessage());
                $error = 'order 做空 error: '.$e->getMessage();
                logger($error);
                break;
            }

            $position="";

        }

        // echo 'running 做空:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        logger('running 做空:'.date('Y-m-d H:i:s',$time));
    }

    logger(' ');

}catch (\Exception $e){
    print_r($e->getMessage());
}











// echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;

}
}

sleep(1);

}