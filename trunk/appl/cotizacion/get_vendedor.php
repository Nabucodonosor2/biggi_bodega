<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];


$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT	E.COD_USUARIO,
				U.NOM_USUARIO 
		FROM	EMPRESA E, USUARIO U 
		WHERE	E.COD_EMPRESA = $cod_empresa and
				E.COD_USUARIO = U.COD_USUARIO";
$result = $db->build_results($sql);

print $result[0]['COD_USUARIO']."|".$result[0]['NOM_USUARIO'];

?>
