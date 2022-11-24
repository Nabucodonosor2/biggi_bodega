<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");
ini_set('memory_limit', '980M'); //MH 09-06-2020 antes tenia puesto la mitad de M y se cae el informe, se dejo al doble y funciona OK.
ini_set('max_execution_time', 900); // MH 09-06-2020 No se sabe que tanto influye esta instruccion en el informe.
class wo_inf_ventas_por_equipo extends w_informe_pantalla {
	var $dw_stock;
	var $dw_stock_fa;
	var $dw_stock_nc;
	var $sql_original_stock;
	var $sql_original_stock_fa;
	var $sql_original_stock_nc;
	
   function wo_inf_ventas_por_equipo() {
       $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
       
   		// Construye el resultado del informe en un tabla AUXILIA de INFORME
		$ano = session::get("inf_ventas_por_equipo.ANO");
		session::un_set("inf_ventas_por_equipo.ANO");
		$cod_usuario = session::get("COD_USUARIO");;
		
		
		$ULTIMO_5 = session::get("inf_ventas_por_equipo.5_ULTIMO");
		
		$sql_stock = "SELECT
                        SUM(I.TOTAL) TOTAL
                        ,SUM(I.CANTIDAD) CANTIDAD
				FROM INF_VENTAS_POR_EQUIPO I
				where";
		
		$sql_stock_fa = "SELECT
                        sum(I.CANTIDAD) CANT_FA
					    ,sum(I.TOTAL) TOT_FA
				FROM INF_VENTAS_POR_EQUIPO I
				where";
		
		$sql_stock_nc = "SELECT
                        sum(I.CANTIDAD) CANT_NC
					    ,sum(I.TOTAL) TOT_NC
				FROM INF_VENTAS_POR_EQUIPO I
				where";
		
		if($ULTIMO_5 == 'S'){
		    $db->EXECUTE_SP("spi_ventas_por_equipo", "$cod_usuario, $ano,'S'");
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
				FROM INF_VENTAS_POR_EQUIPO I
				where I.COD_USUARIO = $cod_usuario
				and ORIGEN='5ANO'
				group by I.MES, I.ANO, I.COD_PRODUCTO, I.TIPO_DOC, I.COD_DOC, I.NRO_DOC, I.FECHA_DOC, I.NOM_EMPRESA, I.PRECIO
				order by DATE_DOC desc, I.NRO_DOC desc";
		    
		    $sql_stock_completo = $sql_stock." I.COD_USUARIO = $cod_usuario and ORIGEN='5ANO'";
		    $sql_stock_completo_fa = $sql_stock_fa." I.COD_USUARIO = $cod_usuario and ORIGEN='5ANO' AND TIPO_DOC = 'FA'";
		    $sql_stock_completo_nc = $sql_stock_nc." I.COD_USUARIO = $cod_usuario and ORIGEN='5ANO' AND TIPO_DOC = 'NC'";
		}else{
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
				FROM INF_VENTAS_POR_EQUIPO I
				where I.COD_USUARIO = $cod_usuario
				  and I.ANO = $ano
				  and ORIGEN='1ANO'
				group by I.MES, I.ANO, I.COD_PRODUCTO, I.TIPO_DOC, I.COD_DOC, I.NRO_DOC, I.FECHA_DOC, I.NOM_EMPRESA, I.PRECIO
				order by DATE_DOC, I.NRO_DOC";
		    
		    $sql_stock_completo = $sql_stock." I.COD_USUARIO = $cod_usuario and ORIGEN='1ANO'";
		    $sql_stock_completo_fa = $sql_stock_fa." I.COD_USUARIO = $cod_usuario and ORIGEN='1ANO' AND TIPO_DOC = 'FA'";
		    $sql_stock_completo_nc = $sql_stock_nc." I.COD_USUARIO = $cod_usuario and ORIGEN='1ANO' AND TIPO_DOC = 'NC'";
		}
		
		$this->sql_original_stock = $sql_stock_completo;
		$this->sql_original_stock_fa = $sql_stock_completo_fa;
		$this->sql_original_stock_nc = $sql_stock_completo_nc;
		
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
		
   		// Filtro inicial
		$mes_desde = session::get("inf_ventas_por_equipo.MES_DESDE");
		$mes_hasta = session::get("inf_ventas_por_equipo.MES_HASTA");
		session::un_set("inf_ventas_por_equipo.MES_DESDE");
		session::un_set("inf_ventas_por_equipo.MES_HASTA");
		
		
		if($ULTIMO_5 == 'N'){
		    $h_mes->valor_filtro = $mes_desde;
		    $h_mes->valor_filtro2 = $mes_hasta;
		}

		
		$cod_producto = session::get("inf_ventas_por_equipo.COD_PRODUCTO");
		$find_exacto = session::get("inf_ventas_por_equipo.FIND_EXACTO");
		session::un_set("inf_ventas_por_equipo.COD_PRODUCTO");
		session::un_set("inf_ventas_por_equipo.FIND_EXACTO");
		if ($cod_producto != '') {
			$h_cod_producto->valor_filtro = $cod_producto."|".$find_exacto;
		}
		
		$this->row_per_page = 500;
		//if($ULTIMO_5 == 'N')
		  

		$sql_vacio = "selec '' DW_VACIO";  // simplemente para que se cree un DW, lo importante es despues en el redraw
		$this->dw_stock = new datawindow($sql_vacio);
		$this->dw_stock_fa = new datawindow($sql_vacio);
		$this->dw_stock_nc = new datawindow($sql_vacio);
		
		$this->make_filtros();	// filtro incial
		$this->dw_stock->retrieve();
		$this->dw_stock_fa->retrieve();
		$this->dw_stock_nc->retrieve();
		
   }
   
