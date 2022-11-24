function validate() {
	
	var monto_documento =document.getElementById('MONTO_DOCUMENTO_H_0');
	if(monto_documento){
		if (to_num(monto_documento.value) == 0){
			alert('¡el Monto del Documento debe ser distinto de cero!');
			return false;
		}
	}
		
	var cod_estado_pago_faprov_value = get_value('COD_ESTADO_PAGO_FAPROV_0'); 
	if (to_num(cod_estado_pago_faprov_value) == 3){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el Motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}	
	return true;
}

function load_lista_fa() {
	document.getElementById('loader').style.display='';
	var tabla = document.getElementById('PAGO_FAPROV_FAPROV');
	
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}

	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
	var vl_cod_cuenta_corriente = document.getElementById('COD_CUENTA_CORRIENTE_0').value;
	var vl_clic = document.getElementById('CLIC_BOTON').value;
	if (vl_clic=='S')
		vl_cod_cuenta_corriente = 0;	// trae todas la fa del rut		
	else if (vl_cod_cuenta_corriente=='')
		vl_cod_cuenta_corriente = -1;		// no ha seleccionado ninguna cta cte
		
   	ajax = nuevoAjax();
	ajax.open("GET", "load_lista_fa.php?cod_empresa="+cod_empresa +"&cod_cuenta_corriente="+vl_cod_cuenta_corriente,false);
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
	document.getElementById('loader').style.display='none';
}

function select_1_empresa(valores, record) {
/* Se reimplementa para agregar codigo adicional */
	set_values_empresa(valores, record);
	
	var nom_emp = document.getElementById('NOM_EMPRESA_0').value;
	document.getElementById('PAGUESE_A_0').value = nom_emp;
	
	load_lista_fa();
}


function ver_otras_fa() {
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	for(i=0 ; i < aTR.length ; i++){
		document.getElementById('PAGO_FAPROV_FAPROV_' + i).style.display='';
	}
	document.getElementById('BTN_VER_FA').style.display='none';
	document.getElementById('BTN_OCULTAR_FA').style.display='';
	
	
}

function ocultar_otras_fa(){
	var vl_cod_cuenta_corriente = document.getElementById('COD_CUENTA_CORRIENTE_HI_0').value;
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	var suma = 0;
	var vl_suma_nc = 0;
	for(i=0 ; i < aTR.length ; i++){
		var vl_cod_cta_corriente_h = document.getElementById('COD_CUENTA_CORRIENTE_H_'+ i).value;
		if(vl_cod_cuenta_corriente != vl_cod_cta_corriente_h){	
			var vl_seleccion = document.getElementById('SELECCION_' + i);
			
			document.getElementById('SELECCION_' + i).checked = false;
			document.getElementById('PAGO_FAPROV_FAPROV_' + i).style.display='none';
			set_monto_asignado(i, 0);
			document.getElementById('MONTO_NCPROV_' + i).innerHTML = 0;
			document.getElementById('MONTO_NCPROV_H_' + i).value = 0;
			
			
			var asignado = document.getElementById('MONTO_ASIGNADO_' + i).value;
			var vl_monto_asig_nc = document.getElementById('MONTO_NCPROV_H_' + i).value;
			suma = parseInt(suma) + parseInt(asignado);
			vl_suma_nc = parseInt(vl_suma_nc) + parseInt(vl_monto_asig_nc);
		}	
	}

	document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(suma, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_S_0').innerHTML = number_format(suma, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_H_0').value = suma;
	
	document.getElementById('TOTAL_MONTO_NC_H_0').value = vl_suma_nc;
	document.getElementById('TOTAL_MONTO_NC_0').innerHTML = number_format(vl_suma_nc, 0, ',', '.');
	
	document.getElementById('BTN_VER_FA').style.display='';
	document.getElementById('BTN_OCULTAR_FA').style.display='none';
}

function set_monto_asignado(ve_record, ve_monto_por_asignar) {
	set_value('MONTO_ASIGNADO_' + ve_record, ve_monto_por_asignar, ve_monto_por_asignar);
}

