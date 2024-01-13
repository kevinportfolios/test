<?php
/**
 *
 * using ema 50 and ema 100  
 * 5minutes
 * */
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';

// $bybit=new BybitV5($key,$secret);

// //You can set special needs
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



$position ="";
$action="";
$isfirstorder=1;
$firstposition="";
$earninglvl=0;
$beforeearninglvl=0;
$canclose=0;
$canbuy=0;

$buyingprice=0;//进场价钱
// $closingprice=0;
$overprice=0;

$currentprice=0;//当前市场价钱
$before1minprice=0;//上1分钟市场价钱
$before2minprice=0;//上2分钟市场价钱
$allowtoclose=0;

$diffrange=0;


// try {
//     $getWalletBalance=$bybit->account()->getWalletBalance([
//         'accountType'=>'UNIFIED',
//     ]);
// }catch (\Exception $e){
//     $error = 'getWalletBalance error: '.$e->getMessage();
//     logger2($error);
// }

// $totalAccountBalance=$getWalletBalance["result"]["list"][0]["totalWalletBalance"];
// logger2("start account balance: ".$totalAccountBalance);







//aldready got order
// $isfirstorder=0;

// $action="long";
// $position ="short to long";

// $action="short";
// $position ="long to short";



// $buyingprice="43627.20";
// $overprice="46730";




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
        logger2($processtime);


