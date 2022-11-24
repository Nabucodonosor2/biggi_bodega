<?php
    require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
    
    $temp = new Template_appl('dlg_print.htm');	
    $entrable = true;
    
    
    $sql = "select  '' R_SOLICITUD
    						,'S' TODAS
    						,'' COD_SOLICITUD_COMPRA";
    	
    
    $dw = new datawindow($sql);
    $dw->add_control(new edit_text('COD_SOLICITUD_COMPRA',20,20));
    $dw->add_control($control = new edit_radio_button('R_SOLICITUD', '', '', 'Ingreso Solicitud', 'IMPRESION'));
		$control->set_onChange("checked_radio_button(this);");
	$dw->add_control($control = new edit_radio_button('TODAS', 'S', 'N', 'Todas', 'IMPRESION'));
		$control->set_onChange("checked_radio_button(this);");
    
    $dw->insert_row();

    $dw->habilitar($temp, $entrable);	
    print $temp->toString();
?>