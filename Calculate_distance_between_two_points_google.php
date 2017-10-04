### Wywołanie przy pomocy curl w crontab: */15 * * * * root /usr/bin/curl --silent http://xxx.xxx.xxx.xxx/calc_distance.php >> /var/log/logcalc_distance.txt

### Zastosowane toporne rozwiązanie problemu z kodowaniem znaków (z mysql'a) przy użyciu tablicy z poprawnym kodowaniem jako klucz oraz niepoprawnym jako wartość

### Skrypt odblicza trasę pomiędzy dwoma punktami na terenie Poznania (częśc ulic)

### Głównym zadaniem skryptu jest obliczanie przybliżonej długośći tras dla "grup" instalatorów 


<?php
header('Content-Type: text/html; charset=utf-8');
require_once('/usr/lib64/nagios/plugins/connect_webcalendar.php');

/*** connect.php

<?php
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
?>
*///


$mysqltime = date("Ymd");
$mysqlmodtime = date("Y-m-d H:i:s");
date_default_timezone_set('Europe/Warsaw');

function remove(){
		$timetodelete = date("Y-m-d");
        $delete = mysql_query("delete from distance where date='$timetodelete'") or die('remove'.mysql_error());
        echo 'usunieto';
}
function insert($origin, $origin_id, $destination, $destination_id, $distance_beetwen, $time_beetwen, $group, $date, $mod_date){
        $insert = mysql_query("insert into distance(origin, origin_id, destination, destination_id, distance_beetwen, time_beetwen, group_name, date, date_mod) 
						values('$origin',$origin_id,'$destination',$destination_id,'$distance_beetwen','$time_beetwen','$group','$date','$mod_date')") or die("insert ".mysql_error());
        if (!mysql_query($insert)){
                       die('Error: ' . mysql_error());
        }
}
function update($origin, $origin_id, $destination, $destination_id, $distance_beetwen, $time_beetwen, $group, $date){
        $update = mysql_query("update distance set origin='$origin', origin_id='$origin_id', destination='$destination', destination_id='$destination_id', 
						distance_beetwen='$distance_beetwen', time_beetwen='$time_beetwen', group = '$group', date='$date'") or die("update ".mysql_error());
        if (!mysql_query($update)){
                        die('Error: ' . mysql_error());
        }
}
function search($origin, $origin_id, $destination, $destination_id, $group, $date){
        $search = mysql_query("select * from distance where origin_id='$origin_id' and destination_id='$destination_id 
					'origin='$origin' and destination='$destination' and group='$group' and date='$date' limit 1") or die("search ".mysql_error());
        if (!mysql_query($search)){
                        die('Error: ' . mysql_error());
        }
}
function counts($origin_id, $destination_id, $group, $date){
        $counts = mysql_query("select count(id) from distance where origin_id='$origin_id' and destination_id='$destination_id' and group_name='$group' and date='$date' limit 1") or die("counts ".mysql_error());
        return mysql_fetch_row($counts);
}
$ulice = array(
        'al. Aleje Karola Marcinkowskiego' => 'al. Aleje Karola Marcinkowskiego',
        'al. Niepodległości' => 'al. Niepodleg³o¶ci',
        'ul. Opalowa' => 'Opalowa',
        'os. Bolesława Śmiałego' => 'os. Boles³awa ¦mia³ego',
        'os. Kosmonautów' => 'os. Kosmonautów',
        'os. Lotnictwa Polskiego' => 'os. Lotnictwa Polskiego',
        'Os. Natura' => 'Os. Natura',
        'os. Powstańców Warszawy' => 'os. Powstañców Warszawy',
        'os. Stare Żegrze' => 'os. Stare ¯egrze',
        'os. Władysława Łokietka' => 'os. W³adys³awa £okietka',
        'os. Kalinowe' => 'os. Kalinowe',
        'os. Szafirowe' => 'os. Szafirowe',
        'os. Władysława Zamoyskiego' => 'os. W³adys³awa Zamoyskiego',
        'pl. Cyryla Ratajskiego' => 'pl. Cyryla Ratajskiego',
        'pl. Wiosny Ludów' => 'pl. Wiosny Ludów',
        'ul. Szkolna' => 'Szkolna',
        'ul. 1-go Maja' => 'ul. 1-go Maja',
        'ul. 23 Lutego' => 'ul. 23 Lutego',
        'ul. 27 Grudnia' => 'ul. 27 Grudnia',
        'ul. 28 Czerwca 1956 r.' => 'ul. 28 Czerwca 1956 r.',
        'ul. 7 Pułku Strzelców Konnych' => 'ul. 7 Pu³ku Strzelców Konnych',
        'ul. abpa Walentego Dymka' => 'ul. abpa Walentego Dymka',
        'ul. Adama Mickiewicza' => 'ul. Adama Mickiewicza',
        'ul. Ajschylosa' => 'ul. Ajschylosa',
        'ul. Akacjowa' => 'ul. Akacjowa',
        'ul. Akacjowa' => 'ul. Akacjowa',
        'ul. Antoniego Andrzejewskiego' => 'ul. Antoniego Andrzejewskiego',
        'ul. Antoniego Kosińskiego' => 'ul. Antoniego Kosiñskiego',
        'ul. Arkadego Fiedlera' => 'ul. Arkadego Fiedlera',
        'ul. Arystofanesa' => 'ul. Arystofanesa',
        'ul. Augusta Emila Fieldorfa' => 'ul. Augusta Emila Fieldorfa',
        'ul. Bałtycka' => 'ul. Ba³tycka',
        'ul. Berylowa' => 'ul. Berylowa',
        'ul. Bełchatowska' => 'ul. Be³chatowska',
        'ul. Bielniki' => 'ul. Bielniki',
        'ul. Blacharska' => 'ul. Blacharska',
        'ul. Bogumiła' => 'ul. Bogumi³a',
        'ul. Bogusława' => 'ul. Bogus³awa',
        'ul. Bolesława Krysiewicza' => 'ul. Boles³awa Krysiewicza',
        'ul. Bolka' => 'ul. Bolka',
        'ul. Boranta' => 'ul. Boranta',
        'ul. Borowikowa' => 'ul. Borowikowa',
        'ul. Bosa' => 'ul. Bosa',
        'ul. Bóżnicza' => 'ul. Bó¿nicza',
        'ul. Bratumiły' => 'ul. Bratumi³y',
        'ul. Brzask' => 'ul. Brzask',
        'ul. Brzozowa' => 'ul. Brzozowa',
        'ul. Budzisława' => 'ul. Budzis³awa',
        'ul. Bukowska' => 'ul. Bukowska',
        'ul. Bursztynowa' => 'ul. Bursztynowa',
        'ul. Bułgarska' => 'ul. Bu³garska',
        'ul. Błażeja' => 'ul. B³a¿eja',
        'ul. Chludowska' => 'ul. Chludowska',
        'ul. Chwaliszewo' => 'ul. Chwaliszewo',
        'ul. Cyprysowa' => 'ul. Cyprysowa',
        'ul. Czarnucha' => 'ul. Czarnucha',
        'ul. Czechosłowacka' => 'ul. Czechos³owacka',
        'ul. Cześnikowska' => 'ul. Cze¶nikowska',
        'ul. Daglezjowa' => 'ul. Daglezjowa',
        'ul. Daleka' => 'ul. Daleka',
        'ul. Dalemińska' => 'ul. Dalemiñska',
        'ul. Dębowa' => 'ul. Dêbowa',
        'ul. Diamentowa' => 'ul. Diamentowa',
        'ul. Dojazd' => 'ul. Dojazd',
        'ul. Dominikańska' => 'ul. Dominikañska',
        'ul. Dożynkowa' => 'ul. Do¿ynkowa',
        'ul. Dworska' => 'ul. Dworska',
        'ul. Długa' => 'ul. D³uga',
        'ul. Elizy Orzeszkowej' => 'ul. Elizy Orzeszkowej',
        'ul. Emilii Sczanieckiej' => 'ul. Emilii Sczanieckiej',
        'ul. Eurypidesa' => 'ul. Eurypidesa',
        'ul. Feliksa Nowowiejskiego' => 'ul. Feliksa Nowowiejskiego',
        'ul. Franciszka Firlika' => 'ul. Franciszka Firlika',
        'ul. Franciszka Morawskiego' => 'ul. Franciszka Morawskiego',
        'ul. Franciszka Ratajczaka' => 'ul. Franciszka Ratajczaka',
        'ul. Fryderyka Chopina' => 'ul. Fryderyka Chopina',
        'ul. Gajowa' => 'ul. Gajowa',
        'ul. Garbary' => 'ul. Garbary',
        'ul. gen. Stanisława Maczka' => 'ul. gen. Stanis³awa Maczka',
        'ul. gen. Tadeusza Kutrzeby' => 'ul. gen. Tadeusza Kutrzeby',
        'ul. Gnieźnieńska' => 'ul. Gnie¼nieñska',
        'ul. Góralska' => 'ul. Góralska',
        'ul. Górczyńska' => 'ul. Górczyñska',
        'ul. Górki' => 'ul. Górki',
        'ul. Grochowe Łąki' => 'ul. Grochowe £±ki',
        'ul. Grochowska' => 'ul. Grochowska',
        'ul. Grunwaldzka' => 'ul. Grunwaldzka',
        'ul. Głogowska' => 'ul. G³ogowska',
        'ul. Głuchowska' => 'ul. G³uchowska',
        'ul. Hawelańska' => 'ul. Hawelañska',
        'ul. Henryka Opieńskiego' => 'ul. Henryka Opieñskiego',
        'ul. Henryka Sienkiewicza' => 'ul. Henryka Sienkiewicza',
        'ul. Henryka Sienkiewicza' => 'ul. Henryka Sienkiewicza',
        'ul. Heweliusza' => 'ul. Heweliusza',
        'ul. Homera' => 'ul. Homera',
        'ul. Horacego' => 'ul. Horacego',
        'ul. Inflancka' => 'ul. Inflancka',
        'ul. Jakuba Krauthofera' => 'ul. Jakuba Krauthofera',
        'ul. Jana Długosza' => 'ul. Jana D³ugosza',
        'ul. Dąbrowskiego' => 'ul. Jana Henryka D±browskiego',
        'ul. Jana Kochanowskiego' => 'ul. Jana Kochanowskiego',
        'ul. Jana Matejki' => 'ul. Jana Matejki',
        'ul. Janusza Meissnera' => 'ul. Janusza Meissnera',
        'ul. Jasielska' => 'ul. Jasielska',
        'ul. Jasna Rola' => 'ul. Jasna Rola',
        'ul. Jaśminowa' => 'ul. Ja¶minowa',
        'ul. Jeleniogórska' => 'ul. Jeleniogórska',
        'ul. Jesionowa' => 'ul. Jesionowa',
        'ul. Jesionowa' => 'ul. Jesionowa',
        'ul. Jeżycka' => 'ul. Je¿ycka',
        'ul. Jodłowa' => 'ul. Jod³owa',
        'ul. Józefa Sowińskiego' => 'ul. Józefa Sowiñskiego',
        'ul. Józefa Żymierskiego' => 'ul. Józefa ¯ymierskiego',
        'ul. Juliusza Słowackiego' => 'ul. Juliusza S³owackiego',
        'ul. Juranda' => 'ul. Juranda',
        'ul. Juwenalisa' => 'ul. Juwenalisa',
        'ul. Kaliska' => 'ul. Kaliska',
        'ul. Kamiennogórska' => 'ul. Kamiennogórska',
        'ul. Kargowska' => 'ul. Kargowska',
        'ul. Karola Libelta' => 'ul. Karola Libelta',
        'ul. Karpia' => 'ul. Karpia',
        'ul. Kartuska' => 'ul. Kartuska',
        'ul. Kasztanowa' => 'ul. Kasztanowa',
        'ul. Kasztanowa' => 'ul. Kasztanowa',
        'ul. Katowicka' => 'ul. Katowicka',
        'ul. Kazimierza Wielkiego' => 'ul. Kazimierza Wielkiego',
        'ul. Klaudyny Potockiej' => 'ul. Klaudyny Potockiej',
        'ul. Klemensa Janickiego' => 'ul. Klemensa Janickiego',
        'ul. Klonowa' => 'ul. Klonowa',
        'ul. Kolejowa' => 'ul. Kolejowa',
        'ul. Konfederacka' => 'ul. Konfederacka',
        'ul. Kosynierska' => 'ul. Kosynierska',
        'ul. Kościelna' => 'ul. Ko¶cielna',
        'ul. Kramarska' => 'ul. Kramarska',
        'ul. ks. Jakuba Wujka' => 'ul. ks. Jakuba Wujka',
        'ul. Księcia Mieszka I' => 'ul. Ksiêcia Mieszka I',
        'ul. Ku Cytadeli' => 'ul. Ku Cytadeli',
        'ul. Kudowska' => 'ul. Kudowska',
        'ul. Kurkowa' => 'ul. Kurkowa',
        'ul. Kwiatowa' => 'ul. Kwiatowa',
        'ul. Kłuszyńska' => 'ul. K³uszyñska',
        'ul. Lebiodowa' => 'ul. Lebiodowa',
        'ul. Leszczyńska' => 'ul. Leszczyñska',
        'ul. Leśna' => 'ul. Le¶na',
        'ul. Lipowa' => 'ul. Lipowa',
        'ul. Lipowa' => 'ul. Lipowa',
        'ul. Literacka' => 'ul. Literacka',
        'ul. Lodowa' => 'ul. Lodowa',
        'ul. Ludwika Braille\'a' => 'ul. Ludwika Braille\'a',
        'ul. Ludwika Zamenhofa' => 'ul. Ludwika Zamenhofa',
        'ul. Lukrecjusza' => 'ul. Lukrecjusza',
        'ul. Macieja Palacza' => 'ul. Macieja Palacza',
        'ul. Maksymiliana Jackowskiego' => 'ul. Maksymiliana Jackowskiego',
        'ul. Marcelińska' => 'ul. Marceliñska',
        'ul. Marcina Chwiałkowskiego' => 'ul. Marcina Chwia³kowskiego',
        'ul. Marcina Kasprzaka' => 'ul. Marcina Kasprzaka',
        'ul. Mariana Jaroczyńskiego' => 'ul. Mariana Jaroczyñskiego',
        'ul. Mariana Smoluchowskiego' => 'ul. Mariana Smoluchowskiego',
        'ul. Marii Konopnickiej' => 'ul. Marii Konopnickiej',
        'ul. Marii Wicherkiewicz' => 'ul. Marii Wicherkiewicz',
        'ul. Marka Hłaski' => 'ul. Marka H³aski',
        'ul. Mazowiecka' => 'ul. Mazowiecka',
        'ul. Małe Garbary' => 'ul. Ma³e Garbary',
        'ul. Małopolska' => 'ul. Ma³opolska',
        'ul. Melchiora Wańkowicza' => 'ul. Melchiora Wañkowicza',
        'ul. Michała Drzymały' => 'ul. Micha³a Drzyma³y',
        'ul. Mieczysława Rawicz-Mysłowskiego' => 'ul. Mieczys³awa Rawicz-Mys³owskiego',
        'ul. Międzychodzka' => 'ul. Miêdzychodzka',
        'ul. Międzyleska' => 'ul. Miêdzyleska',
        'ul. Modra' => 'ul. Modra',
        'ul. Mokra' => 'ul. Mokra',
        'ul. Morzyczańska' => 'ul. Morzyczañska',
        'ul. Mostowa' => 'ul. Mostowa',
        'ul. Murawa' => 'ul. Murawa',
        'ul. Mylna' => 'ul. Mylna',
        'ul. Młyńska' => 'ul. M³yñska',
        'ul. Na Miasteczku' => 'ul. Na Miasteczku',
        'ul. Nad Bogdanką' => 'ul. Nad Bogdank±',
        'ul. Nad Wierzbakiem' => 'ul. Nad Wierzbakiem',
        'ul. Nadolnik' => 'ul. Nadolnik',
        'ul. Naramowicka' => 'ul. Naramowicka',
        'ul. Nałęczowska' => 'ul. Na³êczowska',
        'ul. Nefrytowa' => 'ul. Nefrytowa',
        'ul. Nikodema Pajzderskiego' => 'ul. Nikodema Pajzderskiego',
        'ul. Noblistów' => 'ul. Noblistów',
        'ul. Ogrodowa' => 'ul. Ogrodowa',
        'ul. Ogrodowa' => 'ul. Ogrodowa',
        'ul. Opolska' => 'ul. Opolska',
        'ul. Osiedlowa' => 'ul. Osiedlowa',
        'ul. Owidiusza' => 'ul. Owidiusza',
        'ul. Pasieka' => 'ul. Pasieka',
        'ul. Pawła Włodkowica' => 'ul. Paw³a W³odkowica',
        'ul. Perłowa' => 'ul. Per³owa',
        'ul. Petera Mansfelda' => 'ul. Petera Mansfelda',
        'ul. Piaskowa' => 'ul. Piaskowa',
        'ul. Piekary' => 'ul. Piekary',
        'ul. Piotra Wawrzyniaka' => 'ul. Piotra Wawrzyniaka',
        'ul. Piątkowska' => 'ul. Pi±tkowska',
        'ul. Planetarna' => 'ul. Planetarna',
        'ul. Plauta' => 'ul. Plauta',
        'ul. Plebańska' => 'ul. Plebañska',
        'ul. Pod Gruszą' => 'ul. Pod Grusz±',
        'ul. Podgórna' => 'ul. Podgórna',
        'ul. Podgrzybkowa' => 'ul. Podgrzybkowa',
        'ul. Podlaska' => 'ul. Podlaska',
        'ul. Polanka' => 'ul. Polanka',
        'ul. Polna' => 'ul. Polna',
        'ul. Powstańców Wielkopolskich' => 'ul. Powstañców Wielkopolskich',
        'ul. Poznańska' => 'ul. Poznañska',
        'ul. Półwiejska' => 'ul. Pó³wiejska',
        'ul. Promienista' => 'ul. Promienista',
        'ul. Przelot' => 'ul. Przelot',
        'ul. Przemysłowa' => 'ul. Przemys³owa',
        'ul. Przepiórcza' => 'ul. Przepiórcza',
        'ul. Przy Trakcie' => 'ul. Przy Trakcie',
        'ul. Radosna' => 'ul. Radosna',
        'ul. Rembertowska' => 'ul. Rembertowska',
        'ul. Robocza' => 'ul. Robocza',
        'ul. Rolna' => 'ul. Rolna',
        'ul. Romana Dmowskiego' => 'ul. Romana Dmowskiego',
        'ul. Romana Szymańskiego' => 'ul. Romana Szymañskiego',
        'ul. Romualda Traugutta' => 'ul. Romualda Traugutta',
        'ul. Rubież' => 'ul. Rubie¿',
        'ul. Rubinowa' => 'ul. Rubinowa',
        'ul. Rybaki' => 'ul. Rybaki',
        'ul. Rynarzewska' => 'ul. Rynarzewska',
        'ul. Safony' => 'ul. Safony',
        'ul. Saperska' => 'ul. Saperska',
        'ul. Sarnia' => 'ul. Sarnia',
        'ul. Seneki' => 'ul. Seneki',
        'ul. Serbska' => 'ul. Serbska',
        'ul. Seweryna Mielżyńskiego' => 'ul. Seweryna Miel¿yñskiego',
        'ul. Sielawy' => 'ul. Sielawy',
        'ul. Sielska' => 'ul. Sielska',
        'ul. Smardzewska' => 'ul. Smardzewska',
        'ul. Smolna' => 'ul. Smolna',
        'ul. Smolna' => 'ul. Smolna',
        'ul. Sofoklesa' => 'ul. Sofoklesa',
        'ul. Sokoła' => 'ul. Soko³a',
        'ul. Solna' => 'ul. Solna',
        'ul. Sowia' => 'ul. Sowia',
        'ul. Sowiniecka' => 'ul. Sowiniecka',
        'ul. Stanisława Hejmowskiego' => 'ul. Stanis³awa Hejmowskiego',
        'ul. Stanisława Knapowskiego' => 'ul. Stanis³awa Knapowskiego',
        'ul. Stanisława Przybyszewskiego' => 'ul. Stanis³awa Przybyszewskiego',
        'ul. Stanisława Szczepanowskiego' => 'ul. Stanis³awa Szczepanowskiego',
        'ul. Stefana Czarnieckiego' => 'ul. Stefana Czarnieckiego',
        'ul. Straży Ludowej' => 'ul. Stra¿y Ludowej',
        'ul. Strzałowa' => 'ul. Strza³owa',
        'ul. Strzelecka' => 'ul. Strzelecka',
        'ul. Strzeszyńska' => 'ul. Strzeszyñska',
        'ul. Studzienna' => 'ul. Studzienna',
        'ul. Sucha' => 'ul. Sucha',
        'ul. Szafirowa' => 'ul. Szafirowa',
        'ul. Szydłowska' => 'ul. Szyd³owska',
        'ul. Szyperska' => 'ul. Szyperska',
        'ul. Słowiańska' => 'ul. S³owiañska',
        'ul. Tadeusza Boya-Żeleńskiego' => 'ul. Tadeusza Boya-¯eleñskiego',
        'ul. Tadeusza Kościuszki' => 'ul. Tadeusza Ko¶ciuszki',
        'ul. Teofila Mateckiego' => 'ul. Teofila Mateckiego',
        'ul. Teokryta' => 'ul. Teokryta',
        'ul. Topolowa' => 'ul. Topolowa',
        'ul. Towarowa' => 'ul. Towarowa',
        'ul. Trakt Napoleoński' => 'ul. Trakt Napoleoñski',
        'ul. Trójpole' => 'ul. Trójpole',
        'ul. Truflowa' => 'ul. Truflowa',
        'ul. Ugory' => 'ul. Ugory',
        'ul. Umultowska' => 'ul. Umultowska',
        'ul. Urbanowska' => 'ul. Urbanowska',
        'ul. Ułańska' => 'ul. U³añska',
        'ul. Warmińska' => 'ul. Warmiñska',
        'ul. Warszawska' => 'ul. Warszawska',
        'ul. Wałbrzyska' => 'ul. Wa³brzyska',
        'ul. Wenecjańska' => 'ul. Wenecjañska',
        'ul. Wergiliusza' => 'ul. Wergiliusza',
        'ul. Widna' => 'ul. Widna',
        'ul. Wiejska' => 'ul. Wiejska',
        'ul. Wiejska' => 'ul. Wiejska',
        'ul. Wielka' => 'ul. Wielka',
        'ul. Wierzbięcice' => 'ul. Wierzbiêcice',
        'ul. Wierzbowa' => 'ul. Wierzbowa',
        'ul. Wilczak' => 'ul. Wilczak',
        'ul. Winiarska' => 'ul. Winiarska',
        'ul. Winogrady' => 'ul. Winogrady',
        'ul. Witolda Gombrowicza' => 'ul. Witolda Gombrowicza',
        'ul. Witolda Pileckiego' => 'ul. Witolda Pileckiego',
        'ul. Wiślana' => 'ul. Wi¶lana',
        'ul. Wodna' => 'ul. Wodna',
        'ul. Wojska Polskiego' => 'ul. Wojska Polskiego',
        'ul. Wojskowa' => 'ul. Wojskowa',
        'ul. Wroniecka' => 'ul. Wroniecka',
        'ul. Wysoka' => 'ul. Wysoka',
        'ul. Władysława Nehringa' => 'ul. W³adys³awa Nehringa',
        'ul. Władysława Reymonta' => 'ul. W³adys³awa Reymonta',
        'ul. Za Cytadelą' => 'ul. Za Cytadel±',
        'ul. Zagórze' => 'ul. Zagórze',
        'ul. Zawady' => 'ul. Zawady',
        'ul. Zbąszyńska' => 'ul. Zb±szyñska',
        'ul. Ziębicka' => 'ul. Ziêbicka',
        'ul. Zofii Nałkowskiej' => 'ul. Zofii Na³kowskiej',
        'ul. Zwierzyniecka' => 'ul. Zwierzyniecka',
        'ul. Zygmunta Grudzińskiego' => 'ul. Zygmunta Grudziñskiego',
        'ul. Zygmunta Kubiaka' => 'ul. Zygmunta Kubiaka',
        'ul. Łozowa' => 'ul. £ozowa',
        'ul. Śpiewaków' => 'ul. ¦piewaków',
        'ul. Świebodzińska' => 'ul. ¦wiebodziñska',
        'ul. Świerczewskiego' => 'ul. ¦wierczewskiego',
        'ul. Świerzawska' => 'ul. ¦wierzawska',
        'ul. Świętowidzka' => 'ul. ¦wiêtowidzka',
        'ul. Święty Marcin' => 'ul. ¦wiêty Marcin',
        'ul. Święty Marcin' => 'ul. ¦wiêty Marcin',
        'ul. Żorska' => 'ul. ¯orska',
        'ul. św. Leonarda' => 'ul. ¶w. Leonarda',
        'ul. św. Wawrzyńca' => 'ul. ¶w. Wawrzyñca',
        'ul. Zjednoczenia' => 'ul. Zjednoczenia',
        'ul. Źródlana' => 'ul. ¬ródlana',
        'ul. Żabikowska' => 'ul. ¯abikowska'
);

function calculate_distance(){
        global $ulice;
        global $mysqltime;
        global $mysqlmodtime;
        for($j=1;$j<=11;$j++){
				### Dane adresowe są pobierane z tabeli terminarz zgodnie z przydzieloną grupą "zadaniową"
                $query = mysql_query("select 
				teminarz.id, 
				teminarz.name, 
				teminarz.miejscowosc, 
				teminarz.ulica, 
				teminarz.budynek, 
				teminarz.klatka, 
				teminarz.lokal, 
				teminarz.date, 
				teminarz.time, 
				teminarz.duration, 
				teminarz_user.id, 
				teminarz_user.status
				from teminarz 
				inner join teminarz_user on teminarz.id=teminarz_user.id where teminarz.name='Inst Gr.$j' 
				and teminarz.date='$mysqltime' and teminarz_user.status != 'D' and teminarz_user.login = '__public__' and 
				teminarz.ulica not like'DODATKO%' and teminarz.ulica not like'PO PO%' 
				AND teminarz.ulica != 'Brak danych' order by teminarz.time;
                ") or die ("coś poszło nie tak".mysql_error());	
                while(($row = mysql_fetch_row($query)) != false){
                   $rows[] = $row;
                }
				
                # Array counts -2 because the last point does not another end point
                # if count = 5 , start index 0, step 1 (0-1), step 2 (1-2), step 3 (2-3), step 4 (3-4), step 5 (4-5) where 5 is null
                for($i=0;$i<=count($rows)-2;$i++){
						#znajdz pierwszy pkt.
                        if(array_search($rows[$i][3], $ulice)){
                                $ulica = array_search($rows[$i][3], $ulice);
                        }
						#znajdz drugi pkt.
                        if(array_search($rows[$i+1][3], $ulice)){
                                $ulica_2 = array_search($rows[$i+1][3], $ulice);
                        }
						
						# w dwóch przypadkach poniżej występuje problem z wyszukaniem ulic w google api
                        if($ulica ==  'ul. Wojska Polskiego' || $ulica_2 == 'ul. Wojska Polskiego') {
                                        $rows[$i][4] = substr($rows[$i][4],0,5);
                                        $rows[$i+1][4] = substr($rows[$i+1][4],0,5);
                        }
                        if($ulica ==  'ul. Zjednoczenia' || $ulica_2 == 'ul. Zjednoczenia'){
                                        $rows[$i][4] = substr($rows[$i][4],0,5);
                                        $rows[$i+1][4] = substr($rows[$i+1][4],0,5);
                        }
						#
                        $from = $ulica.' '.$rows[$i][4].''.$rows[$i][5].', '.$rows[$i][2];
                        $to = $ulica_2.' '.$rows[$i+1][4].''.$rows[$i+1][5].', '.$rows[$i+1][2];
						
                        #echo $j.' '.$from.' '.$to.'<br>';
						
						#transformacja zmiennych na kodowanie wykorzysytwane w URL'ach
                        $from = urlencode($from);
                        $to = urlencode($to);
						#wykonanie
                        $data = file_get_contents("https://maps.googleapis.com/maps/api/distancematrix/json?key=your_key&origins=$from&destinations=$to&language=en-EN&sensor=false");
						#zdekodowanie
                        $data = json_decode($data);
                        // var_dump($data);
                        $time = 0;
                        $distance = 0;
                        foreach($data->rows[0]->elements as $road) {
                                $time += $road->duration->value;
                                $distance += $road->distance->value;
                        }
                        $distance = $distance * 0.001;
                        $time = $time / 60;
                        $origins = substr($data->origin_addresses[0],0,-8);
                        $origin_id = $rows[$i][0];
                        $destinations = substr($data->destination_addresses[0],0,-8);
                        $destination_id = $rows[$i+1][0];
                        $distance_beetwen = round($distance,2);
                        $time_beetwen = round($time,2);
                        $group_name = $rows[$i][1];
						#jeżeli nie istnieje to wykonaj -> count = 0 w innym przypadku kontynuuj 
                        $count = counts($origin_id, $destination_id, $group_name, $mysqltime);
                        if($count[0] == '0'){
                                insert($origins, $origin_id, $destinations, $destination_id, $distance_beetwen, $time_beetwen, $group_name, $mysqltime, $mysqlmodtime);
                        }else{
                                echo 'Już istnieje';
                                continue;
                        }
                        // echo $rows[$i][0].' '.$rows[$i][1].' '.$ulica.' '.$rows[$i][4].', '.$rows[$i][2].' '.$mysqltime.'<br/>';
                        // echo $rows[$i+1][0].' '.$rows[$i+1][1].' '.$ulica_2.' '.$rows[$i+1][4].', '.$rows[$i+1][2].' '.$mysqltime.'<br/>';
                }
                unset($rows);
        }
}
remove();
calculate_distance();
connection();
?>
