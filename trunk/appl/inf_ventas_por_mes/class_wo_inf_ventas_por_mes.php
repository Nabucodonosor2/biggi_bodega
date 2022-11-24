<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_static_num_miles.php");

ini_set('max_execution_time', 900); //900 seconds = 15 minutes

class header_num_miles extends  header_num {
	function header_num_miles($field, $field_bd, $nom_header, $cant_decimal=0, $solo_positivos=true, $operacion_accumulate='') {
		parent:: header_num($field, $field_bd, $nom_header, $cant_decimal, $solo_positivos, $operacion_accumulate);
	}
	function draw_valor_accumulate() {
		return number_format(round($this->valor_accumulate /1000, 0), 0, ',', '.');
	}
}

class wo_inf_ventas_por_mes extends w_informe_pantalla
{
	function wo_inf_ventas_por_mes() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_mes.ANO");
		session::un_set("inf_ventas_por_mes.ANO");
		
		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ventas_por_mes", "$cod_usuario, $ano"); 
		$sql = "select	I.COD_NOTA_VENTA
						,I.COD_NOTA_VENTA COD_NOTA_VENTA_H
						,I.MES
						,I.ANO
						,I.NOM_MES
						,I.FECHA_NOTA_VENTA
						,convert(varchar, I.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA_STR
						,I.NOM_EMPRESA 
						,I.COD_USUARIO_VENDEDOR1
						,I.INI_USUARIO
						,I.SUBTOTAL
						 ,I.TOTAL_NETO
						 ,I.TOTAL_VENTA
						 ,I.PORC_DSCTO
						 ,I.MONTO_DSCTO
						 ,I.MONTO_DSCTO_CORPORATIVO
						 ,I.DESPACHADO_NETO
						 ,I.COBRADO_NETO
						 ,I.POR_COBRAR_NETO
						 ,I.NV_CONFIRMADA
						 ,I.NV_X_CONFIRMAR
						 ,I.NOM_ESTADO_NOTA_VENTA
						 ,I.COD_ESTADO_NOTA_VENTA
						 ,I.CANT_NV
				FROM INF_VENTAS_POR_MES I
				where I.COD_USUARIO = $cod_usuario
				order by I.COD_NOTA_VENTA";   		
		parent::w_informe_pantalla('inf_ventas_por_mes', $sql, $_REQUEST['cod_item_menu']);
		$this->b_export_visible = false;
		$this->b_print_visible = false;
		$this->css_oscuro = "";
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'MES', 'Mes'));
		$this->add_header(new header_num('ANO', 'ANO', 'Año'));
		$this->add_header(new header_date('FECHA_NOTA_VENTA_STR', 'I.FECHA_NOTA_VENTA', 'Fecha'));
		$this->add_header(new header_num('COD_NOTA_VENTA', 'COD_NOTA_VENTA', 'NV'));
		$this->add_header(new header_text('NOM_EMPRESA', "NOM_EMPRESA", 'Cliente'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from NOTA_VENTA N, USUARIO U where N.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'I.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_num_miles('SUBTOTAL', 'SUBTOTAL', 'TVN s/d', 0, true, 'SUM'));
		$this->add_header(new header_num('PORC_DSCTO', 'PORC_DSCTO', '% D', 0));  		
		$this->add_header(new header_num_miles('MONTO_DSCTO', 'MONTO_DSCTO', 'Dscto', 0, true, 'SUM'));  		
		$this->add_header(new header_num_miles('MONTO_DSCTO_CORPORATIVO', "MONTO_DSCTO_CORPORATIVO", 'Dscto Corporativo', 0, true, 'SUM'));  		
		$this->add_header(new header_num_miles('TOTAL_NETO', 'TOTAL_NETO', 'TVN c/d', 0, true, 'SUM'));
		$this->add_header(new header_num_miles('TOTAL_VENTA', "TOTAL_VENTA", 'TVenta', 0, true, 'SUM'));
		$this->add_header(new header_num_miles('DESPACHADO_NETO', 'DESPACHADO_NETO', 'TEN', 0, true, 'SUM'));      
		$this->add_header(new header_num_miles('COBRADO_NETO', 'COBRADO_NETO', 'TPN', 0, true, 'SUM'));      
		$this->add_header(new header_num_miles('POR_COBRAR_NETO', 'POR_COBRAR_NETO', 'TxCN', 0, true, 'SUM'));
		$sql = "select COD_ESTADO_NOTA_VENTA, NOM_ESTADO_NOTA_VENTA from ESTADO_NOTA_VENTA order by COD_ESTADO_NOTA_VENTA";      
		$this->add_header(new header_drop_down('NOM_ESTADO_NOTA_VENTA', 'COD_ESTADO_NOTA_VENTA', 'Estado', $sql));      
		
		$this->add_header(new header_num('NV_CONFIRMADA', 'NV_CONFIRMADA', '', 0, true, 'SUM'));      
		$this->add_header(new header_num('NV_X_CONFIRMAR', 'NV_X_CONFIRMAR', '', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_NV', '1', '', 0, true, 'SUM'));
		
		
		$this->dw->add_control(new static_num_miles('SUBTOTAL'));
		$this->dw->add_control(new edit_porcentaje('PORC_DSCTO'));
		$this->dw->add_control(new static_num_miles('TOTAL_NETO'));
		$this->dw->add_control(new static_num_miles('TOTAL_VENTA'));
		$this->dw->add_control(new static_num_miles('DESPACHADO_NETO'));
		$this->dw->add_control(new static_num_miles('COBRADO_NETO'));
		$this->dw->add_control(new static_num_miles('POR_COBRAR_NETO'));
   		
   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_mes.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_mes.MES_HASTA");
		
		session::un_set("inf_ventas_por_mes.MES_DESDE");
		session::un_set("inf_ventas_por_mes.MES_HASTA");
		
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$this->row_per_page = 500;
		$this->make_filtros();	// filtro incial
	}
   	function make_menu(&$temp) {
   		/*  MODIFICACION PARA USUARIO ANGEL SCIANCA, EN EL INFORME DE VENTAS SE ENCOJE EL TAMAÑO DEL MENU */   		
	   	$menu = session::get('menu_appl');
	    $menu->ancho_completa_menu = 1;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = 79;	    	    	    		
    }
   	function _redraw() {
		parent::_redraw();
		
		if (session::is_set('ULTIMA_NV_CONSAULTADA')) {
			$cod_nota_venta = session::get('ULTIMA_NV_CONSAULTADA');
			session::un_set('ULTIMA_NV_CONSAULTADA');
			print '<script type="text/javascript">haga_scroll('.$cod_nota_venta.');</script>';
		}
	}
	function redraw(&$temp) {
		$total_dscto = $this->headers['MONTO_DSCTO']->valor_accumulate + $this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate;
		$temp->setVar('SUM_MONTO_DSCTO_TOTAL', number_format(round($total_dscto/1000,0), 0, ',', '.'));
		
		if ($this->headers['SUBTOTAL']->valor_accumulate==0) {
			$porc_directo = 0;
			$porc_corporativo = 0;
			$porc_total = 0;
		}
		else {
			$porc_directo = round(($this->headers['MONTO_DSCTO']->valor_accumulate / $this->headers['SUBTOTAL']->valor_accumulate) * 100, 1);
			$porc_corporativo = round(($this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate / $this->headers['SUBTOTAL']->valor_accumulate) * 100, 1);
			$porc_total = round((($this->headers['MONTO_DSCTO']->valor_accumulate + $this->headers['MONTO_DSCTO_CORPORATIVO']->valor_accumulate) / $this->headers['SUBTOTAL']->valor_accumulate)* 100, 1);
		}
		$temp->setVar('PORC_DSCTO_DIRECTO', number_format($porc_directo, 1, ',', '.'));
		$temp->setVar('PORC_DSCTO_CORPORATIVO', number_format($porc_corporativo, 1, ',', '.'));
		$temp->setVar('PORC_DSCTO_TOTAL', number_format($porc_total, 1, ',', '.'));
				
	}
	function get_totales() {
		// Exporta la data
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = $this->dw->get_sql();
		$res = $db->query($sql);

		$result[0]['SUBTOTAL'] = 0;
		$result[0]['MONTO_DSCTO'] = 0;
		$result[0]['MONTO_DSCTO_CORPORATIVO'] = 0;
		$result[0]['TOTAL_NETO'] = 0;
		$result[0]['TOTAL_VENTA'] = 0;
		$result[0]['DESPACHADO_NETO'] = 0;
		$result[0]['COBRADO_NETO'] = 0;
		$result[0]['POR_COBRAR_NETO'] = 0;
		$result[0]['NV_CONFIRMADA'] = 0;
		$result[0]['NV_X_CONFIRMAR'] = 0;
		$result[0]['CANT_NV'] = 0;
		
		while($my_row = $db->get_row()){
			if ($my_row['COD_ESTADO_NOTA_VENTA']==3)		// nula
				continue;
				
			if ($my_row['COD_ESTADO_NOTA_VENTA']==2 || $my_row['COD_ESTADO_NOTA_VENTA']==4)	// cerrada o confirmada
				$result[0]['NV_CONFIRMADA'] += 1;
			else if ($my_row['COD_ESTADO_NOTA_VENTA']==1)	// emitida
				$result[0]['NV_X_CONFIRMAR'] += 1;
			
			$result[0]['SUBTOTAL'] += $my_row['SUBTOTAL'];
			$result[0]['MONTO_DSCTO'] += $my_row['SUBTOTAL'] - $my_row['TOTAL_NETO'];
			$result[0]['MONTO_DSCTO_CORPORATIVO'] += $my_row['MONTO_DSCTO_CORPORATIVO'];
			$result[0]['TOTAL_NETO'] += $my_row['TOTAL_NETO'];
			$result[0]['DESPACHADO_NETO'] += $my_row['DESPACHADO_NETO'];
			$result[0]['COBRADO_NETO'] += $my_row['COBRADO_NETO'];
			$result[0]['POR_COBRAR_NETO'] += $my_row['POR_COBRAR_NETO'];
			$result[0]['CANT_NV'] += 1;
		}
		$result[0]['TOTAL_VENTA'] = $result[0]['TOTAL_NETO'] - $result[0]['MONTO_DSCTO_CORPORATIVO'];
		
		$indices = array_keys($this->headers);
		for ($i=0; $i<count($this->headers); $i++) {
			$operacion = $this->headers[$indices[$i]]->operacion_accumulate;
			if ($operacion != '')
				$this->headers[$indices[$i]]->valor_accumulate = $result[0][$indices[$i]];
		}
	}
}
?>