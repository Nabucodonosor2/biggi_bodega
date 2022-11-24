/***********MODULO NOTA DE VENTA NO SE OCUPA EN BIGGI BODEGA*********/
function dlg_print() {
	var args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:500px;dialogHeight:225px;dialogLocation:0;Toolbar:no;";
	var cod_nota_venta_value = document.getElementById('COD_NOTA_VENTA_H_0').value;
	var returnVal = window.showModalDialog("dlg_print_nota_venta.php?cod_nota_venta="+cod_nota_venta_value, "_blank", args);
 	if (returnVal == null)
 		return false;
	else {	
			var dato = returnVal.substring(0,5);			  
			if (dato == 'marca') {
			args = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:620px;dialogHeight:225px;dialogLocation:0;Toolbar:no;";
			returnVal = window.showModalDialog("dlg_print_marca.php?cod_nota_venta="+cod_nota_venta_value, "_blank", args);
 			if (returnVal == null)
 				return false;
 			else {
				document.getElementById('wi_hidden').value = 'marca|'+returnVal;
				document.input.submit();
		   		return true;
 			}
		
		}			
		document.getElementById('wi_hidden').value = returnVal;
		document.input.submit();
   		return true;
	}
}
function change_orden_compra(ve_campo){
	ve_campo.value = trim(ve_campo.value);
	var nro_orden_compra = ve_campo.value;
	if (nro_orden_compra != ''){
		var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "../nota_venta/ajax_nro_orden_compra.php?nro_orden_compra="+nro_orden_compra+"&cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var lista	= resp.split('|');
		var	count_nro_oc = (lista[0]);
		if (count_nro_oc > 0) {
			var	cod_nota_venta = (lista[1]);
			var	nom_estado_nv = (lista[2]);
			var	nom_usuario_emisor = (lista[3]);
			var aux_cod_nota_venta = document.getElementById('COD_NOTA_VENTA_H_0').value;
			if(aux_cod_nota_venta != cod_nota_venta){
				alert('Nº OC. ' + nro_orden_compra + ' fue ingresada. Por ' +nom_usuario_emisor+ '\nEn la  Nota Venta Nº ' +cod_nota_venta+ ' Estado: ' +nom_estado_nv);
				document.getElementById('NRO_ORDEN_COMPRA_0').value = '';
				document.getElementById('NRO_ORDEN_COMPRA_0').focus();
				return false;
			}else{
				return true;
			}
		}
		else												 		
			return true;
	}
}

function validate() {
	// Valida que esten en modo ingresables el NRO_OC_COMPRA antes de validar
	if (document.getElementById('NRO_ORDEN_COMPRA_0')) {
		var nro_orden_compra = document.getElementById('NRO_ORDEN_COMPRA_0').value;
		var fecha_orden_compra = document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value;
		
		if(nro_orden_compra != '' || fecha_orden_compra != ''){
			if(nro_orden_compra == ''){
				alert('Debe ingresar Nº OC Cliente. Antes de Continuar');
				document.getElementById('NRO_ORDEN_COMPRA_0').focus();
				return false;
			}else if(fecha_orden_compra == ''){
				alert('Debe ingresar Fecha OC cliente. Antes de Continuar');
				document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').focus();
				return false;
			}
		}
	}
	
	return validate_cot_nv('ITEM_NOTA_VENTA');
}

function add_line_pre_orden_compra(ve_id_item_nota_venta) {
	var row = add_line('PRE_ORDEN_COMPRA', 'nota_venta');
	document.getElementById('CC_COD_ITEM_NOTA_VENTA_' + row).value = document.getElementById('COD_ITEM_NOTA_VENTA_' + ve_id_item_nota_venta).value ;
}

function add_line_item(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('ITEM_NOTA_VENTA');
	document.getElementById('ITEM_' + row).value = aTR.length;
	document.getElementById('COD_PRODUCTO_' + row).focus();
	
}

