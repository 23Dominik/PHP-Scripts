#!/usr/bin/php
<?php
# Call : php check_raid_battery.php host(ip_numerical)
# This nagios script check state of battery array, input data download with output read_raid_battery script, 
# checks all values and return state & messages.

date_default_timezone_set('Europe/Berlin');
set_time_limit(300);
function errorHandler($errno, $errstr, $errfile, $errline)
{
	throw new Exception($errstr, $errno);
}
#Exception for try->catch
set_error_handler('errorHandler');
#empty string and string for preg_match
$msg = '';
$reg_string_all = "/(?<=)Operational|Optimal|[0-9]+/";
$reg_string_other = "/(?<=)OK|Yes/";
#act datetime
$mysqltime = date ("Y-m-d H:i:s");
#state for nagios 0 ok,  1 warrning, 2 critical, 3 unkown
$status = 0;

function check_charge_state($host){

global $reg_string_all;
global $reg_string_other;
global $mysqltime;
global $msg;
global $status;

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
				#match value
				preg_match($reg_string_other, $handle, $exit_other);
				#create new array with output preg_match and seperation on $key=>$value
				$exit_other_array = array($key => $exit_other);
				#read array
				foreach($exit_other_array as $key => $server_other){
					#value equal value[index 0];
					#return 4 key index 0-3 (4 lines)
					$server_other = $server_other[0];
					if($key == 0){
						#if state is not YES return critical state
						if(!$server_other == 'Yes'){
								$status = 2;
								if($msg == ""){
										$msg = 'Attention. Battery have the bad of state Ready: '.$server_other;
								}else{
										$msg = 'Attention. Battery have the bad of state Ready: '.$server_other;;
								}
						#else ok and return 0 state		
						}else{
								$msg = 'State of battery. Ready OK: '.$server_other;
								$status = 0;
						}
					}if($key == 1){
						if(!$server_other == 'OK'){
								$status = 2;
								if($msg == ""){
										$msg = 'Attention. Battery have the bad of state Status: '.$server_other;
								}else{
										$msg = 'Attention. Battery have the bad of state Status: '.$server_other;;
								}
						}else{
								$msg = 'State of battery. OK Status: '.$server_other;
								$status = 0;
						}
					}if($key == 2){
						if(!$server_other == 'OK'){
								$status = 2;
								if($msg == ""){
										$msg = 'Attention. Battery have the bad of state Voltage: '.$server_other;
								}else{
										$msg = 'Attention. Battery have the bad of state Voltage: '.$server_other;;
								}
						}else{
								$msg = 'State of battery. OK Voltage: '.$server_other;
								$status = 0;
						}
					}if($key == 3){
						if(!$server_other == 'OK'){
								$status = 2;
								if($msg == ""){
										$msg = 'Attention. Battery have the bad of state Temperature: '.$server_other;
								}else{
										$msg = 'Attention. Battery have the bad of state Temperature: '.$server_other;;
								}
						}else{
								$msg = 'State of battery. OK Temperature: '.$server_other;
								$status = 0;
						}
					}
					#echo msg for all key
				}echo $msg;
			}else{
				#Here it works the same way
				preg_match($reg_string_all, $handle, $exit_all);
				$exit_array = array($key => $exit_all);
				foreach($exit_array as $key => $server_state){
					$server_state = $server_state[0];
					if($key == 0){
						if($server_state <= 1000){
								$status = 2;
								if($msg == ""){
										$msg =  'Critical Voltage is too low! '.$server_state.'mV';
								}else{
										$msg =  'Critical Voltage is too low! '.$server_state.'mV';
								}
						}else if($server_state > 1000 and $server_state <= 2000){
								if($status < 2){
										$status = 1;
								}if($msg == ""){
										$msg = 'Warrning Voltage is too low! '.$server_state.'mV';
								}else{
										$msg = 'Warrning Voltage is too low! '.$server_state.'mV';
								}
						}else{
								$msg = 'Voltage OK! '.$server_state.'mV';
								$status = 0;
						}
					}if($key == 1){
						if($server_state >= 120){
								$status = 1;
								if($msg == ""){
										$msg = 'Read Error Temperature! '.$server_state.'°C';
								}else{
										$msg = 'Read Error Temperature! '.$server_state.'°C';
								}
						}else if($server_state <= 10){
								$status = 2;
								if($msg == ""){
										$msg = 'Critical Temperature is too low! '.$server_state.'°C';
								}else{
										$msg = 'Critical Temperature is too low! '.$server_state.'°C';
								}
						}else if($server_state >= 40 and $server_state < 120){
								$status = 2;
								if($msg == ""){
										$msg = 'Critical Temperature is too high! '.$server_state.'°C';
								}else{
										$msg = 'Critical Temperature is too high! '.$server_state.'°C';
								}
						}else if($server_state >= 35 and $server_state < 40){
								if($status < 2){
										$status = 1;
								}if($msg == ""){
										$msg = 'Warrning Temperature is too high! '.$server_state.'°C';
								}else{
										$msg = 'Warrning Temperature is too high! '.$server_state.'°C';
								}
						}else if($server_state > 10 and $server_state <= 15){
								if($status < 2){
										$status = 1;
								}if($msg == ""){
										$msg = 'Warrning Temperature is too low! '.$server_state.'°C';
								}else{
										$msg = 'Warrning Temperature is too low! '.$server_state.'°C';
								}
						}else{
								$msg = 'Temperature OK! '.$server_state.'°C';
								$status = 0;
						}
					}if($key == 2){
						if(!$server_state == 'Operational' || !$server_state == 'Optimal'){
								$status = 2;
						if($msg == ""){
								$msg = 'Attention. Battery have the bad of state: '.$server_state;
						}else{
								$msg = 'Attention. Battery have the bad of state: '.$server_state;;
								}
						}else{
								$msg = 'State of battery. OK! '.$server_state;
								$status = 0;
						}
					}
					if($key == 3){
						if($server_state <= 40){
								$status = 2;
						if($msg == ""){
								$msg = 'Critical Charger level is too low! '.$server_state.'%';
								}else{
										$msg = 'Critical Charger level is too low! '.$server_state.'%';
								}
						}else if($server_state > 40 and $server_state <= 70){
								if($status < 2){
										$status = 1;
								}if($msg == ""){
										$msg = 'Warrning Charger level is too low! '.$server_state.'%';
								}else{
										$msg = 'Warrning Charger level is too low! '.$server_state.'%';
								}
						}else{
								$msg = 'Charger level OK! '.$server_state.'%';
								$status = 0;
						}
					}
				}echo $msg;
			}
		}
	}catch(Exception $e){
		$msg = 'Connection ERROR'.$e;
		print($msg);
		$status = 3;
	}
server_state($status);
}
check_charge_state($argv[1]);