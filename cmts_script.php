#!/usr/bin/php
<?php
### This script(nagios plugin) check SNR value for two CMTS's.

require('connect.php');
##Connection with database 

###Error BLOCK
function errorHandler($errno, $errstr, $errfile, $errline) {
       throw new Exception($errstr, $errno);
}
###

$reg_string = "/[^0-9\-]*([-]?[0-9]*\.?[0-9]*)[^0-9]*/";
#Regular expressions validate the value chain

$community_cmts1 = 'xxx';
$community_cmts2 = 'xxx';
#community string to CMTS

$ret_time = 1500000;
#retry time

$status = 0;
### NAGIOS STATUS ###
# 0 - OK # 1 - warning # 2 - critical # 3 - empty

$msg = "";
#empty string for message

#oid for cmts1 and cmts2
$oid =  "Numerical OID SNMP";

### cmts1 MODULATION BLOCK ###
$modulation_qpsk_cmts1 = "QPSK";
$modulation_qam16_cmts1 = "QAM-16";
$modulation_qam64_cmts1 = "QAM-64";
$modulation_empty_cmts1 = "Nieznana";

### cmts2 MODULATION NUMERICAL BLOCK ###
$modulation_qpsk_cmts2_2 = 2;
$modulation_qpsk_cmts2_5 = 5;
$modulation_qpsk_cmts2_7 = 7; 
$modulation_qam16_cmts2_3 = 3;
$modulation_qam16_cmts2_6 = 6;
$modulation_qam16_cmts2_8 = 8;
$modulation_qam64_cmts2_4 = 4;
$modulation_qam64_cmts2_9 = 9;
$modulation_qpsk_cmts2_10 = 10;
$modulation_empty_cmts2 = "";

set_error_handler('errorHandler'); #add new exception