function del_line_item(tr_id, nom_tabla) {
	var record = get_num_rec_field(tr_id);
	var cod_item_nv = document.getElementById('COD_ITEM_NOTA_VENTA_' + record).value;
	var cod_estado_nv = document.getElementById('COD_ESTADO_NOTA_VENTA_0').value;
	var is_new_item = document.getElementById('IS_NEW_' + record).value;
	if (cod_estado_nv == 4 && is_new_item == 'N'){ // si estado es confirmado y es antiguo item, llama ajax que busca si el item tiene doctos relacionados
		var ajax = nuevoAjax();
		ajax.open("GET", "../nota_venta/ajax_doctos_relacionados.php?cod_item_nv="+cod_item_nv, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		if (resp != ''){
			if(!confirm('Atención: \nEl ítem que está eliminando tiene los siguientes documentos asociados:\n\n'+resp+'\n¿Desea de todas maneras eliminar el ítem?'))
				return false;
		}		
	}
	del_line(tr_id, nom_tabla);
	
	//elimina items	PRE_ORDEN_COMPRA
	var aTR = get_TR('PRE_ORDEN_COMPRA');	
	for (var i=0; i<aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var cod_item_nv_pre_oc = document.getElementById('CC_COD_ITEM_NOTA_VENTA_' + record).value;
		if (cod_item_nv_pre_oc==cod_item_nv) {
			del_line('PRE_ORDEN_COMPRA_' + record, 'nota_venta');
		}
	}		
}

function find_item(ve_tabla_item, ve_nom_cod, ve_cod_value,ve_comienzo) {
	var aTR = get_TR(ve_tabla_item);
	for (var i=ve_comienzo; i<aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var cod_value = document.getElementById(ve_nom_cod + '_' + record).value;
		if (cod_value==ve_cod_value) {
			return aTR[i];
		}
	}
	return false;
}

function precio_proveedor(ve_cod_proveedor) {
	var cc_precio_compra_value = ve_cod_proveedor.options[ve_cod_proveedor.selectedIndex].label; 
	var record = get_num_rec_field(ve_cod_proveedor.id);
	set_value('CC_PRECIO_COMPRA_' + record, cc_precio_compra_value, number_format(cc_precio_compra_value, 0, ',', '.'));
	set_value('CC_PRECIO_COMPRA_H_' + record, cc_precio_compra_value, cc_precio_compra_value);

	var cantidad_compra = to_num(get_value('CC_CANTIDAD_' + record));	
	set_value('CC_TOTAL_' + record, cantidad_compra * cc_precio_compra_value, number_format(cantidad_compra * cc_precio_compra_value, 0, ',', '.'));
}

function change_item_nota_venta(ve_valor, ve_campo) {
	var record_item_nv = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_nv).value;
	var cod_item_nv = document.getElementById('COD_ITEM_NOTA_VENTA_' + record_item_nv).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_nv);
	var cod_estado_nv = document.getElementById('COD_ESTADO_NOTA_VENTA_H_0').value;
	if (cod_estado_nv == 4)
		var cod_producto = document.getElementById('COD_PRODUCTO_H_' + record_item_nv);
	else
		var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_nv);
		
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
		//cuando se modifique el modelo, obliga al usuario a ingresar la cantidad.
		document.getElementById('CANTIDAD_' + record_item_nv).value = '';
					
		//IS = no entiendo porque en queda cod_producto_old.value = cod_producto
		// por lo tanto, al mostrar alerta los datos del producto quedan '' 
		help_producto(ve_valor, 0);	
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos a una Nota de Venta.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_nv).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_nv).value = '';
			}
			else{
				cod_producto.value = cod_producto_old.value; 
				help_producto(cod_producto, 0); 
			}	
		}
		
		///llama ajax que busca en PRODUCTO o PRODUCTO_COMPUESTO
		var ajax = nuevoAjax();
		ajax.open("GET", "../nota_venta/ajax_change_item_nota_venta.php?cod_producto="+URLEncode(cod_producto.value), false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var aDato = eval("(" + resp + ")");
		var aTR = get_TR('PRE_ORDEN_COMPRA');
		//elimina items PRE_ORDEN_COMPRA	
		for (var i=0; i<aTR.length; i++) {
			var record = get_num_rec_field(aTR[i].id);
			var cod_item_nv_pre_oc = document.getElementById('CC_COD_ITEM_NOTA_VENTA_' + record).value;
			if (cod_item_nv_pre_oc==cod_item_nv) {
				del_line('PRE_ORDEN_COMPRA_' + record, 'nota_venta');
			}
		}
		//agrega items PRE_ORDEN_COMPRA
		for (var i = 0; i < aDato.length; i++) {
				var row = add_line('PRE_ORDEN_COMPRA', 'nota_venta');
				document.getElementById('CC_COD_ITEM_NOTA_VENTA_' + row).value = cod_item_nv;
				document.getElementById('CC_ITEM_' + row).innerHTML = item_value;				
				if (aDato[i]['GENERA_COMPRA']=='S')
					document.getElementById('CC_GENERA_COMPRA_'+ row).checked = true;
				else
					document.getElementById('CC_GENERA_COMPRA_'+ row).checked = false;
			
				document.getElementById('CC_COD_PRODUCTO_' + row).innerHTML = aDato[i]['COD_PRODUCTO_HIJO'];	
				document.getElementById('CC_COD_PRODUCTO_H_' + row).value = aDato[i]['COD_PRODUCTO_HIJO'];
				
				if(aDato[i]['COD_PRODUCTO_HIJO']== 'TE')
					document.getElementById('CC_NOM_PRODUCTO_' + row).innerHTML = document.getElementById('NOM_PRODUCTO_' + record_item_nv).value;
				else	
					document.getElementById('CC_NOM_PRODUCTO_' + row).innerHTML = aDato[i]['NOM_PRODUCTO'];	
				
				var cod_proveedor = document.getElementById('CC_COD_PROVEEDOR_' + row);	
				load_con_ajax('nota_venta', cod_proveedor, cod_proveedor.id, aDato[i]['COD_PRODUCTO_HIJO']);
				cod_proveedor = document.getElementById('CC_COD_PROVEEDOR_' + row);
				cod_proveedor.selectedIndex = 1;
				document.getElementById('CC_PRECIO_COMPRA_' + row).innerHTML = number_format(cod_proveedor.options[1].label, 0, ',', '.');
				document.getElementById('CC_PRECIO_COMPRA_H_' + row).value = cod_proveedor.options[1].label;
		}	
	}
	else if(ve_campo == 'CANTIDAD'){
		var is_new_item = document.getElementById('IS_NEW_' + record_item_nv).value;
		if (cod_estado_nv == 4 && is_new_item =='N'){ // si estado es confirmado y es antiguo item, llama ajax que busca si el item tiene doctos relacionados
			var ajax = nuevoAjax();
			ajax.open("GET", "../nota_venta/ajax_doctos_relacionados.php?cod_item_nv="+cod_item_nv, false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);			
			if (resp != '')
				alert('Atención: \nEl ítem que está modificando tiene los siguientes documentos asociados:\n'+resp);
		}	
		else{
			///llama ajax que crea lineas en pre_orden_compra
			var ajax = nuevoAjax();
			ajax.open("GET", "../nota_venta/ajax_change_item_nota_venta.php?cod_producto="+URLEncode(cod_producto.value), false);
			ajax.send(null);
			var resp = URLDecode(ajax.responseText);
			var aDato = eval("(" + resp + ")");
			var aTR = get_TR('PRE_ORDEN_COMPRA');
			for (var i=0; i<aTR.length; i++) {
				var record = get_num_rec_field(aTR[i].id);
				var cod_item_nv_pre_oc = document.getElementById('CC_COD_ITEM_NOTA_VENTA_' + record).value;
				if (cod_item_nv_pre_oc==cod_item_nv) {
					for (var j = 0; j < aDato.length; j++) {
						var cod_producto_hijo = aDato[j]['COD_PRODUCTO_HIJO'];
						var cod_producto_poc = document.getElementById('CC_COD_PRODUCTO_' + record).innerHTML		
						
						if(cod_producto_hijo==cod_producto_poc){
							//calcula la cantidad a comprar
							var es_compuesto = aDato[j]['ES_COMPUESTO'];
							if(es_compuesto == 'S'){
								var cant_compuesto = aDato[j]['CANTIDAD'];	
								var cant_total_compra = to_num(get_value('CANTIDAD_' + record_item_nv)) * cant_compuesto;
								
							}else if(es_compuesto == 'N'){
								var cant_total_compra = to_num(get_value('CANTIDAD_' + record_item_nv));
							}
							document.getElementById('CC_CANTIDAD_' + record).innerHTML = cant_total_compra;
							document.getElementById('CC_CANT_COMPUESTO_H_' + record).value = cant_total_compra;	
							var precio_compra = to_num(get_value('CC_PRECIO_COMPRA_' + record));
							document.getElementById('CC_TOTAL_' + record).innerHTML = number_format(precio_compra * cant_total_compra, 0, ',', '.');
						}
					}
				}
			}
		}//end else	
	} // if(ve_campo == 'CANTIDAD')
}			


