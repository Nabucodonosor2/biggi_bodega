<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$incluye_no_vigente = $_REQUEST['INCLUYE_NO_VIGENTE'];
$temp = new Template_appl(session::get('K_ROOT_DIR').'html/dlg_find_vendedor.htm');
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
if($incluye_no_vigente =='S'){


$sql = "select NOM_USUARIO
		  from USUARIO
		 where VENDEDOR_VISIBLE_FILTRO in (1,2)";
$result = $db->build_results($sql);
$nom_usuario = $result_correo[0]['NOM_USUARIO'];
}
else{
$sql = "select NOM_USUARIO
		  from USUARIO
		 where VENDEDOR_VISIBLE_FILTRO = 1";
$result = $db->build_results($sql);	
$nom_usuario = $result_correo[0]['NOM_USUARIO'];	
}

$temp->setVar("VALOR", $nom_usuario);

print urlencode(json_encode($result));
?>
