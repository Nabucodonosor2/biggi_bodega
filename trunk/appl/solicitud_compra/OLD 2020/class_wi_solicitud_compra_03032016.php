<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");

class dw_sc_orden_compra extends datawindow {	
	function dw_sc_orden_compra () {
		$sql = "SELECT	 OC.COD_ORDEN_COMPRA
						,OC.COD_ESTADO_ORDEN_COMPRA
						,CONVERT(VARCHAR(10),OC.FECHA_ORDEN_COMPRA,103) FECHA_ORDEN_COMPRA
						,E.NOM_EMPRESA
						,OC.TOTAL_NETO
				FROM ORDEN_COMPRA OC, EMPRESA E
				WHERE OC.TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'
				AND OC.COD_DOC = {KEY1}
				AND OC.COD_EMPRESA = E.COD_EMPRESA
				ORDER BY OC.COD_ORDEN_COMPRA ASC";
		parent::datawindow($sql, 'ITEM_ORDEN_COMPRA_SOLICITUD');
		
		$this->add_control(new static_link('COD_ORDEN_COMPRA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=solicitud_compra&modulo_destino=orden_compra&cod_modulo_destino=[COD_ORDEN_COMPRA]&cod_item_menu=1520&current_tab_page=3'));
		
		$sql = "SELECT COD_ESTADO_ORDEN_COMPRA
					  ,NOM_ESTADO_ORDEN_COMPRA
				FROM ESTADO_ORDEN_COMPRA";
		$this->add_control(new drop_down_dw('COD_ESTADO_ORDEN_COMPRA', $sql));
		
		$this->add_control(new static_text('FECHA_ORDEN_COMPRA'));
		$this->add_control(new static_text('NOM_EMPRESA'));
		$this->add_control(new static_num('TOTAL_NETO'));
	}
	
}
// Copiado desde EMPRESA
class edit_arma_compuesto extends edit_radio_button {	
	function edit_arma_compuesto($field) {
		parent::edit_radio_button($field, 'S', 'N', '', $field);
	}
	function draw_entrable($dato, $record) {
		$value_true = $this->value_true;
		$this->value_true = $this->value_true.'_'.$record;
		$dato = $dato.'_'.$record;
		$draw = parent::draw_entrable($dato, $record);
		$this->value_true = $value_true;
		return $draw;
	}		
	function get_values_from_POST($record) {
		$value_true = $this->value_true.'_'.$record;
		$field_post = $this->group;
		if (isset($_POST[$field_post]) && $_POST[$field_post]==$value_true)
			return $this->value_true;
		else 
			return $this->value_false;
	}
}
class dw_item_solicitud_compra extends datawindow {
	function dw_item_solicitud_compra() {
				
		$sql = "select I.COD_ITEM_SOLICITUD_COMPRA IT_COD_ITEM_SOLICITUD_COMPRA
					,I.COD_SOLICITUD_COMPRA IT_COD_SOLICITUD_COMPRA
					,I.COD_PRODUCTO IT_COD_PRODUCTO
					,I.COD_PRODUCTO IT_COD_PRODUCTO_H
					,P.NOM_PRODUCTO IT_NOM_PRODUCTO
					,I.CANTIDAD_UNITARIA IT_CANTIDAD
					,I.CANTIDAD_TOTAL IT_CANTIDAD_TOTAL
					,I.CANTIDAD_TOTAL IT_CANTIDAD_TOTAL_H
					,I.GENERA_COMPRA IT_GENERA_COMPRA
					,I.COD_EMPRESA IT_COD_EMPRESA
					,I.PRECIO_COMPRA IT_PRECIO_COMPRA
					,I.PRECIO_COMPRA IT_PRECIO_COMPRA_H
					,I.ARMA_COMPUESTO IT_ARMA_COMPUESTO
				from ITEM_SOLICITUD_COMPRA I, PRODUCTO P 
				where I.COD_SOLICITUD_COMPRA = {KEY1}
				  and P.COD_PRODUCTO = I.COD_PRODUCTO";	
					
		parent::datawindow($sql, 'ITEM_SOLICITUD_COMPRA', true, true);	
		$this->add_control(new static_text('IT_COD_PRODUCTO'));
		$this->add_control(new static_text('IT_NOM_PRODUCTO'));
		$this->add_control(new static_text('IT_CANTIDAD_TOTAL'));
		$this->add_control(new edit_check_box('IT_GENERA_COMPRA','S','N'));
		$sql = "select E.COD_EMPRESA IT_COD_EMPRESA, 
						E.ALIAS  IT_ALIAS,
						dbo.f_prod_get_precio_costo (PP.COD_PRODUCTO, PP.COD_EMPRESA, getdate()) PRECIO_COMPRA
				FROM PRODUCTO_PROVEEDOR PP, EMPRESA E
				WHERE PP.COD_PRODUCTO = '{KEY1}' AND
				  		PP.ELIMINADO = 'N' AND
				  		E.COD_EMPRESA = PP.COD_EMPRESA";
		$this->add_control($control = new drop_down_dw('IT_COD_EMPRESA', $sql));
		$control->set_onChange("change_empresa(this);");
		$this->add_control(new static_num('IT_PRECIO_COMPRA'));
		
		// valores hidden
		$this->add_control(new edit_text('IT_COD_ITEM_SOLICITUD_COMPRA', 10, 10, 'hidden'));
		$this->add_control(new edit_text('IT_COD_PRODUCTO_H', 30, 30, 'hidden'));
		$this->add_control(new edit_text('IT_CANTIDAD', 10, 10, 'hidden'));
		$this->add_control(new edit_text('IT_CANTIDAD_TOTAL_H', 10, 10, 'hidden'));
		$this->add_control(new edit_text('IT_PRECIO_COMPRA_H', 10, 10, 'hidden'));
		$this->add_control(new edit_arma_compuesto('IT_ARMA_COMPUESTO'));
	}
	function draw_field($field, $record) {
		if ($field=='IT_COD_EMPRESA') {
			$cod_producto = $this->get_item($record, 'IT_COD_PRODUCTO');
			$this->controls['IT_COD_EMPRESA']->retrieve($cod_producto);
		}
		return parent::draw_field($field, $record);
	}	
	