function mostrarOcultar(ve_cod_forma_pago) {
	var cod_forma_pago = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].value; 	
	if (parseFloat(cod_forma_pago) == 1){
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");					
		document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");				
   
    }
    else{
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').value='';
    	document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').value='';
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='hidden';
    	document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').type='hidden';
    	document.getElementById('AA').type='hidden';
  
    }
}
function print_por_despachar_facturar(ve_opcion) {
	var porc_gd = document.getElementById(ve_opcion).innerHTML;					
	porc_gd = porc_gd.substring(0,porc_gd.length-2)
		
	if(porc_gd >= 100){ 
		alert('No existen documentos pendientes');
		return false;						
	}		
	
	if (document.getElementById('b_save')) {
		if (validate_save()) { 															  	 		 	
			document.getElementById('wi_hidden').value = 'save_desde_' + ve_opcion;
			document.getElementById('b_save').click();
			document.input.submit();
			return true;
		}
		else												 		
			return false;
	}
	else {
		document.getElementById('wi_hidden').value = 'save_desde_' + ve_opcion;
		document.input.submit();
		return true;
	}
	
}

function change_forma_pago(ve_tipo_forma_pago, ve_cod_forma_pago) {
	if (ve_tipo_forma_pago == 'OTRO')  // forma de pago = OTRO
		var cant_docs = document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').value;			
	else{
		mostrarOcultar(ve_cod_forma_pago);
		var cant_docs = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].label;
	}
	var total_con_iva = document.getElementById('TOTAL_CON_IVA_H_0').value;
	var monto_doc = parseInt(to_num(total_con_iva) / to_num(cant_docs));
	var aux_monto_doc = 0;
	
	//elimina lineas
	var aTR = get_TR('DOC_NOTA_VENTA');
	for (var i = 0; i < aTR.length; i++) {
		del_line(aTR[i].id, 'nota_venta');
	}
	
	// agrega lineas
	for (var i = 0; i < cant_docs; i++) {
		if (i == to_num(cant_docs)-1){
			monto_doc = total_con_iva - aux_monto_doc;	
		}	
		var row = add_line('DOC_NOTA_VENTA', 'nota_venta');
		document.getElementById('MONTO_DOC_' + row).value = monto_doc;
		// el 'MONTO_DOC_H_' no es necesario actualizarso porque es no visible
		document.getElementById('MONTO_DOC_H_H_' + row).value = monto_doc;
		aux_monto_doc = aux_monto_doc + monto_doc;
	}
	
	// actualiza la suma
	document.getElementById('SUM_MONTO_DOC_H_0').innerHTML = number_format(total_con_iva, 0, ',', '.');
	document.getElementById('SUM_MONTO_DOC_H_H_0').value =	total_con_iva;
}

