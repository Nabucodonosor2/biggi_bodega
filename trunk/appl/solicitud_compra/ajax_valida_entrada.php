<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_orden_compra_s = explode(',', $_REQUEST['cod_orden_compra_s']);
$db 				= new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$existe_entrada		= '';
$error				= 0;

for($i= 0 ; $i < count($cod_orden_compra_s) ; $i++){
	
	$sql = "SELECT OC.COD_ORDEN_COMPRA
	  			  ,IOC.COD_ITEM_DOC 
			FROM ENTRADA_BODEGA EB
				,ORDEN_COMPRA OC
				,SOLICITUD_COMPRA SC
				,ITEM_ORDEN_COMPRA IOC
			WHERE OC.COD_ORDEN_COMPRA = $cod_orden_compra_s[$i]
			AND OC.COD_ORDEN_COMPRA = EB.COD_DOC
			AND SC.COD_SOLICITUD_COMPRA = OC.COD_DOC
			AND OC.COD_ORDEN_COMPRA = IOC.COD_ORDEN_COMPRA"; 
	
	$result = $db->build_results($sql);
	
	if(count($result) <> 0){
		
		$sql_armador = "SELECT ARMA_COMPUESTO
						FROM ITEM_SOLICITUD_COMPRA
						WHERE COD_ITEM_SOLICITUD_COMPRA =".$result[0]['COD_ITEM_DOC'];
		
		$result_armador = $db->build_results($sql_armador);
		
		if($result_armador[0]['ARMA_COMPUESTO'] == 'S')
			$error = 1;
		
		$existe_entrada .= $result[0]['COD_ORDEN_COMPRA'].', ';
	}	
}

if($error <> 1){
	if($existe_entrada == '')
		print 'NO_ENTRADA';
	else
		print trim($existe_entrada,', ');
}else
	print 'ALERTA';			

?>