function add_line(tabla_id, nom_tabla) {
	add_line_porc(tabla_id);
}
function add_line_porc(ve_tipo_porc) {
	var tr = document.getElementById('EN_' + ve_tipo_porc);
	tr.style.display = '';	
	 
	var visible = document.getElementById('VISIBLE_' + ve_tipo_porc + '_0' );	
	visible.value = 'S';
}

function del_line_porc(ve_tipo_porc) {
	var tr = document.getElementById('EN_' + ve_tipo_porc);
	tr.style.display = 'none';	
	 
	var visible = document.getElementById('VISIBLE_' + ve_tipo_porc  + '_0');
	visible.value = 'N';
}


function validate_porc_param (ve_tipo_param) {
	var porc 	= document.getElementById('APORTE_' + ve_tipo_param + '_0');
	var fecha 	= document.getElementById('FECHA_' + ve_tipo_param + '_0');
	var visible = document.getElementById('VISIBLE_PORCENTAJE_' + ve_tipo_param + '_0');	
	
	if (visible.value == 'S'){
		if (porc.value == ''){
			alert('Debe ingresar un Porcentaje antes de grabar');
			porc.focus();
			return false;
		}
		if (fecha.value == ''){
			alert('Debe ingresar una Fecha antes de grabar');
			fecha.focus();
			return false;
		}		
	}	
	return true;
} 

function validate() {
	if (!validate_porc_param('AA'))
		return false;
		
	if (!validate_porc_param('GF'))
		return false;
		
	if (!validate_porc_param('GV'))
		return false;
	
	if (!validate_porc_param('ADM'))
		return false;
				
	return true;
}
function valida_acceso(ve_cod_item_menu) {
   	ajax = nuevoAjax();
	ajax.open("GET", "ajax_tiene_acceso.php?cod_item_menu=" + ve_cod_item_menu, false);
    ajax.send(null);
     
    var resp = URLDecode(ajax.responseText);   
	if (resp=='N') {
		alert('Ud. no esta autorizado para ingresar a esta opción.');
		return false;
	}
	return true;
}