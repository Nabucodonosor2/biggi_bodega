function validate() {
	var aTR = get_TR('DOC_INGRESO_PAGO');
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 documento de pago antes de grabar.');
		return false;
	}

	var cod_estado_ingreso_pago = get_value('COD_ESTADO_INGRESO_PAGO_0'); 
	if (to_num(cod_estado_ingreso_pago) == 3){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el Motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	var tipo_doc_pago_efectivo = 1;
	var tipo_doc_pago_anticipo = 9;
	var tipo_doc_pago_nc = 7;
	var tipo_doc_pago_tarjeta_credito = 6;
	var tipo_doc_deposito_cta_cte = 4;
	var tipo_doc_transferencia_bancaria = 10;
	var tipo_doc_factura_compra = 11;
	for (var i = 0; i < aTR.length; i++) {
	
		var record = get_num_rec_field(aTR[i].id);
		var cod_tipo_doc_pago = document.getElementById('COD_TIPO_DOC_PAGO_'+ record).value;
		var fecha_doc = document.getElementById('FECHA_DOC_'+ record).value;
		var nro_doc = document.getElementById('NRO_DOC_'+ record).value;
		var cod_banco = document.getElementById('COD_BANCO_'+ record).value;
		var monto_doc = document.getElementById('MONTO_DOC_'+ record).value;		
		if (cod_tipo_doc_pago == ''){
			alert ("Debe ingresar el Tipo de Documento.");
			document.getElementById('COD_TIPO_DOC_PAGO_'+ record).focus();
			return false;
		}
		else if(fecha_doc == ''){
			alert ("Debe ingresar la Fecha de pago.");
			document.getElementById('FECHA_DOC_'+ record).focus();
			return false;
		}
		else if (monto_doc == ''){
			alert ("Debe ingresar el Monto del Documento.");
			document.getElementById('MONTO_DOC_'+ record).focus();
			return false;			
		}
		else if (cod_banco == '' && cod_tipo_doc_pago == tipo_doc_deposito_cta_cte){
			alert ("Debe ingresar el Banco.");
			document.getElementById('COD_BANCO_' + record).focus();
			return false;		
		}
		else if (cod_banco == '' && cod_tipo_doc_pago == tipo_doc_transferencia_bancaria){
			alert ("Debe ingresar el Banco.");
			document.getElementById('COD_BANCO_' + record).focus();
			return false;			
		}
		else if (cod_tipo_doc_pago != tipo_doc_pago_efectivo 
			  && cod_tipo_doc_pago != tipo_doc_pago_anticipo  
			  && cod_tipo_doc_pago != tipo_doc_pago_nc 
			  && cod_tipo_doc_pago != tipo_doc_factura_compra 
			  && cod_tipo_doc_pago != tipo_doc_deposito_cta_cte 
			  && cod_tipo_doc_pago != tipo_doc_transferencia_bancaria){
			if (nro_doc == 0){
				alert ("Debe ingresar el Número de Documento.");
				document.getElementById('NRO_DOC_' + record).focus();
				return false;			
			}
			else if (cod_banco == '' && cod_tipo_doc_pago != tipo_doc_pago_tarjeta_credito){
				alert ("Debe ingresar el Banco.");
				document.getElementById('COD_BANCO_' + record).focus();
				return false;			
			}
		}	
	}// end for  
	
	var sum_monto_doc = to_num(document.getElementById('SUM_MONTO_DOC_C_H_0').value);
	var monto_asignado_fa = parseInt(document.getElementById('SUM_MONTO_ASIGNADO_C_H_0').value);
	var monto_asignado_nv = parseInt(document.getElementById('SUM_MONTO_ASIGNADO_C_NV_H_0').value);
	var sum_monto_asignado = monto_asignado_fa + monto_asignado_nv;
	var otro_ingreso = to_num(document.getElementById('OTRO_INGRESO_0').value);
	var otro_gasto = to_num(document.getElementById('OTRO_GASTO_0').value);
	var otro_anticipo = to_num(document.getElementById('OTRO_ANTICIPO_0').value);
	var sum_ingreso = parseInt(sum_monto_doc) + parseInt(otro_gasto);
	var sum_gasto   = parseInt(sum_monto_asignado) + parseInt(otro_ingreso)+ parseInt(otro_anticipo);
	if (sum_ingreso != sum_gasto){
		alert('¡No se puede registrar el ingreso de pago!. El "Total de Documentos" es distinto al "Total Asignado".\n \n - Total de Documentos: $'+number_format(sum_ingreso, 0, ',', '.')+'\n - Total Asignado: $'+number_format(sum_gasto, 0, ',', '.'));
		return false;
	}
	return true;
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
function mostrarOcultar_Anula() { 
	var tr_anula = document.getElementById('tr_anula');
	var cod_estado_ingreso_pago = get_value('COD_ESTADO_INGRESO_PAGO_0'); 

		if (to_num(cod_estado_ingreso_pago)== 3) {
			tr_anula.style.display = ''; 
			
			document.getElementById('MOTIVO_ANULA_0').type='text';
			document.getElementById('MOTIVO_ANULA_0').setAttribute('onblur', "this.style.border=''");				
			document.getElementById('MOTIVO_ANULA_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");	
			document.getElementById('MOTIVO_ANULA_0').focus();
		}else{
			document.getElementById('MOTIVO_ANULA_0').value = '';
			tr_anula.style.display = 'none'; 
		}
		return true;
}

function select_1_empresa(valores, record) {
/* Se reimplementa para agregar codigo adicional */
	set_values_empresa(valores, record);
	
	var tabla = document.getElementById('INGRESO_PAGO_FACTURA');
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
   	ajax = nuevoAjax();
	ajax.open("GET", "ajax_load_factura.php?cod_empresa="+cod_empresa,false);
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
	var aTR = get_TR('INGRESO_PAGO_FACTURA');
	var suma_saldo_t = 0;
	var suma_saldo_c = 0;
	for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		suma_saldo_t = suma_saldo_t + parseFloat(document.getElementById('SALDO_T_H_' + rec_tr).value);
		suma_saldo_c = suma_saldo_c + parseFloat(document.getElementById('SALDO_C_H_' + rec_tr).value);
			
	}//fin for	
	set_value('SUM_SALDO_T_0', suma_saldo_t, number_format(suma_saldo_t, 0, ',', '.'));
	set_value('SUM_SALDO_T_H_0', suma_saldo_t, number_format(suma_saldo_t, 0, ',', '.'));
	
	set_value('SUM_SALDO_C_0', suma_saldo_c, number_format(suma_saldo_c, 0, ',', '.'));
	set_value('SUM_SALDO_C_H_0', suma_saldo_c, number_format(suma_saldo_c, 0, ',', '.'));
	
	////////////////////////////////////////////////////////////////////
	var tabla = document.getElementById('INGRESO_PAGO_NOTA_VENTA');
	// borra todos los tr
	while (tabla.firstChild) {
	  tabla.removeChild(tabla.firstChild);
	}
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
   	ajax = nuevoAjax();
	ajax.open("GET", "ajax_load_nv.php?cod_empresa="+cod_empresa,false);
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
	
	var aTR = get_TR('INGRESO_PAGO_NOTA_VENTA');
	var suma_saldo_t = 0;
	var suma_saldo_c = 0;
	for (i=0; i<aTR.length; i++){
		var rec_tr =get_num_rec_field(aTR[i].id);
		suma_saldo_t = suma_saldo_t + parseFloat(document.getElementById('SALDO_T_NV_H_' + rec_tr).value);
		suma_saldo_c = suma_saldo_c + parseFloat(document.getElementById('SALDO_C_NV_H_' + rec_tr).value);
			
	}//fin for	
	set_value('SUM_SALDO_T_NV_0', suma_saldo_t, number_format(suma_saldo_t, 0, ',', '.'));
	set_value('SUM_SALDO_T_NV_H_0', suma_saldo_t, number_format(suma_saldo_t, 0, ',', '.'));
	
	set_value('SUM_SALDO_C_NV_0', suma_saldo_c, number_format(suma_saldo_c, 0, ',', '.'));
	set_value('SUM_SALDO_C_NV_H_0', suma_saldo_c, number_format(suma_saldo_c, 0, ',', '.'));
}

function set_monto_asignado(ve_record, ve_monto_por_asignar, ve_tipo_asignacion) {
	if(ve_tipo_asignacion == 'INGRESO_PAGO_FACTURA'){
		set_value('MONTO_ASIGNADO_' + ve_record, ve_monto_por_asignar, ve_monto_por_asignar);
		if (ve_monto_por_asignar==0)
			 document.getElementById('SELECCION_' + ve_record).checked = false;
			 
		computed(ve_record, 'MONTO_ASIGNADO_C');
		computed(ve_record, 'SALDO_T');
	}	
	else if(ve_tipo_asignacion == 'INGRESO_PAGO_NOTA_VENTA'){
		set_value('MONTO_ASIGNADO_NV_' + ve_record, ve_monto_por_asignar, ve_monto_por_asignar);
		if (ve_monto_por_asignar==0)
			 document.getElementById('SELECCION_NV_' + ve_record).checked = false;
			 
		computed(ve_record, 'MONTO_ASIGNADO_C_NV');
		computed(ve_record, 'SALDO_T_NV');
	}
}

function asignacion_monto(ve_seleccion, ve_tipo_asignacion) {
	var seleccion = ve_seleccion.checked;
	var record = get_num_rec_field(ve_seleccion.id);
	var sum_monto_doc = document.getElementById('SUM_MONTO_DOC_C_H_0').value;
	if(sum_monto_doc > 0){
		var suma = 0;
		// asignacion FA
		var aTR = get_TR('INGRESO_PAGO_FACTURA');
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			suma = suma + parseFloat(document.getElementById('MONTO_ASIGNADO_C_H_' + rec_tr).value);
		}
		// asignacion NV	
		var aTR = get_TR('INGRESO_PAGO_NOTA_VENTA');
		for (i=0; i<aTR.length; i++){
			var rec_tr =get_num_rec_field(aTR[i].id);
			suma = suma + parseFloat(document.getElementById('MONTO_ASIGNADO_C_NV_H_' + rec_tr).value);
		}//fin for	
		if (seleccion == true){
			if(ve_tipo_asignacion == 'INGRESO_PAGO_FACTURA')
				var monto_por_pagar = to_num(get_value('SALDO_' + record));
			else if(ve_tipo_asignacion == 'INGRESO_PAGO_NOTA_VENTA')
				var monto_por_pagar = to_num(get_value('SALDO_NV_' + record));
			
			var monto_asignado = sum_monto_doc - suma;
						
			if (monto_asignado > monto_por_pagar)
				monto_asignado = monto_por_pagar;
			
			set_monto_asignado(record, parseFloat(monto_asignado), ve_tipo_asignacion);
			
			if(suma >= sum_monto_doc){
				set_monto_asignado(record, 0, ve_tipo_asignacion);
				alert('Saldo insuficiente para asignar "Monto Pago"');	
				if(ve_tipo_asignacion == 'INGRESO_PAGO_FACTURA'){
					document.getElementById('SELECCION_' + record).checked = false;
				}
				else if(ve_tipo_asignacion == 'INGRESO_PAGO_NOTA_VENTA'){
					document.getElementById('SELECCION_NV_' + record).checked = false;
				}
				return false;
			}
		}//fin	(seleccion == true)
		else{
			set_monto_asignado(record, 0, ve_tipo_asignacion);//si la seleccion es false setea el valor en cero
		}
	}// fin if(total_iva != 0)
	else{
		if(seleccion == true){
			alert('Primero debe ingresar algún documento de pago');
			if(ve_tipo_asignacion == 'INGRESO_PAGO_FACTURA')
				document.getElementById('SELECCION_' + record).checked = false;
			else if(ve_tipo_asignacion == 'INGRESO_PAGO_NOTA_VENTA')
				document.getElementById('SELECCION_NV_' + record).checked = false;
		}
	}
}

