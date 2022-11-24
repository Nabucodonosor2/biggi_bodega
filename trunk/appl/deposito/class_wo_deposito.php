<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_deposito extends w_output_biggi {
	function wo_deposito() {   	
		$sql = "select D.COD_DEPOSITO
						,D.NRO_DEPOSITO
						,convert(varchar, D.FECHA_DEPOSITO, 103) FECHA_DEPOSITO
						,D.FECHA_DEPOSITO DATE_DEPOSITO
						,C.NOM_CUENTA_CORRIENTE
						,dbo.f_dep_monto(D.COD_DEPOSITO) MONTO 
						--,1111 MONTO 
				from DEPOSITO D, CUENTA_CORRIENTE C
				where C.COD_CUENTA_CORRIENTE = D.COD_CUENTA_CORRIENTE
				  and D.COD_DEPOSITO > 1	-- el uno es para traspaso inicial
				order by COD_DEPOSITO desc";
			
		parent::w_output_biggi('deposito', $sql, $_REQUEST['cod_item_menu']);
      
		$this->dw->add_control(new edit_precio('MONTO'));
		
      	// headers      
		$this->add_header(new header_num('COD_DEPOSITO', 'D.COD_DEPOSITO', 'Código'));
		$this->add_header(new header_num('NRO_DEPOSITO', 'D.NRO_DEPOSITO', 'Número'));
		$this->add_header($control = new header_date('FECHA_DEPOSITO', 'D.FECHA_DEPOSITO', 'Fecha'));
		$control->field_bd_order = 'DATE_DEPOSITO';
		$this->add_header(new header_text('NOM_CUENTA_CORRIENTE', 'C.NOM_CUENTA_CORRIENTE', 'Cuenta Corriente'));
		$this->add_header(new header_num('MONTO', 'dbo.f_dep_monto(D.COD_DEPOSITO)', 'Monto'));
	}
}
?>