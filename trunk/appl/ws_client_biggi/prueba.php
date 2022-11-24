<?php
require("class_client_biggi.php");
//192.168.2.140/desarrolladores/icampos/biggi/trunk/appl/ws_client_biggi/prueba.php
//$biggi = new client_biggi("ws_biggi", "2821", "http://192.168.2.13/desarrolladores/vmelo/biggi/trunk/appl/ws_server_biggi/server_biggi.php");
$biggi = new client_biggi("ws_biggi", "2821", "http://www.biggi.cl/sysbiggi_new/comercial_biggi/biggi/trunk/appl/ws_server_biggi/server_biggi.php");

$res = $biggi->cli_orden_compra(187581);


print_r($res);
?>