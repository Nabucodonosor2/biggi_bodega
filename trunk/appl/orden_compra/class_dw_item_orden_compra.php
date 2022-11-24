<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_orden_compra extends datawindow {
	function dw_item_orden_compra() {		
		$sql = "SELECT		COD_ITEM_ORDEN_COMPRA,
							COD_ORDEN_COMPRA,
							ORDEN,
							ITEM,
							COD_PRODUCTO,
							COD_PRODUCTO COD_PRODUCTO_OLD,
							NOM_PRODUCTO,
							CANTIDAD,							
							PRECIO,
							PRECIO PRECIO_H,
							COD_TIPO_TE,
							MOTIVO_TE,
							'' BOTON_PRECIO, -- se utiliza en funcion comun js 'ingreso_TE'
							'' MOTIVO_AUTORIZA_TE -- se utiliza en funcion comun js 'ingreso_TE'
				FROM		ITEM_ORDEN_COMPRA
				WHERE		COD_ORDEN_COMPRA = {KEY1}
				ORDER BY	ORDEN asc";
					
					
		parent::datawindow($sql, 'ITEM_ORDEN_COMPRA', true, true);	
		
		$this->add_control(new edit_text_upper('COD_ITEM_ORDEN_COMPRA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		$this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
		$this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
		
		$this->add_control($control = new edit_precio('PRECIO'));
		$control->set_onChange("change_precio(this);");
		$this->add_control(new edit_text('PRECIO_H',10, 10, 'hidden'));
		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL', "calc_dscto();");
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO

		$this->controls['COD_PRODUCTO']->set_onChange("change_item_orden_compra(this, 'COD_PRODUCTO');");
		
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
		return $row;
	}
	
	function update($db)	{
		$sp = 'spu_item_orden_compra';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_ITEM_ORDEN_COMPRA 	= $this->get_item($i, 'COD_ITEM_ORDEN_COMPRA');
			$COD_ORDEN_COMPRA 		= $this->get_item($i, 'COD_ORDEN_COMPRA');			
			$ORDEN 					= $this->get_item($i, 'ORDEN');
			$ITEM 					= $this->get_item($i, 'ITEM');
			$COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
			$NOM_PRODUCTO 			= $this->get_item($i, 'NOM_PRODUCTO');
			$PRECIO					= $this->get_item($i, 'PRECIO');
			$CANTIDAD 				= $this->get_item($i, 'CANTIDAD');
			$COD_TIPO_TE			= $this->get_item($i, 'COD_TIPO_TE');
			$COD_TIPO_TE			= ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";			
			$MOTIVO_TE		 		= $this->get_item($i, 'MOTIVO_TE');			
 			
			//$PRECIO					= 10;
			//***********      FALTA IMPLEMENTAR BUSCAR POR COD PRODUCTO ***********/  
			
			if ($PRECIO=='') $PRECIO = 0;
			$COD_ITEM_ORDEN_COMPRA = ($COD_ITEM_ORDEN_COMPRA=='') ? "null" : $COD_ITEM_ORDEN_COMPRA;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$COD_ITEM_ORDEN_COMPRA, $COD_ORDEN_COMPRA, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_TIPO_TE, '$MOTIVO_TE'"; 
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_ORDEN_COMPRA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_ORDEN_COMPRA', $COD_ITEM_ORDEN_COMPRA);		
				}
			}
		}
		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_ORDEN_COMPRA = $this->get_item($i, 'COD_ITEM_ORDEN_COMPRA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_ORDEN_COMPRA")){
			return false;				
			}			
		}
		return true;
	}

}
?>