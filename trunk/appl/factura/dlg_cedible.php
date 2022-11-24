<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_cedible.htm');
$sql = "SELECT NULL PRINT_NORMAL
			  ,NULL PRINT_CEDIBLE";
$dw = new datawindow($sql);

$dw->add_control($control = new edit_check_box('PRINT_NORMAL', 'S', 'N'));
$control->set_onClick("valida_check(this);");
$dw->add_control($control = new edit_check_box('PRINT_CEDIBLE', 'S', 'N'));
$control->set_onClick("valida_check(this);");
$dw->insert_row();
$dw->set_item(0, 'PRINT_NORMAL', 'S');
$dw->habilitar($temp, true);
print $temp->toString();	
?>