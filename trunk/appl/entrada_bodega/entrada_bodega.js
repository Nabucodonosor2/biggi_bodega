function add_line_item(ve_tabla_id, ve_nom_tabla) {
	var vl_row = add_line(ve_tabla_id, ve_nom_tabla);
	return vl_row
}


function valida_cantidad_max(ve_campo){
	var record = get_num_rec_field(ve_campo.id);
	var cantidad = ve_campo.value;
	var cantidad_max = document.getElementById('CANTIDAD_MAX_'+record).value;
	
	if (parseFloat(cantidad) > parseFloat(cantidad_max)){
		alert('La cantidad máxima de entrada es: '+cantidad_max);
		document.getElementById('CANTIDAD_'+record).value = cantidad_max;
		return false;
	}
}

function validate_save(){
	var aTR = get_TR('ITEM_ENTRADA_BODEGA');
	for(i=0 ; i < aTR.length ; i++){
		var record = get_num_rec_field(aTR[i].id);
		var cantidad = document.getElementById('CANTIDAD_'+record).value;
		var cantidad_max = document.getElementById('CANTIDAD_MAX_'+record).value;

		if (parseFloat(cantidad) > parseFloat(cantidad_max)){
			alert('La cantidad máxima de entrada es: '+cantidad_max);
			document.getElementById('CANTIDAD_'+record).value = cantidad_max;
			computed(get_num_rec_field('CANTIDAD_'+record), 'TOTAL');
			document.getElementById('CANTIDAD_'+record).focus();
			return false;
		}
	}
	
	return true;
}