function valida_monto_doc(ve_monto_doc) {
	var cant_docs = document.getElementById('COD_FORMA_PAGO_0').options[document.getElementById('COD_FORMA_PAGO_0').selectedIndex].label;  //en el label está la cantidad de documentos.
	var total_con_iva = document.getElementById('TOTAL_CON_IVA_H_0').value;
	var monto_calculado = parseInt(to_num(total_con_iva) / to_num(cant_docs));
	var rango_porc = document.getElementById('RANGO_DOC_NOTA_VENTA_0').value;
	
	var rango_max = roundNumber(monto_calculado + (monto_calculado * parseFloat(to_num(rango_porc) / 100)), 0);
	var rango_min = roundNumber(monto_calculado - (monto_calculado * parseFloat(to_num(rango_porc) / 100)), 0);
	var record = get_num_rec_field(ve_monto_doc.id);
	var monto_ingresado = to_num(get_value('MONTO_DOC_' + record));
	
	if (monto_ingresado>rango_max){
		alert('El monto ingresado no puede ser superior a un '+rango_porc+'% del monto sugerido.\n \n - Máximo permitido: '+number_format(rango_max, 0, ',', '.'));
		ve_monto_doc.value = monto_calculado;		
	}
	else if (monto_ingresado<rango_min){
		alert('El monto ingresado no puede ser inferior a un '+rango_porc+'% del monto sugerido.\n \n - Mínimo permitido: '+number_format(rango_min, 0, ',', '.'));
		ve_monto_doc.value = monto_calculado;		
	}

}

