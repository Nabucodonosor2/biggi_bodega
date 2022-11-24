<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_nota_venta extends w_output_biggi {
	const K_ESTADO_EMITIDA 			= 1;	
	const K_ESTADO_CERRADA			= 2;
	const K_ESTADO_ANULADA			= 3;
	const K_ESTADO_CONFIRMADA		= 4;
	const K_PARAM_NOM_EMPRESA 		= 6;
	const K_PARAM_DIR_EMPRESA 		= 10;
	const K_PARAM_TEL_EMPRESA 		= 11;
	const K_PARAM_FAX_EMPRESA 		= 12;
	const K_PARAM_MAIL_EMPRESA 		= 13;
	const K_PARAM_CIUDAD_EMPRESA	= 14;
	const K_PARAM_PAIS_EMPRESA 		= 15; 
	const K_PARAM_GTE_VTA 			= 16;
	const K_PARAM_RUT_EMPRESA 		= 20;
	const K_PARAM_SITIO_WEB_EMPRESA	= 25;
	const K_PARAM_PORC_DSCTO_MAX 	= 26;
	const K_PARAM_RANGO_DOC_NOTA_VENTA = 27;
	const K_AUTORIZA_CIERRE 		 = '991005';
	const K_CAMBIA_DSCTO_CORPORATIVO = '991010';
	//const K_AUTORIZA_EXPORTAR = '991045';
	
	function wo_nota_venta() {
		
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
		parent::w_base('nota_venta', $_REQUEST['cod_item_menu']);
		$sql = "select		COD_NOTA_VENTA
							/*INI_CAMPOS*/
							,convert(varchar(20), FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
							,FECHA_NOTA_VENTA DATE_NOTA_VENTA		
							,RUT
							,DIG_VERIF
							,NOM_EMPRESA
							,REFERENCIA
							,INI_USUARIO				
							,NOM_ESTADO_NOTA_VENTA
							,TOTAL_NETO
							,NRO_ORDEN_COMPRA
							,NV.COD_USUARIO_VENDEDOR1
							,ENV.COD_ESTADO_NOTA_VENTA		
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO
							,TOTAL_NETO - dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') VENTA_NETA_FINAL
							,(((TOTAL_NETO - dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO')) *
							 dbo.f_get_parametro_porc('GF', isnull(NV.FECHA_NOTA_VENTA, getdate())))/100) MONTO_GASTO_FIJO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1') COMISION_V1
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM') COMISION_ADM
							,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE
							/*FIN_CAMPOS*/
				from 		NOTA_VENTA NV,
							EMPRESA E,
							USUARIO U,
							ESTADO_NOTA_VENTA ENV
				where		NV.COD_EMPRESA = E.COD_EMPRESA
							and NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO
							and NV.COD_ESTADO_NOTA_VENTA = ENV.COD_ESTADO_NOTA_VENTA
							and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'NOTA_VENTA',COD_USUARIO_VENDEDOR1, COD_USUARIO_VENDEDOR2) = 1 
							/*FIN_CONDICION*/
				order by	COD_NOTA_VENTA desc";
		
		parent::w_output_biggi('nota_venta', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_nro_doc('COD_NOTA_VENTA', 'NOTA_VENTA' ));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));

		// headers
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'Nº NV'));
		$this->add_header($control = new header_date('FECHA_NOTA_VENTA', 'FECHA_NOTA_VENTA', 'Fecha'));
		$control->field_bd_order = 'DATE_NOTA_VENTA';
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		
		$this->add_header(new header_vendedor('INI_USUARIO', 'NV.COD_USUARIO_VENDEDOR1', 'V1'));
		
		$this->add_header(new header_text('NRO_ORDEN_COMPRA', 'NRO_ORDEN_COMPRA', 'N° OC'));
		  
		$sql_nv = "select COD_ESTADO_NOTA_VENTA ,NOM_ESTADO_NOTA_VENTA from ESTADO_NOTA_VENTA order by ORDEN";
		$this->add_header(new header_drop_down('NOM_ESTADO_NOTA_VENTA', 'ENV.COD_ESTADO_NOTA_VENTA', 'Estado', $sql_nv));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
		
		$cod_usuario = session::get('COD_USUARIO');
		
		//headers para el excel especial => solo a administración
		if($cod_usuario == 1){
			$this->add_header(new header_num('MONTO_DSCTO_CORPORATIVO', 'MONTO_DSCTO_CORPORATIVO', 'Descto. Corporativo '));
			$this->add_header(new header_num('VENTA_NETA_FINAL', 'VENTA_NETA_FINAL', 'Venta Neta Final'));
			$this->add_header(new header_num('MONTO_GASTO_FIJO', 'MONTO_GASTO_FIJO', 'Gasto Fijo'));
			$this->add_header(new header_num('SUM_OC_TOTAL', 'SUM_OC_TOTAL', 'Compra Neta Total'));
			$this->add_header(new header_num('RESULTADO', 'RESULTADO', 'Resultado'));
			$this->add_header(new header_num('MONTO_DIRECTORIO', 'MONTO_DIRECTORIO', 'Aporte a Administración'));
			$this->add_header(new header_num('COMISION_V1', 'COMISION_V1', 'Participación'));
			$this->add_header(new header_num('COMISION_ADM', 'COMISION_ADM', 'Participación Administración'));
			$this->add_header(new header_num('REMANENTE', 'REMANENTE', 'Remanente'));
		}	
		
    }

	function crear_nv_from_cot($cod_cotizacion) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',C.COD_USUARIO_VENDEDOR1, C.COD_USUARIO_VENDEDOR2) TIENE_ACCESO
						,C.COD_ESTADO_COTIZACION
				FROM COTIZACION C 
				WHERE C.COD_COTIZACION = $cod_cotizacion";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La Cotización Nº '.$cod_cotizacion.' no existe.');								
			return;
		}
		else if ($result[0]['TIENE_ACCESO']==0){
			$this->_redraw();
			$this->alert('Ud. no tiene acceso a a Cotización Nº '.$cod_cotizacion);								
			return;
		}
		else if ($result[0]['COD_ESTADO_COTIZACION'] == 5){ //RECHAZADA
			session::set('CREADA_DESDE_COTIZACION_COD_RECHAZADA', 6);
		}

		session::set('NV_CREADA_DESDE', $cod_cotizacion);
		$this->add();
	}

	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_nv_from_cot($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>