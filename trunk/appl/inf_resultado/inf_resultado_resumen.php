<?phprequire_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_print_dw_resultado_resumen.php");
ini_set('memory_limit', '720M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes
$temp = new Template_appl('inf_resultado_resumen.htm');// make_menu$menu = session::get('menu_appl');$menu->draw($temp);$cod_usuario = session::get("COD_USUARIO");
$sql = "SELECT	(SELECT NOM_MES FROM MES ME WHERE MONTH(RE.FECHA_NOTA_VENTA) = ME.COD_MES) NOM_MES
				,(SUM(RE.MONTO_RESULTADO) / SUM(RE.TOTAL_NETO) ) * 100.00 PORC_RESULTADO --0%
				,SUM(RE.MONTO_RESULTADO)  MONTO_RESULTADO
				,(SUM(RE.MONTO_AA ) / SUM(RE.TOTAL_NETO )) * 100.00 PORC_DIRECTORIO 
				,SUM(RE.MONTO_AA ) MONTO_DIRECTORIO
				,SUM(RE.PAGO_AA) PAGO_DIRECTORIO
				,(SUM(RE.MONTO_GV) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_GV   
				,SUM(RE.MONTO_GV) MONTO_GV
				,SUM(RE.PAGO_GV)  PAGO_GV
				,(SUM(RE.MONTO_ADM) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_ADM  
				,SUM(RE.MONTO_ADM) MONTO_ADM
				,SUM(RE.PAGO_ADM)  PAGO_ADM
				,(SUM(RE.MONTO_VENDEDOR) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_VENDEDOR
				,SUM(RE.MONTO_VENDEDOR)  MONTO_VENDEDOR
				,SUM(RE.PAGO_VENDEDOR)   PAGO_VENDEDOR
		FROM INF_RESULTADO RE		where COD_USUARIO = $cod_usuario
		GROUP BY MONTH(RE.FECHA_NOTA_VENTA)
		ORDER BY MONTH(RE.FECHA_NOTA_VENTA) ASC";$dw_resultado = new datawindow($sql,'ITEM_INF_RESULTADO');$dw_resultado->add_control(new static_text('NOM_MES'));$dw_resultado->add_control(new static_num('PORC_RESULTADO',2));$dw_resultado->add_control(new static_num('MONTO_RESULTADO'));$dw_resultado->add_control(new static_num('PORC_DIRECTORIO',2));$dw_resultado->add_control(new static_num('MONTO_DIRECTORIO'));$dw_resultado->add_control(new static_num('PAGO_DIRECTORIO'));$dw_resultado->add_control(new static_num('PORC_GV',2));$dw_resultado->add_control(new static_num('MONTO_GV'));$dw_resultado->add_control(new static_num('PAGO_GV'));$dw_resultado->add_control(new static_num('PORC_ADM',2));$dw_resultado->add_control(new static_num('MONTO_ADM'));$dw_resultado->add_control(new static_num('PAGO_ADM'));$dw_resultado->add_control(new static_num('PORC_VENDEDOR',2));$dw_resultado->add_control(new static_num('MONTO_VENDEDOR'));$dw_resultado->add_control(new static_num('PAGO_VENDEDOR'));// sumas$dw_resultado->accumulate('MONTO_RESULTADO', '', false);$dw_resultado->accumulate('MONTO_DIRECTORIO', '', false);$dw_resultado->accumulate('PAGO_DIRECTORIO', '', false);$dw_resultado->accumulate('MONTO_GV', '', false);$dw_resultado->accumulate('PAGO_GV', '', false);$dw_resultado->accumulate('MONTO_ADM', '', false);$dw_resultado->accumulate('PAGO_ADM', '', false);$dw_resultado->accumulate('MONTO_VENDEDOR', '', false);$dw_resultado->accumulate('PAGO_VENDEDOR', '', false);		
if (isset($_POST['b_print_x'])) {
	$xml = session::get('K_ROOT_DIR').'appl/inf_resultado/inf_resultado_resumen.xml';
	$labels = array();
	$labels['str_mes'] = 'PRINT PANTALLA';//$result[0]['NOM_MES'];
		
	$rpt = new print_dw_resultado_resumen($sql, $xml, $labels, "Resultado.pdf", 0);
}else if (isset($_POST['b_export_x'])) {
	ini_set('memory_limit', '30M');
	//error_reporting(E_ALL & ~E_NOTICE);
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php");
	require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php");
	
	$fname = tempnam("/tmp", "resultado_resumen.xls");
	$workbook = &new writeexcel_workbook($fname);
	$worksheet = &$workbook->addworksheet('RESULTADO_RESUMEN');
	
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql = "SELECT	(SELECT NOM_MES FROM MES ME WHERE MONTH(RE.FECHA_NOTA_VENTA) = ME.COD_MES) NOM_MES
				,(SUM(RE.MONTO_RESULTADO) / SUM(RE.TOTAL_NETO) ) * 100.00 PORC_RESULTADO --0%
				,SUM(RE.MONTO_RESULTADO)  MONTO_RESULTADO
				,(SUM(RE.MONTO_AA ) / SUM(RE.TOTAL_NETO )) * 100.00 PORC_DIRECTORIO 
				,SUM(RE.MONTO_AA ) MONTO_DIRECTORIO
				,SUM(RE.PAGO_AA) PAGO_DIRECTORIO
				,(SUM(RE.MONTO_GV) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_GV   
				,SUM(RE.MONTO_GV) MONTO_GV
				,SUM(RE.PAGO_GV)  PAGO_GV
				,(SUM(RE.MONTO_ADM) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_ADM  
				,SUM(RE.MONTO_ADM) MONTO_ADM
				,SUM(RE.PAGO_ADM)  PAGO_ADM
				,(SUM(RE.MONTO_VENDEDOR) / SUM(RE.TOTAL_NETO)) * 100.00 PORC_VENDEDOR
				,SUM(RE.MONTO_VENDEDOR)  MONTO_VENDEDOR
				,SUM(RE.PAGO_VENDEDOR)   PAGO_VENDEDOR
		FROM INF_RESULTADO RE
		where COD_USUARIO = $cod_usuario		GROUP BY MONTH(RE.FECHA_NOTA_VENTA)
		ORDER BY MONTH(RE.FECHA_NOTA_VENTA) ASC";
	$result = $db->build_results($sql);
	$count = count($result);
	
	//se les da formato a la fuente
	$text =& $workbook->addformat();
	$text->set_font("Verdana");
	$text->set_valign('vcenter');
    
	$text_bold =& $workbook->addformat();
	$text_bold->copy($text);
	$text_bold->set_bold(1);

	$text_normal_left =& $workbook->addformat();
	$text_normal_left->copy($text);
	$text_normal_left->set_align('left');
	$text_normal_center =& $workbook->addformat();
	$text_normal_center->copy($text);
	$text_normal_center->set_align('center');
	$text_normal_right =& $workbook->addformat();
	$text_normal_right->copy($text);
	$text_normal_right->set_align('right');
	
	//DECIMALES
	$porc_normal =& $workbook->addformat();
	$porc_normal->copy($text_normal_center);
	$porc_normal->set_num_format('0.00');
				
	$monto_normal =& $workbook->addformat();
	$monto_normal->copy($text_normal_right);
	$monto_normal->set_num_format('#,##0');
	////////////////////////////////////////////////////////////////	
	
	$text_normal_bold_left =& $workbook->addformat();
	$text_normal_bold_left->copy($text_bold);
	$text_normal_bold_left->set_align('left');
	$text_normal_bold_center =& $workbook->addformat();
	$text_normal_bold_center->copy($text_bold);
	$text_normal_bold_center->set_align('center');
	$text_normal_bold_right =& $workbook->addformat();
	$text_normal_bold_right->copy($text_bold);
	$text_normal_bold_right->set_align('right');
	
	$text_blue_bold_left =& $workbook->addformat();
	$text_blue_bold_left->copy($text_bold);
	$text_blue_bold_left->set_align('left');
	$text_blue_bold_left->set_color('blue_0x20');
	$text_blue_bold_center =& $workbook->addformat();
	$text_blue_bold_center->copy($text_bold);
	$text_blue_bold_center->set_align('center');
	$text_blue_bold_center->set_color('blue_0x20');
	$text_blue_bold_right =& $workbook->addformat();
	$text_blue_bold_right->copy($text_bold);

	$space_item_left_bold = & $workbook->addformat();
	$space_item_left_bold->copy($text_blue_bold_center);
	$space_item_left_bold->set_border_color('black');
	$space_item_left_bold->set_top(2);
	$space_item_left_bold->set_left(2);
	
	$space_item_right_bold = & $workbook->addformat();
	$space_item_right_bold->copy($text_blue_bold_center);
	$space_item_right_bold->set_border_color('black');
	$space_item_right_bold->set_top(2);
	$space_item_right_bold->set_right(2);
	
	//bordes
	$border_item_left_bold = & $workbook->addformat();
	$border_item_left_bold->copy($text_blue_bold_center);
	$border_item_left_bold->set_border_color('black');
	$border_item_left_bold->set_top(2);
	$border_item_left_bold->set_left(2);
	$border_item_left_bold->set_right(2);
	$border_item_left_bold->set_bottom(2);

	$worksheet->write(1, 0, "MES", $border_item_left_bold);
	$worksheet->write(0, 1, "RESULTADOS", $space_item_left_bold);
	$worksheet->write(0, 3, " ", $space_item_left_bold);
	$worksheet->write(0, 4, "DIRECTORIO", $text_blue_bold_center);
	$worksheet->write(0, 5, " ", $text_blue_bold_center);
	$worksheet->write(0, 6, " ", $space_item_left_bold);
	$worksheet->write(0, 7, "GTE. VENTA", $text_blue_bold_center);
	$worksheet->write(0, 8, " ", $text_blue_bold_center);
	$worksheet->write(0, 9, " ", $space_item_left_bold);
	$worksheet->write(0, 10, "ADMINISTRACION", $text_blue_bold_center);
	$worksheet->write(0, 11, " ", $text_blue_bold_center);
	$worksheet->write(0, 12, " ", $space_item_left_bold);
	$worksheet->write(0, 13, "VENDEDOR", $text_blue_bold_center);
	$worksheet->write(0, 14, " ", $space_item_right_bold);
	
	$worksheet->write(1, 1, "Porc. %", $border_item_left_bold);
	$worksheet->write(1, 2, "Monto $", $border_item_left_bold);
	$worksheet->write(1, 3, "Porc. %", $border_item_left_bold);
	$worksheet->write(1, 4, "Monto $", $border_item_left_bold);
	$worksheet->write(1, 5, "Pagado $", $border_item_left_bold);
	$worksheet->write(1, 6, "Porc. %", $border_item_left_bold);
	$worksheet->write(1, 7, "Monto $", $border_item_left_bold);
	$worksheet->write(1, 8, "Pagado $", $border_item_left_bold);
	$worksheet->write(1, 9, "Porc. %", $border_item_left_bold);
	$worksheet->write(1, 10, "Monto $", $border_item_left_bold);
	$worksheet->write(1, 11, "Pagado $", $border_item_left_bold);
	$worksheet->write(1, 12, "Porc. %", $border_item_left_bold);
	$worksheet->write(1, 13, "Monto $", $border_item_left_bold);
	$worksheet->write(1, 14, "Pagado $", $border_item_left_bold);
	
	$sum_monto_resultado	= 0;	$sum_porc_directorio	= 0;	$sum_monto_directorio	= 0;	$sum_pago_directorio	= 0;	$sum_monto_gv			= 0;	$sum_pago_gv			= 0;	$sum_monto_adm			= 0;	$sum_pago_adm			= 0;	$sum_monto_vendedor		= 0;	$sum_pago_vendedor		= 0;	$i=2;
	for($h=0; $h<$count; $h++){
		$nom_mes			= $result[$h]['NOM_MES'];
		$porc_resultado 	= $result[$h]['PORC_RESULTADO'];
		$monto_resultado	= $result[$h]['MONTO_RESULTADO'];
		$porc_directorio	= $result[$h]['PORC_DIRECTORIO'];
		$monto_directorio	= $result[$h]['MONTO_DIRECTORIO'];
		$pago_directorio	= $result[$h]['PAGO_DIRECTORIO'];
		$porc_gv			= $result[$h]['PORC_GV'];
		$monto_gv			= $result[$h]['MONTO_GV'];
		$pago_gv			= $result[$h]['PAGO_GV'];
		$porc_adm			= $result[$h]['PORC_ADM'];
		$monto_adm			= $result[$h]['MONTO_ADM'];
		$pago_adm			= $result[$h]['PAGO_ADM'];
		$porc_vendedor		= $result[$h]['PORC_VENDEDOR'];
		$monto_vendedor		= $result[$h]['MONTO_VENDEDOR'];
		$pago_vendedor		= $result[$h]['PAGO_VENDEDOR'];

		$sum_monto_resultado	+= $monto_resultado;		$sum_porc_directorio	+= $porc_directorio;		$sum_monto_directorio	+= $monto_directorio;		$sum_pago_directorio	+= $pago_directorio;		$sum_monto_gv			+= $monto_gv;		$sum_pago_gv			+= $pago_gv;		$sum_monto_adm			+= $monto_adm;		$sum_pago_adm			+= $pago_adm;		$sum_monto_vendedor		+= $monto_vendedor;		$sum_pago_vendedor		+= $pago_vendedor;		$worksheet->write($i, 0,$nom_mes, $text_normal_left);
		$worksheet->write($i, 1,$porc_resultado, $porc_normal);
		$worksheet->write($i, 2,$monto_resultado, $monto_normal);
		$worksheet->write($i, 3,$porc_directorio, $porc_normal);
		$worksheet->write($i, 4,$monto_directorio, $monto_normal);
		$worksheet->write($i, 5,$pago_directorio, $monto_normal);
		$worksheet->write($i, 6,$porc_gv, $porc_normal);
		$worksheet->write($i, 7,$monto_gv, $monto_normal);
		$worksheet->write($i, 8,$pago_gv, $monto_normal);
		$worksheet->write($i, 9,$porc_adm, $porc_normal);
		$worksheet->write($i,10,$monto_adm, $monto_normal);
		$worksheet->write($i,11,$pago_adm, $monto_normal);
		$worksheet->write($i,12,$porc_vendedor, $porc_normal);
		$worksheet->write($i,13,$monto_vendedor, $monto_normal);
		$worksheet->write($i,14,$pago_vendedor, $monto_normal);
		
		$i++;
	}
	$worksheet->write($i, 2,$sum_monto_resultado, $monto_normal);	$worksheet->write($i, 4,$sum_monto_directorio, $monto_normal);	$worksheet->write($i, 5,$sum_pago_directorio, $monto_normal);	$worksheet->write($i, 7,$sum_monto_gv, $monto_normal);	$worksheet->write($i, 8,$sum_pago_gv, $monto_normal);	$worksheet->write($i,10,$sum_monto_adm, $monto_normal);	$worksheet->write($i,11,$sum_pago_adm, $monto_normal);	$worksheet->write($i,13,$sum_monto_vendedor, $monto_normal);	$worksheet->write($i,14,$sum_pago_vendedor, $monto_normal);	
	$workbook->close();
	
	header("Content-Type: application/x-msexcel; name=\"resultado_resumen.xls\"");
	header("Content-Disposition: inline; filename=\"resultado_resumen.xls\"");
	$fh=fopen($fname, "rb");
	fpassthru($fh);
} 

	$db_user = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$sql_usuario = "SELECT	NOM_USUARIO
							,convert(varchar, getdate(), 103) FECHA_ACTUAL
					FROM	usuario 
					WHERE cod_usuario = $cod_usuario";
	$sql_usuario = $db_user->build_results($sql_usuario);
	
	$nom_usuario = $sql_usuario[0]['NOM_USUARIO'];
	$fecha_actual = $sql_usuario[0]['FECHA_ACTUAL'];
	
	$temp->setVar("NOM_USUARIO", $nom_usuario);
	$temp->setVar("FECHA_ACTUAL", $fecha_actual);
// draw	$dw_resultado->retrieve();	$dw_resultado->habilitar($temp, true);	print $temp->toString();?>