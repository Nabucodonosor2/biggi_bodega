<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('ncprov'))
{
  $wo_ncprov = new wo_ncprov();
  $wo_ncprov->retrieve();
} else
{
  $wo = session::get('wo_ncprov');
  $wo->procesa_event();
}
?>