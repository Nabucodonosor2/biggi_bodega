<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_factura_rechazada extends w_input {
	function wi_factura_rechazada($cod_item_menu) {
		parent::w_input('factura_rechazada', $cod_item_menu);

		$sql = "SELECT COD_FACTURA_RECHAZADA
					  ,NRO_FACTURA
					  ,FR.COD_FACTURA
					  ,CONVERT(VARCHAR, FECHA_RECHAZO, 103) FECHA_RECHAZO
					  ,CONVERT(VARCHAR, FECHA_RESUELTA, 103) FECHA_RESUELTA
					  ,RESUELTA
					  ,COD_USUARIO_RESUELTA
					  ,NOM_USUARIO
					  ,FR.OBS
				FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
					,FACTURA F
				WHERE FR.COD_FACTURA_RECHAZADA = {KEY1}
				AND FR.COD_FACTURA = F.COD_FACTURA
				ORDER BY COD_FACTURA_RECHAZADA DESC";
						
		$this->dws['dw_factura_rechazada'] = new datawindow($sql);

		// asigna los formatos
		$this->dws['dw_factura_rechazada']->add_control(new edit_check_box('RESUELTA','S','N'));
		$this->dws['dw_factura_rechazada']->add_control(new edit_text_multiline('OBS',90, 3));
	}
	
	function load_record(){
		$cod_factura_rechazada = $this->get_item_wo($this->current_record, 'COD_FACTURA_RECHAZADA');
		$this->dws['dw_factura_rechazada']->retrieve($cod_factura_rechazada);
		$cod_factura = $this->dws['dw_factura_rechazada']->get_item(0, 'COD_FACTURA');
		$this->dws['dw_factura_rechazada']->add_control(new static_link('NRO_FACTURA', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=factura_rechazada&modulo_destino=factura&cod_modulo_destino='.$cod_factura.'&cod_item_menu=1535'));
	}
	
	function get_key() {
		return $this->dws['dw_factura_rechazada']->get_item(0, 'COD_FACTURA_RECHAZADA');
	}
	
	function save_record($db){
		$COD_FACTURA_RECHAZADA	= $this->get_key();
		$RESUELTA				= $this->dws['dw_factura_rechazada']->get_item(0, 'RESUELTA');		
		$OBS					= $this->dws['dw_factura_rechazada']->get_item(0, 'OBS');
		
		$OBS					= ($OBS=='') ? "null" : "'$OBS'";		
    
		$sp = 'spu_factura_rechazada';

	    $param	= "'UPDATE'
	    		  ,$COD_FACTURA_RECHAZADA
	    		  ,NULL
	    		  ,'$RESUELTA'
	    		  ,$OBS
	    		  ,".$this->cod_usuario; 
		
		if ($db->EXECUTE_SP($sp, $param)){
			return true;
		}
		return false;
	}
}
?>