<?php
###############  2023 Billing software - Christian Zeler@Comdif Innovation  ###############
$host = exec("cat /etc/fusionpbx/config.conf |grep host |awk -F \" = \" '{print $2}'");
$port = exec("cat /etc/fusionpbx/config.conf |grep 0.port |awk -F \" = \" '{print $2}'");
$name = exec("cat /etc/fusionpbx/config.conf |grep 0.name |awk -F \" = \" '{print $2}'");
$username = exec("cat /etc/fusionpbx/config.conf |grep username |awk -F \" = \" '{print $2}'");
$password = exec("cat /etc/fusionpbx/config.conf |grep password |awk -F \" = \" '{print $2}'");
$conn_string = "host=".$host." port=".$port." dbname=".$name." user=".$username." password=".$password."";
$dbcon = pg_connect($conn_string) or die ("Connect error");
?>
