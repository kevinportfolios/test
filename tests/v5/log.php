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
	$folderName = 'test';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'log.txt';
	$filename= $folderName . '/' .'testlog3.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r";

    file_put_contents($filename,$contents);
}

function orderloggertest($log){
	$time=time()+(7*3600);
	$folderName = 'test';  // folder name
	// $filename= $folderName . '/' .date('Y-m-d',$time).'orderlog.txt';
	$filename= $folderName . '/' .'testorderlog3.txt';
	if(!file_exists($filename)){
	   file_put_contents($filename,'');
	}
	
	$contents = file_get_contents($filename);
	$contents .= "$log\r\r";

    file_put_contents($filename,$contents);
}