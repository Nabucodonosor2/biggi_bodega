function confirm_cambio(){
	var vl_sistema = get_value('SISTEMA_0');
	var vl_cod_empresa = get_value('COD_EMPRESA_H_0');
	var vl_cod_pago_faprov = get_value('COD_PAGO_FAPROV_COMERCIAL_0');
	if(vl_sistema == 'TODOINOX')
		var vl_nom_empresa_cli = "Todoinox";
	else if(vl_sistema == 'BODEGA')	
		var vl_nom_empresa_cli = "Bodega";
	
	if(vl_cod_empresa == 1337)
		var vl_nom_empresa_prov = "Comercial Biggi";
	else if(vl_cod_empresa == 9)
		var vl_nom_empresa_prov = "Bodega";
	else if(vl_cod_empresa == 29)
		var vl_nom_empresa_prov = "Rental";
	else if(vl_cod_empresa == 7)
		var vl_nom_empresa_prov = "Todoinox";		
		
	var vl_confirm = confirm("Señor usuario se realizará el traspaso del PAGO FAPROV N° "+vl_cod_pago_faprov+" desde "+vl_nom_empresa_prov+", y se\ningresará como INGRESO PAGO en "+vl_nom_empresa_cli+".");
	if(vl_confirm)
		return true;
	else
		return false;	
}