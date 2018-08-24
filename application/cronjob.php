<?php
session_start();
$myfile = fopen("newfile.txt", "a") or die("Unable to open file!");
$txt = date("h:i:s a")."\n";
//$txt = print_r($this->session->userdata())."\n";
fwrite($myfile, $txt);

fclose($myfile);


?>