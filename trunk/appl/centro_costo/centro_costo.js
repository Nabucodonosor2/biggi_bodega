function validate() {
	var aTR = get_TR('CENTRO_COSTO_EMPRESA');	
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item de Empresa antes de grabar.');
			return false;
		}
	return true;
}

function select_1_empresa(valores, record) {
	set_values_empresa(valores, record);
	var cod_empresa = document.getElementById('COD_EMPRESA_' + record).value;
	var nom_empresa = document.getElementById('NOM_EMPRESA_' + record).value;

	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_valida_empresa.php?cod_empresa="+cod_empresa, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	var aDato = eval("(" + resp + ")");
	var dato_centro_costo = aDato[0]['COD_CENTRO_COSTO'];

	if(dato_centro_costo != 'NO_EXISTE'){
		alert('La Empresa  '+ nom_empresa + '  Se encuentra registrada. \nEn Centro Costo con el C\u00f3digo  "' + dato_centro_costo +'"');
		document.getElementById('COD_EMPRESA_' + record).value = '';
		document.getElementById('RUT_' + record).value = '';
		document.getElementById('DIG_VERIF_' + record).innerHTML = '';
		document.getElementById('ALIAS_' + record).value = '';
		document.getElementById('NOM_EMPRESA_' + record).value = '';
		document.getElementById('COD_EMPRESA_' + record).focus();
	}
}