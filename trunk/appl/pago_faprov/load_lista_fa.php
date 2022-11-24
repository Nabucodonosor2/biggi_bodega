<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa']; 
$cod_cuenta_corriente = $_REQUEST['cod_cuenta_corriente'];	// si viene $cod_cuenta_corriente=0, significa todas las FA del rut

	
$wi = session::get('wi_pago_faprov');

$sql_original = $wi->dws['dw_pago_faprov_faprov']->get_sql();
$sql = "SELECT 'N' SELECCION
				,0 COD_PAGO_FAPROV_FAPROV
				,0 COD_PAGO_FAPROV
				,F.COD_FAPROV
				,F.NRO_FAPROV
				,convert(varchar(20), F.FECHA_FAPROV, 103) FECHA_FAPROV
				,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA
				,dbo.f_pago_faprov_get_monto_ncprov(F.COD_FAPROV)MONTO_NCPROV
				,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV
				,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV_H
				,0 MONTO_ASIGNADO
				,0 MONTO_ASIGNADO_C 
				,dbo.f_pago_faprov_get_pago_ant(F.COD_FAPROV) PAGO_ANTERIOR
				,cc.NOM_CUENTA_CORRIENTE
		FROM 	FAPROV F, CUENTA_COMPRA c, CUENTA_CORRIENTE cc
		WHERE 	F.COD_EMPRESA = $cod_empresa  
		  and 	dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) > 0
		  and 	c.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA
          and 	($cod_cuenta_corriente = 0 or c.COD_CUENTA_CORRIENTE = $cod_cuenta_corriente)
          and	c.COD_CUENTA_CORRIENTE = cc.COD_CUENTA_CORRIENTE				
		order by F.NRO_FAPROV desc";
		
$wi->dws['dw_pago_faprov_faprov']->set_sql($sql);
$wi->dws['dw_pago_faprov_faprov']->make_tabla_htm($wi->nom_template);
$wi->dws['dw_pago_faprov_faprov']->set_sql($sql_original);
$wi->save_SESSION();
?>