<?php

class wo_cotizacion extends wo_cotizacion_base {
   function wo_cotizacion() {
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		//parent::w_base('cotizacion', $_REQUEST['cod_item_menu']);
   		
		 parent::wo_cotizacion_base();
		$sql = "select		C.COD_COTIZACION
						,convert(varchar(20), C.FECHA_COTIZACION, 103) FECHA_COTIZACION
						,C.FECHA_COTIZACION DATE_COTIZACION
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,C.REFERENCIA
						,U.INI_USUARIO
						,EC.NOM_ESTADO_COTIZACION
						,C.TOTAL_NETO
						,NV.COD_NOTA_VENTA
			from 		COTIZACION C left outer join NOTA_VENTA NV ON NV.COD_COTIZACION = C.COD_COTIZACION
						,EMPRESA E
						,USUARIO U
						,ESTADO_COTIZACION EC
			where		C.COD_EMPRESA = E.COD_EMPRESA and 
						C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO and 
						C.COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION
						and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) = 1
			order by	C.COD_COTIZACION desc";
			
     			parent::w_output('cotizacion', $sql, $_REQUEST['cod_item_menu']);

		//$this->dw->set_sql($sql);
			$this->dw->add_control(new edit_nro_doc('COD_COTIZACION','COTIZACION'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
			
	      // headers
      	$this->add_header($control = new header_date('FECHA_COTIZACION', 'C.FECHA_COTIZACION', 'Fecha'));
	    $control->field_bd_order = 'DATE_COTIZACION';
	    $this->add_header(new header_num('COD_COTIZACION', 'C.COD_COTIZACION', 'Nº Cot.'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	      
	    $this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Razón Social'));
	    $this->add_header(new header_text('REFERENCIA', 'C.REFERENCIA', 'Referencia'));
		$this->add_header(new header_vendedor('INI_USUARIO', 'C.COD_USUARIO_VENDEDOR1', 'Vend'));

	    $this->add_header(new header_num('COD_NOTA_VENTA', 'isnull(NV.COD_NOTA_VENTA, 0)', 'NV'));
	    $this->add_header(new header_num('TOTAL_NETO', 'C.TOTAL_NETO', 'Total Neto'));
		
  	}
  	// Boton Crear Desde
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create' && $habilita)
			$temp->setVar("WO_ADD_DESDE", '<input name="b_'.$boton.'" id="b_'.$boton.'" src="../../../../commonlib/trunk/images/b_'.$boton.'.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_'.$boton.'\',\'\',\'../../../../commonlib/trunk/images/b_'.$boton.'_over.jpg\',1)" '.
											'onClick="return request_crear_desde();" />');
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	function redraw(&$temp) {
		parent::redraw($temp);
		$this->habilita_boton($temp, 'create', true);		
			
	}

	function crear_cot_from_cot($seleccion) {
		$seleccion = explode("|", $seleccion);
		if ($seleccion[0]=="SOLICITUD") { 		
			$cod_solicitud = $seleccion[1];
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT * FROM SOLICITUD_COTIZACION WHERE COD_SOLICITUD_COTIZACION  = $cod_solicitud";
			$result = $db->build_results($sql);
			
				if (count($result) == 0){
					$this->_redraw();
					$this->alert('La solicitud cotización Nº '.$cod_solicitud.' no existe.');								
					return;
				}else{
			session::set('CREADA_DESDE_SOLICITUD', $cod_solicitud);
			$this->add();
			
			
			
			}
		}
		else if ($seleccion[0]=="COTIZACION") { 
			$cod_cotizacion = $seleccion[1];
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "SELECT * FROM COTIZACION WHERE COD_COTIZACION = $cod_cotizacion";
			$result = $db->build_results($sql);
				if (count($result) == 0){
					$this->_redraw();
					$this->alert('La cotización Nº '.$cod_cotizacion.' no existe.');								
					return;
				}
				
				
			session::set('CREADA_DESDE_COTIZACION', $cod_cotizacion);
			$this->add();
		}
	}
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_cot_from_cot($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>