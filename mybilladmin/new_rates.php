<?php
###############  2023 Billing software - Christian Zeler@Comdif Innovation  ###############
require ("header.php");
if(isset($_GET['action']) && $_GET['action'] == "add")
	{
	if($_POST['button'] == "store")
		{
		pg_query($dbcon,"INSERT INTO v_routes VALUES ('".$_POST['pat']."','".$_POST['com']."','','".$_POST['cco']."','".$_POST['inc']."',
		'".$_POST['eka']."','".$_POST['costa']."','0','0','0')") or die(pg_last_error());
		echo'<center>OK Done</center>';
		echo"<SCRIPT LANGUAGE='JavaScript'>window.location.replace('".$_SERVER['PHP_SELF']."?action=add&pat=".$_POST['pat']."')</script>";
		}
	echo'<div class="headline_global">Rates and routing</div><br />
		<p align="center"><font color="#CC0000"><strong>Prices are in cents/100, eg for 11 cents enter 1100</br>Never use comma !</strong>
		</font></p>';
	echo'<form name="new_rates_frm" action="'.$_SERVER['PHP_SELF'].'?action=add" method="POST">
		<input type="hidden" name="action" value="add" />
		<input type="hidden" name="button" value="store" />
		<table class="rates_tbl" align="center">';
	echo'<tr><td>Prefix</td><td><input type="text" name="pat" value="" /></td></tr>';
	echo'<tr><td>Destination</td><td><input type="text" name="com" value="" /></td></tr>';
	echo'<tr><td>Purchase</td><td><input type="text" name="eka" value="" /></td></tr>';
	echo'<tr><td>Sale</td><td><input type="text" name="costa" value="" /></td></tr>';
	echo'<tr><td>Connect cost</td><td><input type="text" name="cco" value="" /></td></tr>';
	echo'<tr><td>Included seconds</td><td><input type="text" name="inc" value="" /></td></tr>';
	echo'<input type="hidden" name="action" value="add" />';
	echo'<tr><td class="gapright" colspan="2"><input type="submit" value="Create" /></td></tr></table></form>';
	echo"<div align='center'>Return: <a class='big_links' href='".$_SERVER['PHP_SELF']."'> Rate list</a></div>";		
	}
