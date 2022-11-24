<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
ini_set('memory_limit', '720M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes

if (isset($_POST['b_ok'])) {
	$dw_param = session::get("dw_param");
	session::un_set("dw_param");
	
	$dw_param->get_values_from_POST();
	
	$ano = $dw_param->get_item(0, 'ANO');
	$cod_cc = $dw_param->get_item(0, 'COD_CC');
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$cod_usuario = session::get("COD_USUARIO");
	$sp = 'spi_resultado';
	$param = "$cod_usuario
			,$ano
			,'$cod_cc'";
	
	$db->EXECUTE_SP($sp, $param);
	header('Location: inf_resultado_resumen.php');
}
else if (isset($_POST['b_cancel'])){
	session::un_set("dw_param");
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_resultado.htm');	
	
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$sql = "select year(getdate()) ANO
					,'' COD_CC";
	$dw_param = new datawindow($sql);
	$dw_param->add_control(new edit_ano('ANO'));
	$sql = "SELECT COD_CENTRO_COSTO
					, NOM_CENTRO_COSTO
			from CENTRO_COSTO
			where COD_CENTRO_COSTO <> '007'	-- todoinox
			order by  COD_CENTRO_COSTO";
	$dw_param->add_control(new drop_down_dw('COD_CC', $sql, 0, '', false));
	
	// draw
	$dw_param->retrieve();
	$dw_param->habilitar($temp, true);
	session::set("dw_param", $dw_param);
	
	print $temp->toString();
}
?>