<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('pago_faprov_comercial'))
{
  $wo_pago_faprov_comercial = new wo_pago_faprov_comercial();
  $wo_pago_faprov_comercial->retrieve();
} else
{
  $wo = session::get('wo_pago_faprov_comercial');
  $wo->procesa_event();
}
?>