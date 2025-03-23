<?php
/**
 * using ema 10 and ema 20   
 * day
 * sol
 * */
date_default_timezone_set('Asia/Kuala_Lumpur'); 
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';

$bybit=new BybitV5($key,$secret);
// $bybit=new BybitV5($argv[1],$argv[2]);

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
$todayLowPrice =0;
$lastTradeDate = ""; // 存储最近交易日期
$currentTradeDate = "";

$startingtime= 'Starting Time:'.date('Y-m-d H:i:s',(time()));
loggertest($startingtime);




$starttime = strtotime('2025-01-14 22:00:00') * 1000;//开始时间前1小时
$endtime = strtotime('2025-01-14 23:00:00') * 1000;//想要的开始时间
$startingDay = (int)date('d', $endtime / 1000);



$dayEndTime = strtotime('2025-01-15 00:00:00') * 1000;//想要的开始时间
$interval = 24 * 3600 * 1000; // 1 天（毫秒）
$required_days = 20;
$dayStartTime = $dayEndTime - ($required_days * $interval);


$is_running_order=0;

while(1){
$time=time();
$start=1596446400;

if($time>$start){

    //结束时间
    if($endtime>=(strtotime('2025-02-28 00:00:00') * 1000)){
        loggertest("Reach end time");
        echo "Reach end time";

        $endingtime= 'Ending Time:'.date('Y-m-d H:i:s',(time()));
        loggertest($endingtime);
        break;
    }

    $currentDay = (int)date('d', $endtime / 1000);
    $currentTradeDate = date('Y-m-d', $endtime / 1000);
    // loggertest($lastTradeDate);
    if($startingDay!=$currentDay){
        $currentDaylog= 'startingDay: '.(int)$startingDay.' , '.'currentDay: '.(int)$currentDay;
        loggertest("currentDaylog",$currentDaylog);
 
        $dayStartTime+=(24 * 3600 * 1000);
        $dayEndTime+=(24 * 3600 * 1000);
        $startingDay = $currentDay;
    }

 
    $processtime= 'Process Time:'.date('Y-m-d H:i:s',($endtime/1000));
    loggertest($processtime);
    echo $processtime;
    echo PHP_EOL; 


try {
    //ema 10 is blue color
    //ema 50 is white color

    //calcuate ema 
    try {
        $getKline=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'SOLUSDT',
            'interval'=>'1',
            'start'=>$starttime,
            'end'=>$endtime,
            'limit'=>'20',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertest($error);
        break;
    }

    //calcuate ema 
    try {
        $getKline2=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'SOLUSDT',
            'interval'=>'D',
            'start'=>$dayStartTime,
            'end'=>$dayEndTime,
            'limit'=>'20',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertest($error);
        break;
    }


    // $starttime+=60000;
    // $endtime+=60000;

    $starttime+=300000;
    $endtime+=300000;
   
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }

    $closePrice202=array();
    foreach ($getKline2["result"]["list"] as $r) {
        $closePrice202[]=$r["4"];
    }
    // $closePrice202 = array_reverse($closePrice202);

    // $priceRecord20 = 'closePrice20: ' . json_encode($closePrice20);
    $priceRecord202 = 'closePrice202: ' . json_encode($closePrice202);
    // loggertest($priceRecord20);
    loggertest($priceRecord202);

    $average10 = calculateEMA($closePrice202, 10);
    $average20 = calculateEMA($closePrice202, 20);

    $dayLowPrice = array();
    foreach ($getKline2["result"]["list"] as $r) {
        $dayLowPrice[]=$r["3"];
    }
    $todayLowPrice = $dayLowPrice["0"];
    loggertest('todayLowPrice: ' .$todayLowPrice);


    $dayTime =array();
    foreach ($getKline2["result"]["list"] as $r) {
        $dayTime[]=$r["0"];
    }
    loggertest('dayTime: ' . json_encode($dayTime));


    $currentprice=$closePrice20[0];

    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema20: '.$finalema20.' , '.'currentprice: '.$currentprice;
    loggertest($finalema);
    echo $finalema;
    echo PHP_EOL; echo PHP_EOL;






    //checking for first trade
    if(($finalema10>$finalema20)&&$firstposition==""&&$isfirstorder==1){
        $firstposition="long";
    }else if(($finalema10<$finalema20)&&$firstposition==""&&$isfirstorder==1){
        $firstposition="short";
    }

    if($firstposition=="long"&&($finalema20<$todayLowPrice)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="long to short";
        loggertest("start order");
    }else if($firstposition=="short"&&($todayLowPrice>$finalema20)&&$isfirstorder==1){
        $isfirstorder=0;
        $position ="short to long";
        loggertest("start order");
    }


    //start trade
    if($isfirstorder==0){
        

        if($is_running_order==0){
            //order
        
            if($position==""){
                loggertest('order meet the stoplose');
                orderloggertest('order meet the stoplose:'.date('Y-m-d H:i:s',($endtime/1000)));
                orderloggertest('============================');
                $isfirstorder=1;
                $firstposition="";
              
            }else{

            

              
                // if($finalema10>($finalema20)&&($currentprice>$finalema20)&&(($currentprice - $finalema20)<15)){
                // if(($todayLowPrice>$finalema20)&&(($todayLowPrice - $finalema20)<15)&&($position=="short to long")){
                if(($todayLowPrice>$finalema20)&&(($todayLowPrice - $finalema20)<15)&&($lastTradeDate!=$currentTradeDate)){
                    //做多
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                    
                        $buyingprice=$closePrice20[0];
                        // $stopLoss=$buyingprice-800;
                        $stopLoss=$finalema10-10;
                        $is_running_order=1;    
                        $action="long";
                        $position="";

                        orderloggertest('making order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);
                        $lastTradeDate = date('Y-m-d', $endtime / 1000);
                    
                    }
                    loggertest('running order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                
                // }else if(($finalema10)<$finalema20&&($currentprice<$finalema20)&&(($finalema20 - $currentprice)<15)){
                // }else if(($todayLowPrice<$finalema20)&&(($finalema20 - $todayLowPrice)<15)&&($position=="long to short")){
                }else if(($todayLowPrice<$finalema20)&&(($finalema20 - $todayLowPrice)<15)&&($lastTradeDate!=$currentTradeDate)){
                    //做空
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));

                        $buyingprice=$closePrice20[0];
                        // $stopLoss=$buyingprice+800;
                        $stopLoss=$finalema10+10;
                        $is_running_order=1;
                        $action="short";
                        $position="";

                        orderloggertest('making order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);
                    }
                
                    loggertest('running order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                    $lastTradeDate = date('Y-m-d', $endtime / 1000);
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
                        loggertest($earninglvllog);
                    
                        if(($beforeearninglvl>$earninglvl)){
                            $canclose+=1;
                        }
                    
                        if($canclose>1){
                            orderloggertest('diffprice>50 long:'.date('Y-m-d H:i:s',($endtime/1000)).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

                    
                
                    //closed 当亏50%
                    // if(($buyingprice-$currentprice)>800){
                    //     $allowtoclose =1;
                    // }
                    if(($finalema20>$todayLowPrice)){
                        $allowtoclose =1;
                        orderloggertest('closing order 做多:$finalema10<$finalema20');
                    }
                    // if(($currentprice<($finalema20))){
                    //     $allowtoclose =1;
                    //     orderloggertest('closing order 做多:over sma50');
                    // }

                    if($stopLoss>$currentprice){
                        $allowtoclose =1;
                        orderloggertest('order meet the stoplose');
                    }
                
                    if( $allowtoclose==1 ){
                        loggertest('close 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                        $position ="long to short";
                        $allowtoclose =0;
                        orderloggertest('closing order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));         
                        orderloggertest('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));    
                        result(($currentprice-$buyingprice));  
                        $is_running_order=0;
                    
                        orderloggertest('============================');
                    }
                
                loggertest('running cancel 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    $diffprice =  $buyingprice-$currentprice;
                    $beforeearninglvl=$earninglvl;

                    $earninglvl= getearninglvlsol($diffprice,$earninglvl);
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertest($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                        // if(($beforeearninglvl>$earninglvl)&&($beforeearninglvl>($diffprice+10))){
                            $canclose+=1;
                        }
                    
                        if($canclose>1){
                            orderloggertest('diffprice>50 short:'.date('Y-m-d H:i:s',($endtime/1000)).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

                     //closed 当亏50%
                    // if(($currentprice-$buyingprice)>800){
                    //     $allowtoclose =1;
                    // }
                    if(($finalema20<$todayLowPrice)){
                        $allowtoclose =1;
                        orderloggertest('closing order 做空:$finalema10>$finalema20');
                    }

                    // if(($currentprice>($finalema20))){
                    //     $allowtoclose =1;
                    //     orderloggertest('closing order 做空:over sma50');
                    // }

                    if($currentprice>$stopLoss){
                        $allowtoclose =1;
                        orderloggertest('order meet the stoplose');
                    }
                
                    if( $allowtoclose==1 ){
                        loggertest('close 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                        $position ="short to long";
                        $allowtoclose =0;
                        orderloggertest('closing order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                        orderloggertest('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                        result(($buyingprice-$currentprice));  
                        $is_running_order=0;     
                    
                        orderloggertest('============================');
                    }
                
                
                loggertest('running cancel 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
            }
        
        
        
        }


    }
    
    loggertest(' ');


}catch (\Exception $e){
        print_r($e->getMessage());
}

}

// sleep(1);

}
