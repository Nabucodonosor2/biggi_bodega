function validate() {
	var K_ENVIO_CONFIRMADA = 2;
	var K_TIPO_COMPRA = 2;
	
	var vl_cod_estado_envio = document.getElementById('COD_ESTADO_ENVIO_0');
	if (vl_cod_estado_envio) {	// esta ingresable
		if (vl_cod_estado_envio.value==K_ENVIO_CONFIRMADA) {	// confirmada
			// valida que este ingresado el nro del comprobante
			var vl_nro_comprobante = document.getElementById('NRO_COMPROBANTE_0');
			if (vl_nro_comprobante.value=='' || vl_nro_comprobante.value==0) {
				alert("Para confirmar bebe ingresar el número del comprobante");
				vl_nro_comprobante.focus();
				return false;
			}
			
			// valida que no existan FA o NC de diferentes meses
			/*var vl_dif_meses = document.getElementById('RE_DIF_MESES_0');
			if (vl_dif_meses.innerHTML!='') {
				alert(vl_dif_meses.innerHTML);
				return false;
			}*/
			
			// si es envio de compras debe ingresar el correlativo interno
			var vl_cod_tipo_envio = document.getElementById('COD_TIPO_ENVIO_0').value;
			if (vl_cod_tipo_envio==K_TIPO_COMPRA) {
				var vl_nro_correlativo_interno = document.getElementById('NRO_CORRELATIVO_INTERNO_0');
				if (vl_nro_correlativo_interno.value=='' || vl_nro_correlativo_interno.value==0) {
					alert("Para confirmar bebe ingresar el número de Correlativo interno");
					vl_nro_correlativo_interno.focus();
					return false;					
				}
			}
		}
	}
	return true;
}
function selecciona_documento(ve_seleccion) {
	var vl_record = get_num_rec_field(ve_seleccion.id);
	var vl_tipo;
	if (ve_seleccion.id.substr(0,3)=='FA_')
		vl_tipo = 'FA';
	else if (ve_seleccion.id.substr(0,3)=='FC_')
		vl_tipo = 'FC';
	else if (ve_seleccion.id.substr(0,4)=='NCC_')
		vl_tipo = 'NCC';
	
	// obtiene los datos de la linea clickeada
	var vl_neto = document.getElementById(vl_tipo + '_TOTAL_NETO_' + vl_record).innerHTML;
	var vl_iva = document.getElementById(vl_tipo + '_MONTO_IVA_' + vl_record).innerHTML;
	var vl_total = document.getElementById(vl_tipo + '_TOTAL_CON_IVA_' + vl_record).innerHTML;
	// borra los puntos en los miles
	vl_neto = parseInt(to_num(vl_neto));
	vl_iva = parseInt(to_num(vl_iva));
	vl_total = parseInt(to_num(vl_total));
	
	// Para las compras, FC -> FA; NCC -> NC
	if (vl_tipo == 'FC')
		vl_tipo = 'FA';
	else if (vl_tipo == 'NCC')
		vl_tipo = 'NC';
	
	// obtiene los totales del resumen
	var vl_tot_cant = document.getElementById('RE_CANT_' + vl_tipo + '_0').innerHTML;
	var vl_tot_neto = document.getElementById('RE_TOTAL_NETO_' + vl_tipo + '_0').innerHTML;
	var vl_tot_iva = document.getElementById('RE_MONTO_IVA_' + vl_tipo + '_0').innerHTML;
	var vl_tot_total = document.getElementById('RE_TOTAL_' + vl_tipo + '_0').innerHTML;
	// borra los puntos en los miles
	vl_tot_cant = parseInt(to_num(vl_tot_cant));
	vl_tot_neto = parseInt(to_num(vl_tot_neto));
	vl_tot_iva = parseInt(to_num(vl_tot_iva));
	vl_tot_total = parseInt(to_num(vl_tot_total));
	
	// calcula los nuevos totales
	if (ve_seleccion.checked) {
		vl_tot_cant = vl_tot_cant + 1;
		vl_tot_neto = vl_tot_neto + vl_neto;
		vl_tot_iva = vl_tot_iva + vl_iva;
		vl_tot_total = vl_tot_total + vl_total;
	}
	else {
		vl_tot_cant = vl_tot_cant - 1;
		vl_tot_neto = vl_tot_neto - vl_neto;
		vl_tot_iva = vl_tot_iva - vl_iva;
		vl_tot_total = vl_tot_total - vl_total;
	}
	
	//actualiza los totales
	document.getElementById('RE_CANT_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_cant, 0, ',', '.');
	document.getElementById('RE_TOTAL_NETO_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_neto, 0, ',', '.');
	document.getElementById('RE_MONTO_IVA_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_iva, 0, ',', '.');
	document.getElementById('RE_TOTAL_' + vl_tipo + '_0').innerHTML = number_format(vl_tot_total, 0, ',', '.');
	
	// verifica si existen FA o NC de diferentes meses
	var vl_dif_meses = false;
	var vl_mes = 0; 
	var aTR = get_TR('ENVIO_FACTURA');
	if (aTR.length > 0) {
		for (var i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('FA_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('FA_FECHA_FACTURA_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1]; 
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	var aTR = get_TR('ENVIO_NOTA_CREDITO');
	if (vl_dif_meses==false && aTR.length > 0) {
		for (i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);			
			if (document.getElementById('NC_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('NC_FECHA_NOTA_CREDITO_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else { 
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	if (vl_dif_meses)
		document.getElementById('RE_DIF_MESES_0').innerHTML = 'Existen Facturas o Nota de Crédito de diferentes meses';
	else
		document.getElementById('RE_DIF_MESES_0').innerHTML = '';
}
function change_nro_comprobante(ve_nro_comprobante) {
	//Ajax para saber si el nro ya esta siendo usado
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_nro_comprobante_usado.php?nro_comprobante="+ve_nro_comprobante.value, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	if (resp != 0) {
		alert('El nro de comprobante ' + ve_nro_comprobante.value + ' ya esta siendo usado en el Envío nro: ' + resp);
		ve_nro_comprobante.value = '';
		ve_nro_comprobante.focus();
	}
}
function dlg_print() {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:470px;dialogHeight:225px;dialogLocation:0;Toolbar:no;";
	var vl_cod_tipo_envio = document.getElementById('COD_TIPO_ENVIO_0').value;
	var returnVal = window.showModalDialog("dlg_print_envio_softland.php?cod_tipo_envio="+vl_cod_tipo_envio, "_blank", args);
 	if (returnVal == null)
 		return false;
	else {
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
		
		
		// Asigna correlativo compra
		var vl_correlativo = document.getElementById('NRO_CORRELATIVO_H_0').value;
		var aTR = get_TR('ENVIO_FAPROV');
		for (var i=0; i < aTR.length; i++)	{
			var vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('FC_SELECCION_' + vl_record).checked) {
				document.getElementById('FC_CORRELATIVO_' + vl_record).innerHTML = vl_correlativo;
				vl_correlativo++; 
			}
		}
		var aTR = get_TR('ENVIO_NCPROV');
		for (var i=0; i < aTR.length; i++)	{
			var vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('NCC_SELECCION_' + vl_record).checked) {
				document.getElementById('NCC_CORRELATIVO_' + vl_record).innerHTML = vl_correlativo;
				vl_correlativo++; 
			}
		}
		
   		return true;
	}
}
function marcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById(ve_prefijo + '_' + vl_record).checked = true;
	}
}
function desmarcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById(ve_prefijo + '_' + vl_record).checked = false;
	}
}
function dejar_seleccion(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR(ve_tabla_id);
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById(ve_prefijo + '_' + vl_record).checked == false)
			del_line(aTR[i].id, 'envio_softland');
	}
}
function request_cuenta(ve_tabla_id, ve_prefijo) {
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:350px;dialogHeight:180px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("request_cuenta.php", "_blank", args);
	
	if (returnVal == null)		
		return false;		
	else {
		var aLista = returnVal.split('|'); 
		var vl_cod_cuenta_compra = aLista[0];
		var vl_nom_cuenta_compra = aLista[1];
		var aTR = get_TR(ve_tabla_id);
		for (var i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById(ve_prefijo + '_SELECCION_CUENTA_' + vl_record).checked == true) {
				document.getElementById(ve_prefijo + '_COD_CUENTA_COMPRA_' + vl_record).value = vl_cod_cuenta_compra;
				document.getElementById(ve_prefijo + '_NOM_CUENTA_COMPRA_' + vl_record).innerHTML = vl_nom_cuenta_compra;
				document.getElementById(ve_prefijo + '_SELECCION_CUENTA_' + vl_record).checked = false;
			}
		}
	   	return true;	
	}
}
function selecciona_faprov(ve_seleccion) {
	var vl_record = get_num_rec_field(ve_seleccion.id);
	
	// obtiene los datos de la linea clickeada
	var vl_monto = document.getElementById(vl_tipo + 'EG_MONTO_DOCUMENTO_' + vl_record).innerHTML;
	// borra los puntos en los miles
	vl_monto = parseInt(to_num(vl_monto));
	
	// obtiene los totales del resumen
	var vl_tot_cant = document.getElementById('RE_CANT_EG_0').innerHTML;
	var vl_tot_monto = document.getElementById('RE_MONTO_EG_0').innerHTML;
	// borra los puntos en los miles
	vl_tot_cant = parseInt(to_num(vl_tot_cant));
	vl_tot_monto = parseInt(to_num(vl_tot_monto));
	
	// calcula los nuevos totales
	if (ve_seleccion.checked) {
		vl_tot_cant = vl_tot_cant + 1;
		vl_tot_monto = vl_tot_monto + vl_monto;
	}
	else {
		vl_tot_cant = vl_tot_cant - 1;
		vl_tot_monto = vl_tot_monto - vl_monto;
	}
	
	//actualiza los totales
	document.getElementById('RE_CANT_EG_0').innerHTML = number_format(vl_tot_cant, 0, ',', '.');
	document.getElementById('RE_MONTO_EG_0').innerHTML = number_format(vl_tot_monto, 0, ',', '.');
	
	/*
	// verifica si existen FA o NC de diferentes meses
	var vl_dif_meses = false;
	var vl_mes = 0; 
	var aTR = get_TR('ENVIO_FACTURA');
	if (aTR.length > 0) {
		for (var i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);
			if (document.getElementById('FA_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('FA_FECHA_FACTURA_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1]; 
				else {
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	var aTR = get_TR('ENVIO_NOTA_CREDITO');
	if (vl_dif_meses==false && aTR.length > 0) {
		for (i=0; i < aTR.length; i++)	{
			vl_record = get_num_rec_field(aTR[i].id);			
			if (document.getElementById('NC_SELECCION_' + vl_record).checked) {
				var vl_fecha = document.getElementById('NC_FECHA_NOTA_CREDITO_' + vl_record).innerHTML;
				var aFecha = vl_fecha.split('/');
				if (vl_mes==0)
					vl_mes = aFecha[1];
				else { 
					if (vl_mes != aFecha[1]) {
						vl_dif_meses = true;
						break;
					}
				}
			}
		}
	}
	if (vl_dif_meses)
		document.getElementById('RE_DIF_MESES_0').innerHTML = 'Existen Facturas o Nota de Crédito de diferentes meses';
	else
		document.getElementById('RE_DIF_MESES_0').innerHTML = '';
	*/
}