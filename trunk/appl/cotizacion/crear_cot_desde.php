<?php

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_cot = $_REQUEST['cod_cot'];

$sql = "SELECT COUNT(*) FROM COTIZACION WHERE COD_COTIZACION = $cod_cot";
$result = $db->build_results($sql);
	if (count($result)>0) {
		print 1;
		return;
	}
	
$temp = new template("que_usa.htm")

/**
  
 for 1.. cant item
	if precioo <> precio BD
		$temp->next('')
		$temp->setVar(ITEm.COD_PROD, $cod_prod)	
*/

?>