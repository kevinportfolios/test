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



$position ="";
$action="";
$isfirstorder=1;
$firstposition="";
$earninglvl=0;
$beforeearninglvl=0;
$canclose=0;

$buyingprice=0;//进场价钱
$currentprice=0;//当前市场价钱
$allowtoclose=0;



try {
    $getWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    loggervol($error);
}


if(isset($getWalletBalance["result"]["list"][0])) {
    $totalAccountBalance=$getWalletBalance["result"]["list"][0]["totalWalletBalance"];
    loggervol("start account balance: ".$totalAccountBalance);
} else {
    echo("Token expired");
    echo(json_encode($getWalletBalance));
    return true;
}




while(1){
$time=time()+(8*3600);
$start=1596446400;

if($time>$start){
    $t=$time-$start;
    //如果要24小时执行
    //if(is_int($t/86400)){

    //方便测试每10秒执行一次
    if(is_int($t/10)){
        // echo 'Process Time:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        $processtime= 'Process Time:'.date('Y-m-d H:i:s',$time);
        loggervol($processtime);


try {

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'BTCUSDT',
            'interval'=>'1',
            'limit'=>'100',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggervol($error);
        break;
    }
   

    // $closePrice3=array();
    $closePrice4=array();
    $closePrice20=array();
   
   
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }

    $volume=$getKline["result"]["list"][0]["5"];
    $volumebefore=$getKline["result"]["list"][1]["5"];
    $currentprice=$closePrice20[0];
    $closePrice4 = array_slice($closePrice20, 0, 5);


    // $closePrice3 = array_slice($closePrice10, 0, 2);//checking for order again




    $finalema= 'closeprice: '.json_encode($closePrice4).';volumenow: '.$volume.';volumebefore: '.$volumebefore;
    loggervol($finalema);
    echo 'volumenow: '.$volume.';volumebefore: '.$volumebefore;
    echo PHP_EOL; echo PHP_EOL;

   
    if($volume>1000){
        loggervol("volume more than 1000");
    }else if($volume>900){
        loggervol("volume more than 900");
    }else if($volume>800){
        loggervol("volume more than 800");
    }else if($volume>700){
        loggervol("volume more than 700");
    }else if($volume>600){
        loggervol("volume more than 600");
    }else if($volume>500){
        loggervol("volume more than 500");
    }






    //checking for first trade
    if(($closePrice4[1]<$closePrice4[2])&&$isfirstorder==1&&($volumebefore>500)){
        $isfirstorder=0;
        $position ="short to long";
        loggervol("start order");
    }else if(($closePrice4[1]>$closePrice4[2])&&$isfirstorder==1&&($volumebefore>500)){
        $isfirstorder=0;
        $position ="long to short";
        loggervol("start order");
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
    loggervol($error);
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




//////////////////////////////////////////////////








if($is_running_order==0){
    //order

    if($position==""){
        loggervol('order meet the stoplose');
        orderloggervol('order meet the stoplose:'.date('Y-m-d H:i:s',$time));
       
        $isfirstorder=1;
        // $firstposition="";

        // if($closePrice4[0]>$closePrice4[1]){
        //     $position ="short to long";
        // }else if($closePrice4[0]<$closePrice4[1]){
        //     $position = "long to short";
        // }
        // loggervol('position:'.$position.';action:'.$action);

    }else{





    if(($closePrice4[1]<$closePrice4[2])&&($volumebefore>500)){
    // if(($closePrice4[0]>$closePrice4[1])&&($closePrice4[1]>$closePrice4[2])&&($volume>600&&($volume>$volumebefore))){
        //做多
        loggervol('position:'.$position);

        // $array = $closePrice3;
        // $allBigger = true;
        //  //前面的大过后面的value
        //  // Check if each value is greater than its succeeding value
        // for ($i = 0; $i < count($array) - 1; $i++) {
        //     if ($array[$i] <= $array[$i + 1]) {
        //         $allBigger = false;
        //         break;
        //     }
        // }

     
        
        // if($volume>1000){
      

        if($position =="short to long"){
            $buyingprice=$closePrice20[0];
            //if not,open order
             //做多
             loggervol('order 做多:'.date('Y-m-d H:i:s',$time));

             $stopLoss=$currentprice-60;

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
                    loggervol($error);
                }else{
                    $position="";
                    // $canbuy=0;
                }

                orderloggervol('making order 做多:'.date('Y-m-d H:i:s',$time));
                // orderloggervol('overprice:'.$overprice);

            }catch (\Exception $e){
                $error = 'order 做多 error: '.$e->getMessage();
                loggervol($error);
                break;
            }

            $action="long";

        }
        loggervol('running order 做多:'.date('Y-m-d H:i:s',$time));



        // }
      

    }else if(($closePrice4[1]>$closePrice4[2])&&($volumebefore>500)){
    // }else if(($closePrice4[0]<$closePrice4[1])&&($closePrice4[1]<$closePrice4[2])&&($volume>600&&($volume>$volumebefore))){
        //做空
      
        loggervol('position:'.$position);

        // $array = $closePrice3;
        // //后面的大过前面的value
        // // Check if each value is greater than its preceding value
        // $allBigger = true;
        // for ($i = 1; $i < count($array); $i++) {
        //     if ($array[$i] <= $array[$i - 1]) {
        //         $allBigger = false;
        //         break;
        //     }
        // }



   
        // if($volume>1000 ){
    
        if($position =="long to short"){
            $buyingprice=$closePrice20[0];
            //if not,open order
            //做空
            loggervol('order 做空:'.date('Y-m-d H:i:s',$time));
            
            $stopLoss=$currentprice+60;
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
                    loggervol($error);
                }else{
                    $position="";
                    // $canbuy=0;
                }


                orderloggervol('making order 做空:'.date('Y-m-d H:i:s',$time));
                // orderloggervol('overprice:'.$overprice);


            }catch (\Exception $e){
                $error = 'order 做空 error: '.$e->getMessage();
                loggervol($error);
                break;
            }

            $action="short";
        }

        loggervol('running order 做空:'.date('Y-m-d H:i:s',$time));



        

        // }
       
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
                loggervol($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }

                // if($before1minprice>$currentprice){
                //     $canclose+=1;
                // }
               
                if($canclose>0){
                    loggervol('diffprice>200 long:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);

                    $allowtoclose =1;
                    // $firstposition="";
                    $isfirstorder=0;

                    $canclose=0;
                    $earninglvl=0;
                    $beforeearninglvl=0;

                }
            }

           
            if($allowtoclose==1){
                // $closingprice=$closePrice20[0];
                loggervol('close 做多:'.date('Y-m-d H:i:s',$time));

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
                        loggervol($error);
                    }else{
                        $position ="long to short";
                        $allowtoclose =0;
                        // $overprice = $currentprice;
                    }

                    
                    orderloggervol('closing order 做多:'.date('Y-m-d H:i:s',$time));
                }catch (\Exception $e){
                    $error = 'close 做多 error: '.$e->getMessage();
                    loggervol($error);
                    break;
                }
    

            }
          
        loggervol('running cancel 做多:'.date('Y-m-d H:i:s',$time));
       


   
    }else if($action=="short"){
       //close 做空
      

            $diffprice =  $buyingprice-$currentprice;
            $beforeearninglvl=$earninglvl;
            $earninglvl= getearninglvl2($diffprice,$earninglvl);

            if($beforeearninglvl!=0){
                $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                loggervol($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }
               
                // if($currentprice>$before1minprice){
                //     $canclose+=1;
                // }
               
                // if(($beforeearninglvl>$earninglvl)&&($currentprice>$before1minprice)){
                if($canclose>0){
                    loggervol('diffprice>200 short:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);

                    $allowtoclose =1;
                    // $firstposition="";
                    $isfirstorder=0;

                    $canclose=0;
                    $earninglvl=0;
                    $beforeearninglvl=0;
            
                }
            }

            
            if($allowtoclose==1){
                // $closingprice=$closePrice20[0];
                loggervol('close 做空:'.date('Y-m-d H:i:s',$time));
             
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
                        loggervol($error);
                    }else{
                        $position ="short to long";
                        $allowtoclose =0;
                        // $overprice = $currentprice;
                    }

                    orderloggervol('closing order 做空:'.date('Y-m-d H:i:s',$time));
                }catch (\Exception $e){
                    $error = 'close 做空 error: '.$e->getMessage();
                    loggervol($error);
                    break;
                }

            }
        

        loggervol('running cancel 做空:'.date('Y-m-d H:i:s',$time));
    }



}










//////////////////////////////////////////////////

}
    
loggervol(' ');

}catch (\Exception $e){
    print_r($e->getMessage());
}




try {
    $curentWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    loggervol($error);
}


if(!empty($curentWalletBalance["result"])&&isset($curentWalletBalance["result"]["list"][0]["totalWalletBalance"])){


$curentAccountBalance=$curentWalletBalance["result"]["list"][0]["totalWalletBalance"];

//if total loss more than 20%,closed and stop
if($curentAccountBalance<($totalAccountBalance*0.8)){
    if($action=="long"){


        loggervol('close 做多:'.date('Y-m-d H:i:s',$time));

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
            loggervol($error);
            break;
        }


    }else if($action=="short"){

        loggervol('close 做空:'.date('Y-m-d H:i:s',$time));

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
            loggervol($error);
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