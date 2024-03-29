<?php  
// ===== BEGIN / Input Variable =====
define("VAR_MYSQL_HOST", “<hostmysql>”);
define("VAR_MYSQL_USER", “<usermysql>”);
define("VAR_MYSQL_PASSWORD", “<passmysql>”);
define("VAR_MYSQL_DBNAME", “<dbname>“);
define("VAR_BOT_TOKEN", “<token>“);
define("VAR_TELEGRAM_CHATID", “<chatid>“);
define("VAR_PROXY_SERVER", “<proxyserver>“);
define("VAR_CACERT_PATHFILE", “<cafile>”); 
define("VAR_RTOTIME_TOLERANT", “<rtotolerant>“); // max 59 minutes

 
// ===== BEGIN / Connection =====
$koneksi = mysql_connect(VAR_MYSQL_HOST, VAR_MYSQL_USER, VAR_MYSQL_PASSWORD);
$pilihdatabase = mysql_select_db(VAR_MYSQL_DBNAME, $koneksi);

// ===== BEGIN / Function untuk Kirim Telegram =====
function kirim_telegram($pesan){
	$pesan = urlencode($pesan);
	$bot_token=VAR_BOT_TOKEN;
	$chat_id=VAR_TELEGRAM_CHATID;
	$proxy=VAR_PROXY_SERVER;
	$cacert_file_path=VAR_CACERT_PATHFILE;
	
	$token = "bot"."$bot_token";
	$url = "https://api.telegram.org/$token/sendMessage?parse_mode=markdown&chat_id=$chat_id&disable_web_page_preview=true&text=$pesan";
	$ch = curl_init();
		
	if($proxy==""){
		$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CAINFO => "$cacert_file_path"	
		);
	}
	else{ 
		$optArray = array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_PROXY => "$proxy",
			CURLOPT_CAINFO => "$cacert_file_path"	
		);	
	}
		
	curl_setopt_array($ch, $optArray);
	$result = curl_exec($ch);
		
	$err = curl_error($ch);
	curl_close($ch);	
		
	if($err<>"") echo "<br><b>Error: $err</b><br><br>";
	else echo "<br><b> --- Telegram SENT ---</b><br><br>";
}

// ===== BEGIN / Ping Network =====

date_default_timezone_set("Asia/Jakarta");
$bulan=date("Y-m-d");

$menit=$_GET['menit']; 
if($menit>150 || $menit=="") $menit=0;
else if($pilihdatabase) $menit++;

if((($menit-0) % 15) == 0 || $menit==1) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 0,20) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else if((($menit-1) % 15) == 0 || $menit==2 ) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 21,20) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else if((($menit-2) % 15) == 0 || $menit==3 ) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 41,20) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else if((($menit-3) % 15) == 0 || $menit==4 ) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 61,20) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else if((($menit-4) % 15) == 0 || $menit==5 ) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 81,20) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else if((($menit-5) % 15) == 0 || $menit==6 ) $strSQL2 = "(SELECT * FROM data_ip where dipindai=1 order by ip_address LIMIT 101,300) UNION (SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address)";
else $strSQL2 = "SELECT * FROM data_ip where dipindai=1 and status='offline' order by ip_address";

$tampilkanSQL = $strSQL2;
$select_query2 = mysql_query($strSQL2);
$data2 = $select_query2;
$jml2 = mysql_num_rows($data2);

