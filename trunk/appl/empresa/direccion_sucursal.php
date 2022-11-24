<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_sucursal = $_REQUEST['cod_sucursal'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select	dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION
				from		SUCURSAL
				where		COD_SUCURSAL = ".$cod_sucursal;	
$result = $db-> build_results($sql);
print urlencode($result[0]['DIRECCION']);
?>