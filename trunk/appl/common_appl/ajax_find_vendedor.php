<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$vigente = $_REQUEST["vigente"];

$resultado = "";
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

if ($vigente == 'NO_VIGENTE'){ //devuelve vigente y no vigente
	$sql = "SELECT U.COD_USUARIO,U.NOM_USUARIO 
			  FROM USUARIO U 
			 WHERE U.VENDEDOR_VISIBLE_FILTRO =1
			 or U.VENDEDOR_VISIBLE_FILTRO=2 
		  ORDER BY NOM_USUARIO ASC";
	
}else if ($vigente == 'VIGENTE'){//devuelve solo vigente
	$sql = "SELECT U.COD_USUARIO, U.NOM_USUARIO 
			  FROM USUARIO U 
			 WHERE U.VENDEDOR_VISIBLE_FILTRO = 1 
		  ORDER BY NOM_USUARIO ASC";
}

$result = $db->build_results($sql);

for ($i=0; $i<count($result); $i++) {
$result[$i]['COD_USUARIO'] = urlencode($result[$i]['COD_USUARIO']);
$result[$i]['NOM_USUARIO'] = urlencode($result[$i]['NOM_USUARIO']);
}
print urlencode(json_encode($result));
?>