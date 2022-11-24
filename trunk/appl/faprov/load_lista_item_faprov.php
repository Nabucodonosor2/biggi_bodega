<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$wi = session::get('wi_faprov');

$sql_original = $wi->dws['dw_item_faprov']->get_sql();
if ($wi->faprov_desde=='ORDEN_COMPRA') {
	$sql = "SELECT  'N' SELECCION
					,0 COD_ITEM_FAPROV
					,0 COD_FAPROV
					,OC.COD_ORDEN_COMPRA COD_DOC
					,convert(varchar(20), OC.FECHA_ORDEN_COMPRA, 103) FECHA_ITEM 
					,OC.REFERENCIA REFERENCIA_ITEM
					,OC.COD_NOTA_VENTA
					,OC.TOTAL_NETO TOTAL_NETO_ITEM
					,OC.MONTO_IVA MONTO_IVA_ITEM 
					,OC.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
					,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) SALDO_SIN_FAPROV
					,dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) SALDO_SIN_FAPROV_H
					,0 MONTO_ASIGNADO
					,'none' DISPLAY_CERO
					,0 MONTO_ASIGNADO_C
					,'' TD_REFERENCIA_ITEM
					,'' TD_COD_NOTA_VENTA
					,'' TD_SALDO_SIN_FAPROV
			from	ORDEN_COMPRA OC
			where	OC.COD_EMPRESA = $cod_empresa
			   		and  dbo.f_oc_get_saldo_sin_faprov(OC.COD_ORDEN_COMPRA) > 0
			order by OC.COD_ORDEN_COMPRA asc";
}			
else {
	$sql = "SELECT  'N' SELECCION
					,0 COD_ITEM_FAPROV
					,0 COD_FAPROV
					,P.COD_PARTICIPACION COD_DOC
					,convert(varchar(20), P.FECHA_PARTICIPACION, 103) FECHA_ITEM 
					,null REFERENCIA_ITEM  --por ahora null, no hay campo referencia en participacion 
					,null COD_NOTA_VENTA	--por ahora null, no hay campo cod_nota_venta en participacion
					,P.TOTAL_NETO TOTAL_NETO_ITEM
					,P.MONTO_IVA MONTO_IVA_ITEM 
					,P.TOTAL_CON_IVA TOTAL_CON_IVA_ITEM
					,dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) SALDO_SIN_FAPROV
					,dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) SALDO_SIN_FAPROV_H
					,0 MONTO_ASIGNADO
					,'none' DISPLAY_CERO
					,0 MONTO_ASIGNADO_C
					,'none' TD_REFERENCIA_ITEM
					,'none' TD_COD_NOTA_VENTA
					,'' TD_SALDO_SIN_FAPROV
			from	PARTICIPACION P, USUARIO U
			where	P.COD_USUARIO_VENDEDOR = U.COD_USUARIO
					and U.COD_EMPRESA = $cod_empresa
			   		and  P.TIPO_DOCUMENTO <> 'SUELDO'
			   		and  dbo.f_part_get_saldo_sin_faprov(P.COD_PARTICIPACION) > 0
			order by P.COD_PARTICIPACION asc";	
}
		
$wi->dws['dw_item_faprov']->set_sql($sql);
$wi->dws['dw_item_faprov']->make_tabla_htm($wi->nom_template);
$wi->dws['dw_item_faprov']->set_sql($sql_original);
$wi->save_SESSION();
?>