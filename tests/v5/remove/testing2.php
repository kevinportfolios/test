<?php
/**
 * using volume
 * 5minutes
 * */
// use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';
include 'cal_ema.php';
include 'log.php';
include 'record.php';





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
$is_running_order=0;








$currenttime="1705744980000";
// $beforetime="1705744800000";

while($currenttime!="1705745160000"){

$beforetime=(string)((int)$currenttime-180000);










try {

    $record3min=array();
    $closePrice4=array();
    $record3min=json_decode($record3,true);

    $volume=$record3min[$currenttime]["volume"];
    $currentprice=$record3min[$currenttime]["closeprice"];
    $closePrice4[] = $record3min[$currenttime]["closeprice"];
    $closePrice4[] = $record3min[$beforetime]["closeprice"];


/////////////////////////////////////////////////////////////////////

    $closePrice3=array();
    $closePrice10=array();
    $record1min=array();
  

    $record1min=json_decode($record1,true);
    $closePrice10[]= $record1min[$currenttime]["closeprice"];
    $closePrice10[]= $record1min[(string)((int)$currenttime-60000)]["closeprice"];
    $closePrice10[]= $record1min[(string)((int)$currenttime-120000)]["closeprice"];
    $closePrice10[]= $record1min[(string)((int)$currenttime-180000)]["closeprice"];

    $closePrice3 = array_slice($closePrice10, 0, 4);//checking for order again
/////////////////////////////////////////////////////////////////////



  
    $finalema= 'time: '.$currenttime.'closeprice: '.json_encode($closePrice4).';volume: '.$volume;
    logger3($finalema);
    echo $finalema;
    echo PHP_EOL; echo PHP_EOL;

    if($volume>1000){
        logger3("volume more than 1000");
    }else if($volume>900){
        logger3("volume more than 900");
    }else if($volume>800){
        logger3("volume more than 800");
    }else if($volume>700){
        logger3("volume more than 700");
    }else if($volume>600){
        logger3("volume more than 600");
    }else if($volume>500){
        logger3("volume more than 500");
    }else if($volume>400){
        logger3("volume more than 400");
    }






    //checking for first trade
    if(($closePrice4[0]>$closePrice4[1])&&$isfirstorder==1&&$volume>500){
        $isfirstorder=0;
        $position ="short to long";
        logger3("start order");
    }else if(($closePrice4[0]<$closePrice4[1])&&$isfirstorder==1&&$volume>500){
        $isfirstorder=0;
        $position ="long to short";
        logger3("start order");
    }












//start trade
if($isfirstorder==0){






if($is_running_order==0){
    //order

    if($position==""){
        logger3('order meet the stoplose');
        orderlogger3('order meet the stoplose:'.$currenttime);
       
        $isfirstorder=0;
        $firstposition="";

        if($closePrice4[0]>$closePrice4[1]){
            $position ="short to long";
        }else if($closePrice4[0]<$closePrice4[1]){
            $position = "long to short";
        }
      

    }else{






    if($closePrice4[0]>$closePrice4[1]){
        //做多
        logger3('position:'.$position);

        $array = $closePrice3;
        $allBigger = true;
         //前面的大过后面的value
         // Check if each value is greater than its succeeding value
        for ($i = 0; $i < count($array) - 1; $i++) {
            if ($array[$i] <= $array[$i + 1]) {
                $allBigger = false;
                break;
            }
        }

     
        
        if($volume>500 && $allBigger){
        logger3('allBigger:'.json_encode($closePrice3));

        if($position =="short to long"){
            $buyingprice=$currentprice;
            //if not,open order
             //做多
             logger3('order 做多:'.$currenttime);
             orderlogger3('making order 做多:'.$currenttime);

             $stopLoss=$currentprice-60;
             $is_running_order=1;
             $position="";
             $action="long";

        }
        logger3('running order 做多:'.$currenttime);



        }
      


    }else if($closePrice4[0]<$closePrice4[1]){
        //做空
      
        logger3('position:'.$position);

        $array = $closePrice3;
        //后面的大过前面的value
        // Check if each value is greater than its preceding value
        $allBigger = true;
        for ($i = 1; $i < count($array); $i++) {
            if ($array[$i] <= $array[$i - 1]) {
                $allBigger = false;
                break;
            }
        }

   
        if($volume>500 && $allBigger){
        logger3('allBigger:'.json_encode($closePrice3));
    
        if($position =="long to short"){
            $buyingprice=$currentprice;
            //if not,open order
            //做空
            logger3('order 做空:'.$currenttime);
            orderlogger3('making order 做空:'.$currenttime);
            $stopLoss=$currentprice+60;
            $is_running_order=1;
            $position="";
            $action="short";
        }

        logger3('running order 做空:'.$currenttime);



        

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
                logger3($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }

                // if($before1minprice>$currentprice){
                //     $canclose+=1;
                // }
               
                if($canclose>0){
                    logger3('diffprice>200 long:'.$currenttime.';buyingprice:'.$buyingprice.';currentprice:'.$currentprice);

                    $allowtoclose =1;
                    $firstposition="";
                    $isfirstorder=0;

                    $canclose=0;
                    $earninglvl=0;
                    $beforeearninglvl=0;

                }
            }

           
            if($allowtoclose==1){
                // $closingprice=$closePrice20[0];
                logger3('close 做多:'.$currenttime);

                // try {
                //     $result=$bybit->cancel()->postCancel([
                //         'category'=>'linear',
                //         'action'=>'PositionClose',
                //         'closeOnTrigger'=>true,
                //         'createType'=>'CreateByClosing',
                //         'leverage'=>'100',
                //         'leverageE2'=>'10000',
                //         'orderType'=>'Market',
                //         'positionIdx'=> '0',
                //         'price'=>'0',
                //         'qty'=>'0.001',
                //         //'qtyX'=>"100000",
                //         'side'=>"Sell",
                //         'symbol'=>"BTCUSDT",
                //         'timeInForce'=>"GoodTillCancel",
                //         'type'=>"Activity",
                //     ]);


                //     if($result["retCode"]!=0){
                //         $error = 'close 做多 error: '.$result["retCode"].';'.$result["retMsg"];
                //         logger3($error);
                //     }else{
                //         $position ="long to short";
                //         $allowtoclose =0;
                //         // $overprice = $currentprice;
                //     }

                    
                //     orderlogger3('closing order 做多:'.$currenttime);
                // }catch (\Exception $e){
                //     $error = 'close 做多 error: '.$e->getMessage();
                //     logger3($error);
                //     break;
                // }
                
                $position ="long to short";
                $allowtoclose =0;
    

            }
          
        logger3('running cancel 做多:'.$currenttime);
       


   
    }else if($action=="short"){
       //close 做空
      

            $diffprice =  $buyingprice-$currentprice;
            $beforeearninglvl=$earninglvl;
            $earninglvl= getearninglvl2($diffprice,$earninglvl);

            if($beforeearninglvl!=0){
                $earninglvllog= 'beforeearninglvl: '.$beforeearninglvl.' , '.'earninglvl: '.$earninglvl;
                logger3($earninglvllog);

                if($beforeearninglvl>$earninglvl){
                    $canclose+=1;
                }
               
                // if($currentprice>$before1minprice){
                //     $canclose+=1;
                // }
               
                // if(($beforeearninglvl>$earninglvl)&&($currentprice>$before1minprice)){
                if($canclose>0){
                    logger3('diffprice>200 short:'.$currenttime.';buyingprice:'.$buyingprice.';currentprice:'. $currentprice);

                    $allowtoclose =1;
                    $firstposition="";
                    $isfirstorder=0;

                    $canclose=0;
                    $earninglvl=0;
                    $beforeearninglvl=0;
            
                }
            }

            
            if($allowtoclose==1){
                // $closingprice=$closePrice20[0];
                logger3('close 做空:'.$currenttime);
             
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
                        logger3($error);
                    }else{
                        $position ="short to long";
                        $allowtoclose =0;
                        // $overprice = $currentprice;
                    }

                    orderlogger3('closing order 做空:'.$currenttime);
                }catch (\Exception $e){
                    $error = 'close 做空 error: '.$e->getMessage();
                    logger3($error);
                    break;
                }

            }
        

        logger3('running cancel 做空:'.$currenttime);
    }



}





}





$currenttime+=180000;

    
logger3(' ');

}catch (\Exception $e){
    print_r($e->getMessage());
}










}












