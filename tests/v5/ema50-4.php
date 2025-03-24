

<?php
/**
 * using ema 10 and ema 20   
 * day
 * sol
 * test at sma50c3.php
 * */
use \Lin\Bybit\BybitV5;
date_default_timezone_set('Asia/Kuala_Lumpur');

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';

// $bybit=new BybitV5($key,$secret);
$bybit=new BybitV5($argv[1],$argv[2]);
$bybit->setOptions([
    'timeout'=>300,
    'headers'=>[
        'X-BAPI-RECV-WINDOW'=>'6000',
    ]
]);

$position ="";
$action="";
$isfirstorder=1;
$firstposition="";
$earninglvl=0;
$beforeearninglvl=0;
$canclose=0;
$canbuy=0;

$buyingprice=0;//进场价钱
$overprice=0;

$currentprice=0;//当前市场价钱
$allowtoclose=0;

$diffrange=0;
$todayLowPrice =0;
$lastTradeDate = ""; // 存储最近交易日期
$currentTradeDate = "";

try {
    $getWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    logger2($error);
}

$totalAccountBalance=$getWalletBalance["result"]["list"][0]["totalWalletBalance"];
logger2("start account balance: ".$totalAccountBalance);



//aldready got order
// $isfirstorder=0;

// $action="long";
// $position ="short to long";

// $action="short";
// $position ="long to short";

// $buyingprice="43627.20";
// $overprice="46730";


