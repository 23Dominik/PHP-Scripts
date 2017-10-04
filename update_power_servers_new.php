<?php
date_default_timezone_set('Europe/Warsaw');
$mysqltime = date("Y-m-d H:i:s");
require_once("connect.php");
/*** connect.php
$mysql_server = "xxx.xxx.xxx.xxx";
$mysql_admin = "user";
$mysql_pass = "password";
$mysql_db = "database";
$connect = mysql_connect($mysql_server, $mysql_admin, $mysql_pass);
if(!$connect){
        echo 'Nie udalo polaczyc sie z serwerem MySQL';
}
mysql_select_db($mysql_db) or die('Błąd wyboru bazy danych.');
	mysql_query("SET CHARACTER SET utf8");
	mysql_query('SET collation_connection = utf8_polish_ci');
function connection(){
	global $connect;
	mysql_close($connect);
	echo"rozlaczona";
}
***/
function insert($old_value, $value, $index,  $host){
        global $mysqltime;
        $insert_notification = "Zmiana pola: ".$index." z ".$old_value." na ".$value." dla hosta ".$host."";
        $query_insert = "insert into power_servers_hist(notification, date) values('$insert_notification', '$mysqltime')";
        if (!mysql_query($query_insert)){
                die('Error: ' . mysql_error());
        }
}
function xml_reader_power(){
$file = "power.xml";
global $mysqltime;
$dir = "/var/www/html/status/";
$xml = simplexml_load_file($dir.$file) or die("Error: Cannot create object");
        for($i=1;$i<=50;$i++){
                if (isset($xml->host[$i])){
                        $name = trim($xml->host[$i]->name);
                        $amper1 = trim($xml->host[$i]->amper1);
                        $vamper1 = trim($xml->host[$i]->vamper1);
                        $amper2 = trim($xml->host[$i]->amper2);
                        $vamper2 = trim($xml->host[$i]->vamper2);
                        $zasilacz1 = trim($xml->host[$i]->zasilacz1);
                        $zasilacz2 = trim($xml->host[$i]->zasilacz2);
                        $raid = trim($xml->host[$i]->raid);

                        $query_select = "select amp_1, amp_2, volt_amp1, volt_amp2, power_supply1, power_supply2, raid_type from power_servers where server_name='$name'";
                        $select = mysql_fetch_assoc(mysql_query($query_select));
                        if($amper1 != $select['amp_1']){$index = 'amp_1';insert($select['amp_1'],$amper1,$index, $name);}
                        if($amper2 != $select['amp_2']){$index = 'amp_2';insert($select['amp_2'],$amper2,$index, $name);}
                        if($vamper1 != $select['volt_amp1']){$index = 'volt_amp1';insert($select['volt_amp1'],$vamper1,$index, $name);}
                        if($vamper2 != $select['volt_amp2']){$index = 'volt_amp2';insert($select['volt_amp1'],$vamper2,$index, $name);}
                        if($zasilacz1 != $select['power_supply1']){$index = 'power_supply1';insert($select['power_supply1'],$zasilacz1,$index, $name);}
                        if($zasilacz2 != $select['power_supply2']){$index = 'power_supply2';insert($select['power_supply2'],$zasilacz2,$index, $name);}
                        if($raid != $select['raid_type']){$index = 'raid_type';insert($select['raid_type'],$raid,$index, $name);}

                        $query_update = "update power_servers set amp_1='$amper1', amp_2='$amper2', volt_amp1='$vamper1', volt_amp2='$vamper2',
                        power_supply1='$zasilacz1', power_supply2='$zasilacz2', raid_type='$raid', date='$mysqltime' where server_name='$name'";
                        if (!mysql_query($query_update)){
                            die('Error: ' . mysql_error());

                        }
                }else{continue;}

        }
echo 'Zrobione!';
}
function xml_reader_interfaces(){
global $mysqltime;
$file = "interfaces.xml";
$dir = "/var/www/html/status/";
$mysqltime = date("Y-m-d H:i:s");
$xml = simplexml_load_file($dir.$file) or die("Error: Cannot create object");
        for($i=1;$i<=50;$i++){
                if (isset($xml->host[$i])){
                        $name = trim($xml->host[$i]->name);
                        $eth = trim($xml->host[$i]->eth);
                        $eth1 = trim($xml->host[$i]->eth1);
                        $eth2 = trim($xml->host[$i]->eth2);
                        $eth3 = trim($xml->host[$i]->eth3);
                        $eth4 = trim($xml->host[$i]->eth4);
                        $eth5 = trim($xml->host[$i]->eth5);
                        $eth6 = trim($xml->host[$i]->eth6);
                        $eth7 = trim($xml->host[$i]->eth7);
                        $dev_eth = trim($xml->host[$i]->dev_eth);
                        $dev_eth1 = trim($xml->host[$i]->dev_eth1);
                        $dev_eth2 = trim($xml->host[$i]->dev_eth2);
                        $dev_eth3 = trim($xml->host[$i]->dev_eth3);
                        $dev_eth4 = trim($xml->host[$i]->dev_eth4);
                        $dev_eth5 = trim($xml->host[$i]->dev_eth5);
                        $dev_eth6 = trim($xml->host[$i]->dev_eth6);
                        $dev_eth7 = trim($xml->host[$i]->dev_eth7);
                        $port_eth = trim($xml->host[$i]->port_eth);
                        $port_eth1 = trim($xml->host[$i]->port_eth1);
                        $port_eth2 = trim($xml->host[$i]->port_eth2);
                        $port_eth3 = trim($xml->host[$i]->port_eth3);
                        $port_eth4 = trim($xml->host[$i]->port_eth4);
                        $port_eth5 = trim($xml->host[$i]->port_eth5);
                        $port_eth6 = trim($xml->host[$i]->port_eth6);
                        $port_eth7 = trim($xml->host[$i]->port_eth7);

                        $query_select = "select eth, eth1, eth2, eth3, eth4, eth5, eth6, eth7, dev_eth, dev_eth1, dev_eth2, dev_eth3, dev_eth4, dev_eth5, dev_eth6, dev_eth7, port_eth, port_eth1, port_eth2, port_eth3, port_eth4, port_eth5, port_eth6, port_eth7  from power_servers where server_name='$name'";
                        $select = mysql_fetch_assoc(mysql_query($query_select));

                        if($eth != $select['eth']){$index = 'eth';insert($select['eth'],$eth,$index, $name);}
                        if($eth1 != $select['eth1']){$index = 'eth1';insert($select['eth1'],$eth1,$index, $name);}
                        if($eth2 != $select['eth2']){$index = 'eth2';insert($select['eth2'],$eth2,$index, $name);}
                        if($eth3 != $select['eth3']){$index = 'eth3';insert($select['eth3'],$eth3,$index, $name);}
                        if($eth4 != $select['eth4']){$index = 'eth4';insert($select['eth4'],$eth4,$index, $name);}
                        if($eth5 != $select['eth5']){$index = 'eth5';insert($select['eth5'],$eth5,$index, $name);}
                        if($eth6 != $select['eth6']){$index = 'eth6';insert($select['eth6'],$eth6,$index, $name);}
                        if($eth7 != $select['eth7']){$index = 'eth7';insert($select['eth7'],$eth7,$index, $name);}
                        if($dev_eth != $select['dev_eth']){$index = 'dev_eth';insert($select['dev_eth'],$dev_eth,$index, $name);}
                        if($dev_eth1 != $select['dev_eth1']){$index = 'dev_eth1';insert($select['dev_eth1'],$dev_eth1,$index, $name);}
                        if($dev_eth2 != $select['dev_eth2']){$index = 'dev_eth2';insert($select['dev_eth2'],$dev_eth2,$index, $name);}
                        if($dev_eth3 != $select['dev_eth3']){$index = 'dev_eth3';insert($select['dev_eth3'],$dev_eth3,$index, $name);}
                        if($dev_eth4 != $select['dev_eth4']){$index = 'dev_eth4';insert($select['dev_eth4'],$dev_eth4,$index, $name);}
                        if($dev_eth5 != $select['dev_eth5']){$index = 'dev_eth5';insert($select['dev_eth5'],$dev_eth5,$index, $name);}
                        if($dev_eth6 != $select['dev_eth6']){$index = 'dev_eth6';insert($select['dev_eth6'],$dev_eth6,$index, $name);}
                        if($dev_eth7 != $select['dev_eth7']){$index = 'dev_eth7';insert($select['dev_eth7'],$dev_eth7,$index, $name);}
                        if($port_eth != $select['port_eth']){$index = 'port_eth';insert($select['port_eth'],$port_eth,$index, $name);}
                        if($port_eth1 != $select['port_eth1']){$index = 'port_eth1';insert($select['port_eth1'],$port_eth1,$index, $name);}
                        if($port_eth2 != $select['port_eth2']){$index = 'port_eth2';insert($select['port_eth2'],$port_eth2,$index, $name);}
                        if($port_eth3 != $select['port_eth3']){$index = 'port_eth3';insert($select['port_eth3'],$port_eth3,$index, $name);}
                        if($port_eth4 != $select['port_eth4']){$index = 'port_eth4';insert($select['port_eth4'],$port_eth4,$index, $name);}
                        if($port_eth5 != $select['port_eth5']){$index = 'port_eth5';insert($select['port_eth5'],$port_eth5,$index, $name);}
                        if($port_eth6 != $select['port_eth6']){$index = 'port_eth6';insert($select['port_eth6'],$port_eth6,$index, $name);}
                        if($port_eth7 != $select['port_eth7']){$index = 'port_eth7';insert($select['port_eth7'],$port_eth7,$index, $name);}

                        $query_update = "update power_servers set eth='$eth', eth1='$eth1', eth2='$eth2', eth3='$eth3', eth4='$eth4', eth5='$eth5', eth6='$eth6',
                                        eth7='$eth7', dev_eth='$dev_eth', dev_eth1='$dev_eth1', dev_eth2='$dev_eth2', dev_eth3='$dev_eth3', dev_eth4='$dev_eth4', dev_eth5='$dev_eth5', dev_eth6='$dev_eth6',
                                        dev_eth7='$dev_eth7', port_eth='$port_eth', port_eth1='$port_eth1', port_eth2='$port_eth2', port_eth3='$port_eth3', port_eth4='$port_eth4', port_eth5='$port_eth5',
                                        port_eth6='$port_eth6', port_eth7='$port_eth7', date='$mysqltime' where server_name='$name'";
                        if (!mysql_query($query_update)){die('Error: ' . mysql_error());
                        }
                }else{continue;}
        }
echo 'Zrobione!';
}
xml_reader_interfaces();
xml_reader_power();
connection();
?>
