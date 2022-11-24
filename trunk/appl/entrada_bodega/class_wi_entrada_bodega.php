<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");

class dw_item_entrada_bodega_base extends datawindow {
	function dw_item_entrada_bodega_base() {		
		$sql = "SELECT	IEB.COD_ITEM_ENTRADA_BODEGA,
						IEB.COD_ENTRADA_BODEGA,
						IEB.ORDEN,
						IEB.ITEM,
						IEB.COD_PRODUCTO,
						IEB.NOM_PRODUCTO,
						IEB.CANTIDAD,
						IEB.PRECIO
				FROM	ITEM_ENTRADA_BODEGA IEB
				WHERE 	IEB.COD_ENTRADA_BODEGA =  {KEY1}";

		parent::datawindow($sql, 'ITEM_ENTRADA_BODEGA', true, true);	

		$this->add_control(new edit_text('COD_ITEM_ENTRADA_BODEGA',10, 10, 'hidden'));
		$this->add_control(new edit_num('ORDEN',4, 10));
		$this->add_control(new edit_text('ITEM',4 , 5));
		$this->add_control(new edit_cantidad('CANTIDAD',12,10));

//		$this->add_control(new computed('PRECIO', 0));
		$this->add_control(new edit_num('PRECIO',10, 10));
		
		$this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
		$this->accumulate('TOTAL');
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
	function update($db, $cod_entrada_bodega)	{
		$sp = 'spu_item_entrada_bodega';

		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_item_entrada_bodega= $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA');
			$orden 					= $this->get_item($i, 'ORDEN');
			$item 					= $this->get_item($i, 'ITEM');
			$cod_producto 			= $this->get_item($i, 'COD_PRODUCTO');
			$nom_producto 			= $this->get_item($i, 'NOM_PRODUCTO');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$precio 				= $this->get_item($i, 'PRECIO');

			$cod_item_entrada_bodega = ($cod_item_entrada_bodega=='') ? "null" : $cod_item_entrada_bodega;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion'
						,$cod_item_entrada_bodega
						,$cod_entrada_bodega
						,$orden
						,'$item'
						,'$cod_producto'
						,'$nom_producto'
						,$cantidad
						,$precio";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$cod_item_entrada_bodega = $this->get_item($i, 'COD_ITEM_ENTRADA_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_item_entrada_bodega")){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0) {
			$parametros_sp = "'ITEM_ENTRADA_BODEGA','ENTRADA_BODEGA', $cod_entrada_bodega";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
				return false;
		}
		return true;
	}
}

class dw_entrada_bodega extends datawindow {
	const K_BODEGA_NORMAL = 1;
	
	function dw_entrada_bodega() {
		$sql = "SELECT	EB.COD_ENTRADA_BODEGA,
						convert(varchar(20), EB.FECHA_ENTRADA_BODEGA, 103) FECHA_ENTRADA_BODEGA,
						U.NOM_USUARIO,
						EB.TIPO_DOC,
						EB.COD_DOC,
						dbo.f_get_nro_doc(EB.TIPO_DOC, EB.COD_DOC) NRO_DOC,
						EB.COD_BODEGA,
						EB.REFERENCIA REFERENCIA,
						EB.OBS,
						EB.NRO_FACTURA_PROVEEDOR NRO_FA_PROVEEDOR,
						CONVERT(VARCHAR(10),EB.FECHA_FACTURA_PROVEEDOR,103) FECHA_FA_PROVEEDOR
				FROM	ENTRADA_BODEGA EB, USUARIO U
				WHERE 	EB.COD_ENTRADA_BODEGA =  {KEY1}
				AND		EB.COD_USUARIO = U.COD_USUARIO";
		
		parent::datawindow($sql);
		
		// asigna los formatos
		$sql = "select COD_BODEGA
						,NOM_BODEGA
				from BODEGA
				where COD_TIPO_BODEGA = ".self::K_BODEGA_NORMAL;
		$this->add_control(new drop_down_dw('COD_BODEGA', $sql));

		$this->add_control(new edit_text_upper('TIPO_DOC', 30, 100));
		$this->add_control(new static_text('NRO_DOC'));

		$this->add_control(new edit_text_upper('REFERENCIA', 100 , 100));
		$this->add_control(new edit_text_multiline('OBS',59,1));
	
		
		$this->set_mandatory('COD_BODEGA', 'Bodega');
	}
}

class wi_entrada_bodega_base extends w_input {
	function wi_entrada_bodega_base($cod_item_menu) {
		parent::w_input('entrada_bodega', $cod_item_menu);
		// tab salida de bodega
		// DATAWINDOWS ENTRADA_BODEGA
		$this->dws['dw_entrada_bodega'] = new dw_entrada_bodega();

		//tab items
		// DATAWINDOWS ITEMS GUIA DESPACHO
		$this->dws['dw_item_entrada_bodega'] = new dw_item_entrada_bodega();

		//************
		$this->b_print_visible = false;
		//************
	}
	function new_record() {
		$this->dws['dw_entrada_bodega']->insert_row();	
		$this->dws['dw_entrada_bodega']->set_item(0, 'FECHA_ENTRADA_BODEGA', $this->current_date());
		$this->dws['dw_entrada_bodega']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
	}
	function load_record() {
		$COD_ENTRADA_BODEGA = $this->get_item_wo($this->current_record, 'COD_ENTRADA_BODEGA');
		$this->dws['dw_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);	
		$this->dws['dw_item_entrada_bodega']->retrieve($COD_ENTRADA_BODEGA);

		
		$this->b_delete_visible  = false;
		$this->b_save_visible 	 = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible	 = false;
		$this->b_print_visible	 = false;
	}

	function get_key() {
		return $this->dws['dw_entrada_bodega']->get_item(0, 'COD_ENTRADA_BODEGA');
	}
	function save_record($db) {
		$cod_entrada_bodega = $this->get_key();
		$cod_bodega = $this->dws['dw_entrada_bodega']->get_item(0, 'COD_BODEGA');
		$tipo_doc = $this->dws['dw_entrada_bodega']->get_item(0, 'TIPO_DOC');
		$referencia = $this->dws['dw_entrada_bodega']->get_item(0, 'REFERENCIA');
		$obs = $this->dws['dw_entrada_bodega']->get_item(0, 'OBS');
		
		$cod_entrada_bodega = ($cod_entrada_bodega=='') ? 'null' : $cod_entrada_bodega;
		
		$sp = 'spu_entrada_bodega';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    		    
	    $param	= "'$operacion'
	    			,$cod_entrada_bodega
	    			,$this->cod_usuario
	    			,$cod_bodega
	    			,'SIN_DOCUMENTO'
	    			,null
	    			,'$referencia'";
	    	
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_entrada_bodega = $db->GET_IDENTITY();
				$this->dws['dw_entrada_bodega']->set_item(0, 'COD_ENTRADA_BODEGA', $cod_entrada_bodega);
			}
			
			if (!$this->dws['dw_item_entrada_bodega']->update($db, $cod_entrada_bodega))
				return false;
				
			
			return true;
		}
		return false;		
				
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_entrada_bodega.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class dw_item_entrada_bodega extends dw_item_entrada_bodega_base {
		function dw_item_entrada_bodega() {
			parent::dw_item_entrada_bodega_base(); 
		}
	}
		
	class wi_entrada_bodega extends wi_entrada_bodega_base {
		function wi_entrada_bodega($cod_item_menu) {
			parent::wi_entrada_bodega_base($cod_item_menu); 
		}
	}
}
?>