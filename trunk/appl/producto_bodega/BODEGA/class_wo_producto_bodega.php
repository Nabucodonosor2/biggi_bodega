<?php
class wo_producto_bodega extends wo_producto_bodega_base{
	const K_BODEGA_TERMINADO = 2;
	var $checkbox_bajo_margen = true;
	var $checkbox_sobre_margen = true;
	
	function wo_producto_bodega(){
		// Es igual al BASE, solo cambia elk sql donde se agrega stock
		$sql = $this->make_sql();
		parent::w_output_biggi('producto_bodega', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_modelo('COD_PRODUCTO', 'COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('AUXCOLOR', 'AUXCOLOR', 'AUXCOLOR'));
		
		$this->add_header(new header_text('NOM_PRODUCTO', 'NOM_PRODUCTO', 'Descripción'));
		$this->add_header(new header_num('PRECIO_VENTA_INTERNO', 'PRECIO_VENTA_INTERNO', 'Precio Interno'));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio Público'));
		$sql_tipo_producto = "select COD_TIPO_PRODUCTO ,NOM_TIPO_PRODUCTO from TIPO_PRODUCTO order by	ORDEN";
		$this->add_header($header = new header_drop_down('NOM_TIPO_PRODUCTO', 'TP.COD_TIPO_PRODUCTO', 'Tipo Producto', $sql_tipo_producto));
		$this->add_header($control=new header_num('STOCK', "dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate())", 'Stock'));
		$this->add_header($control=new header_num('PRECIO_ARMADO', "dbo.f_prod_ultima_compra(P.COD_PRODUCTO)", 'PRECIO ARMADO'));
		
		$control->field_bd_order = 'STOCK';
		
		// formatos de columnas
		$this->dw->add_control(new edit_num('PRECIO_VENTA_INTERNO'));
		$this->dw->add_control(new edit_num('PRECIO_VENTA_PUBLICO'));
   		$this->dw->add_control(new edit_num('PRECIO_ARMADO'));

		$sql = "select  'S' CHECK_B_MARGEN,
					    'S' CHECK_S_MARGEN,
					    'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);

		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_B_MARGEN','S','N'));
		$control->set_onClick("filtro(); document.getElementById('loader').style.display='';");
		
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_S_MARGEN','S','N'));
		$control->set_onClick("filtro(); document.getElementById('loader').style.display='';");

		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();

		// Filtro inicial
		$header->valor_filtro = '1';
		$this->make_filtros();
	}

	function make_sql(){
		$sql = "select	COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_INTERNO
						,dbo.f_prod_ultima_compra(P.COD_PRODUCTO) PRECIO_ARMADO
            			,PRECIO_VENTA_PUBLICO
						,NOM_TIPO_PRODUCTO
						,dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) STOCK
						,CASE
                			when P.PRECIO_VENTA_INTERNO <= ((dbo.f_prod_ultima_compra(P.COD_PRODUCTO) * p.FACTOR_VENTA_INTERNO) - CONVERT(NUMERIC, dbo.f_get_parametro(84))) THEN 'RED'
             				ELSE  'NAVY'
             			END AUXCOLOR
				from 	PRODUCTO P
						,TIPO_PRODUCTO TP
				where	P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
				AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'";

		if($this->checkbox_bajo_margen == true && $this->checkbox_sobre_margen == false)
			$sql .= " AND P.PRECIO_VENTA_INTERNO <= ((dbo.f_prod_ultima_compra(P.COD_PRODUCTO) * p.FACTOR_VENTA_INTERNO) - CONVERT(NUMERIC, dbo.f_get_parametro(84)))";

		if($this->checkbox_bajo_margen == false && $this->checkbox_sobre_margen == true)
			$sql .= " AND P.PRECIO_VENTA_INTERNO > ((dbo.f_prod_ultima_compra(P.COD_PRODUCTO) * p.FACTOR_VENTA_INTERNO) - CONVERT(NUMERIC, dbo.f_get_parametro(84)))";
		
		if($this->checkbox_bajo_margen == false && $this->checkbox_sobre_margen == false)
			$sql .= " AND 0 = 1";

		$sql .= " order by COD_PRODUCTO";

		return $sql;
	}

	function redraw(&$temp) {
		parent::redraw($temp);
		$this->dw_check_box->habilitar($temp, true);
	}
	
	function make_menu(&$temp) {
	    $menu = session::get('menu_appl');
	    $menu_original = $menu->ancho_completa_menu;
	    $menu->ancho_completa_menu = 304;
	    $menu->draw($temp);
	    $menu->ancho_completa_menu = $menu_original;    // volver a setear el tamaño original
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
                }else
                    $sql = substr($sql, 0, $pos).' WHERE '.substr($filtro_total, 0, strlen($filtro_total) - 4).' '.substr($sql, $pos);	// borra 'and ' y agrega el resto
            }else
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
        $sql = str_replace('/*FILTROS*/',$filtro_total,$sql);
        $this->dw->set_sql($sql);
	}

	function procesa_event(){
		if($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_bajo_margen		= isset($_POST['CHECK_B_MARGEN_0']);
			$this->checkbox_sobre_margen	= isset($_POST['CHECK_S_MARGEN_0']);

			if($this->checkbox_bajo_margen)
			    $this->dw_check_box->set_item(0, 'CHECK_B_MARGEN', 'S');
		    else
		        $this->dw_check_box->set_item(0, 'CHECK_B_MARGEN', 'N');
		    
	        if($this->checkbox_sobre_margen)
	            $this->dw_check_box->set_item(0, 'CHECK_S_MARGEN', 'S');
            else
                $this->dw_check_box->set_item(0, 'CHECK_S_MARGEN', 'N');

			$sql = $this->make_sql();

			$this->dw->set_sql($sql);
			$this->sql_original = $sql;
			$this->save_SESSION();
			$this->make_filtros();
			$this->retrieve();
		}else{
			parent::procesa_event();
		}
	}
}
?>