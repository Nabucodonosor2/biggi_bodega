<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");

class wo_pago_faprov_comercial extends w_output_biggi{
	const K_PARAM_DIRECTORIO = 31;
	
   	function wo_pago_faprov_comercial(){
   		parent::w_base('pago_faprov_comercial', $_REQUEST['cod_item_menu']);
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$cod_usuario = $this->cod_usuario;
   		$db->EXECUTE_SP("spu_pago_faprov_comercial", "$cod_usuario");

		if(K_CLIENTE == 'TODOINOX') {		
	   		$this->load_pago('COMERCIAL', 'TODOINOX');
	   		$this->load_pago('BODEGA', 'TODOINOX');
	   		$this->load_pago('RENTAL', 'TODOINOX');
		}else if(K_CLIENTE == 'BODEGA') {		
	   		$this->load_pago('COMERCIAL', 'BODEGA');
	   		$this->load_pago('RENTAL', 'BODEGA');
	   		$this->load_pago('TODOINOX', 'BODEGA');
		}
		
		$sql = "SELECT COD_PAGO_FAPROV_COMERCIAL
					  ,CONVERT(VARCHAR, FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
					  ,FECHA_PAGO_FAPROV DATE_PAGO_FAPROV
					  ,RUT
					  ,NOM_EMPRESA
					  ,NOM_USUARIO
					  ,NRO_DOCUMENTO
					  ,CONVERT(VARCHAR, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
					  ,FECHA_DOCUMENTO DATE_FECHA_DOCUMENTO
					  ,MONTO_DOCUMENTO
				FROM PAGO_FAPROV_COMERCIAL
				WHERE COD_USUARIO_WS = $cod_usuario";
				
		if(K_CLIENTE == 'TODOINOX')		
			$sql .= " AND COD_PAGO_FAPROV_COMERCIAL NOT IN (11145, 11138, 11135, 11121, 11169, 11160, 11158)";
		if(K_CLIENTE == 'BODEGA')		
			$sql .= " AND COD_PAGO_FAPROV_COMERCIAL NOT IN (1795)";	
					
		$sql .=	" ORDER BY COD_PAGO_FAPROV_COMERCIAL DESC";		
		
   		parent::w_output_biggi('pago_faprov_comercial', $sql, $_REQUEST['cod_item_menu']);
		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		
		// headers
		$this->add_header(new header_num('COD_PAGO_FAPROV_COMERCIAL', 'COD_PAGO_FAPROV_COMERCIAL', 'Código'));
		$this->add_header($control = new header_date('FECHA_PAGO_FAPROV', 'CONVERT(VARCHAR, FECHA_PAGO_FAPROV, 103)', 'Fecha'));
		$control->field_bd_order = 'DATE_PAGO_FAPROV';
		$this->add_header(new header_text('RUT', 'RUT', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Usuario'));
		$this->add_header(new header_num('NRO_DOCUMENTO', 'NRO_DOCUMENTO', 'Nº Doc')); 
		$this->add_header(new header_date('FECHA_DOCUMENTO', 'CONVERT(VARCHAR, FECHA_DOCUMENTO, 103)', 'Fecha Doc.'));
		$control->field_bd_order = 'DATE_DOCUMENTO';
		$this->add_header(new header_num('MONTO_DOCUMENTO', 'MONTO_DOCUMENTO', 'Monto Doc.'));
   	}
   	function load_pago($sistema_pf, $sistema) {
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$sql = "SELECT SISTEMA
   					  ,URL_WS
   					  ,USER_WS
   					  ,PASSWROD_WS
   				FROM PARAMETRO_WS
				WHERE SISTEMA = '$sistema_pf'";
		$result = $db->build_results($sql);
		
		$user_ws		= $result[0]['USER_WS'];
		$passwrod_ws	= $result[0]['PASSWROD_WS'];
		$url_ws			= $result[0]['URL_WS'];
   		 
   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
   		$biggi->cli_wo_pago_faprov($this->cod_usuario, $sistema_pf, $sistema);
   	}
}
?>