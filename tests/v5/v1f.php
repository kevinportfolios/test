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


// $bybit=new BybitV5($key,$secret);
$bybit=new BybitV5($argv[1],$argv[2]);

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
$allowtoclose=0;

$status ="";
$highestprice=0;
$lowestprice=0;

$exceedprice=0;
$exceedhighestprice=0;
$exceedlowestprice=0;
$ismeetstoplose=0;





try {
    $getWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    loggertestvol($error);
}

$totalAccountBalance=$getWalletBalance["result"]["list"][0]["totalWalletBalance"];


loggertestvol("start account balance: ".$totalAccountBalance);


$startingtime= 'Starting Time:'.date('Y-m-d H:i:s',(time()+(8*3600)));
loggertestvol($startingtime);


// $starttime = 1723626000000;//开始时间前1小时
// $endtime   = 1723629600000;//想要的开始时间
// $allowtime = $starttime;

$is_running_order=0;









while(1){
$time=time()+(8*3600);
$start=1596446400;
// echo $time;
// echo date('Y-m-d H:i:s',$time);
// break;

if($time>$start){

    //结束时间
    if($time>=1724637600){
        loggertestvol("Reach end time");
        echo "Reach end time";

        $endingtime= 'Ending Time:'.date('Y-m-d H:i:s',$time);
        loggertestvol($endingtime);
        break;
    }

    // $t=$time-$start;
    //如果要24小时执行
    //if(is_int($t/86400)){

    //方便测试每10秒执行一次
    // if(is_int($t/5)){
        // echo 'Process Time:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        $processtime= 'Process Time:'.date('Y-m-d H:i:s',$time);
        loggertestvol($processtime);
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
            'interval'=>'1',
            // 'start'=>$starttime,
            // 'end'=>$endtime,
            'limit'=>'30',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertestvol($error);
        break;
    }

    $closePrice4=array();
    $closePrice20=array();


     foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
     }

     $average10 = calculateEMA($closePrice20, 10);
     $average20 = calculateEMA($closePrice20, 20);
     $finalema10=$average10[0];
     $finalema20=$average20[0];

     $currentprice=$closePrice20[0];
     $closePrice4 = array_slice($closePrice20, 0, 5);

 
     $volume=$getKline["result"]["list"][0]["5"];
     $volume2=$getKline["result"]["list"][1]["5"];
 
 
 
 
     $finalema= 'closeprice: '.json_encode($closePrice4).';volumenow: '.$volume.';volumebefore: '.$volume2;
     loggertestvol($finalema);

    //  $closePrice5 = array_slice($closePrice202, 0, 5);
    //  $finalemaa= 'closeprice2: '.json_encode($closePrice5);
    //  loggertestvol($finalemaa);
 
     if($volume>1000){
         loggertestvol("volume more than 1000");
     }else if($volume>900){
         loggertestvol("volume more than 900");
     }else if($volume>800){
         loggertestvol("volume more than 800");
     }else if($volume>700){
         loggertestvol("volume more than 700");
     }else if($volume>600){
         loggertestvol("volume more than 600");
     }else if($volume>500){
         loggertestvol("volume more than 500");
     }
 



    //  $position ="short to long";//close 做多 ($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)
    //  $position ="long to short";//close 做空 ($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])


     //checking for first trade
    if($isfirstorder==1&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
        //做空
        orderloggertestvol('position111:'.$position.'time:'.date('Y-m-d H:i:s',$time));
        if($position!="long to short"){
            $exceedprice = $currentprice;
            $isfirstorder=0;
            $position ="short to long";
            loggertestvol("start order1");
            loggertestvol('update exceedprice111:'.date('Y-m-d H:i:s',$time).';exceedprice:'.$exceedprice);
        }
        
    }else if($isfirstorder==1&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
        //做多
        orderloggertestvol('position222:'.$position.'time:'.date('Y-m-d H:i:s',$time));
        if($position!="short to long"){
            $exceedprice = $currentprice;
            $isfirstorder=0;
            $position ="long to short";
            loggertestvol("start order2");
            loggertestvol('update exceedprice222:'.date('Y-m-d H:i:s',$time).';exceedprice:'.$exceedprice);
        }
    }

    //
    if($isfirstorder==0&&$position=="short to long"&&($finalema10>($closePrice20[0]))&&($finalema20>($closePrice20[0]))){
        orderloggertestvol('position333:'.$position.'time:'.date('Y-m-d H:i:s',$time));
        $exceedprice = $currentprice;
        loggertestvol('update exceedprice333:'.date('Y-m-d H:i:s',$time).';exceedprice:'.$exceedprice.';finalema10:'.$finalema10.';finalema20:'.$finalema20);
        $position ="long to short";
    }else if($isfirstorder==0&&$position=="long to short"&&($closePrice20[0]>($finalema10))&&($closePrice20[0]>($finalema20))){
        orderloggertestvol('position444:'.$position.'time:'.date('Y-m-d H:i:s',$time));
        $exceedprice = $currentprice;
        loggertestvol('update exceedprice444:'.date('Y-m-d H:i:s',$time).';exceedprice:'.$exceedprice.';finalema10:'.$finalema10.';finalema20:'.$finalema20);
        $position ="short to long";
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
            loggertestvol($error);
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
        
        









        // if($is_running_order==0 && (intval($starttime) >= intval($allowtime)) ){
        if($is_running_order==0){
            //order
        
            if($position==""){
                loggertestvol('order meet the stoplose');
                orderloggertestvol('order meet the stoplose:'.date('Y-m-d H:i:s',$time));
            
                $isfirstorder=1;
                $firstposition="";

              
            }else{

                if(($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)&& (($currentprice - $exceedprice)>250)){

                    //做空
                    loggertestvol('position:'.$position.';volume:'.$volume);
                
                    // if($position !=""){
                    //     loggertestvol('order 做空:'.date('Y-m-d H:i:s',$time));
                    //     $buyingprice=$closePrice20[0];
                    //     $stopLoss=$buyingprice+80;
                    //     $is_running_order=1;
                    //     $action="short";
                    //     $position="";
                    //     $lowestprice=$closePrice20[0];
                    //     orderloggertestvol('making order 做空:'.date('Y-m-d H:i:s',$time));
                    //     orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume.';currentprice:'.$currentprice.';exceedprice:'.$exceedprice.';diff:'.($currentprice - $exceedprice));
                    //     orderloggertestvol('stopLoss:'.$stopLoss);
                    // }

                    if($position !=""){
                        $buyingprice=$closePrice20[0];
                        //if not,open order
                        //做空
                        loggertestvol('order 做空:'.date('Y-m-d H:i:s',$time));
    
                        // $stopLoss=$currentprice+50;
                        $stopLoss=$buyingprice+80;
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
                                loggertestvol($error);
                            }else{
                                $position="";
                            }
                        
                        
            
                            // $is_running_order=1;
                            // $action="short";
                            // $position="";
                            $lowestprice=$closePrice20[0];
                            orderloggertestvol('making order 做空:'.date('Y-m-d H:i:s',$time));
                            orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume.';currentprice:'.$currentprice.';exceedprice:'.$exceedprice.';diff:'.($currentprice - $exceedprice));
                            orderloggertestvol('stopLoss:'.$stopLoss);
                        
                        }catch (\Exception $e){
                            $error = 'order 做空 error: '.$e->getMessage();
                            loggertestvol($error);
                            break;
                        }
                    
                        $action="short";
                    }
                         
                    loggertestvol('running order 做空:'.date('Y-m-d H:i:s',$time));
                
                
        
                }else if(($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])&& (($exceedprice - $currentprice)>250)){
        

                        //做多
                        loggertestvol('position:'.$position.';volume:'.$volume);
                
                        if($position !=""){
                            loggertestvol('order 做多:'.date('Y-m-d H:i:s',$time));
                        
                            $buyingprice=$closePrice20[0];
                            $stopLoss=$buyingprice-80;

                            $is_running_order=1;    
                            $action="long";
                            $position="";
                            $highestprice=$closePrice20[0];
    
                            orderloggertestvol('making order 做多:'.date('Y-m-d H:i:s',$time));
                            orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume.';currentprice:'.$currentprice.';exceedprice:'.$exceedprice.';diff:'.($exceedprice - $currentprice));
                            orderloggertestvol('stopLoss:'.$stopLoss);
                        
                        }
                        

                        if($position !=""){
                            $buyingprice=$closePrice20[0];
                            //if not,open order
                             //做多
                             loggertestvol('order 做多:'.date('Y-m-d H:i:s',$time));
                        
                            //  $stopLoss=$currentprice-50;
                            $stopLoss=$buyingprice-80;
                        
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
                                    loggertestvol($error);
                                }else{
                                    $position="";
                                }
                            
                                

                                // $is_running_order=1;    
                                // $action="long";
                                // $position="";
                                $highestprice=$closePrice20[0];
        
                                orderloggertestvol('making order 做多:'.date('Y-m-d H:i:s',$time));
                                orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume.';currentprice:'.$currentprice.';exceedprice:'.$exceedprice.';diff:'.($exceedprice - $currentprice));
                                orderloggertestvol('stopLoss:'.$stopLoss);
                            
                            }catch (\Exception $e){
                                $error = 'order 做多 error: '.$e->getMessage();
                                loggertestvol($error);
                                break;
                            }
                        
                            $action="long";
                        
                        }
                        loggertestvol('running order 做多:'.date('Y-m-d H:i:s',$time));
                
                }


        
        
            }
        











        }else if($is_running_order==1){
            //cancel
        
            // $ismeetstoplose=0;

            if($action=="long"){
                //close 做多
            
                    $diffprice =  $currentprice-$buyingprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertestvol($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                        //     $canclose+=1;
                        //     loggertestvol('canclose:'.$canclose);
                        // }
                        // if($canclose>1){
                            loggertestvol('diffprice>200 long:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

                    if($currentprice > $highestprice){
                        $highestprice = $currentprice;
                    }

                    //closed 当亏50%
                    if(($buyingprice-$currentprice)>200){
                        $allowtoclose =1;
                    }
                    // if(($finalema10<$finalema20)){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做多:$finalema10<$finalema20');
                    // }
                    // if(($currentprice<($finalema20-30))){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做多:over sma50');
                    // }

                    // if($stopLoss>$currentprice){
                    //     $allowtoclose =1;
                    //     $ismeetstoplose =1;
                    //     orderloggertestvol('order meet the stoplose');
                    // }
                

                    // if( $allowtoclose==1 ){
                    //     loggertestvol('close 做多:'.date('Y-m-d H:i:s',$time));
                    //     // $position ="long to short";
                    //     $position ="short to long";
                    //     $isfirstorder=1;

                    //     $allowtoclose =0;
                    //     orderloggertestvol('closing order 做多:'.date('Y-m-d H:i:s',$time));         
                      
                    //     if($ismeetstoplose == 1){
                    //         orderloggertestvol('stopLoss:'.$stopLoss.' - buyingprice:'.$buyingprice.' = '.($stopLoss-$buyingprice));    
                    //         resultvol(($stopLoss-$buyingprice));  
                    //     }else{
                    //         orderloggertestvol('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));    
                    //         resultvol(($currentprice-$buyingprice));  
                    //     }
                        
                    //     $is_running_order=0;
                    //     // $allowtime = $starttime +  60000;
                    //     orderloggertestvol('highestprice:'.$highestprice);
                    //     orderloggertestvol('============================');
                    //     $highestprice=0;
                    //     $lowestprice=0;
                    // }




                    if( $allowtoclose==1 ){
                        loggertestvol('close 做多:'.date('Y-m-d H:i:s',$time));
                    
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
                                loggertestvol($error);
                            }else{
                                $position ="short to long";
                                $isfirstorder=1;
                                $allowtoclose =0;
                            }
                        

                            orderloggertestvol('closing order 做多:'.date('Y-m-d H:i:s',$time));     


                            // if($ismeetstoplose == 1){
                            //     orderloggertestvol('stopLoss:'.$stopLoss.' - buyingprice:'.$buyingprice.' = '.($stopLoss-$buyingprice));    
                            //     resultvol(($stopLoss-$buyingprice));  
                            // }else{
                                orderloggertestvol('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));    
                                resultvol(($currentprice-$buyingprice));  
                            // }
                            
                            // $is_running_order=0;
                            // $allowtime = $starttime +  60000;
                            orderloggertestvol('highestprice:'.$highestprice);
                            orderloggertestvol('============================');
                            $highestprice=0;
                            $lowestprice=0;

                            
                        }catch (\Exception $e){
                            $error = 'close 做多 error: '.$e->getMessage();
                            loggertestvol($error);
                            break;
                        }
                    
                    
                    }





                
                loggertestvol('running cancel 做多:'.date('Y-m-d H:i:s',$time));
                
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    $diffprice =  $buyingprice-$currentprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertestvol($earninglvllog);
                        
                    
                        if($beforeearninglvl>$earninglvl){
                        //     $canclose+=1;
                        //     loggertestvol('canclose:'.$canclose);
                        // }
                        // if($canclose>1){
                            loggertestvol('diffprice>200 short:'.date('Y-m-d H:i:s',$time).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

                    if($currentprice < $lowestprice){
                        $lowestprice = $currentprice;
                    }
                     //closed 当亏50%
                    if(($currentprice-$buyingprice)>200){
                        $allowtoclose =1;
                    }

                    // if(($finalema10>$finalema20)){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做空:$finalema10>$finalema20');
                    // }

                    // if(($currentprice>($finalema20+30))){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做空:over sma50');
                    // }

                    // if($currentprice>$stopLoss){
                    //     $allowtoclose =1;
                    //     $ismeetstoplose =1;
                    //     orderloggertestvol('order meet the stoplose');
                    // }
                
                    // if( $allowtoclose==1 ){
                    //     loggertestvol('close 做空:'.date('Y-m-d H:i:s',$time));
                    //     // $position ="short to long";
                    //     $position ="long to short";
                    //     $isfirstorder=1;
                    //     $allowtoclose =0;
                    //     orderloggertestvol('closing order 做空:'.date('Y-m-d H:i:s',$time));
                      
                    //     // if($ismeetstoplose == 1){
                    //     //     orderloggertestvol('buyingprice:'.$buyingprice.' - stopLoss:'.$stopLoss.' = '.($buyingprice-$stopLoss));    
                    //     //     resultvol(($buyingprice-$stopLoss));  
                    //     // }else{
                    //         orderloggertestvol('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                    //         resultvol(($buyingprice-$currentprice));  
                    //     // }
                    //     $is_running_order=0;     
                    //     // $allowtime = $starttime +  60000;
                    //     orderloggertestvol('lowestprice:'.$lowestprice);
                    //     orderloggertestvol('============================');
                    //     $highestprice=0;
                    //     $lowestprice=0;
                    // }



                    if( $allowtoclose==1 ){
                        loggertestvol('close 做空:'.date('Y-m-d H:i:s',$time));
                    
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
                                loggertestvol($error);
                            }else{
                                $position ="long to short";
                                $isfirstorder=1;
                                $allowtoclose =0;
                            }
                        
                            orderloggertestvol('closing order 做空:'.date('Y-m-d H:i:s',$time));
                            orderloggertestvol('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                            resultvol(($buyingprice-$currentprice));  
                            $is_running_order=0;     
                            // $allowtime = $starttime +  60000;
                            orderloggertestvol('lowestprice:'.$lowestprice);
                            orderloggertestvol('============================');
                            $highestprice=0;
                            $lowestprice=0;
                        }catch (\Exception $e){
                            $error = 'close 做空 error: '.$e->getMessage();
                            loggertestvol($error);
                            break;
                        }
                    
                    }



                
                loggertestvol('running cancel 做空:'.date('Y-m-d H:i:s',$time));
            }
        
        
        
        }


    }
    
    loggertestvol(' ');



    // $starttime+=5000;
    // $endtime+=5000;

    // $starttime+=30000;
    // $endtime+=30000;

}catch (\Exception $e){
        print_r($e->getMessage());
}




















try {
    $curentWalletBalance=$bybit->account()->getWalletBalance([
        'accountType'=>'UNIFIED',
    ]);
}catch (\Exception $e){
    $error = 'getWalletBalance error: '.$e->getMessage();
    loggertestvol($error);
}


if(!empty($curentWalletBalance["result"])&&isset($curentWalletBalance["result"]["list"][0]["totalWalletBalance"])){


$curentAccountBalance=$curentWalletBalance["result"]["list"][0]["totalWalletBalance"];

//if total loss more than 20%,closed and stop
if($curentAccountBalance<($totalAccountBalance*0.8)){
    if($action=="long"){


        loggertestvol('total loss more than 20%, close 做多:'.date('Y-m-d H:i:s',$time));

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
            loggertestvol($error);
            break;
        }


    }else if($action=="short"){

        loggertestvol('total loss more than 20%, close 做空:'.date('Y-m-d H:i:s',$time));

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
            loggertestvol($error);
            break;
        }

    }

    break;
}

}



}

sleep(1);

}
