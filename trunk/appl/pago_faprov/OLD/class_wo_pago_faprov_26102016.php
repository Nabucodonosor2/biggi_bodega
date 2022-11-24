<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_pago_faprov extends w_output_biggi{
	const K_PARAM_DIRECTORIO = 31;
	
   	function wo_pago_faprov(){
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		//obtiene el codigo de usuario asignado como directorio		
   		$sql_cod_usuario_dir = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = ".self::K_PARAM_DIRECTORIO;
   	 	$result = $db->build_results($sql_cod_usuario_dir);			
		$cod_usuario_dir = $result[0]['VALOR'];
		
		//obtiene el codigo de la empresa asociada al usuario directorio
		$sql_cod_empresa = "SELECT COD_EMPRESA FROM USUARIO WHERE COD_USUARIO = ".$cod_usuario_dir;
   	 	$result = $db->build_results($sql_cod_empresa);			
   	 	if ($result[0]['COD_EMPRESA']=='')
			$cod_empresa = 0;
   	 	else			
			$cod_empresa = $result[0]['COD_EMPRESA'];
		
		$sql = "SELECT COD_PAGO_FAPROV
						,convert(varchar(20), FECHA_PAGO_FAPROV, 103) FECHA_PAGO_FAPROV
						,FECHA_PAGO_FAPROV DATE_PAGO_FAPROV
						,RUT
						,DIG_VERIF
						,NOM_EMPRESA
						,NOM_USUARIO
						,NOM_TIPO_PAGO_FAPROV
						,NRO_DOCUMENTO
						,convert(varchar(20), FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO
						,FECHA_DOCUMENTO DATE_DOCUMENTO
						,EPF.NOM_ESTADO_PAGO_FAPROV
						,MONTO_DOCUMENTO
						,U.COD_USUARIO
						,TPF.COD_TIPO_PAGO_FAPROV
						,EPF.COD_ESTADO_PAGO_FAPROV
				FROM 	PAGO_FAPROV PF, TIPO_PAGO_FAPROV TPF, EMPRESA E, USUARIO U, 
						ESTADO_PAGO_FAPROV EPF 
				WHERE 	E.COD_EMPRESA = PF.COD_EMPRESA AND
						PF.COD_EMPRESA <> $cod_empresa AND
						TPF.COD_TIPO_PAGO_FAPROV = PF.COD_TIPO_PAGO_FAPROV AND
						U.COD_USUARIO = PF.COD_USUARIO AND
						EPF.COD_ESTADO_PAGO_FAPROV = PF.COD_ESTADO_PAGO_FAPROV 
						ORDER BY COD_PAGO_FAPROV DESC";		
			
   		parent::w_output_biggi('pago_faprov', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_precio('MONTO_DOCUMENTO'));
		$this->dw->add_control(new static_num('RUT'));
		
		// headers
		$this->add_header(new header_num('COD_PAGO_FAPROV', 'COD_PAGO_FAPROV', 'Código'));
		$this->add_header($control = new header_date('FECHA_PAGO_FAPROV', 'PF.FECHA_PAGO_FAPROV', 'Fecha'));
		$control->field_bd_order = 'DATE_PAGO_FAPROV';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$sql_usuario = "select COD_USUARIO, NOM_USUARIO from USUARIO order by COD_USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'U.COD_USUARIO', 'Usuario', $sql_usuario));
		$sql_tipo_faprov = "select COD_TIPO_PAGO_FAPROV, NOM_TIPO_PAGO_FAPROV from TIPO_PAGO_FAPROV order by COD_TIPO_PAGO_FAPROV";
		$this->add_header(new header_drop_down('NOM_TIPO_PAGO_FAPROV', 'TPF.COD_TIPO_PAGO_FAPROV', 'Tipo Doc.', $sql_tipo_faprov));
		$sql_estado_pago_faprov = "select COD_ESTADO_PAGO_FAPROV, NOM_ESTADO_PAGO_FAPROV from ESTADO_PAGO_FAPROV order by COD_ESTADO_PAGO_FAPROV";
		$this->add_header(new header_drop_down('NOM_ESTADO_PAGO_FAPROV', 'EPF.COD_ESTADO_PAGO_FAPROV', 'Estado', $sql_estado_pago_faprov));
		$this->add_header(new header_num('NRO_DOCUMENTO', 'NRO_DOCUMENTO', 'Nº Doc')); 
		$this->add_header($control =new header_date('FECHA_DOCUMENTO', 'convert(varchar(20), FECHA_DOCUMENTO, 103)', 'Fecha Doc.'));
		$control->field_bd_order = 'DATE_DOCUMENTO';
		$this->add_header(new header_num('MONTO_DOCUMENTO', 'MONTO_DOCUMENTO', 'Monto Doc.'));  
   	}
   	
	function agregar_pago_faprov($valores){
		session::set('ADD_VALORES', $valores);
		$this->add();
	}
   	
	function habilita_boton(&$temp, $boton, $habilita){
		if ($boton=='add'){
			if ($habilita)
				$temp->setVar("WO_ADD", '<input name="b_add" id="b_add" src="../../../../commonlib/trunk/images/b_add.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_add\',\'\',\'../../../../commonlib/trunk/images/b_add_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_add\',\'\',\'../../../../commonlib/trunk/images/b_add_over.jpg\',1)" '.
											'onClick="return dlg_empresa_cta_corriente();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_add_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
	function procesa_event(){		
		if(isset($_POST['b_add_x'])){
			$this->agregar_pago_faprov($_POST['wo_hidden']);
		}
		else
			parent::procesa_event();
	}
}
?>