#!/usr/bin/php
<?php 
# Call : php check_raid_battery_for_mrtg.php host(ip_numerical) mode(operational||parameters)

# This nagios script check state of battery array, input data download with output read_raid_battery script, 
# checks all values and return value in mrtg format:

/*
value
value
on
on
*/

# Target in mrtg.cfg file:
# Eg. Target[1-raid]: `php /servers/raid/check_raid_battery_for_mrtg.php 10.10.10.11 operational`


date_default_timezone_set('Europe/Berlin'); 
set_time_limit(300); 
function errorHandler($errno, $errstr, $errfile, $errline) 
{
       throw new Exception($errstr, $errno);
}
#Exception for try->catch
set_error_handler('errorHandler'); 
		
function check_charge_state($host, $mode){
#empty string and string for preg_match
$msg = ''; 
$reg_string_all = "/(?<=)Operational|Optimal|[0-9]+/"; 
$reg_string_other = "/(?<=)OK|Yes/";

#static numerical value for variable
#temperature
$msg_temp = 0;
#voltage
$msg_volt = 0;
#operational
$msg_oper = 0;
#charge %
$msg_charge = 0;
#ready status
$msg_ready = 0;
	
	$handle_to_read = file("/battery/".$host.".txt");
	try{
		/* read txt file with input:
			#Voltage: 4005 mV
			#Temperature: 14 C
			#Battery State     : Operational
			#Relative State of Charge: 95 %

			#or for 10.10.10.11
			#/c0/bbu BBU Ready                 = Yes
			#/c0/bbu BBU Status                = OK
			#/c0/bbu Battery Voltage           = OK
			#/c0/bbu Battery Temperature Status= OK
		*/
		foreach($handle_to_read as $key => $handle){
			if($host == '10.10.10.11'){
				#match value in lines
				preg_match($reg_string_other, $handle, $exit_other);
				#create new array with output preg_match and seperation on $key=>$value
				$exit_other_array = array($key => $exit_other);
				#if input for function(argv[2]) is operational
				if($mode == 'operational'){
					foreach($exit_other_array as $key => $server_other){
					#value equal value[index 0];
					#return 4 key index 0-3 (4 lines)
					$server_other = $server_other[0];
						if($key == 0){
							#if state is YES or ok return state 1 (OK)
							if($server_other == 'OK' || $server_other == 'Yes'){	
								$msg_ready = 1;
							}else{
								$msg_ready = 0;
							}
						}if($key == 1){
							if($server_other == 'OK' || $server_other == 'Yes'){	
								$msg_oper = 1;
							}else{
								$msg_oper = 0;
							}
						}
					}
					#return msg in format value value on on with new line
					$msg = "$msg_ready\n$msg_oper\non\non\n"; 
				}elseif($mode == 'parameters'){
					#if input for function(argv[2]) is parameters
					foreach($exit_other_array as $key => $server_other){
					#value equal value[index 0];
					#return 4 key index 0-3 (4 lines)
					$server_other = $server_other[0];
						if($key == 2){
							if($server_other == 'OK' || $server_other == 'Yes'){	
								$msg_volt = 1;
							}else{
								$msg_volt = 0;
							}
						}if($key == 3){
							if($server_other == 'OK' || $server_other == 'Yes'){	
								$msg_temp = 1;			
							}else{
								$msg_temp = 0;
								
							}
						}
					}
					$msg = "$msg_volt\n$msg_temp\non\non\n"; 		
				}else{
					$msg = "Sorry bad mode";
				}
				#Here it works the same way
			}else{
				preg_match($reg_string_all, $handle, $exit_all);
				$exit_array = array($key => $exit_all);
				if($mode == 'operational'){
					foreach($exit_array as $key => $server_raid){
						$server_raid = $server_raid[0];
						if($key == 2){
							if($server_raid == 'Operational' || $server_raid == 'Optimal'){	
								$msg_oper = 1;
							}else{
								$msg_oper = 0;
							}
						}if($key == 3){
							if($server_raid != 0){
								$msg_charge = $server_raid;
							}else{
								$msg_charge = $server_raid;
							}
						}
					}
					$msg = "$msg_oper\n$msg_charge\non\non\n";
				}elseif($mode == 'parameters'){
					foreach($exit_array as $key => $server_raid){
						$server_raid = $server_raid[0];
						if($key == 0){
							if($server_raid != 0){
								#convert value for diagram MRTG 4000 on 4.00
								$msg_volt = $server_raid*0.001;
								$msg_volt = round($msg_volt,2);
							}else{
								$msg_volt = $server_raid*0.001;
								$msg_volt = round($msg_volt,2);
							}
						}if($key == 1){
							if($server_raid != 0){
								$msg_temp = $server_raid;
							}else{
								$msg_temp = $server_raid;
							}
						}
					}
					$msg = "$msg_volt\n$msg_temp\non\non\n";
				}else{
					$msg = "Sorry bad mode";
				}
			}
		}		
		echo $msg;
	}catch(Exception $e){
	   $msg = 'Connection ERROR'.$e;
	   print($msg);
       $status = 3;
	}
}
check_charge_state($argv[1], $argv[2]);
?>
