<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_orden_compra_arriendo extends w_output {
	const K_ARRIENDO_APROBADO = 2;
	
	function wo_orden_compra_arriendo() {
		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA              							                      
							,E.NOM_EMPRESA              
							,REFERENCIA       
							,U.NOM_USUARIO	
							,NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO                   
				from 		ORDEN_COMPRA O
							,EMPRESA E
							,USUARIO U
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and 
							O.COD_USUARIO = U.COD_USUARIO and
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA = 'RENTAL'					
				order by	COD_ORDEN_COMPRA desc";		
			
   		parent::w_output('orden_compra_arriendo', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº OC'));
		$this->add_header(new header_date('FECHA_ORDEN_COMPRA', 'convert(varchar(20), FECHA_ORDEN_COMPRA, 103)', 'Fecha'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$sql_solicitante = "select COD_USUARIO, NOM_USUARIO from USUARIO order by COD_USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'U.COD_USUARIO', 'Solicitante', $sql_solicitante));
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
	}
	function crear_desde_arriendo($cod_arriendo) {
		// Cuando se compra para la bodega de RENTAL $cod_arriendo == 0
		if ($cod_arriendo!=0) {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select COD_ESTADO_ARRIENDO
					from ARRIENDO
					where COD_ARRIENDO = $cod_arriendo";
			$result = $db->build_results($sql);
			if (count($result)==0) {
				$this->_redraw();
				$this->alert("Contrato de arriendo no existe, contrato Nº: $cod_arriendo");
				return;
			}
			$cod_estado_arriendo = $result[0]['COD_ESTADO_ARRIENDO'];
			if ($cod_estado_arriendo !=self:: K_ARRIENDO_APROBADO) {
				$this->_redraw();
	 			$this->alert("El arriendo $cod_arriendo, no esta aprobado.");
				return;
			}
		}
		
		session::set('ORDEN_COMPRA.CREAR_DESDE_ARRIENDO', $cod_arriendo);
		$this->add();
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_desde_arriendo($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>