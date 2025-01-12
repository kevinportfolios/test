<?php
/**
 * test running one week
 * using sma 10 and sma 50 
 * 1minutes
 * cd documents\test\tests\v5
 * */
date_default_timezone_set('Asia/Shanghai'); 
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
    'timeout'=>300,

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



$startingtime= 'Starting Time:'.date('Y-m-d H:i:s',(time()));
loggertest($startingtime);





// 设置整个月的起止范围
// $month_start = strtotime('2024-12-20 19:00:00') * 1000; // 月初的 18:00 (毫秒)
// $month_end = strtotime('2024-12-31 23:59:59') * 1000;   // 月末的 01:00 (毫秒)
$month_start = strtotime('2025-01-01 19:00:00') * 1000; // 月初的 18:00 (毫秒)
$month_end = strtotime('2025-01-11 23:59:59') * 1000;   // 月末的 01:00 (毫秒)

$current_day_start = $month_start; // 从月初开始
$interval = 4 * 3600 * 1000; // 每次获取 4 小时数据（毫秒）


$is_running_order=0;

while ($current_day_start < $month_end) {

$day_end = $current_day_start + (5 * 3600 * 1000); // 当天的 11:00
if ($day_end > $month_end) $day_end = $month_end;
$starttime = $current_day_start; // 每天的 18:00
$endtime = $starttime + $interval;   

echo 'day_end:'.$day_end;
echo PHP_EOL;

while ($starttime < $day_end) {

echo $starttime;
echo PHP_EOL;
echo $endtime;
echo PHP_EOL;

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
            'symbol'=>'BTCUSDT',
            'interval'=>'1',
            'start'=>$starttime,
            'end'=>$endtime,
            // 'limit'=>'100',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertest($error);
        break;
    }
   
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }
    // print_r($closePrice20);

    $average10 = calculateEMA($closePrice20, 10);
    $average20 = calculateEMA($closePrice20, 20);
    // $average10 = calculateEMA($closePrice20, 50);
    // $average20 = calculateEMA($closePrice20, 100);

    $currentprice=$closePrice20[0];
    $currentprice2=$closePrice20[1];

    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema10: '.$finalema10.' , '.'finalema50: '.$finalema20.' , '.'currentprice: '.$currentprice;
    loggertest($finalema);

    $volume=$getKline["result"]["list"][0]["5"];
    $volume2=$getKline["result"]["list"][1]["5"];
    $finalema3= 'volumenow: '.$volume.';volumebefore: '.$volume2;
    loggertest($finalema3);

    echo $finalema;
    echo PHP_EOL; echo PHP_EOL;








    //checking for first trade
    // if(($finalema10>$finalema20)&&$firstposition==""&&$isfirstorder==1){
    //     $firstposition="long";
    // }else if(($finalema10<$finalema20)&&$firstposition==""&&$isfirstorder==1){
    //     $firstposition="short";
    // }


    // if($firstposition=="long"&&($finalema10<$finalema20)&&$isfirstorder==1){
    //     $isfirstorder=0;
    //     $position ="long to short";
    //     loggertest("start order");
    // }else if($firstposition=="short"&&($finalema10>$finalema20)&&$isfirstorder==1){
    //     $isfirstorder=0;
    //     $position ="short to long";
    //     loggertest("start order");
    // }

    if(($finalema10<$finalema20)){
        $isfirstorder=0;
        $position ="long to short";
        loggertest("start order");
    }else if(($finalema10>$finalema20)){
        $isfirstorder=0;
        $position ="short to long";
        loggertest("start order");
    }

    // loggertest('kevintest1');
    //start trade
    if($isfirstorder==0){
        

        if($is_running_order==0){
            //order
        
            if($position==""){
                loggertest('order meet the stoplose');
                orderloggertest('order meet the stoplose:'.date('Y-m-d H:i:s',($endtime/1000)));
            
                $isfirstorder=1;
                $firstposition="";
              
            }else{

                // loggertest('kevintest2');

                if(($finalema10>$finalema20)&&($currentprice>$finalema20)&&($currentprice>$currentprice2)&&(($volume>100) && ($volume<300))&& ($volume<$volume2)){
                // if(($finalema10>$finalema20)&&($currentprice>$finalema20)&&($volume>$volume2)&&($currentprice>$currentprice2)){
                // if(($finalema10>$finalema20)&&($currentprice>$finalema20)&&($currentprice>$currentprice2)){
                // if(($finalema10>$finalema20)&&($currentprice>$finalema20)){
                    //做多
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                    
                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice-80;
                        $is_running_order=1;    
                        $action="long";
                        $position="";

                        orderloggertest('making order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);
                    
                        orderloggertest($finalema3);
                    }
                    loggertest('running order 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                
                }else if(($finalema10<$finalema20)&&($currentprice<$finalema20)&&($currentprice2>$currentprice)&&(($volume>100) && ($volume<300) && ($volume<$volume2))){
                // }else if(($finalema10<$finalema20)&&($currentprice<$finalema20)&&($volume>$volume2)&&($currentprice2>$currentprice)){
                // }else if(($finalema10<$finalema20)&&($currentprice<$finalema20)&&($currentprice2>$currentprice)){
                // }else if(($finalema10<$finalema20)&&($currentprice<$finalema20)){
                    //做空
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));

                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice+80;
                        $is_running_order=1;
                        $action="short";
                        $position="";

                        orderloggertest('making order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);

                        orderloggertest($finalema3);
                    }
                
                    loggertest('running order 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
                
                }
        
        
            }
        











        }else if($is_running_order==1){
            //cancel
        
            if($action=="long"){
                //close 做多
            
                    $diffprice =  $currentprice-$buyingprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl4($diffprice,$earninglvl);
            
                    // if($beforeearninglvl!=0){
                    //     $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                    //     loggertest($earninglvllog);
                    
                    //     if($beforeearninglvl>$earninglvl){
                    //         $canclose+=1;
                    //     }
                    
                    //     if($canclose>1){
                        if($earninglvl>0){
                            loggertest('diffprice>200 long:'.date('Y-m-d H:i:s',($endtime/1000)).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    // }

                    
                
                    //closed 当亏50%
                    if(($buyingprice-$currentprice)>200){
                        $allowtoclose =1;
                    }
                    if(($finalema10<$finalema20)){
                        $allowtoclose =1;
                        orderloggertest('closing order 做多:$finalema10<$finalema20');
                    }
                    if(($currentprice<($finalema20-30))){
                        $allowtoclose =1;
                        orderloggertest('closing order 做多:over sma50');
                    }

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

                        if(($currentprice-$buyingprice) < -80){
                            result(-80);  
                        }else{
                           result(($currentprice-$buyingprice));  
                        }

                        $is_running_order=0;
                    
                        orderloggertest('============================');
                    }
                
                loggertest('running cancel 做多:'.date('Y-m-d H:i:s',($endtime/1000)));
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    $diffprice =  $buyingprice-$currentprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl4($diffprice,$earninglvl);
            
                    // if($beforeearninglvl!=0){
                    //     $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                    //     loggertest($earninglvllog);
                    
                    //     if($beforeearninglvl>$earninglvl){
                    //         $canclose+=1;
                    //     }
                    
                    //     if($canclose>1){
                        if($earninglvl>0){
                            loggertest('diffprice>200 short:'.date('Y-m-d H:i:s',($endtime/1000)).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    // }

                     //closed 当亏50%
                    if(($currentprice-$buyingprice)>200){
                        $allowtoclose =1;
                    }

                    if(($finalema10>$finalema20)){
                        $allowtoclose =1;
                        orderloggertest('closing order 做空:$finalema10>$finalema20');
                    }

                    if(($currentprice>($finalema20+30))){
                        $allowtoclose =1;
                        orderloggertest('closing order 做空:over sma50');
                    }

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

                        if(($buyingprice-$currentprice) < -80){
                            result(-80);  
                        }else{
                            result(($buyingprice-$currentprice));  
                        }
                        
                        $is_running_order=0;     
                    
                        orderloggertest('============================');
                    }
                
                
                loggertest('running cancel 做空:'.date('Y-m-d H:i:s',($endtime/1000)));
            }
        
        
        
        }


        }
    
    loggertest(' ');
    $starttime+=60000;
    $endtime+=60000;
    // 防止超过 day_end 时继续循环
    if ($endtime > $day_end) {
        $is_running_order = 0;
        break; // 跳出当前内层循环
    }


}catch (\Exception $e){
        print_r($e->getMessage());
}

}

echo "Next Day=================";
echo PHP_EOL;

// 完成当天所有分钟数据后，跳到下一天
$current_day_start += 24 * 3600 * 1000; // 增加一天（毫秒）


}
