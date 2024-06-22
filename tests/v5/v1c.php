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
loggertestvol($startingtime);


$starttime = 1718622000000;//开始时间前1小时
$endtime = 1718625600000;//想要的开始时间
$allowtime = $starttime;

$is_running_order=0;

while(1){
$time=time()+(8*3600);
$start=1596446400;

if($time>$start){

    //结束时间
    if($endtime>=1718647200000){
        loggertestvol("Reach end time");
        echo "Reach end time";

        $endingtime= 'Ending Time:'.date('Y-m-d H:i:s',(time()+(8*3600)));
        loggertestvol($endingtime);
        break;
    }

    // $t=$time-$start;
    //如果要24小时执行
    //if(is_int($t/86400)){

    //方便测试每10秒执行一次
    // if(is_int($t/5)){
        // echo 'Process Time:'.date('Y-m-d H:i:s',$time).PHP_EOL;
        $processtime= 'Process Time:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600)));
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
            'interval'=>'3',
            'start'=>$starttime,
            'end'=>$endtime,
            // 'limit'=>'100',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertestvol($error);
        break;
    }

    try {
        $getKline2=$bybit->market()->getKline([
            'category'=>'linear',
            'symbol'=>'BTCUSDT',
            'interval'=>'1',
            'start'=>$starttime,
            'end'=>$endtime,
            // 'limit'=>'100',
        ]);
    }catch (\Exception $e){
        $error = 'getKline error: '.$e->getMessage();
        loggertestvol($error);
        break;
    }


   
     // $closePrice3=array();
     $closePrice4=array();
     $closePrice20=array();
     $closePrice202=array();
    
    
     foreach ($getKline["result"]["list"] as $r) {
         $closePrice20[]=$r["4"];
     }

     foreach ($getKline2["result"]["list"] as $r) {
        $closePrice202[]=$r["4"];
    }

     $average10 = calculateEMA($closePrice20, 10);
     $average20 = calculateEMA($closePrice20, 20);
     $finalema10=$average10[0];
     $finalema20=$average20[0];

     $currentprice=$closePrice20[0];
     $currentprice2=$closePrice202[0];
     $closePrice4 = array_slice($closePrice20, 0, 5);

 
     $volume=$getKline["result"]["list"][0]["5"];
     $volume2=$getKline["result"]["list"][1]["5"];
 
    // $closePrice3 = array_slice($closePrice10, 0, 2);//checking for order again
 
 
 
     $finalema= 'closeprice: '.json_encode($closePrice4).';volumenow: '.$volume.';volumebefore: '.$volume2;
     loggertestvol($finalema);

     $closePrice5 = array_slice($closePrice202, 0, 5);
     $finalemaa= 'closeprice2: '.json_encode($closePrice5);
     loggertestvol($finalemaa);

    //  $turnoverlog= 'turnovernow: '.$turnover.';turnoverbefore: '.$turnover2;
    //  loggertestvol($turnoverlog);
     // echo $volume;
     // echo PHP_EOL;
     // echo $volume2;
     // echo PHP_EOL; echo PHP_EOL;
 
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
 
 
 
 
 
 
     //checking for first trade
    //  if(($closePrice4[0]>$closePrice4[1])&&$isfirstorder==1&&($volume>600&&($volume>$volume2))){
    // if($isfirstorder==1&&($volume>600&&$volume2>400&&($volume>$volume2))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
    // if($isfirstorder==1&&($volume>600&&(($volume-$volume2)>=400))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)&&($finalema10>$finalema20)){
    if($isfirstorder==1&&($volume>600&&(($volume-$volume2)>=400))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
         $isfirstorder=0;
         $position ="short to long";
         loggertestvol("start order");
    //  }else if(($closePrice4[0]<$closePrice4[1])&&$isfirstorder==1&&($volume>600&&($volume>$volume2))){
    // }else if($isfirstorder==1&&($volume>600&&$volume2>400&&($volume>$volume2))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
    // }else if($isfirstorder==1&&($volume>600&&(($volume-$volume2)>=400))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])&&($finalema10<$finalema20)){
    }else if($isfirstorder==1&&($volume>600&&(($volume-$volume2)>=400))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
         $isfirstorder=0;
         $position ="long to short";
         loggertestvol("start order");
     }


     


    //start trade
    if($isfirstorder==0){
        

        if($is_running_order==0 && (intval($starttime) >= intval($allowtime)) ){
            //order
        
            if($position==""){
                loggertestvol('order meet the stoplose');
                orderloggertestvol('order meet the stoplose:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
            
                $isfirstorder=1;
                $firstposition="";

                // if($closePrice4[0]>$closePrice4[1]){
                //     $position ="short to long";
                // }else if($closePrice4[0]<$closePrice4[1]){
                //     $position = "long to short";
                // }
              
            }else{

            
                // if(($closePrice4[0]>$closePrice4[1])&&($closePrice4[1]>$closePrice4[2])&&($volume>600&&($volume>$volume2))&&($closePrice20[0]>$finalema10)){
                // if(($volume>600)&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
                // if(($volume>600&&$volume2>400&&($volume>$volume2))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
                // if(($volume>600&&(($volume-$volume2)>=400))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)&&($finalema10>$finalema20)){
                if(($volume>600&&(($volume-$volume2)>=400))&&($closePrice20[0]>$finalema10)&&($closePrice20[0]>$finalema20)){
                    //做多
                    loggertestvol('position:'.$position.';volume:'.$volume);
                
                    if($position !=""){
                        loggertestvol('order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                    
                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice-80;
                        $is_running_order=1;    
                        $action="long";
                        $position="";

                        orderloggertestvol('making order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume);
                        orderloggertestvol('stopLoss:'.$stopLoss);
                    
                    }
                    loggertestvol('running order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                // }else if(($closePrice4[0]<$closePrice4[1])&&($closePrice4[1]<$closePrice4[2])&&($volume>600&&($volume>$volume2))&&($finalema10>$closePrice20[0])){
                // }else if(($volume>600)&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
                // }else if(($volume>600&&$volume2>400&&($volume>$volume2))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
                // }else if(($volume>600&&(($volume-$volume2)>=400))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])&&($finalema10<$finalema20)){
                }else if(($volume>600&&(($volume-$volume2)>=400))&&($finalema10>$closePrice20[0])&&($finalema20>$closePrice20[0])){
                    //做空
                    loggertestvol('position:'.$position.';volume:'.$volume);
                
                    if($position !=""){
                        loggertestvol('order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));

                        $buyingprice=$closePrice20[0];
                        $stopLoss=$buyingprice+80;
                        $is_running_order=1;
                        $action="short";
                        $position="";

                        orderloggertestvol('making order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        orderloggertestvol('buyingprice:'.$buyingprice.';volume:'.$volume);
                        orderloggertestvol('stopLoss:'.$stopLoss);
                    }
                
                    loggertestvol('running order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                }
        
        
            }
        











        }else if($is_running_order==1){
            //cancel
        
            $ismeetstoplose=0;

            if($action=="long"){
                //close 做多
            
                    $diffprice =  $currentprice2-$buyingprice;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertestvol($earninglvllog);
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                            loggertestvol('canclose:'.$canclose);
                        }
                        if($canclose>2){
                            loggertestvol('diffprice>200 long:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))).';buyingprice:'.$buyingprice.';currentprice:'.$currentprice2);
                        
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
                    // if(($finalema10<$finalema20)){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做多:$finalema10<$finalema20');
                    // }
                    // if(($currentprice<($finalema20-30))){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做多:over sma50');
                    // }

                    if($stopLoss>$currentprice){
                        $allowtoclose =1;
                        $ismeetstoplose =1;
                        orderloggertestvol('order meet the stoplose');
                    }
                
                    if( $allowtoclose==1 ){
                        loggertestvol('close 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        $position ="long to short";
                        $allowtoclose =0;
                        orderloggertestvol('closing order 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));         
                      
                        if($ismeetstoplose == 1){
                            orderloggertestvol('stopLoss:'.$stopLoss.' - buyingprice:'.$buyingprice.' = '.($stopLoss-$buyingprice));    
                            resultvol(($stopLoss-$buyingprice));  
                        }else{
                            orderloggertestvol('currentprice:'.$currentprice.' - buyingprice:'.$buyingprice.' = '.($currentprice-$buyingprice));    
                            resultvol(($currentprice-$buyingprice));  
                        }
                        
                        $is_running_order=0;
                        $allowtime = $starttime +  60000;
                        orderloggertestvol('============================');
                    }
                
                loggertestvol('running cancel 做多:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                
                
                
                
            }else if($action=="short"){
               //close 做空
            
                    $diffprice =  $buyingprice-$currentprice2;
                    $beforeearninglvl=$earninglvl;
                    $earninglvl= getearninglvl3($diffprice,$earninglvl);
            
                    if($beforeearninglvl!=0){
                        $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                        loggertestvol($earninglvllog);
                        
                    
                        if($beforeearninglvl>$earninglvl){
                            $canclose+=1;
                            loggertestvol('canclose:'.$canclose);
                        }
                        if($canclose>2){
                            loggertestvol('diffprice>200 short:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))).';buyingprice:'.$buyingprice.';currentprice:'. $currentprice2);
                        
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

                    // if(($finalema10>$finalema20)){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做空:$finalema10>$finalema20');
                    // }

                    // if(($currentprice>($finalema20+30))){
                    //     $allowtoclose =1;
                    //     orderloggertestvol('closing order 做空:over sma50');
                    // }

                    if($currentprice>$stopLoss){
                        $allowtoclose =1;
                        $ismeetstoplose =1;
                        orderloggertestvol('order meet the stoplose');
                    }
                
                    if( $allowtoclose==1 ){
                        loggertestvol('close 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                        $position ="short to long";
                        $allowtoclose =0;
                        orderloggertestvol('closing order 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
                      
                        if($ismeetstoplose == 1){
                            orderloggertestvol('buyingprice:'.$buyingprice.' - stopLoss:'.$stopLoss.' = '.($buyingprice-$stopLoss));    
                            resultvol(($buyingprice-$stopLoss));  
                        }else{
                            orderloggertestvol('buyingprice:'.$buyingprice.' - currentprice:'.$currentprice.' = '.($buyingprice-$currentprice));    
                            resultvol(($buyingprice-$currentprice));  
                        }
                        $is_running_order=0;     
                        $allowtime = $starttime +  60000;
                        orderloggertestvol('============================');
                    }
                
                
                loggertestvol('running cancel 做空:'.date('Y-m-d H:i:s',($endtime/1000+(8*3600))));
            }
        
        
        
        }


    }
    
    loggertestvol(' ');



    $starttime+=5000;
    $endtime+=5000;

    // $starttime+=10000;
    // $endtime+=10000;

}catch (\Exception $e){
        print_r($e->getMessage());
}

}

// sleep(1);

}