function valida_asignacion(ve_record, ve_tipo_asignacion){
/*esta funcion valida que al ingresar monto asignado, primero debe estar seleccionado*/
	if(ve_tipo_asignacion == 'INGRESO_PAGO_FACTURA'){
		var seleccion = document.getElementById('SELECCION_' + ve_record).checked;
		if(seleccion == false){
			alert ('Debe estar seleccionado para que pueda asignar "Monto Pago"');
			set_monto_asignado(ve_record, 0, ve_tipo_asignacion);
		}
	}
	else if(ve_tipo_asignacion == 'INGRESO_PAGO_NOTA_VENTA'){
		var seleccion = document.getElementById('SELECCION_NV_' + ve_record).checked;
		if(seleccion == false){
			alert ('Debe estar seleccionado para que pueda asignar "Monto Pago"');
			set_monto_asignado(ve_record, 0, ve_tipo_asignacion);
		}
	}
}

function valida_tipo_doc_pago(ve_tipo_doc_pago) {
	var record = get_num_rec_field(ve_tipo_doc_pago.id);
	var nro_doc = document.getElementById('NRO_DOC_'+ record);
	var cod_banco = document.getElementById('COD_BANCO_'+ record);
	var kl_tipo_doc_efectivo = 1;
	var kl_tipo_doc_tarjeta_credito = 6;
	var kl_tipo_doc_deposito_cta_cte = 4;
	var kl_tipo_doc_transferencia_bancaria = 10;

	if (ve_tipo_doc_pago.value == kl_tipo_doc_efectivo){  //tipo de documento = efectivo 
		nro_doc.value = '';
		nro_doc.setAttribute('type', "hidden");
		cod_banco.setAttribute('disabled', "");
		cod_banco.value = '';
	}
	else if (ve_tipo_doc_pago.value == kl_tipo_doc_tarjeta_credito){
		cod_banco.setAttribute('disabled', "");
		cod_banco.value = '';
	}else if (ve_tipo_doc_pago.value == kl_tipo_doc_deposito_cta_cte){
		nro_doc.setAttribute('type', "hidden");
		cod_banco.removeAttribute('disabled');
		cod_banco.value = '';
	}else{
		nro_doc.setAttribute('type', "text");
		cod_banco.removeAttribute('disabled');
	}
}


