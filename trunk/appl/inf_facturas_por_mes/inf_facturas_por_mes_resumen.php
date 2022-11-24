<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

if (isset($_POST['b_print'])) {
	$dw_fa = session::get("inf_facturas_por_mes.dw_fa");
	$dw_nc = session::get("inf_facturas_por_mes.dw_nc");
	$dw_tot = session::get("inf_facturas_por_mes.dw_tot");
	session::un_set("inf_facturas_por_mes.dw_fa");
	session::un_set("inf_facturas_por_mes.dw_nc");
	session::un_set("inf_facturas_por_mes.dw_tot");
	
	$fa_cant 			        = $dw_fa->get_item(0, 'FA_CANT');
	$fa_total_neto_afecto 		= $dw_fa->get_item(0, 'FA_TOTAL_NETO_AFECTO');
	$fa_total_neto_exento 		= $dw_fa->get_item(0, 'FA_TOTAL_NETO_EXENTO');
	$fa_monto_iva 		        = $dw_fa->get_item(0, 'FA_MONTO_IVA');
	$fa_total_con_iva 	        = $dw_fa->get_item(0, 'FA_TOTAL_CON_IVA');
	
	
	$nc_cant 			        = $dw_nc->get_item(0, 'NC_CANT');
	$nc_total_neto_afecto 		= $dw_nc->get_item(0, 'NC_TOTAL_NETO_AFECTO');
	$nc_total_neto_exento 		= $dw_nc->get_item(0, 'NC_TOTAL_NETO_EXENTO');
	$nc_monto_iva 		        = $dw_nc->get_item(0, 'NC_MONTO_IVA');
	$nc_total_con_iva 	        = $dw_nc->get_item(0, 'NC_TOTAL_CON_IVA');
	
	$tot_cant 			       = $dw_tot->get_item(0, 'TOT_CANT');
	$tot_total_neto_exento 	   = $dw_tot->get_item(0, 'TOT_TOTAL_NETO_EXENTO');
	$tot_total_neto_afecto 	   = $dw_tot->get_item(0, 'TOT_TOTAL_NETO_AFECTO');
	$tot_monto_iva 		       = $dw_tot->get_item(0, 'TOT_MONTO_IVA');
	$tot_total_con_iva 	       = $dw_tot->get_item(0, 'TOT_TOTAL_CON_IVA');
	
	$sql = "select $fa_cant				    FA_CANT
					,$fa_total_neto_afecto 	FA_TOTAL_NETO_AFECTO
                    ,$fa_total_neto_exento  FA_TOTAL_NETO_EXENTO
					,$fa_monto_iva		    FA_MONTO_IVA
					,$fa_total_con_iva	    FA_TOTAL_CON_IVA
					,$nc_cant			    NC_CANT
					,$nc_total_neto_afecto 	NC_TOTAL_NETO_AFECTO
                    ,$nc_total_neto_exento  NC_TOTAL_NETO_EXENTO
					,$nc_monto_iva		    NC_MONTO_IVA
					,$nc_total_con_iva	    NC_TOTAL_CON_IVA
					,$tot_cant			    TOT_CANT
					,$tot_total_neto_exento TOT_TOTAL_NETO_EXENTO
                    ,$tot_total_neto_afecto TOT_TOTAL_NETO_AFECTO
					,$tot_monto_iva		    TOT_MONTO_IVA
					,$tot_total_con_iva	    TOT_TOTAL_CON_IVA";	
					
	$ano = session::get("inf_facturas_por_mes.ANO");
	$mes_desde = session::get("inf_facturas_por_mes.MES_DESDE");
	$mes_hasta = session::get("inf_facturas_por_mes.MES_HASTA");

	$b = new base();
	$mes_desde = $b->nom_mes($mes_desde);
	$mes_hasta = $b->nom_mes($mes_hasta);

	$labels = array();
	$labels['strANO'] = $ano;
	$labels['strMES_DESDE'] = $mes_desde;
	$labels['strMES_HASTA'] = $mes_hasta;
	$rpt = new reporte($sql, dirname(__FILE__).'/inf_facturas_por_mes_resumen.xml', $labels, "Resumen Facturas", true, true);
}
else if (isset($_POST['b_cancel'])) {
	session::un_set("inf_ventas_por_mes.dw");
	base::presentacion();
}
else {
	$temp = new Template_appl('inf_facturas_por_mes_resumen.htm');	
		
	// make_menu
	$menu = session::get('menu_appl');
	$menu->draw($temp);
	
	$ano = session::get("inf_facturas_por_mes.ANO");
	$mes_desde = session::get("inf_facturas_por_mes.MES_DESDE");
	$mes_hasta = session::get("inf_facturas_por_mes.MES_HASTA");
	$cod_usuario =  session::get("COD_USUARIO");
	$sql = "select    count(*) FA_CANT
                        ,sum(case F.MONTO_IVA
                                when 0 then 0
                                else F.TOTAL_NETO
                            end) FA_TOTAL_NETO_AFECTO
                        ,sum(case F.MONTO_IVA
                                when 0 then F.TOTAL_NETO
                                else 0
                            end) FA_TOTAL_NETO_EXENTO
                        ,sum(F.MONTO_IVA) FA_MONTO_IVA
                        ,sum(F.TOTAL_CON_IVA) FA_TOTAL_CON_IVA
                FROM FACTURA F
                WHERE F.COD_ESTADO_DOC_SII in (2,3)
				  and year(F.FECHA_FACTURA) = $ano
				  and month(F.FECHA_FACTURA) between $mes_desde and $mes_hasta";
	$dw_fa = new datawindow($sql);
	$dw_fa->add_control(new static_num('FA_CANT'));
	$dw_fa->add_control(new static_num('FA_TOTAL_NETO_AFECTO'));
	$dw_fa->add_control(new static_num('FA_MONTO_IVA'));
	$dw_fa->add_control(new static_num('FA_TOTAL_CON_IVA'));
	$dw_fa->add_control(new static_num('FA_TOTAL_NETO_EXENTO'));
	session::set("inf_facturas_por_mes.dw_fa", $dw_fa);
	
	$sql = "select    count(*) NC_CANT
                        ,sum(case NC.MONTO_IVA
                                when 0 then 0
                                else NC.TOTAL_NETO
                            end) NC_TOTAL_NETO_AFECTO
                        ,sum(case NC.MONTO_IVA
                                when 0 then NC.TOTAL_NETO
                                else 0
                            end) NC_TOTAL_NETO_EXENTO
                        ,sum(NC.MONTO_IVA) NC_MONTO_IVA
                        ,sum(NC.TOTAL_CON_IVA) NC_TOTAL_CON_IVA
                FROM NOTA_CREDITO NC
                WHERE NC.COD_ESTADO_DOC_SII in (2,3)
				  and year(NC.FECHA_NOTA_CREDITO) = $ano
				  and month(NC.FECHA_NOTA_CREDITO) between $mes_desde and $mes_hasta";
	$dw_nc = new datawindow($sql);
	$dw_nc->add_control(new static_num('NC_CANT'));
	$dw_nc->add_control(new static_num('NC_TOTAL_AFECTO'));
	$dw_nc->add_control(new static_num('NC_TOTAL_NETO_EXENTO'));
	$dw_nc->add_control(new static_num('NC_TOTAL_NETO_AFECTO'));
	$dw_nc->add_control(new static_num('NC_MONTO_IVA'));
	$dw_nc->add_control(new static_num('NC_TOTAL_CON_IVA'));
	$dw_nc->add_control(new static_num('NC_TOTAL_EXENTO'));
	session::set("inf_facturas_por_mes.dw_nc", $dw_nc);

	$sql = "select	0 TOT_CANT
					,0 TOT_TOTAL_NETO_EXENTO
                    ,0 TOT_TOTAL_NETO_AFECTO
					,0 TOT_MONTO_IVA
					,0 TOT_TOTAL_CON_IVA";
	$dw_tot = new datawindow($sql);
	$dw_tot->add_control(new static_num('TOT_CANT'));
	$dw_tot->add_control(new static_num('TOT_TOTAL_NETO_EXENTO'));
	$dw_tot->add_control(new static_num('TOT_TOTAL_NETO_AFECTO'));
	$dw_tot->add_control(new static_num('TOT_MONTO_IVA'));
	$dw_tot->add_control(new static_num('TOT_TOTAL_CON_IVA'));
	session::set("inf_facturas_por_mes.dw_tot", $dw_tot);

	// draw
	$dw_fa->retrieve();
	$dw_fa->habilitar($temp, false);

	$dw_nc->retrieve();
	$dw_nc->habilitar($temp, false);
	
	$dw_tot->retrieve();
	$dw_tot->set_item(0, 'TOT_CANT', $dw_fa->get_item(0, 'FA_CANT') + $dw_nc->get_item(0, 'NC_CANT'));
	$dw_tot->set_item(0, 'TOT_TOTAL_NETO_EXENTO', $dw_fa->get_item(0, 'FA_TOTAL_NETO_EXENTO') - $dw_nc->get_item(0, 'NC_TOTAL_NETO_EXENTO'));
	$dw_tot->set_item(0, 'TOT_TOTAL_NETO_AFECTO', $dw_fa->get_item(0, 'FA_TOTAL_NETO_AFECTO') - $dw_nc->get_item(0, 'NC_TOTAL_NETO_AFECTO'));	
	$dw_tot->set_item(0, 'TOT_MONTO_IVA', $dw_fa->get_item(0, 'FA_MONTO_IVA') - $dw_nc->get_item(0, 'NC_MONTO_IVA'));
	$dw_tot->set_item(0, 'TOT_TOTAL_CON_IVA', $dw_fa->get_item(0, 'FA_TOTAL_CON_IVA') - $dw_nc->get_item(0, 'NC_TOTAL_CON_IVA'));
	$dw_tot->habilitar($temp, false);

	print $temp->toString();
}
?>