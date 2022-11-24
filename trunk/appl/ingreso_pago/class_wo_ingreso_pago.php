<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_ingreso_pago extends w_output_biggi {
   	function wo_ingreso_pago() {
		$sql = "SELECT 	IP.COD_INGRESO_PAGO
						,convert(varchar(20), IP.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
						,IP.FECHA_INGRESO_PAGO DATE_INGRESO_PAGO
						,RUT
						,DIG_VERIF
						,E.NOM_EMPRESA
						,EIP.NOM_ESTADO_INGRESO_PAGO
						,EIP.COD_ESTADO_INGRESO_PAGO
						,dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO) MONTO_DOC
						,dbo.f_ingreso_pago_get_cant_doc(IP.COD_INGRESO_PAGO) CANT_DOC
				FROM 	INGRESO_PAGO IP, EMPRESA E, ESTADO_INGRESO_PAGO EIP
				WHERE 	IP.COD_EMPRESA = E.COD_EMPRESA AND
						IP.COD_ESTADO_INGRESO_PAGO = EIP.COD_ESTADO_INGRESO_PAGO
						ORDER BY COD_INGRESO_PAGO DESC";		
			
   		parent::w_output_biggi('ingreso_pago', $sql, $_REQUEST['cod_item_menu']);

   		$this->dw->add_control(new edit_nro_doc('COD_INGRESO_PAGO','INGRESO_PAGO'));
		$this->dw->add_control(new edit_precio('MONTO_DOC'));
		$this->dw->add_control(new static_num('RUT'));
   		
		// headers 
		$this->add_header(new header_num('COD_INGRESO_PAGO', 'COD_INGRESO_PAGO', 'Código'));
		$this->add_header($control = new header_date('FECHA_INGRESO_PAGO', 'FECHA_INGRESO_PAGO', 'Fecha '));
		$control->field_bd_order = 'DATE_INGRESO_PAGO';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Cliente'));
		$sql_estado_ip = "select COD_ESTADO_INGRESO_PAGO, NOM_ESTADO_INGRESO_PAGO from ESTADO_INGRESO_PAGO order by	COD_ESTADO_INGRESO_PAGO";
      	$this->add_header(new header_drop_down('NOM_ESTADO_INGRESO_PAGO', 'EIP.COD_ESTADO_INGRESO_PAGO', 'Estado', $sql_estado_ip));
		$this->add_header($control = new header_num('CANT_DOC', '(dbo.f_ingreso_pago_get_cant_doc(IP.COD_INGRESO_PAGO))', 'Cant. Doc.'));
		$control->field_bd_order = 'CANT_DOC';
		$this->add_header($control = new header_num('MONTO_DOC','(dbo.f_ingreso_pago_get_saldo_output(IP.COD_INGRESO_PAGO))', 'Monto Doc.'));  
		$control->field_bd_order = 'MONTO_DOC';
   	}
}
?>