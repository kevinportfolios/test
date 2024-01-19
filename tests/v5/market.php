<?php
/**
 * @author lin <465382251@qq.com>
 * */
use \Lin\Bybit\BybitV5;

require __DIR__ .'../../../vendor/autoload.php';

include 'key_secret.php';

$bybit=new BybitV5();

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


try {
    $result=$bybit->market()->getKline([
        'category'=>'linear',
        'symbol'=>'BTCUSDT',
        'interval'=>'3',
        'limit'=>'20',
    ]);
    // print_r($result);
}catch (\Exception $e){
    print_r($e->getMessage());
}



$closePrice20=array();
   
foreach ($result["result"]["list"] as $r) {
    $closePrice20[]=$r["0"].";".$r["4"].";".$r["5"];
}

echo json_encode($closePrice20);
echo PHP_EOL; echo PHP_EOL;







// try {
//     $result=$bybit->market()->getIndexPriceKline([
//         'category'=>'linear',
//         'symbol'=>'BTCUSDT',
//         'interval'=>'1',
//         'limit'=>'3',
//     ]);
//     print_r($result);
// }catch (\Exception $e){
//     print_r($e->getMessage());
// }