function ingreso_gasto_abono(ve_campo){
	
	var nom_campo = get_nom_field(ve_campo.id);
	if (nom_campo == "OTRO_INGRESO"){
		document.getElementById('OTRO_GASTO_0').value = 0;
		document.getElementById('OTRO_ANTICIPO_0').value = 0;
	}	
	else if (nom_campo == "OTRO_GASTO"){
		document.getElementById('OTRO_INGRESO_0').value = 0;
		document.getElementById('OTRO_ANTICIPO_0').value = 0;
	}
	else if(nom_campo == "OTRO_ANTICIPO"){	
		document.getElementById('OTRO_INGRESO_0').value = 0;
		document.getElementById('OTRO_GASTO_0').value = 0;
	}	
}

function change_monto_doc(){
	var aTR = get_TR('INGRESO_PAGO_FACTURA');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var	monto_asignado = parseFloat(document.getElementById('MONTO_ASIGNADO_C_H_' + rec_tr).value);
		var	monto_fa = parseFloat(document.getElementById('MONTO_ASIGNADO_' + rec_tr).value);
		
		if(monto_asignado > 0){
			set_monto_asignado(rec_tr, monto_fa, 'INGRESO_PAGO_FACTURA');
			document.getElementById('SELECCION_' + rec_tr).checked = true;
		}	
	}//fin for

	var aTR = get_TR('INGRESO_PAGO_NOTA_VENTA');
	for (i=0; i<aTR.length; i++){
		var rec_tr = get_num_rec_field(aTR[i].id);
		var	monto_asignado = parseFloat(document.getElementById('MONTO_ASIGNADO_C_NV_H_' + rec_tr).value);
		var	monto_nv = parseFloat(document.getElementById('MONTO_ASIGNADO_NV_' + rec_tr).value);

		if(monto_asignado > 0){
			set_monto_asignado(rec_tr, monto_nv, 'INGRESO_PAGO_NOTA_VENTA');
			document.getElementById('SELECCION_NV_' + rec_tr).checked = true;
		}	
	}//fin for
}

