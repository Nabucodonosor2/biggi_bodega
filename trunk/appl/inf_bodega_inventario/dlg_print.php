<?php
    require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
    
    $temp = new Template_appl('dlg_print.htm');	
    $entrable = true;
    
    
   $sql = "select  '' COD_BODEGA
						,convert(varchar, getdate(), 103) FECHA";
    	
    
    $dw = new datawindow($sql);
    $dw->add_control(new edit_text('COD_SOLICITUD_COMPRA',20,20));
    $sql_bodega="SELECT COD_BODEGA
							,NOM_BODEGA
					FROM BODEGA
					where COD_BODEGA = 2	-- eq terminado
					ORDER BY COD_BODEGA ASC";
    $dw->add_control(new drop_down_dw('COD_BODEGA', $sql_bodega, 0, '', false));
	$dw->add_control(new edit_date('FECHA'));
	
	
    $dw->insert_row();
    $dw->set_item(0,'FECHA',$dw->current_date());
    $dw->habilitar($temp, $entrable);	
    print $temp->toString();
?>