	function fill_record(&$temp, $record){	
		parent::fill_record($temp, $record);
		$row = $this->redirect($record);
		$detalle = "<img id=\"DETALLE_STOCK_$row\" name=\"DETALLE_STOCK_$row\" src=\"../../../../commonlib/trunk/images/lupa2.jpg\" onclick=\"detalle_stock(this);\" style=\"display:;\">";
		$temp->setVar('ITEM_SOLICITUD_COMPRA.DETALLE_STOCK', $detalle);

	}
	
	function update($db, $COD_SOLICITUD_COMPRA)	{
		$sp = 'spu_item_solicitud_compra';
		
		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_ITEM_SOLICITUD_COMPRA = $this->get_item($i, 'IT_COD_ITEM_SOLICITUD_COMPRA');
			$COD_PRODUCTO = $this->get_item($i, 'IT_COD_PRODUCTO_H');
			$CANTIDAD_UNITARIA = $this->get_item($i, 'IT_CANTIDAD');
			$CANTIDAD_TOTAL = $this->get_item($i, 'IT_CANTIDAD_TOTAL_H');
			$PRECIO_COMPRA = $this->get_item($i, 'IT_PRECIO_COMPRA_H');
			$GENERA_COMPRA = $this->get_item($i, 'IT_GENERA_COMPRA');
			$COD_EMPRESA = $this->get_item($i, 'IT_COD_EMPRESA');
			$ARMA_COMPUESTO = $this->get_item($i, 'IT_ARMA_COMPUESTO');
			
			$COD_ITEM_SOLICITUD_COMPRA = $COD_ITEM_SOLICITUD_COMPRA=='' ? 'null' : $COD_ITEM_SOLICITUD_COMPRA;
			$PRECIO_COMPRA = $PRECIO_COMPRA=='' ? 'null' : $PRECIO_COMPRA;
			$COD_EMPRESA = $COD_EMPRESA=='' ? 'null' : $COD_EMPRESA;

			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			$param = "'$operacion', $COD_ITEM_SOLICITUD_COMPRA,$COD_SOLICITUD_COMPRA,'$COD_PRODUCTO', $CANTIDAD_UNITARIA,$CANTIDAD_TOTAL,$PRECIO_COMPRA,'$GENERA_COMPRA',$COD_EMPRESA,'$ARMA_COMPUESTO'";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$COD_ITEM_SOLICITUD_COMPRA = $this->get_item($i, 'IT_COD_ITEM_SOLICITUD_COMPRA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_SOLICITUD_COMPRA"))
				return false;		
		}			
		return true;
	}
}

