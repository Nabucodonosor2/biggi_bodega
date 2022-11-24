<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_pago_directorio extends w_output {
	const K_PARAM_DIRECTORIO = 31;
	
   	function wo_pago_directorio() {
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		//obtiene el codigo de usuario asignado como directorio		
   		$sql_cod_usuario_dir = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = ".self::K_PARAM_DIRECTORIO;
   	 	$result = $db->build_results($sql_cod_usuario_dir);			
		$cod_usuario_dir = $result[0]['VALOR'];
		
		//obtiene el codigo de la empresa asociada al usuario directorio
		$sql_cod_empresa = "SELECT COD_EMPRESA FROM USUARIO WHERE COD_USUARIO = ".$cod_usuario_dir;
   	 	$result = $db->build_results($sql_cod_empresa);			
		$cod_empresa = $result[0]['COD_EMPRESA'];
		
		$sql = "SELECT COD_PAGO_FAPROV
						,convert(varchar(20), FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
						,FECHA_PAGO_FAPROV DATE_PAGO_FAPROV
						,PAGUESE_A
						,NOM_USUARIO
						,NOM_TIPO_PAGO_FAPROV
						,NRO_DOCUMENTO
						,convert(varchar(20), FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
						,EPF.NOM_ESTADO_PAGO_FAPROV
						,MONTO_DOCUMENTO
				FROM 	PAGO_FAPROV PF, TIPO_PAGO_FAPROV TPF, EMPRESA E, USUARIO U, 
						ESTADO_PAGO_FAPROV EPF 
				WHERE 	E.COD_EMPRESA = PF.COD_EMPRESA AND
						PF.COD_EMPRESA = $cod_empresa AND
						TPF.COD_TIPO_PAGO_FAPROV = PF.COD_TIPO_PAGO_FAPROV AND
						U.COD_USUARIO = PF.COD_USUARIO AND
						EPF.COD_ESTADO_PAGO_FAPROV = PF.COD_ESTADO_PAGO_FAPROV 
						ORDER BY COD_PAGO_FAPROV DESC";		
		
		parent::w_output('pago_directorio', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_PAGO_FAPROV', 'COD_PAGO_FAPROV', 'Código'));
		$this->add_header($control = new header_date('FECHA_PAGO_FAPROV', 'convert(varchar(20), FECHA_PAGO_FAPROV, 103)', 'Fecha'));
		$control->field_bd_order = 'DATE_PAGO_FAPROV';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('PAGUESE_A', 'PAGUESE_A', 'Paguese a'));
		$sql_usuario = "select COD_USUARIO, NOM_USUARIO from USUARIO order by COD_USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'U.COD_USUARIO', 'Usuario', $sql_usuario));
		$sql_tipo_faprov = "select COD_TIPO_PAGO_FAPROV, NOM_TIPO_PAGO_FAPROV from TIPO_PAGO_FAPROV order by COD_TIPO_PAGO_FAPROV";
		$this->add_header(new header_drop_down('NOM_TIPO_PAGO_FAPROV', 'TPF.COD_TIPO_PAGO_FAPROV', 'Tipo Doc.', $sql_tipo_faprov));
		$sql_estado_pago_faprov = "select COD_ESTADO_PAGO_FAPROV, NOM_ESTADO_PAGO_FAPROV from ESTADO_PAGO_FAPROV order by COD_ESTADO_PAGO_FAPROV";
		$this->add_header(new header_drop_down('NOM_ESTADO_PAGO_FAPROV', 'EPF.COD_ESTADO_PAGO_FAPROV', 'Estado', $sql_estado_pago_faprov));
		$this->add_header(new header_num('NRO_DOCUMENTO', 'NRO_DOCUMENTO', 'Nº Doc')); 
		$this->add_header(new header_date('FECHA_DOCUMENTO', 'convert(varchar(20), FECHA_DOCUMENTO, 103)', 'Fecha Doc.'));
		$this->add_header(new header_num('MONTO_DOCUMENTO', 'MONTO_DOCUMENTO', 'Monto Doc.'));  
   	}
}
?>