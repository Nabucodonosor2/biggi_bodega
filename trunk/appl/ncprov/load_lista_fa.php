<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_empresa = $_REQUEST['cod_empresa'];

$wi = session::get('wi_ncprov');

$sql_original = $wi->dws['dw_ncprov_faprov']->get_sql();
$sql = "SELECT			'N'SELECCION
						,0 COD_NCPROV_FAPROV
						,0 COD_NCPROV
						,COD_FAPROV
						,NRO_FAPROV 
						,convert(varchar(20), FECHA_FAPROV, 103) FECHA_FAPROV_FA
						,TOTAL_NETO TOTAL_NETO_FA 
						,MONTO_IVA MONTO_IVA_FA  
						,TOTAL_CON_IVA TOTAL_CON_IVA_FA
						,dbo.f_faprov_get_saldo_sin_ncprov(COD_FAPROV)SALDO_SIN_NCPROV
						,dbo.f_faprov_get_saldo_sin_ncprov(COD_FAPROV)SALDO_SIN_NCPROV_H
						,0 MONTO_ASIGNADO
						,0 MONTO_ASIGNADO_C 
				FROM	FAPROV
				WHERE 	COD_EMPRESA = $cod_empresa AND
						dbo.f_faprov_get_saldo_sin_ncprov(COD_FAPROV) > 0
						order by NRO_FAPROV desc";
		
$wi->dws['dw_ncprov_faprov']->set_sql($sql);
$wi->dws['dw_ncprov_faprov']->make_tabla_htm($wi->nom_template);
$wi->dws['dw_ncprov_faprov']->set_sql($sql_original);
$wi->save_SESSION();
?>