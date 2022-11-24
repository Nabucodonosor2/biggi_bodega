<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_bodega extends w_input {
	const K_BODEGA_NORMAL	= 1;
	const K_BODEGA_ARRIENDO	= 2;
	function wi_bodega($cod_item_menu) {
		parent::w_input('bodega', $cod_item_menu);

		$sql = "SELECT	B.COD_BODEGA
						,B.NOM_BODEGA
						,'Tipo: '+TB.NOM_TIPO_BODEGA NOM_TIPO_BODEGA
						,TB.COD_TIPO_BODEGA
						,case
							when TB.COD_TIPO_BODEGA = ".self::K_BODEGA_ARRIENDO." then ''
						else 'none'
						end DISPLAY_TIPO_BODEGA
				FROM BODEGA B, TIPO_BODEGA TB
				WHERE B.COD_BODEGA = {KEY1} 
				AND B.COD_TIPO_BODEGA = TB.COD_TIPO_BODEGA
				ORDER BY B.COD_BODEGA";

		$this->dws['dw_bodega'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_bodega']->add_control(new edit_text_upper('NOM_BODEGA', 80, 80));
		
		
		$sql = "select P.COD_PRODUCTO
						,P.NOM_PRODUCTO
						,dbo.f_bodega_stock(P.COD_PRODUCTO, B.COD_BODEGA, getdate()) CANTIDAD
				from BODEGA B, PRODUCTO P
				where B.COD_BODEGA = {KEY1}
  				  and dbo.f_bodega_stock(P.COD_PRODUCTO, B.COD_BODEGA, getdate()) > 0
  				order by P.COD_PRODUCTO";
		$this->dws['dw_stock_bodega'] = new datawindow($sql, 'STOCK_BODEGA');
		
		// asigna los mandatorys
		$this->dws['dw_bodega']->set_mandatory('NOM_BODEGA', 'Nombre Bodega');
	}
	
	function new_record() {
		$this->dws['dw_bodega']->insert_row();
		$this->dws['dw_bodega']->set_item(0, 'COD_TIPO_BODEGA', self::K_BODEGA_NORMAL);
		$this->dws['dw_bodega']->set_item(0, 'DISPLAY_TIPO_BODEGA', 'none');
	}

	function load_record() {
		$COD_BODEGA = $this->get_item_wo($this->current_record, 'COD_BODEGA');
		$this->dws['dw_bodega']->retrieve($COD_BODEGA);
		$this->dws['dw_stock_bodega']->retrieve($COD_BODEGA);
	}

	function get_key() {
		return $this->dws['dw_bodega']->get_item(0, 'COD_BODEGA');
	}

	function save_record($db) {
		$COD_BODEGA = $this->get_key();
		$NOM_BODEGA = $this->dws['dw_bodega']->get_item(0, 'NOM_BODEGA');
		$COD_TIPO_BODEGA = $this->dws['dw_bodega']->get_item(0, 'COD_TIPO_BODEGA');

		$COD_BODEGA = ($COD_BODEGA=='') ? "null" : $COD_BODEGA;
		$NOM_BODEGA = ($NOM_BODEGA=='') ? "null" : $NOM_BODEGA;
		$COD_TIPO_BODEGA = ($COD_TIPO_BODEGA=='') ? "null" : $COD_TIPO_BODEGA;

		$sp = 'spu_bodega';

	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    $param = "'$operacion', $COD_BODEGA, '$NOM_BODEGA', $COD_TIPO_BODEGA";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_BODEGA = $db->GET_IDENTITY();
				$this->dws['dw_bodega']->set_item(0, 'COD_BODEGA', $COD_BODEGA);
			}
			return true;
		}
		return false;							
	}
}
?>