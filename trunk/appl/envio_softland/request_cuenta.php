<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('request_cuenta.htm');	

$sql = "select COD_CUENTA_COMPRA, NOM_CUENTA_COMPRA from CUENTA_COMPRA order by NOM_CUENTA_COMPRA";
$cod_cuenta_compra = new drop_down_dw('COD_CUENTA_COMPRA', $sql);
$htm = $cod_cuenta_compra->draw_entrable('', 0); 
$temp->setVar("COD_CUENTA_COMPRA", $htm);

print $temp->toString();
?>