   function make_filtros() {
       $this->nom_filtro = '';
       $filtro_total = '';
       $indices = array_keys($this->headers);
       for ($i=0; $i<count($this->headers); $i++) {
           $filtro = $this->headers[$indices[$i]]->make_filtro();
           if ($filtro != '') {
               $filtro_total .= $filtro;
               $this->nom_filtro .= $this->headers[$indices[$i]]->make_nom_filtro()."; ";
           }
       }
       // Elimina ; final
       if ($this->nom_filtro != '')
           $this->nom_filtro = substr($this->nom_filtro, 0, strlen($this->nom_filtro)-2);
           
       $sql = $this->sql_original;
       if ($filtro_total != '') {
           $pos = strrpos(strtoupper($sql), 'WHERE');
           if ($pos === false) {
               $pos = strrpos(strtoupper($sql), 'GROUP');
               if ($pos === false) {
                   $pos = strrpos(strtoupper($sql), 'ORDER');
                   if ($pos===false)
                       $sql = $sql.' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4);	// borra 'and '
                       else
                           $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
               }
               else
                   $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
           }
           else
               $sql = substr($sql, 0, $pos).' WHERE '.$filtro_total.' '.substr($sql, $pos + 5);
       }
       
       // Aplica un order by si ha sido seleccionado por el usuario
       if ($this->field_sort != '') {
           $pos_order = strrpos(strtoupper($sql), 'ORDER');	// posible error si es que existe un nombre de campo que contenga la palabra ORDER !!
           if ($pos_order===false)
               $pos_order = strlen($sql);
               $sql = substr($sql, 0, $pos_order - 1);
               
               $sql .= ' ORDER BY ';
               $lista = explode(",", $this->headers[$this->field_sort]->field_bd_order);
               for ($i=0; $i<count($lista); $i++)
                   $sql .= $lista[$i].' '.$this->sort_asc_desc.",";
                   $sql = substr($sql, 0, strlen($sql)-1);
       }
       $this->dw->set_sql($sql);
       $this->make_filtros_stock();
   }
   