for ($i=0; $i<$jml2; $i++) {
	$results = mysql_fetch_array($data2);
   	$ip = $results["ip_address"] ;
	$tgl=date("Y-m-d");
	$jam=date("H:i:s");
	$tgljam=date("Y-m-d H:i:s");

	$ping = shell_exec("ping -n 1 $ip"); 
			
	if(strpos(strtoupper($ping),"TTL=")<=0 or strpos($ping,"TTL expired")>0 or strpos($ping,"Request timed out")>0 or strpos($ping,"Destination host unreachable")>0){
		$status="rto"; 
		$strSQL = "SELECT * FROM monitor_rto where ip_address='$ip' and tanggal_reply is null";
		$select_query = mysql_query($strSQL);
		$data = $select_query;
		$hasil2 = mysql_fetch_array($data);
		$menit_ke = $hasil2["menit_ke"];
		$menit_ke_baru = $hasil2["menit_ke"] + 1;
		$email = $hasil2["email"];
			 
		$jml = mysql_num_rows($data);
		if ($jml==0){
			$strSQL = "INSERT INTO monitor_rto";
			$strSQL .="(ip_address,nama_unit,tanggal_rto,jam_rto,menit_ke,email, tanggal_rto2) ";
			$strSQL .="VALUES ";
			$strSQL .="('".$ip."','".$results["nama_unit"]."','".$tgl."','".$jam."',1,3, '".$tgljam."' )";
			$update = mysql_query($strSQL); 
		}

		// cek ada rto baru ga
		$strSQLrto = "
			select * from data_ip where ip_address='$ip' and dipindai=1 and 
			timediff(now(),tanggal_rto) > '00:58:00' and timediff(now(),tanggal_rto) < '00:60:00' 
		";
		$rtobaru = mysql_query($strSQLrto); 
		$jmlrtobaru += mysql_num_rows($rtobaru);

		$strSQL2 = "UPDATE data_ip";
		$strSQL2 .=" SET status='offline',jam_rto='$jam',tanggal_rto='$tgljam', tanggal_reply=null "; 
		$strSQL2 .="where status='online' and ip_address='$ip'";
		$update2 = mysql_query($strSQL2); 
	} 
	else {
		$status="reply";  
		$strSQL = "UPDATE monitor_rto";
		$strSQL .=" SET tanggal_reply='$tgl',jam_reply='$jam', tanggal_reply2='$tgljam', durasi2=timediff('$tgljam',tanggal_rto2) "; 
		$strSQL .="where ip_address='$ip' and tanggal_reply is null";
		$update = mysql_query($strSQL); 

		$strSQL2 = "UPDATE data_ip";
		$strSQL2 .=" SET status='online',tanggal_reply='$tgljam' ";
		$strSQL2 .="where status='offline' and ip_address='$ip'";
		$update2 = mysql_query($strSQL2); 
	}
}	 
if ($jmlrtobaru>0) $menit=0;
?>

<html>
<head>
<title>Network Monitoring (menit ke-<?=$menit?>)</title>
<!-- refresh script setiap 1 menit -->
<meta http-equiv="refresh" content="60; url=<?php echo $_SERVER['PHP_SELF']."?menit=".$menit; ?>">
<style type="text/css">
		body {
			font-family: Arial, Verdana, sans-serif;
		}
	  </style>
</head>
<body>
<h1>Monitoring Network </h1>
<h2>Tanggal <?=date("d-M-Y H:i")?> (menit ke-<?=$menit?>)</h2>
<h3><font color=#FF0000>(halaman ini jangan ditutup)</font></h3>


<?php

// ===== BEGIN / Buat Greeting Telegram =====
if(date("H")>=21) $gre = "Sori ganggu waktu istirahatnya..."; 
else if(date("H")>=20) $gre = "Halo belum tidur kan?";
else if(date("H")>=19) $gre = "Malem semua...";
else if(date("H")>=18) $gre = "Met petang semua...";
else if(date("H")>=15) $gre = "Sore semua...";
else if(date("H")>=13) $gre = "Siang semua...";
else if(date("H")>=12) $gre = "Hai dah makan siang belum?";
else if(date("H")>=10) $gre = "Siang semua...";
else if(date("H")>=8) $gre = "Pagi semua...";
else if(date("H")>=7) $gre = "Hai dah sarapan kan?";
else if(date("H")>=5) $gre = "Pagi semua...";
else if(date("H")>=4) $gre = "Halo dah bangun kan?";
else $gre = "Sori ganggu waktu tidurnya..."; 


// ===== BEGIN / Kirim Telegram Bila Ada yang Down ===== 
$rto_tolerant = VAR_RTOTIME_TOLERANT;
$query = "
	select service_id, gps_x, gps_y,pic,
	kategori, nama_unit, ip_address, DATE_FORMAT(tanggal_rto,'%d %M %Y jam %H:%i WIB') tanggal_rto, 
	status, cast( timediff(now(),tanggal_rto) as char) lama_padam 
	from data_ip where dipindai=1 and status='offline' and 
	timediff(now(),tanggal_rto)> '00:$rto_tolerant:00'
	order by pic
";

