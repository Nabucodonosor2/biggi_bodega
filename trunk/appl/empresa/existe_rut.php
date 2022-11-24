<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$rut = $_REQUEST['rut'];
$cod_empresa = $_REQUEST['cod_empresa'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "SELECT count(*) CUANTO
			 	FROM empresa 
			 	WHERE RUT = ".$rut;		  
$result = $db->build_results($sql);

if ($result[0]['CUANTO']== 0){
	print urlencode(0);
}
Else{
	// SI ENCONTRO SOLO UNA VEZ EL RUT VALIDA SI PERTENECE A LA EMPRESA QUE SE ESTA MODIFICANDO
	if($result[0]['CUANTO']== 1){
		$sql = "SELECT COD_EMPRESA
			 	FROM empresa 
			 	WHERE RUT = ".$rut;		  
		$result = $db->build_results($sql);
		// EL RUT PERTENECE A LA EMPRESA QUE SE ESTA MODIFICANDO
		if($result[0]['COD_EMPRESA']== $cod_empresa){
			print urlencode(1);									
		}
		// EL RUT NO PERTENECE A LA EMPRESA QUE SE ESTA MODIFICANDO
		else{
			print urlencode(2);		
		}	
	}
	// ENTRA AL ELSE SOLO SI ENCONTRO MAS DE UNA VEZ EL RUT LO CUAL ESTA MALO PUES EL RUT ES UNICO
	else{
		print urlencode(3);		
	}
}

?>
	