<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_guia_despacho_arriendo extends w_output {
	const K_ARRIENDO_APROBADO	= 2;
	const K_TIPO_ARRIENDO		= 5;
	const K_ESTADO_SII_EMITIDA	= 1;
   
	function wo_guia_despacho_arriendo() {
		$sql = "select	GD.COD_GUIA_DESPACHO
						,GD.NRO_GUIA_DESPACHO
						,convert(varchar(20), GD.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO
						,GD.RUT
						,GD.DIG_VERIF
						,GD.NOM_EMPRESA
						,NOM_ESTADO_DOC_SII
						,GD.COD_FACTURA
						,dbo.f_gd_nros_factura(COD_GUIA_DESPACHO) NRO_FACTURA
						,NOM_TIPO_GUIA_DESPACHO
						,GD.COD_DOC
			from 		GUIA_DESPACHO GD LEFT OUTER JOIN FACTURA F 
					ON GD.COD_FACTURA = F.COD_FACTURA
						,ESTADO_DOC_SII EDS
						,TIPO_GUIA_DESPACHO TGD
			where		GD.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII  and
						GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO and
						GD.COD_TIPO_GUIA_DESPACHO = ".self::K_TIPO_ARRIENDO."
			order by	isnull(NRO_GUIA_DESPACHO, 9999999999) desc, COD_GUIA_DESPACHO desc";
			
		parent::w_output('guia_despacho_arriendo', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_GUIA_DESPACHO','GUIA_DESPACHO'));
		$this->dw->add_control(new static_num('RUT'));
					
		// headers
		$this->add_header(new header_date('FECHA_GUIA_DESPACHO', 'FECHA_GUIA_DESPACHO', 'Fecha'));
		$this->add_header(new header_num('NRO_GUIA_DESPACHO', 'NRO_GUIA_DESPACHO', 'Nº GD'));
		$this->add_header(new header_rut('RUT', 'GD', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'GD.NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº Factura'));
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by	COD_TIPO_GUIA_DESPACHO";
		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_DESPACHO', 'TGD.COD_TIPO_GUIA_DESPACHO', 'Tipo Docto.', $sql_tipo_guia_despacho));
		$this->add_header(new header_num('COD_DOC', 'GD.COD_DOC', 'N° Docto.'));
  	}
	function crear_gd_from_arriendo($cod_arriendo) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		///valida que exista
		$sql = "select COD_ESTADO_ARRIENDO 
				from ARRIENDO 
				where COD_ARRIENDO = $cod_arriendo";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' no existe.');								
			return;
		}
		if ($result[0]['COD_ESTADO_ARRIENDO']!=self::K_ARRIENDO_APROBADO) {	
			$this->_redraw();
			$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' no esta confirmado.');								
			return;
		}
		
		/* valida que el ARR no tenga GDs anteriores en estado = emitida
		ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar siempre será la misma 
		cantidad de la NV.
		*/
		$sql = "select COD_GUIA_DESPACHO
				from GUIA_DESPACHO
				where COD_DOC = $cod_arriendo 
				and COD_TIPO_GUIA_DESPACHO = ".self::K_TIPO_ARRIENDO."
				and COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
		$result = $db->build_results($sql);
		if (count($result) != 0){
			$this->_redraw();
			$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' tiene Guía(s) pendientes(s) en estado emitido. Para poder generar más guías deberá imprimir los documentos emitidos.');						
			return;
		}
		
		// valida que hayan item por despachar
		$sql = "select sum(dbo.f_arr_cant_por_despachar(IT.COD_ITEM_ARRIENDO, 'TODO_ESTADO')) POR_DESPACHAR
				from ITEM_ARRIENDO IT, ARRIENDO A
				where A.COD_ARRIENDO = $cod_arriendo
				  and A.COD_ARRIENDO = IT.COD_ARRIENDO";
		$result = $db->build_results($sql);
		$por_despachar = $result[0]['POR_DESPACHAR'];
		if ($por_despachar <= 0){
			$this->_redraw();
			$this->alert('El contrato de arriendo Nº '.$cod_arriendo.' está totalmente despachado.');								
			return;
		}
	  	
		$db->BEGIN_TRANSACTION();
		$cod_usuario = $this->cod_usuario;			
		$sp = 'sp_gd_crear_desde_arriendo';
		$param = "$cod_arriendo, $cod_usuario";
		
		if ($db->EXECUTE_SP($sp, $param)){ 
			$db->COMMIT_TRANSACTION();
			$this->detalle_record_desde(true);
		}
		else{ 
			$db->ROLLBACK_TRANSACTION();	
			$this->_redraw();
			$this->alert("No se pudo crear la guía de despacho. Error en 'sp_gd_crear_desde_arriendo', favor contacte a IntegraSystem.");
		}			
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_gd_from_arriendo($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>