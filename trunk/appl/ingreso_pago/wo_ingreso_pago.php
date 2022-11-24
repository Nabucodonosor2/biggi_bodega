<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('ingreso_pago'))
{
  $wo_ingreso_pago = new wo_ingreso_pago();
  $wo_ingreso_pago->retrieve();
} else
{
  $wo = session::get('wo_ingreso_pago');
  $wo->procesa_event();
}
?>