#!/usr/bin/php
<?php

# Call : php sip_register.php search number(numerical)
# This script checks if the number has a registered session on the sip server and prints all entries for that number
# Format log: INSERT INTO T_REGISTERED VALUES(101072556,'612222222','','sip:612222222','sip:612222222@10.10.10.10:5060','','10.10.10.10:5060',180,1000,1493776801085,1493776981085,'10.10.10.10',0)

date_default_timezone_set('Europe/Berlin'); 
function write_file($search_string, $entry_number){
$search_str = 'search';
$dir = "/dir/in/linux/server/";
$handle = fopen($dir."registered.log", "r") or die("Unable to open file");	
#$handle_to_write = fopen(__DIR__."/write.txt", "w") or die("Unable to open file");		
	#Counter to line count
	$start_line = 1;
	$status = 0;
	#if number != 9
	if(strlen($entry_number) != 9){
		echo '#################################################'.PHP_EOL;
		echo PHP_EOL.'Sorry number is short(9 numbers)'.PHP_EOL.PHP_EOL;
		echo '#################################################'.PHP_EOL;
		exit();
	}
	#if number(only number) have special chars and char 
	if (strval($entry_number) != strval(intval($entry_number)) ){
		echo '###############################################################################'.PHP_EOL;
		echo PHP_EOL.'Sorry use only numbers'.PHP_EOL.PHP_EOL;
		echo '###############################################################################'.PHP_EOL;
		exit();
	}
	#if input data it's ok
	if($search_string == $search_str){
		echo '######################## Entries for the number :'.$entry_number.' ########################'.PHP_EOL;
		if ($handle) {
			#read file
			while(($line = fgets($handle)) !== false){ 
					#if line starts with INSERT
					if(substr($line,0,6) == 'INSERT'){
						#Counter line ++
						$start_line++;
						#remove ' and ,
						$line = preg_split("/[',]/",$line);
						#preg unix timestamp for date register
						preg_match('/(?<=)[0-9]{10}/', $line[21], $exit_update);
						#preg unix timestamp for date unregister
						preg_match('/(?<=)[0-9]{10}/', $line[22], $exit_expiry);
						#preg number
						preg_match('/(?<=)[^sip:]+/', $line[8], $exit_number);
						$number = $exit_number[0];
							#if time data is empty return: Thursday 1970-01-01 02:00:00
							if(isset($exit_update[0])){
								$time_update = $exit_update[0];}
							else{$time_update = "Thursday 1970-01-01 02:00:00";
							}
							if(isset($exit_expiry[0])){
								$time_expiry = $exit_expiry[0];}
							else{$time_update = "Thursday 1970-01-01 02:00:00";
							}						
						# convert to DAY 2017-04-19 18:50:03 (+ 0100) 
						$date_update = gmdate('l Y-m-d H:i:s ', $time_update + 3600*(1+date("I"))); 
						$date_expiry = gmdate('l Y-m-d H:i:s ', $time_expiry + 3600*(1+date("I")));
						#string msg for console
						$lines = 'Number: '.$number.' Host: '.$line[17].' Reg.Date: '.$date_update.' Exp.Date: '.$date_expiry.'SIP: '.$line[11].' Expires:'.$line[19].' Priority: '.$line[20].PHP_EOL;
						if($entry_number == $number){
							$status = 1;
							echo $lines;
						}
						#fwrite_stream($handle_to_write, $line);
					}
				}if($status != 1){echo PHP_EOL.'I found nothing'.PHP_EOL.PHP_EOL;}
		   fclose($handle);
		echo '######################## Processed lines:'.$start_line.' ########################'.PHP_EOL;	
		}
	}else{
		echo 'Bad input data: search (eg. 612222222)'.PHP_EOL;
	}
}
write_file($argv[1], $argv[2]);
/*
Funkcja wpsujaca do pliku

function fwrite_stream($fp, $array) {
    for ($writt = 0; $writt < count($array); $writt += $fwrite) {
        $fwrite = fwrite($fp, print_r($array, true));
        if ($fwrite === false) {
            return $writt;
        }
    }
    return $writt;
}*/
?>