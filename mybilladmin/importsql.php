<?php
###############  2023 Billing software - Christian Zeler@Comdif Innovation  ###############
require ("header.php");
?>
<form method="post" enctype="multipart/form-data" action="importsql.php"> 
<table width="100%" border="0" align="center" cellpadding="5" cellspacing="0" bgcolor="#eeeeee"> 
	<tr>
	<td width="500"><font size=3><b>
	File.csv  format: (pattern;name;trunk;connectcost;includedsec;purchase;sale)</b> minimal is pattern;name;;;;purchase;sale</font><br/>
	<font color="#FF0000"><strong>Prices are in cents/100, eg for 11 cents enter 1100 and Never use comma !</strong></font></td> 
	<td width="244" align="right"><input type="file" name="userfile" value="userfile"></td> 
	<td width="137" align="left"><input type="submit" value="Send" name="envoyer"></td> 
	</tr> 
</table> 
</form>
<table width="644" border="1" align="center" cellpadding="2" cellspacing="0" bgcolor="#eeeeee">
<tr>
<td></td>
  <td>Prefix</td>
  <td>Name</td>
  <td>Trunk</td>
  <td>Connect cost</td>
  <td width="114">Included seconds</td>
  <td width="94">Purchase price</td>
  <td width="76">Sale price</td>
 </tr>
<?php
$fichier=$_FILES["userfile"]["name"];
if ($fichier !='')
	{
	$allowed_ext = 'csv';
	$file_ext = strtolower(pathinfo($_FILES["userfile"]["name"], PATHINFO_EXTENSION));
	if ($file_ext !== $allowed_ext)
		{
    	die("Invalid file type. Only .csv files are allowed.");
		}

	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime = finfo_file($finfo, $_FILES["userfile"]["tmp_name"]);
	finfo_close($finfo);
	$allowed_mimes = ['text/plain', 'text/csv', 'application/vnd.ms-excel'];
	if (!in_array($mime, $allowed_mimes))
		{
    	die("Invalid file format. Only CSV text files are allowed.");
		}		
	$fp = fopen ($_FILES["userfile"]["tmp_name"], "r");
	$empty = "TRUNCATE TABLE `v_routes`";
	$done = pg_query($dbcon,$empty);
	}
else{
	exit();
	}
$cpt=0;
echo "<p align=\"center\">Successful</p>";
while (!feof($fp))
	{
	$ligne = fgets($fp,4096);
	$liste = explode(";",$ligne);
	$liste[0] = ( isset($liste[0]) ) ? $liste[0] : Null;
	$liste[1] = ( isset($liste[1]) ) ? $liste[1] : Null;
	$liste[2] = ( isset($liste[2]) ) ? $liste[2] : Null;
	$liste[3] = ( isset($liste[3]) ) ? $liste[3] : Null;
	$liste[4] = ( isset($liste[4]) ) ? $liste[4] : Null;
	$liste[5] = ( isset($liste[5]) ) ? $liste[5] : Null;
	$liste[6] = ( isset($liste[6]) ) ? $liste[6] : Null;
	$champs0=$liste[0]; // Prefix
	$champs1=$liste[1]; // Name
	$champs2=$liste[2]; // Trunks
	$champs3=$liste[3]; // Concost
	$champs4=$liste[4]; // Incsec
	$champs5=$liste[5]; // Purchase
	$champs6=$liste[6]; // Sale
	if ($champs1!='')
		{
		$cpt++;
		$sql= "INSERT INTO v_routes (pattern,comment,trunks,connectcost,includedseconds,ek,cost) 
		VALUES('$champs0','$champs1','$champs2','$champs3','$champs4','$champs5','$champs6') ";
		$requete = pg_query($dbcon,$sql);
		echo'<tr>
		<td width="62">Eléments importés :</td>
		<td width="54">'.$champs0.'</td>
		<td width="56">'.$champs1.'</td>
		<td width="55">'.$champs2.'</td>
		<td width="83">'.$champs3.'</td>
		<td width="56">'.$champs4.'</td>
		<td width="55">'.$champs5.'</td>
		<td width="83">'.$champs6.'</td>
		</tr>';
		}
	}
fclose($fp); unset($fichier);
echo "</table>";
require("footer.php");
?>
