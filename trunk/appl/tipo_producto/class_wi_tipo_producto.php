<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : WI_TIPO_PRODUCTO
*/
class wi_tipo_producto extends w_input {
	function wi_tipo_producto($cod_item_menu) {
		parent::w_input('tipo_producto', $cod_item_menu);

		$sql = "select COD_TIPO_PRODUCTO, 
						NOM_TIPO_PRODUCTO,														 
						ORDEN
						from TIPO_PRODUCTO
						where COD_TIPO_PRODUCTO = {KEY1}";
		$this->dws['dw_tipo_producto'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_tipo_producto']->add_control(new edit_text_upper('NOM_TIPO_PRODUCTO', 80, 100));		
		$this->dws['dw_tipo_producto']->add_control(new edit_num('ORDEN',12,10));	
		
		// asigna los mandatorys
		$this->dws['dw_tipo_producto']->set_mandatory('NOM_TIPO_PRODUCTO', 'Tipo Producto');
		$this->dws['dw_tipo_producto']->set_mandatory('ORDEN', 'Orden');

	}
	
	function new_record() {
		$this->dws['dw_tipo_producto']->insert_row();		

	}
	function load_record() {
		$cod_tipo_producto = $this->get_item_wo($this->current_record, 'COD_TIPO_PRODUCTO');
		$this->dws['dw_tipo_producto']->retrieve($cod_tipo_producto);
	}
	function get_key() {
		return $this->dws['dw_tipo_producto']->get_item(0, 'COD_TIPO_PRODUCTO');
	}
	
	function save_record($db) {
		$COD_TIPO_PRODUCTO = $this->get_key();
		$NOM_TIPO_PRODUCTO = $this->dws['dw_tipo_producto']->get_item(0, 'NOM_TIPO_PRODUCTO');
		$ORDEN = $this->dws['dw_tipo_producto']->get_item(0, 'ORDEN');

		$COD_TIPO_PRODUCTO = ($COD_TIPO_PRODUCTO=='') ? "null" : $COD_TIPO_PRODUCTO;		
    
		$sp = 'spu_tipo_producto';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_TIPO_PRODUCTO, '$NOM_TIPO_PRODUCTO', $ORDEN"; 
		
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_tipo_producto = $db->GET_IDENTITY();
				$this->dws['dw_tipo_producto']->set_item(0, 'COD_TIPO_PRODUCTO', $cod_tipo_producto);
			}
			return true;
		}
		return false;		
				
	}
	
}
////////////////////////

?>