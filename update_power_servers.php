#!/usr/bin/php
<?php
# Call : php update_power_servers.php
# This script read data with xml file and save data in data base
# Input data is -> (for dell servers) server name, amperage and VoltAmperage for power supply I and power supply II.
# The last values pwsupply1 and pwsupply2. They are the information to which ups are connected
date_default_timezone_set('Europe/Warsaw');
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
function xml_reader(){
connection();	
$file = "power.xml";
$dir = __DIR__;
#act datetime
$mysqltime = date("Y-m-d H:i:s");
$xml = simplexml_load_file($dir.$file) or die("Error: Cannot create object");
    #servers count is static
	for($i=1;$i<=20;$i++){
		if (isset($xml->host[$i])){
			#$variable = remove all white char(read index of host)
			$name = trim($xml->host[$i]->name);
			$amper1 = trim($xml->host[$i]->amper1);
			$vamper1 = trim($xml->host[$i]->vamper1);
			$amper2 = trim($xml->host[$i]->amper2);
			$vamper2 = trim($xml->host[$i]->vamper2);
			$pwsupply1 = trim($xml->host[$i]->pwsupply1);
			$pwsupply2 = trim($xml->host[$i]->pwsupply2);
			$query_update = "update power set amp_1='$amper1', 
							 amp_2='$amper2', volt_amp1='$vamper1', volt_amp2='$vamper2', 
							 power_supply1='$pwsupply1', power_supply2='$pwsupply2',
							 date='$mysqltime' where server_name='$name'";
			if (!mysql_query($query_update)){die('Error: ' . mysql_error());
		}
		else{continue;}
		}
	}
echo 'OK!';
}
xml_reader();
?>
