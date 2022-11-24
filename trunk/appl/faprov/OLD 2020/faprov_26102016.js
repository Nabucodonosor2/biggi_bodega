function validate() {
	var cod_cuenta_compra = document.getElementById('COD_CUENTA_COMPRA_0').selectedIndex;
	if (cod_cuenta_compra == 0){
		alert('Debe ingresar un Proyecto compra antes de guardar');
		cod_cuenta_compra.focus();
		return false;
		}
	var total_con_iva =document.getElementById('TOTAL_CON_IVA_H_0');
	if(total_con_iva)
		/*valida que el Total con iva no sea cero en ningun caso*/
		if (to_num(total_con_iva.value) == 0){
			alert('¡El Total c/Iva debe ser distinto de cero!');
			return false;
		}
	
	var cod_estado_faprov_value = get_value('COD_ESTADO_FAPROV_0'); 
	if (to_num(cod_estado_faprov_value) == 5){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el Motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	if(total_con_iva){
		var aTR = get_TR('ITEM_FAPROV');
		var suma= 0;
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			suma = suma + parseFloat(document.getElementById('MONTO_ASIGNADO_C_H_' + rec_tr).value);
			
			var por_asignar = document.getElementById('SALDO_SIN_FAPROV_H_' + rec_tr).value;
			var asignado = document.getElementById('MONTO_ASIGNADO_' + rec_tr).value;

			if(parseInt(asignado) > parseInt(por_asignar)){				
				if(!confirm('El "Monto Asignado" no puede ser mayor que el valor "Por Asignar". \n¿Desea de todas maneras registrar la Recepcion?'))
			 		return false;
			}
		}	
		if	(suma < total_con_iva.value){
			alert('La suma del "Monto Asignado" es menor que el "Total c/ IVA"');
			return false;
		}
	}

	return true;
}


function calcula_totales(){
	var cod_tipo_faprov = COD_TIPO_FAPROV_0.options[COD_TIPO_FAPROV_0.selectedIndex].value
	var total_con_iva =parseInt(document.getElementById('TOTAL_CON_IVA_H_0').value);
	if(to_num(cod_tipo_faprov) == 1 | to_num(cod_tipo_faprov) == 4){//tipo factura normal ó electronica
		var porc_iva =parseFloat(document.getElementById('PORC_IVA_H_0').value);
		var iva =(1+(porc_iva /100));
		var total_neto = roundNumber(total_con_iva / iva, 0);
		var monto_iva = roundNumber(total_con_iva - total_neto, 0);
	
		document.getElementById('TOTAL_NETO_0').innerHTML = number_format(total_neto, 0, ',', '.');
		document.getElementById('TOTAL_NETO_H_0').value = total_neto;
	
		document.getElementById('MONTO_IVA_0').innerHTML = number_format(monto_iva, 0, ',', '.');
		document.getElementById('MONTO_IVA_H_0').value = monto_iva;
		
		document.getElementById('LABEL_BRUTO_NETO_0').innerHTML = 'Monto Neto';
		document.getElementById('LABEL_RETENCION_IVA_0').innerHTML = 'Monto IVA';
		document.getElementById('LABEL_TOTAL_0').innerHTML = 'Total c/IVA';
		
		document.getElementById('LABEL_BRUTO_NETO1_0').innerHTML = 'Monto Neto';
		document.getElementById('LABEL_RETENCION_IVA1_0').innerHTML = 'Monto IVA';
		document.getElementById('LABEL_TOTAL1_0').innerHTML = 'Total c/IVA';
	}
	else if(to_num(cod_tipo_faprov) == 2){// tipo factura exenta
	
		document.getElementById('TOTAL_NETO_0').innerHTML = number_format(total_con_iva, 0, ',', '.');
		document.getElementById('TOTAL_NETO_H_0').value = total_con_iva;
	
		document.getElementById('MONTO_IVA_0').innerHTML = 0;
		document.getElementById('MONTO_IVA_H_0').value = 0;
		
		document.getElementById('LABEL_BRUTO_NETO_0').innerHTML = 'Monto Neto';
		document.getElementById('LABEL_RETENCION_IVA_0').innerHTML = 'Monto IVA';
		document.getElementById('LABEL_TOTAL_0').innerHTML = 'Total c/IVA';
		
		document.getElementById('LABEL_BRUTO_NETO1_0').innerHTML = 'Monto Neto';
		document.getElementById('LABEL_RETENCION_IVA1_0').innerHTML = 'Monto IVA';
		document.getElementById('LABEL_TOTAL1_0').innerHTML = 'Total c/IVA';
	}
	else if(to_num(cod_tipo_faprov) == 3){// tipo boleta honorarios
		var ret_bh = parseFloat(document.getElementById('RETENCION_BH_H_0').value);
		var total_neto = roundNumber((total_con_iva * 100) / (100 - ret_bh), 0);
		var monto_iva = roundNumber(total_neto - total_con_iva, 0);
		document.getElementById('TOTAL_NETO_0').innerHTML = number_format(total_neto, 0, ',', '.');;
		document.getElementById('TOTAL_NETO_H_0').value = total_neto;
		document.getElementById('MONTO_IVA_0').innerHTML = number_format(monto_iva, 0, ',', '.');
		document.getElementById('MONTO_IVA_H_0').value = monto_iva;
		document.getElementById('LABEL_BRUTO_NETO_0').innerHTML = 'Monto Bruto';
		document.getElementById('LABEL_RETENCION_IVA_0').innerHTML = 'Monto Retención';
		document.getElementById('LABEL_TOTAL_0').innerHTML = 'Total Líquido';
		document.getElementById('LABEL_BRUTO_NETO1_0').innerHTML = 'Monto Bruto';
		document.getElementById('LABEL_RETENCION_IVA1_0').innerHTML = 'Monto Retención';
		document.getElementById('LABEL_TOTAL1_0').innerHTML = 'Total Líquido';
	}
}

