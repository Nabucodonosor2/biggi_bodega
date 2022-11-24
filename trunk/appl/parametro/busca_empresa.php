<?php
///////////////////////
// php llamado con ajax en el javascript parametro.js
////////////////////////
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_usuario = $_REQUEST['cod_usuario'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

/*$sql = "select RUT, DIG_VERIF, NOM_EMPRESA
		from EMPRESA
		where COD_EMPRESA = ".$cod_empresa;	*/	  

$sql = "select E.COD_EMPRESA, E.RUT, E.DIG_VERIF, E.NOM_EMPRESA
		from EMPRESA E, USUARIO U
		where	U.COD_EMPRESA = E.COD_EMPRESA
		and		U.COD_USUARIO = ".$cod_usuario;		


$db->query($sql);
//$count_rows = $db->count_rows();

			
$result = $db->build_results($sql);
//print $result[0]['RUT'];

$RUT = $result[0]['RUT'];
$DIG_VERIF = $result[0]['DIG_VERIF'];
$NOM_EMPRESA = $result[0]['NOM_EMPRESA'];
$COD_EMPRESA = $result[0]['COD_EMPRESA'];

print $RUT."|".$DIG_VERIF."|".$NOM_EMPRESA."|".$COD_EMPRESA;
?>
