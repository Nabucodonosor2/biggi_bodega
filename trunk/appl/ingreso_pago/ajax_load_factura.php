 <?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$wi = session::get('wi_ingreso_pago');

$sql_original = $wi->dws['dw_ingreso_pago_factura']->get_sql();
$sql = "SELECT  'N' SELECCION
				,0 COD_INGRESO_PAGO_FACTURA
				,0 COD_INGRESO_PAGO
				,F.COD_FACTURA COD_DOC
				,F.NRO_FACTURA
				,convert(varchar(20), F.FECHA_FACTURA, 103) FECHA_FACTURA
				,F.REFERENCIA
				,dbo.f_factura_get_saldo(F.COD_FACTURA) SALDO
				,dbo.f_factura_get_saldo(F.COD_FACTURA) SALDO_C
				,0 MONTO_ASIGNADO
				,'none' DISPLAY_CERO
				,0 MONTO_ASIGNADO_C
				,dbo.f_factura_get_saldo(F.COD_FACTURA)SALDO_T
				,TOTAL_CON_IVA
				
		from	FACTURA F
		where	F.COD_EMPRESA = $cod_empresa and  
				dbo.f_factura_get_saldo(F.COD_FACTURA) > 0
		order by F.NRO_FACTURA desc";
		
$wi->dws['dw_ingreso_pago_factura']->set_sql($sql);
$wi->dws['dw_ingreso_pago_factura']->make_tabla_htm($wi->nom_template);
$wi->dws['dw_ingreso_pago_factura']->set_sql($sql_original);
$wi->save_SESSION();
?>