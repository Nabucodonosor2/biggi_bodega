<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (session::is_set('PRESENTACION_MESSAGE'))
	$mess = session::get('PRESENTACION_MESSAGE');		// mensaje que se desea aparezca al desplegar la ventana de presentacion
else
	$mess = '';
if (session::is_set('PRESENTACION_PRINT'))
	$print = session::get('PRESENTACION_PRINT'); 		// codigo javascrip o texto que se desea enviar con un print desde presentacion.php
else
	$print = '';
if (session::is_set('ALERTA_REGISTRO')){
	$nro_orden_compra = session::get('ALERTA_REGISTRO');
	$mess = 'Se han creado nuevas facturas para la OC N°:'.$nro_orden_compra.', mientras se creaba esta factura.';
	$mess .= '\nFavor vuelva a crear la factura desde la OC';
}else if (session::is_set('CREADO_TRASPASO')){
	$cod_ingreso_pago = session::get('CREADO_TRASPASO');
	$mess = 'Señor usuario, se ha generado el INGRESO DE PAGO Nº '.$cod_ingreso_pago.', en '.K_CLIENTE;
}else
	$mess = '';
session::un_set('PRESENTACION_MESSAGE');
session::un_set('PRESENTACION_PRINT');
session::un_set('ALERTA_REGISTRO');
session::un_set('CREADO_TRASPASO');

$t = new Template_appl(session::get('K_ROOT_DIR').'html/presentacion.htm');
$menu = session::get('menu_appl');
$menu->draw($t);

print $t->toString();
if ($mess != '')
	print '<script type="text/javascript">
          alert("'.$mess.'");
					</script>';
if ($print != '')
		print $print;
?>