<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_factura_base extends w_output_biggi {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA = 2;
	const K_PARAM_MAX_IT_FA = 29;
	const K_TIPO_VENTA = 1;
	const K_AUTORIZA_SOLO_BITACORA = '992025';
	const K_AUTORIZA_SUMAR = '992060';
	var $checkbox_sumar;

	function wo_factura_base() {
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::w_base('factura', $_REQUEST['cod_item_menu']);
   		$this->checkbox_sumar = false;

		$sql = "select F.COD_FACTURA
						,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
						,F.FECHA_FACTURA DATE_FACTURA
						,F.NRO_FACTURA
						,F.RUT
						,F.DIG_VERIF
						,F.NOM_EMPRESA
						,F.COD_DOC
						,EDS.NOM_ESTADO_DOC_SII
						,F.TOTAL_CON_IVA
						,U.INI_USUARIO
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_FA
						,F.COD_USUARIO_VENDEDOR1
						,EDS.COD_ESTADO_DOC_SII
				 from	FACTURA F, ESTADO_DOC_SII EDS, USUARIO U
				where	F.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII AND
						F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						F.COD_TIPO_FACTURA = ".self::K_TIPO_VENTA."
						and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1
				order by	isnull(NRO_FACTURA, 9999999999) desc, COD_FACTURA desc";
				
	     parent::w_output_biggi('factura', $sql, $_REQUEST['cod_item_menu']);
			
		$this->dw->add_control(new edit_nro_doc('COD_FACTURA','FACTURA'));
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new edit_precio('TOTAL_CON_IVA'));
/*
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}*/
	   	$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SOLO_BITACORA, $this->cod_usuario);	// acceso bitacora
		if ($priv=='E') {
			$this->b_add_visible = false;
      	}
      	else {
			$this->b_add_visible = true;
      	}
	   	
		// headers
		$this->add_header($control = new header_date('FECHA_FACTURA', 'FECHA_FACTURA', 'Fecha'));
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nº FA'));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));
		$this->add_header(new header_text('COD_DOC', 'COD_DOC', 'N° NV'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from FACTURA F, USUARIO U where F.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_FA 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_FA
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_FA";
		$this->add_header($control = new header_drop_down_string('TIPO_FA', 'dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII)', 'Tipo FA', $sql));
		$control->field_bd_order = 'TIPO_FA';
		
		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E')
			$DISPLAY_SUMAR = '';
      	else
			$DISPLAY_SUMAR = 'none';
			
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
  	}
	function redraw(&$temp) {
  		if ($this->b_add_visible)
			$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario($this->cod_item_menu, $this->cod_usuario)=='E');
		$this->dw_check_box->habilitar($temp, true);
	}	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='create') {
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return request_factura(\'Ingrese Nº de la Nota de Venta\',\'\');"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}
		else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
	function detalle_record_desde($modificar, $cant_fa_a_hacer) 
	{
		// No se llama al ancestro porque se reimplementa toda la rutina
		session::set("cant_fa_a_hacer", $cant_fa_a_hacer);

		// retrieve
		$this->set_count_output();
		$this->last_page = Ceil($this->row_count_output / $this->row_per_page);
		$this->set_current_page(0);
		$this->save_SESSION();

		$pag_a_mostrar=$cant_fa_a_hacer -1;

		$this->detalle_record($pag_a_mostrar);	// Se va al primer registro
	}
  	function crear_fa_from_nv($valor_devuelto) 
  	{
  		
  		$pos = strpos($valor_devuelto, '-');
	  	if ($pos!==false)
	  	{
	  		list($cod_nota_venta,$codigosGD )=split('[-]', $valor_devuelto);
			$opcion=substr($codigosGD, 0,9);
	  	}
		else
	  		list($opcion, $cod_nota_venta)=split('[|]', $valor_devuelto);

	  	$cantidad_max = $this->get_parametro(self::K_PARAM_MAX_IT_FA);
	  	if ($cantidad_max=='' || $cantidad_max==0)
	  		$cantidad_max = 18;
		if ($opcion=='desde_nv' || $opcion=='desde_nv_anticipo') {
				//crear la FA para todos los itemsNV que tengan pendiente por facturar
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				///valida que la NV exista
				$sql = "select * from NOTA_VENTA where COD_NOTA_VENTA = $cod_nota_venta";
				$result = $db->build_results($sql);
				if (count($result) == 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no existe.');								
					return;
				}

				//valida que la NV este confirmada
				$sql = "select * from NOTA_VENTA 
						where COD_NOTA_VENTA = ".$cod_nota_venta." 
						and	COD_ESTADO_NOTA_VENTA IN (".self::K_ESTADO_CONFIRMADA.", ".self::K_ESTADO_CERRADA.")";
				$result = $db->build_results($sql);
				if (count($result) == 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no esta confirmada.');								
					return;
				}

				/* valida que la NV no tenga FAs anteriores en estado = emitida
				ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar 
				siempre será la misma cantidad de la NV.
				*/
				$sql = "select * from FACTURA
							where COD_DOC = $cod_nota_venta and
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
				$result = $db->build_results($sql);
				if (count($result) != 0) {
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' tiene Factura(s) pendientes(s) en estado emitido. Para poder generar más Facturas deberá imprimir los documentos emitidos.');						
					return;
				}

				//****************
				// valida que este pendiente de facturar
				$sql = "select dbo.f_nv_porc_facturado($cod_nota_venta ) PORC_FACTURA";
				$result = $db->build_results($sql);
				$porc_factura = $result[0]['PORC_FACTURA'];
				if ($porc_factura >= 100) { 
					$this->_redraw();
					$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' está totalmente Facturada.');								
					return;
				}
					
				if ($opcion=='desde_nv') {
					//cuenta cuantos items hay
					$sql_cuenta="select count(*) cantidad
									from ITEM_NOTA_VENTA IT, NOTA_VENTA NV
									where NV.COD_NOTA_VENTA = $cod_nota_venta and 
									NV.COD_NOTA_VENTA = IT.COD_NOTA_VENTA";
					$result_cuenta = $db->build_results($sql_cuenta);
					$cantidad = $result_cuenta[0]['cantidad'];
					$cant_fa_a_hacer=ceil($cantidad/$cantidad_max);
						
					$cod_usuario = $this->cod_usuario;	
								
					$sp = 'sp_fa_crear_desde_nv';
					$param = "$cod_nota_venta, $cod_usuario";
				}
				else if ($opcion=='desde_nv_anticipo') {
					$cant_fa_a_hacer = 1;
					$cod_usuario = $this->cod_usuario;	
								
					$sp = 'sp_fa_crear_desde_nv_anticipo';
					$param = "$cod_nota_venta, $cod_usuario";
				}
					
				
					
				$db->BEGIN_TRANSACTION();
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
					$this->detalle_record_desde(true,$cant_fa_a_hacer);
				}
				else { 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_nv', favor contacte a IntegraSystem.");
				}	
		}
		else  {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$codigos=substr($codigosGD, 10); //solo los codigos, sin valor de la opcion (venía primero en la variable)
			$codigos_gd=str_replace('|', ' ', $codigos);
			$codigos_gd2=str_replace('|', ',', $codigos);
			
			$largo=strlen($codigos_gd);
			
			$codigos_gd=substr($codigos_gd, 0,$largo -1);
			$codigos_gd2=substr($codigos_gd2, 0,$largo -1);
			
			// valida que hayan item pendiente por facturar.
				$sql_por_facturar = "select sum(dbo.f_nv_cant_por_facturar(IT.COD_ITEM_DOC, 'TODO_ESTADO')) POR_FACTURAR
				from ITEM_GUIA_DESPACHO IT, GUIA_DESPACHO GD
				where GD.COD_GUIA_DESPACHO = IT.COD_GUIA_DESPACHO
						AND GD.COD_DOC = $cod_nota_venta 
						AND GD.COD_GUIA_DESPACHO in ($codigos_gd2)
						AND IT.CANTIDAD > 0 
						AND IT.PRECIO > 0";
						
				$result = $db->build_results($sql_por_facturar);
				$por_facturarGd = $result[0]['POR_FACTURAR'];
				if ($por_facturarGd <= 0)
				{
						$this->_redraw();
						$this->alert('La Guía de despacho está totalmente Facturada.');								
						return;
				}
	
			//ver cuantos items tendré en todos esos GD
			$sql_items="select count(distinct(cod_item_doc)) cantidad from item_guia_despacho where COD_GUIA_DESPACHO in (".$codigos_gd2.")";
			$result_items = $db->build_results($sql_items);
			$cantidad= $result_items[0]['cantidad'];

			/* VM, 11-03-2014
			 * Se elimina esta validación.
			 * Cuando la FA es creada desde varias GD puede exeder $cantidad_max y esto es manejado por sp_fa_crear_desde_gds
			 * 
			if($cantidad > $cantidad_max){
				$this->_redraw();
				$this->alert('Se está ingresando más item que la cantidad permitida, favor contacte a IntegraSystem.');
				return;
			}
			*/
			
			$cant_fa_a_hacer=ceil($cantidad/$cantidad_max);
			$db->BEGIN_TRANSACTION();
			$cod_usuario = $this->cod_usuario;	
			$codigos_gd="'".$codigos_gd."'";		
			$sp = 'sp_fa_crear_desde_gds';
			$param = "$cod_nota_venta, $codigos_gd, $cod_usuario";
			 	
			if ($db->EXECUTE_SP($sp, $param)) { 
				$db->COMMIT_TRANSACTION();
				$this->detalle_record_desde(true,$cant_fa_a_hacer);
			}
			else { 
				$db->ROLLBACK_TRANSACTION();
				$this->_redraw();
				$this->alert("No se pudo crear la factura. Error en 'sp_fa_crear_desde_gds', favor contacte a IntegraSystem.");
			}	
		}
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x'])) {
			$this->crear_fa_from_nv($_POST['wo_hidden']);
		}else if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_CON_IVA']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_CON_IVA']->valor_filtro2;
			
			if($this->checkbox_sumar){
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_CON_IVA', 'TOTAL_CON_IVA', 'Total c/IVA'));  
			}

			// vuelve a setear el filtro aplicado
			$this->headers['TOTAL_CON_IVA']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_CON_IVA']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();	
		}else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}	
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_factura.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_factura extends wo_factura_base {
		function wo_factura() {
			parent::wo_factura_base(); 
		}
	}
}
?>