<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_familia_producto extends w_input {
	function wi_familia_producto($cod_item_menu) {
		parent::w_input('familia_producto', $cod_item_menu);
		$sql = "select COD_FAMILIA_PRODUCTO, 
						NOM_FAMILIA_PRODUCTO,
						ORDEN
						from FAMILIA_PRODUCTO
						where COD_FAMILIA_PRODUCTO = {KEY1}";
		$this->dws['dw_familia_producto'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_familia_producto']->add_control(new edit_text_upper('NOM_FAMILIA_PRODUCTO', 80, 100));		
		$this->dws['dw_familia_producto']->add_control(new edit_num('ORDEN',12,10));		
		
		// asigna los mandatorys		
		$this->dws['dw_familia_producto']->set_mandatory('NOM_FAMILIA_PRODUCTO', 'Nombre');
		$this->dws['dw_familia_producto']->set_mandatory('ORDEN', 'Orden');
		
	}
	function new_record() {
		$this->dws['dw_familia_producto']->insert_row();
	}
	function load_record() {
		$cod_familia_producto = $this->get_item_wo($this->current_record, 'COD_FAMILIA_PRODUCTO');
		$this->dws['dw_familia_producto']->retrieve($cod_familia_producto);

	}
	function get_key() {
		return $this->dws['dw_familia_producto']->get_item(0, 'COD_FAMILIA_PRODUCTO');
	}
	
	function save_record($db) {
		$COD_FAMILIA_PRODUCTO = $this->get_key();
		$NOM_FAMILIA_PRODUCTO = $this->dws['dw_familia_producto']->get_item(0, 'NOM_FAMILIA_PRODUCTO');
		$ORDEN = $this->dws['dw_familia_producto']->get_item(0, 'ORDEN');
		
		$COD_FAMILIA_PRODUCTO = ($COD_FAMILIA_PRODUCTO=='') ? "null" : $COD_FAMILIA_PRODUCTO;		
    
		$sp = 'spu_familia_producto';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_FAMILIA_PRODUCTO, '$NOM_FAMILIA_PRODUCTO', $ORDEN"; 
		
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_familia_producto = $db->GET_IDENTITY();
				$this->dws['dw_familia_producto']->set_item(0, 'COD_FAMILIA_PRODUCTO', $cod_familia_producto);
			}
			return true;
		}
		return false;		
				
	}	
	
}
?>