<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select 'N' SELECCCION
				,COD_ARRIENDO
				,NOM_ARRIENDO
				,REFERENCIA
				,dbo.f_arr_total_actual(COD_ARRIENDO) TOTAL
		from arriendo
		where cod_empresa = $cod_empresa
		  and dbo.f_arr_total_actual(COD_ARRIENDO) > 0
		  and dbo.f_arr_esta_facturado(COD_ARRIENDO, getdate()) = 0";
$result = $db->build_results($sql);
for($i=0; $i<count($result); $i++) {
	$result[$i]['NOM_ARRIENDO'] = urlencode($result[$i]['NOM_ARRIENDO']); 
	$result[$i]['REFERENCIA'] = urlencode($result[$i]['REFERENCIA']); 
}
print urlencode(json_encode($result));
?>