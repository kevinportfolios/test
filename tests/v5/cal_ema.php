<?php

function ema($src, $length) {
    $alpha = 2 / ($length + 1);
    $ema = [];
    $ema[0] = $src[0];

    for ($i = 1; $i < count($src); $i++) {
        $ema[$i] = $alpha * $src[$i] + (1 - $alpha) * $ema[$i - 1];
    }

    return $ema;
}



function ma($source, $length, $type) {
    switch ($type) {
        case "SMA":
            return array_slice(array_values(array_map(function ($i) use ($source, $length) {
                return array_sum(array_slice($source, $i - $length + 1, $length)) / $length;
            }, array_keys($source))), $length - 1);
        case "EMA":
            return ema($source, $length);
        case "SMMA (RMA)":
            // Implement the calculation for SMMA (RMA) if needed
            break;
        case "WMA":
            // Implement the calculation for WMA if needed
            break;
        case "VWMA":
            // Implement the calculation for VWMA if needed
            break;
        default:
            return [];
    }
}

// $len = 9;
// $src = [/* Your array of source values here */];
// $offset = 0;

// $out = ema($src, $len);

// // Plot EMA
// echo "EMA: ";
// print_r($out);

// $typeMA = "SMA";
// $smoothingLength = 5;

// $smoothingLine = ma($out, $smoothingLength, $typeMA);

// // Plot Smoothing Line
// echo "Smoothing Line: ";
// print_r($smoothingLine);



function calculateEMA($data, $length) {
    // Validate input data
    if (count($data) < $length) {
        return false; // Not enough data points
    }

    $alpha = 2 / ($length + 1);
    $ema = [];

    // Calculate initial EMA as the simple moving average (SMA) of the first $length data points
    $sma = array_sum(array_slice($data, 0, $length)) / $length;
    $ema[] = $sma;

    // Calculate EMA for the rest of the data
    for ($i = $length; $i < count($data); $i++) {
        $emaValue = $alpha * $data[$i] + (1 - $alpha) * $ema[$i - $length];
        $ema[] = $emaValue;
    }

    return $ema;
}



function trader_sma($values, $period) {
    $total = array_sum(array_slice($values, -$period));
    $sma = $total / $period;
    return $sma;
}


//1minutes
function getearninglvl($diffprice,$earninglvl) {

    if ($diffprice > 75) {
        $earninglvl = 75;
    } 
    if ($diffprice > 100) {
        $earninglvl = 100; //25%
    } 
    if ($diffprice > 125) {
        $earninglvl = 125; 
    } 
    if ($diffprice > 150) {
        $earninglvl = 150; 
    } 

    
    if ($diffprice > 200) {
        $earninglvl = 200; // 50%
    } 
    if ($diffprice > 300) {
        $earninglvl = 300; // 75%
    }
    if ($diffprice > 400) {
        $earninglvl = 400; // 100%
    }
    if ($diffprice > 500) {
        $earninglvl = 500;
    }
    if ($diffprice > 600) {
        $earninglvl = 600;
    }
    if ($diffprice > 700) {
        $earninglvl = 700;
    }
    if ($diffprice > 800) {
        $earninglvl = 800;
    }
    if ($diffprice > 900) {
        $earninglvl = 900;
    }
    if ($diffprice > 1000) {
        $earninglvl = 1000;
    }
    if ($diffprice > 1100) {
        $earninglvl = 1100;
    }
    if ($diffprice > 1200) {
        $earninglvl = 1200;
    }

    return $earninglvl;
}



//5minutes
function getearninglvl2($diffprice,$earninglvl) {
    if ($diffprice > 125) {
        $earninglvl = 125; 
    } 
    if ($diffprice > 150) {
        $earninglvl = 150; 
    } 
    if ($diffprice > 200) {
        $earninglvl = 200; // 50%
    } 
    if ($diffprice > 250) {
        $earninglvl = 250;
    } 
    if ($diffprice > 300) {
        $earninglvl = 300; // 75%
    }
    if ($diffprice > 400) {
        $earninglvl = 400; // 100%
    }
    if ($diffprice > 500) {
        $earninglvl = 500;
    }
    if ($diffprice > 600) {
        $earninglvl = 600;
    }
    if ($diffprice > 700) {
        $earninglvl = 700;
    }
    if ($diffprice > 800) {
        $earninglvl = 800;
    }
    if ($diffprice > 900) {
        $earninglvl = 900;
    }
    if ($diffprice > 1000) {
        $earninglvl = 1000;
    }
    if ($diffprice > 1100) {
        $earninglvl = 1100;
    }
    if ($diffprice > 1200) {
        $earninglvl = 1200;
    }

    return $earninglvl;
}


//15minutes
function getearninglvl3($diffprice,$earninglvl) {
   
    // if ($diffprice > 150) {
    //     $earninglvl = 150; 
    // } 
    // if ($diffprice > 200) {
    //     $earninglvl = 200; // 50%
    // } 
    if ($diffprice > 250) {
        $earninglvl = 250;
    } 
    if ($diffprice > 300) {
        $earninglvl = 300; // 75%
    }
    if ($diffprice > 400) {
        $earninglvl = 400; // 100%
    }
    if ($diffprice > 500) {
        $earninglvl = 500;
    }
    if ($diffprice > 600) {
        $earninglvl = 600;
    }
    if ($diffprice > 700) {
        $earninglvl = 700;
    }
    if ($diffprice > 800) {
        $earninglvl = 800;
    }
    if ($diffprice > 900) {
        $earninglvl = 900;
    }
    if ($diffprice > 1000) {
        $earninglvl = 1000;
    }
    if ($diffprice > 1100) {
        $earninglvl = 1100;
    }
    if ($diffprice > 1200) {
        $earninglvl = 1200;
    }

    return $earninglvl;
}


function getearninglvleth($diffprice,$earninglvl) {
   
    // if ($diffprice > 150) {
    //     $earninglvl = 150; 
    // } 
    // if ($diffprice > 200) {
    //     $earninglvl = 200; // 50%
    // } 

    if ($diffprice > 40) {
        $earninglvl = 40;
    } 
    if ($diffprice > 50) {
        $earninglvl = 50;
    } 
    if ($diffprice > 75) {
        $earninglvl = 75; // 75%
    }
    if ($diffprice > 100) {
        $earninglvl = 100; // 100%
    }
    if ($diffprice > 125) {
        $earninglvl = 125;
    }
    if ($diffprice > 150) {
        $earninglvl = 150;
    }
    if ($diffprice > 175) {
        $earninglvl = 175;
    }
    if ($diffprice > 200) {
        $earninglvl = 200;
    }
    if ($diffprice > 225) {
        $earninglvl = 225;
    }
    if ($diffprice > 250) {
        $earninglvl = 250;
    }
    if ($diffprice > 275) {
        $earninglvl = 275;
    }
    if ($diffprice > 300) {
        $earninglvl = 300;
    }

    return $earninglvl;
}