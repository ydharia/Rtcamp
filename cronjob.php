<?php
session_start();

$postdata = array('nodata' => "");
$curl = curl_init("https://rtcampyash.myfoodstore.in/example/cronMoveAlbums");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
$result = curl_exec($curl);
/*
$list = json_decode($result, true);

//echo "<pre>";

$postdata = array("albumId" => $list["id"][$pos], "albumName"=>$list["name"][$pos]);
//print_r($postdata);

$myfile = fopen("cron/albumPosition.txt", "w") or die("Unable to open file!");
$pos = "".++$pos;
fwrite($myfile, $pos);
fclose($myfile);

$myfile = fopen("cronstatus.txt", "w") or die("Unable to open file!");
$pos = "corn position : ".$pos;
fwrite($myfile, $pos);
fclose($myfile);

$curl = curl_init("https://rtcampyash.myfoodstore.in/example/moveSingle");
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_POSTFIELDS, $postdata);
$result = curl_exec($curl);
//echo "<pre>";
//print_r($result);

if(count($list["id"])-1 <= $pos)
{
	unlink("cron/albumPosition.txt");
	unlink("cron/session.txt");
}

*/

?>