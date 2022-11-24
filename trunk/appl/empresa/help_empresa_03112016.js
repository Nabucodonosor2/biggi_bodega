/////////////////
////////////// HELP DE EMPRESA
/////////////////
function set_empresa_vacio(campo) {
	var campo_id = campo.id;
	var record = get_num_rec_field(campo_id);

	set_value('COD_EMPRESA_' + record, '', '');
	set_value('RUT_' + record, '', '');
	set_value('ALIAS_' + record, '', '');
	set_value('NOM_EMPRESA_' + record, '', '');
	set_value('DIG_VERIF_' + record, '', '');
	set_value('DIRECCION_FACTURA_' + record, '', '');
	set_value('DIRECCION_DESPACHO_' + record, '', '');
	set_value('GIRO_' + record, '', '');
	set_value('SUJETO_A_APROBACION_' + record, '', '');
	set_drop_down_vacio('COD_SUCURSAL_FACTURA_' + record);
	set_drop_down_vacio('COD_SUCURSAL_DESPACHO_' + record);
	set_drop_down_vacio('COD_PERSONA_' + record);
	set_value('MAIL_CARGO_PERSONA_' + record, '', '');
	set_value('COD_CUENTA_CORRIENTE_' + record, '', '');
	set_value('NOM_CUENTA_CORRIENTE_' + record, '', '');
	set_value('NRO_CUENTA_CORRIENTE_' + record, '', '');
	campo.focus();
}
function set_values_empresa(valores, record) {
	set_value('COD_EMPRESA_' + record, valores[1], valores[1]);
	set_value('RUT_' + record, valores[2], valores[2]);
	set_value('ALIAS_' + record, valores[3], valores[3]);
	set_value('NOM_EMPRESA_' + record, valores[4], valores[4]);
	set_value('DIG_VERIF_' + record, valores[5], valores[5]);
	set_value('GIRO_' + record, valores[6], valores[6]);
	set_value('SUJETO_A_APROBACION_' + record, valores[7], valores[7]);
	set_drop_down('COD_SUCURSAL_FACTURA_' + record, valores[8]);
	set_value('DIRECCION_FACTURA_' + record, valores[9], valores[9]);
	set_drop_down('COD_SUCURSAL_DESPACHO_' + record, valores[10]);
	set_value('DIRECCION_DESPACHO_' + record, valores[11], valores[11]);
	set_drop_down('COD_PERSONA_' + record, valores[12]);
	set_value('MAIL_CARGO_PERSONA_' + record, '', '');
	set_value('COD_CUENTA_CORRIENTE_' + record, valores[13], valores[13]);
	set_value('NOM_CUENTA_CORRIENTE_' + record, valores[14], valores[14]);
	set_value('NRO_CUENTA_CORRIENTE_' + record, valores[15], valores[15]);
}
function select_1_empresa(valores, record) {
/* Esta funcion se llama cuando el usuario selecciono una empresa de la lista o el dato
ingresado dio como resultado 1 empresa 

En los modulos donde es usado help_empresa, si se desea agregar un código adiconal se debe 
reimplementar esta funcion
ver ejmplo en nota_venta.js
*/
	 set_values_empresa(valores, record);
}
function help_empresa(campo, tipo_empresa) {
	var campo_id = campo.id;
	var field = get_nom_field(campo_id);
	var record = get_num_rec_field(campo_id);
	var cod_empresa_value = rut_value = alias_value = nom_empresa_value = '';
	switch (field) {
	   case 'COD_EMPRESA':	cod_empresa_value = campo.value;    break;
	   case 'RUT': 					rut_value = campo.value;	break;
	   case 'ALIAS': 				alias_value = campo.value;	break;
	   case 'NOM_EMPRESA': 	nom_empresa_value = campo.value;	break;
	}
	var ajax = nuevoAjax();
	alias_value = URLEncode(alias_value);
	nom_empresa_value = URLEncode(nom_empresa_value);
	var php = "../empresa/help_empresa.php?cod_empresa="+cod_empresa_value+"&rut="+rut_value+"&alias="+alias_value+"&nom_empresa="+nom_empresa_value+"&tipo_empresa="+tipo_empresa;
	ajax.open("GET", php, true);
	ajax.onreadystatechange=function() { 
		if (ajax.readyState==4) {
			var resp = URLDecode(ajax.responseText);
			var lista = resp.split('|');
			switch (lista[0]) {
		  	case '0':	
	 				alert('La empresa no existe, favor ingrese nuevamente');
	 				set_empresa_vacio(campo);
			   	break;
		  	case '1':
		  		select_1_empresa(lista, record);
			   	break;
		  	default:
				var url = "../empresa/help_lista_empresa.php?sql="+URLEncode(lista[1]);
				$.showModalDialog({
					 url: url,
					 dialogArguments: '',
					 height: 480,
					 width: 650,
					 scrollable: false,
					 onClose: function(){ 
					 	var returnVal = this.returnValue;
					 	if (returnVal == null){		
							set_empresa_vacio(campo);
						}			
						else {
							returnVal = URLDecode(returnVal);
			   				var valores = returnVal.split('|');
		  					select_1_empresa(valores, record);
						}
					}
				});	
				break;
			}
		} 
	}
	ajax.send(null);	
}
function direccion_sucursal(sucursal) {
	var sucursal_id = sucursal.id;
	var field = get_nom_field(sucursal_id);
	var record = get_num_rec_field(sucursal_id);
	
	var pos = field.lastIndexOf('_');
	var tipo_sucursal = field.substr(pos, field.length - pos);		// en tipo_sucursal queda "_FACTURA" o "_DESPACHO"
	var direccion = document.getElementById('DIRECCION' + tipo_sucursal + '_' + record); 
	if (sucursal.options[sucursal.selectedIndex].value=='')
		direccion.innerHTML = '';
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "../empresa/direccion_sucursal.php?cod_sucursal="+sucursal.options[sucursal.selectedIndex].value, true);
		ajax.onreadystatechange=function() { 
			if (ajax.readyState==4) {
				var resp = ajax.responseText;
				direccion.innerHTML = URLDecode(resp);
			} 
		}
		ajax.send(null);	
	}
}
function mail_cargo_persona(ve_persona) {
	var persona_id = ve_persona.id;
	var field = get_nom_field(persona_id);
	var record = get_num_rec_field(persona_id);
	
	var mail_cargo = document.getElementById('MAIL_CARGO_PERSONA_' + record); 
	if (ve_persona.options[ve_persona.selectedIndex].value=='')
		mail_cargo.innerHTML = '';
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "../empresa/mail_cargo_persona.php?cod_persona="+ve_persona.options[ve_persona.selectedIndex].value, true);
		ajax.onreadystatechange=function() { 
			if (ajax.readyState==4) {
				var resp = ajax.responseText;
				mail_cargo.innerHTML = URLDecode(resp);
			} 
		}
		ajax.send(null);	
	}
}
function crear_cliente(ve_cod_item_menu) {
	var returnVal = add_documento('empresa', ve_cod_item_menu);
 	if (returnVal == null)
 		return false;
	else {
		var cod_empresa = document.getElementById('COD_EMPRESA_0'); 
		cod_empresa.value = returnVal; 
		help_empresa(cod_empresa, 'C');
   		return true;
	}
}
function modificar_cliente(ve_cod_item_menu) {
	var cod_empresa_value = document.getElementById('COD_EMPRESA_0').value;
	if (cod_empresa_value=='') {
		alert('Debe seleccionar un cliente');
		return false;
	}
	var returnVal = mod_documento('empresa', cod_empresa_value, ve_cod_item_menu, 'S');
 	if (returnVal == null)
 		return false;
	else {
		var cod_empresa = document.getElementById('COD_EMPRESA_0'); 
		cod_empresa.value = returnVal; 
		help_empresa(cod_empresa, 'C');
   		return true;
	}
}