function valida_tipo_doc_pago(ve_tipo_doc_pago) {
	var record = get_num_rec_field(ve_tipo_doc_pago.id);
	var fecha_doc = document.getElementById('FECHA_DOC_'+ record);
	var nro_doc = document.getElementById('NRO_DOC_'+ record);
	var cod_banco = document.getElementById('COD_BANCO_'+ record);
	var cod_plaza = document.getElementById('COD_PLAZA_'+ record);
	var nro_autoriza = document.getElementById('NRO_AUTORIZA_'+ record);
	
	if (ve_tipo_doc_pago.value == 1){  //tipo de documento = efectivo 
		fecha_doc.setAttribute('type', "text");
		nro_doc.value = '';
		nro_doc.setAttribute('type', "hidden");
		cod_banco.setAttribute('disabled', "");
		cod_banco.value = '';
		cod_plaza.setAttribute('disabled', "");
		cod_plaza.value = '';
		nro_autoriza.setAttribute('type', "hidden");
	}
	else if (ve_tipo_doc_pago.value == 8){  //tipo de documento = por definir 
		fecha_doc.value = '';
		fecha_doc.setAttribute('type', "hidden");
		nro_doc.value = '';
		nro_doc.setAttribute('type', "hidden");
		cod_banco.setAttribute('disabled', "");
		cod_banco.value = '';
		cod_plaza.setAttribute('disabled', "");
		cod_plaza.value = '';
		nro_autoriza.setAttribute('type', "hidden");
	}
	else{
		fecha_doc.setAttribute('type', "text");
		nro_doc.setAttribute('type', "text");
		cod_banco.removeAttribute('disabled');
		cod_plaza.removeAttribute('disabled');
		nro_autoriza.setAttribute('type', "text");
	}
}

function porc_dscto_corporativo(){
	var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
    var ajax = nuevoAjax();
    ajax.open("GET", "porc_dscto_corporativo.php?cod_empresa="+cod_empresa, false);
    ajax.send(null);    
	var resp = ajax.responseText; 
	document.getElementById('PORC_DSCTO_CORPORATIVO_H_0').value = resp;
	document.getElementById('PORC_DSCTO_CORPORATIVO_0').innerHTML = number_format(resp, 1, ',', '.');
	document.getElementById('PORC_DSCTO_CORPORATIVO_0').value = resp;
}
function select_1_empresa(valores, record) {
/* Se reimplementa para agregar codigo adicional */
	 set_values_empresa(valores, record);

	// Codigo adicional
	porc_dscto_corporativo();
	computed(0, 'MONTO_DSCTO_CORPORATIVO');

	var vl_nro_oc = document.getElementById('NRO_ORDEN_COMPRA_0');
	if (change_orden_compra(vl_nro_oc))
		vl_nro_oc.focus();
}
function historia_corporativo(){
		var cant_cambio_porc_descto = document.getElementById('CANT_CAMBIO_PORC_DESCTO_CORP_0').value;
		
		if (cant_cambio_porc_descto == 0)
			alert('¡No se registran modificaciones de Descto. Corporativo!');
		else{
			var cod_nota_venta = document.getElementById('COD_NOTA_VENTA_H_0').value;
			var args = "location:no;dialogLeft:632px;dialogTop:337px;dialogWidth:500px;dialogHeight:207px;dialogLocation:0;Toolbar:no;";
			var returnVal = window.showModalDialog("historia_corporativo.php?cod_nota_venta="+cod_nota_venta, "_blank", args);
		}
}

