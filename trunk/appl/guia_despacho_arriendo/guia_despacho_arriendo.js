function validate() {
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0');
	// cod_estado_doc_sii_value = 1 = emitida
	if (to_num(cod_estado_doc_sii_value) == 1){
		var aTR = get_TR('ITEM_GUIA_DESPACHO');
		var cant_total = 0;
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	
		for (var i = 0; i < aTR.length; i++){
			cant_total = cant_total + document.getElementById('CANTIDAD_' + i).value;		
		}	
		
		if(cant_total == 0){
			alert('La Cantidad a Despachar debe ser superior a "0"');
			document.getElementById('CANTIDAD_0').focus();
			return false;
		}	
	
	}
	// cod_estado_doc_sii_value = 4 = anulada
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	return true;
}
function valida_ct_x_despachar(ve_campo) {
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_despachar = to_num(document.getElementById('CANTIDAD_POR_DESPACHAR_' + record).innerHTML);
		var vl_cantidad_bodega = document.getElementById('CANTIDAD_BODEGA_' + record).value;
		var cant_ingresada = to_num(ve_campo.value);
		if (parseFloat(cant_por_despachar) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad "por Despachar": '+ number_format(cant_por_despachar, 1, ',', '.'));
			return number_format(cant_por_despachar, 1, ',', '.');
		}
		else if (parseFloat(vl_cantidad_bodega) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad disponible en Bodega, stock actual: '+ number_format(vl_cantidad_bodega, 1, ',', '.'));
			return number_format(vl_cantidad_bodega, 1, ',', '.');
		}
		else
			return ve_campo.value;
	}
	else
		return ve_campo.value;
}
function dlg_print() {
	var vl_nro_guia_despacho = document.getElementById('NRO_GUIA_DESPACHO_0').innerHTML;
	if (vl_nro_guia_despacho == '') {
		var vl_new_nro_guia_despacho = document.getElementById('NEW_NRO_GUIA_DESPACHO_0').value;
		return request('Ingrese el número de la Guía de Despacho:', vl_new_nro_guia_despacho);
	}
	else {
		document.getElementById('wi_hidden').value = vl_nro_guia_despacho;
		return true;
	}
}
function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==4) {
		tr_anula.style.display = ''; 
		
		document.getElementById('MOTIVO_ANULA_0').type='text';
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('MOTIVO_ANULA_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
		document.getElementById('MOTIVO_ANULA_0').focus();
	}
	else{
		document.getElementById('MOTIVO_ANULA_0').value = '';
		tr_anula.style.display = 'none'; 
	}
}
