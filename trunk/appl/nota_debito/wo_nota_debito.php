<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (w_output::f_viene_del_menu('nota_debito'))
{
  $wo_nota_debito = new wo_nota_debito();
  $wo_nota_debito->retrieve();
} else
{
  $wo = session::get('wo_nota_debito');
  $wo->procesa_event();
}
?>