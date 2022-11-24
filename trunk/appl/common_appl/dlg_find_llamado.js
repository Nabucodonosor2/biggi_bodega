function dlg_find_llamado() {
	var url = "../common_appl/dlg_find_llamado.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 420,
		 width: 730,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	retorna_cotizacion(returnVal);
		}
	});
}
function buscar_llamado(){
	
	var tabla = document.getElementById('LLAMADO');
	
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}
	
	fecha_desde = document.getElementById('FECHA_DESDE_0').value;
	fecha_hasta = document.getElementById('FECHA_HASTA_0').value;
	empresa = document.getElementById('EMPRESA_0').value;
	rut = document.getElementById('RUT_0').value;
	contacto = document.getElementById('CONTACTO_0').value;
	var ajax = nuevoAjax();
	ajax.open("GET","../common_appl/ajax_llamado.php?fecha_desde="+fecha_desde+"&fecha_hasta="+fecha_hasta+"&empresa="+empresa+"&rut="+rut+"&contacto="+contacto,false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	
	var result = eval("(" + resp + ")");
	
	// arma la tabla resultado
	var vl_tabla = document.getElementById('LLAMADO');
	for (var i=0; i < result.length; i++) {
		var vl_tr = document.createElement("tr");
		vl_tr.className="claro";
		
		var vl_td = document.createElement("td");
		vl_td.width = "15%";
		vl_td.align = "center";
		vl_td.innerHTML = '<a href="#" onclick="returnValue='+result[i]['COD_LLAMADO']+'; setWindowReturnValue(returnValue);window.close();">'+ result[i]['COD_LLAMADO'] +'</a>'; 
		vl_tr.appendChild(vl_td); 

		var vl_td = document.createElement("td");
		vl_td.width = "20%";
		vl_td.align = "center";
		vl_td.innerHTML = result[i]['FECHA_LLAMADO'];
		vl_tr.appendChild(vl_td); 

		var vl_td = document.createElement("td");
		vl_td.width = "37%";
		vl_td.align = "left";
		vl_td.innerHTML = URLDecode(result[i]['NOM_CONTACTO']);
		vl_tr.appendChild(vl_td); 

		var vl_td = document.createElement("td");
		vl_td.width = "28%";
		vl_td.align = "left";
		vl_td.innerHTML = URLDecode(result[i]['NOM_PERSONA']);
		vl_tr.appendChild(vl_td); 

		vl_tabla.appendChild(vl_tr); 
	}
}
function find_1_llamado(ve_campo){
	cod_llamado = ve_campo.value;

	var ajax = nuevoAjax();
	ajax.open("GET","../common_appl/ajax_find_1_llamado.php?cod_llamado="+cod_llamado,false);
	ajax.send(null);	
	var resp = URLDecode(ajax.responseText);
	var resp = eval("(" + resp + ")");
	
	if (resp[0] == 'NO_EXISTE')
		alert('No existe llamado código: '+cod_llamado);
	else{
		document.getElementById('LL_FECHA_LLAMADO_0').innerHTML = resp[0]['FECHA_LLAMADO'];
		document.getElementById('LL_NOM_LLAMADO_ACCION_0').innerHTML = resp[0]['NOM_LLAMADO_ACCION'];
		document.getElementById('LL_NOM_CONTACTO_0').innerHTML = resp[0]['NOM_CONTACTO'];
		document.getElementById('LL_TELEFONO_CONTACTO_0').innerHTML = resp[0]['TELEFONO_CONTACTO'];
		document.getElementById('LL_NOM_PERSONA_0').innerHTML = resp[0]['NOM_PERSONA'];
		document.getElementById('LL_TELEFONO_PERSONA_0').innerHTML = resp[0]['TELEFONO_PERSONA'];
		document.getElementById('LL_MENSAJE_0').innerHTML = resp[0]['MENSAJE'];
	}	
}
function retorna_cotizacion(cod_llamado){
	var ajax = nuevoAjax();
	ajax.open("GET","../common_appl/ajax_find_1_llamado.php?cod_llamado="+cod_llamado,false);
	ajax.send(null);	
	var resp = URLDecode(ajax.responseText);
	var resp = eval("(" + resp + ")");
	
	if (resp == 'NO_EXISTE')
		alert('No existe llamado código: '+cod_llamado);
	else{
		
		document.getElementById('LL_COD_LLAMADO_0').value = resp[0]['COD_LLAMADO'];
		document.getElementById('LL_FECHA_LLAMADO_0').innerHTML = resp[0]['FECHA_LLAMADO'];
		document.getElementById('LL_NOM_LLAMADO_ACCION_0').innerHTML = resp[0]['NOM_LLAMADO_ACCION'];
		document.getElementById('LL_NOM_CONTACTO_0').innerHTML = resp[0]['NOM_CONTACTO'];
		document.getElementById('LL_TELEFONO_CONTACTO_0').innerHTML = resp[0]['TELEFONO_CONTACTO'];
		document.getElementById('LL_NOM_PERSONA_0').innerHTML = resp[0]['NOM_PERSONA'];
		document.getElementById('LL_TELEFONO_PERSONA_0').innerHTML = resp[0]['TELEFONO_PERSONA'];
		document.getElementById('LL_MENSAJE_0').innerHTML = resp[0]['MENSAJE'];
	}	
}