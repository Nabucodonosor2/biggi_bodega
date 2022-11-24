<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../ws_client_biggi/class_client_biggi.php");
require_once(dirname(__FILE__) . "/../empresa/class_dw_help_empresa.php");
/*
 Clase : WI_PRODUCTO
 */
////////////////////////////////////////////CLASE dw_atributo_producto////////////////////////////////////////////
class dw_atributo_producto extends datawindow{
	function dw_atributo_producto(){
		$sql = "select    	COD_ATRIBUTO_PRODUCTO
	                        ,ORDEN AP_ORDEN
	                        ,NOM_ATRIBUTO_PRODUCTO
	                        ,COD_PRODUCTO
              	from      	ATRIBUTO_PRODUCTO
              	where      	COD_PRODUCTO = '{KEY1}'
              	order by	ORDEN asc";

		parent::datawindow($sql, 'ATRIBUTO_PRODUCTO', true, true);
		$this->add_control(new edit_num('AP_ORDEN', 10));
		$this->add_control(new edit_text('NOM_ATRIBUTO_PRODUCTO', 100, 1000));

		// asigna los mandatorys
		$this->set_mandatory('AP_ORDEN', 'Orden');
		$this->set_mandatory('NOM_ATRIBUTO_PRODUCTO', 'Atributo');

		// Setea el focus en NOM_ATRIBUTO_PRODUCTO para las nuevas lineas
		$this->set_first_focus('NOM_ATRIBUTO_PRODUCTO');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'AP_ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_atributo_producto';	
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO');
			$orden = $this->get_item($i, 'AP_ORDEN');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$nom_atributo_producto = $this->get_item($i, 'NOM_ATRIBUTO_PRODUCTO');
			$cod_atributo_producto = ($cod_atributo_producto == '') ? "null" : $cod_atributo_producto;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion',$cod_atributo_producto, '$nom_atributo_producto','$cod_producto', $orden";

			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++){
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}
			$cod_atributo_producto = $this->get_item($i, 'COD_ATRIBUTO_PRODUCTO', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_atributo_producto")){
				return false;
			}
		}
		//  Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO');
			$parametros_sp = "'ATRIBUTO_PRODUCTO','PRODUCTO', null, '$cod_producto'";
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}
		return true;
	}
}

//////////////////////////////////DW_PRODUCTO_PROVEEDOR//////////////////////////////////
class dw_producto_proveedor extends dw_help_empresa{
	function dw_producto_proveedor(){
		$sql = "select    	COD_PRODUCTO_PROVEEDOR
	                      	,COD_PRODUCTO
	                      	,COD_INTERNO_PRODUCTO
	                      	,PP.COD_EMPRESA COD_EMPRESA
	                      	,ORDEN
	                      	,RUT
	                      	,DIG_VERIF
	                      	,ALIAS
	                      	,NOM_EMPRESA
	                      	,dbo.f_prod_get_precio_costo(COD_PRODUCTO,PP.COD_EMPRESA,getdate()) PRECIO
	                      	,'N' IS_NEW
            	from      	PRODUCTO_PROVEEDOR PP
                      		,EMPRESA E
            	where     	PP.COD_EMPRESA = E.COD_EMPRESA
	                      	and COD_PRODUCTO = '{KEY1}'
	                      	and PP.ELIMINADO = 'N'
            	order by	ORDEN asc";

		parent::dw_help_empresa($sql, 'PRODUCTO_PROVEEDOR', true, true, 'P');
		// se ajustan tamaños para la ventana de productos
		$this->controls['COD_EMPRESA']->size = 5;
		$this->controls['NOM_EMPRESA']->size = 45;
		$this->controls['ALIAS']->size = 35;

		// deja protected los datos, excepto si la columna es nueva
		$this->set_protect('COD_EMPRESA', "[IS_NEW]=='N'");
		$this->set_protect('NOM_EMPRESA', "[IS_NEW]=='N'");
		$this->set_protect('ALIAS', "[IS_NEW]=='N'");
		$this->set_protect('RUT', "[IS_NEW]=='N'");

		$this->add_control(new edit_num('ORDEN', 4, 10));
		$this->add_control(new edit_text_upper('COD_INTERNO_PRODUCTO', 10, 20));
		//$this->add_control(new edit_precio('PRECIO', 12, 8));		
		$this->add_control($control = new edit_precio('PRECIO', 12, 8));
		$control->set_onChange("set_costo_base_proveedor();");

		// asigna los mandatorys
		$this->set_mandatory('ORDEN', 'Orden');
		$this->set_mandatory('COD_EMPRESA', 'Código del proveedor');
		$this->set_mandatory('PRECIO', 'Precio');

		// Setea el focus en COD_EMPRESA para las nuevas lineas
		$this->set_first_focus('RUT');
	}
	function insert_row($row = -1){
		$row = parent::insert_row($row);
		$this->set_item($row, 'ORDEN', $this->row_count() * 10);
		return $row;
	}
	function update($db){
		$sp = 'spu_producto_proveedor';
		/* Se cambia el ordern, 1ro delete y lugo los insert y update.
		 * Esto porque si el usuario borra y luego agrega la misma empresa,
		 * como por dentro el insert hace una update en caso de que ya exista datos en la bd
		 * entonces luego el delete lo borra.
		 */
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED){
				continue;
			}

			$cod_producto_proveedor = $this->get_item($i, 'COD_PRODUCTO_PROVEEDOR', 'delete');

