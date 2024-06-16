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



$startingtime= 'Starting Time:'.date('Y-m-d H:i:s',(time()+(8*3600)));
loggertest($startingtime);


$starttime = 1717412400000;//开始时间前1小时
$endtime = 1717416000000;//想要的开始时间

$is_running_order=0;

while(1){
$time=time()+(8*3600);
$start=1596446400;

if($time>$start){

    //结束时间
    if($endtime>=1717437600000){
        loggertest("Reach end time");
        echo "Reach end time";

        $endingtime= 'Ending Time:'.date('Y-m-d H:i:s',(time()+(8*3600)));
        loggertest($endingtime);
        break;
    }

    // $t=$time-$start;
    //如果要24小时执行
    //if(is_int($t/86400)){

    //方便测试每10秒执行一次
    // if(is_int($t/5)){
        // echo 'Process Time:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        $processtime= 'Process Time:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600)));
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

    $starttime+=5000;
    $endtime+=5000;
   
    $closePrice20=array();
    foreach ($getKline["result"]["list"] as $r) {
        $closePrice20[]=$r["4"];
    }
    // print_r($closePrice20);

    $average10 = calculateEMA($closePrice20, 10);
    $average20 = calculateEMA($closePrice20, 20);

    $currentprice=$closePrice20[0];

    //start order
    $finalema10=$average10[0];
    $finalema20=$average20[0];
  
    $finalema= 'finalema20: '.$finalema10.' , '.'finalema50: '.$finalema20;
    loggertest($finalema);
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
        loggertest("start order");
    }else if($firstposition=="short"&&($finalema10>$finalema20)&&$isfirstorder==1){
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
                orderloggertest('order meet the stoplose:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
            
                $isfirstorder=1;
                $firstposition="";
              
            }else{

            
                if($finalema10>($finalema20+10)&&($currentprice>$finalema20)){
                    //做多
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                    
                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice-80;
                        $is_running_order=1;    
                        $action="long";
                        $position="";

                        orderloggertest('making order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);
                    
                    }
                    loggertest('running order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                }else if(($finalema10+10)<$finalema20&&($currentprice<$finalema20)){
                    //做空
                    loggertest('position:'.$position);
                
                    if($position !=""){
                        loggertest('order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));

                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice+80;
                        $is_running_order=1;
                        $action="short";
                        $position="";

                        orderloggertest('making order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        orderloggertest('buyingprice:'.$buyingprice);
                        orderloggertest('stopLoss:'.$stopLoss);
                    }
                
                    loggertest('running order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                }
        
        
            }
        











        }else if($is_running_order==1){
            //cancel
        
            if($action=="long"){
                //close 做多
            
                    $diffprice =  $currentprice-$buyingprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertest($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                        }
                    
                        if($canclose>1){
                            loggertest('diffprice>200 long:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

                    
                
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
                        loggertest('close 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        $position ="long to short";
                        $allowtoclose =0;
                        orderloggertest('closing order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));         
                        orderloggertest('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));    
                        result(($currentprice-$buyingprice));  
                        $is_running_order=0;
                    
                        orderloggertest('============================');
                    }
                
                loggertest('running cancel 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    $diffprice =  $buyingprice-$currentprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertest($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                        }
                    
                        if($canclose>1){
                            loggertest('diffprice>200 short:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);
                        
                            $allowtoclose =1;
                            $firstposition="";
                            $isfirstorder=0;
                        
                            $canclose=0;
                            $earninglvl=0;
                            $beforeearninglvl=0;
                        
                        }
                    }

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
                        loggertest('close 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        $position ="short to long";
                        $allowtoclose =0;
                        orderloggertest('closing order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        orderloggertest('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                        result(($buyingprice-$currentprice));  
                        $is_running_order=0;     
                    
                        orderloggertest('============================');
                    }
                
                
                loggertest('running cancel 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
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