function cerrar_nota_venta(ve_autoriza_cierre, ve_es_vendedor){
	
	var cod_estado_nv = document.getElementById('COD_ESTADO_NOTA_VENTA_H_0').value;
	if (cod_estado_nv != 4){ //estado de la NV es != confirmada
		alert('¡No se puede generar el cierre!\n \nAntes debe confirmar la nota de venta.');
		return false;								
	}
	
	if (ve_autoriza_cierre == 'S' | ve_es_vendedor == 'S'){
		var aTR = get_TR('TIPO_PENDIENTE_NOTA_VENTA');
		for (var i = 0; i < aTR.length; i++){
			var autoriza = document.getElementById('AUTORIZA_' + i).checked;
			//alertas de acuerdo al tipo de usuario
			if (ve_autoriza_cierre == 'S'){
				var motivo = document.getElementById('MOTIVO_AUTORIZA_' + i).value;
				var alert_autoriza = '¡No se puede cerrar la Nota de Venta!\n \nDebe autorizar las observaciones pendientes.';
				var alert_motivo = '¡No se puede cerrar la Nota de Venta!\n \nDebe ingresar el Motivo de autorización.';
			}
			else{
				var motivo = 'tiene_motivo';
				var alert_autoriza = '¡No se puede cerrar la Nota de Venta!\n \nUn usuario con privilegios debe autorizar las observaciones pendientes!';	
			}	
			if (autoriza == false){
				alert(alert_autoriza);
				if (ve_autoriza_cierre == 'S'){	
					document.getElementById('AUTORIZA_' + i).setAttribute('onblur', "this.style.border=''");
					document.getElementById('AUTORIZA_' + i).setAttribute('onfocus', "this.style.border='1px solid #FF0000'");
					document.getElementById('AUTORIZA_' + i).focus();
				}
				return false;
			}	
			else if (motivo == ' '){
				alert(alert_motivo);
				document.getElementById('MOTIVO_AUTORIZA_' + i).setAttribute('onblur', "this.style.border=''");
				document.getElementById('MOTIVO_AUTORIZA_' + i).setAttribute('onfocus', "this.style.border='1px solid #FF0000'");
				document.getElementById('MOTIVO_AUTORIZA_' + i).focus();
				return false;
			}
		}
		if (validate_save()) {												 		
			document.getElementById('CIERRE_H_0').value = 'S';
			document.getElementById('b_save').click();
			alert('¡Se ha generado el cierre de la Nota de Venta con éxito!');
		} 												 	
	}else{
		alert('¡Ud. no está autorizado para cerrar la Nota de Venta!');
		return false;
	}
	
}	

function cambia_estado(ve_campo){
	document.getElementById('COD_ESTADO_NOTA_VENTA_H_0').value = document.getElementById('COD_ESTADO_NOTA_VENTA_0').value;	
	mostrarOcultar_Anula(ve_campo);
}

