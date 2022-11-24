<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('arriendo'))
{
  $wo_arriendo = new wo_arriendo();
  $wo_arriendo->retrieve();
} else
{
  $wo = session::get('wo_arriendo');
  $wo->procesa_event();
}
?>