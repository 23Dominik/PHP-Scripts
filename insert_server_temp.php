#!/usr/bin/php
<?php
#this script read state of temperature in server and save data output in database
date_default_timezone_set('Europe/Berlin'); 
set_time_limit(300); 
function errorHandler($errno, $errstr, $errfile, $errline) 
{
       throw new Exception($errstr, $errno);
}
set_error_handler('errorHandler'); 
function connection(){
    $mysql_server = "servername";
    $mysql_admin = "username";
    $mysql_pass = "password";
    $mysql_db = "database";
    @mysql_connect($mysql_server, $mysql_admin, $mysql_pass)
    or die('Brak połączenia z serwerem MySQL.');
    @mysql_select_db($mysql_db)
    or die('Błąd wyboru bazy danych.');
}
$msg =""; 
$reg_string = "/[^0-9\-]*([-]?[0-9]*\.?[0-9]*)[^0-9]*\D([0-9]*)/"; 
$mysqltime = date ("Y-m-d H:i:s");
				 
function check_temp_server($host){
	connection();
	global $msg;
	global $reg_string;
	global $mysqltime;
	#for oldest dell servers
	if($host == "10.10.10.11" || $host == "10.10.10.21"){
		try{
			#ssh command for login on user ipmitool with RSA key and grep return output
			#after login user exec local command ipmitool sensor for unix and linux
			$fmt="/usr/bin/ssh -i /ipmitool.key ipmitool@$host sdr | awk '/VRD 1/ && /ok/'";
			#convert command
			$cmd=sprintf($fmt);
			#execution command by php
			$exec = exec($cmd);
			#match numerical value for temperature
			preg_match($reg_string, $exec, $exit);
			#temperature equal exit[index 2];
			$temperature = $exit[2];
			#query for select ip address server
			$query_select = mysql_query("SELECT ip_server FROM svr_temperature where ip_server='$host'") or die("Zapytanie niepoprawne");
			#execution query
			$select = mysql_fetch_assoc($query_select);
			#if ip address is not find in data base insert new row
			if($select == false){
				$query_insert = "INSERT INTO svr_temperature(ip_server, temp_value, date) VALUES('$host', '$temperature', '$mysqltime')";
				if (!mysql_query($query_insert)){
					die('Error: ' . mysql_error());
				}
			#else update temperature value where ip address	
			}else{
				$query_update = "UPDATE svr_temperature SET temp_value='$temperature', date='$mysqltime' WHERE ip_server='$host' ";
				if (!mysql_query($query_update)){
					die('Error: ' . mysql_error());
				}
			}			
		}catch(Exception $e){
			$msg = 'Connection ERROR'.$e;
		}
	}else{
		#Here it works the same way
		try{
		   $fmt="/usr/bin/ssh -i /ipmitool.key ipmitool@$host sdr | awk '/Temp/ && /ok/'";
		   $cmd=sprintf($fmt);
		   $exec = exec($cmd);
		   preg_match($reg_string, $exec, $exit);
		   $temperature = $exit[1]*1;
		   
			$query_select = mysql_query("SELECT ip_server FROM svr_temperature where ip_server='$host'") or die("Zapytanie niepoprawne");
			$select = mysql_fetch_assoc($query_select);
			
			if($select == false){
				$query_insert = "INSERT INTO svr_temperature(ip_server, temp_value, date) VALUES('$host', '$temperature', '$mysqltime')";
				if (!mysql_query($query_insert)){
					die('Error: ' . mysql_error());
				}
			}else{
				$query_update = "UPDATE svr_temperature SET temp_value='$temperature', date='$mysqltime' WHERE ip_server='$host' ";
				if (!mysql_query($query_update)){
					die('Error: ' . mysql_error());
				}
			}		   
		}catch(Exception $e){
			$msg = 'Connection ERROR'.$e;
		}
	}
	echo $msg;
}
foreach ($argv AS $arg){
    function_exists($arg) AND call_user_func($arg);
}
foreach ($argv as $i=>$arg ) {
	try{
		check_temp_server($argv[1]);
		exit($status);
	}catch(Exception $e){
		echo $e->getLine().$e;
	}
}
?>
