<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");
include(dirname(__FILE__)."/../../appl.ini");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_producto_base extends w_output_biggi{
	function wo_producto_base(){
		$sql = "select	COD_PRODUCTO
						,NOM_PRODUCTO
						,PRECIO_VENTA_PUBLICO
						,NOM_TIPO_PRODUCTO
						,TP.COD_TIPO_PRODUCTO
			from 		PRODUCTO P
						,TIPO_PRODUCTO TP
			where		P.COD_TIPO_PRODUCTO = TP.COD_TIPO_PRODUCTO
						AND dbo.f_prod_valido (COD_PRODUCTO) = 'S'
			order by 	COD_PRODUCTO";
			
		parent::w_output_biggi('producto', $sql, $_REQUEST['cod_item_menu']);

		// headers
		$this->add_header(new header_modelo('COD_PRODUCTO', 'COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', 'NOM_PRODUCTO', 'Descripción'));
		$this->add_header(new header_num('PRECIO_VENTA_PUBLICO', 'PRECIO_VENTA_PUBLICO', 'Precio'));
		$sql_tipo_producto = "select COD_TIPO_PRODUCTO ,NOM_TIPO_PRODUCTO from TIPO_PRODUCTO order by	ORDEN";
		$this->add_header($header = new header_drop_down('NOM_TIPO_PRODUCTO', 'TP.COD_TIPO_PRODUCTO', 'Tipo Producto', $sql_tipo_producto));

		// formatos de columnas
		$this->dw->add_control(new edit_num('PRECIO_VENTA_PUBLICO'));

		// Filtro inicial
		$header->valor_filtro = '1';
		$this->make_filtros();
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_producto.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_producto extends wo_producto_base {
		function wo_producto() {
			parent::wo_producto_base(); 
		}
	}
}
?>