   function make_filtros_stock(){
       $this->nom_filtro = '';
       $filtro_total = '';
       $indices = array_keys($this->headers);
       for ($i=0; $i<count($this->headers); $i++) {
           $filtro = $this->headers[$indices[$i]]->make_filtro();
           if ($filtro != '') {
               $filtro_total .= $filtro;
               $this->nom_filtro .= $this->headers[$indices[$i]]->make_nom_filtro()."; ";
           }
       }
       // Elimina ; final
       if ($this->nom_filtro != '')
           $this->nom_filtro = substr($this->nom_filtro, 0, strlen($this->nom_filtro)-2);
           
       $sql = $this->sql_original_stock;
       if ($filtro_total != '') {
           $pos = strrpos(strtoupper($sql), 'WHERE');
           if ($pos === false) {
               $pos = strrpos(strtoupper($sql), 'GROUP');
               if ($pos === false) {
                   $pos = strrpos(strtoupper($sql), 'ORDER');
                   if ($pos===false)
                       $sql = $sql.' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4);	// borra 'and '
                       else
                           $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
               }
               else
                   $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
           }
           else
               $sql = substr($sql, 0, $pos).' WHERE '.$filtro_total.' '.substr($sql, $pos + 5);
       }
       
       $this->dw_stock->set_sql($sql);
       /**FA**/
       $sql = $this->sql_original_stock_fa;
       if ($filtro_total != '') {
           $pos = strrpos(strtoupper($sql), 'WHERE');
           if ($pos === false) {
               $pos = strrpos(strtoupper($sql), 'GROUP');
               if ($pos === false) {
                   $pos = strrpos(strtoupper($sql), 'ORDER');
                   if ($pos===false)
                       $sql = $sql.' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4);	// borra 'and '
                       else
                           $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
               }
               else
                   $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
           }
           else
               $sql = substr($sql, 0, $pos).' WHERE '.$filtro_total.' '.substr($sql, $pos + 5);
       }
       
       $this->dw_stock_fa->set_sql($sql);
       /**NC**/
       $sql = $this->sql_original_stock_nc;
       if ($filtro_total != '') {
           $pos = strrpos(strtoupper($sql), 'WHERE');
           if ($pos === false) {
               $pos = strrpos(strtoupper($sql), 'GROUP');
               if ($pos === false) {
                   $pos = strrpos(strtoupper($sql), 'ORDER');
                   if ($pos===false)
                       $sql = $sql.' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4);	// borra 'and '
                       else
                           $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
               }
               else
                   $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
           }
           else
               $sql = substr($sql, 0, $pos).' WHERE '.$filtro_total.' '.substr($sql, $pos + 5);
       }
       
       $this->dw_stock_nc->set_sql($sql);
   }
   
	function redraw(&$temp) {
		$this->dw_stock->habilitar($temp, false);
		$this->dw_stock_fa->habilitar($temp, false);
		$this->dw_stock_nc->habilitar($temp, false);
		
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = $this->dw_stock->get_sql();	
		$result = $db->build_results($sql);
		
		$sql_fa = $this->dw_stock_fa->get_sql(); 
		$result_fa = $db->build_results($sql_fa);
		
		$sql_nc = $this->dw_stock_nc->get_sql(); 
		$result_nc = $db->build_results($sql_nc);
		
		$sum_cant_fa = $result_fa[0]['CANT_FA'];
		$sum_cant_nc = $result_nc[0]['CANT_NC'] * -1;
		$sum_tot_fa = $result_fa[0]['TOT_FA'];
		$sum_tot_nc = $result_nc[0]['TOT_NC'];
		$sum_cantidad = $result[0]['CANTIDAD'];
		$sum_total = $result[0]['TOTAL'];
		
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
		        $message1 = '';
	            $message2 = 'No se ingresó un código producto exacto.';
	            $message3 = 'No se puede mostrar el stock';
	            $stock_tdnx = 'null';
	            $stock_bod = 'null';
		    }
		    
		    if(K_CLIENTE=='TODOINOX'){
		        
		        $sql = "select $stock_tdnx STOCK";
		        $result = $db->build_results($sql);
		        
		        $temp->setVar("LABEL_STOCK",$message1 );
		        $temp->setVar("LABEL_ALERT",$message2 );
		        $temp->setVar("LABEL_ALERT_R",$message3 );
		        
		        if($stock_tdnx != 'null')
		          $temp->setVar("STOCK", number_format($result[0]['STOCK'], 0, ',', '.'));
		        
		    }else{//BODEGA
		        $sql = "select $stock_bod STOCK";
		        $result = $db->build_results($sql);
		        
		        $temp->setVar("LABEL_STOCK",$message1 );
		        $temp->setVar("LABEL_ALERT",$message2 );
		        $temp->setVar("LABEL_ALERT_R",$message3 );
		        
		        if($stock_bod != 'null')
		        $temp->setVar("STOCK", number_format($result[0]['STOCK'], 0, ',', '.'));
		    }
		}else{
		    $temp->setVar("LABEL_STOCK",'' );
		    $temp->setVar("LABEL_ALERT",'' );
		    $temp->setVar("LABEL_ALERT_R",'' );
		    $temp->setVar("STOCK", '');
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