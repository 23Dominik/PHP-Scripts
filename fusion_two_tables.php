<?php
# Call : php fusion_two_table.php fusion
# This script combines two tables to one.
# In tables numbers_dir are located short number value Eg. 0022, 01, 1234
# In tables numbers are located long number value Eg. 612220022
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
function fusion_table($exec_string){
	connection();
	$exec_string = 'fusion';
	$mysqltime = date ("Y-m-d H:i:s");
	
	#select query for table: numbers_dir 
	$query_select_number_short = mysql_query("SELECT numer_skrocony FROM numbers_dir") or die("Zapytanie niepoprawne numery skrocone"); 

	#execute query and save output in array[]
	while($select_number_short = mysql_fetch_assoc($query_select_number_short)){
		$array_number_short[] = $select_number_short['numer_skrocony'];
	}
	#read array for short number
	foreach($array_number_short as $key => $number){
		#select query for table: numbers 
		$query_select = mysql_query("SELECT number FROM numbers where number like'%$number%'") or die("Zapytanie niepoprawne numery");
		#execute query
		$select = mysql_fetch_assoc($query_select);
		#save output in array[] = array($key => $value); where $key is a short number and value is a long number
		$array_number[] = array($number, $select['number']);
	}
	#read array_number[]
	foreach($array_number as $key => $num){
		#if value is no empty
		if($num[1] != ''){
			#update table numbers set short number, date(now) where long number 
			$query_insert = "Update numbers set short_numbers='$num[0]', date='$mysqltime' WHERE number='$num[1]'";
				if (!mysql_query($query_insert)){
					die('Error: ' . mysql_error());
			}
		}else{
			echo 'There is nothing here';
		}
	}
	echo 'OK';
}
fusion_table($argv[1]);
?>