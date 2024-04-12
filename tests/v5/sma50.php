<?php
/**
 * using sma 10 and sma 50 
 * 1minutes
 * cd documents\test\tests\v5
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
$action="";
$isfirstorder=1;
$firstposition="";
$earninglvl=0;
$beforeearninglvl=0;
$canclose=0;

$buyingprice=0;//进场价钱
$currentprice=0;//当前市场价钱
// $before1minprice=0;//上1分钟市场价钱
// $before2minprice=0;//上2分钟市场价钱
$allowtoclose=0;




try {
    $getWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    logger($error);
}

$totalAccountBalance=$getWalletBalance["result"]["list"][0]["totalWalletBalance"];


logger("start account balance: ".$totalAccountBalance);







//aldready got order
// $isfirstorder=0;

// $action="long";
// $position ="short to long";

// $action="short";
// $position ="long to short";

// $buyingprice="43838.30";




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
        echo $processtime;
        echo PHP_EOL; 


try {
    //ema 10 is blue color
    //ema 50 is white color

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'BTCUSDT',
            'interval'=>'15',
            'limit'=>'60',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        logger($error);
        break;
    }
   
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }

    $average10 = calculateEMA($closePrice20, 10);
    $average20 = calculateEMA($closePrice20, 50);

    $currentprice=$closePrice20[0];
    // $before1minprice=$closePrice20[1];
    // $before2minprice=$closePrice20[2];

    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema50: '.$finalema20;
    logger($finalema);
    echo $finalema;
    echo PHP_EOL; echo PHP_EOL;




    //checking for first trade
    if(($finalema10>$finalema20)&&$firstposition==""&&$isfirstorder==1){
        $firstposition="long";
    }else if(($finalema10<$finalema20)&&$firstposition==""&&$isfirstorder==1){
        $firstposition="short";
    }


    if($firstposition=="long"&&($finalema10<$finalema20)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="long to short";
        logger("start order");
    }else if($firstposition=="short"&&($finalema10>$finalema20)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="short to long";
        logger("start order");
    }




    //start trade
    if($isfirstorder==0){
    
        //check got order running or not
        try {
            $getRealTime=$bybit->position()->getList([
                'category'=>'linear',
                'symbol'=>'BTCUSDT',
            ]);
        }catch (\Exception $e){
            $error = 'getRealTime error: '.$e->getMessage();
            logger($error);
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
                logger('order meet the stoplose');
                orderlogger('order meet the stoplose:'.date('Y-m-d H:i:s',$time));
            
                $isfirstorder=1;
                $firstposition="";
            
                // if($action == "long"){
                //     $position ="long to short";
                // }else if($action == "short"){
                //     $position = "short to long";
                // }
                // logger('position:'.$position.';action:'.$action);
            
            }else{
            
            
            
            if($finalema10>$finalema20){
                //做多
                logger('position:'.$position);
            
                if($position =="short to long"){
                    $buyingprice=$closePrice20[0];
                    //if not,open order
                     //做多
                     logger('order 做多:'.date('Y-m-d H:i:s',$time));
                
                     $stopLoss=$currentprice-50;
                
                     try {
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
                    
                        if($result["retCode"]!=0){
                            $error = 'order 做多 error: '.$result["retCode"].';'.$result["retMsg"];
                            logger($error);
                        }else{
                            $position="";
                        }
                    
                        orderlogger('making order 做多:'.date('Y-m-d H:i:s',$time));
                    
                    }catch (\Exception $e){
                        $error = 'order 做多 error: '.$e->getMessage();
                        logger($error);
                        break;
                    }
                
                    $action="long";
                
                }
                logger('running order 做多:'.date('Y-m-d H:i:s',$time));
            
            
            
            }else if($finalema10<$finalema20){
                $buyingprice=$closePrice20[0];
                //做空
            
                logger('position:'.$position);
            
                if($position =="long to short"){
                    //if not,open order
                    //做空
                    logger('order 做空:'.date('Y-m-d H:i:s',$time));

                    $stopLoss=$currentprice+50;
                    try {
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
                            //'qtyX'=>"100000",
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
                    
                        if($result["retCode"]!=0){
                            $error = 'order 做空 error: '.$result["retCode"].';'.$result["retMsg"];
                            logger($error);
                        }else{
                            $position="";
                        }
                    
                    
                        orderlogger('making order 做空:'.date('Y-m-d H:i:s',$time));
                    
                    }catch (\Exception $e){
                        $error = 'order 做空 error: '.$e->getMessage();
                        logger($error);
                        break;
                    }
                
                    $action="short";
                }
            
                logger('running order 做空:'.date('Y-m-d H:i:s',$time));
            
            }
        
        
            }
        











        }else if($is_running_order==1){
            //cancel
        
            if($action=="long"){
                //close 做多
            
                    // $diffprice =  $buyingprice-$finalema20;
                    $diffprice =  $currentprice-$buyingprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        logger($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                        }
                    
                        // if($before1minprice>$currentprice){
                        //     $canclose+=1;
                        // }
                    
                        // if($canclose>2&&($before2minprice>$before1minprice)){
                        if($canclose>1){
                            logger('diffprice>200 long:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }
                
                
                
                    // $diffema=$finalema10-$finalema20;
                    // if(($diffema<5)){
                    // if(($diffema<0.1)||$allowtoclose==1){
                    if(($finalema10<$finalema20)||$allowtoclose==1){
                        logger('close 做多:'.date('Y-m-d H:i:s',$time));
                    
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
                                'qty'=>'0.001',
                                //'qtyX'=>"100000",
                                'side'=>"Sell",
                                'symbol'=>"BTCUSDT",
                                'timeInForce'=>"GoodTillCancel",
                                'type'=>"Activity",
                            ]);
                        
                        
                            if($result["retCode"]!=0){
                                $error = 'close 做多 error: '.$result["retCode"].';'.$result["retMsg"];
                                logger($error);
                            }else{
                                $position ="long to short";
                                $allowtoclose =0;
                            }
                        

                            orderlogger('closing order 做多:'.date('Y-m-d H:i:s',$time));
                        }catch (\Exception $e){
                            $error = 'close 做多 error: '.$e->getMessage();
                            logger($error);
                            break;
                        }
                    
                    
                    }
                
                logger('running cancel 做多:'.date('Y-m-d H:i:s',$time));
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    // $diffprice =  $finalema20-$buyingprice;
            
                    $diffprice =  $buyingprice-$currentprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        logger($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                        }
                    
                        // if($currentprice>$before1minprice){
                        //     $canclose+=1;
                        // }
                    
                        // if(($beforeearninglvl>$earninglvl)&&($currentprice>$before1minprice)){
                        // if($canclose>2&&($before1minprice>$before2minprice)){
                        if($canclose>1){
                            logger('diffprice>200 short:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }
                
                
                    // $diffema=$finalema20-$finalema10;
                    // if($diffema<5){
                    // if(($diffema<0.1)||$allowtoclose==1){
                    if(($finalema10>$finalema20)||$allowtoclose==1){
                        logger('close 做空:'.date('Y-m-d H:i:s',$time));
                    
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
                                'qty'=>'0.001',
                                //'qtyX'=>"100000",
                                'side'=>"Buy",
                                'symbol'=>"BTCUSDT",
                                'timeInForce'=>"GoodTillCancel",
                                'type'=>"Activity",
                            ]);
                        
                            if($result["retCode"]!=0){
                                $error = 'close 做空 error: '.$result["retCode"].';'.$result["retMsg"];
                                logger($error);
                            }else{
                                $position ="short to long";
                                $allowtoclose =0;
                            }
                        
                            orderlogger('closing order 做空:'.date('Y-m-d H:i:s',$time));
                        }catch (\Exception $e){
                            $error = 'close 做空 error: '.$e->getMessage();
                            logger($error);
                            break;
                        }
                    
                    }
                
                
                logger('running cancel 做空:'.date('Y-m-d H:i:s',$time));
            }
        
        
        
        }


        }
    
        logger(' ');
    }catch (\Exception $e){
        print_r($e->getMessage());
    }
















try {
    $curentWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    logger($error);
}


if(!empty($curentWalletBalance["result"])&&isset($curentWalletBalance["result"]["list"][0]["totalWalletBalance"])){


$curentAccountBalance=$curentWalletBalance["result"]["list"][0]["totalWalletBalance"];

//if total loss more than 20%,closed and stop
if($curentAccountBalance<($totalAccountBalance*0.8)){
    if($action=="long"){


        logger('close 做多:'.date('Y-m-d H:i:s',$time));

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
                'qty'=>'0.001',
                //'qtyX'=>"100000",
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


    }else if($action=="short"){

        logger('close 做空:'.date('Y-m-d H:i:s',$time));

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
                'qty'=>'0.001',
                //'qtyX'=>"100000",
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

    }

    break;
}



}



// echo PHP_EOL; echo PHP_EOL; echo PHP_EOL;

}
}

sleep(1);

}
