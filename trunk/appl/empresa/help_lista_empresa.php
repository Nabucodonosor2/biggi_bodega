<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_help_empresa.php");

$sql = $_REQUEST['sql'];
$sql = str_replace("\\'", "'", $sql);		// Las comillas simples ', vuelven como \'

help_empresa::draw_htm_lista_empresa($sql);
?>