### SNR BLOCK ###
function snr($host, $id_module){
	
	### Global VARIABLE BLOCK ###
	global $oid;
	global $community_cmts2;
	global $community_cmts1;
	global $reg_string;
	global $ret_time;
	global $msg;
	global $status;
	global $modulation_empty_cmts1;
	global $modulation_qpsk_cmts1;
	global $modulation_qam16_cmts1;
	global $modulation_qam64_cmts1;
	global $modulation_qpsk_cmts2_2;
	global $modulation_qpsk_cmts2_5;
	global $modulation_qpsk_cmts2_7;
	global $modulation_qpsk_cmts2_10; 
	global $modulation_qam16_cmts2_3;
	global $modulation_qam16_cmts2_6;
	global $modulation_qam16_cmts2_8;
	global $modulation_qam64_cmts2_4;
	global $modulation_qam64_cmts2_9;
	global $modulation_empty_cmts2;
	
	### cmts1 ###
	#if id module is between 16865 and 19484
	if($id_module >= 16865 and $id_module <= 19484){
		connection();	#connection with database
		# select all columns with statistic_cmts2_table table
		$ref = mysql_query("SELECT * FROM statistic_cmts1_table where upstream_id like'$id_module' limit 1") or die("Zapytanie niepoprawne"); 
		#save columns to array
		$u = mysql_fetch_array($ref);
			
		if($modulation_qpsk_cmts1 == $u['upstream_modulation']){	
			$snr_warn = 12.5;
			$snr_crit = 12;
		}	
		elseif($modulation_qam16_cmts1 == $u['upstream_modulation']){	
			$snr_warn = 16.5;
			$snr_crit = 16;
		}
		elseif($modulation_qam64_cmts1 == $u['upstream_modulation']){	
			$snr_warn = 24.5;
			$snr_crit = 24;
		}
		elseif($modulation_empty_cmts1 == $u['upstream_modulation']){	
			$snr_warn = 24.5;
			$snr_crit = 24;
		}
		if($u['upstream_status'] == 'up'){
				try{
						$go_value = snmpget($host, $community_cmts1, $oid.$id_module, $ret_time);
						preg_match($reg_string, $go_value, $exit);
						$ex = $exit[1]*0.1;
						$value = $ex;
						if($value <= $snr_crit){
							$status = 2;
							if($msg == ""){
								$msg = "SNR poziom krytyczny dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}else{
								$msg = "SNR poziom krytyczny dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}
						}
						if($value > $snr_crit and $value <= $snr_warn){
							if($status < 2){
								$status = 1;
							}if($msg = ''){
								$msg = "SNR poziom ostrzegawczy dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}else{
								$msg = "SNR poziom ostrzegawczy dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}                       
						}
						if($msg == ''){
							$msg = 'SNR dla '.$u['upstream_opis'].' OK!';			
						}
						echo $msg;
						$msg = "";							
					}catch(Exception $e){
						$msg = 'SNMP ERROR for: '. $host." upstream: ". $u['upstream_opis'].PHP_EOL.$e;
						print($msg);
						$status = 3;
					}
						$status;
						$msg = "";
		}else{
			$msg = "Upstream posiada status DOWN";
			$status = 3;
		}
	}
	### cmts2 ###
	#if id module is between 66073 and 524914
	else if($id_module >= 66073 and $id_module <= 524914){
		
		connection();	
		# select all columns with statistic_cmts2_table table
		$ref = mysql_query("SELECT * FROM statistic_cmts2_table where upstream_id like'$id_module' limit 1") or die("Zapytanie niepoprawne"); 
		#save columns to array
		$u = mysql_fetch_array($ref);
		/*	
		if(($modulation_qpsk_cmts2_2 || $modulation_qpsk_cmts2_5 || $modulation_qpsk_cmts2_7) == $u['upstream_modulation']){	
			$snr_warn = 12.5;
			$snr_crit = 12;
		}	
		if(($modulation_qam16_cmts2_3 || $modulation_qam16_cmts2_6 || $modulation_qam16_cmts2_8) == $u['upstream_modulation']){	
			$snr_warn = 16.5;
			$snr_crit = 16;
		}
		if(($modulation_qam64_cmts2_4 || $modulation_qam64_cmts2_9) == $u['upstream_modulation']){	
                        $snr_warn = 34.5;
                        $snr_crit = 34;
		}*/
		if($modulation_qpsk_cmts2_2 == $u['upstream_modulation']){
						$snr_warn = 12.5;
                        $snr_crit = 12;
				}
	 	if($modulation_qpsk_cmts2_5 == $u['upstream_modulation']){
                        $snr_warn = 12.5;
                        $snr_crit = 12;
                }
		if($modulation_qpsk_cmts2_7 == $u['upstream_modulation']){
                        $snr_warn = 12.5;
                        $snr_crit = 12;
                }
	    if($modulation_qpsk_cmts2_10 == $u['upstream_modulation']){
                        $snr_warn = 12.5;
                        $snr_crit = 12;
                }
		if($modulation_qam16_cmts2_3 == $u['upstream_modulation']){
              		 $snr_warn = 16.5;
                         $snr_crit = 16;
		}
		if($modulation_qam16_cmts2_6 == $u['upstream_modulation']){
                         $snr_warn = 16.5;
                         $snr_crit = 16;
                }
		if($modulation_qam16_cmts2_8 == $u['upstream_modulation']){
                         $snr_warn = 16.5;
                         $snr_crit = 16;
                }
		if($modulation_qam64_cmts2_4 == $u['upstream_modulation']){
						$snr_warn = 24.5;
                        $snr_crit = 24;
				}
		if($modulation_qam64_cmts2_9 == $u['upstream_modulation']){
                        $snr_warn = 24.5;
                        $snr_crit = 24;
                }
		if($modulation_empty_cmts2 == $u['upstream_modulation']){	
			$snr_warn = 24.5;
			$snr_crit = 24;
		}
		//Test VALUE 	echo $snr_warn." ".$snr_crit." ".$u['upstream_modulation'];
		
		if($u['upstream_status'] == 'up'){
				try{
						$go_value = snmpget($host, $community_cmts2, $oid.$id_module, $ret_time);
						preg_match($reg_string, $go_value, $exit);
						$ex = $exit[1]*0.1;
						$value = $ex;
						if($value <= $snr_crit){
							$status = 2;
							if($msg == ""){
								$msg = "SNR poziom krytyczny dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}else{
								$msg = "SNR poziom krytyczny dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}
						}
						if($value > $snr_crit and $value <= $snr_warn){
							if($status < 2){
								$status = 1;
							}if($msg = ''){
								$msg = "SNR poziom ostrzegawczy dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";

							}else{
								$msg = "SNR poziom ostrzegawczy dla UPstream'u: ".$u['upstream_opis'].": ".$value." db";
							}                       
						}
						if($msg == ''){
							$msg = 'SNR dla '.$u['upstream_opis'].' OK!';			
						}
						echo $msg;
						$msg = "";
					}catch(Exception $e){
						$msg = 'SNMP ERROR for: '. $host." upstream: ".$u['upstream_opis'].PHP_EOL.$e;
						print($msg);
						$status = 3;
					}
						$status;
						$msg = "";
		}else{
			$msg =  "Upstream posiada status DOWN";
			$status = 3;
		}
	}
	else{
		$msg = "Nie znaleziono modulu na zadnym CMTS'ie";
		$status = 3;
	}
	echo $msg;
}
## script return messages with status and numerical variable for nagios 
foreach ($argv AS $arg){
    function_exists($arg) AND call_user_func($arg);
}
foreach ($argv as $i=>$arg ) {
	try{
		snr($argv[$i + 1], $argv[$i + 2]);
		exit($status);		
	}catch(Exception $e){
		echo 'Wystapil problem z wywolaniem sprawdz parametry wejscia'.PHP_EOL;
	}
}
?>
