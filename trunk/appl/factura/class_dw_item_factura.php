<?php
class dw_item_factura_base extends datawindow {
	const K_ESTADO_SII_EMITIDA 			= 1;
	
	function dw_item_factura_base() {
		$sql = " SELECT ifa.COD_ITEM_FACTURA,
						ifa.COD_FACTURA,
						ifa.ORDEN,
						ifa.ITEM,
						ifa.COD_PRODUCTO,
						ifa.COD_PRODUCTO COD_PRODUCTO_OLD,
						ifa.NOM_PRODUCTO,
						ifa.CANTIDAD,
						ifa.PRECIO,
						ifa.COD_ITEM_DOC,
						ifa.TIPO_DOC,
						case ifa.TIPO_DOC
							when 'ITEM_NOTA_VENTA' then dbo.f_nv_cant_por_facturar(ifa.COD_ITEM_DOC, default)
							when 'ITEM_GUIA_DESPACHO' then dbo.f_gd_cant_por_facturar(ifa.COD_ITEM_DOC, default)
						end CANTIDAD_POR_FACTURAR,
						case
							when f.COD_DOC IS not NULL and f.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_FACT,	
						case
							when f.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO, -- se utiliza en funcion comun js 'ingreso_TE'
						COD_TIPO_GAS,
						COD_TIPO_ELECTRICIDAD
				FROM    ITEM_FACTURA ifa, factura f
				WHERE   f.cod_factura=ifa.cod_factura and ifa.COD_FACTURA = {KEY1}
				ORDER BY ORDEN";
		
		 
		parent::datawindow($sql, 'ITEM_FACTURA', true, true);
		
		$this->add_control(new edit_text_upper('COD_ITEM_FACTURA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text_upper('ITEM',4, 5));
		$this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
		$control->set_onChange("this.value = valida_ct_x_facturar(this);");
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		$this->add_control(new static_num('CANTIDAD_POR_FACTURAR',1));
		$this->add_control(new computed('PRECIO', 0));
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_factura(this, 'COD_PRODUCTO');");
		
		$sql = "select COD_TIPO_GAS, NOM_TIPO_GAS
				from TIPO_GAS
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_GAS', $sql, 80));					

		$sql = "select COD_TIPO_ELECTRICIDAD, NOM_TIPO_ELECTRICIDAD
				from TIPO_ELECTRICIDAD
				order by ORDEN";
		$this->add_control(new drop_down_dw('COD_TIPO_ELECTRICIDAD', $sql, 80));					
		
		$this->set_first_focus('COD_PRODUCTO');


		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ITEM', $this->row_count());
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'TD_DISPLAY_CANT_POR_FACT', 'none');
		return $row;
	}
	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);
		// si existe COD_FACTURA no despliega boton "-".
		$COD_FACTURA = $this->get_item(0, 'COD_FACTURA');
		if ($COD_FACTURA != ''){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT COD_ESTADO_DOC_SII 
					FROM FACTURA
					WHERE COD_FACTURA = $COD_FACTURA";
			$result = $db->build_results($sql);
			if($result[0]['COD_ESTADO_DOC_SII'] != 1){
				$row = $this->redirect($record);
				$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
				$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
			}		
		}
		
		/*if ($COD_FACTURA != ''){
			 
			$row = $this->redirect($record);
			$eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
		}*/
	}
	
	
	function fill_template(&$temp) {
		parent::fill_template($temp);
		
		if ($this->row_count()==0)
			$COD_FACTURA = '';		// debe ser == '' para que se agregue el boton "+"
		else
			$COD_FACTURA = $this->get_item(0, 'COD_FACTURA');
		
		if ($COD_FACTURA != ''){
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT COD_ESTADO_DOC_SII 
					FROM FACTURA
					WHERE COD_FACTURA = $COD_FACTURA";
			$result = $db->build_results($sql);
			if($result[0]['COD_ESTADO_DOC_SII'] != 1){
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
				$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
			}		
		}	
			
		/*if ($COD_FACTURA != ''){ 
			
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}*/
		if ($this->b_add_line_visible) {
			if ($this->entrable){
				if ($COD_FACTURA != '')
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_fa(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
				else {
					$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_fa(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
					$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
				}
			}
			else 
				$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
		}
	}
	function update($db)	{
		$sp = 'spu_item_factura';
		
		for ($i = 0; $i < $this->row_count(); $i++) {
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$COD_ITEM_FACTURA		= $this->get_item($i, 'COD_ITEM_FACTURA');
			$COD_FACTURA 			= $this->get_item($i, 'COD_FACTURA');
			$ORDEN 					= $this->get_item($i, 'ORDEN');
			$ITEM 					= $this->get_item($i, 'ITEM');
			$COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO 			= $this->get_item($i, 'NOM_PRODUCTO');
			$PRECIO 				= $this->get_item($i, 'PRECIO');
			$COD_ITEM_DOC			= $this->get_item($i, 'COD_ITEM_DOC');			
			$CANTIDAD 				= $this->get_item($i, 'CANTIDAD');
			if($CANTIDAD == 0)
				continue;
			
			$COD_TIPO_TE			= $this->get_item($i, 'COD_TIPO_TE');
			$COD_TIPO_GAS 			= $this->get_item($i, 'COD_TIPO_GAS');
			$COD_TIPO_ELECTRICIDAD 	= $this->get_item($i, 'COD_TIPO_ELECTRICIDAD');
			$TIPO_DOC 				= $this->get_item($i, 'TIPO_DOC');
			$MOTIVO_TE		 		= $this->get_item($i, 'MOTIVO_TE');
			
			if ($PRECIO=='') $PRECIO = 0;		
		
			$COD_ITEM_FACTURA   	= ($COD_ITEM_FACTURA=='') ? "null" : $COD_ITEM_FACTURA;
			$COD_ITEM_DOC			= ($COD_ITEM_DOC=='') ? "null" : $COD_ITEM_DOC;
			$COD_TIPO_GAS 			= ($COD_TIPO_GAS=='') ? "null" : $COD_TIPO_GAS;
			$COD_TIPO_ELECTRICIDAD 	= ($COD_TIPO_ELECTRICIDAD=='') ? "null" : $COD_TIPO_ELECTRICIDAD;
			$COD_TIPO_TE			= ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";			
			$MOTIVO_TE		 		= ($MOTIVO_TE =='') ? "null" : "'".$MOTIVO_TE."'";
			$TIPO_DOC		 		= ($TIPO_DOC =='') ? "null" : "'$TIPO_DOC'";
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			else if ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';		
				
			$param = "'$operacion', $COD_ITEM_FACTURA, $COD_FACTURA, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_ITEM_DOC, $COD_TIPO_TE, $MOTIVO_TE, $TIPO_DOC, $COD_TIPO_GAS, $COD_TIPO_ELECTRICIDAD";
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_FACTURA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_FACTURA', $COD_ITEM_FACTURA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_FACTURA = $this->get_item($i, 'COD_ITEM_FACTURA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_FACTURA"))
				return false;
		}	
		return true;
	}
}	
?>