function usar_anticipo() {
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value; 
	
	if(cod_empresa == ''){
		alert('Sr. Usuario debe ingresar primero una empresa, así podrá ver si tiene anticipos asociados');
		return false;
	}
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:440px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("usar_anticipo.php?cod_empresa="+cod_empresa, "_blank", args);
	if (returnVal == null)
 		return false;
	else {
		var dato = returnVal.split("|"); 		//78|12-01-01|100000|79|12-01-01|100000
		
		var sum_monto_doc = document.getElementById('SUM_MONTO_DOC_C_H_0').value;
		var kl_tipo_doc_anticipo = 9;
		for (i=0; i < (dato.length - 1); i=i+3){
			cod_ingreso_pago = dato[i];
			fecha			 = dato[i+1];
			otro_anticipo	 = dato[i+2];

			var row = add_line('DOC_INGRESO_PAGO', 'ingreso_pago');
			document.getElementById('NRO_DOC_' + row).value = cod_ingreso_pago;
			document.getElementById('FECHA_DOC_' + row).value = fecha;
			document.getElementById('MONTO_DOC_' + row).value = otro_anticipo;
			document.getElementById('MONTO_DOC_C_H_' + row).value = otro_anticipo;
			document.getElementById('COD_TIPO_DOC_PAGO_' + row).value = kl_tipo_doc_anticipo; //ME RESCATA EL COD_TIPO_DOC_PAGO = "ANTICIPO"
			document.getElementById('COD_TIPO_DOC_PAGO_H_' + row).value = kl_tipo_doc_anticipo; //ME RESCATA EL COD_TIPO_DOC_PAGO = "ANTICIPO"
			
			document.getElementById('NRO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('FECHA_DOC_'+ row).setAttribute('readonly', "");
			document.getElementById('MONTO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('COD_BANCO_'+ row).setAttribute('disabled', "");
			
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).setAttribute('disabled', "");
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).style.color = '#000';
			
			sum_monto_doc = parseInt(sum_monto_doc) + parseInt(document.getElementById('MONTO_DOC_C_H_' + row).value);
			
		}
		document.getElementById('SUM_MONTO_DOC_C_0').innerHTML = number_format(sum_monto_doc, 0, ',', '.');
		document.getElementById('SUM_MONTO_DOC_C_H_0').value = sum_monto_doc;
		
   	return true;
	}
}

