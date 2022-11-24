<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('participacion'))
{
  $wo_participacion = new wo_participacion();
  $wo_participacion->retrieve();
} else
{
  $wo = session::get('wo_participacion');
  $wo->procesa_event();
}
?>