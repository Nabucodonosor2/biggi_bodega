<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];
$temp = new Template_appl('dlg_print_nota_venta.htm');	
$temp->setVar("COD_NOTA_VENTA", $cod_nota_venta);

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT  COD_NOTA_VENTA       
				FROM    NOTA_VENTA
				WHERE COD_NOTA_VENTA = ".$cod_nota_venta;
			
$result = $db->build_results($sql);		
print $temp->toString();
?>