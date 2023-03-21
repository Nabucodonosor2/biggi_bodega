<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");
class edit_num_producto extends edit_num{
	function edit_num_producto($field, $size = 16, $maxlen = 16, $num_dec=0, $solo_positivos = true, $readonly=false, $con_separador_miles=true) {
		parent::edit_num($field, $size, $maxlen, $num_dec,$solo_positivos,$readonly, $con_separador_miles);
		
		$this->class = 'input_num2';
	}
}
class dw_producto_compuesto extends dw_producto_compuesto_base{
	function dw_producto_compuesto(){
		$sql = "	SELECT		COD_PRODUCTO_COMPUESTO							
							,P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL
							,PC.COD_PRODUCTO_HIJO COD_PRODUCTO					
							,dbo.f_prod_get_costo_base(COD_PRODUCTO_HIJO) COSTO_BASE_PC
							,dbo.f_prod_compuesto_origen(COD_PRODUCTO_HIJO, 'NOM_PRODUCTO') NOM_PRODUCTO
							,dbo.f_prod_compuesto_origen(COD_PRODUCTO_HIJO, 'PRECIO_VENTA_INTERNO_PC') PRECIO_VENTA_INTERNO_PC
							,(PC.CANTIDAD * dbo.f_prod_compuesto_origen(COD_PRODUCTO_HIJO, 'PRECIO_VENTA_INTERNO_PC')) TOTAL_PRECIO_INTERNO
							,(SELECT PRECIO_VENTA_PUBLICO FROM PRODUCTO WHERE COD_PRODUCTO = COD_PRODUCTO_HIJO) PRECIO_VENTA_PUBLICO_PC	
							,ORDEN ORDEN_PC
							,PC.CANTIDAD
							,PC.GENERA_COMPRA
							,ARMA_COMPUESTO							
				FROM		PRODUCTO_COMPUESTO PC, PRODUCTO P
				WHERE		P.COD_PRODUCTO = '{KEY1}'
							AND P.COD_PRODUCTO = PC.COD_PRODUCTO
				ORDER BY	ORDEN";


		parent::datawindow($sql, 'PRODUCTO_COMPUESTO', true, true);

		$this->add_control(new edit_text('COD_PRODUCTO_COMPUESTO', 20, 20, 'hidden'));
		$this->add_controls_producto_help();
		$this->add_control(new edit_num('ORDEN_PC', 5));
		$this->add_control($control = new edit_check_box('GENERA_COMPRA','S','N'));
		$control->set_onChange("calculo_producto();");	
		$this->add_control($control = new edit_num('CANTIDAD', 4, 4));
		$control->set_onChange("calculo_producto();");			
		$this->add_control(new static_text('PRECIO_VENTA_INTERNO_PC', 10, 8));
		$this->add_control(new static_num('TOTAL_PRECIO_INTERNO'));
		$this->add_control(new edit_check_box('ARMA_COMPUESTO','S','N'));
						
		$this->controls['NOM_PRODUCTO']->size = 52;
		$this->controls['COD_PRODUCTO']->size = 11;
		
		$this->set_first_focus('COD_PRODUCTO');
	}

	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN_PC', $this->row_count() * 10);
		return $row;
	}	
		
	function update($db){
		$sp = 'spu_producto_compuesto';
		for ($i = 0; $i < $this->row_count(); $i++){			
			$statuts = $this->get_status_row($i);
			
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}			
				
			$cod_producto_compuesto = $this->get_item($i, 'COD_PRODUCTO_COMPUESTO');
			$cod_producto_principal = $this->get_item($i, 'COD_PRODUCTO_PRINCIPAL');
			$cod_producto_hijo 		= $this->get_item($i, 'COD_PRODUCTO');
			$orden 					= $this->get_item($i, 'ORDEN_PC');
			$cantidad 				= $this->get_item($i, 'CANTIDAD');
			$genera_compra 			= $this->get_item($i, 'GENERA_COMPRA');
			$arma_compuesto			= $this->get_item($i, 'ARMA_COMPUESTO');
			
			$cod_producto_compuesto = ($cod_producto_compuesto == '') ? "null" : "$cod_producto_compuesto";
			
			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
						
			$param = "'$operacion', $cod_producto_compuesto,'$cod_producto_principal','$cod_producto_hijo',$orden,$cantidad, '$genera_compra', '$arma_compuesto'";
			
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {			
			$cod_producto_compuesto = $this->get_item($i, 'COD_PRODUCTO_COMPUESTO', 'delete');			
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_compuesto")){			
				return false;				
			}			
		}		
		//Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO_PRINCIPAL');			
			$parametros_sp = "'PRODUCTO_COMPUESTO','PRODUCTO', null, '$cod_producto'";			 
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}		
		
		return true;
	}
}

