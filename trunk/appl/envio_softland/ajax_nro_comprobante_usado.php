<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$K_ENVIO_CONFIRMADO = 2;

$nro_comprobante = $_REQUEST['nro_comprobante'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
// envio FA de venta
$sql = "select top 1 EF.COD_ENVIO_SOFTLAND
		from ENVIO_FACTURA EF, ENVIO_SOFTLAND ES 
		where EF.NRO_COMPROBANTE = $nro_comprobante
		  and ES.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND
		  and ES.COD_ESTADO_ENVIO = $K_ENVIO_CONFIRMADO  
		  and year(ES.FECHA_ENVIO_SOFTLAND) = year(getdate())";  
$result = $db->build_results($sql);
if (count($result)==0) {
	// envio NC de venta
	$sql = "select top 1 EN.COD_ENVIO_SOFTLAND
			from ENVIO_NOTA_CREDITO EN, ENVIO_SOFTLAND ES
			where EN.NRO_COMPROBANTE = $nro_comprobante
		  	  and ES.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND
		  	  and ES.COD_ESTADO_ENVIO = $K_ENVIO_CONFIRMADO  
		  	  and year(ES.FECHA_ENVIO_SOFTLAND) = year(getdate())";  
	$result = $db->build_results($sql);
}
if (count($result)==0) {
	// envio FA de compras
	$sql = "select top 1 EF.COD_ENVIO_SOFTLAND
			from ENVIO_FAPROV EF, ENVIO_SOFTLAND ES 
			where EF.NRO_COMPROBANTE = $nro_comprobante
			  and ES.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND
			  and ES.COD_ESTADO_ENVIO = $K_ENVIO_CONFIRMADO  
		  	  and year(ES.FECHA_ENVIO_SOFTLAND) = year(getdate())";  
	$result = $db->build_results($sql);
}
if (count($result)==0) {
	// envio NC de compra
	$sql = "select top 1 EN.COD_ENVIO_SOFTLAND
			from ENVIO_NCPROV EN, ENVIO_SOFTLAND ES
			where EN.NRO_COMPROBANTE = $nro_comprobante
		  	  and ES.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND
		  	  and ES.COD_ESTADO_ENVIO = $K_ENVIO_CONFIRMADO  
		  	  and year(ES.FECHA_ENVIO_SOFTLAND) = year(getdate())";  
	$result = $db->build_results($sql);
}
if (count($result)==0)
	$cod_envio_softland = 0;
else
	$cod_envio_softland = $result[0]['COD_ENVIO_SOFTLAND'];
print urlencode($cod_envio_softland);
?>