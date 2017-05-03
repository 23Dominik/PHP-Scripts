<?php 
date_default_timezone_set('Europe/Warsaw'); 
require_once 'PHPMailerAutoload.php';
## Connection to mysql server function
function connection(){
    $mysql_server = "server address";
    $mysql_admin = "my username";
    $mysql_pass = "my password";
    $mysql_db = "test";
    @mysql_connect($mysql_server, $mysql_admin, $mysql_pass)
    or die('No connection to server MySQL.');
    @mysql_select_db($mysql_db)
    or die('Database selection error.');
}
## Write line to .txt file performed in function: read_file_numbers || example with php.org
function fwrite_stream($fp, $array) {
    for ($writt = 0; $writt < count($array); $writt += $fwrite) {
        $fwrite = fwrite($fp, print_r($array, true));
        if ($fwrite === false) {
            return $writt;
        }
    }
    return $writt;
}## For Try-catch block -> error function
function errorHandler($errno, $errstr, $errfile, $errline) {
       throw new Exception($errstr, $errno);
}

set_error_handler('errorHandler');

function read_file_ondo($exec_string){
$exec_string = 'read';
#connection();
$dir = "/var/...xxxx/";
#regular expression read for number: example: phoneforward=%88156565 -> return 88156565
$str_pattern_value = '/phoneforward=([!@#$%&*])|([0-9]+)/';
#scan all users dir in $dir 
$dir_in_dir = scandir($dir);
#actuall time
$mysqltime = date ("Y-m-d H:i:s");
#empty string for messages
$msg = '';

try{
                foreach($dir_in_dir as $subdir){
					#scandir return all folders also . and .. in linux
                        if(strlen($subdir) >= 2 and strlen($subdir) <= 7 and $subdir <> '..'){
                                #if dir "list" continue function
								if($subdir == 'list'){continue;}
								#create an array with lines in properties file
                                $handle_to_read[] = file($dir.$subdir."/user.properties");
                                #create an array with "users" dir (this is short number 2-7 chars)
								$short_number[] = $subdir;
                        }
                }#read line after line with properties file
                foreach($handle_to_read as $handle_line){
					#detailed reading line
                                foreach($handle_line as $line){
									#matching numerical value
                                        preg_match($str_pattern_value, $line, $match_value);
										#if match
                                        if($match_value){
											#if value is not empty and is >= 9
                                                if(isset($match_value[2]) && strlen($match_value[2]) >= 9){
													#create an array for standard lenght number
                                                        $long_number[] = $match_value[2];
                                                }
                                        }
                                }
                }#read line with array $long_number[]
                foreach($long_number as $l_number){
					# select count for tables where number is in array line
                        $query_id = mysql_query("SELECT count(number) FROM numbers where number='$l_number'")
                                                or die("Zapytanie niepoprawne");
						#add return to array						
                        $data = mysql_fetch_assoc($query_id);
						#if count == 0
                        if($data['count(number)'] == 0){
							#insert into numbers and actuall date
                                $query_insert_for_empty ="INSERT INTO numbers(number, date)
                                                         VALUES('$l_number', '$mysqltime')";
                                if (!mysql_query($query_insert_for_empty)){
                                        die('Error: ' . mysql_error());
                                }
                        }
                }#read line with array $short_number[]
                foreach($short_number as $s_number){
                        $query_id = mysql_query("SELECT count(short_number) FROM numbers_for_billing_dir where short_number='$s_number'")
                                                or die("Zapytanie niepoprawne");
                        $data = mysql_fetch_assoc($query_id);
                        if($data['count(short_number)'] == 0){
                                $query_insert_for_empty ="INSERT INTO numbers_for_billing_dir(short_number, date)
                                                         VALUES('$s_number', '$mysqltime')";
                                if (!mysql_query($query_insert_for_empty)){
                                        die('Error: ' . mysql_error());
                                }
                                $msg =  'Add number';
                        }
                }
                if($msg == ''){
                        echo 'There is nothing here';
                }else{
                        echo $msg;
                }
        }catch(Exception $ex){
                echo 'Error: '.$ex.' Trace: '.$ex->getLine();
        }
}
#function for conversion short to longer numbers example: 0061 to 225410061
function read_file_numbers($exec_string, $file_to_read, $file_to_write){
	connection();
	$exec_string = 'convert';
	#$match_string = '/61([0-9]+)/'; For 61 prefix
	$mysqltime = date ("Y-m-d H:i:s");
	$handle_to_read = file(__DIR__."/".$file_to_read.".txt");
	$handle_to_write = fopen(__DIR__."/".$file_to_write.".txt", "w") or die("Unable to open file");	
	#dir for notification
	$dir = __DIR__."/".$file_to_write.".txt";
	#read line with $handle_to_read
	   foreach($handle_to_read as $handle_line){ 
	  
			$query_select = mysql_query("SELECT number FROM numbers where short_number=$handle_line") or die("Zapytanie niepoprawne numery");
			$selects = mysql_fetch_assoc($query_select);
			$select = $selects['number'];
			#if not find number in table -> write short number
			if($select == ''){
				$val = $handle_line;
			}#else find number in table -> write long number
			else{
				#preg_match($match_string, $select, $formated_line);
				$val = $select.PHP_EOL;
			}#call function for write stream $file string and $line value
			fwrite_stream($handle_to_write, $val);		
	   }#call to function for notification
	send_notification('send', $dir);	   
}
#send notification to admin
function send_notification($exec_string, $dir){
	$exec_string = 'send';
	$mail = new PHPMailer();
	$mail->isSMTP();
#	$mail->SMTPDebug  = 2; 
	$mail->Host = "smtp.server.pl";
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = 'tls'; 
	$mail->CharSet = "UTF-8";
	$mail->Encoding = "base64";
	$mail->Username = "my username";
	$mail->Password = "my password";
	$mail->setFrom('my email address', 'Convert Billing');
	$mail->AddAddress('example emaila ddress');
	$mail->WordWrap = 50;
	$mail->IsHTML(true);
	$mail->Subject = "Billing Notification: ".$dir;
	$mail->Body = "Billing Notification: ".$dir;
	$mail->AddAttachment($dir);
	$mail->Send();
}
foreach ($argv AS $arg){
    function_exists($arg) AND call_user_func($arg);
}
foreach ($argv as $i=>$arg ) {
	try{
		#Conditions for input variables example in linux: ||| php script.php convert numbers_file numbers_file_convert |||
		if($argv[$i + 1] == 'read'){
			read_file_ondo($argv[$i + 1]);
			echo 'completed!';
			exit();	
		}
		if($argv[$i + 1] == 'convert'){
			read_file_numbers($argv[$i + 1], $argv[$i + 2], $argv[$i + 3]);
			echo 'completed!';
			exit();	
		}
		if($argv[$i + 1] == 'send'){
			send_notification($argv[$i + 1], $argv[$i + 2]);
			echo 'completed!';
			exit();	
		}	
	}catch(Exception $e){
		echo 'Error Info: '.$e.PHP_EOL;
	}
}
?>