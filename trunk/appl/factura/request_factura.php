<?php

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('request_factura.htm');	
$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);

print $temp->toString();
?>