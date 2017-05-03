#!/usr/bin/php
<?php
#© Dominik Koliński 2017
### This is one of my first scripts ###
### This multi diagnostic code downloading value from list device in MYSQL base -> ICOTERA IGW3000 status it Telephone Line. ###
### This Device have a problem because every now line pass for state "Request Sent" and VOIP connections not working###
### Code downloading value from device and checks problem, reboot device, mail notifications, add log in table. etc.###
### Based on three tables private don't for user table([DEVICE],###
### [DEVICE_HISTORY]), public for user table([DEVICE_LOG], [DEVICE_SPECIAL_FOR_SHOW])###

//Connecton BLOCK
date_default_timezone_set('Europe/Warsaw');
require('connect.php');
#connect with DB
header('Content-Type: text/html; charset=utf-8'); #encoding
require('PHPMailerAutoload.php'); ### PHP MAILER CLASS FOR notofications 
connection(); ### Standard Method Connections for MYSQL 
//Error BLOCK
function errorHandler($errno, $errstr, $errfile, $errline) {
        throw new Exception($errstr, $errno);
}
//Execute BLOCK
function executesnmp (){
  
  $polecenie = mysql_query("SELECT [YOUR_IP_COLUMN] FROM [YOUR_DEVICE_TABLE] where [YOUR_MAC_COLUMN] like'000f15%'") 
      or die("Zapytanie niepoprawne");
 
 $mysqltime = date ("Y-m-d H:i:s"); ## static value for time and date
  
  while($u = mysql_fetch_assoc($polecenie)){
      foreach ($u as $a) { //$u read data in loop foreach from array MYSQL($polecenie), $a convert array on string 
        
		if (!is_null($a)){
			
			set_error_handler('errorHandler');
			
			//STRING BLOCK
			$polecenie_historia = mysql_query("SELECT [DEVICE_ID] FROM [YOUR_DEVICE_TABLE] where [YOUR_IP_COLUMN] like'$a' limit 1")or die("Zapytanie niepoprawne");
			$reboot =  mysql_query("SELECT [REBOOT_COLUMN] FROM [DEVICE_SPECIAL_FOR_SHOW] WHERE [YOUR_IP_COLUMN] LIKE'$a' limit 1") or die("Zapytanie niepoprawne");
			
			$rb = mysql_fetch_assoc($reboot); 
			$srb = implode($rb);//convert array to string
			
			$errorString_1 = '<b>'.$mysqltime.'</b> Uwaga 1 linia telefoniczna: <b>'.$a.'</b> posiada status : Wyrejestrowana lub zawieszona. Liczba wczesniejszych restartow automatycznych: '.$srb.'';
			$errorString_2 = '<b>'.$mysqltime.'</b> Uwaga 2 linia telefoniczna: <b>'.$a.'</b> posiada status : Wyrejestrowana lub zawieszona. Liczba wczesniejszych restartow automatycznych: '.$srb.'';
			$errorString_3 = '<b>'.$mysqltime.'</b> Uwaga jedna z lini telefonicznych: <b>'.$a.'</b> posiada status : Wyrejestrowana lub zawieszona. Liczba wczesniejszych restartow automatycznych: '.$srb.'';
			$okString = ''.$mysqltime.'<b> OK! </b>'.$a.'';		
			//Try->Catch Exeption BLOCK
			
              try{
				  
              $line1 = snmp2_walk($a, "connection_string", "[ICOTERA-MIB-OID]", 10000); //SNMP for WALK MIB::ICOTERA-IGW return state registered for line 1
              $line2 = snmp2_walk($a, "connection_string", "[ICOTERA-MIB-OID]", 10000); // -||- for line 2
			  
					foreach($line1 as $l1){
									$sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET line_1_stat = '$l1' WHERE [YOUR_IP_COLUMN] = '$a'";
									if (!mysql_query($sql)){die('Error: ' . mysql_error());}
					}
					
					foreach($line2 as $l2){
								   $sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET  line_2_stat = '$l2' WHERE [YOUR_IP_COLUMN] = '$a'";
									if (!mysql_query($sql)){die('Error: ' . mysql_error());}
					}//Exeption for line 1
					
                   if(strpos($l1, "Unregistered") OR strpos($l1, "Request")){	
                     $sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET alarm = '$errorString_1' WHERE [YOUR_IP_COLUMN] = '$a'";
					 
                     if (!mysql_query($sql)){die('Error: ' . mysql_error());}
                     $sql2="INSERT INTO [DEVICE_LOG] (bug_log) VALUES('$errorString_1')";
					 
                     if (!mysql_query($sql2)){die('Error: ' . mysql_error());}
						
						foreach(mysql_fetch_assoc($polecenie_historia) as $id){
							$sql3="INSERT INTO [YOUR_DEVICE_HISTORY_TABLE] ...";
							if (!mysql_query($sql3)){die('Error: ' . mysql_error());}
						
						}
						snmp2_set($a, "connection_string", "[ICOTERA-MIB-OID]", "i", "1", 10000);
							
							if($srb == 0 || $srb != 0){
								$sql4= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET i_reboot = '$srb' + 1 WHERE [YOUR_IP_COLUMN] = '$a'";
								if (!mysql_query($sql4)){die('Error: ' . mysql_error());}
							
							}// Send Mail if string in line1 is Unregistered or Request Sent 
						$mail = new PHPMailer;
						$mail->isSMTP();
						$mail->Host = "[YOUR_SNMP_HOST]";
						$mail->Port = 587;
						$mail->SMTPAuth = true;
						$mail->SMTPSecure = 'tls';
						$mail->CharSet = "UTF-8";
						$mail->Encoding = "base64";
						$mail->Username = "[YOUR_USERNAME]";
						$mail->Password = "[YOUR_PASS]";
						$mail->setFrom('[YOUR_EMAIL]', '[DESCRIPTION_FROM]');
						$mail->addAddress('[YOUR_EMAIL]', '[DESCRIPTION_FROM]');
						$today = date("Y-m-d");
						$time = date( "H:i");
						$mail->IsHTML(true);
						$mail->Subject = ''.$today.' '.$time.' Problem z telefonem ICOTERA '.$a.'';
						$mail->Body = '<tbody><tr><td align="left"><table width="600" cellspacing="0" cellpadding="0" align="left" style="margin:0px auto;color:#474747;font-size:16px;font-family:Tahoma, Arial, Helvetica, sans-serif;box-shadow: 10px 10px 8px grey;">
						<tbody><tr align="left"><td width="180" height="20" colspan="2" style="background-color:#ffffff; padding:0px;font-size:0px;border-radius: 20px 10px 0px 0px;"></td></tr>
						<tr align="left"><td width="600" align="justify" colspan="2" style="padding-left:30px;padding-right:30px;font-size:16px;background-color:#ffffff;border-radius: 0px 0px 10px 10px;">
						<font size="5" face="Arial"><b>Problem ICOTERA</font></b><br/> <br/>----------------------------------------------------------------------- <br/><br/>
						<font size="3" face="Arial"><b>Adres MGMT ICOTERY : </b>'.$a.'</font><br/><br/><font size="3" face="Arial"><b>Błąd: </b>'.$errorString_3.'</font><br/><br/>
						----------------------------------------------------------------------- <br/> <br/><p>&nbsp;</p></td></tr></tbody></td></table></tr>';
						$mail->AltBody = 'Wiadomość domyślna jeżeli ją widzisz powiadom nas o tym pod adresem';
						if (!$mail->send()){echo "Mailer Error: " . $mail->ErrorInfo;}	
						
				   }//Exeption for line 2
                   elseif(strpos($l2, "Unregistered") OR strpos($l2, "Request")) {
                     $sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET alarm = '$errorString_2' WHERE [YOUR_IP_COLUMN] = '$a'";
                     
					 if (!mysql_query($sql)){die('Error: ' . mysql_error());}
                     $sql2="INSERT INTO [DEVICE_LOG] (bug_log)
                      VALUES('$errorString_2')";
                     
					 if (!mysql_query($sql2)){die('Error: ' . mysql_error());}
						
						foreach(mysql_fetch_assoc($polecenie_historia) as $id){
							$sql3="INSERT INTO [YOUR_DEVICE_HISTORY_TABLE] ..."; 
							if (!mysql_query($sql3)){die('Error: ' . mysql_error());}
						}
						
						snmp2_set($a, "connection_string", "[ICOTERA-MIB-OID]", "i", "1", 10000);
							if($srb == 0 || $srb != 0){
								$sql4= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET i_reboot = '$srb' + 1 WHERE [YOUR_IP_COLUMN] = '$a'";
								if (!mysql_query($sql4)){die('Error: ' . mysql_error());}
							}	// Send Mail if string in line2 is Unregistered or Request Sent 
							
						$mail = new PHPMailer;$mail->isSMTP();						
						$mail->Host = "[YOUR_SNMP_HOST]";
						$mail->Port = 587;
						$mail->SMTPAuth = true;
						$mail->SMTPSecure = 'tls';
						$mail->CharSet = "UTF-8";
						$mail->Encoding = "base64";
						$mail->Username = "[YOUR_USERNAME]";
						$mail->Password = "[YOUR_PASS]";
						$mail->setFrom('[YOUR_EMAIL]', '[DESCRIPTION_FROM]');
						$mail->addAddress('[YOUR_EMAIL]', '[DESCRIPTION_FROM]');
						$today = date("Y-m-d");$time = date( "H:i");$mail->IsHTML(true);$mail->Subject = ''.$today.' '.$time.' Problem z telefonem ICOTERA '.$a.'';
						$mail->Body = '<tbody><tr><td align="left"><table width="600" cellspacing="0" cellpadding="0" align="left" style="margin:0px auto;color:#474747;font-size:16px;font-family:Tahoma, Arial, Helvetica, sans-serif;box-shadow: 10px 10px 8px grey;">
						<tbody><tr align="left"><td width="180" height="20" colspan="2" style="background-color:#ffffff; padding:0px;font-size:0px;border-radius: 20px 10px 0px 0px;"></td></tr>
						<tr align="left"><td width="600" align="justify" colspan="2" style="padding-left:30px;padding-right:30px;font-size:16px;background-color:#ffffff;border-radius: 0px 0px 10px 10px;">
						<font size="5" face="Arial"><b>Problem ICOTERA</font></b><br/> <br/>----------------------------------------------------------------------- <br/><br/>
						<font size="3" face="Arial"><b>Adres MGMT ICOTERY : </b>'.$a.'</font><br/><br/><font size="3" face="Arial"><b>Błąd: </b>'.$errorString_3.'</font><br/><br/>
						----------------------------------------------------------------------- <br/> <br/><p>&nbsp;</p></td></tr></tbody></td></table></tr>';
						$mail->AltBody = 'Wiadomość domyślna jeżeli ją widzisz powiadom nas o tym pod adresem';
						if (!$mail->send()){echo "Mailer Error: " . $mail->ErrorInfo;}		
                   }
				   
                   else {
					   
                      $sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET alarm = 'Linia telefoniczna 1 i 2 OK!' WHERE [YOUR_IP_COLUMN] = '$a'";
                      
					  if (!mysql_query($sql)){die('Error: ' . mysql_error());}
					  
					  foreach(mysql_fetch_assoc($polecenie_historia) as $id){
							$sql3="INSERT INTO [YOUR_DEVICE_HISTORY_TABLE] ..."; 
							if (!mysql_query($sql3)){die('Error: ' . mysql_error());}
					  }
                  }
                } //IF Exeption return statement and update/insert in MYSQL Data Base
				
                catch(Exception $e){
                  echo "<table><tr>";
                  echo "<td>Uwaga - > Host: $a jest nieosiągalny <br/></td>";
                  echo "</tr></table>";
                  $sql= "UPDATE [DEVICE_SPECIAL_FOR_SHOW] SET line_1_stat = '', line_2_stat = '', alarm = 'Uwaga brama: $a nie odpowiada' WHERE [YOUR_IP_COLUMN] = '$a'";
                  
				  if (!mysql_query($sql)){die('Error: ' . mysql_error());}
                  $sql2="INSERT INTO [DEVICE_LOG] (bug_log) VALUES('Uwaga brama: $a nie odpowiada')";
                 
				  if (!mysql_query($sql2)){die('Error: ' . mysql_error());}
				  foreach(mysql_fetch_assoc($polecenie_historia) as $id){
							$sql3="INSERT INTO [YOUR_DEVICE_HISTORY_TABLE] ..."; 
							if (!mysql_query($sql3)){die('Error: ' . mysql_error());}
				  }
                }
              }
            }
          }
}
executesnmp();
echo 'result : OK!';
?>
