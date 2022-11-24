<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_traspaso_bodega extends datawindow {
	function dw_item_traspaso_bodega() {		
		$sql = "SELECT	COD_ITEM_TRASPASO_BODEGA,
						COD_TRASPASO_BODEGA,
						ORDEN,
						ITEM,
						COD_PRODUCTO,
						NOM_PRODUCTO,
						CANTIDAD
				FROM	ITEM_TRASPASO_BODEGA
				WHERE 	COD_TRASPASO_BODEGA = {KEY1}";

		parent::datawindow($sql, 'ITEM_TRASPASO_BODEGA', true, true);	

		$this->add_control(new edit_text('COD_ITEM_TRASPASO_BODEGA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));
		$this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
		
		$this->set_first_focus('COD_PRODUCTO');

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
		
	}
	function insert_row($row=-1) {
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		$this->set_item($row, 'ITEM', $this->row_count());
		return $row;
	}
	function update($db, $cod_traspaso_bodega)	{
		$sp = 'spu_item_traspaso_bodega';
										
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_traspaso_bodega	= $this->get_item($i, 'COD_ITEM_TRASPASO_BODEGA');
			$orden 						= $this->get_item($i, 'ORDEN');
			$item 						= $this->get_item($i, 'ITEM');
			$cod_producto 				= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 				= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 					= $this->get_item($i, 'CANTIDAD');

			$cod_item_traspaso_bodega = ($cod_item_traspaso_bodega=='') ? "null" : $cod_item_traspaso_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_traspaso_bodega
						,$cod_traspaso_bodega
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad";
						
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_traspaso_bodega = $this->get_item($i, 'COD_ITEM_TRASPASO_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_traspaso_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_TRASPASO_BODEGA','TRASPASO_BODEGA', $cod_traspaso_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}

class dw_traspaso_bodega extends datawindow {
	const K_BODEGA_NORMAL = 1;
	
	function dw_traspaso_bodega() {
		$sql = "SELECT	COD_TRASPASO_BODEGA,
						convert(varchar(20), FECHA_TRASPASO_BODEGA, 103) FECHA_TRASPASO_BODEGA,
						U.NOM_USUARIO,
						TIPO_DOC,
						COD_DOC,
						COD_BODEGA_ORIGEN,
						COD_BODEGA_DESTINO,
						REFERENCIA
				FROM	TRASPASO_BODEGA TB, USUARIO U
				WHERE 	COD_TRASPASO_BODEGA = {KEY1}
				AND		TB.COD_USUARIO = U.COD_USUARIO";
		
		parent::datawindow($sql);

		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA";
		
		$this->add_control(new drop_down_dw('COD_BODEGA_ORIGEN', $sql));
		$this->add_control(new drop_down_dw('COD_BODEGA_DESTINO', $sql));
		$this->add_control(new edit_text_upper ('REFERENCIA', 120,100));
		
		$this->set_mandatory('COD_BODEGA_ORIGEN', 'Bodega Origen');
		$this->set_mandatory('COD_BODEGA_DESTINO', 'Bodega Destino');
	}
}

class wi_traspaso_bodega extends w_input {
	function wi_traspaso_bodega($cod_item_menu) {
		parent::w_input('traspaso_bodega', $cod_item_menu);

		$this->dws['dw_traspaso_bodega'] = new dw_traspaso_bodega();

		$this->dws['dw_item_traspaso_bodega'] = new dw_item_traspaso_bodega();

		$this->b_print_visible = false;
	}
	function new_record() {
		$this->dws['dw_traspaso_bodega']->insert_row();	
		$this->dws['dw_traspaso_bodega']->set_item(0, 'FECHA_TRASPASO_BODEGA', $this->current_date());
		$this->dws['dw_traspaso_bodega']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
	}
	function load_record() {
		$COD_TRASPASO_BODEGA = $this->get_item_wo($this->current_record, 'COD_TRASPASO_BODEGA');
		$this->dws['dw_traspaso_bodega']->retrieve($COD_TRASPASO_BODEGA);	
		$this->dws['dw_item_traspaso_bodega']->retrieve($COD_TRASPASO_BODEGA);
		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = false;
	}

	function get_key() {
		return $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_TRASPASO_BODEGA');
	}
	function save_record($db) {
		$cod_traspaso_bodega = $this->get_key();
		$cod_bodega_origen = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_BODEGA_ORIGEN');
		$cod_bodega_destino = $this->dws['dw_traspaso_bodega']->get_item(0, 'COD_BODEGA_DESTINO');
		$tipo_doc = 'SIN_DOCUMENTO';
		$cod_doc= 'null';
		$referencia= $this->dws['dw_traspaso_bodega']->get_item(0, 'REFERENCIA');
		
		$cod_traspaso_bodega = ($cod_traspaso_bodega=='') ? 'null' : $cod_traspaso_bodega;
		$referencia = ($referencia=='') ? 'null' : "'$referencia'";
										
		$sp = 'spu_traspaso_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_traspaso_bodega
	    			,$this->cod_usuario
	    			,$cod_bodega_origen
	    			,$cod_bodega_destino
	    			,$tipo_doc
	    			,$cod_doc
	    			,$referencia";
	    	
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_traspaso_bodega = $db->GET_IDENTITY();
				$this->dws['dw_traspaso_bodega']->set_item(0, 'COD_TRASPASO_BODEGA', $cod_traspaso_bodega);
			}
			
			if (!$this->dws['dw_item_traspaso_bodega']->update($db, $cod_traspaso_bodega))
				return false;
				
			
			return true;
		}
		return false;		
				
	}
}
?>