function usar_nota_credito() {
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value; 
	
	if(cod_empresa == ''){
		alert('Sr. Usuario debe ingresar primero una empresa, así podrá ver las notas de crédito asociadas');
		return false;
	}
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:440px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("usar_nota_credito.php?cod_empresa="+cod_empresa, "_blank", args);
	if (returnVal == null)
 		return false;
	else {
		var dato = returnVal.split("|");
		
		var sum_monto_doc = document.getElementById('SUM_MONTO_DOC_C_H_0').value;
		var kl_tipo_doc_nc = 7;
		for (i=0; i < (dato.length - 1); i=i+3){
			nro_nota_credito = dato[i];
			fecha_nota_credito = dato[i+1];
			total_con_iva	 = dato[i+2];

			var row = add_line('DOC_INGRESO_PAGO', 'ingreso_pago');
			document.getElementById('NRO_DOC_' + row).value = nro_nota_credito;
			document.getElementById('FECHA_DOC_' + row).value = fecha_nota_credito;
			document.getElementById('MONTO_DOC_' + row).value = total_con_iva;
			document.getElementById('MONTO_DOC_C_H_' + row).value = total_con_iva;
			document.getElementById('COD_TIPO_DOC_PAGO_' + row).value = kl_tipo_doc_nc;
			document.getElementById('COD_TIPO_DOC_PAGO_H_' + row).value = kl_tipo_doc_nc;
			
			document.getElementById('NRO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('FECHA_DOC_'+ row).setAttribute('readonly', "");
			document.getElementById('MONTO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('COD_BANCO_'+ row).setAttribute('disabled', "");
			
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).setAttribute('disabled', "");
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).style.color = '#000';
			
			sum_monto_doc = parseInt(sum_monto_doc) + parseInt(document.getElementById('MONTO_DOC_C_H_' + row).value);
			
		}
		document.getElementById('SUM_MONTO_DOC_C_0').innerHTML = number_format(sum_monto_doc, 0, ',', '.');
		document.getElementById('SUM_MONTO_DOC_C_H_0').value = sum_monto_doc;
		
   	return true;
	}
}

