<?php
###############  2023 Billing software - Christian Zeler@Comdif Innovation  ###############
include 'header.php';
$test = pg_query($dbcon, "SELECT * FROM v_routes");
if (!$test)
	{
	echo "<center>Table v_routes dont exist in DB. &nbsp;&nbsp;";
	echo'<form action="" method="post"><button name="ctable" value="ctable">Create table</button></form>';
	if(!empty($_POST['ctable']))
		{
		echo "Table v_routes is created reload page and use RATES IMPORT to add rates</br>";
		pg_query($dbcon, "CREATE TABLE v_routes (
		pattern varchar(10) NOT NULL PRIMARY KEY,
		comment text,
		trunks text,
		connectcost numeric,
		includedseconds numeric,
		ek numeric,
		cost numeric,
		opcc numeric,
		opsecinc numeric,
		oppal numeric
		);");
		pg_query($dbcon, "CREATE TABLE v_free (
		pattern varchar(10) NOT NULL PRIMARY KEY,
		comment text
		);");
		echo'</center>';
		}
	exit;
	}
$test2 = pg_fetch_row($test);
if (!$test2)
	{
	echo "Table a_routes exist in DB but is empty Use <a href=\"importsql.php\">RATES IMPORT</a> to import CSV ";
	}
$do = pg_query($dbcon, "SELECT domain_name FROM v_domains WHERE domain_enabled = 'true'");
echo'<table align="center"><tr><td class="aro"><form name="count" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
echo'&nbsp;&nbsp;<select name="mes" onchange="forms.count.submit()">';
while ($dom = pg_fetch_row($do))
	{
	echo'<OPTION VALUE="">Please Select Account</OPTION>';
	echo'<OPTION VALUE="'.$dom[0].'">'.$dom[0].'</OPTION>';
	}
echo '</SELECT>&nbsp;&nbsp;';
echo'</form></td></tr></table><br/>';

if(isset($_POST['mes']))
	{
	$dmn = $_POST['mes'];
	if(!isset($_POST['mois'])){ $_POST['mois'] = date("Y-m",strtotime("-0 month"));}
	$result = pg_query($dbcon, "SELECT domain_name,direction,caller_destination,destination_number,start_stamp,answer_stamp,end_stamp,duration,
	billsec FROM v_xml_cdr WHERE domain_name = '".$dmn."' AND destination_number != '' AND billsec > 0 
	AND direction = 'outbound' AND caller_destination = destination_number 
	AND CAST(answer_stamp AS TEXT) LIKE '".$_POST['mois']."%' ORDER BY answer_stamp") or die(pg_last_error());
	echo'<center><table class="ryel">';
	//////////////////////////////// SELECT MONTH ////////////////////////////////
	echo'<tr><td class="aro"><strong>ACCOUNT: '.$_POST['mes'].'</strong></td><td colspan="5" class="aro"><form name="sm" action="'.$_SERVER['PHP_SELF'].'" method="POST">';
	echo'<input type="hidden" name="mes" value="'.$_POST['mes'].'" />
		<select name="mois" onchange="forms.sm.submit()">';
	echo'<OPTION VALUE="">Please Select Month</OPTION>';
	for($i=0;$i<13;$i++)
		{
		echo'<OPTION VALUE="'.date("Y-m",strtotime("-$i month")).'">'.date("Y-m",strtotime("-$i month")).'</OPTION>';
		}
	echo '</SELECT></td><td class="aro"><strong>MONTH: '.$_POST['mois'].'</strong>';
	echo'</td></tr>';
//////////////////////////////// RESULT ////////////////////////////////
	echo'<tr>
		<td class="ar">Client</td>
		<td class="ar">Date</td>
		<td class="ar">Destination</td>
		<td class="ar">Secondes</td>
		<td class="ar">Destination</td>
		<td class="ar">Tarif/min</td>
		<td class="ar">Prix</td></tr>';
		$TOTAL = 0; $nbap = 0;
	while ($row = pg_fetch_array($result))
		{
		$numbe = $row['destination_number'];
		if($numbe[0] == 0 && $numbe[1] != 0)
			{
			$numbe = ltrim($numbe, '0'); $numbe = '33'.$numbe;
			}
		if($numbe[0] == 0 && $numbe[1] == 0)
			{
			$numbe = ltrim($numbe, '0'); $numbe = ltrim($numbe, '0');
			}
		if(strlen($numbe) == 4)
			{
			$numbe = '333'.$numbe;
			//echo"<tr><td>".$row['domain_name']."</td><td>".$row['start_stamp']."</td><td>".$numbe."</td><td>".$row['billsec']."</td></tr>";
			}
			$resultat = pg_query($dbcon,"SELECT pattern,comment,connectcost,includedseconds,ek,cost FROM v_routes 
			WHERE '".$numbe."' LIKE \"pattern\" || '%' ORDER BY pattern DESC LIMIT 1") or die(pg_last_error());

			$resu = pg_fetch_array($resultat);
			$euro = ($resu['cost'] / 10000);
			//$bill = (ceil((($euro / 60)* $row['billsec']) * 10) / 100 );
			$bill = (ceil((($euro / 60) * $row['billsec']) * 1000) /1000);
			$Rbill = (($resu['cost'] / 60) * $row['billsec']);
			
  		echo "<tr>
			<td class='aro'>".$row['domain_name']."</td>
			<td class='aro'>".$row['answer_stamp']."</td>
			<td class='aro'>".$numbe."</td>
			<td class='aro'>".$row['billsec']."</td>
			<td class='aro'>".$resu['comment']."</td>
			<td class='aro'>".$euro." &euro;</td>
			<td class='aro'>".$bill." Cents</td></tr>";
			$TOTAL =$TOTAL + $bill; $nbap = ($nbap + 1 );
		}
	echo'<tr><td colspan="5"></td><td class="aro">Appels: '.$nbap.'</td><td class="aro">Total: '.(ceil($TOTAL * 100) / 100).'</td></tr>';
	}
include 'footer.php';
?>
