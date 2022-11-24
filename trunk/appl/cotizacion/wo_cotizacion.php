<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('cotizacion'))
{
  $wo_cotizacion = new wo_cotizacion();
  $wo_cotizacion->retrieve();
} else
{
  $wo = session::get('wo_cotizacion');
  $wo->procesa_event();
}
?>