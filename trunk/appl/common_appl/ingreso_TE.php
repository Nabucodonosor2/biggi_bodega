<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

// En NV cuando se consulta en modo lectura
if (isset($_REQUEST['cod_item'])) {
	$cod_item = $_REQUEST['cod_item'];
	$sql = "select NOM_PRODUCTO
					,PRECIO
					,COD_TIPO_TE
					,MOTIVO_TE
			from ITEM_NOTA_VENTA
			where COD_ITEM_NOTA_VENTA = $cod_item"; 
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$result = $db->build_results($sql);
	$nom_te = $result[0]['NOM_PRODUCTO'];
	$precio = $result[0]['PRECIO'];
	$cod_tipo_te = $result[0]['COD_TIPO_TE'];
	$motivo_te = $result[0]['MOTIVO_TE'];

	// autoriza TE
	$sql = "select dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'MOTIVO_AUTORIZA') MOTIVO_AUTORIZA
					,dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'FECHA_AUTORIZA') FECHA_AUTORIZA
					,dbo.f_nv_get_datos_autoriza_te(COD_ITEM_NOTA_VENTA, 'USUARIO_AUTORIZA') USUARIO_AUTORIZA
			from AUTORIZA_TE
			where COD_ITEM_NOTA_VENTA = $cod_item"; 
	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	$result = $db->build_results($sql);
	if (count($result) > 0) {
		$nom_usuario_autoriza_te = $result[0]['USUARIO_AUTORIZA'];
		$fecha_autoriza_te = $result[0]['FECHA_AUTORIZA'];
		$motivo_autoriza_te = $result[0]['MOTIVO_AUTORIZA'];
		$display_autoriza = '';
	}
	else {
		$nom_usuario_autoriza_te = '';
		$fecha_autoriza_te = '';
		$motivo_autoriza_te = '__SIN_AUTORIZAR__';
		$display_autoriza = 'none';
	}
}
else {
	$nom_te = $_REQUEST['nom_te'];
	$precio = $_REQUEST['precio'];
	$cod_tipo_te = $_REQUEST['cod_tipo_te'];
	$motivo_te = $_REQUEST['motivo_te'];

	if (isset($_REQUEST['nom_usuario_autoriza_te'])) {
		$nom_usuario_autoriza_te = trim($_REQUEST['nom_usuario_autoriza_te']);
		$fecha_autoriza_te = trim($_REQUEST['fecha_autoriza_te']);
		$motivo_autoriza_te = trim($_REQUEST['motivo_autoriza_te']);
	
		// En NV cuando se consulta en modo lectura
		$K_AUTORIZA_TE = '991015';      // autorizaTE
		$cod_usuario = session::get("COD_USUARIO");
		if (w_base::tiene_privilegio_opcion_usuario($K_AUTORIZA_TE, $cod_usuario))
			$display_autoriza = '';
		else
			$display_autoriza = 'none';
	}
	else {
		$nom_usuario_autoriza_te = '';
		$fecha_autoriza_te = '';
		$motivo_autoriza_te = '';
		$display_autoriza = 'none';
	}
}

$temp = new Template_appl('ingreso_TE.htm');	
$sql = "select null NOM_TE
				,null PRECIO
				,null COD_TIPO_TE
				,null MOTIVO_TE
				,null NOM_USUARIO_AUTORIZA_TE
				,null FECHA_AUTORIZA_TE
				,null MOTIVO_AUTORIZA_TE
				,null DISPLAY_AUTORIZA
				,null AUTORIZA_TE
				,null DISABLE_OK
				,null NOM_USUARIO_SESSION";

$dw = new datawindow($sql);
$dw->add_control(new edit_text_upper('NOM_TE', 100, 100));
$dw->add_control(new edit_num('PRECIO'));
$sql = "select COD_TIPO_TE, NOM_TIPO_TE from TIPO_TE order by ORDEN";
$dw->add_control(new drop_down_dw('COD_TIPO_TE', $sql));
$dw->add_control(new edit_text_multiline('MOTIVO_TE', 45, 4));

$dw->add_control(new static_text('NOM_USUARIO_AUTORIZA_TE'));
$dw->add_control(new static_text('FECHA_AUTORIZA_TE'));
$dw->add_control(new edit_text_multiline('MOTIVO_AUTORIZA_TE', 45, 1));
$dw->add_control($control = new edit_check_box('AUTORIZA_TE', 'S', 'N'));
$control->set_onClick("change_autoriza(this);");
$dw->add_control(new edit_text('NOM_USUARIO_SESSION', 100, 100, 'hidden'));

$dw->insert_row();
$dw->set_item(0, 'NOM_TE',$nom_te); 
$dw->set_item(0, 'PRECIO',$precio); 
$dw->set_item(0, 'COD_TIPO_TE',$cod_tipo_te); 
$dw->set_item(0, 'MOTIVO_TE',$motivo_te); 
$dw->set_item(0, 'NOM_USUARIO_AUTORIZA_TE',$nom_usuario_autoriza_te); 
$dw->set_item(0, 'FECHA_AUTORIZA_TE',$fecha_autoriza_te); 
$dw->set_item(0, 'MOTIVO_AUTORIZA_TE',$motivo_autoriza_te); 
$dw->set_item(0, 'DISPLAY_AUTORIZA',$display_autoriza);
$nom_usuario = session::get("NOM_USUARIO");
$dw->set_item(0, 'NOM_USUARIO_SESSION',$nom_usuario);

if ($motivo_autoriza_te=='') {
	$autoriza_te = 'N';
	$entrable = true;
	$dw->set_item(0, 'DISABLE_OK','');
}
else {
	$autoriza_te = 'S';
	$entrable = false;
	$dw->set_item(0, 'DISABLE_OK','disabled');
}
$dw->set_item(0, 'AUTORIZA_TE', $autoriza_te); 

$dw->habilitar($temp, $entrable);

	
print $temp->toString();
?>