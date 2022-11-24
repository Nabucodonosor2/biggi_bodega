<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_nota_debito extends w_output{	
	function wo_nota_debito(){
		$sql = "SELECT ND.COD_NOTA_DEBITO
						,CONVERT(VARCHAR(20), ND.FECHA_NOTA_DEBITO, 103) FECHA_NOTA_DEBITO
						,ND.NRO_NOTA_DEBITO
						,ND.RUT
						,ND.DIG_VERIF
						,ND.NOM_EMPRESA
						,EDS.NOM_ESTADO_DOC_SII
						,TIPO_DOC NOM_TIPO_DOC
						,ND.TOTAL_CON_IVA
				FROM	NOTA_DEBITO ND ,ESTADO_DOC_SII EDS
				WHERE	ND.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII
				ORDER BY COD_NOTA_DEBITO DESC";

		parent::w_output('nota_debito', $sql, $_REQUEST['cod_item_menu']);
	    $this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('RUT'));
		// headers
		$this->add_header(new header_date('FECHA_NOTA_DEBITO', 'FECHA_NOTA_DEBITO', 'Fecha'));
		$this->add_header(new header_num('NRO_NOTA_DEBITO', 'NRO_NOTA_DEBITO', 'Nº ND'));
		$this->add_header(new header_rut('RUT', 'ND', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_text('NOM_TIPO_DOC', 'NOM_TIPO_DOC', 'Tipo Docto.'));	
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/iva'));
	}
	
	function crear_nd_from_nc($ve_nro_nota_credito){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT NRO_NOTA_CREDITO
				FROM NOTA_CREDITO
				WHERE NRO_NOTA_CREDITO = $ve_nro_nota_credito";
	
		$result = $db->build_results($sql);
		if(count($result) == 0){
			$this->_redraw();
			$this->alert('La Nota de Crédito Nº '.$ve_nro_nota_credito.' no existe.');								
			return;
		}

		session::set('ND_CREADA_DESDE', $ve_nro_nota_credito);
		$this->add();
	}
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_nd_from_nc($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>