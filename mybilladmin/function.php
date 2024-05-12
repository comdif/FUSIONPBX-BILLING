<?php
function cleany($string)
	{ 
	return 
	preg_replace( array('#[\\s-]+#', '#[^A-Za-z0-9\. -]+#'), array('-', ''), urldecode($string) );
	}
function translate($sKey)
	{
	}
function printFormTextToolTip($title, $name, $value="", $tooltiptext, $parameter="", $onerow=1)
	{
	echo'<tr><th class="txt" width="40%" onmouseover="return escape(\''.$tooltiptext.'\')">'.$title.'</th>';
	if($onerow==0){ echo'</tr><tr>'; }
	echo'<td width="60%"><input type="text" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$parameter.'></td></tr>';
	}
function printFormText($title, $name, $value="", $parameter="", $onerow=1)
	{
	echo'<tr><th class="txt" width="40%">'.$title.'</th>';
	if($onerow==0) { echo'</tr><tr>'; }
	echo'<td width="60%"><input type="text" name="'.$name.'" value="'.htmlspecialchars($value).'" '.$parameter.'></td></tr>';
	}
?>
