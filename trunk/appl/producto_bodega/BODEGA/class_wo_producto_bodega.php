<?php
class wo_producto_bodega extends wo_producto_bodega_base{
	const K_BODEGA_TERMINADO = 2;
	
	function wo_producto_bodega(){
		// Es igual al BASE, solo cambia elk sql donde se agrega stock
		$sql = "select	COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_INTERNO
						,dbo.f_prod_ultima_compra(P.COD_PRODUCTO) PRECIO_ARMADO
            ,PRECIO_VENTA_PUBLICO
						,NOM_TIPO_PRODUCTO
						,dbo.f_bodega_stock(P.COD_PRODUCTO, ".self::K_BODEGA_TERMINADO.", getdate()) STOCK
						,CASE
                when P.PRECIO_VENTA_INTERNO <= (dbo.f_prod_ultima_compra(P.COD_PRODUCTO) * p.FACTOR_VENTA_INTERNO) THEN 'RED'
             ELSE  'NAVY'
             END AUXCOLOR
			from 		PRODUCTO P
						,TIPO_PRODUCTO TP
			where		P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
						AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'
			order by 	COD_PRODUCTO";
			
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
		//$this->add_header(new header_num('PRECIO_ARMADO', 'PRECIO_ARMADO', 'PRECIO ARMADO'));
		//dbo.f_prod_ultima_compra(P.COD_PRODUCTO) PRECIO_ARMADO
		
		$control->field_bd_order = 'STOCK';
		
		// formatos de columnas
		$this->dw->add_control(new edit_num('PRECIO_VENTA_INTERNO'));
		$this->dw->add_control(new edit_num('PRECIO_VENTA_PUBLICO'));
   $this->dw->add_control(new edit_num('PRECIO_ARMADO'));
		// Filtro inicial
		$header->valor_filtro = '1';
		$this->make_filtros();
	}
}
?>