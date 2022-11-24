<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('wi_inf_facturas_por_cliente.htm');	

$sql = "select  null RUT
				,'' DIG_VERIF
				,null F_INICIAL
				,null F_TERMINO
				,CONVERT(VARCHAR(10),getdate(),103) F_ACTUAL";
$dw_datos = new datawindow($sql);

$dw_datos->add_control($control = new edit_rut('RUT',10,10));
$control->set_onChange("change_rut(this);");

$dw_datos->add_control(new static_text('DIG_VERIF'));
$dw_datos->controls['DIG_VERIF']->type = 'text';
$dw_datos->add_control(new edit_text_hidden('F_ACTUAL',10,10));

$dw_datos->add_control(new edit_date('F_INICIAL'));
$dw_datos->add_control(new edit_date('F_TERMINO'));
	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql_fecha = 'select CONVERT(VARCHAR(10),getdate(),103) F_ACTUAL';
$result = $db->build_results($sql);

$rut = '';
$dig_verif = '';
$f_inicial = '';
$f_termino = '';
$f_actual = $result[0]['F_ACTUAL'];
$entrable = true;

//$entrable = false;
$dw_datos->insert_row();
$dw_datos->set_item(0, 'RUT',$rut);
$dw_datos->set_item(0, 'DIG_VERIF',$dig_verif);
$dw_datos->set_item(0, 'F_INICIAL',$f_inicial); 
$dw_datos->set_item(0, 'F_TERMINO',$f_termino); 
$dw_datos->set_item(0, 'F_ACTUAL',$f_actual); 
$dw_datos->habilitar($temp, $entrable);
$menu = session::get('menu_appl');
$menu->draw($temp);
print $temp->toString();
?>