<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = session::get("COD_USUARIO");	// viene del login
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select INI_USUARIO
		from USUARIO
		where COD_USUARIO = $cod_usuario";  
$result = $db->build_results($sql);
print urlencode($result[0]['INI_USUARIO']);
?>