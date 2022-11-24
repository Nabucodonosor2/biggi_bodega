<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_factura_arriendo extends w_output {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA = 2;
	const K_PARAM_MAX_IT_FA = 29;
	const K_AUTORIZA_EXPORTAR = '992010';
	const K_AUTORIZA_SOLO_BITACORA = '992025';
	const K_TIPO_ARRIENDO = 2;

	function wo_factura_arriendo() {
		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_fa_tipo_doc(F.COD_FACTURA) TIPO_FA
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						F.COD_TIPO_FACTURA = ".self::K_TIPO_ARRIENDO."
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
				
	     parent::w_output('factura_arriendo', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_FACTURA','FACTURA'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new edit_precio('TOTAL_CON_IVA'));

	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SOLO_BITACORA, $this->cod_usuario);	// acceso bitacora
		if ($priv=='E') {
			$this->b_add_visible = false;
      	}
      	else {
			$this->b_add_visible = true;
      	}
	   	
		// headers
		$this->add_header(new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha'));
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº FA'));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from FACTURA F, USUARIO U where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		
		$sql = "SELECT 'NORMAL' ES_TIPO, 'NORMAL' TIPO_FA UNION SELECT 'EXENTA' ES_TIPO , 'EXENTA' TIPO_FA";
		$this->add_header(new header_drop_down_string('TIPO_FA', '(select dbo.f_fa_tipo_doc(COD_FACTURA))', 'Tipo FA', $sql)); 
  	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
	}	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return selecciona_contrato();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}	
  	function crear_fa_from_arriendo($lista_contratos) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		$db->BEGIN_TRANSACTION();
		$cod_usuario = $this->cod_usuario;	
		$sp = 'sp_fa_arriendo';
		$param = "'$lista_contratos'
					,'S'
					,$cod_usuario";
		if ($db->EXECUTE_SP($sp, $param)) { 
			$db->COMMIT_TRANSACTION();
			$this->retrieve();
		}
		else { 
			$db->ROLLBACK_TRANSACTION();
			$this->_redraw();
			$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_gds', favor contacte a IntegraSystem.");
		}	
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_fa_from_arriendo($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>