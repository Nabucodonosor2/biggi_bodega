function validate() {
	var cod_estado_participacion = get_value('COD_ESTADO_PARTICIPACION_0'); 
	if (to_num(cod_estado_participacion) == 3){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el Motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	if (parseInt(document.getElementById('TOTAL_NETO_H_0').value) == 0){
		alert('Debe seleccionar alguna orden de pago antes de grabar.');
		return false;
	}
	
	var vl_tipo_documento = document.getElementById('TIPO_DOCUMENTO_H_0').value;
	if (vl_tipo_documento=='SUELDO') {
		var vl_monto = document.getElementById('TOTAL_NETO_H_0').value;
		var vl_suma = document.getElementById('SUM_TOTAL_NETO_POP_C_H_0').value;
		if (parseInt(vl_suma) < parseInt(vl_monto)) {
			alert('Las OP seleccionadas suman menos que el sueldo ingresado.');
			return false;
		}
	}
}	

function dlg_ok() {
	var cod_usuario = document.getElementById('COD_USUARIO_0').value;
	var cod_tipo_participacion = document.getElementById('COD_TIPO_ORDEN_PAGO_0').value;
	var cod_centro_costo = document.getElementById('COD_CC_0').value;

	if (parseInt(cod_usuario) == 0){
		alert('Debe seleccionar Vendedor.');
		document.getElementById('COD_USUARIO_0').focus();
		return false;
	}else if (parseInt(cod_tipo_participacion) == 0){
		alert('Debe seleccionar Tipo Participacion.');
		document.getElementById('COD_TIPO_ORDEN_PAGO_0').focus();
		return false;
	}else if (cod_centro_costo == ''){
		alert('Debe seleccionar Centro Costo.');
		document.getElementById('COD_CC_0').focus();
		return false;
	}else{
		if(cod_centro_costo != '0'){
			var ajax = nuevoAjax();
			ajax.open("GET", "ajax_cant_sql_item.php?cod_usuario="+cod_usuario+"&cod_tipo_op="+cod_tipo_participacion+"&cod_cc="+cod_centro_costo, false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);
			var lista1	= resp.split('|');
			var	count_sql = (lista1[0]);
			var	nom_usuario = (lista1[1]);
			var	nom_tipo_orden_pago =(lista1[2]);
			var	nom_centro_costo =(lista1[3]);
			if(count_sql == 0){
				alert('El vendedor no tiene participaciones pendientes.  Filtros: \nVendedor: ' +nom_usuario+ '\nTipo Participacion: '+ nom_tipo_orden_pago + '\nCentro Costo: '+nom_centro_costo);
				return false;
			}else{
				returnValue= document.getElementById('COD_USUARIO_0').value + '|' + document.getElementById('COD_TIPO_ORDEN_PAGO_0').value+ '|' + document.getElementById('COD_CC_0').value;
				if (document.getElementById('ES_SUELDO_0').checked)
					returnValue= returnValue + '|S'
				else
					returnValue= returnValue + '|N'
				window.close();
			}
		}else{
			returnValue= document.getElementById('COD_USUARIO_0').value + '|' + document.getElementById('COD_TIPO_ORDEN_PAGO_0').value+ '|' + document.getElementById('COD_CC_0').value;
			if (document.getElementById('ES_SUELDO_0').checked)
				returnValue= returnValue + '|S'
			else
				returnValue= returnValue + '|N'
			window.close();
		}		
	}
}

function dlg_crear_participacion() {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:420px;dialogHeight:200px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_crear_participacion.php", "_blank", args);
 	if (returnVal == null)
 		return false;
	else {	
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
   		return true;
	}
}

function dlg_tipo_op(ve_cod_usuario) {
	//TIPO PARTICIPACION 
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_tipo_orden_pago.php?cod_usuario="+ve_cod_usuario.value, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	var lista1	= resp.split('*');
	var cod_tipo_orden_pago = document.getElementById('COD_TIPO_ORDEN_PAGO_0');
	cod_tipo_orden_pago.length = 0;	
	cod_tipo_orden_pago.value = '';	
	var selOpcion = new Option('', 0); //crea un objeto option
	eval(document.getElementById('COD_TIPO_ORDEN_PAGO_0').options[0]=selOpcion); //le asigna un valor inical de 0 y vacio al objeto
	
	var j = 1; //crear y manejar indices de options
	for(var i=0;i<lista1.length-1;i++){
    	var lista2 = lista1[i].split('|');
    	var selOpcion = new Option(lista2[1], lista2[0]);//creacion de options con ajax
		eval(document.getElementById('COD_TIPO_ORDEN_PAGO_0').options[j]=selOpcion);//asiganacion de valores
		j++;  
    }
    // Agrega OPCION todos
    var selOpcion = new Option('Todos', 99);
	eval(document.getElementById('COD_TIPO_ORDEN_PAGO_0').options[j]=selOpcion);//asiganacion de valores
    
    // MUESTRA O no EL CHECK BOX DE SUELDO
    document.getElementById('ES_SUELDO_0').checked = false;
    var vl_recibe_sueldo = document.getElementById('RECIBE_SUELDO');
    if (ve_cod_usuario.options[ve_cod_usuario.selectedIndex].dataset.dropdown=='S') 
    	vl_recibe_sueldo.style.display = '';
    else
    	vl_recibe_sueldo.style.display = 'none';
}
function dlg_centro_costo(ve_cod_tipo_op){
    //CENTRO COSTO
    var cod_usuario = document.getElementById('COD_USUARIO_0').value;
    var cod_tipo_op = ve_cod_tipo_op.value;
    var ajax = nuevoAjax();
    ajax.open("GET", "ajax_centro_costo.php?cod_usuario="+cod_usuario+"&cod_tipo_op="+cod_tipo_op, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	var lista1	= resp.split('*');
	var cod_centro_costo = document.getElementById('COD_CC_0');
	cod_centro_costo.length = 0;
	cod_centro_costo.value = '';
	var selOpcion = new Option('Todos', 0); //crea un objeto option
	eval(document.getElementById('COD_CC_0').options[0]=selOpcion); //le asigna un valor inical de 0 y vacio al objeto
	var j = 1; //crear y manejar indices de options
	for(var i=0;i<lista1.length-1;i++){
    	var lista2 = lista1[i].split('|');
    	var selOpcion = new Option(lista2[1], lista2[0]);//creacion de options con ajax
		eval(document.getElementById('COD_CC_0').options[j]=selOpcion);//asiganacion de valores
		j++;  
    }
}
function muestra_lista_op(ve_visible){
/*
esta funcion realiza dos acciones:
1.- dado el boton "Dejar Selección" solo despliega las OP seleccionadas
2.- dado el boton "Volver a todo el listado" despliega todas las OP.
*/
	var aTR = get_TR('PARTICIPACION_ORDEN_PAGO');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var seleccion = document.getElementById('SELECCION_' + rec_tr).checked;
		if(seleccion == false){
			var tr_anula = document.getElementById('PARTICIPACION_ORDEN_PAGO_' + rec_tr);
			tr_anula.style.display = ve_visible;
		}
	}//fin for	
}
 function mostrarOcultar_Anula() {
	var tr_anula = document.getElementById('tr_anula');
	var cod_estado_participacion = get_value('COD_ESTADO_PARTICIPACION_0'); 
	
	if (to_num(cod_estado_participacion)== 3) {
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
	return true;
}
function asignacion_monto(ve_seleccion) {	
	var seleccion = ve_seleccion.checked;
	var record = get_num_rec_field(ve_seleccion.id);
	var sum_total_neto = to_num(document.getElementById('SUM_TOTAL_NETO_POP_C_H_0').value);
	var total_neto = to_num(document.getElementById('TOTAL_NETO_POP_C_H_' + record).value);
	var vl_tipo_documento = document.getElementById('TIPO_DOCUMENTO_H_0').value;
	if (seleccion == true) {
		var new_sum_total_neto = parseInt(sum_total_neto) + parseInt(total_neto);
		if (vl_tipo_documento=='SUELDO') {
			var vl_monto_sueldo = document.getElementById('TOTAL_NETO_H_0').value;
			if (vl_monto_sueldo==0 || vl_monto_sueldo=='') {
				alert('Debe ingresar el monto del sueldo antes de continuar.');
				ve_seleccion.checked = false;
				document.getElementById('TOTAL_NETO_H_0').focus();
				return;
			}
			var vl_old_suma = document.getElementById('SUM_TOTAL_NETO_POP_C_H_0').value;
			if (parseInt(vl_old_suma) >= parseInt(vl_monto_sueldo)) {
				alert('Ya supero el monto ingresado como sueldo.');
				ve_seleccion.checked = false;
				return;
			}
		}
	}
	else
		var new_sum_total_neto = parseInt(sum_total_neto) - parseInt(total_neto);
		
	//asigna total neto
	document.getElementById('SUM_TOTAL_NETO_POP_C_0').innerHTML = number_format(new_sum_total_neto, 0, ',', '.');
	document.getElementById('SUM_TOTAL_NETO_POP_C_H_0').value = new_sum_total_neto;
	
	if (vl_tipo_documento!='SUELDO') {
		document.getElementById('TOTAL_NETO_0').innerHTML = number_format(new_sum_total_neto, 0, ',', '.');
		document.getElementById('TOTAL_NETO_H_0').value = new_sum_total_neto;
		
		porc_iva_label = document.getElementById('PORC_IVA_0').innerHTML;
		porc_iva_label = findAndReplace(porc_iva_label, ',', '.');
		
		//calcula total iva
		var monto_iva = roundNumber(parseInt(new_sum_total_neto) * parseFloat(porc_iva_label) / 100, 0);
		document.getElementById('MONTO_IVA_0').innerHTML = number_format(monto_iva, 0, ',', '.');
		document.getElementById('MONTO_IVA_H_0').value = monto_iva;	
	}
	
	//calcula total con iva
	if(vl_tipo_documento == 'BH')			
		var total_con_iva = parseInt(new_sum_total_neto) - parseInt(monto_iva);
	else if(vl_tipo_documento == 'FA')			
		var total_con_iva = parseInt(new_sum_total_neto) + parseInt(monto_iva);
	else if(vl_tipo_documento == 'SUELDO')			
		var total_con_iva = parseInt(new_sum_total_neto);
	
	document.getElementById('TOTAL_CON_IVA_0').innerHTML = number_format(total_con_iva, 0, ',', '.');
	document.getElementById('TOTAL_CON_IVA_H_0').value = total_con_iva;	
}
function change_monto_sueldo(ve_monto_sueldo) {
	var vl_monto = ve_monto_sueldo.value;
	var aTR = get_TR('PARTICIPACION_ORDEN_PAGO');
	
	// desmarca todo
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var vl_seleccion = document.getElementById('SELECCION_' + rec_tr);
		if (vl_seleccion.checked) {
			vl_seleccion.checked = false;
			asignacion_monto(vl_seleccion);
		}
	}//fin for	
	
	// Marca hasta el monto
	var vl_suma = 0;
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var vl_seleccion = document.getElementById('SELECCION_' + rec_tr);
		if (parseInt(vl_suma) < parseInt(vl_monto)) {
			vl_seleccion.checked = true;
			asignacion_monto(vl_seleccion);
			var vl_monto_OP = to_num(document.getElementById('TOTAL_NETO_POP_C_H_' + rec_tr).value);
			vl_suma = vl_suma + parseInt(vl_monto_OP);
		}
	}//fin for	
}