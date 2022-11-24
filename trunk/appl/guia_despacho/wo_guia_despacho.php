<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('guia_despacho'))
{
  $wo_guia_despacho = new wo_guia_despacho();
  $wo_guia_despacho->retrieve();
} else
{
  $wo = session::get('wo_guia_despacho');
  $wo->procesa_event();
}
?>