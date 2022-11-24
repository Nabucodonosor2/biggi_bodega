<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_llamado_ajax {
	function dw_llamado_ajax() {
		$temp = new Template_appl('dlg_find_llamado.htm');
		
		$sql_dw="select NULL COD_LLAMADO
						 ,NULL FECHA_LLAMADO
						 ,NULL NOM_EMPRESA
						 ,NULL NOM_CONTACTO";
		
		$dw_llamado_ajax = new datawindow($sql_dw,'LLAMADO',true,true);
		$dw_llamado_ajax->add_control(new static_text('COD_LLAMADO',1));
		$dw_llamado_ajax->add_control(new static_text('FECHA_LLAMADO',1));
		$dw_llamado_ajax->add_control(new static_text('NOM_EMPRESA',1));
		$dw_llamado_ajax->add_control(new static_text('NOM_CONTACTO',1));
		$dw_llamado_ajax->retrieve();
		$dw_llamado_ajax->habilitar($temp, true);
		session::set('dw_llamado_ajax', $dw_llamado_ajax);
		
		$sql="select  NULL FECHA_DESDE
					 ,NULL FECHA_HASTA
					 ,NULL EMPRESA
					 ,NULL RUT
					 ,NULL CONTACTO";
		
		$dw = new datawindow($sql);
		
		
		$dw->add_control(new edit_date('FECHA_DESDE',20,10));
		$dw->add_control(new edit_date('FECHA_HASTA',20,10));
		$dw->add_control(new edit_text('EMPRESA',20,10));
		$dw->add_control(new edit_num('RUT',20,10));
		$dw->add_control(new edit_text('CONTACTO',40,10));
		$dw->retrieve();
		$dw->habilitar($temp, true);
		
		print $temp->toString();
	}
}

$d = new dw_llamado_ajax();
?>