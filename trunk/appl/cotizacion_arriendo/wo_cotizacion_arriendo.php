<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('wo_cotizacion_arriendo.htm');	

if (w_output::f_viene_del_menu('cotizacion_arriendo'))
{
  $wo_cotizacion = new wo_cotizacion_arriendo();
  $wo_cotizacion->retrieve();
} else
{
  $wo = session::get('wo_cotizacion_arriendo');
  $wo->procesa_event();
}




?>