function asignacion_monto(ve_seleccion){
	var vl_campo_id = ve_seleccion.id;
	var vl_record = get_num_rec_field(vl_campo_id);
	
	if(ve_seleccion.checked == true)
		document.getElementById('MONTO_ASIGNADO_' + vl_record).readOnly = false;
	else
		document.getElementById('MONTO_ASIGNADO_' + vl_record).readOnly = true;	
	
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	var vl_suma_doc = 0;
	var vl_suma_nc = 0;
	var vl_monto_nc_tot = document.getElementById('MONTO_NC_H_0').value;
	
	if(vl_monto_nc_tot == '')
		vl_monto_nc_tot = 0;
	vl_monto_nc_tot = parseInt(vl_monto_nc_tot);
	
	for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		vl_saldo_sin_pago = parseInt(document.getElementById('SALDO_SIN_PAGO_FAPROV_H_' + rec_tr).value);
		if (document.getElementById('SELECCION_' + rec_tr).checked == true){
			if (vl_monto_nc_tot >= vl_saldo_sin_pago){
				vl_monto_asig_nc = vl_saldo_sin_pago
			}else{
				vl_monto_asig_nc = vl_monto_nc_tot;
			}
			document.getElementById('MONTO_NCPROV_' + rec_tr).innerHTML = number_format(vl_monto_asig_nc, 0, ',', '.');
			document.getElementById('MONTO_NCPROV_H_' + rec_tr).value = vl_monto_asig_nc;
			vl_monto_nc_tot = parseInt(vl_monto_nc_tot) - parseInt(vl_monto_asig_nc);
			vl_suma_nc = parseInt(vl_suma_nc) + parseInt(vl_monto_asig_nc);
				
			vl_monto_doc = parseInt(vl_saldo_sin_pago) - parseInt(vl_monto_asig_nc);
			set_monto_asignado(rec_tr, vl_monto_doc);

			vl_suma_doc = parseInt(vl_suma_doc) + parseInt(vl_monto_doc);
			
		}else{
			document.getElementById('MONTO_NCPROV_' + rec_tr).innerHTML = 0;
			document.getElementById('MONTO_NCPROV_H_' + rec_tr).value = 0;
			set_monto_asignado(rec_tr, 0);//si la seleccion es false setea el valor en cero
		}
	}//fin for
	document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(vl_suma_doc, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_S_0').innerHTML = number_format(vl_suma_doc, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_H_0').value = vl_suma_doc;
	
	document.getElementById('TOTAL_MONTO_NC_H_0').value = vl_suma_nc;
	document.getElementById('TOTAL_MONTO_NC_0').innerHTML = number_format(vl_suma_nc, 0, ',', '.');
	
}

function valida_asignacion(ve_record){
//************* debe considera el monto_nc
/*
esta funcion valida que al ingresar monto asignado, primero debe estar seleccionado
*/
	var seleccion = document.getElementById('SELECCION_' + ve_record).checked;
	var suma = 0;
	
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	var suma= 0;
	for (i=0; i<aTR.length; i++){ 
		var rec_tr =get_num_rec_field(aTR[i].id);
		
		var por_pagar = document.getElementById('SALDO_SIN_PAGO_FAPROV_H_' + rec_tr).value;		
		var asignado = document.getElementById('MONTO_ASIGNADO_' + rec_tr).value;
		var monto_nc_prov = document.getElementById('MONTO_NCPROV_H_' + rec_tr).value;
		
		var vl_result = parseInt(monto_nc_prov) + parseInt(asignado);
		
		if(parseFloat(asignado) > parseFloat(por_pagar)){
			alert('El "Monto Pagado" no puede ser mayor que el valor "por Pagar"');
			set_monto_asignado(rec_tr, 0);
			var suma = 0;
			for (j=0; j<aTR.length; j++){
				var rec_tr =get_num_rec_field(aTR[j].id);	
				var asignado = document.getElementById('MONTO_ASIGNADO_' + rec_tr).value;
				suma = parseInt(suma) + parseInt(asignado);	
			}
			document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(suma, 0, ',', '.');
			document.getElementById('MONTO_DOCUMENTO_S_0').innerHTML = number_format(suma, 0, ',', '.');
			document.getElementById('MONTO_DOCUMENTO_H_0').value = suma;
			return;
		}
		
		if(parseInt(por_pagar) < parseInt(vl_result)){
			alert('El "Monto Pagado" no puede ser mayor que el valor "por Pagar"');
			set_monto_asignado(rec_tr, 0);
			var suma = 0;
			for (j=0; j<aTR.length; j++){
				var rec_tr =get_num_rec_field(aTR[j].id);	
				var asignado = document.getElementById('MONTO_ASIGNADO_' + rec_tr).value;
				suma = parseInt(suma) + parseInt(asignado);	
			}
			document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(suma, 0, ',', '.');
			document.getElementById('MONTO_DOCUMENTO_S_0').innerHTML = number_format(suma, 0, ',', '.');
			document.getElementById('MONTO_DOCUMENTO_H_0').value = suma;
			return;
		}
		suma = parseInt(suma) + parseInt(asignado);

	}
	document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(suma, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_S_0').innerHTML = number_format(suma, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_H_0').value = suma;
}
function copia_suma_a_monto_doc() {
	var vl_suma = document.getElementById('SUM_MONTO_ASIGNADO_C_H_0').value;
	document.getElementById('MONTO_DOCUMENTO_0').innerHTML = number_format(vl_suma, 0, ',', '.');
	document.getElementById('MONTO_DOCUMENTO_H_0').value = vl_suma;
}