////////////////////////////////////// ########################## //////////////////////////////////////		
if(isset($_GET['action']) && $_GET['action'] == "edit")
	{				
	if($_POST['button'] == "store")
  		{
		pg_query($dbcon,"UPDATE v_routes SET pattern='".$_POST['pat']."',trunks='',comment='".$_POST['com']."',ek='".$_POST['eka']."',
		cost='".$_POST['costa']."',connectcost='".$_POST['cco']."',includedseconds='".$_POST['inc']."' 
		WHERE CAST(pattern AS TEXT)='".$_POST['pat']."'") or die(pg_last_error());
		echo'<center>OK Done</center>';
		echo"<SCRIPT LANGUAGE='JavaScript'>window.location.replace('".$_SERVER['PHP_SELF']."?action=edit&pat=".$_POST['pat']."')</script>";
  		}
	if(!empty($_GET['pat'])){ $iPattern = $_GET['pat']; }
	$aRate = pg_query($dbcon,"SELECT * FROM v_routes WHERE CAST(pattern AS TEXT)='".$iPattern."' LIMIT 1") or die(pg_last_error());
	while($rowa= pg_fetch_array($aRate))
		{
		echo'<div class="headline_global">Rates and routing</div><br /><p align="center">
		<font color="#CC0000"><strong>Prices are in cents/100, eg for 11 cents enter 1100</br>Never use comma !</strong></font></p><p align="center">
		<font color="#330000" >Actual price for this destination '.$rowa['pattern'].' - '.$rowa['comment'].' is:<br />Purchase: 
		<font color="#0000FF"><strong>'.($rowa['ek']/100).'</font></strong> Cents, Retail: <font color="#0000FF"><strong>'.($rowa['cost']/100).'</font>
		</strong> Cents, Connect cost: <font color="#0000FF"><strong>'.($rowa['connectcost']/100).'</font></strong> Cents, Included seconds: 
		<font color="#0000FF"><strong>'.$rowa['includedseconds'].'</font></strong> Seconds</font>';
		echo'<form name="new_rates_frm" action="'.$_SERVER['PHP_SELF'].'?action=edit" method="POST">
		<input type="hidden" name="button" value="store" /><table class="rates_tbl" align="center">';
		echo'<tr><td>Prefix</td><td><input type="text" name="pat" value="'.$rowa['pattern'].'" /></td></tr>';
		echo'<tr><td>Destination</td><td><input type="text" name="com" value="'.$rowa['comment'].'" /></td></tr>';
		echo'<tr><td>Purchase</td><td><input type="text" name="eka" value="'.$rowa['ek'].'" /></td></tr>';
		echo'<tr><td>Sale</td><td><input type="text" name="costa" value="'.$rowa['cost'].'" /></td></tr>';
		echo'<tr><td>Connect cost</td><td><input type="text" name="cco" value="'.$rowa['connectcost'].'" /></td></tr>';
		echo'<tr><td>Included seconds</td><td><input type="text" name="inc" value="'.$rowa['includedseconds'].'" /></td></tr>';
		echo'<input type="hidden" name="action" value="edit" />';
		echo'<tr><td class="gapright" colspan="2"><input type="submit" value="Change" /></td></tr></table></form>';
		}
	}
////////////////////////////////////// ########################## //////////////////////////////////////
if(isset($_GET['action']) && $_GET['action'] == "del")
		{		
		$iDelRoute = pg_query($dbcon,"DELETE FROM v_routes WHERE CAST(pattern AS TEXT)='".$_GET['pat']."'") or die(pg_last_error());
		echo"<div align='center'>OK ".$_GET['pat']." deleted<br />Return: <a class='big_links' href='".$_SERVER['PHP_SELF']."?letter=".$_GET['let']."'> Link</a></div>";
	}		
##########################////////////////// ####DEFAULT#### //////////////////##########################
if(!isset($_GET['action']))
	{
	$sel = pg_query($dbcon,"SELECT comment , pattern FROM v_routes");	
	echo'</br><div align="center"><form name="Myselect" action="'.$_SERVER['PHP_SELF'].'?sort=" method="get" style="display:inline;">';
	echo'Name/Prefix <input type="texte" name="letter" /><input type="button" value="Search" OnClick="document.Myselect.submit()">
	</form><font size="1"> (Prepend with * for a strict search)</font></div><br/>';
////////////////////////////////////// ########################## //////////////////////////////////////
	if (isset($_GET['letter']))
		{
		$letter = $_GET['letter'];
		if( $letter[0] == '*' )
			{
			$sCdrsql = "SELECT comment, trunks, ek, cost, connectcost, includedseconds, pattern FROM v_routes WHERE 
			comment='".substr($letter, 1)."' OR CAST(pattern AS TEXT)='".substr($letter, 1)."' ORDER BY comment";
			}
		else
			{
			$sCdrsql="SELECT comment,trunks,ek,cost,connectcost,includedseconds,pattern FROM v_routes WHERE 
			comment ILIKE '".$letter."%' OR CAST(pattern AS TEXT) LIKE '".$letter."%' ORDER BY comment";
			}
		echo'<tr><td><table class="rgrey" align="center"><tr><td>Below Destinations in free package ?  
			<form style="display:inline;" name="freedest" action="'.$_SERVER['PHP_SELF'].'?letter='.$letter.'" method="POST">
			<input type="hidden" name="action" value="freedest" />
			<input type="hidden" name="leti" value="'.$letter.'" />
			<input type="submit" value="Include" />
			</form>&nbsp;|&nbsp;
			<form style="display:inline;" name="delfreedest" action="'.$_SERVER['PHP_SELF'].'?letter='.$letter.'" method="POST">
			<input type="hidden" name="action" value="freedest" />
			<input type="hidden" name="remfree" value="remfree" />
			<input type="hidden" name="leti" value="'.$letter.'" />
			<input type="submit" value="Remove" />
			</form></td></tr></table></td></tr></table>';
		}
	else
		{			
		echo '<strong>'.translate("srate").'</strong>';	exit();
		}
	if(isset($_POST['leti']))
		{
		if(!empty($_POST['remfree']))
			{
			if( $letter[0] == '*' )
				{
				$sCD = "DELETE FROM v_free WHERE comment='".substr($_POST['leti'], 1)."' OR CAST(pattern AS TEXT)='".substr($_POST['leti'], 1)."'";
				}
			else
				{
				$sCD = "DELETE FROM v_free WHERE comment ILIKE '".$_POST['leti']."%' OR CAST(pattern AS TEXT) LIKE '".$_POST['leti']."%'";
				}
			pg_query($dbcon,$sCD);
			}
		else
			{
			if( $letter[0] == '*' )
				{
				$seo = "SELECT pattern,comment FROM v_routes WHERE comment='".substr($_POST['leti'], 1)."' OR CAST(pattern AS TEXT)='".substr($_POST['leti'], 1)."'";
				}
			else
				{
				$seo = "SELECT pattern,comment FROM v_routes WHERE comment ILIKE '".$_POST['leti']."%' OR CAST(pattern AS TEXT) LIKE '".$_POST['leti']."%'";
				}
			$sea = pg_query($dbcon,$seo);
			while($pop = pg_fetch_array($sea))
				{
				pg_query($dbcon,"INSERT INTO v_free (pattern,comment) VALUES ('".$pop['pattern']."','".$pop['comment']."') 
				on conflict (pattern) do nothing;") or die(pg_last_error());
				}
			}
		echo"<SCRIPT LANGUAGE='JavaScript'>window.location.replace('".$_SERVER['PHP_SELF']."?letter=".$_POST['leti']."')</script>";
		}
	echo'<div class="headline_global">Prices & Destinations</div><table class="rgrey" align="center"><tr>
		<th class="small_headline">Destination</th>
		<th class="small_headline">Trunk</th>
		<th class="small_headline">Prefix</th>
		<th class="small_headline">Purchase</th>
		<th class="small_headline">Sale</th>
		<th class="small_headline">Connect cost</th>
		<th class="small_headline">Included time</th>
		<th class="small_headline"></th>
		<th class="small_headline"></th>
		<th class="small_headline"></th>		
		</tr>';
	$uno = pg_query($dbcon,$sCdrsql);
	while($res = pg_fetch_array($uno))
		{
		$iRate = number_format($res['cost']/100, 1, ",", ".");
		$iEkRate = number_format($res['ek']/100, 1, ",", ".");
		$iconnectcost = number_format($res['connectcost']/100, 0, ",", ".");
		$iincludedseconds = number_format($res['includedseconds'], 0, ",", ".");
		echo'<tr>
			<td class="border_tds">'.$res['comment'].'</td>
			<td class="border_tds">'.$res['trunks'].'</td>
			<td class="border_tds">'.$res['pattern'].'</td>
			<td class="border_tds">'.$iEkRate.' '.translate("centperminute").'</td>
			<td class="border_tds">'.$iRate.' '.translate("centperminute").'</td>
			<td class="border_tds">'.$iconnectcost.' Cents</td>
			<td class="border_tds">'.$iincludedseconds.' Seconds</td>';
		echo'<td class="border_tds"><a href="'.$_SERVER['PHP_SELF'].'?action=edit&pat='.$res['pattern'].'">';
		echo'<img src="imgs/info.gif" width="12" height="12" alt="Info/Edit" title="Info/Edit" /></a> </td><td class="border_tds"> ';
		echo'<a href="javascript:if(confirm(\''.translate("adminratesconfirmdelete").' '.$res['pattern'].'\')) document.location.href
			=\''.$_SERVER['PHP_SELF'].'?action=del&pat='.$res['pattern'].'&let='.$_GET['letter'].'\';">
			<img src="imgs/del.gif" width="12" height="12" alt="Delete" title="Delete" /></a></td><td class="border_tds">';
		$exist = pg_query($dbcon,"SELECT pattern FROM v_free WHERE CAST(pattern AS TEXT)= '".$res['pattern']."'");
		$rcnt = pg_num_rows($exist);
		if($rcnt !='0')
			{	
			echo' Free';
			}				
		echo'</td></tr>';
		}
		echo'<tr><td class="gapright" colspan="9"><a class="big_links" href=\''.$_SERVER['PHP_SELF'].'?action=add\'>Add new destination</a></td></tr></table><br/>';
	}
echo "<p align=\"center\"><a href=\"importsql.php\">Import from cvs database click here</a>";
