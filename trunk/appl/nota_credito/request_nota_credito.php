<?php

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$prompt = $_REQUEST['prompt'];
$valor =  $_REQUEST['valor'];
$temp = new Template_appl('request_nota_credito.htm');	
$temp->setVar("PROMPT", $prompt);
$temp->setVar("VALOR", $valor);

$sql="SELECT '' COD_TIPO_NC_INTERNO_SII";

$dw = new datawindow($sql);

$sql="SELECT COD_TIPO_NC_INTERNO_SII
	  		,NOM_TIPO_NC_INTERNO_SII 
	  FROM TIPO_NC_INTERNO_SII";

$dw->add_control(new drop_down_dw('COD_TIPO_NC_INTERNO_SII' ,$sql));
$dw->retrieve();
	
$dw->habilitar($temp, true);
print $temp->toString();
?>