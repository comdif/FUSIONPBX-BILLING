<?php
###############  2023 Billing software - Christian Zeler@Comdif Innovation  ###############
include 'function.php';
include 'cfg.php';
echo'<!DOCTYPE html>
	<head><link rel="stylesheet" type="text/css" href="css/main.css"></head><body>';

$SESSID = "sess_".$_COOKIE['PHPSESSID'];
$ISadm=shell_exec("cat /var/lib/php/sessions/".$SESSID."|grep \"".$adminusername."\"|grep \"".$superadmingrpname."\"");

if (empty($ISadm))
	{
	echo'<br/><table class="rgrey" align="center">
		<tr><td>
		<div style="text-align:center;margin-top:50px;">
		You must first log in as administrator to your 
		<a href="https://'.$_SERVER['HTTP_HOST'].'">FUSIONPBX</a> interface before you can access here</br>
		</div>';
	exit;
	}
else
	{
	echo'<center><ul class="spe">
	<li class="spe"><a href="index.php">MAIN</a></li>
	<li class="spe"><a href="new_rates.php">RATES</a></li>
	<li class="spe"><a href="importsql.php">RATES IMPORT</a></li>
	<li class="spe"><a href=""></a></li>
	</ul></center>
	<br/>
<table class="rgrey" align="center">
<tr><td>';
	include 'mydbconnect.php';
	}
?>