class wi_solicitud_compra extends w_input {
	const K_ESTADO_SOLICITADO 	= 1;
	const K_ESTADO_APROBADO 	= 2;
	const K_ESTADO_ANULADO		= 3;	
	
	function wi_solicitud_compra($cod_item_menu) {
		parent::w_input('solicitud_compra', $cod_item_menu);

		$sql = "SELECT SC.COD_SOLICITUD_COMPRA
					,convert(varchar(20), SC.FECHA_SOLICITUD_COMPRA, 103) FECHA_SOLICITUD_COMPRA
					,SC.COD_USUARIO
					,U.NOM_USUARIO
					,SC.COD_ESTADO_SOLICITUD_COMPRA
					,SC.COD_ESTADO_SOLICITUD_COMPRA COD_ESTADO_SOLICITUD_COMPRA_H
					,ESC.NOM_ESTADO_SOLICITUD_COMPRA
					,SC.REFERENCIA
					,SC.COD_PRODUCTO
					,P.NOM_PRODUCTO
					,SC.CANTIDAD
					,SC.TERMINADO_COMPUESTO TERMINADO
					,SC.TERMINADO_COMPUESTO COMPUESTO
				FROM SOLICITUD_COMPRA SC, USUARIO U, PRODUCTO P, ESTADO_SOLICITUD_COMPRA ESC
				WHERE COD_SOLICITUD_COMPRA = {KEY1}
					AND	SC.COD_ESTADO_SOLICITUD_COMPRA = ESC.COD_ESTADO_SOLICITUD_COMPRA
					AND U.COD_USUARIO = SC.COD_USUARIO
					AND P.COD_PRODUCTO = SC.COD_PRODUCTO";
				
		$this->dws['dw_solicitud_compra'] = new datawindow($sql);
		
				// asigna los formatos
		$this->dws['dw_solicitud_compra']->add_control(new edit_text_upper('REFERENCIA', 100, 100));
		$this->dws['dw_solicitud_compra']->add_controls_producto_help();
		$this->dws['dw_solicitud_compra']->add_control(new static_text('NOM_ESTADO_SOLICITUD_COMPRA'));
		$this->dws['dw_solicitud_compra']->add_control(new edit_text('COD_ESTADO_SOLICITUD_COMPRA',10,10, 'hidden'));
		$this->dws['dw_solicitud_compra']->add_control(new edit_text('COD_ESTADO_SOLICITUD_COMPRA_H',10,10, 'hidden'));
		$this->dws['dw_solicitud_compra']->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("change_cantidad(this);");
		$this->dws['dw_solicitud_compra']->add_control($control = new edit_radio_button('TERMINADO','T','N', 'Terminado', 'TERMINADO_COMPUESTO'));
		$control->set_onChange("terminado_compuesto(this);");
		$this->dws['dw_solicitud_compra']->add_control($control = new edit_radio_button('COMPUESTO','C','N', 'Compuesto', 'TERMINADO_COMPUESTO'));
		$control->set_onChange("terminado_compuesto(this);");
		
		// asigna los mandatorys		
		$this->dws['dw_solicitud_compra']->set_mandatory('REFERENCIA', 'Referencia');
		
		$this->dws['dw_item_solicitud_compra'] = new dw_item_solicitud_compra();
		//$this->dws['dw_item_solicitud_compra']->set_mandatory('IT_COD_EMPRESA', 'Empresa');
		
		$this->dws['dw_sc_orden_compra'] = new dw_sc_orden_compra();

		$this->add_auditoria('COD_ESTADO_SOLICITUD_COMPRA');
		$this->add_auditoria('COD_PRODUCTO');
		$this->add_auditoria('CANTIDAD');
	}
	function new_record() {
		$this->dws['dw_solicitud_compra']->insert_row();
		$this->dws['dw_solicitud_compra']->set_item(0, 'FECHA_SOLICITUD_COMPRA', substr($this->current_date_time(), 0, 16));
		$this->dws['dw_solicitud_compra']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_solicitud_compra']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->dws['dw_solicitud_compra']->set_item(0, 'COD_ESTADO_SOLICITUD_COMPRA', self::K_ESTADO_SOLICITADO);
		$this->dws['dw_solicitud_compra']->set_item(0, 'NOM_ESTADO_SOLICITUD_COMPRA', 'SOLICITADO');
		$this->dws['dw_solicitud_compra']->set_item(0, 'CANTIDAD', 1);
		$this->dws['dw_solicitud_compra']->set_item(0, 'TERMINADO', 'T');
		$this->dws['dw_solicitud_compra']->set_item(0, 'COMPUESTO', 'N');
	}
	function load_record() {
		$COD_SOLICITUD_COMPRA = $this->get_item_wo($this->current_record, 'COD_SOLICITUD_COMPRA');
		$this->dws['dw_solicitud_compra']->retrieve($COD_SOLICITUD_COMPRA);
		$this->dws['dw_item_solicitud_compra']->retrieve($COD_SOLICITUD_COMPRA);
		$this->dws['dw_sc_orden_compra']->retrieve($COD_SOLICITUD_COMPRA);
		$COD_ESTADO_SOLICITUD_COMPRA = $this->dws['dw_solicitud_compra']->get_item(0, 'COD_ESTADO_SOLICITUD_COMPRA');
		
		if ($COD_ESTADO_SOLICITUD_COMPRA == self::K_ESTADO_SOLICITADO) {
			$sql = "select	COD_ESTADO_SOLICITUD_COMPRA,
							NOM_ESTADO_SOLICITUD_COMPRA
					from	ESTADO_SOLICITUD_COMPRA
					order by	COD_ESTADO_SOLICITUD_COMPRA";
			
			unset($this->dws['dw_solicitud_compra']->controls['COD_ESTADO_SOLICITUD_COMPRA']);
			$this->dws['dw_solicitud_compra']->add_control(new drop_down_dw('COD_ESTADO_SOLICITUD_COMPRA', $sql, 140));
			$this->dws['dw_solicitud_compra']->controls['NOM_ESTADO_SOLICITUD_COMPRA']->type = 'hidden';
			
		}
		
		$this->b_print_visible	 = true;
		
		if ($COD_ESTADO_SOLICITUD_COMPRA == self::K_ESTADO_APROBADO){
			$this->b_delete_visible  = false;
			
			$sql = "select	COD_ESTADO_SOLICITUD_COMPRA,
							NOM_ESTADO_SOLICITUD_COMPRA
					from	ESTADO_SOLICITUD_COMPRA
					where COD_ESTADO_SOLICITUD_COMPRA <> 1
					order by	COD_ESTADO_SOLICITUD_COMPRA";
			
			unset($this->dws['dw_solicitud_compra']->controls['COD_ESTADO_SOLICITUD_COMPRA']);
			$this->dws['dw_solicitud_compra']->add_control(new drop_down_dw('COD_ESTADO_SOLICITUD_COMPRA', $sql, 140));
			$this->dws['dw_solicitud_compra']->controls['NOM_ESTADO_SOLICITUD_COMPRA']->type = 'hidden';
			
			unset($this->dws['dw_solicitud_compra']->controls['REFERENCIA']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('REFERENCIA'));
			unset($this->dws['dw_solicitud_compra']->controls['COD_PRODUCTO']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('COD_PRODUCTO'));	
			unset($this->dws['dw_solicitud_compra']->controls['CANTIDAD']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('CANTIDAD'));
			unset($this->dws['dw_solicitud_compra']->controls['NOM_PRODUCTO']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('NOM_PRODUCTO'));
			unset($this->dws['dw_solicitud_compra']->controls['TERMINADO']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('TERMINADO'));	
			unset($this->dws['dw_solicitud_compra']->controls['COMPUESTO']);
			$this->dws['dw_solicitud_compra']->add_control(new static_text('COMPUESTO'));
			
			
			
		}else if ($COD_ESTADO_SOLICITUD_COMPRA == self::K_ESTADO_ANULADO){	
			$this->b_no_save_visible = false;
			$this->b_save_visible 	 = false;
			$this->b_modify_visible  = false;
			$this->b_delete_visible  = false;
		}
	}
	function get_key() {
		return $this->dws['dw_solicitud_compra']->get_item(0, 'COD_SOLICITUD_COMPRA');
	}
	
