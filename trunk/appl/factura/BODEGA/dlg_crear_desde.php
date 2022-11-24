<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('dlg_crear_desde.htm');
$temp->setVar("DESDE_TODOINOX", '<input id="DESDE_TODOINOX" type="radio" name="group" value="BODEGA" > Desde Todoinox');
$temp->setVar("DESDE_COMERCIAL", '<input id="DESDE_COMERCIAL" type="radio" name="group" value="BODEGA" checked> Desde Comercial');
$temp->setVar("DESDE_RENTAL", '<input id="DESDE_RENTAL" type="radio" name="group" value="BODEGA"> Desde Rental');
$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);

print $temp->toString();
?>