$hasil = mysql_query($query);
$jumlah = mysql_num_rows($hasil); 
if ($jumlah>0){
	$isi="";
	$warnaheader="#7CFC00";
	$warnagenap="#E0FFFF";
	$warnaganjil="#FOF8FF";
	$counter=1;
	$ctr = 0;
	$ctr2 = 0;

	while ($data = mysql_fetch_array($hasil)){
		if($counter % 2==0) $warna=$warnagenap;
		else $warna=$warnaganjil;
		
		$tanggal_rto = $data[tanggal_rto];
		$tanggal_reply = $data[tanggal_reply];
		$jam_rto = $data[jam_rto];
		$jam_reply = $data[jam_reply];
		$lama_padam = $data[lama_padam];
		$pic = $data[pic];
		$status = $data[status];
		$nama_unit = $data[nama_unit];
		$ip_address = $data[ip_address];
		$kategori = $data[kategori];
		$service_id = $data[service_id];
		$gps_x = $data[gps_x];
		$gps_y = $data[gps_y];
		$kat = $data[kategori];
		
		if($service_id<>"") $service_id = " SID ".$service_id;
		else $service_id = "";
		
		if($gps_x<>"" && $gps_y<>"") $gps = "[Lihat Lokasi.](http://maps.google.com/maps?q=$gps_y,$gps_x)";
		else $gps = "";
		
		// isi di halaman web
		$isi=$isi. '<tr bgcolor='.$warna.'><tr bgcolor='.$warna.'><td>'. $kat.'</td><td>'. $data['nama_unit'].' - '.$data['ip_address'].'</td><td align=center>'.$data['tanggal_rto'].' '.$data['jam_rto']. '</td><td align=center>'. str_replace(".000000", "",$data['lama_padam']). '</td></tr>';
		
		// isi telegram
		if($isi_telegram<>"") $isi_telegram = $isi_telegram . "\n\n";
		
		// cek PIC
		$pic = $data[pic];
		if($pic<>"") $sisipan = " PIC nya $pic.";
		else $sisipan = ""; 
		
		$isi_telegram .= "*$nama_unit* ($ip_address)$service_id sudah DOWN selama $lama_padam sejak $tanggal_rto.$sisipan $gps";
		
		$counter++;
	}
	
	$isi_telegram = "$gre ada ".($counter-1)." layanan yang masih padam nih, yuk segera dinormalin!\n\n$isi_telegram";
	
	// tampilkan di halaman web
	$body = ' 
	<h3>Status Network Down</h3>
	<body>
	   <table border="0">
		 <tr bgcolor='.$warnaheader.'>
		  <th><font color=#FFFFFF>Kategori</font></th>
		  <th><font color=#FFFFFF>Nama</font></th>
		  <th><font color=#FFFFFF>Padam</font></th>
		  <th><font color=#FFFFFF>Durasi</font></th>
		</tr>
	 '.$isi.'
	  </table>
	';
	echo $body;
  
	// kirim telegram
	if ($menit==1 ){
		kirim_telegram($isi_telegram);
	}
}  


// ====== BEGIN / Kirim Telegram Bila Semua Nyala ======
if($menit==1){ 
	$padam_sql = "
	select count(*) jumlah_padam from data_ip where tanggal_reply is null and dipindai=1
	";
	$padam_query = mysql_query($padam_sql);
	$padam_data = mysql_fetch_array($padam_query);
	$padam_jml = $padam_data["jumlah_padam"];
	
	if($padam_jml==0) {
		$padam_telegram = "$gre Network terpantau aman semua nih! Gak ada satupun layanan yang terganggu, semoga gitu terus ya...";
		echo "<h3>Status semua Network Online</h3>";
		kirim_telegram($padam_telegram);
	}
}
 
// ===== BEGIN / Kirim Telegram Bila Ada Perubahan Status ======
$rto_tolerant = VAR_RTOTIME_TOLERANT;
$query_baru = "
	select kategori, subkategori, pic,
	nama_unit, ip_address, service_id, gps_x, gps_y, date_format(tanggal_rto,'%H:%i:%s') tanggal_rto, date_format(tanggal_reply,'%H:%i:%s') tanggal_reply, status, cast( timediff(tanggal_reply,tanggal_rto)
	 as char) lama_padam from data_ip where 
	 dipindai=1 and
	(tanggal_rto>tanggal_email and
	tanggal_reply is null and
	timediff(now(),tanggal_rto) > '00:$rto_tolerant:00') 
	or
	(tanggal_reply>tanggal_email and
	tanggal_rto<tanggal_email ) 
	order by subkategori, nama_unit
";
$hasil_baru = mysql_query($query_baru);
$jumlah_baru = mysql_num_rows($hasil_baru);  