	function save_record($db){
		$sp = 'spu_solicitud_compra';
		$COD_SOLICITUD_COMPRA			= $this->get_key();
		$COD_USUARIO					= $this->dws['dw_solicitud_compra']->get_item(0, 'COD_USUARIO');
		$COD_ESTADO_SOLICITUD_COMPRA	= $this->dws['dw_solicitud_compra']->get_item(0, 'COD_ESTADO_SOLICITUD_COMPRA');
		$COD_PRODUCTO					= $this->dws['dw_solicitud_compra']->get_item(0, 'COD_PRODUCTO');
		$CANTIDAD						= $this->dws['dw_solicitud_compra']->get_item(0, 'CANTIDAD');
		$REFERENCIA						= $this->dws['dw_solicitud_compra']->get_item(0, 'REFERENCIA');
		$TERMINADO						= $this->dws['dw_solicitud_compra']->get_item(0, 'TERMINADO');
		
		$COD_SOLICITUD_COMPRA			= $COD_SOLICITUD_COMPRA=='' ? 'null' : $COD_SOLICITUD_COMPRA;
		$TERMINADO						= $TERMINADO == 'T' ? 'T' :'C';
				
	    if ($this->is_new_record()){
	    	$operacion = 'INSERT';
			$COD_ESTADO_ANTERIOR = self::K_ESTADO_SOLICITADO;
	    }
	    else{
	    	$operacion = 'UPDATE';
			$sql = "SELECT COD_ESTADO_SOLICITUD_COMPRA 
					FROM SOLICITUD_COMPRA 
					WHERE COD_SOLICITUD_COMPRA = $COD_SOLICITUD_COMPRA";
			$result = $db->build_results($sql);
			$COD_ESTADO_ANTERIOR = $result[0]['COD_ESTADO_SOLICITUD_COMPRA'];
	    }
	    
	    $param	= "'$operacion'
	    		  ,$COD_SOLICITUD_COMPRA
	    		  ,$COD_USUARIO
	    		  ,$COD_ESTADO_SOLICITUD_COMPRA
	    		  ,'$COD_PRODUCTO'
	    		  ,$CANTIDAD
	    		  ,'$REFERENCIA'
	    		  ,'$TERMINADO'";
		
	    if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()){
				$COD_SOLICITUD_COMPRA = $db->GET_IDENTITY();
				$this->dws['dw_solicitud_compra']->set_item(0, 'COD_SOLICITUD_COMPRA', $COD_SOLICITUD_COMPRA);
			}
		 	if (!$this->dws['dw_item_solicitud_compra']->update($db, $COD_SOLICITUD_COMPRA))
		 		return false;
			 
			if ($COD_ESTADO_ANTERIOR==self::K_ESTADO_SOLICITADO && $COD_ESTADO_SOLICITUD_COMPRA==self::K_ESTADO_APROBADO){
			    $param	= "'COMPRAR', $COD_SOLICITUD_COMPRA, $this->cod_usuario";
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
			}	
			return true;
		}	
		return false;		
	}
	
	function print_record() {
		$cod_solicitud = $this->get_key();
		$sql = "exec spi_solicitud_compra $cod_solicitud";
		// reporte
		$labels = array();
		$labels['strCOD_SOLICITUD'] = $cod_solicitud;					
		$file_name = $this->find_file('solicitud_compra', 'solicitud_compra.xml');					
		$rpt = new print_solicitud_compra($sql, $file_name, $labels, "Solicitud Compra ".$cod_solicitud.".pdf", 1);
		$this->_load_record();
		return true;
	}	
}

class print_solicitud_compra extends reporte_biggi {	
	function print_solicitud_compra($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte_biggi($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}

?>