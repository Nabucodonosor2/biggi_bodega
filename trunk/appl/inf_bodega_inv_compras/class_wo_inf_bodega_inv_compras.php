<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_inf_bodega_inv_compras extends w_informe_pantalla {
	function wo_inf_bodega_inv_compras() {
	
		$sql = "select P.COD_PRODUCTO
					   ,P.NOM_PRODUCTO
					   ,dbo.f_bodega_stock(P.COD_PRODUCTO, 2, getdate()) CANTIDAD
					   ,(select sum(s.cantidad) 
			    		   from solicitud_compra s 
			  	          where s.cod_producto = p.cod_producto
				            and dbo.f_sol_por_llegar(cod_solicitud_compra) > 0
				            and S.COD_ESTADO_SOLICITUD_COMPRA = 2)	CANT_SOLICITADA -- aprobado		
			           ,(select sum(dbo.f_sol_recibido(s.cod_solicitud_compra)) 
			  			   from solicitud_compra s 
			  			  where s.cod_producto = p.cod_producto
							and dbo.f_sol_por_llegar(cod_solicitud_compra) > 0
							and S.COD_ESTADO_SOLICITUD_COMPRA = 2)	CANT_RECIBIDA -- aprobado		
						,dbo.f_bodega_por_recibir(P.COD_PRODUCTO) POR_RECIBIR
				from PRODUCTO P left outer join MARCA M on M.COD_MARCA = P.COD_MARCA
				where substring(sistema_valido, 2, 1) = 'S'
	  			and P.maneja_inventario = 'S'
	  			ORDER BY P.COD_PRODUCTO";
		
		parent::w_informe_pantalla('inf_bodega_inv_compras', $sql, $_REQUEST['cod_item_menu']);
		$this->add_header(new header_text('COD_PRODUCTO', 'P.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', "P.NOM_PRODUCTO", 'Equipo'));
		$this->add_header(new header_num('CANTIDAD', 'CANTIDAD', 'Stock'));
		$this->add_header(new header_num('CANT_SOLICITADA', 'CANT_SOLICITADA', 'Solicitada'));
		$this->add_header(new header_num('CANT_RECIBIDA', 'CANT_RECIBIDA', 'Recibida'));
		$this->add_header(new header_num('POR_RECIBIR', 'POR_RECIBIR', 'Por Recibir'));
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result_count = $db->build_results($sql);
		$cantidad = count($result_count);
		$this->row_per_page = $cantidad;
	}
	function print_informe(){
			// reporte
				$sql = "exec spi_bodega_inv_compras";
				// selecciona xml
				$xml = session::get('K_ROOT_DIR').'appl/inf_bodega_inv_compras/inf_bodega_inv_compras.xml';
				$labels = array();
				$labels['str_filtro'] = "Fecha impresión = ".$this->current_date()."; ";
				//$labels['str_fecha'] = $this->current_date();
				$rpt = new reporte($sql, $xml, $labels, "Inventario Compras.pdf", true);
				$this->_redraw();
	}
}
?>