<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select dbo.f_get_porc_dscto_corporativo_empresa(COD_EMPRESA, getdate()) PORC_DSCTO_CORPORATIVO
		from EMPRESA
		where COD_EMPRESA = ".$cod_empresa;		  

$result = $db->build_results($sql);
print $result[0]['PORC_DSCTO_CORPORATIVO'];

?>
