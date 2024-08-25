<?php



function logger($log){
	$time=time()+(7*3600);
	$folderName = 'log2';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderlogger($log){
	$time=time()+(7*3600);
	$folderName = 'log2';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}


function logger2($log){
	$time=time()+(7*3600);
	$folderName = 'log2';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'log2.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderlogger2($log){
	$time=time()+(7*3600);
	$folderName = 'log2';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'orderlog2.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}


function loggervol($log){
	$time=time()+(7*3600);
	$folderName = 'logvol';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'log2.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderloggervol($log){
	$time=time()+(7*3600);
	$folderName = 'logvol';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'orderlog2.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}

function loggereth($log){
	$time=time()+(7*3600);
	$folderName = 'logeth';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderloggereth($log){
	$time=time()+(7*3600);
	$folderName = 'logeth';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}

function testlogger($log){
	$time=time()+(7*3600);
	$folderName = 'log2';  // folder name
	$filename= $folderName . '/' .date('Y-m-d',$time).'logtest.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log";

    file_put_contents($filename,$contents);


}







function loggertest($log){
	$time=time()+(7*3600);
	$folderName = 'sma';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
	$filename= $folderName . '/' .'20240703testlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderloggertest($log){
	$time=time()+(7*3600);
	$folderName = 'sma';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	$filename= $folderName . '/' .'20240703testorderlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}

function result($log){
	$time=time()+(7*3600);
	$folderName = 'smaresult';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	$filename= $folderName . '/' .'20240703result.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}








//////////////////////////////


function loggertestvol($log){
	$runningdate ="20240825";
	$time=time()+(7*3600);
	$folderName = 'v1';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
	$filename= $folderName . '/' .$runningdate.'testlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderloggertestvol($log){
	$runningdate ="20240825";
	$time=time()+(7*3600);
	$folderName = 'v1';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	$filename= $folderName . '/' .$runningdate.'testorderlog.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}

function resultvol($log){
	$runningdate ="20240825";
	$time=time()+(7*3600);
	$folderName = 'v1result';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	$filename= $folderName . '/' .$runningdate.'result.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}




// function loggertestvol($log){
// 	$time=time()+(7*3600);
// 	$folderName = '3bar';  // folder name
// 	// $filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
// 	$filename= $folderName . '/' .'20240703testlog3.txt';
// 	if(!file_exists($filename)){
// 	   file_put_contents($filename,'');
// 	}
	
// 	$contents = file_get_contents($filename);
// 	$contents .= "$log\r";

//     file_put_contents($filename,$contents);
// }

// function orderloggertestvol($log){
// 	$time=time()+(7*3600);
// 	$folderName = '3bar';  // folder name
// 	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
// 	$filename= $folderName . '/' .'20240703testorderlog3.txt';
// 	if(!file_exists($filename)){
// 	   file_put_contents($filename,'');
// 	}
	
// 	$contents = file_get_contents($filename);
// 	$contents .= "$log\r\r";

//     file_put_contents($filename,$contents);
// }

// function resultvol($log){
// 	$time=time()+(7*3600);
// 	$folderName = '3barresult';  // folder name
// 	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
// 	$filename= $folderName . '/' .'20240703result3.txt';
// 	if(!file_exists($filename)){
// 	   file_put_contents($filename,'');
// 	}
	
// 	$contents = file_get_contents($filename);
// 	$contents .= "$log\r\r";

//     file_put_contents($filename,$contents);
// }