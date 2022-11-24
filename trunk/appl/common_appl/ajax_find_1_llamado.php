<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_llamado = $_REQUEST['cod_llamado'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql="SELECT  COD_LLAMADO, 
        LL.COD_CONTACTO_PERSONA,
			convert(varchar(10),LL.FECHA_LLAMADO,103)  FECHA_LLAMADO,
			LLA.NOM_LLAMADO_ACCION,
			C.NOM_CONTACTO NOM_CONTACTO,
			CT.TELEFONO TELEFONO_CONTACTO,
			CPT.TELEFONO TELEFONO_PERSONA,
			CP.NOM_PERSONA,
			LL.MENSAJE
	 FROM LLAMADO LL
		  ,CONTACTO C left outer join CONTACTO_TELEFONO CT ON C.COD_CONTACTO = CT.COD_CONTACTO
		  ,CONTACTO_PERSONA	 CP left outer join CONTACTO_PERSONA_TELEFONO CPT ON CP.COD_CONTACTO_PERSONA = CPT.COD_CONTACTO_PERSONA
		  ,LLAMADO_ACCION LLA
	 WHERE LL.COD_LLAMADO = $cod_llamado
		and LL.COD_CONTACTO_PERSONA = CP.COD_CONTACTO_PERSONA
		AND CP.COD_CONTACTO = C.COD_CONTACTO
		AND LL.COD_CONTACTO_PERSONA = CPT.COD_CONTACTO_PERSONA
		AND LLA.COD_LLAMADO_ACCION = LL.COD_LLAMADO_ACCION";
		
	
$result = $db->build_results($sql);
$row_count = $db->count_rows();
if ($row_count == 0){
	$respuesta = "NO_EXISTE";
	print $respuesta;
}
else{
	print urlencode(json_encode($result));
}
?>