function devolver_docto(){	
	//para no validacion cuando graba y ha devuelto - pc
	document.getElementById('DEVOLUCION_H_0').value = 'S';
	//********************************************************
	
	var asig_nro_doc_sii = document.getElementById('COD_ASIG_NRO_DOC_SII_H_0').value;
    var ajax = nuevoAjax();
    ajax.open("GET", "devolver_docto.php?cod_asig="+asig_nro_doc_sii, false);
    ajax.send(null);    

    var resp = ajax.responseText.split('|');      // retorna los dos nros del rango devuelto	
	 
	document.getElementById('NRO_INICIO_DEVOL_0').innerHTML = to_num(resp[1]);
	document.getElementById('NRO_INICIO_DEVOL_H_0').value = to_num(resp[1]);
    document.getElementById('NRO_TERMINO_DEVOL_0').innerHTML = to_num(resp[2]);
    document.getElementById('NRO_TERMINO_DEVOL_H_0').value = to_num(resp[2]);
	document.getElementById('FECHA_DEVOL_0').innerHTML = resp[0];	
	document.getElementById('FECHA_DEVOL_0').value = resp[0];

}

function validate_asignacion_dctos() {
	var cod_tipo_doc_sii_value;
	var cod_tipo_doc_sii_value;
	var cod_usuario_receptor;
	var nro_inicio_value;
	var nro_termino_value;
	
	cod_tipo_doc_sii_value = document.getElementById('COD_TIPO_DOC_SII_0').value;
	cod_usuario_receptor = document.getElementById('COD_USUARIO_RECEPTOR_0').value;	
	nro_inicio_value = document.getElementById('NRO_INICIO_0').value;
	nro_termino_value = document.getElementById('NRO_TERMINO_0').value;
	
	var ajax = nuevoAjax();    
    ajax.open("GET", "valida_docs_asignados.php?cod_tipo_doc_sii="+cod_tipo_doc_sii_value+"&cod_usuario_receptor="+cod_usuario_receptor+"&nro_inicio="+nro_inicio_value+"&nro_termino="+nro_termino_value, false);
    ajax.send(null);    

    var resp = ajax.responseText;
           
    if (resp=='S') {		
		alert ('Error, los rangos seleccionados están siendo utilizados');
		return false;	
	}
	
	return true;

}

function validate_doc_usados(){   
// Valida que no existan nros usados en el rango indicado
	var cod_tipo_doc_sii_value;
	var nro_inicio_value;
	var nro_termino_value;
	
	cod_tipo_doc_sii_value = document.getElementById('COD_TIPO_DOC_SII_0').value;
	nro_inicio_value = document.getElementById('NRO_INICIO_0').value;
	nro_termino_value = document.getElementById('NRO_TERMINO_0').value;
	
    var ajax = nuevoAjax();
    ajax.open("GET", "existen_nros_impresos.php?cod_tipo_doc_sii="+cod_tipo_doc_sii_value+"&nro_inicio="+nro_inicio_value+"&nro_termino="+nro_termino_value, false);
    ajax.send(null);    

    var resp = ajax.responseText;      // retorna 'S' si existen nros usados o 'N' en caso contrario
    
    if (resp=='S') {
		alert ('Existen documentos impresos con números dentro del rango ingresado.');
		return false;	
	}
	return true;
}

function validate_menor_mayor(){
	var nro_inicio = document.getElementById('NRO_INICIO_0').value;
	var nro_termino = document.getElementById('NRO_TERMINO_0').value;	
	if (parseInt(to_num(nro_inicio))>parseInt(to_num(nro_termino))){
		alert ('Incorrecta asignación de Documentos.\n \n El número Inicial es mayor que el número Final.');
		return false;
	}
}

function validate() {
	if(validate_menor_mayor() == false)
		return false;
	if(validate_asignacion_dctos() == false)
		return false;	
	if(validate_doc_usados() == false)
		return false;
}

		