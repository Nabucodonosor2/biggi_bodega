<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_sucursal = $_REQUEST['cod_sucursal'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);			 
$sql = "SELECT SO.NAME TABLA, SYSCOLUMNS.NAME COLUMNA
				FROM SYSOBJECTS, SYSFOREIGNKEYS, SYSOBJECTS SO, SYSCOLUMNS
				WHERE 
						 SYSOBJECTS.XTYPE = 'U' AND SYSOBJECTS.NAME = 'SUCURSAL' AND 
						 RKEYID = SYSOBJECTS.ID AND SO.ID = FKEYID AND
						 SYSCOLUMNS.COLID = SYSFOREIGNKEYS.FKEY AND	
      			 SYSCOLUMNS.ID = FKEYID and
      			 SO.NAME <> 'PERSONA'";	
      			 	 			 	  
$result = $db->build_results($sql);


$encontro = 0;
for ($i = 0; $i< count($result); $i++){
	$tabla = $result[$i]['TABLA'];
	$columna = $result[$i]['COLUMNA'];
	$sql = "SELECT count(*) CUANTO FROM ".$tabla." WHERE ".$columna." = ".$cod_sucursal;	
	$resultaux = $db->build_results($sql);
	if ($resultaux[0]['CUANTO'] > 0){
		$encontro = 1;
		print urlencode($tabla);
		break;
	}	
}
if ($encontro == 0){
	print urlencode('noencontrada');
}
?>	