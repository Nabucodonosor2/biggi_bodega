<?php
class wo_gasto_fijo extends wo_gasto_fijo_base {
   	function wo_gasto_fijo() {
   		parent::wo_gasto_fijo_base();
		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,FECHA_ORDEN_COMPRA DATE_GASTO_FIJO             							                      
							,E.NOM_EMPRESA              
							,REFERENCIA  
							,EOC.COD_ESTADO_ORDEN_COMPRA     
							,NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO  
							,E.RUT
							,E.DIG_VERIF 
				from 		ORDEN_COMPRA O
							,EMPRESA E
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA = 'GASTO_FIJO'					
				order by	COD_ORDEN_COMPRA desc";		

		$this->dw->set_sql($sql);		
		$this->sql_original = $sql;
		$this->dw->add_control(new static_num('RUT'));
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
	}
}
?>