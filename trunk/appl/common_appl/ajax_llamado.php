<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
//require_once("dlg_find_llamado.php");	//** al parecer se debe elimnar archivo

$fecha_desde = $_REQUEST['fecha_desde'];
$fecha_hasta = $_REQUEST['fecha_hasta'];
$empresa = $_REQUEST['empresa'];
$rut = $_REQUEST['rut'];
$contacto = $_REQUEST['contacto'];

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	$sql="SELECT COD_LLAMADO
				,convert(varchar, LL.FECHA_LLAMADO, 103) FECHA_LLAMADO
				,C.NOM_CONTACTO
				,CP.NOM_PERSONA
			 FROM LLAMADO LL
				  ,CONTACTO C 
				  ,CONTACTO_PERSONA	 CP
			 WHERE LL.COD_CONTACTO = C.COD_CONTACTO
			 AND LL.COD_CONTACTO_PERSONA = CP.COD_CONTACTO_PERSONA
			 AND  CP.COD_CONTACTO = C.COD_CONTACTO";
	
	
	if($fecha_desde != ''){
		$fecha_desde = base::str2date($fecha_desde);
		$sql .=" AND LL.FECHA_LLAMADO >= $fecha_desde";
	}
	
	if($fecha_hasta != ''){
		$fecha_hasta = base::str2date($fecha_hasta, '23:59:59');
		$sql .=" AND LL.FECHA_LLAMADO <= $fecha_hasta";
	}
	
	if($empresa != ''){
		$sql .= " AND C.NOM_CONTACTO like '%$empresa%'";
	}
	
	if($rut != ''){
		$sql .=" AND RUT = $rut";	
	}
		$sql .=" ORDER BY COD_LLAMADO ASC";
	$result = $db->build_results($sql);
	for ($i=0; $i<count($result); $i++) {
		$result[$i]['NOM_CONTACTO'] = urlencode($result[$i]['NOM_CONTACTO']);	
		$result[$i]['NOM_PERSONA'] = urlencode($result[$i]['NOM_PERSONA']);	
	}
	print urlencode(json_encode($result));
	
	
/*
	//$result = $db->build_results($sql);
	//if (session::is_set('DW_LLAMADO')) {
$dw_llamado = session::get('dw_llamado_ajax');
		$dw_llamado->set_sql($sql);
		$dw_llamado->retrieve();
		

$dw_llamado->set_sql($sql);
$dw_llamado->make_tabla_htm($dw_llamado->nom_template);
//$dw_llamado->set_sql($sql_original);
$dw_llamado->save_SESSION();

		
	//}
	//print urlencode(json_encode($result));
*/	
?>