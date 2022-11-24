<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_item_menu = $_REQUEST['cod_item_menu']; 
$cod_usuario = session::get("COD_USUARIO");	// viene del login
$priv = w_base::get_privilegio_opcion_usuario($cod_item_menu, $cod_usuario);
print $priv;
?>