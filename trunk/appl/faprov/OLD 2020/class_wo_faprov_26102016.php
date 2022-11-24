<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

// Control que no dibuja NADA, es como si no existiera
class control_null extends edit_control {
	function control_null($field) {
		parent::edit_control($field);
	}
	function draw_entrable($dato, $record) {
		return '';
	}
	function draw_no_entrable($dato, $record) {
		return '';
	}
}

class wo_faprov extends w_output_biggi {
	const K_ASIGNA_PROY_COMPRA = '993005';
	const K_PARAM_DIRECTORIO = 31;
	
   	function wo_faprov() {
 
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
		
		//se listan todas las FA exepto las de directorio
		$sql = "SELECT   F.COD_FAPROV
						,convert(varchar(20), FECHA_REGISTRO, 103) FECHA_REGISTRO
						,FECHA_REGISTRO DATE_REGISTRO
						,NOM_EMPRESA
						,RUT
						,DIG_VERIF
						,NRO_FAPROV
						,dbo.f_faprov_get_oc(F.COD_FAPROV) OC_PAR
						,convert(varchar(20), FECHA_FAPROV, 103) FECHA_FAPROV
						,FECHA_FAPROV DATE_FAPROV
						,TOTAL_NETO
						,EF.NOM_ESTADO_FAPROV
						,isnull(C.NOM_CUENTA_COMPRA, 'Sin asignar') NOM_CUENTA_COMPRA
						,'N' SELECCION
						,case F.COD_ESTADO_FAPROV
							when 5 then 'none'
							else ''
						end DISPLAY_SELECCION
 				FROM	FAPROV F LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = F.COD_CUENTA_COMPRA
 						, EMPRESA E, ESTADO_FAPROV EF
				WHERE	F.COD_EMPRESA = E.COD_EMPRESA AND
						EF.COD_ESTADO_FAPROV = F.COD_ESTADO_FAPROV AND
						F.COD_EMPRESA <> $cod_empresa
				order by	F.COD_FAPROV desc";		
			
   		parent::w_output_biggi('faprov', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new static_num('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));

	    // tiene acceso para asignar
   		$priv = $this->get_privilegio_opcion_usuario(self::K_ASIGNA_PROY_COMPRA, $this->cod_usuario);
		if ($priv=='E'){
			$this->dw->entrable = true;
			$this->dw->add_control(new edit_check_box('SELECCION', 'S', 'N'));
      	}
      	else
			$this->dw->add_control(new control_null('SELECCION'));

      	// headers
		$this->add_header(new header_num('COD_FAPROV', 'COD_FAPROV', 'Código'));
		$this->add_header($control = new header_date('FECHA_REGISTRO', 'FECHA_REGISTRO', 'Fecha'));
		$control->field_bd_order = 'DATE_REGISTRO';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$this->add_header(new header_num('NRO_FAPROV', 'NRO_FAPROV', 'Nº Doc'));

		$this->add_header($control = new header_text('OC_PAR', '(SELECT dbo.f_faprov_get_oc(F.COD_FAPROV))', 'OC/ PART'));
		$control->field_bd_order = "OC_PAR";

		$this->add_header(new header_date('FECHA_FAPROV', 'convert(varchar(20), FECHA_FAPROV, 103', 'Fecha Doc'));
		$control->field_bd_order = "DATE_FAPROV";
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  

		$sql_estado_faprov = "select COD_ESTADO_FAPROV, NOM_ESTADO_FAPROV from ESTADO_FAPROV order by COD_ESTADO_FAPROV";
		$this->add_header($control = new header_drop_down('NOM_ESTADO_FAPROV', 'EF.COD_ESTADO_FAPROV', 'Estado', $sql_estado_faprov));
		$control->field_bd_order = "NOM_ESTADO_FAPROV";

		$sql = "select null COD_CUENTA_COMPRA, '(Sin asignar)' NOM_CUENTA_COMPRA union select COD_CUENTA_COMPRA, NOM_CUENTA_COMPRA from CUENTA_COMPRA order by NOM_CUENTA_COMPRA";
		$this->add_header($control = new header_drop_down('NOM_CUENTA_COMPRA', 'F.COD_CUENTA_COMPRA', 'Proyecto Compra', $sql));
		$control->field_bd_order = "NOM_CUENTA_COMPRA"; 

		$this->row_per_page = 300;
   	}
	function crear_faprov_desde($tipo_faprov) {
		session::set('FAPROV_CREADA_DESDE', $tipo_faprov);
		$res = explode("|", $tipo_faprov);			
		$faprov_desde = $res[0];
		$cod_orden_compra = $res[1];
		
		if ($faprov_desde=='ORDEN_COMPRA' && $cod_orden_compra!='') {
			// valida que exista la OC
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select count(*) CANT
					from ORDEN_COMPRA
					where COD_ORDEN_COMPRA = $cod_orden_compra";
			$result = $db->build_results($sql);
			if ($result[0]['CANT']==0) {
				$this->_redraw();
				$this->alert("La orden de compra $cod_orden_compra no existe");
				return;
			}
			$sql = "SELECT	 TIPO_ORDEN_COMPRA
							,COD_ESTADO_ORDEN_COMPRA
					FROM ORDEN_COMPRA
					WHERE cod_orden_compra = $cod_orden_compra";
			$result = $db->build_results($sql);
			$tipo_oc = $result[0]['TIPO_ORDEN_COMPRA'];
			$cod_estado_oc = $result[0]['COD_ESTADO_ORDEN_COMPRA'];
			
			if ($tipo_oc != 'GASTO_FIJO') {
				// Valida que la OC este pendiente de FA
				$sql = "SELECT  dbo.f_oc_get_saldo_sin_faprov(COD_ORDEN_COMPRA) SALDO
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
				if ($result[0]['SALDO'] <= 0) {		
					$sql = "SELECT COD_FAPROV
							FROM ITEM_FAPROV
							WHERE COD_DOC = $cod_orden_compra";
					$result = $db->build_results($sql);
					$nro_factura = '';
					for ($i = 0; $i < count($result); $i++){
						$nro_factura .= $result[$i]['COD_FAPROV'].", ";
					}
					$nro_factura = substr($nro_factura, 0, strlen($nro_factura) -2);
					$this->_redraw();
					$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
					return;
				}
			}else{
				
				$sql="SELECT  COD_ESTADO_ORDEN_COMPRA
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
				$cod_estado_orden_compra = $result[0]['COD_ESTADO_ORDEN_COMPRA'];
				if($cod_estado_orden_compra <> 1){
				//if  preguntado por autgoriza  y no este 
				$sql = "SELECT  dbo.f_oc_get_saldo_sin_faprov(COD_ORDEN_COMPRA) SALDO
						from ORDEN_COMPRA
						where COD_ORDEN_COMPRA = $cod_orden_compra";
				$result = $db->build_results($sql);
//				echo $result[0]['SALDO'];
				if ($result[0]['SALDO'] <= 0) {		
					$sql = "SELECT COD_FAPROV
							FROM ITEM_FAPROV
							WHERE COD_DOC = $cod_orden_compra";
					$result = $db->build_results($sql);
					$nro_factura = '';
					for ($i = 0; $i < count($result); $i++){
						$nro_factura .= $result[$i]['COD_FAPROV'].", ";
					}
					$nro_factura = substr($nro_factura, 0, strlen($nro_factura) -2);
					$this->_redraw();
					$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
					return;
				}else{
					if ($cod_estado_oc != 4) {
						$this->_redraw();
						//$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
						$this->alert("La OC Nº $cod_orden_compra no esta autorizada.");
						return;		
					}
				}
			  }else{
			  	$this->_redraw();
						//$this->alert("La OC Nº $cod_orden_compra ya tiene recepción de facturas por el total.".'\n'."La(s) Factura(s) proveedor asociadas a la OC son: $nro_factura");
						$this->alert("La OC Nº $cod_orden_compra no esta autorizada.");
						return;		
			  }	
			}
		}
		$this->add();
	}
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='asignar') {
  			if ($this->get_privilegio_opcion_usuario(self::K_ASIGNA_PROY_COMPRA, $this->cod_usuario)=='E') {
				if ($habilita)
					$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
																'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
																'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
																'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
																'onClick="return request_cuenta();"'.'style="display:{DISPLAY_MARCAR}"'.
															'/>');
				else
					$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
  			}
		}else if ($boton=='cambio_estado') {
			if ($this->get_privilegio_opcion_usuario(self::K_ASIGNA_PROY_COMPRA, $this->cod_usuario)=='E') {
				if ($habilita)
					$temp->setVar("WO_".strtoupper($boton), '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../images_appl/b_'.$boton.'.jpg" type="image" '.
																'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_click.jpg\',1)" '.
																'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
																'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../images_appl/b_'.$boton.'_over.jpg\',1)" '.
																'onClick="return confirm_cambio();"'.'style="display:"'.
															'/>');
				else
					$temp->setVar("WO_".strtoupper($boton), '<img src="../../images_appl/b_'.$boton.'_d.jpg"/>');
			}	
		}
		else 
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function redraw(&$temp) {
		parent::redraw($temp);
      	
		if ($this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E'){
			$this->habilita_boton($temp, 'asignar', true);
			$this->habilita_boton($temp, 'cambio_estado', true);	
      	}else{
			$this->habilita_boton($temp, 'asignar', false);
			$this->habilita_boton($temp, 'cambio_estado', false);
      	}
      		
   		$priv = $this->get_privilegio_opcion_usuario(self::K_ASIGNA_PROY_COMPRA, $this->cod_usuario);
		if ($priv=='E')
			$temp->setVar("DISPLAY_MARCAR", '');
		else
			$temp->setVar("DISPLAY_MARCAR", 'none');

			
	}
	function asignar($array){
		$result = explode("|", $array);
		$cod_cuenta_compra = $result[0];
		$check_cambio_estado = $result[1];
		
		$this->dw->get_values_from_POST();
		$sp = 'spu_faprov';
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$error = false;
		// primer registro de la pagina
		$ind = $this->row_per_page * ($this->current_page - 1);		
		// loop en los registros de la pagina visible
		$i = 0;
		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$seleccion = $this->dw->get_item($i, 'SELECCION');
			if ($seleccion=='S') { 
				$cod_faprov = $this->dw->get_item($i, 'COD_FAPROV');
				$param = "'ASIGNAR', $cod_faprov, $cod_cuenta_compra"; 
	    		if (!$db->EXECUTE_SP($sp, $param)) {
	    			$error = true;
					$db->ROLLBACK_TRANSACTION();
					$error_sp = $db->GET_ERROR();
					$this->alert('No se pudo grabar el registro.\n\n'.$db->make_msg_error_bd());
					break;
	    		}
	    		
	    		if($check_cambio_estado == 'S'){
		    		$param = "'CAMBIO_ESTADO', $cod_faprov"; 
		    		if (!$db->EXECUTE_SP($sp, $param)) {
		    			$error = true;
						$db->ROLLBACK_TRANSACTION();
						$error_sp = $db->GET_ERROR();
						$this->alert('No se pudo grabar el registro.\n\n'.$db->make_msg_error_bd());
						break;
		    		}
	    		}
    		}

    		$i++;
			$ind++;
		}
		if (!$error) 
			$db->COMMIT_TRANSACTION();
		$this->retrieve();		
	}
	function cambio_estado() {
		$this->dw->get_values_from_POST();
		$sp = 'spu_faprov';
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->BEGIN_TRANSACTION();
		$error = false;
		// primer registro de la pagina
		$ind = $this->row_per_page * ($this->current_page - 1);		
		// loop en los registros de la pagina visible
		$i = 0;
		while (($i < $this->row_per_page) && ($ind < $this->row_count_output)){
			$seleccion = $this->dw->get_item($i, 'SELECCION');
			if ($seleccion=='S') { 
				$cod_faprov = $this->dw->get_item($i, 'COD_FAPROV');
				$param = "'CAMBIO_ESTADO', $cod_faprov"; 
	    		if (!$db->EXECUTE_SP($sp, $param)) {
	    			$error = true;
					$db->ROLLBACK_TRANSACTION();
					$error_sp = $db->GET_ERROR();
					$this->alert('No se pudo grabar el registro.\n\n'.$db->make_msg_error_bd());
					break;
	    		}
    		}

    		$i++;
			$ind++;
		}
		if (!$error) 
			$db->COMMIT_TRANSACTION();
		$this->retrieve();	
	}	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_faprov_desde($_POST['wo_hidden']);
		else if(isset($_POST['b_asignar_x']))
			$this->asignar($_POST['wo_hidden']);
		else if(isset($_POST['b_cambio_estado_x']))
			$this->cambio_estado();
		else
			parent::procesa_event();
	}
}
?>