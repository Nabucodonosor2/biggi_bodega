<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class wo_orden_compra extends w_output {
   	function wo_orden_compra() {
		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA              							                      
							,E.NOM_EMPRESA              
							,NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO
							,COD_DOC
							,SC.COD_PRODUCTO
							,SC.CANTIDAD                 
				from 		ORDEN_COMPRA O LEFT OUTER JOIN SOLICITUD_COMPRA SC ON O.COD_DOC = SC.COD_SOLICITUD_COMPRA
							,EMPRESA E
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and 
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'SOLICITUD_COMPRA')					
				order by	COD_ORDEN_COMPRA desc";		
			
   		parent::w_output('orden_compra', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº OC'));
		$this->add_header(new header_date('FECHA_ORDEN_COMPRA', 'convert(varchar(20), FECHA_ORDEN_COMPRA, 103)', 'Fecha'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
		$this->add_header(new header_num('COD_DOC', 'COD_DOC', 'Cod. Solicitud'));
		$this->add_header(new header_text('COD_PRODUCTO', 'SC.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_num('CANTIDAD', 'SC.CANTIDAD', 'Cantidad', 1));  
	}
}

?>