function select_1_empresa(valores, record) {
/* Se reimplementa para agregar codigo adicional */
	 set_values_empresa(valores, record);

	var tabla = document.getElementById('ITEM_FAPROV');
	
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}

	var cod_empresa = document.getElementById('COD_EMPRESA_0').value
   	ajax = nuevoAjax();
	ajax.open("GET", "load_lista_item_faprov.php?cod_empresa="+cod_empresa ,false);
    ajax.send(null);    
    var resp = ajax.responseText; 
    
	// Copia los TR a la tabla correspondiente, 
	// este codigo se copio desde general.js -> add_line
	var table_aux = document.createElement("TABLE"); 
	table_aux.innerHTML = resp;
 	var children = table_aux.childNodes;
	for (var i=0; i < children.length; i++) {
		if (children[i].nodeName=='TBODY') {
		  	var children2 = children[i].childNodes;
		  	for (j=0; j < children2.length; j++) {
				if (children2[j].nodeName=='TR') {
					var tr_contenido = children2[j].innerHTML;
					
					var tbody = null; 
					var child_tabla = tabla.childNodes;
					for (k=0; k < child_tabla.length; k++)
						if (child_tabla[k].nodeName=='TBODY') {
							tbody = child_tabla[k];
							break;
						}
					if (! tbody) {
						tbody = document.createElement("TBODY"); 
						tabla.appendChild(tbody);
					}		
					tbody.appendChild(children2[j]);
				}
			}
		}
	}
}

function set_monto_asignado(ve_record, ve_monto_por_asignar) {
	set_value('MONTO_ASIGNADO_' + ve_record, ve_monto_por_asignar, ve_monto_por_asignar);
}

function asignacion_monto(ve_seleccion) {
	var aTR = get_TR('ITEM_FAPROV');
	var suma = 0;
	
	for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + rec_tr).checked == true){
			suma = suma + parseFloat(document.getElementById('SALDO_SIN_FAPROV_H_' + rec_tr).value);
			set_monto_asignado(rec_tr, parseFloat(document.getElementById('SALDO_SIN_FAPROV_H_' + rec_tr).value));
		}
		else
			set_monto_asignado(rec_tr, 0);//si la seleccion es false setea el valor en cero
	}//fin for				
	computed(get_num_rec_field(ve_seleccion.id), 'MONTO_ASIGNADO_C');	
}

function valida_asignacion(ve_record){
/*
esta funcion valida que al ingresar monto asignado, primero debe estar seleccionado
*/
	var seleccion = document.getElementById('SELECCION_' + ve_record).checked;

	if(seleccion == false){
		alert ('Debe estar seleccionado para que pueda asignar montos');
		set_monto_asignado(ve_record, 0);
	}
}

function copia_suma_a_total() {
	var vl_suma = document.getElementById('SUM_MONTO_ASIGNADO_C_H_0').value;
	document.getElementById('TOTAL_CON_IVA_0').innerHTML = number_format(vl_suma, 0, ',', '.');
	document.getElementById('TOTAL_CON_IVA_H_0').value = vl_suma;
	calcula_totales();
}

function muestra_lista(ve_visible){
/*
esta funcion realiza dos acciones:
1.- dado el boton "Dejar Selección" solo despliega lo seleccionado
2.- dado el boton "Volver a todo el listado" despliega todo
*/
	var aTR = get_TR('ITEM_FAPROV');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var seleccion = document.getElementById('SELECCION_' + rec_tr).checked;
		if(seleccion == false){
			var tr_anula = document.getElementById('ITEM_FAPROV_' + rec_tr);
			tr_anula.style.display = ve_visible;
		}
	}//fin for	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula() {
	var tr_anula = document.getElementById('tr_anula');
	var cod_estado_faprov = get_value('COD_ESTADO_FAPROV_0');
	if (to_num(cod_estado_faprov)== 5) {
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

// OUTPUT
function request_tipo_faprov(ve_prompt, ve_valor) 
{	
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:350px;dialogHeight:180px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../../../trunk/appl/faprov/request_tipo_faprov.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
	
	if (returnVal == null)		
		return false;		
	else 
	{
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
	   	return true;	
	}
}
// INPUT
function request_tipo_faprov2(ve_prompt, ve_valor) 
{	
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:350px;dialogHeight:180px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../../../trunk/appl/faprov/request_tipo_faprov.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
	
	if (returnVal == null)		
		return false;		
	else 
	{
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
		return true;	
	}
}
function request_cuenta() {
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:380px;dialogHeight:180px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("request_cuenta.php", "_blank", args);
	
	if (returnVal == null)		
		return false;		
	else 
	{
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
	   	return true;	
	}
}
function marcar_todo() {
	var aTR = get_TR('wo_registro');
	for (var i=0; i < aTR.length; i++)	{
		document.getElementById('SELECCION_' + i).checked = true;
	}
}
function desmarcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR('wo_registro');
	for (var i=0; i < aTR.length; i++)	{
		document.getElementById('SELECCION_' + i).checked = false;
	}
}
function confirm_cambio(){
	var vl_confirm = confirm("¿Está seguro que deséa cambiar el estado de estas facturas proveedor de Ingresada a Aprobada?");
	if(vl_confirm)
		return true;
	else
		return false;	
}