try {
    //ema 10 is blue color
    //ema 100 is white color

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'BTCUSDT',
            'interval'=>'5',
            'limit'=>'50',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        logger2($error);
        break;
    }
   

    $closePrice4=array();
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }

    // echo json_encode($closePrice20);
    $closePrice4 = array_slice($closePrice20, 0, 3);//checking for order again
    // echo PHP_EOL; echo PHP_EOL;
    // echo json_encode($closePrice4);

    $average10 = calculateEMA($closePrice20, 10);
    $average20 = calculateEMA($closePrice20, 50);

    $currentprice=$closePrice20[0];
    $before1minprice=$closePrice20[1];
    $before2minprice=$closePrice20[2];


    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema50: '.$finalema20;
    logger2($finalema);
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
        logger2("start order");
    }else if($firstposition=="short"&&($finalema10>$finalema20)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="short to long";
        logger2("start order");
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
       
        $isfirstorder=0;
        $firstposition="";

        if($action == "long"){
            $position ="long to short";
        }else if($action == "short"){
            $position = "short to long";
        }
        logger2('position:'.$position.';action:'.$action);

    }else{






    if($finalema10>$finalema20){
        //做多
        logger2('position:'.$position);

        $diffrange=$finalema10-$finalema20;

        if($diffrange>0&&$diffrange<10){
            $overprice=$finalema20;
        }

        if($diffrange>50){

           
        if($position =="long to short"){
        
            $array = $closePrice4;
            $allBigger = true;
             //前面的大过后面的value
             // Check if each value is greater than its succeeding value
            for ($i = 0; $i < count($array) - 1; $i++) {
                if ($array[$i] <= $array[$i + 1]) {
                    $allBigger = false;
                    break;
                }
            }

            logger2('allBigger 做多: '.$allBigger);
            logger2('closePrice4: '.json_encode($closePrice4));



            $diffprice =  $currentprice-$overprice;

            if($diffprice>180){
                $canbuy+=1;
            }

            logger2('canbuy 做多:'.$canbuy.';diffprice:'. $diffprice.';currentprice:'.$currentprice.';overprice:'.$overprice);

            //60s
            if ($canbuy>12&&$allBigger) {
                logger2('update position status long:'.json_encode($closePrice4));
                $position ="short to long";
            }

        }

        if($position =="short to long"){
            $buyingprice=$closePrice20[0];
            //if not,open order
             //做多
             logger2('order 做多:'.date('Y-m-d H:i:s',$time));

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
                    logger2($error);
                }else{
                    $position="";
                    $canbuy=0;
                }

                orderlogger2('making order 做多:'.date('Y-m-d H:i:s',$time));
                orderlogger2('overprice:'.$overprice);

            }catch (\Exception $e){
                $error = 'order 做多 error: '.$e->getMessage();
                logger2($error);
                break;
            }

            $action="long";

        }
        logger2('running order 做多:'.date('Y-m-d H:i:s',$time));



        }
      


    }else if($finalema10<$finalema20){
        //做空
      
        logger2('position:'.$position);

        $diffrange=$finalema20-$finalema10;

        if($diffrange>0&&$diffrange<10){
            $overprice=$finalema20;
        }

        if($diffrange>50){




        if($position =="short to long"){
        
            $array = $closePrice4;
            //后面的大过前面的value
            // Check if each value is greater than its preceding value
            $allBigger = true;
            for ($i = 1; $i < count($array); $i++) {
                if ($array[$i] <= $array[$i - 1]) {
                    $allBigger = false;
                    break;
                }
            }

            logger2('allBigger 做空:'.$allBigger);
            logger2('closePrice4: '.json_encode($closePrice4));

            $diffprice = $overprice -$currentprice;

            if($diffprice>180){
                $canbuy+=1;
            }

            logger2('canbuy 做空:'.$canbuy.';diffprice:'. $diffprice.';currentprice:'.$currentprice.';overprice:'.$overprice);

           //60s
           if ($canbuy>12&&$allBigger) {
                logger2('update position status short:'.json_encode($closePrice4));
                $position ="long to short";
            }


        }

        if($position =="long to short"){
            $buyingprice=$closePrice20[0];
            //if not,open order
            //做空
            logger2('order 做空:'.date('Y-m-d H:i:s',$time));
            
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
                    logger2($error);
                }else{
                    $position="";
                    $canbuy=0;
                }


                orderlogger2('making order 做空:'.date('Y-m-d H:i:s',$time));
                orderlogger2('overprice:'.$overprice);


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






    }












}else if($is_running_order==1){
    //cancel

    if($action=="long"){
        //close 做多

            $diffprice =  $currentprice-$buyingprice;
            $beforeearninglvl=$earninglvl;
            $earninglvl= getearninglvl2($diffprice,$earninglvl);

            if($beforeearninglvl!=0){
                $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                logger2($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }

                // if($before1minprice>$currentprice){
                //     $canclose+=1;
                // }
               
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



            $diffema=$finalema10-$finalema20;
           
            if(($diffema<0.1)||$allowtoclose==1){
                // $closingprice=$closePrice20[0];
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
                        'qty'=>'0.001',
                        //'qtyX'=>"100000",
                        'side'=>"Sell",
                        'symbol'=>"BTCUSDT",
                        'timeInForce'=>"GoodTillCancel",
                        'type'=>"Activity",
                    ]);


                    if($result["retCode"]!=0){
                        $error = 'close 做多 error: '.$result["retCode"].';'.$result["retMsg"];
                        logger2($error);
                    }else{
                        $position ="long to short";
                        $allowtoclose =0;
                        // $overprice = $currentprice;
                    }

                    
                    orderlogger2('closing order 做多:'.date('Y-m-d H:i:s',$time));
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
            $earninglvl= getearninglvl2($diffprice,$earninglvl);

            if($beforeearninglvl!=0){
                $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                logger2($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }
               
                // if($currentprice>$before1minprice){
                //     $canclose+=1;
                // }
               
                // if(($beforeearninglvl>$earninglvl)&&($currentprice>$before1minprice)){
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


            $diffema=$finalema20-$finalema10;
            
            if(($diffema<0.1)||$allowtoclose==1){
                // $closingprice=$closePrice20[0];
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
                        'qty'=>'0.001',
                        //'qtyX'=>"100000",
                        'side'=>"Buy",
                        'symbol'=>"BTCUSDT",
                        'timeInForce'=>"GoodTillCancel",
                        'type'=>"Activity",
                    ]);

                    if($result["retCode"]!=0){
                        $error = 'close 做空 error: '.$result["retCode"].';'.$result["retMsg"];
                        logger2($error);
                    }else{
                        $position ="short to long";
                        $allowtoclose =0;
                        // $overprice = $currentprice;
                    }

                    orderlogger2('closing order 做空:'.date('Y-m-d H:i:s',$time));
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
if($curentAccountBalance<($totalAccountBalance*0.8)){
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
            logger2($error);
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