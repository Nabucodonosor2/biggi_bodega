<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_inf_ventas_por_equipo extends w_informe_pantalla {
	var $dw_stock;
	
   function wo_inf_ventas_por_equipo() {
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_equipo.ANO");
		session::un_set("inf_ventas_por_equipo.ANO");
		$cod_usuario = session::get("COD_USUARIO");;
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spi_ventas_por_equipo", "$cod_usuario, $ano"); 
		$sql = "select	I.MES
						,I.ANO
					    ,I.COD_PRODUCTO           
					    ,I.TIPO_DOC
					    ,I.COD_DOC                                        
					    ,I.NRO_DOC                      
						,convert(varchar, I.FECHA_DOC, 103) FECHA_DOC
						,I.FECHA_DOC DATE_DOC
					    ,I.NOM_EMPRESA                  
					    ,SUM(I.CANTIDAD) CANTIDAD
					    ,I.PRECIO                       
					    ,SUM(I.TOTAL) TOTAL                           
					    ,case when sum(I.CANTIDAD) >=0 then sum(I.CANTIDAD) else 0 end CANT_FA                      
					    ,case when sum(I.CANTIDAD) < 0 then sum(I.CANTIDAD) else 0 end CANT_NC                      
					    ,case when sum(I.CANTIDAD) >=0 then sum(I.TOTAL) else 0 end TOT_FA                      
					    ,case when sum(I.CANTIDAD) < 0 then sum(I.TOTAL) else 0 end TOT_NC     
				FROM INF_VENTAS_POR_EQUIPO I
				where I.COD_USUARIO = $cod_usuario
				  and I.ANO = $ano
				group by I.MES, I.ANO, I.COD_PRODUCTO, I.TIPO_DOC, I.COD_DOC, I.NRO_DOC, I.FECHA_DOC, I.NOM_EMPRESA, I.PRECIO
				order by DATE_DOC, I.NRO_DOC";
			  
		parent::w_informe_pantalla('inf_ventas_por_equipo', $sql, $_REQUEST['cod_item_menu']);
		$this->b_print_visible = false;
		// controls
		$this->dw->add_control(new static_num('PRECIO'));
		$this->dw->add_control(new static_num('TOTAL'));
		
		// headers
		$this->add_header($h_mes = new header_mes('MES', 'I.MES', 'Mes'));
		$this->add_header($h_cod_producto = new header_modelo('COD_PRODUCTO', 'I.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('TIPO_DOC', 'I.TIPO_DOC', 'Tipo Doc'));
		$this->add_header(new header_num('NRO_DOC', 'I.NRO_DOC', 'Nro Doc'));
		$this->add_header($control = new header_date('FECHA_DOC', 'I.FECHA_DOC', 'Fecha'));
		$control->field_bd_order = 'I.DATE_DOC';
		$this->add_header(new header_text('NOM_EMPRESA', 'I.NOM_EMPRESA', 'Cliente'));
		$this->add_header(new header_num('CANTIDAD', 'I.CANTIDAD', 'CT'));
		$this->add_header(new header_num('PRECIO', 'I.PRECIO', 'Precio'));
		$this->add_header(new header_num('TOTAL', 'I.TOTAL', 'Total'));
		/*
		$this->add_header(new header_num('CANT_FA', 'case when I.CANTIDAD >=0 then I.CANTIDAD else 0 end', 'CANT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_NC', 'case when I.CANTIDAD < 0 then I.CANTIDAD else 0 end', 'CANT_NC', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_FA', 'case when I.CANTIDAD >=0 then I.TOTAL else 0 end', 'TOT_FA', 0, true, 'SUM'));
		$this->add_header(new header_num('TOT_NC', 'case when I.CANTIDAD < 0 then I.TOTAL else 0 end', 'TOT_NC', 0, true, 'SUM'));
		*/
   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_equipo.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_equipo.MES_HASTA");
		session::un_set("inf_ventas_por_equipo.MES_DESDE");
		session::un_set("inf_ventas_por_equipo.MES_HASTA");
		$h_mes->valor_filtro = $mes_desde;
		$h_mes->valor_filtro2 = $mes_hasta;
		
		$cod_producto = session::get("inf_ventas_por_equipo.COD_PRODUCTO");
		$find_exacto = session::get("inf_ventas_por_equipo.FIND_EXACTO");
		session::un_set("inf_ventas_por_equipo.COD_PRODUCTO");
		session::un_set("inf_ventas_por_equipo.FIND_EXACTO");
		if ($cod_producto != '') {
			$h_cod_producto->valor_filtro = $cod_producto."|".$find_exacto;
		}
		
		$this->row_per_page = 500;
		$this->make_filtros();	// filtro incial
		
		// dw stock
		if (K_CLIENTE=='TODOINOX' || K_CLIENTE=='BODEGA'){
			$sql = $this->dw->get_sql();
			
			$result = $db->build_results($sql);
			$cod_producto_new = "";
			$cod_producto_old = "";
			$message1 = "stock";
			$message2 = "";
			$message3 = "";
			
			for($i=0 ; $i < count($result) ; $i++){
				$cod_producto_new = $result[$i]['COD_PRODUCTO'];

				if($i <> 0){
					if($cod_producto_new <> $cod_producto_old){
						$message1 = '';
						$message2 = 'Hay 2 o mas productos en la lista.';
						$message3 = 'No se puede mostrar el stock';
						$stock_tdnx = 'NULL';
						$stock_bod = 'NULL';
						break;
					}	
				}
				
				$cod_producto_old = $cod_producto_new;
			}
			
			if($message1 <> ''){
				$stock_tdnx = "dbo.f_bodega_stock('$cod_producto_new', 1, getdate())";
				$stock_bod = "dbo.f_bodega_stock('$cod_producto_new', 2, getdate())";
			}
			
			if(count($result) == 0){
				$sql_val = "SELECT COUNT(*) COUNT
							FROM PRODUCTO
							WHERE COD_PRODUCTO = '$cod_producto'";
				$result_val = $db->build_results($sql_val);	
				
				if($result_val[0]['COUNT'] > 0){
					if(K_CLIENTE=='TODOINOX')
						$stock_tdnx = "dbo.f_bodega_stock('$cod_producto', 1, getdate())";
					else//BODEGA
						$stock_bod = "dbo.f_bodega_stock('$cod_producto', 2, getdate())";
				}else{
					$message1 = '';
					$message2 = 'No se ingresó un código producto exacto.';
					$message3 = 'No se puede mostrar el stock';
					$stock_tdnx = 'null';
					$stock_bod = 'null';
				}
			}
			
			if(K_CLIENTE=='TODOINOX'){
				$sql = "select '$message1' LABEL_STOCK
								,'$message2' LABEL_ALERT
								,'$message3' LABEL_ALERT_R
								,$stock_tdnx STOCK";
			}else{//BODEGA
				$sql = "select '$message1' LABEL_STOCK
								,'$message2' LABEL_ALERT
								,'$message3' LABEL_ALERT_R
								,$stock_bod STOCK";
			}
		}else                 
			$sql = "select null LABEL_STOCK
							,'' LABEL_ALERT
							,'' LABEL_ALERT_R
							,null STOCK";
		$this->dw_stock = new datawindow($sql);
		$this->dw_stock->add_control(new static_num('STOCK'));
		$this->dw_stock->retrieve();
   }
	function redraw(&$temp) {
		$this->dw_stock->habilitar($temp, false);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = $this->dw->get_sql();	
		$result = $db->build_results($sql);
		
		$sum_cant_fa = 0;
		$sum_cant_nc = 0;
		$sum_tot_fa = 0;
		$sum_tot_nc = 0;
		$sum_cantidad = 0;
		$sum_total = 0;
		
		for($i=0 ; $i < count($result) ; $i++){
			$sum_cant_fa = $sum_cant_fa + $result[$i]['CANT_FA'];
			$sum_cant_nc = $sum_cant_nc + $result[$i]['CANT_NC'];
			$sum_tot_fa = $sum_tot_fa + $result[$i]['TOT_FA'];
			$sum_tot_nc = $sum_tot_nc + $result[$i]['TOT_NC'];
			$sum_cantidad = $sum_cantidad + $result[$i]['CANTIDAD'];
			$sum_total = $sum_total + $result[$i]['TOTAL'];
		}
		
		$temp->setVar("SUM_CANT_FA", number_format($sum_cant_fa, 0, ',', '.'));
		$temp->setVar("SUM_CANT_NC", number_format($sum_cant_nc, 0, ',', '.'));
		$temp->setVar("SUM_CANTIDAD", number_format($sum_cantidad, 0, ',', '.'));
		$temp->setVar("SUM_TOT_FA", number_format($sum_tot_fa, 0, ',', '.'));
		$temp->setVar("SUM_TOT_NC", number_format($sum_tot_nc, 0, ',', '.'));
		$temp->setVar("SUM_TOTAL", number_format($sum_total, 0, ',', '.'));
	}
}
?>