<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_inf_por_despachar extends w_informe_pantalla {
   function wo_inf_por_despachar() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$sql = "select NV.COD_NOTA_VENTA
						,convert(varchar(20), NV.FECHA_NOTA_VENTA, 3) FECHA_NOTA_VENTA
						,NV.FECHA_NOTA_VENTA DATE_NOTA_VENTA
						,E.NOM_EMPRESA
						,U.INI_USUARIO
						,INV.ITEM
						,INV.COD_PRODUCTO
						,INV.NOM_PRODUCTO
						,INV.CANTIDAD
						,dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) CANTIDAD_POR_DESPACHAR
						,1 CANTIDAD_LINEA
				from	NOTA_VENTA NV, ITEM_NOTA_VENTA INV, PRODUCTO P, EMPRESA E, USUARIO U
				where	NV.COD_NOTA_VENTA = INV.COD_NOTA_VENTA AND
						P.COD_PRODUCTO = INV.COD_PRODUCTO AND
						E.COD_EMPRESA = NV.COD_EMPRESA  AND
						NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO AND
						dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default) > 0 and
						NV.cod_estado_nota_venta <> 3 and	-- Anulada
				        dbo.f_get_tiene_acceso(".$cod_usuario.", 'NOTA_VENTA', NV.COD_USUARIO_VENDEDOR1,NV.COD_USUARIO_VENDEDOR2) = 1
				order by NV.COD_NOTA_VENTA, INV.ITEM";
		parent::w_informe_pantalla('inf_por_despachar', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_num('COD_NOTA_VENTA', 'NV.COD_NOTA_VENTA', 'NV'));
		$this->add_header($control = new header_date('FECHA_NOTA_VENTA', 'NV.FECHA_NOTA_VENTA', 'Fecha'));
		$control->field_bd_order = 'DATE_NOTA_VENTA';
		$this->add_header(new header_text('NOM_EMPRESA', "E.NOM_EMPRESA", 'Cliente'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from NOTA_VENTA NV, USUARIO U where NV.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'NV.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_text('ITEM', "INV.ITEM", 'Item'));
		$this->add_header(new header_modelo('COD_PRODUCTO', "INV.COD_PRODUCTO", 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', "INV.NOM_PRODUCTO", 'Producto'));
		$this->add_header(new header_num('CANTIDAD', 'INV.CANTIDAD', 'Cant', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_POR_DESPACHAR', 'dbo.f_nv_cant_por_despachar(INV.COD_ITEM_NOTA_VENTA, default)', 'x Desp', 0, true, 'SUM'));
		$this->add_header(new header_num('CANTIDAD_LINEA', '1', '', 0, true, 'SUM'));
   }
	function print_informe() {
		$cod_usuario = $_POST['wo_hidden'];

		// reporte
		$sql = "exec spr_por_despachar $cod_usuario";
		// selecciona xml
		if ($cod_usuario==0)
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar_global.xml';
		else
			$xml = session::get('K_ROOT_DIR').'appl/inf_por_despachar/inf_por_despachar.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$rpt = new reporte($sql, $xml, $labels, "Por despachar", true);
		
		$this->_redraw();
	}
}
?>