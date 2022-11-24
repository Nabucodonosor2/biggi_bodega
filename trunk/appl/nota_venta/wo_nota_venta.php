<?php
include ("class_wo_nota_venta.php");


if (w_output::f_viene_del_menu('nota_venta'))
{
  $wo_nota_venta = new wo_nota_venta();
  $wo_nota_venta->retrieve();
} else
{
  $wo = session::get('wo_nota_venta');
  $wo->procesa_event();
}
?>