while(1){
$time=time();
$start=1596446400;



if($time>$start){

$t=$time-$start;
$currentTradeDate = date('Y-m-d',$time);

//方便测试每10秒执行一次
if(is_int($t/5)){
    $processtime= 'Process Time:'.date('Y-m-d H:i:s',$time);
    logger2($processtime);

try {
    //ema 10 is blue color
    //ema 100 is white color

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'SOLUSDT',
            'interval'=>'1',
            // 'start'=>$starttime,
            // 'end'=>$endtime,
            'limit'=>'20',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        logger2($error);
        break;
    }

    //calcuate ema 
    try {
        $getKline2=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'SOLUSDT',
            'interval'=>'D',
            // 'start'=>$dayStartTime,
            // 'end'=>$dayEndTime,
            'limit'=>'20',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        logger2($error);
        break;
    }
   

    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }

    $closePrice202=array();
    foreach ($getKline2["result"]["list"] as $r) {
        $closePrice202[]=$r["4"];
    }
    // $closePrice202 = array_reverse($closePrice202);

    $dayLowPrice =array();
    foreach ($getKline2["result"]["list"] as $r) {
        $dayLowPrice[]=$r["3"];
    }
    $todayLowPrice = $dayLowPrice["0"];

    // $priceRecord20 = 'closePrice20: ' . json_encode($closePrice20);
    // $priceRecord202 = 'closePrice202: ' . json_encode($closePrice202);
    // logger2($priceRecord20);
    // logger2($priceRecord202);
    // logger2('dayLowPrice: ' . json_encode($dayLowPrice));

    $average10 = calculateEMA($closePrice202, 10);
    $average20 = calculateEMA($closePrice202, 20);

    $currentprice=$closePrice20[0];

    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema20: '.$finalema20.' , '.'currentprice: '.$currentprice;
    // echo($finalema);
    // echo PHP_EOL; echo PHP_EOL; 
    logger2($finalema);

   
    //checking for first trade
    // if(($finalema10>$finalema20)&&$firstposition==""&&$isfirstorder==1){
    //     $firstposition="long";
    // }else if(($finalema10<$finalema20)&&$firstposition==""&&$isfirstorder==1){
    //     $firstposition="short";
    // }


    // // if($firstposition=="long"&&($finalema10<$finalema20)&&$isfirstorder==1){
    // if($firstposition=="long"&&($finalema20<$todayLowPrice)&&$isfirstorder==1){
    //     $isfirstorder=0;
    //     $position ="long to short";
    //     logger2("start order");
    //     $ordertime= 'Process Time:'.date('Y-m-d H:i:s',$time);
    //     logger2($ordertime);
    // }else if($firstposition=="short"&&($todayLowPrice>$finalema20)&&$isfirstorder==1){
    // // }else if($firstposition=="short"&&($finalema10>$finalema20)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="short to long";
        // logger2("start order");
        // $ordertime= 'Process Time:'.date('Y-m-d H:i:s',$time);
        // logger2($ordertime);
    // }


    //start trade
    if(($isfirstorder==0) && ($currentprice<130)){
        //check got order running or not
        try {
            $getRealTime=$bybit->position()->getList([
                'category'=>'linear',
                'symbol'=>'BTCUSDT',
            ]);
        }catch (\Exception $e){
            $error = 'getRealTime error: '.$e->getMessage();
            logger2($error);
            break;
        }

        $is_running_order=1;
        if(!empty($getRealTime["result"]["list"])){
            if($getRealTime["result"]["list"][0]["avgPrice"]!=0){
                $is_running_order=1;
            }else{
                $is_running_order=0;
                
            }
        }

        if($is_running_order==0){
            //order
            if($position==""){
                logger2('order meet the stoplose');
                orderlogger2('order meet the stoplose:'.date('Y-m-d H:i:s',$time));
                orderlogger2('============================');
                $isfirstorder=0;
                $firstposition="";
                if($action == "long"){
                    $position ="long to short";
                }else if($action == "short"){
                    $position = "short to long";
                }
                logger2('position:'.$position.';action:'.$action);
            }else{
                // if($finalema10>($finalema20)&&($currentprice>$finalema20)){
                // if($finalema10>($finalema20)&&($todayLowPrice>$finalema20)&&(($todayLowPrice - $finalema20)<15)&&($lastTradeDate!=$currentTradeDate)){
                if(($todayLowPrice>$finalema20)&&(($todayLowPrice - $finalema20)<15)&&($lastTradeDate!=$currentTradeDate)){
                    //做多
                    logger2('position:'.$position);
                    if($position !=""){
                        $buyingprice=$closePrice20[0];
                        //if not,open order
                        //做多
                        logger2('order 做多:'.date('Y-m-d H:i:s',$time));
                        $stopLoss=$finalema10-10;
                        try {
                             $result=$bybit->order2()->postCreate([
                                'category'=>'linear',
                                'action'=>'open',
                                // 'basePrice'=>'42838.8',
                                'closeOnTrigger'=>false,
                                'coin'=>'SOL',
                                'leverage'=>'100',
                                'leverageE2'=>'10000',
                                'needGeneratePid'=>true,
                                'orderType'=>'Market',
                                'positionIdx'=> 0,
                                'preCreateId'=>'',
                                // 'price'=>'42838.8',
                                'qty'=>'0.1',
                                'qtyType'=>0,
                                'qtyTypeValue'=>0,
                                //'qtyX'=>"100000",
                                'reduceOnly'=>false,
                                'side'=>"Buy",
                                // 'slOrderType'=>"Market",
                                'slTriggerBy'=>"LastPrice",
                                'stopLoss'=> (string)$stopLoss,
                                'symbol'=>"SOLUSDT",
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

                            orderlogger2('making order 做多:'.date('Y-m-d H:i:s',$time));
                            orderlogger2('buyingprice:'.$buyingprice);
                            orderlogger2('stopLoss:'.$stopLoss);
                            $lastTradeDate = date('Y-m-d',$time);
                            // orderlogger2('overprice:'.$overprice);

                        }catch (\Exception $e){
                            $error = 'order 做多 error: '.$e->getMessage();
                            logger2($error);
                            break;
                        }
                        $action="long";
                    }
                    logger2('running order 做多:'.date('Y-m-d H:i:s',$time));

                // }else if(($finalema10)<$finalema20&&($currentprice<$finalema20)){
                // }else if(($finalema10)<$finalema20&&($todayLowPrice<$finalema20)&&(($finalema20 - $todayLowPrice)<15)&&($lastTradeDate!=$currentTradeDate)){
                }else if(($todayLowPrice<$finalema20)&&(($finalema20 - $todayLowPrice)<15)&&($lastTradeDate!=$currentTradeDate)){
                    //做空
                    logger2('position:'.$position);

                    if($position !=""){
                        $buyingprice=$closePrice20[0];
                        //if not,open order
                        //做空
                        logger2('order 做空:'.date('Y-m-d H:i:s',$time));
                        $stopLoss=$finalema10+10;
                        try {
                            $result=$bybit->order2()->postCreate([
                                'category'=>'linear',
                                'action'=>'open',
                                // 'basePrice'=>'42803.2',
                                'closeOnTrigger'=>false,
                                'coin'=>'SOL',
                                'leverage'=>'100',
                                'leverageE2'=>'10000',
                                'needGeneratePid'=>true,
                                'orderType'=>'Market',
                                'positionIdx'=> 0,
                                'preCreateId'=>'',
                                // 'price'=>'42803.2',
                                'qty'=>'0.1',
                                'qtyType'=>0,
                                'qtyTypeValue'=>0,
                                //'qtyX'=>"100000",
                                'reduceOnly'=>false,
                                'side'=>"Sell",
                                // 'slOrderType'=>"Market",
                                'slTriggerBy'=>"LastPrice",
                                'stopLoss'=>(string)$stopLoss,
                                'symbol'=>"SOLUSDT",
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
                                $error = 'order 做空 error: '.$result["retCode"].';'.$result["retMsg"];
                                logger2($error);
                            }else{
                                $position="";
                                $canbuy=0;
                            }


                            orderlogger2('making order 做空:'.date('Y-m-d H:i:s',$time));
                            orderlogger2('buyingprice:'.$buyingprice);
                            orderlogger2('stopLoss:'.$stopLoss);
                            $lastTradeDate = date('Y-m-d',$time);
                            // orderlogger2('overprice:'.$overprice);


                        }catch (\Exception $e){
                            $error = 'order 做空 error: '.$e->getMessage();
                            logger2($error);
                            break;
                        }

                        $action="short";
                    }

                    logger2('running order 做空:'.date('Y-m-d H:i:s',$time));

                }
            }

        }else if($is_running_order==1){
            //cancel

            if($action=="long"){
                //close 做多

                $diffprice =  $currentprice-$buyingprice;
                $beforeearninglvl=$earninglvl;

                $earninglvl= getearninglvlsol($diffprice,$earninglvl);

                if($beforeearninglvl!=0){
                    $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                    logger2($earninglvllog);

                    if($beforeearninglvl>$earninglvl){
                        $canclose+=1;
                    }
               
                    if($canclose>1){
                        logger2('diffprice>200 long:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        $allowtoclose =1;
                        $firstposition="";
                        $isfirstorder=0;
                        $canclose=0;
                        $earninglvl=0;
                        $beforeearninglvl=0;
                    }
                }

                //closed 当亏50%
                // if(($buyingprice-$currentprice)>200){
                //     $allowtoclose =1;
                //     logger2('closing order 做多:lose 50%:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                // }

                // if(($finalema10<$finalema20))
                if(($finalema20>$todayLowPrice)){
                    $allowtoclose =1;
                    logger2('closing order 做多:$finalema10<$finalema20');
                }
                // if(($currentprice<($finalema20-10))){
                //     $allowtoclose =1;
                //     logger2('closing order 做多:over sma50');
                // }
                if($stopLoss>$currentprice){
                    $allowtoclose =1;
                    logger2('order meet the stoplose');
                }

                // $diffema=$finalema10-$finalema20;
                // if(($diffema<0.1)||$allowtoclose==1){
                if( $allowtoclose==1 ){
                    logger2('close 做多:'.date('Y-m-d H:i:s',$time));
                    try {
                        $result=$bybit->cancel()->postCancel([
                            'category'=>'linear',
                            'action'=>'PositionClose',
                            'closeOnTrigger'=>true,
                            'createType'=>'CreateByClosing',
                            'leverage'=>'100',
                            'leverageE2'=>'10000',
                            'orderType'=>'Market',
                            'positionIdx'=> '0',
                            'price'=>'0',
                            'qty'=>'0.1',
                            //'qtyX'=>"100000",
                            'side'=>"Sell",
                            'symbol'=>"SOLUSDT",
                            'timeInForce'=>"GoodTillCancel",
                            'type'=>"Activity",
                        ]);
                        if($result["retCode"]!=0){
                            $error = 'close 做多 error: '.$result["retCode"].';'.$result["retMsg"];
                            logger2($error);
                        }else{
                            $position ="long to short";
                            $allowtoclose =0;
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                            // $overprice = $currentprice;
                        }
                        orderlogger2('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));  
                        orderlogger2('closing order 做多:'.date('Y-m-d H:i:s',$time));
                        orderlogger2('============================');
                    }catch (\Exception $e){
                        $error = 'close 做多 error: '.$e->getMessage();
                        logger2($error);
                        break;
                    }

                }   
                logger2('running cancel 做多:'.date('Y-m-d H:i:s',$time));
       


   
            }else if($action=="short"){
                //close 做空
      
                $diffprice =  $buyingprice-$currentprice;
                $beforeearninglvl=$earninglvl;

                $earninglvl= getearninglvlsol($diffprice,$earninglvl);

                if($beforeearninglvl!=0){
                    $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                    logger2($earninglvllog);
                    if($beforeearninglvl>$earninglvl){
                        $canclose+=1;
                    }
                
                    if($canclose>1){
                        logger2('diffprice>200 short:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        $allowtoclose =1;
                        $firstposition="";
                        $isfirstorder=0;
                        $canclose=0;
                        $earninglvl=0;
                        $beforeearninglvl=0;
                    }
                }

                // //closed 当亏50%
                // if(($currentprice-$buyingprice)>200){
                //     $allowtoclose =1;
                //     logger2('closing order 做空:lose 50%:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);

                // }
    
                // if(($finalema10>$finalema20)){
                if(($finalema20<$todayLowPrice)){
                    $allowtoclose =1;
                    logger2('closing order 做空:$finalema10>$finalema20');
                }
    
                // if(($currentprice>($finalema20+10))){
                //     $allowtoclose =1;
                //     logger2('closing order 做空:over sma50');
                // }
    
                if($currentprice>$stopLoss){
                    $allowtoclose =1;
                    logger2('order meet the stoplose');
                }

                // $diffema=$finalema20-$finalema10;
                // if(($diffema<0.1)||$allowtoclose==1){
                if( $allowtoclose==1 ){
                    logger2('close 做空:'.date('Y-m-d H:i:s',$time));
                    try {
                        $result=$bybit->cancel()->postCancel([
                            'category'=>'linear',
                            'action'=>'PositionClose',
                            'closeOnTrigger'=>true,
                            'createType'=>'CreateByClosing',
                            'leverage'=>'100',
                            'leverageE2'=>'10000',
                            'orderType'=>'Market',
                            'positionIdx'=> '0',
                            'price'=>'0',
                            'qty'=>'0.1',
                            //'qtyX'=>"100000",
                            'side'=>"Buy",
                            'symbol'=>"SOLUSDT",
                            'timeInForce'=>"GoodTillCancel",
                            'type'=>"Activity",
                        ]);
                        if($result["retCode"]!=0){
                            $error = 'close 做空 error: '.$result["retCode"].';'.$result["retMsg"];
                            logger2($error);
                        }else{
                            $position ="short to long";
                            $allowtoclose =0;
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                            // $overprice = $currentprice;
                        }
                        orderlogger2('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                        orderlogger2('closing order 做空:'.date('Y-m-d H:i:s',$time));
                        orderlogger2('============================');
                    }catch (\Exception $e){
                        $error = 'close 做空 error: '.$e->getMessage();
                        logger2($error);
                        break;
                    }
                }
                logger2('running cancel 做空:'.date('Y-m-d H:i:s',$time));
            }

        }
    }


    logger2(' ');

}catch (\Exception $e){
    print_r($e->getMessage());
}






try {
    $curentWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    logger2($error);
}

if(!empty($curentWalletBalance["result"])&&isset($curentWalletBalance["result"]["list"][0]["totalWalletBalance"])){

$curentAccountBalance=$curentWalletBalance["result"]["list"][0]["totalWalletBalance"];

//if total loss more than 20%,closed and stop
// if($curentAccountBalance<30.50){
if($curentAccountBalance<($totalAccountBalance*0.8)){
    logger2('force stop:'.date('Y-m-d H:i:s',$time));

    if($action=="long"){

        logger2('close 做多:'.date('Y-m-d H:i:s',$time));

        try {
            $result=$bybit->cancel()->postCancel([
                'category'=>'linear',
                'action'=>'PositionClose',
                'closeOnTrigger'=>true,
                'createType'=>'CreateByClosing',
                'leverage'=>'100',
                'leverageE2'=>'10000',
                'orderType'=>'Market',
                'positionIdx'=> '0',
                'price'=>'0',
                'qty'=>'0.1',
                //'qtyX'=>"100000",
                'side'=>"Sell",
                'symbol'=>"SOLUSDT",
                'timeInForce'=>"GoodTillCancel",
                'type'=>"Activity",
            ]);
        }catch (\Exception $e){
            // print_r($e->getMessage());
            $error = 'close 做多 error: '.$e->getMessage();
            logger2($error);
            break;
        }


    }else if($action=="short"){

        logger2('close 做空:'.date('Y-m-d H:i:s',$time));

        try {
            $result=$bybit->cancel()->postCancel([
                'category'=>'linear',
                'action'=>'PositionClose',
                'closeOnTrigger'=>true,
                'createType'=>'CreateByClosing',
                'leverage'=>'100',
                'leverageE2'=>'10000',
                'orderType'=>'Market',
                'positionIdx'=> '0',
                'price'=>'0',
                'qty'=>'0.1',
                //'qtyX'=>"100000",
                'side'=>"Buy",
                'symbol'=>"SOLUSDT",
                'timeInForce'=>"GoodTillCancel",
                'type'=>"Activity",
            ]);
        }catch (\Exception $e){
            // print_r($e->getMessage());
            $error = 'close 做空 error: '.$e->getMessage();
            logger2($error);
            break;
        }

    }

    break;
}



}

}
}

sleep(1);

}