<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('factura'))
{
  $wo_factura = new wo_factura();
  $wo_factura->retrieve();
} else
{
  $wo = session::get('wo_factura');
  $wo->procesa_event();
}
?>