			if (!$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_proveedor")){
				return false;
			}
		}
		for ($i = 0; $i < $this->row_count(); $i++) {
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW){
				continue;
			}
			$cod_producto_proveedor = $this->get_item($i, 'COD_PRODUCTO_PROVEEDOR');
			$cod_producto = $this->get_item($i, 'COD_PRODUCTO');
			$cod_interno_producto = $this->get_item($i, 'COD_INTERNO_PRODUCTO');
			$cod_empresa = $this->get_item($i, 'COD_EMPRESA');
			$orden = $this->get_item($i, 'ORDEN');
			$precio = $this->get_item($i, 'PRECIO');
			$cod_usuario = session::get("COD_USUARIO");

			$cod_producto_proveedor = ($cod_producto_proveedor == '') ? "null" : $cod_producto_proveedor;

			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
			$param = "'$operacion', $cod_producto_proveedor, $cod_empresa, '$cod_producto', '$cod_interno_producto', $precio, $orden,$cod_usuario";
						
			if (!$db->EXECUTE_SP($sp, $param)){
				return false;
			}
		}
		//Ordernar
		if ($this->row_count() > 0){
			$cod_producto = $this->get_item(0, 'COD_PRODUCTO');			 
			$parametros_sp = "'PRODUCTO_PROVEEDOR','PRODUCTO', null, '$cod_producto'";			 
			if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp)){
				return false;
			}
		}
		return true;
	}
}

