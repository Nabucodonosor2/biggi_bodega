<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('orden_pago'))
{
  $wo_orden_pago = new wo_orden_pago();
  $wo_orden_pago->retrieve();
} else
{
  $wo = session::get('wo_orden_pago');
  $wo->procesa_event();
}
?>