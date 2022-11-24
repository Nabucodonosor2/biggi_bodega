function change_rut(ve_rut) {
 	if(ve_rut.value == ''){
		alert('Debe ingresar un RUT');
		return false;
	}else{
		//llama ajax
		var rut_empresa = ve_rut.value; 
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_valida_empresa.php?rut_empresa="+rut_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var aDato = eval("(" + resp + ")");
		var dig_verif = aDato[0]['DIG_VERIF'];

		if(dig_verif == 'NO_EXISTE'){
			alert('El RUT de la Empresa no Existe');
			return false;			
		}else{
			document.getElementById('DIG_VERIF_0').innerHTML = dig_verif;
		}
	}
}

function valida_fecha() {
	var f_inicio = document.getElementById('F_INICIAL_0').value;
	var f_termino = document.getElementById('F_TERMINO_0').value;
	var f_actual = document.getElementById('F_ACTUAL_0').value;
	var vl_rut = document.getElementById('RUT_0');

	if(change_rut(vl_rut) == false){
		return false;
	}

 	if((f_inicio == '') | (f_termino == '')){
		if(f_inicio == ''){
			alert('Debe ingresar un Fecha Inicial');
			return false;
		}else{
			alert('Debe ingresar una Fecha Final');
			return false;
		}
	}else{
		var fecha_inicial = f_inicio.split('/');
		var fecha_termino = f_termino.split('/');
		var fecha_actual  = f_actual.split('/');
		
		var fecha_ini = new Date(fecha_inicial[2],fecha_inicial[1],fecha_inicial[0]);
		var fecha_ter = new Date(fecha_termino[2],fecha_termino[1],fecha_termino[0]);
		var fecha_act = new Date(fecha_actual[2],fecha_actual[1],fecha_actual[0]); 
		
		if(fecha_ini > fecha_ter){
			alert('La Fecha Inicial tiene que ser Menor a la Fecha Final');
			return false;
		}else if(fecha_ter > fecha_act){
			alert('La Fecha Final no puede ser Mayor a la fecha de Hoy');
			return false;
		}
	}
	return true;
}

function valida_datos() {

		var f_inicio = document.getElementById('F_INICIAL_0').value;
		var f_termino = document.getElementById('F_TERMINO_0').value;
		var rut_empresa = document.getElementById('RUT_0').value;
 
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_valida_dato.php?vl_rut="+rut_empresa+"&f_inicio="+f_inicio+"&f_termino="+f_termino, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var aDato = eval("(" + resp + ")");
		var resp = aDato[0]['COUNT'];

		if(resp == '0'){
			alert('No Existen Registros Guardados');
			return false;			
		}
	return true;
}
