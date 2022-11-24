<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_facturas_por_cobrar_base extends w_informe_pantalla {
	
   const K_AUTORIZA_EXPORTA_EXCEL	 = '995005';
   function wo_inf_facturas_por_cobrar_base() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		
		
		
		
		
		
		$sql = "select	F.COD_FACTURA
						,F.NRO_FACTURA
						,F.FECHA_FACTURA
						,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA_STR
						,F.FECHA_FACTURA DATE_FACTURA
						,E.RUT
						,E.DIG_VERIF
						,E.NOM_EMPRESA
						,U1.INI_USUARIO INI_USUARIO_VENDEDOR_A
						,U2.INI_USUARIO INI_USUARIO_VENDEDOR_B
						,F.TOTAL_CON_IVA
						,dbo.f_fa_saldo(F.COD_FACTURA) SALDO
						,dbo.f_fa_total_ingreso_pago(F.COD_FACTURA) PAGO
						,1 CANTIDAD_FA 
						,F.COD_USUARIO_VENDEDOR1
				FROM FACTURA F left outer join USUARIO U1 on U1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1 
					           left outer join USUARIO U2 on U2.COD_USUARIO = F.COD_USUARIO_VENDEDOR2
					,EMPRESA E
				WHERE dbo.f_fa_saldo(F.COD_FACTURA) > 0
				  and E.COD_EMPRESA = F.COD_EMPRESA
				  and dbo.f_get_tiene_acceso(".$cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1 
				ORDER BY F.FECHA_FACTURA";
		parent::w_informe_pantalla('inf_facturas_por_cobrar', $sql, $_REQUEST['cod_item_menu']);
		
		$this->dw->add_control(new edit_text_hidden('COD_NOTA_VENTA_H'));
		
		// headers
		$this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Número'));
		$this->add_header($control = new header_date('FECHA_FACTURA_STR', 'F.FECHA_FACTURA', 'Fecha'));//*****
		$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_text('NOM_EMPRESA', "E.NOM_EMPRESA", 'Cliente'));
		$sql = "select	distinct F.COD_USUARIO_VENDEDOR1 COD_USUARIO ,U.NOM_USUARIO 
				FROM FACTURA F left outer join USUARIO U on U.COD_USUARIO = F.COD_USUARIO_VENDEDOR1 
				WHERE dbo.f_fa_saldo(F.COD_FACTURA) > 0
				order by U.NOM_USUARIO";

		$this->add_header(new header_drop_down('INI_USUARIO_VENDEDOR_A', 'F.COD_USUARIO_VENDEDOR1', 'V1', $sql));
		$this->add_header(new header_rut('RUT', 'F', 'Rut'));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'F.TOTAL_CON_IVA', 'Total', 0, true, 'SUM'));
		$this->add_header($control = new header_num('SALDO', 'dbo.f_fa_saldo(F.COD_FACTURA)', 'Saldo', 0, true, 'SUM'));
		$control->field_bd_order = 'SALDO';
		$this->add_header($control = new header_num('PAGO', 'dbo.f_fa_total_ingreso_pago(F.COD_FACTURA)', 'Pagos', 0, true, 'SUM'));
		$control->field_bd_order = 'PAGO';
		$this->add_header(new header_num('CANTIDAD_FA', '1', '', 0, true, 'SUM'));
		

		// controls
		$this->dw->add_control(new static_num('RUT'));
		$this->dw->add_control(new static_num('TOTAL_CON_IVA'));
		$this->dw->add_control(new static_num('SALDO'));
		$this->dw->add_control(new static_num('PAGO'));
		
   	}
   	function redraw($temp){
   		parent::redraw($temp);
   		
   		$cod_usuario =  session::get("COD_USUARIO");
   		
   		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT EXPORTAR
				FROM AUTORIZA_MENU
				WHERE COD_ITEM_MENU = 4035
				AND COD_PERFIL = (SELECT COD_PERFIL 
								  FROM USUARIO 
								  WHERE COD_USUARIO = $this->cod_usuario)";
		$result = $db->build_results($sql);
		if($result[0]['EXPORTAR'] == 'S')
			$this->habilita_boton($temp, 'export', true);
		else
			$this->habilita_boton($temp, 'export', false);
		
		$sql_nc = "SELECT SUM (TOTAL_CON_IVA)  NC_SIN_USAR
				FROM NOTA_CREDITO WHERE NRO_NOTA_CREDITO > 103473 
				AND dbo.f_get_ip_from_nc(NRO_NOTA_CREDITO, 'S') IN ('NO ASIGNADO', ' / ANULADA')";
		$result_nc = $db->build_results($sql_nc);	
		$nc_sin_usar = number_format($result_nc[0]['NC_SIN_USAR'], 0, ',', '.');
		
		
		$temp->setVar("NC_SIN_USAR", $nc_sin_usar);
		
		
		$sql_total = "select	sum(F.TOTAL_CON_IVA) TOTAL_CON_IVA
				FROM FACTURA F left outer join USUARIO U1 on U1.COD_USUARIO = F.COD_USUARIO_VENDEDOR1 
					           left outer join USUARIO U2 on U2.COD_USUARIO = F.COD_USUARIO_VENDEDOR2
					,EMPRESA E
				WHERE dbo.f_fa_saldo(F.COD_FACTURA) > 0
				  and E.COD_EMPRESA = F.COD_EMPRESA
				  and dbo.f_get_tiene_acceso(".$cod_usuario.", 'FACTURA',F.COD_USUARIO_VENDEDOR1, F.COD_USUARIO_VENDEDOR2) = 1";
		$result_total = $db->build_results($sql_total);
		$total_con_iva = number_format($result_total[0]['TOTAL_CON_IVA'], 0, ',', '.');
		
		$saldo_por_cobrar = number_format($result_total[0]['TOTAL_CON_IVA'] - $result_nc[0]['NC_SIN_USAR'], 0, ',', '.');
		$temp->setVar("SALDO_POR_COBRAR", $saldo_por_cobrar);
   	}
   	
	function print_informe() {
		// reporte
		$sql = $this->dw->get_sql();
		$xml = session::get('K_ROOT_DIR').'appl/inf_facturas_por_cobrar/inf_facturas_por_cobrar_global.xml';
		$labels = array();
		$labels['str_fecha'] = $this->current_date();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Facturas por cobrar.pdf", true);

		$this->_redraw();
	}
	
	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_facturas_por_cobrar', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_inf_facturas_por_cobrar.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_inf_facturas_por_cobrar extends wo_inf_facturas_por_cobrar_base {
		function wo_inf_facturas_por_cobrar() {
			parent::wo_inf_facturas_por_cobrar_base(); 
		}
	}
}
?>