if ($jumlah_baru > 0 && $menit<>1) {
	$rto_tolerant = VAR_RTOTIME_TOLERANT;
	$query_email = "
		update data_ip set tanggal_email='$tgljam' where 
		(tanggal_rto>tanggal_email and
		tanggal_reply is null and
		timediff(now(),tanggal_rto) > '00:$rto_tolerant:00') 
		or
		(tanggal_reply>tanggal_email and
		tanggal_rto<tanggal_email )
		order by kategori, nama_unit
	";
	$hasil_email = mysql_query($query_email);

	$isi1="";
	$isi2="";
	$i=0;
	$warnaheader="#7CFC00";
	$warnaheader2="#0000FF";
	$warnagenap="#E0FFFF";
	$warnaganjil="#FOF8FF";
	$counter=1;
	$subkategori =-1;

	for ($i=0; $i<$jumlah_baru; $i++) {
		$data = mysql_fetch_array($hasil_baru);
		
		$tanggal_rto = $data['tanggal_rto'];
		$tanggal_reply = $data['tanggal_reply'];
		$lama_padam = $data['lama_padam'];
		$pic = $data[pic];
		$status = $data[status];
		$nama_unit = $data[nama_unit];
		$ip_address = $data[ip_address];
		$service_id = $data[service_id];
		$kat = $data[kategori];
		$gps_x = $data[gps_x];
		$gps_y = $data[gps_y];
		
		if($counter % 2==0) $warna=$warnagenap;
		else $warna=$warnaganjil;
		
		if($subkategori<>$data['subkategori']){
			$subkategori = $data['subkategori'];
			$isi1 .= "<tr><th bgcolor='.$warnaheader2.' colspan=2 align=center><font color=#FFFFFF>$subkategori</font></th></tr>";
		}

		// isi ketstatus
		if($data['status']=="online"){
		   $ketstatus="Down - ".$data['tanggal_rto']."<br>Up - ".$data['tanggal_reply']."<br>Rectime - ".$data['lama_padam'];
		   
		}
		else {
		   $ketstatus="Down - ".$data['tanggal_rto'];
		   
		}
		
		// cek pic / pic
		if($pic<>"") $sisipan = " PIC nya $pic,";
		else $sisipan = "";
		
		// cek sid
		if($service_id=="") $sid = "";
		else $sid = " SID $service_id";
		
		// cek GPS
		if($gps_x<>"" && $gps_y<>"") $gps = "[Lihat Lokasi.](http://maps.google.com/maps?q=$gps_y,$gps_x)";
		else $gps = "";
		
		// telegram awal
		if($isi_telegram2<>"") $isi_telegram2 = $isi_telegram2 ."\n\n";
		else {
			$isi_telegram2 = "$gre Ada info terbaru nih...\n\n";
		}
		
		// telegram isi
		if($status=="online"){
			$isi_telegram2 =$isi_telegram2."$nama_unit ($ip_address) barusan dah *UP* jam $tanggal_reply, recovery time nya $lama_padam";
		}
		else{
			$isi_telegram2 =$isi_telegram2."$nama_unit ($ip_address)$sid barusan aja *DOWN* jam $tanggal_rto,$sisipan buruan dicek yah! $gps";
		}
		
		// isi body web
		if ($data['status']=="online"){
			$isi1=$isi1.'<tr  bgcolor="#00B953"><td width="50%"><font color=#FFFFFF>'.$data['nama_unit'].' - '. $data['ip_address']. '</font></td><td><font color=#FFFFFF>'. $ketstatus.'</font></td></tr>';
		} 
		else{
			$isi1=$isi1.'<tr  bgcolor="#FF0000"><td width="50%"><font color=#FFFFFF>'.$data['nama_unit'].' - '. $data['ip_address']. '</font></td><td><font color=#FFFFFF>'. $ketstatus.'</font></td></tr>';
		}    
	
		$counter++;
	}
	
	// tampilkan di halaman web
	$body = '  
		<h3>Perubahan Status Network</h3>
		<table border="0">
		<tr bgcolor='.$warnaheader.'>
		  <th><font color=#FFFFFF>Nama</font></th>
		  <th><font color=#FFFFFF>Kategori</font></th>
		</tr>
		'.$isi1.'
		</table>
		</body>
		</html>
	';	
	echo $body;
	kirim_telegram($isi_telegram2);
}  
 
?>
