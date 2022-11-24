function request_nota_debito(ve_prompt, ve_valor) 
{
	var args = "location:no;dialogLeft:100px;dialogTop:200px;dialogWidth:300px;dialogHeight:250px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("request_nota_debito.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
 	if(returnVal == null)		
		return false;
	else{		
		document.getElementById('wo_hidden').value = returnVal;
		document.output.submit();
 		return true;
	}
}

function change_fecha() {
	
	var fecha_nueva = document.getElementById('FECHA_NOTA_CREDITO_0').value;
	document.getElementById('FECHA_NOTA_CREDITO_I_0').innerHTML = fecha_nueva;
	
	/* valida que no ingrese un fecha vacia*/
	if(fecha_nueva == ''){
		alert('Debe ingresar la fecha de la Nota Credito');
		return false;
	}
}

function validate() {
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0');
	// cod_estado_doc_sii_value = 1 = emitida
	if (to_num(cod_estado_doc_sii_value) == 1){
		var aTR = get_TR('ITEM_NOTA_DEBITO');
		var cant_total = 0;
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	
		for (var i = 0; i < aTR.length; i++){
			cant_total = cant_total + document.getElementById('CANTIDAD_' + i).value;		
		}	
		
		if(cant_total == 0){
			alert('La Cantidad a Despachar debe ser superior a "0"');
			document.getElementById('CANTIDAD_0').focus();
			return false;
		}	
	
	}
	//valida cod_tipo_nota_debito
	var cod_tipo_nota_debito = document.getElementById('COD_TIPO_NOTA_DEBITO_0');
		if (cod_tipo_nota_debito.value == '') {
			alert('Debe ingresar el Tipo Nota Debito antes de grabar.');
			cod_tipo_nota_debito.focus();
			return false;
		}
		
	var tipo_doc = document.getElementById('TIPO_DOC_0');
		if (tipo_doc.value == '') {
			alert('Debe ingresar el Tipo Documento antes de grabar.');
			tipo_doc.focus();
			return false;
		}
		
	var cod_doc = document.getElementById('COD_DOC_0');
		if (cod_doc.value == '') {
			alert('Debe ingresar el Cod. Documento antes de grabar.');
			cod_doc.focus();
			return false;
		}	
	
	// cod_estado_doc_sii_value = 4 = anulada
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}	

	return true;
}
function add_line_nc(ve_tabla_item, nomTabla) {
	var aTR = get_TR(ve_tabla_item);
	var VALOR_NC_H = document.getElementById('VALOR_NC_H_0').value;
	if (aTR.length >= VALOR_NC_H){
		alert('¡No se pueden agregar más ítems, se ha llegado al máximo permitido!');
		return false;
		}
	else
		add_line(ve_tabla_item,nomTabla);
}

function change_item_nota_credito(ve_valor, ve_campo) {
	var record_item_nc = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_nc).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_nc);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_nc);
	var cod_item_nv = document.getElementById('COD_ITEM_NOTA_CREDITO_' + record_item_nc).value;
	
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
			
		help_producto(ve_valor, 0);	
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos a una Nota de Credito.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_nc).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_nc).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
	}	
}

function mostrarOcultar_Anula(ve_campo) {
	var tr_anula = document.getElementById('tr_anula');
	
	if (to_num(ve_campo.value)==4) {
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


function valida_ct_x_nc(ve_campo) {
	// valida solo si la NC es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_H_0').value);
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_nc = to_num(document.getElementById('CANTIDAD_POR_NC_' + record).innerHTML);
		var cant_ingresada = to_num(ve_campo.value);
			if (parseFloat(cant_por_nc) < parseFloat(cant_ingresada)) {
				alert('El valor ingresado no puede ser mayor que la cantidad esperada: '+ number_format(cant_por_nc, 1, ',', '.'));
				return number_format(cant_por_nc, 1, ',', '.');
			}
			else
				return ve_campo.value;
	}
	else
		return ve_campo.value;
}
function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	var vl_precio = valores[3];
	vl_precio = vl_precio.replace('.', '', 'g');	// borra los puntos en los miles
	set_value('PRECIO_' + record, vl_precio, vl_precio);
}
function change_item_nc_adm(ve_cod_producto) {
	ve_cod_producto.value = ve_cod_producto.value.toUpperCase();
	if (ve_cod_producto.value != 'TE') {
		alert('Debe ingresar la NC como TE');
		ve_cod_producto.value = 'TE';
	}
	help_producto(ve_cod_producto, 0);	
}/*
function select_printer_dte() {
	
	var cod_nota_credito = document.getElementById('COD_NOTA_CREDITO_0').value;
	
	// retorna la cantudad de registros en IMPRESORA_DTE, si es cero 
	var ajax = nuevoAjax();
	ajax.open("GET", "../../../trunk/appl/factura/ajax_select_printer_dte.php", false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);

	// retorna si es que esta  factura fue creada desde NV
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_nc_desde_nv.php?cod_nota_credito="+cod_nota_credito, false);
	ajax.send(null);
	var resp_desde_nv = URLDecode(ajax.responseText);

	if (resp != 0) {
		var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:360px;dialogHeight:90px;dialogLocation:0;Toolbar:no;";
		var returnVal = window.showModalDialog("../../../trunk/appl/factura/select_printer_dte.php", "_blank", args);
		if (returnVal == null)		
			return false;		
		else 
			document.getElementById('wi_impresora_dte').value = returnVal;
		}
	if(resp_desde_nv == 'S'){
			document.getElementById('wi_impresora_dte').value = 100 // Claudia Morales imprime (impresora laser claudia);

		}
}*/