class wi_producto_bodega extends wi_producto_bodega_base{
	const K_BODEGA_EQ_TERMINADO = 2;
	
	function wi_producto_bodega($cod_item_menu) {
		parent::wi_producto_bodega_base($cod_item_menu);
		$sql = "select   P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL 
						,P.COD_PRODUCTO COD_PRODUCTO_H
			            ,NOM_PRODUCTO NOM_PRODUCTO_PRINCIPAL
			            ,TP.COD_TIPO_PRODUCTO
			            ,NOM_TIPO_PRODUCTO
			            ,P.COD_MARCA
			            ,NOM_MARCA
			            ,NOM_PRODUCTO_INGLES
			            ,COD_FAMILIA_PRODUCTO
			            ,LARGO
			            ,ANCHO
			            ,ALTO
			            ,PESO
			            ,(LARGO/100 * ANCHO/100 * ALTO/100) VOLUMEN
			            ,LARGO_EMBALADO
			            ,ANCHO_EMBALADO
			            ,ALTO_EMBALADO
			            ,PESO_EMBALADO
			            ,(LARGO_EMBALADO/100 * ANCHO_EMBALADO/100 * ALTO_EMBALADO/100) VOLUMEN_EMBALADO
			            ,dbo.number_format(dbo.f_prod_get_costo_base(P.COD_PRODUCTO), 0, ',', '.') COSTO_BASE_PI
			            ,FACTOR_VENTA_INTERNO
			            ,PRECIO_VENTA_INTERNO
			            ,dbo.f_redondeo_biggi(dbo.f_prod_get_costo_base(P.COD_PRODUCTO),FACTOR_VENTA_INTERNO) PRECIO_VENTA_INT_SUG
			            ,PRECIO_VENTA_INTERNO PRECIO_VENTA_INTERNO_NO_ING
			            ,FACTOR_VENTA_PUBLICO
			            ,PRECIO_VENTA_PUBLICO			            
			            ,PRECIO_VENTA_PUBLICO PRECIO_VENTA_PUBLICO_H
			            ,dbo.f_redondeo_biggi(dbo.f_prod_get_costo_base(P.COD_PRODUCTO),FACTOR_VENTA_PUBLICO) PRECIO_VENTA_PUB_SUG
			            ,'none' PRECIO_INTERNO_ALTO
			            ,'none' PRECIO_INTERNO_BAJO			            
			            ,'none' PRECIO_PUBLICO_ALTO
			            ,'none' PRECIO_PUBLICO_BAJO
			            ,USA_ELECTRICIDAD
			            ,NRO_FASES MONOFASICO
			            ,NRO_FASES TRIFASICO
			            ,CONSUMO_ELECTRICIDAD
			            ,RANGO_TEMPERATURA
			            ,VOLTAJE
			            ,FRECUENCIA
			            ,NRO_CERTIFICADO_ELECTRICO
			            ,USA_GAS
			            ,POTENCIA
			            ,CONSUMO_GAS
			            ,USA_VAPOR
			            ,NRO_CERTIFICADO_GAS
			            ,CONSUMO_VAPOR
			            ,PRESION_VAPOR
			            ,USA_AGUA_FRIA
			            ,USA_AGUA_CALIENTE
			            ,CAUDAL
			            ,PRESION_AGUA
			            ,DIAMETRO_CANERIA
			            ,USA_VENTILACION
			            ,CAIDA_PRESION
			            ,DIAMETRO_DUCTO
			            ,NRO_FILTROS
			            ,USA_DESAGUE
			            ,DIAMETRO_DESAGUE
			            ,MANEJA_INVENTARIO
			            ,STOCK_CRITICO
			            ,VOLUMEN VOLUMEN_ESP
			            ,TIEMPO_REPOSICION
		                ,FOTO_GRANDE
		                ,FOTO_CHICA
		                ,'' FOTO_CON_CAMBIO
		                ,PL.ES_COMPUESTO
		                ,POTENCIA_KW
		                ,PRECIO_LIBRE
		                ,ES_DESPACHABLE
		                ,'' TABLE_PRODUCTO_COMPUESTO
		                ,'' ULTIMO_REG_INGRESO
		                ,dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_EQ_TERMINADO.", GETDATE()) STOCK
						,0 SUM_TOTAL_PI        
        from   			PRODUCTO P
        				,MARCA M
        				,TIPO_PRODUCTO TP
        				,PRODUCTO_LOCAL PL
        where			P.COD_PRODUCTO = '{KEY1}'
        				AND P.COD_MARCA = M.COD_MARCA
        				AND PL.COD_PRODUCTO = P.COD_PRODUCTO
        				AND P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO";
		$this->dws['dw_producto'] = new datawindow($sql);	

		$this->set_first_focus('COD_PRODUCTO_PRINCIPAL');
		// asigna los formatos
		$this->dws['dw_producto']->add_control(new edit_text('COD_PRODUCTO_H',10, 10, 'hidden'));
		$this->dws['dw_producto']->add_control($control = new edit_text_upper('NOM_PRODUCTO_PRINCIPAL', 100, 100));		
		$control->set_onChange("actualiza_otros_tabs();");
		$this->dws['dw_producto']->add_control(new static_text('COSTO_BASE_PI'));		
					
		$this->dws['dw_producto']->add_control(new static_text('PRECIO_VENTA_INT_SUG'));	
		
		$this->dws['dw_producto']->add_control(new static_num('SUM_TOTAL_PI'));
		$this->dws['dw_producto']->add_control(new static_num('PRECIO_VENTA_INTERNO_NO_ING'));
		$this->dws['dw_producto']->add_control(new static_text('PRECIO_VENTA_PUB_SUG'));		
		
		/*****/
		$this->dws['dw_producto']->add_control($control = new edit_num('FACTOR_VENTA_INTERNO', 16, 16, 1));
		$control->set_onChange("calculo_producto();");
		$this->dws['dw_producto']->add_control($control = new edit_num_producto('PRECIO_VENTA_INTERNO'));
		$control->set_onChange("calculo_producto();");
		$this->dws['dw_producto']->add_control($control = new edit_num('FACTOR_VENTA_PUBLICO', 16, 16, 1));
		$control->set_onChange("calculo_producto();");
		$this->dws['dw_producto']->add_control($control = new edit_num_producto('PRECIO_VENTA_PUBLICO'));
		$control->set_onChange("calculo_producto();");

		$this->dws['dw_producto']->add_control(new edit_text_upper('NOM_PRODUCTO_INGLES', 100, 100));
		$sql = "select		COD_MARCA
              				,NOM_MARCA
              				,ORDEN
        		from     	MARCA
        		order by	ORDEN";
		$this->dws['dw_producto']->add_control(new drop_down_dw('COD_MARCA', $sql, 100));

		$sql = "select		COD_TIPO_PRODUCTO
              				,NOM_TIPO_PRODUCTO
              				,ORDEN
        		from     	TIPO_PRODUCTO
        		order by	ORDEN";
		$this->dws['dw_producto']->add_control($control = new drop_down_dw('COD_TIPO_PRODUCTO', $sql, 100));
		$control->set_onChange("actualiza_otros_tabs();");
		
		$sql = "select    	COD_FAMILIA_PRODUCTO
				           	,NOM_FAMILIA_PRODUCTO
				            ,ORDEN
        		from     	FAMILIA_PRODUCTO
        		order by	ORDEN";

		$this->dws['dw_producto']->add_control($control = new edit_check_box('ES_COMPUESTO','S','N'));
		$control->set_onChange("checked_checkbox();");

		$this->dws['dw_producto']->add_control(new edit_check_box('PRECIO_LIBRE', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_check_box('ES_DESPACHABLE', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new drop_down_dw('COD_FAMILIA_PRODUCTO', $sql, 200));
		$this->dws['dw_producto']->add_control(new edit_num('LARGO'));
		$this->dws['dw_producto']->add_control(new edit_num('ANCHO'));
		$this->dws['dw_producto']->add_control(new edit_num('ALTO'));
		$this->dws['dw_producto']->add_control(new edit_num('PESO'));
		$this->dws['dw_producto']->add_control(new edit_num('LARGO_EMBALADO'));
		$this->dws['dw_producto']->add_control(new edit_num('ANCHO_EMBALADO'));
		$this->dws['dw_producto']->add_control(new edit_num('ALTO_EMBALADO'));
		$this->dws['dw_producto']->add_control(new edit_num('PESO_EMBALADO'));

		$this->dws['dw_producto']->set_computed('VOLUMEN', '[LARGO] * [ANCHO] * [ALTO] / 1000000', 4);
		$this->dws['dw_producto']->set_computed('VOLUMEN_EMBALADO', '[LARGO_EMBALADO] * [ANCHO_EMBALADO] * [ALTO_EMBALADO] / 1000000', 4);
		
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_ELECTRICIDAD', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_radio_button('TRIFASICO', 'T', 'M', 'TRIFASICO', 'NRO_FASES'));
		$this->dws['dw_producto']->add_control(new edit_radio_button('MONOFASICO', 'M', 'T', 'MONOFASICO', 'NRO_FASES'));
		$this->dws['dw_producto']->add_control(new edit_num('CONSUMO_ELECTRICIDAD', 16, 16, 2));
		$this->dws['dw_producto']->add_control(new edit_num('RANGO_TEMPERATURA'));
		$this->dws['dw_producto']->add_control(new edit_num('VOLTAJE'));
		$this->dws['dw_producto']->add_control(new edit_num('FRECUENCIA'));
		$this->dws['dw_producto']->add_control(new edit_text_upper('NRO_CERTIFICADO_ELECTRICO', 100, 100));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_GAS', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_num('POTENCIA'));
		// VMC, 17-08-2011 se deja no ingresable por solicitud de JJ a traves de MH 
		$this->dws['dw_producto']->add_control(new edit_num('CONSUMO_GAS'));
		$this->dws['dw_producto']->add_control(new edit_text_upper('NRO_CERTIFICADO_GAS', 100, 100));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_VAPOR', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_num('POTENCIA_KW'));
		$this->dws['dw_producto']->add_control(new edit_num('CONSUMO_VAPOR'));
		$this->dws['dw_producto']->add_control(new edit_num('PRESION_VAPOR'));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_AGUA_FRIA', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_AGUA_CALIENTE', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_num('CAUDAL'));
		$this->dws['dw_producto']->add_control(new edit_num('PRESION_AGUA'));
		$this->dws['dw_producto']->add_control(new edit_text('DIAMETRO_CANERIA', 10, 10));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_VENTILACION', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_num('CAIDA_PRESION'));
		$this->dws['dw_producto']->add_control(new edit_num('DIAMETRO_DUCTO'));
		$this->dws['dw_producto']->add_control(new edit_num('NRO_FILTROS'));
		$this->dws['dw_producto']->add_control(new edit_check_box('USA_DESAGUE', 'S', 'N'));
		$this->dws['dw_producto']->add_control(new edit_text('DIAMETRO_DESAGUE', 10, 10));
	}
	function load_record(){
		parent::load_record();

		$total_costo_base = 0;
		for ($i=0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++){
			if($this->dws['dw_producto_compuesto']->get_item($i, 'GENERA_COMPRA') == 'S')
				$total_costo_base += $this->dws['dw_producto_compuesto']->get_item($i, 'TOTAL_PRECIO_INTERNO');
		}
		
		$this->dws['dw_producto']->set_item(0, 'SUM_TOTAL_PI', $total_costo_base);

		$factor_venta_int = $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_INTERNO');
		$factor_venta_pub = $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_PUBLICO');
		$factor_venta_int_no_ing = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INTERNO_NO_ING');
		$this->dws['dw_producto']->set_item(0, 'COSTO_BASE_PI',number_format($total_costo_base, 0, ',', '.'));
		$precio_venta_int_sug = $total_costo_base * $factor_venta_int;
		$precio_venta_pub_sug = str_replace(".", "", $factor_venta_int_no_ing) * $factor_venta_pub;

		if($precio_venta_int_sug < 1000)
			$precio_venta_int_sug = round($precio_venta_int_sug, -1);
		else if($precio_venta_int_sug < 20000)
			$precio_venta_int_sug = round($precio_venta_int_sug, -2);
		else if($precio_venta_int_sug < 100000)
			$precio_venta_int_sug = round($precio_venta_int_sug, -3);
		else
			$precio_venta_int_sug = round(($precio_venta_int_sug * 2), -4)/2;

		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_INT_SUG',number_format($precio_venta_int_sug, 0, ',', '.'));
		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_PUB_SUG',number_format($precio_venta_pub_sug, 0, ',', '.'));
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$sql_porc = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO between ".self::K_PARAM_FACTOR_PRE_INT_BAJO."and ".self::K_PARAM_FACTOR_PRE_PUB_ALTO;
		$result = $db->build_results($sql_porc);
		// porcentajes considerados bajo y alto
		$pre_int_bajo = $result[0]['VALOR'];//1.0
		$pre_int_alto = $result[1]['VALOR'];//1.9
		$pre_pub_bajo = $result[2]['VALOR'];//1.0
		$pre_pub_alto = $result[3]['VALOR'];//1.9
		
		$precio_venta_int = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INTERNO');		
		$precio_venta_pub = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUBLICO');		
		 			
		// SE LE DA VALOR DE 0.01 PARA QUE NO OCURRA UNA DIVISION POR CERO
		$precio_venta_int_sug = ($precio_venta_int_sug == 0) ? 0.01 : $precio_venta_int_sug;
		$precio_venta_pub_sug = ($precio_venta_pub_sug == 0) ? 0.01 : $precio_venta_pub_sug;

		// CALCULO DE LABEL PRECIO INT BAJO O ALTO
		$variacion_interno = ($precio_venta_int - $precio_venta_int_sug)/$precio_venta_int_sug;
		if($variacion_interno > $pre_int_alto)
			$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_ALTO','');
		else if($variacion_interno < ($pre_int_bajo * -1))
			$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_BAJO','');
			
		// CALCULO DE LABEL PRECIO PUB BAJO O ALTO						
		$variacion_publico = ($precio_venta_pub - $precio_venta_pub_sug)/$precio_venta_pub_sug;
		if($variacion_publico > $pre_pub_alto)
			$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_ALTO','');
		elseif($variacion_publico < ($pre_pub_bajo * -1))
			$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_BAJO','');
	}
}
?>