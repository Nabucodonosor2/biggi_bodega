<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");

class dw_item_salida_bodega extends datawindow {
	function dw_item_salida_bodega() {		
		$sql = "SELECT	ISB.COD_ITEM_SALIDA_BODEGA,
						ISB.COD_SALIDA_BODEGA,
						P.COD_PRODUCTO COD_PRODUCTO_OLD,
						P.COD_PRODUCTO,
						P.NOM_PRODUCTO,
						ISB.CANTIDAD
				FROM	ITEM_SALIDA_BODEGA ISB,  PRODUCTO P
				WHERE 	ISB.COD_SALIDA_BODEGA =  {KEY1}
				AND		ISB.COD_PRODUCTO = P.COD_PRODUCTO";

		parent::datawindow($sql, 'ITEM_SALIDA_BODEGA', true, true);	

		$this->add_control(new static_num('COD_ITEM_SALIDA_BODEGA'));
		$this->add_control(new static_num('COD_SALIDA_BODEGA'));
		$this->add_control(new static_text('COD_PRODUCTO'));
		$this->add_control(new static_text('NOM_PRODUCTO'));
		$this->add_control(new static_num('CANTIDAD', 1));
	}
}

class dw_salida_bodega extends dw_help_empresa {
	function dw_salida_bodega() {
		$sql = "SELECT	SB.COD_SALIDA_BODEGA
						,convert(varchar(20), SB.FECHA_SALIDA_BODEGA, 103) FECHA_SALIDA_BODEGA
						,U.NOM_USUARIO
						,B.NOM_BODEGA
						,SB.TIPO_DOC
						,SB.COD_DOC
						,dbo.f_get_nro_doc(SB.TIPO_DOC, SB.COD_DOC) NRO_DOC
						,SB.REFERENCIA REFERENCIA
						,CONVERT(varchar, dbo.f_salida_fecha_doc(SB.COD_SALIDA_BODEGA), 103) FECHA_DOC
						,dbo.f_salida_OC_COMERCIAL(SB.COD_SALIDA_BODEGA) COD_ORDEN_COMPRA_COMECIAL
						,dbo.f_salida_NV_COMERCIAL(SB.COD_SALIDA_BODEGA) COD_NOTA_VENTA    
						,dbo.f_salida_VEND_COMERCIAL(SB.COD_SALIDA_BODEGA) NOM_VENDEDOR
				FROM	SALIDA_BODEGA SB, USUARIO U, BODEGA B
				WHERE 	SB.COD_SALIDA_BODEGA = {KEY1}
				AND		SB.COD_USUARIO = U.COD_USUARIO
				AND		SB.COD_BODEGA = B.COD_BODEGA";
		
		parent::dw_help_empresa($sql);
		
		// asigna los formatos
		$this->add_control(new static_text('FECHA_SALIDA_BODEGA'));
		$this->add_control(new static_text('NOM_USUARIO'));
		$this->add_control(new static_text('NOM_BODEGA'));
		$this->add_control(new static_text('TIPO_DOC'));
		$this->add_control(new static_text('NRO_DOC'));
		$this->add_control(new static_text('FECHA_DOC'));
		$this->add_control(new static_text('COD_ORDEN_COMPRA_COMECIAL'));
		$this->add_control(new static_text('COD_NOTA_VENTA'));
		$this->add_control(new static_text('NOM_VENDEDOR'));		
		$this->add_control(new edit_text_upper('REFERENCIA', 100 , 100));
	}
}	

class wi_salida_bodega extends w_input {
	function wi_salida_bodega($cod_item_menu) {
		parent::w_input('salida_bodega', $cod_item_menu);
		// tab salida de bodega
		// DATAWINDOWS SALIDA_BODEGA
		$this->dws['dw_salida_bodega'] = new dw_salida_bodega();
		
		//tab items
		// DATAWINDOWS ITEMS GUIA DESPACHO
		$this->dws['dw_item_salida_bodega'] = new dw_item_salida_bodega();
	}

	function load_record() {
		$COD_SALIDA_BODEGA = $this->get_item_wo($this->current_record, 'COD_SALIDA_BODEGA');
		$this->dws['dw_salida_bodega']->retrieve($COD_SALIDA_BODEGA);	
		$this->dws['dw_item_salida_bodega']->retrieve($COD_SALIDA_BODEGA);
		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = true;
	}

	function get_key() {
		return $this->dws['dw_salida_bodega']->get_item(0, 'COD_SALIDA_BODEGA');
	}

	function print_record() {
		$cod_salida_bodega = $this->get_key();
		$sql= "exec spi_salida_bodega $cod_salida_bodega";

		// reporte'
		$labels = array();
		$labels['strCOD_ITEM_SALIDA_BODEGA'] = $cod_salida_bodega;				
		$file_name = $this->find_file('salida_bodega', 'salida_bodega.xml');				

		$rpt = new print_salida_bodega($sql, $file_name, $labels, "Salida Bodega".$cod_salida_bodega,1);
		$this->_load_record();
		return true;
	}
}

class print_salida_bodega extends reporte_biggi {	
	function print_salida_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false){
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>