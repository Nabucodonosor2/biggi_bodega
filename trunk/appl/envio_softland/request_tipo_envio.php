<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('request_tipo_envio.htm');	

$cod_orden_compra = new edit_num('COD_ORDEN_COMPRA');
$hrmtl = $cod_orden_compra->draw_entrable('', 0); 
$temp->setVar("COD_ORDEN_COMPRA", $hrmtl);

print $temp->toString();
?>