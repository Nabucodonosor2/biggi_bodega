<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_orden_pago extends w_input {
	const K_PARAM_GTE_VTA 			= 16;
	
	function wi_orden_pago($cod_item_menu) {
		parent::w_input('orden_pago', $cod_item_menu);

		$sql = "SELECT COD_ORDEN_PAGO
						,convert(nvarchar, FECHA_ORDEN_PAGO, 103)FECHA_ORDEN_PAGO
						,NOM_USUARIO
						,OP.COD_NOTA_VENTA
						,NOM_EMPRESA
						,COD_TIPO_ORDEN_PAGO
						,OP.TOTAL_NETO
						,OP.PORC_IVA
						,OP.MONTO_IVA
						,OP.TOTAL_CON_IVA
						,convert(nvarchar, FECHA_NOTA_VENTA, 103)FECHA_NOTA_VENTA
						,COD_USUARIO_VENDEDOR1
						,COD_USUARIO_VENDEDOR2
						,(SELECT RUT FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) RUT_NV
						,(SELECT DIG_VERIF FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) DIG_VERIF_NV
						,(SELECT ALIAS FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) ALIAS_NV
						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA)  NOM_EMPRESA_NV
						--resultados
						,NV.TOTAL_NETO TOTAL_NETO_NV 
						,PORC_DSCTO_CORPORATIVO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA_FINAL
						,dbo.f_get_parametro_porc('GF', getdate())PORC_GF
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO') PORC_RESULTADO
						,dbo.f_get_parametro_porc('AA', getdate())PORC_AA
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO
						,PORC_VENDEDOR1  
						,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1
						,PORC_VENDEDOR2
						,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2
						,dbo.f_get_parametro_porc('GV', getdate())PORC_GV
						,dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV
						,dbo.f_get_parametro_porc('ADM', getdate())PORC_ADM
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE
						,NV.REFERENCIA
				FROM 	ORDEN_PAGO OP, USUARIO U, EMPRESA E, NOTA_VENTA NV
				WHERE 	COD_ORDEN_PAGO = {KEY1} AND
						U.COD_USUARIO   = OP.COD_USUARIO AND
						E.COD_EMPRESA = OP.COD_EMPRESA AND
						NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA";			
		$this->dws['dw_orden_pago'] = new datawindow($sql);

		// asigna los formatos		
		$this->dws['dw_orden_pago']->add_control(new edit_nro_doc('COD_ORDEN_PAGO','ORDEN_PAGO'));
		
		$sql = "select 		COD_TIPO_ORDEN_PAGO,
							NOM_TIPO_ORDEN_PAGO
				from 		TIPO_ORDEN_PAGO";
		$this->dws['dw_orden_pago']->add_control(new drop_down_dw('COD_TIPO_ORDEN_PAGO', $sql, 120));
		
		$sql = "select 		COD_USUARIO,
							NOM_USUARIO
				from 		USUARIO";
		$this->dws['dw_orden_pago']->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR1', $sql, 120));
		
		$sql = "select 		COD_USUARIO,
							NOM_USUARIO
				from 		USUARIO";
		$this->dws['dw_orden_pago']->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR2', $sql, 120));
		
		$this->dws['dw_orden_pago']->add_control(new static_num('RUT_NV'));
		$this->dws['dw_orden_pago']->add_control(new static_num('TOTAL_NETO'));
		$this->dws['dw_orden_pago']->add_control(new static_num('MONTO_IVA'));
		$this->dws['dw_orden_pago']->add_control(new static_num('TOTAL_CON_IVA'));
		
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_DSCTO_CORPORATIVO', 1));;
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_GF', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_RESULTADO', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_AA', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_VENDEDOR1', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_VENDEDOR2', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_GV', 1));
		$this->dws['dw_orden_pago']->add_control(new static_num('PORC_ADM', 1));

		$this->dws['dw_orden_pago']->add_control(new static_num('TOTAL_NETO_NV', 0));;
		$this->dws['dw_orden_pago']->add_control(new static_num('MONTO_DSCTO_CORPORATIVO', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('VENTA_NETA_FINAL', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('MONTO_GASTO_FIJO', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('SUM_OC_TOTAL', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('RESULTADO', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('MONTO_DIRECTORIO', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('COMISION_V1', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('COMISION_V2', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('COMISION_GV', 0));
		$this->dws['dw_orden_pago']->add_control(new static_num('COMISION_ADM', 0));		
		$this->dws['dw_orden_pago']->add_control(new static_num('REMANENTE', 0));
	}
	
	function load_record() {
		$COD_ORDEN_PAGO = $this->get_item_wo($this->current_record, 'COD_ORDEN_PAGO');
		$this->dws['dw_orden_pago']->retrieve($COD_ORDEN_PAGO);
		$this->dws['dw_orden_pago']->set_entrable('COD_TIPO_ORDEN_PAGO'      	 , false);
		$this->b_print_visible 	 = true;
	}
	
	function get_key() {
		return $this->dws['dw_orden_pago']->get_item(0, 'COD_ORDEN_PAGO');
	}

	function print_record() {
		$cod_orden_pago = $this->get_key();
		
		$sql = "SELECT  COD_ORDEN_PAGO
						,convert(nvarchar, GETDATE(), 103) FECHA
						,convert(nvarchar, FECHA_ORDEN_PAGO, 103)FECHA_ORDEN_PAGO
						,NOM_USUARIO
						,OP.COD_NOTA_VENTA
						,NOM_EMPRESA
						,COD_TIPO_ORDEN_PAGO
						,(SELECT NOM_TIPO_ORDEN_PAGO from TIPO_ORDEN_PAGO where COD_TIPO_ORDEN_PAGO = OP.COD_TIPO_ORDEN_PAGO) NOM_TIPO_ORDEN_PAGO
						,OP.TOTAL_NETO
						,OP.PORC_IVA
						,OP.MONTO_IVA
						,OP.TOTAL_CON_IVA
						,convert(nvarchar, FECHA_NOTA_VENTA, 103)FECHA_NOTA_VENTA
						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) NOM_USUARIO_VENDEDOR1
						,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) NOM_USUARIO_VENDEDOR2
						,(SELECT RUT FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) RUT_NV
						,(SELECT DIG_VERIF FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) DIG_VERIF_NV
						,(SELECT ALIAS FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA) ALIAS_NV
						,(SELECT NOM_EMPRESA FROM EMPRESA WHERE COD_EMPRESA = NV.COD_EMPRESA)  NOM_EMPRESA_NV
						--resultados
						,NV.TOTAL_NETO TOTAL_NETO_NV 
						,PORC_DSCTO_CORPORATIVO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO') MONTO_DSCTO_CORPORATIVO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'VENTA_NETA') VENTA_NETA_FINAL
						,dbo.f_get_parametro_porc('GF', getdate())PORC_GF
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_GASTO_FIJO') MONTO_GASTO_FIJO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'SUM_OC_TOTAL') SUM_OC_TOTAL
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'RESULTADO') RESULTADO
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'PORC_RESULTADO') PORC_RESULTADO
						,dbo.f_get_parametro_porc('AA', getdate())PORC_AA
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DIRECTORIO') MONTO_DIRECTORIO
						,PORC_VENDEDOR1  
						,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1) VENDEDOR1
						,PORC_VENDEDOR2
						,(select NOM_USUARIO from USUARIO USU where USU.COD_USUARIO = NV.COD_USUARIO_VENDEDOR2) VENDEDOR2
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V1')COMISION_V1
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_V2')COMISION_V2
						,dbo.f_get_parametro_porc('GV', getdate())PORC_GV
						,dbo.f_get_parametro(".self::K_PARAM_GTE_VTA.") GTE_VTA
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_GV')COMISION_GV
						,dbo.f_get_parametro_porc('ADM', getdate())PORC_ADM
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'COMISION_ADM')COMISION_ADM
						,dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'REMANENTE') REMANENTE
				FROM 	ORDEN_PAGO OP, USUARIO U, EMPRESA E, NOTA_VENTA NV
				WHERE 	COD_ORDEN_PAGO = $cod_orden_pago AND
						U.COD_USUARIO   = OP.COD_USUARIO AND
						E.COD_EMPRESA = OP.COD_EMPRESA AND
						NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA";

		$labels = array();
		$labels['strCOD_ORDEN_PAGO'] = $cod_orden_pago;
		$rpt = new rpt_orden_pago($sql, $this->root_dir.'appl/orden_pago/orden_pago.xml', $labels, "Participación Nota de Venta ".$cod_orden_pago, 0);
		$this->_load_record();
		return true;
	}
}

class rpt_orden_pago extends reporte {
	function rpt_orden_pago($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);		
	}

	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);
			
			
	}
}
?>