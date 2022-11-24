<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('nota_credito'))
{
  $wo_nota_credito = new wo_nota_credito();
  $wo_nota_credito->retrieve();
} else
{
  $wo = session::get('wo_nota_credito');
  $wo->procesa_event();
}
?>