function mostrarOcultar_Anula(ve_campo) {	
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==3) {
		var aTR = get_TR('ORDEN_COMPRA');
		for (var i = 0; i < aTR.length; i++){
			var cod_estado_OC = document.getElementById('COD_ESTADO_ORDEN_COMPRA_' + i).value;
			if (cod_estado_OC != 2){ //estado de la OC es != anulada
				alert('¡No se puede anular la Nota de Venta!\n \nAntes debe anular las Ordenes de Compra.');
				ve_campo.selectedIndex = 1;
				return false;								
			}	
		}
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

function fecha_plazo_cierre() {
	var cant_cambio_plazo_cierre = document.getElementById('CANT_CAMBIO_FECHA_PLAZO_CIERRE_0').value;
	if (cant_cambio_plazo_cierre == 0)
		alert('¡No se registran modificaciones en el Plazo de Cierre!');
	else{
		var cod_nota_venta = document.getElementById('COD_NOTA_VENTA_H_0').value;
		var args = "location:no;dialogLeft:632px;dialogTop:337px;dialogWidth:630px;dialogHeight:207px;dialogLocation:0;Toolbar:no;";
		var returnVal = window.showModalDialog("historia_fecha_cierre.php?cod_nota_venta="+cod_nota_venta, "_blank", args);
	}
}

function change_precio_oc(boton, ve_tabla) {
	var record = get_num_rec_field(boton.id);	
	var cod_pre_orden_compra = document.getElementById('COD_'+ve_tabla+'_'+record).value;
	var cod_producto = document.getElementById('CC_COD_PRODUCTO_H_'+record).value;
	var precio = document.getElementById('CC_PRECIO_COMPRA_H_'+record).value;
	var cod_proveedor = document.getElementById('CC_COD_PROVEEDOR_'+record).options[document.getElementById('CC_COD_PROVEEDOR_'+record).selectedIndex].value;

	if (cod_producto=='') {
		alert('Debe ingresar el producto antes de modificar el precio.');
		return;
	}
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:840px;dialogHeight:400px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("change_precio_compra.php?tabla="+ve_tabla+"&cod_pre_orden_compra="+cod_pre_orden_compra+"&cod_producto="+cod_producto+"&precio="+precio+"&cod_proveedor="+cod_proveedor, "_blank", args);
	if (returnVal == null)
 		return false;
	else {
		var precio = document.getElementById('CC_PRECIO_COMPRA_' + record);
		var precio_h = document.getElementById('CC_PRECIO_COMPRA_H_' + record);
		var motivo = document.getElementById('MOTIVO_MOD_PRECIO_' + record);
		var res = returnVal.split('|');
		precio.innerHTML = number_format(res[0], 0, ',', '.'); 
		precio_h.value = res[0];
		motivo.value = res[1];
		
		var cantidad_compra = to_num(get_value('CC_CANTIDAD_' + record));
		document.getElementById('CC_TOTAL_' + record).innerHTML = number_format(precio_h.value * cantidad_compra, 0, ',', '.');
				
   		return true;
	}
}
function change_precio_NV(boton, ve_tabla, ve_entrable) {
	if (ve_entrable)
		return change_precio(boton, ve_tabla);
	else {
		var record = get_num_rec_field(boton.id);
		var cod_item = document.getElementById('COD_'+ve_tabla+'_'+record).value;
		var cod_producto = document.getElementById('COD_PRODUCTO_H_'+record).value;
		// solo consulta
		if (cod_producto=='TE') {
			var args = "dialogLeft:100px;dialogTop:300px;dialogWidth:550px;dialogHeight:410px;dialogLocation:0;Toolbar:'yes';";
			var returnVal = window.showModalDialog("../common_appl/ingreso_TE.php?cod_item="+cod_item, "_blank", args);
		}
		else {
			var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:840px;dialogHeight:400px;dialogLocation:0;Toolbar:no;";
			var returnVal = window.showModalDialog("../common_appl/change_precio.php?tabla="+ve_tabla+"&cod_item="+cod_item+"&cod_producto=__SOLO_CONSULTA__&precio=0", "_blank", args);
		}
	 	return false;
	}
}
function actualiza_dscto_corp_hidden(ve_campo){
	document.getElementById('PORC_DSCTO_CORPORATIVO_H_0').value = ve_campo.value;
	return true;
}