function muestra_lista_fa(ve_visible){
/*
esta funcion realiza dos acciones:
1.- dado el boton "Dejar Selección" solo despliega las OC seleccionadas
2.- dado el boton "Volver a todo el listado" despliega todas las OC.
*/
	var aTR = get_TR('PAGO_FAPROV_FAPROV');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var seleccion = document.getElementById('SELECCION_' + rec_tr).checked;
		
		if(seleccion == false){
			var tr_anula = document.getElementById('PAGO_FAPROV_FAPROV_' + rec_tr);
			tr_anula.style.display = ve_visible;
		}
	}//fin for	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula() {
	var tr_anula = document.getElementById('tr_anula');
	var cod_estado_pago_faprov = get_value('COD_ESTADO_PAGO_FAPROV_0'); 
	
	if (to_num(cod_estado_pago_faprov)== 3) {
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

 //funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_tipo_doc() {
	var td_mostrar_ocultar = document.getElementById('td_mostrar_ocultar');
	var td_mostrar_ocultar1 = document.getElementById('td_mostrar_ocultar1');
	
	var cod_tipo_pago_faprov = get_value('COD_TIPO_PAGO_FAPROV_0'); 
	
	if (to_num(cod_tipo_pago_faprov)== 1) {
		td_mostrar_ocultar.style.display = ''; 
		td_mostrar_ocultar1.style.display = '';
	}
	else{
		td_mostrar_ocultar.style.display = 'none'; 
		td_mostrar_ocultar1.style.display = 'none'; 
	}
}
function dlg_usar_nc(){
	var vl_cod_pago_faprov = get_value('COD_PAGO_FAPROV_H_0');
	var vl_cod_empresa = get_value('COD_EMPRESA_H_0');
	var vl_cod_nc_prov = get_value('COD_NCPROV_S_0');
	if(vl_cod_pago_faprov == '')
		vl_cod_pago_faprov = 0;
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:620px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("dlg_usar_nc.php?cod_pago_faprov="+vl_cod_pago_faprov+"&cod_empresa="+vl_cod_empresa+"&cod_nc_prov="+vl_cod_nc_prov, "_blank", args);
	if(returnVal != null){
		var returnVal2 = returnVal.substring(0, returnVal.length-1);
		var vl_array = returnVal2.split("|");
		
		document.getElementById('MONTO_NC_0').innerHTML = number_format(vl_array[0], 0, ',', '.');
		document.getElementById('MONTO_NC_H_0').value = vl_array[0];
		
		document.getElementById('COD_NCPROV_S_0').value = vl_array[1];
		
	}	
}

function dlg_empresa_cta_corriente(){
	var args = "location:no;dialogLeft:400px;dialogTop:200px;dialogWidth:620px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	returnVal = window.showModalDialog("dlg_empresa_cta_corriente.php", "_blank", args);
	if (returnVal == null)
 		return false;
	else{		
		document.getElementById('wo_hidden').value = returnVal;
   		return true;
	}
}