function usar_factura_compra() {
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value; 
	
	if(cod_empresa == ''){
		alert('Sr. Usuario debe ingresar primero una empresa, así podrá ver las facturas de compra asociadas');
		return false;
	}
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:440px;dialogHeight:300px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("usar_factura_compra.php?cod_empresa="+cod_empresa, "_blank", args);
	if (returnVal == null)
 		return false;
	else {
		var dato = returnVal.split("|");
		
		var sum_monto_doc = document.getElementById('SUM_MONTO_DOC_C_H_0').value;
		var K_TIPO_DOC_FAPROV = 11;
		for (var i=0; i < (dato.length - 1); i=i+3){
			var vl_nro_faprov = dato[i];
			var vl_fecha_faprov = dato[i+1];
			var vl_total_con_iva = dato[i+2];

			var row = add_line('DOC_INGRESO_PAGO', 'ingreso_pago');
			document.getElementById('NRO_DOC_' + row).value = vl_nro_faprov;
			document.getElementById('FECHA_DOC_' + row).value = vl_fecha_faprov;
			document.getElementById('MONTO_DOC_' + row).value = vl_total_con_iva;
			document.getElementById('MONTO_DOC_C_H_' + row).value = vl_total_con_iva;
			document.getElementById('COD_TIPO_DOC_PAGO_' + row).value = K_TIPO_DOC_FAPROV;
			document.getElementById('COD_TIPO_DOC_PAGO_H_' + row).value = K_TIPO_DOC_FAPROV;
			
			document.getElementById('NRO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('FECHA_DOC_'+ row).setAttribute('readonly', "");
			document.getElementById('MONTO_DOC_' + row).setAttribute('readonly', "");
			document.getElementById('COD_BANCO_'+ row).setAttribute('disabled', "");
			
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).setAttribute('disabled', "");
			document.getElementById('COD_TIPO_DOC_PAGO_'+ row).style.color = '#000';
			
			sum_monto_doc = parseInt(sum_monto_doc) + parseInt(document.getElementById('MONTO_DOC_C_H_' + row).value);
			
		}
		document.getElementById('SUM_MONTO_DOC_C_0').innerHTML = number_format(sum_monto_doc, 0, ',', '.');
		document.getElementById('SUM_MONTO_DOC_C_H_0').value = sum_monto_doc;
		
   	return true;
	}
}

function valida_asignacion_doc_pago(ve_campo){	
	var K_TIPO_DOC_PAGO_NC = 7;
	var K_TIPO_DOC_PAGO_ANTICIPO = 9;
	var K_TIPO_DOC_PAGO_FAPROV = 11;
	
	var record = get_num_rec_field(ve_campo.id);
	var cod_tipo_doc_pago =  document.getElementById('COD_TIPO_DOC_PAGO_' + record).value;
		
	if(cod_tipo_doc_pago == K_TIPO_DOC_PAGO_ANTICIPO){
		alert('El tipo de documento seleccionado no puede ser asignado de esta forma.\n Favor utilice el boton "Anticipo".');
		document.getElementById('COD_TIPO_DOC_PAGO_'+ record).value = '';
	}else if(cod_tipo_doc_pago == K_TIPO_DOC_PAGO_NC){
		alert('El tipo de documento seleccionado no puede ser asignado de esta forma.\n Favor utilice el boton "Nota Crédito".');
		document.getElementById('COD_TIPO_DOC_PAGO_'+ record).value = '';
	}else if(cod_tipo_doc_pago == K_TIPO_DOC_PAGO_FAPROV){
		alert('El tipo de documento seleccionado no puede ser asignado de esta forma.\n Favor utilice el boton "Factura Compra".');
		document.getElementById('COD_TIPO_DOC_PAGO_'+ record).value = '';
	}
}