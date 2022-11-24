<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('pago_faprov'))
{
  $wo_pago_faprov = new wo_pago_faprov();
  $wo_pago_faprov->retrieve();
} else
{
  $wo = session::get('wo_pago_faprov');
  $wo->procesa_event();
}
?>