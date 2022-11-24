function validate() {
	var aTR = get_TR('ITEM_AJUSTE_BODEGA');
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	var cod_estado_ajuste_bodega = document.getElementById('COD_ESTADO_AJUSTE_BODEGA_0').value;
	
	// cod_estado_ajuste_bodega = 3 = anulada
	if (to_num(cod_estado_ajuste_bodega) == 3){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
		return false;
		}
	}	
	return true;
}

function valida_cantidad(ve_valor, ve_campo) {
	// valida que el campo cantidad sea  distinto de 0 o null
	var record_item_aj = get_num_rec_field(ve_valor.id);
	var cantidad = document.getElementById('CANTIDAD_' + record_item_aj);
	if(cantidad.value == 0){
		alert('Debe ingresar una cantidad');
		document.getElementById('CANTIDAD_' + record_item_aj).value = '';
		document.getElementById('CANTIDAD_' + record_item_aj).focus();
	}
}

// funcion que despliega un tipo texto si es que el COD_ESTADO_AJUSTE_BODEGA = 'anulada' 
function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==3) {
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

function change_item_ajuste_bodega(ve_valor, ve_campo){
	var record_item_aj = get_num_rec_field(ve_valor.id);
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_aj);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_aj).value.toUpperCase();
	var cod_item_aj = document.getElementById('COD_ITEM_AJUSTE_BODEGA_' + record_item_aj).value;

	if(cod_producto == 'T' | cod_producto == 'TE'){
		document.getElementById('COD_PRODUCTO_' + record_item_aj).value = '';
		alert('No se pueden agregar este Modelo en Ajuste.');
	}	
	else{
		help_producto(ve_valor, 0);
	}	
	
	document.getElementById('NOM_PRODUCTO_STATIC_' + record_item_aj).innerHTML = document.getElementById('NOM_PRODUCTO_' + record_item_aj).value;
}