//////////////////////////////////DW_PRODUCTO_COMPUESTO//////////////////////////////////
class dw_producto_compuesto_base extends datawindow{
	function dw_producto_compuesto_base(){
	$sql = "	SELECT		COD_PRODUCTO_COMPUESTO							
							,P.COD_PRODUCTO COD_PRODUCTO_PRINCIPAL
							,PC.COD_PRODUCTO_HIJO COD_PRODUCTO					
							,(SELECT NOM_PRODUCTO FROM PRODUCTO WHERE COD_PRODUCTO = COD_PRODUCTO_HIJO) NOM_PRODUCTO
							,dbo.f_prod_get_costo_base(COD_PRODUCTO_HIJO) COSTO_BASE_PC
							,(SELECT PRECIO_VENTA_INTERNO FROM PRODUCTO WHERE COD_PRODUCTO = COD_PRODUCTO_HIJO) PRECIO_VENTA_INTERNO_PC
							,(SELECT PRECIO_VENTA_PUBLICO FROM PRODUCTO WHERE COD_PRODUCTO = COD_PRODUCTO_HIJO) PRECIO_VENTA_PUBLICO_PC							 													
							,ORDEN ORDEN_PC
							,PC.CANTIDAD
							,PC.GENERA_COMPRA 							
				FROM		PRODUCTO_COMPUESTO PC, PRODUCTO P
				WHERE		P.COD_PRODUCTO = '{KEY1}'
							AND P.COD_PRODUCTO = PC.COD_PRODUCTO
				ORDER BY	ORDEN";


	parent::datawindow($sql, 'PRODUCTO_COMPUESTO', true, true);

		$this->add_control(new edit_text('COD_PRODUCTO_COMPUESTO', 20, 20, 'hidden'));
		$this->add_controls_producto_help();
		$this->add_control(new edit_num('ORDEN_PC', 5));
		$this->add_control(new edit_check_box('GENERA_COMPRA','S','N'));	
		$this->add_control($control = new edit_num('CANTIDAD', 8, 8));
		$control->set_onBlur("tot_costo_base(this); calc_precio_int_pub(); redondeo_biggi();");
		$this->add_control(new static_text('COSTO_BASE_PC', 10, 8));
		$this->set_computed('TOTAL_COSTO_BASE', '[CANTIDAD] * [COSTO_BASE_PC]');
		$this->accumulate('TOTAL_COSTO_BASE');				
		$this->add_control(new static_text('PRECIO_VENTA_INTERNO_PC', 10, 8));
		$this->set_computed('TOTAL_PRECIO_INTERNO', '[CANTIDAD] * [PRECIO_VENTA_INTERNO_PC]');
		$this->accumulate('TOTAL_PRECIO_INTERNO');
		$this->add_control(new static_text('PRECIO_VENTA_PUBLICO_PC', 10, 8));
		$this->set_computed('TOTAL_PRECIO_PUBLICO', '[CANTIDAD] * [PRECIO_VENTA_PUBLICO_PC]');
		$this->accumulate('TOTAL_PRECIO_PUBLICO');
		
		$this->add_control(new static_text('PRECIO_VENTA_PUBLICO_PC', 10, 8));
		
						
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
			
			$cod_producto_compuesto = ($cod_producto_compuesto == '') ? "null" : "$cod_producto_compuesto";
			
			if ($statuts == K_ROW_NEW_MODIFIED){
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED){
				$operacion = 'UPDATE';
			}
						
			$param = "'$operacion', $cod_producto_compuesto,'$cod_producto_principal','$cod_producto_hijo',$orden,$cantidad, '$genera_compra'";
			
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

	

//////////////////////////////////DW_FOTOS_PRODUCTO//////////////////////////////////
class dw_fotos_producto extends datawindow{
	function dw_fotos_producto(){
		$sql = "select	FOTO_CHICA
              			,FOTO_GRANDE
        		from    PRODUCTO
        		where   COD_PRODUCTO = '{KEY1}'";
	}
}

class wi_producto_bodega_base extends w_input{
	const K_PARAM_FAC_VTA_INT		= 22;
	const K_PARAM_FAC_VTA_PUB		= 23;
	const K_PARAM_FACTOR_PRE_INT_BAJO =34;
	const K_PARAM_FACTOR_PRE_INT_ALTO =35;
	const K_PARAM_FACTOR_PRE_PUB_BAJO =36;
	const K_PARAM_FACTOR_PRE_PUB_ALTO =37;
	const K_PARAM_SISTEMA = 3;
	const K_HABILITA_ACTUALIZA_PRECIO = '995005';
	const K_HABILITA_ACTUALIZA_PRECIO_INT = '996005';
	const K_HABILITA_ACTUALIZA_PRECIO_PUB = '996010';
	
	function wi_producto_bodega_base($cod_item_menu){
		/////////////////////////////////////////////TAB_1/////////////////////////////////////////////
		parent::w_input('producto_bodega', $cod_item_menu);
		// valida que la PK sea unica
		$this->valida_llave = true;
		
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
			            ,VOLUMEN VOLUMEN_ESP
			            ,NRO_FILTROS
			            ,USA_DESAGUE
			            ,DIAMETRO_DESAGUE
			            ,MANEJA_INVENTARIO
			            ,STOCK_CRITICO
			            ,TIEMPO_REPOSICION
		                ,FOTO_GRANDE
		                ,FOTO_CHICA
		                ,'N' FOTO_CON_CAMBIO
		                ,PL.ES_COMPUESTO
		                ,PRECIO_LIBRE
		                ,ES_DESPACHABLE
		                ,'' TABLE_PRODUCTO_COMPUESTO
		                ,'' ULTIMO_REG_INGRESO	  
		                ,P.POTENCIA_KW              
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
		$this->dws['dw_producto']->add_control($control = new edit_porcentaje('FACTOR_VENTA_INTERNO',16,6));
		$control->set_onChange("redondeo_biggi();calc_precio_int_pub();");				
		$this->dws['dw_producto']->add_control(new static_text('PRECIO_VENTA_INT_SUG'));	
		$this->dws['dw_producto']->add_control($control = new edit_num('PRECIO_VENTA_INTERNO'));		
		$control->set_onBlur("calc_precio_int_pub();");		
		$this->dws['dw_producto']->add_control(new static_text('PRECIO_VENTA_INTERNO_NO_ING'));
		$this->dws['dw_producto']->add_control($control = new edit_porcentaje('FACTOR_VENTA_PUBLICO',16,6));
		$control->set_onChange("calc_precio_int_pub();");
		$this->dws['dw_producto']->add_control(new static_text('PRECIO_VENTA_PUB_SUG'));		
		$this->dws['dw_producto']->add_control($control = new edit_num('PRECIO_VENTA_PUBLICO'));
		$control->set_onBlur("calc_precio_int_pub();");	
			
		
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
		$this->dws['dw_producto']->add_control(new edit_num('VOLUMEN_ESP'));
		$this->dws['dw_producto']->set_computed('VOLUMEN', '[LARGO] * [ANCHO] * [ALTO] / 1000000', 4);
		$this->dws['dw_producto']->set_computed('VOLUMEN_EMBALADO', '[LARGO_EMBALADO] * [ANCHO_EMBALADO] * [ALTO_EMBALADO] / 1000000', 4);

		// asigna los mandatorys
		$this->dws['dw_producto']->set_mandatory('COD_PRODUCTO_PRINCIPAL', 'Código del producto');
		$this->dws['dw_producto']->set_mandatory('NOM_PRODUCTO_PRINCIPAL', 'Descipción del producto');
		$this->dws['dw_producto']->set_mandatory('COD_TIPO_PRODUCTO', 'Tipo Producto');
		$this->dws['dw_producto']->set_mandatory('COD_MARCA', 'Marca Producto');
		$this->dws['dw_producto']->set_mandatory('LARGO', 'Largo Producto');
		$this->dws['dw_producto']->set_mandatory('ANCHO', 'Ancho Producto');
		$this->dws['dw_producto']->set_mandatory('ALTO', 'Alto Producto');
		$this->dws['dw_producto']->set_mandatory('PESO', 'Peso Producto');
		$this->dws['dw_producto']->set_mandatory('LARGO_EMBALADO', 'Largo Embalado Producto');
		$this->dws['dw_producto']->set_mandatory('ANCHO_EMBALADO', 'Ancho Embalado Producto');
		$this->dws['dw_producto']->set_mandatory('ALTO_EMBALADO', 'Alto Embalado Producto');
		$this->dws['dw_producto']->set_mandatory('ALTO_EMBALADO', 'Peso Embalado');
		$this->dws['dw_producto']->set_mandatory('FACTOR_VENTA_INTERNO', 'Factor Venta Interno');
		$this->dws['dw_producto']->set_mandatory('PRECIO_VENTA_INTERNO', 'Precio Venta Interno');
		$this->dws['dw_producto']->set_mandatory('FACTOR_VENTA_PUBLICO', 'Factor Venta Público');
		$this->dws['dw_producto']->set_mandatory('PRECIO_VENTA_PUBLICO', 'Precio Venta Público');
		$this->dws['dw_producto']->set_mandatory('USA_ELECTRICIDAD', 'Usa Electricidad');
		$this->dws['dw_producto']->set_mandatory('USA_GAS', 'Usa Gas');
		$this->dws['dw_producto']->set_mandatory('USA_VAPOR', 'Usa Vapor');
		$this->dws['dw_producto']->set_mandatory('USA_AGUA_FRIA', 'Usa Agua Fria');
		$this->dws['dw_producto']->set_mandatory('USA_AGUA_CALIENTE', 'Usa Agua Caliente');
		$this->dws['dw_producto']->set_mandatory('USA_VENTILACION', 'Usa Ventilación');
		$this->dws['dw_producto']->set_mandatory('USA_DESAGUE', 'Usa Desague');
		$this->dws['dw_producto']->set_mandatory('MANEJA_INVENTARIO', 'Maneja Invetario');

		$this->dws['dw_atributo_producto'] = new dw_atributo_producto();

		/////////////////////////////////////////////TAB_2/////////////////////////////////////////////
		$this->dws['dw_producto_compuesto'] = new dw_producto_compuesto();
		//$this->dws['dw_producto']->add_control(new edit_num('NRO_RI'));
		//$this->dws['dw_producto']->add_control(new edit_date('FECHA_RI'));

		/////////////////////////////////////////////TAB_3/////////////////////////////////////////////
		$this->dws['dw_producto_proveedor'] = new dw_producto_proveedor();

		/////////////////////////////////////////////TAB_4/////////////////////////////////////////////
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
		$this->dws['dw_producto']->add_control(new edit_text('FOTO_CON_CAMBIO', 10, 10, 'hidden'));
		
		// Auditoria
		$this->add_auditoria('NOM_PRODUCTO');
		$this->add_auditoria('COD_TIPO_PRODUCTO');
		$this->add_auditoria('COD_MARCA');
		$this->add_auditoria('LARGO');
		$this->add_auditoria('ANCHO');
		$this->add_auditoria('ALTO');
		$this->add_auditoria('PESO');
		$this->add_auditoria('LARGO_EMBALADO');
		$this->add_auditoria('ANCHO_EMBALADO');
		$this->add_auditoria('ALTO_EMBALADO');
		$this->add_auditoria('PESO_EMBALADO');
		$this->add_auditoria('FACTOR_VENTA_INTERNO');
		$this->add_auditoria('PRECIO_VENTA_INTERNO');
		$this->add_auditoria('FACTOR_VENTA_PUBLICO');
		$this->add_auditoria('PRECIO_VENTA_PUBLICO');
		$this->add_auditoria('USA_ELECTRICIDAD');
		$this->add_auditoria('NRO_FASES');
		$this->add_auditoria('CONSUMO_ELECTRICIDAD');
		$this->add_auditoria('RANGO_TEMPERATURA');
		$this->add_auditoria('VOLTAJE');
		$this->add_auditoria('FRECUENCIA');
		$this->add_auditoria('NRO_CERTIFICADO_ELECTRICO');
		$this->add_auditoria('USA_GAS');
		$this->add_auditoria('POTENCIA');
		$this->add_auditoria('CONSUMO_GAS');
		$this->add_auditoria('NRO_CERTIFICADO_GAS');
		$this->add_auditoria('STOCK_CRITICO');
		$this->add_auditoria('TIEMPO_REPOSICION');
		//$this->add_auditoria('ES_COMPUESTO');
		$this->add_auditoria('PRECIO_LIBRE');
		$this->add_auditoria('ES_DESPACHABLE');
		$this->add_auditoria_relacionada('PRODUCTO_PROVEEDOR', 'PRECIO', 'dbo.f_prod_get_precio_costo(A.COD_PRODUCTO, COD_EMPRESA,getdate())');
		$this->add_auditoria_relacionada('PRODUCTO_COMPUESTO', 'COD_PRODUCTO_HIJO');
		$this->add_auditoria_relacionada('PRODUCTO_COMPUESTO', 'CANTIDAD');
		$this->add_auditoria_relacionada('ATRIBUTO_PRODUCTO', 'NOM_ATRIBUTO_PRODUCTO');
	}

	function new_record(){
		$this->dws['dw_producto']->insert_row();
		$this->dws['dw_producto']->set_item(0, 'COSTO_BASE_PI', '0');
		$this->dws['dw_producto']->add_control($control = new edit_text_upper('COD_PRODUCTO_PRINCIPAL', 30, 40));		
		$control->set_onChange("actualiza_otros_tabs();");
		$this->dws['dw_producto']->set_item(0, 'TABLE_PRODUCTO_COMPUESTO', 'none');
		
		$fac_vta_int = $this->get_parametro(self::K_PARAM_FAC_VTA_INT);
		$this->dws['dw_producto']->set_item(0, 'FACTOR_VENTA_INTERNO',$fac_vta_int);	
		$fac_vta_pub = $this->get_parametro(self::K_PARAM_FAC_VTA_PUB);
		$this->dws['dw_producto']->set_item(0, 'FACTOR_VENTA_PUBLICO',$fac_vta_pub);
		
		$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_ALTO','none');
		$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_ALTO','none');
		$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_BAJO','none');
		$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_BAJO','none');
		
		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_INT_SUG','0');
		$this->dws['dw_producto']->set_item(0, 'ES_DESPACHABLE','S');
		
		$this->dws['dw_producto']->set_item(0, 'ES_COMPUESTO','N');	
		$this->dws['dw_producto']->set_item(0, 'MANEJA_INVENTARIO','N');	
	}
	
	function make_sql_auditoria(){
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$res = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $res;
	}
	
	function make_sql_auditoria_relacionada($tabla) {
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'PRODUCTO';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	
	function load_record(){
		$cod_producto = $this->get_item_wo($this->current_record, 'COD_PRODUCTO');
		/////////////////////////////////////////////TAB_1/////////////////////////////////////////////
		$this->dws['dw_producto']->retrieve($cod_producto);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		 
		$this->b_print_visible 	 = true;
		
		$sql = "SELECT PP.COD_EMPRESA COD_EMPRESA
			                      	,ORDEN
									,dbo.f_prod_get_precio_costo(COD_PRODUCTO,PP.COD_EMPRESA,GETDATE()) PRECIO	        
		            	FROM      	PRODUCTO_PROVEEDOR PP
		                      		,EMPRESA E
		            	WHERE     	PP.COD_EMPRESA = E.COD_EMPRESA
			                      	AND COD_PRODUCTO = '$cod_producto'
			                      	AND PP.ELIMINADO = 'N'
		            	ORDER BY	ORDEN asc";
		$result = $db->build_results($sql);
		
		if (count($result)> 0)
			$precio_prov_1 = $result[0]['PRECIO'];
		else
			$precio_prov_1 = 0;
			
		$es_compuesto = $this->dws['dw_producto']->get_item(0, 'ES_COMPUESTO');
		
		if ($es_compuesto == 'S'){
			$this->dws['dw_producto']->set_item(0, 'TABLE_PRODUCTO_COMPUESTO', '');
			$this->dws['dw_producto']->set_item(0, 'ULTIMO_REG_INGRESO', 'none');
		}
		else{
			$this->dws['dw_producto']->set_item(0, 'TABLE_PRODUCTO_COMPUESTO', 'none');
			$this->dws['dw_producto']->set_item(0, 'ULTIMO_REG_INGRESO', '');
			//se setea este valor hasta que se realice la funcion correspondiente
			$this->dws['dw_producto']->set_item(0, 'COSTO_BASE_PI',number_format($precio_prov_1, 0, ',', '.'));
		}		
		
		
		//////////////////////////
		
		
		////////// PRECIO INTERNO Y PUBLICO ///////////////
		/**	
		si se modifica esta funcion tambien debe modificarse en la funcion calc_precio_int_pub()
		de producto.js
		**/
		/*
		$sql_porc = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO between ".self::K_PARAM_FACTOR_PRE_INT_BAJO."and ".self::K_PARAM_FACTOR_PRE_PUB_ALTO;
		$result = $db->build_results($sql_porc);
		// porcentajes considerados bajo y alto
		$pre_int_bajo = $result[0]['VALOR'];
		$pre_int_alto = $result[1]['VALOR'];
		$pre_pub_bajo = $result[2]['VALOR'];
		$pre_pub_alto = $result[3]['VALOR'];
		 
		$factor_venta_int = $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_INTERNO');		
		$factor_venta_pub = $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_PUBLICO');
		
		$precio_venta_int_sug = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INT_SUG');		
		$precio_venta_int = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INTERNO');		
		$precio_venta_pub_sug = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUB_SUG');		 
		$precio_venta_pub = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUBLICO');		
		 
				
		// SE LE DA VALOR DE 0.01 PARA QUE NO OCURRA UNA DIVISION POR CERO
		$precio_venta_int_sug = ($precio_venta_int_sug == 0) ? 0.01 : $precio_venta_int_sug;
		$precio_venta_pub_sug = ($precio_venta_pub_sug == 0) ? 0.01 : $precio_venta_pub_sug;

		// CALCULO DE LABEL PRECIO INT BAJO O ALTO
		$variacion_interno = ($precio_venta_int - $precio_venta_int_sug)/$precio_venta_int_sug;
		if($variacion_interno > $pre_int_alto)
			$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_ALTO','');
		elseif($variacion_interno < ($pre_int_bajo * -1))
			$this->dws['dw_producto']->set_item(0, 'PRECIO_INTERNO_BAJO','');
			
		// CALCULO DE LABEL PRECIO PUB BAJO O ALTO						
		$variacion_publico = ($precio_venta_pub - $precio_venta_pub_sug)/$precio_venta_pub_sug;
		if($variacion_publico > $pre_pub_alto)
			$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_ALTO','');
		elseif($variacion_publico < ($pre_pub_bajo * -1))
			$this->dws['dw_producto']->set_item(0, 'PRECIO_PUBLICO_BAJO','');		
	
			
		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_PUB_SUG',number_format($precio_venta_pub_sug, 0, ',', '.'));
		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_INT_SUG',number_format($precio_venta_int_sug, 0, ',', '.'));
		$this->dws['dw_producto']->set_item(0, 'PRECIO_VENTA_INTERNO_NO_ING',number_format($precio_venta_int, 0, ',', '.'));
		*/
		////////// FIN PRECIO INTERNO Y PUBLICO ///////////////
		
			
		$this->dws['dw_atributo_producto']->retrieve($cod_producto);
		/////////////////////////////////////////////TAB_2/////////////////////////////////////////////
		$this->dws['dw_producto_compuesto']->retrieve($cod_producto);
		/////////////////////////////////////////////TAB_3/////////////////////////////////////////////
		$this->dws['dw_producto_proveedor']->retrieve($cod_producto);
		

		////////// FORMATO A DATAWINDOWS PRODUCTO_COMPUESTO ///////////////		
		$total_costo_base = 0;		
		for ($i = 0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++){
			$costo_base = $this->dws['dw_producto_compuesto']->get_item($i, 'COSTO_BASE_PC');			 
			$precio_interno = $this->dws['dw_producto_compuesto']->get_item($i, 'PRECIO_VENTA_INTERNO_PC');
			$precio_publico = $this->dws['dw_producto_compuesto']->get_item($i, 'PRECIO_VENTA_PUBLICO_PC');
			$this->dws['dw_producto_compuesto']->set_item($i, 'COSTO_BASE_PC',number_format($costo_base, 0, ',', '.'));								
			$this->dws['dw_producto_compuesto']->set_item($i, 'PRECIO_VENTA_INTERNO_PC',number_format($precio_interno, 0, ',', '.'));
			$this->dws['dw_producto_compuesto']->set_item($i, 'PRECIO_VENTA_PUBLICO_PC',number_format($precio_publico, 0, ',', '.'));
			
			//////// caluclo del costo Base //////
			$cantidad = $this->dws['dw_producto_compuesto']->get_item($i, 'CANTIDAD');			
			$total_costo_base += $costo_base * $cantidad;
		}		
		if ($es_compuesto == 'S')
			// SETEA EL COSTO BASE TOTAL
			$this->dws['dw_producto']->set_item(0, 'COSTO_BASE_PI',number_format($total_costo_base, 0, ',', '.'));
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_HABILITA_ACTUALIZA_PRECIO_INT, $this->cod_usuario);
		if ($priv == 'E')
			$this->dws['dw_producto']->set_entrable('PRECIO_VENTA_INTERNO', true);	
		else
			$this->dws['dw_producto']->set_entrable('PRECIO_VENTA_INTERNO', false);
			
		$priv = $this->get_privilegio_opcion_usuario(self::K_HABILITA_ACTUALIZA_PRECIO_PUB, $this->cod_usuario);
		if ($priv == 'E')
			$this->dws['dw_producto']->set_entrable('PRECIO_VENTA_PUBLICO', true);	
		else
			$this->dws['dw_producto']->set_entrable('PRECIO_VENTA_PUBLICO', false);	
	}

	function validate_record() {
		$cod_producto_principal = $this->dws['dw_producto']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		$es_compuesto = $this->dws['dw_producto']->get_item(0, 'ES_COMPUESTO');		
		$row_count = $this->dws['dw_producto_compuesto']->row_count();
		
		if($row_count == 0 && $es_compuesto == 'S')
			return 'Debe ingresar al menos un Producto Compuesto';
		if($row_count > 0){
			for ($i = 0; $i < $row_count; $i++){
				$cod_producto_hijo 		= $this->dws['dw_producto_compuesto']->get_item($i, 'COD_PRODUCTO');				
				$cantidad = $this->dws['dw_producto_compuesto']->get_item($i, 'CANTIDAD');
				if($cantidad == ''){
					return 'Debe ingresar cantidad al producto compuesto: '."'$cod_producto_hijo'".'.';					
				}				
			}					 
		}								
	}	
	
	function habilitar($temp, $habilita){		
		
		$precio_venta_pub_h = $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUBLICO_H');
		$precio_venta_pub_h =  number_format($precio_venta_pub_h, 0, ',', '.');		
		$temp->setVar("PRECIO_VENTA_PUBLICO_NO_ING",$precio_venta_pub_h);
		
		$temp->setVar("COD_PRODUCTO_NO_ING", $this->dws['dw_producto']->get_item(0, 'COD_PRODUCTO_PRINCIPAL'));
		$temp->setVar("NOM_PRODUCTO_NO_ING", $this->dws['dw_producto']->get_item(0, 'NOM_PRODUCTO_PRINCIPAL'));
		$temp->setVar("COD_MARCA_NO_ING", $this->dws['dw_producto']->get_item(0, 'COD_MARCA'));
		$temp->setVar("COD_TIPO_PRODUCTO_NO_ING", $this->dws['dw_producto']->get_item(0, 'COD_TIPO_PRODUCTO'));

		$html = '';
		for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++)
		$html .= '<img src="../../../../commonlib/trunk/images/ico2.gif"width="14"height="15">' . $this->dws['dw_atributo_producto']->get_item($i, 'NOM_ATRIBUTO_PRODUCTO') . '<br>';
		$temp->setVar("LISTA_ATRIBUTOS", $html);
	}

	function get_key(){
		$cod_producto = $this->dws['dw_producto']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		return "'" . $cod_producto . "'";
	}

	function save_record($db){		
		$cod_producto 				= $this->dws['dw_producto']->get_item(0, 'COD_PRODUCTO_PRINCIPAL');
		$nom_producto 				= $this->dws['dw_producto']->get_item(0, 'NOM_PRODUCTO_PRINCIPAL');
		$cod_tipo_producto 			= $this->dws['dw_producto']->get_item(0, 'COD_TIPO_PRODUCTO');
		$cod_marca 					= $this->dws['dw_producto']->get_item(0, 'COD_MARCA');
		$nom_producto_ingles 		= $this->dws['dw_producto']->get_item(0, 'NOM_PRODUCTO_INGLES');
		$cod_familia_producto 		= $this->dws['dw_producto']->get_item(0, 'COD_FAMILIA_PRODUCTO');
		$largo 						= $this->dws['dw_producto']->get_item(0, 'LARGO');
		$ancho 						= $this->dws['dw_producto']->get_item(0, 'ANCHO');
		$alto 						= $this->dws['dw_producto']->get_item(0, 'ALTO');
		$peso 						= $this->dws['dw_producto']->get_item(0, 'PESO');
		$largo_embalado 			= $this->dws['dw_producto']->get_item(0, 'LARGO_EMBALADO');
		$ancho_embalado 			= $this->dws['dw_producto']->get_item(0, 'ANCHO_EMBALADO');
		$alto_embalado 				= $this->dws['dw_producto']->get_item(0, 'ALTO_EMBALADO');
		$peso_embalado 				= $this->dws['dw_producto']->get_item(0, 'PESO_EMBALADO');
		$factor_venta_interno 		= $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_INTERNO');
		$precio_venta_interno 		= $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_INTERNO');
		$factor_venta_publico 		= $this->dws['dw_producto']->get_item(0, 'FACTOR_VENTA_PUBLICO');
		$precio_venta_publico 		= $this->dws['dw_producto']->get_item(0, 'PRECIO_VENTA_PUBLICO');	
		$usa_electricidad 			= $this->dws['dw_producto']->get_item(0, 'USA_ELECTRICIDAD');
		$nro_fases 					= $this->dws['dw_producto']->get_item(0, 'TRIFASICO');
		$consumo_electricidad 		= $this->dws['dw_producto']->get_item(0, 'CONSUMO_ELECTRICIDAD');
		$rango_temperatura 			= $this->dws['dw_producto']->get_item(0, 'RANGO_TEMPERATURA');
		$voltaje 					= $this->dws['dw_producto']->get_item(0, 'VOLTAJE');
		$nro_certificado_electrico 	= $this->dws['dw_producto']->get_item(0, 'NRO_CERTIFICADO_ELECTRICO');
		$frecuencia 				= $this->dws['dw_producto']->get_item(0, 'FRECUENCIA');
		$usa_gas 					= $this->dws['dw_producto']->get_item(0, 'USA_GAS');
		$potencia 					= $this->dws['dw_producto']->get_item(0, 'POTENCIA');
		$consumo_gas 				= $this->dws['dw_producto']->get_item(0, 'CONSUMO_GAS');
		$nro_certificado_gas 		= $this->dws['dw_producto']->get_item(0, 'NRO_CERTIFICADO_GAS');
		$usa_vapor 					= $this->dws['dw_producto']->get_item(0, 'USA_VAPOR');
		$consumo_vapor 				= $this->dws['dw_producto']->get_item(0, 'CONSUMO_VAPOR');
		$presion_vapor 				= $this->dws['dw_producto']->get_item(0, 'PRESION_VAPOR');
		$usa_agua_fria 				= $this->dws['dw_producto']->get_item(0, 'USA_AGUA_FRIA');
		$usa_agua_caliente 			= $this->dws['dw_producto']->get_item(0, 'USA_AGUA_CALIENTE');
		$caudal 					= $this->dws['dw_producto']->get_item(0, 'CAUDAL');
		$presion_agua 				= $this->dws['dw_producto']->get_item(0, 'PRESION_AGUA');
		$diametro_caneria 			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_CANERIA');
		$usa_ventilacion 			= $this->dws['dw_producto']->get_item(0, 'USA_VENTILACION');
		$volumen				= $this->dws['dw_producto']->get_item(0, 'VOLUMEN_ESP');
		$caida_presion 				= $this->dws['dw_producto']->get_item(0, 'CAIDA_PRESION');
		$diametro_ducto 			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_DUCTO');
		$nro_filtros 				= $this->dws['dw_producto']->get_item(0, 'NRO_FILTROS');
		$usa_desague 				= $this->dws['dw_producto']->get_item(0, 'USA_DESAGUE');
		$diametro_desague			= $this->dws['dw_producto']->get_item(0, 'DIAMETRO_DESAGUE');
		$maneja_inventario 			= 'N';
		$stock_critico 				= 0;
		$tiempo_reposicion			= 0;
		$foto_grande 				= $this->dws['dw_producto']->get_item(0, 'FOTO_GRANDE');
		$foto_chica 				= $this->dws['dw_producto']->get_item(0, 'FOTO_CHICA');
		$es_compuesto 				= $this->dws['dw_producto']->get_item(0, 'ES_COMPUESTO');
		$precio_libre 				= $this->dws['dw_producto']->get_item(0, 'PRECIO_LIBRE');
		$es_despachable 			= $this->dws['dw_producto']->get_item(0, 'ES_DESPACHABLE');	
		$potencia_kw				= $this->dws['dw_producto']->get_item(0, 'POTENCIA_KW');

		$nom_producto_ingles 		= ($nom_producto_ingles == '') ? "null" : "'$nom_producto_ingles'";
		$cod_familia_producto 		= ($cod_familia_producto == '') ? "null" : $cod_familia_producto;
		$nro_fases 					= ($nro_fases == '') ? "null" : "'$nro_fases'";
		$consumo_electricidad		= ($consumo_electricidad == '') ? "null" : $consumo_electricidad;
		$rango_temperatura 			= ($rango_temperatura == '') ? "null" : "'$rango_temperatura'";
		$voltaje 					= ($voltaje == '') ? "null" : $voltaje;
		$frecuencia 				= ($frecuencia == '') ? "null" : $frecuencia;
		$nro_certificado_electrico	= ($nro_certificado_electrico == '') ? "null" : "'$nro_certificado_electrico'";
		$potencia 					= ($potencia == '') ? "null" : $potencia;
		$consumo_gas 				= ($consumo_gas == '') ? "null" : $consumo_gas;
		$nro_certificado_gas 		= ($nro_certificado_gas == '') ? "null" : "'$nro_certificado_gas'";
		$consumo_vapor 				= ($consumo_vapor == '') ? "null" : $consumo_vapor;
		$presion_vapor 				= ($presion_vapor == '') ? "null" : $presion_vapor;
		$caudal 					= ($caudal == '') ? "null" : $caudal;
		$presion_agua 				= ($presion_agua == '') ? "null" : $presion_agua;
		$diametro_caneria 			= ($diametro_caneria == '') ? "null" : "'$diametro_caneria'";
		$volumen 					= ($volumen == '') ? "null" : $volumen;
		$caida_presion 				= ($caida_presion == '') ? "null" : $caida_presion;
		$potencia_kw				= ($potencia_kw == '') ? "null" : $potencia_kw;
		$diametro_ducto 			= ($diametro_ducto == '') ? "null" : $diametro_ducto;
		$nro_filtros 				= ($nro_filtros == '') ? "null" : $nro_filtros;
		$diametro_desague 			= ($diametro_desague == '') ? "null" : "'$diametro_desague'";
		$stock_critico 				= ($stock_critico == '') ? "null" : $stock_critico;
		$foto_grande 				= ($foto_grande == '') ? "null" : $foto_grande;
		$foto_chica 				= ($foto_chica == '') ? "null" : $foto_chica;
		$cod_producto 				= ($cod_producto == '') ? "null" : $cod_producto;
		$cod_producto_local			= ($cod_producto_local == '') ? "null" : $cod_producto_local;
		$precio_venta_interno		= ($precio_venta_interno=='')? 0 : $precio_venta_interno;
		$precio_venta_publico		= ($precio_venta_publico=='')? 0 : $precio_venta_publico;
		
		if(K_CLIENTE == "BODEGA")
		{
			//se actualiza el precio del producto si tiene cambios 
			$this->update_costo_producto($cod_producto,$precio_venta_interno,"RENTAL");
			$this->update_costo_producto($cod_producto,$precio_venta_interno,"COMERCIAL");
			$this->update_costo_producto($cod_producto,$precio_venta_interno,"TODOINOX");
		}
		
		$sp = 'dbo.spu_producto';
		
		if ($this->is_new_record()){
			$operacion = 'INSERT';
		}
		else{
			$operacion = 'UPDATE';
		}

		/*marca en campo SISTEMA_VALIDO para que sistema es válido el equipo
		 * solo en el insert del equipo se asignará valor, por lo tanto no tiene update
		 */
		$sistema = $this->get_parametro(self::K_PARAM_SISTEMA);
		if ($sistema == 'COMERCIAL')
			$sistema_valido = 'SSSS';
		else if ($sistema == 'BODEGA')
			$sistema_valido = 'NSNN';
		else if ($sistema == 'CATERING')
			$sistema_valido = 'NNSN';
		else if ($sistema == 'TODOINOX')	
			$sistema_valido = 'NNNS';	
		
			
		$param = "  '$operacion','$cod_producto','$nom_producto',$cod_tipo_producto,$cod_marca,$nom_producto_ingles,
		$cod_familia_producto,$largo,$ancho,$alto,$peso,$largo_embalado,$ancho_embalado,
		$alto_embalado,$peso_embalado,$factor_venta_interno,$precio_venta_interno,
		$factor_venta_publico,$precio_venta_publico,'$usa_electricidad',$nro_fases,
		$consumo_electricidad,$rango_temperatura,$voltaje,$frecuencia,$nro_certificado_electrico,
		'$usa_gas',$potencia,$consumo_gas,$nro_certificado_gas,'$usa_vapor',$consumo_vapor,
		$presion_vapor,'$usa_agua_fria','$usa_agua_caliente',$caudal,$presion_agua,$diametro_caneria,
		'$usa_ventilacion',$volumen,$caida_presion,$diametro_ducto,$nro_filtros,'$usa_desague',
		$diametro_desague,'$maneja_inventario',$stock_critico,$tiempo_reposicion,'$precio_libre', '$es_despachable', '$sistema_valido',$potencia_kw ";
		
		if ($db->EXECUTE_SP($sp, $param)) {
			for ($i = 0; $i < $this->dws['dw_producto_proveedor']->row_count(); $i++){
				$this->dws['dw_producto_proveedor']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++){
				$this->dws['dw_producto_compuesto']->set_item($i, 'COD_PRODUCTO_PRINCIPAL', $cod_producto);
			}
			for ($i = 0; $i < $this->dws['dw_atributo_producto']->row_count(); $i++){
				$this->dws['dw_atributo_producto']->set_item($i, 'COD_PRODUCTO', $cod_producto);
			}
			
			// TAB PROVEEDORES //
			/*
			if ($es_compuesto == 'S'){
				// SI ES COMPUESTO SE ELIMINAN LOS PROVEEDORES Y SE ESCONDE EL TAB PROVEEDORES
				for ($i = 0; $i < $this->dws['dw_producto_proveedor']->row_count(); $i++) {			
					$cod_producto_proveedor = $this->dws['dw_producto_proveedor']->get_item($i, 'COD_PRODUCTO_PROVEEDOR');					 
					$sp = 'spu_producto_proveedor';
					$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_proveedor");
				}				
				//$this->dws['dw_producto']->set_item(0, "TAB_".self::K_IT_MENU_TAB_PROVEE_VISIBLE,'none');
				
				if (!$this->dws['dw_producto_compuesto']->update($db))					
					return false;					
			}
			else{				
				// si no es compuesto se eliminan los productos compuestos de este producto			
				for ($i = 0; $i < $this->dws['dw_producto_compuesto']->row_count(); $i++) {			
					$cod_producto_compuesto = $this->dws['dw_producto_compuesto']->get_item($i, 'COD_PRODUCTO_COMPUESTO');
					$sp = 'spu_producto_compuesto';
					$db->EXECUTE_SP($sp, "'DELETE', $cod_producto_compuesto");
				}
				if (!$this->dws['dw_producto_proveedor']->update($db)){					
					return false;
				}	
			}
			*/
			if (!$this->dws['dw_producto_proveedor']->update($db))					
					return false;
					
			if (!$this->dws['dw_producto_compuesto']->update($db))				
				return false;
						
			if (!$this->dws['dw_atributo_producto']->update($db))
				return false;
				
			$param = "'$operacion',$cod_producto_local,'$cod_producto','$es_compuesto'";	
			if (!$db->EXECUTE_SP('spu_producto_local', $param)){
					return false;
				}	
				
			if (!$this->subir_imagen($db, $cod_producto))
				return false;
			
			return true;
		}
		return false;
	}
	function update_costo_producto($cod_producto,$precio,$origen)
	{
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select SISTEMA, URL_WS, USER_WS,PASSWROD_WS  from PARAMETRO_WS
					where SISTEMA = '".$origen."' ";
			$result = $db->build_results($sql);
			
			$user_ws		= $result[0]['USER_WS'];
			$passwrod_ws	= $result[0]['PASSWROD_WS'];
			$url_ws			= $result[0]['URL_WS'];
   		 
   		$biggi = new client_biggi("$user_ws", "$passwrod_ws", "$url_ws");
		
		$res = $biggi->cli_update_costo_producto(K_CLIENTE,$cod_producto,$precio);
	}
	function subir_imagen($db, $cod_producto){
		$foto_chica = $_FILES['FOTO_CHICA']['tmp_name'];
		$foto_grande = $_FILES['FOTO_GRANDE']['tmp_name'];

		If ($foto_chica <> ''){
			$datastring_chica = file_get_contents($foto_chica);
			$data_chica = unpack("H*hex", $datastring_chica);
			$hexa_chica = '0x' . $data_chica['hex'];
		}
		else {
			$hexa_chica = '0x';
		}

		If ($foto_grande <> ''){
			$datastring_grande = file_get_contents($foto_grande);
			$data_grande = unpack("H*hex", $datastring_grande);
			$hexa_grande = '0x' . $data_grande['hex'];
		}
		else {
			$hexa_grande = '0x';
		}

		$sp = 'sp_subir_imagen';
		$param = "$hexa_chica, $hexa_grande, '$cod_producto'";

		if ($db->EXECUTE_SP($sp, $param)){
			return true;
		}
		return false;
	}
	
	function print_record() {
		$cod_producto = $this->get_key();
		$sql= "SELECT	COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_PUBLICO
				FROM	PRODUCTO
				WHERE	COD_PRODUCTO = $cod_producto";

		// reporte
		$labels = array();
		$labels['strCOD_PRODUCTO'] = $cod_producto;					
		$file_name = $this->find_file('producto', 'producto.xml');					
		$rpt = new print_producto($sql, $file_name, $labels, "Producto ".$cod_producto.".pdf", 0);
		$this->_load_record();
		return true;
	}
	
}

class print_producto extends reporte {	
	function print_producto($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	
	function modifica_pdf(&$pdf) {
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$result = $db->build_results($this->sql);
			$count = count($result);
			$cod_producto	=	'  '.$result[0]['COD_PRODUCTO'];
			$nom_producto	=	$result[0]['NOM_PRODUCTO'];
			$precio_venta_publico	=	'$ '.number_format($result[0]['PRECIO_VENTA_PUBLICO'], 0, ',', '.');
			$linea	= '_____________';
			$porc	= '+ IVA';
			$a = session::get('K_ROOT_DIR');

			$pdf->Image($a."images_appl/logo_reporte_excel.PNG", 120, 15, 390, 70);
			
			//nuevo
			$pdf->SetFont('Arial','',20);
			$pdf->Text(220, 120,'Modelo: ','L');
			$pdf->SetFont('Arial','',25);
			$pdf->Text(290, 120,$cod_producto,'L');
			
			$pdf->SetXY(150,140);
			$pdf->SetFont('Arial','B',20);
			$pdf->MultiCell(300,20,$nom_producto,0, 'C');
			
			$pdf->SetFont('Arial','B',40);
			$pdf->Text(160, 232,$linea,'L');
			$pdf->SetFont('Arial','B',25);
			$pdf->Text(230, 270,$precio_venta_publico.' .-','L');
			$pdf->SetFont('Arial','',10);
			$pdf->Text(370, 270,$porc,'L');	
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_producto_bodega.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class dw_producto_compuesto extends dw_producto_compuesto_base {
		function dw_producto_compuesto() {
			parent::dw_producto_compuesto_base(); 
		}
	}

	class wi_producto_bodega extends wi_producto_bodega_base{
		function wi_producto_bodega($cod_item_menu){
			parent::wi_producto_bodega_base($cod_item_menu); 
		}
	}	
}
?>