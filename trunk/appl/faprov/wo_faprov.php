<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('faprov'))
{
  $wo_faprov = new wo_faprov();
  $wo_faprov->retrieve();
} else
{
  $wo = session::get('wo_faprov');
  $wo->procesa_event();
}
?>