<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('guia_recepcion'))
{
  $wo_guia_recepcion = new wo_guia_recepcion();
  $wo_guia_recepcion->retrieve();
} else
{
  $wo = session::get('wo_guia_recepcion');
  $wo->procesa_event();
}
?>