function dlg_print_dte(){
	var url = "dlg_cedible.php";
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 240,
		 width: 360,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null){		
				return false;
			}			
			else{
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_imprimir_dte_x");
				input.setAttribute("id", "b_imprimir_dte_x");
				document.getElementById("input").appendChild(input);
								
				document.getElementById('wi_hidden').value = returnVal;
				document.input.submit();
				return true;
			}
		}
	});
}

/******NO SE ESTA OCUPANDO*/

function request_factura(ve_prompt, ve_valor) 
{	
	var args = "location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:350px;dialogHeight:230px;dialogLocation:0;Toolbar:no;";
	var returnVal = window.showModalDialog("../../../trunk/appl/factura/request_factura.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor), "_blank", args);
	if (returnVal == null)		
		return false;		
	else 
	{
		
	  var dato = returnVal.split("|");
	  var cod_nota_venta_value= dato[1];
	  var opcion= dato[0];
	  	
	  
		if (opcion == 'desde_nv' || opcion == 'desde_nv_anticipo' ) //
		{  
				//debe crear la FA para todos los itemsNV que tengan pendiente por facturar usar f_nv_por_facturar
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		return true;
		}	
 		else	
 			{  
 			
 				//si selecciona desde GD debe presentar una 2da ventana 
 				args2 = "location:no;dialogLeft:400px;dialogTop:300px;dialogWidth:900px;dialogHeight:400px;dialogLocation:0;Toolbar:no;";
				var returnVal = window.showModalDialog("../../../trunk/appl/factura/request_factura_desdeGd.php?cod_nota_venta="+ cod_nota_venta_value, "_blank", args2);
	 			if (returnVal == null)
	 				return false;
	 			else
	 			{	
	 				
	 				document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
		   			return true;
	 				
	 			}
 			}

   		
   		
	}
}

function change_fecha() {
	
	var fecha_nueva = document.getElementById('FECHA_FACTURA_0').value;
	
	document.getElementById('FECHA_FACTURA_I_0').innerHTML = fecha_nueva;
	document.getElementById('FECHA_FACTURA_P_0').innerHTML = fecha_nueva;
	document.getElementById('FECHA_FACTURA_C_0').innerHTML = fecha_nueva;
	
	/* valida que no ingrese un fecha vacia*/
	if(fecha_nueva == ''){
		alert('Debe ingresar la fecha de la Factura');
		return false;
	}
}

function validate() {
	var vl_cod_tipo_factura = document.getElementById('COD_TIPO_FACTURA_H_0').value;
	var K_TIPO_ARRIENDO = 2;
	
	if (vl_cod_tipo_factura != K_TIPO_ARRIENDO) {
		var aTR = get_TR('ITEM_FACTURA');
		if (aTR.length==0) {
			alert('Debe ingresar al menos 1 item antes de grabar.');
			return false;
		}
	}
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0'); 
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	if (document.getElementById('COD_FORMA_PAGO_0')){
		var cod_forma_pago = document.getElementById('COD_FORMA_PAGO_0').options[document.getElementById('COD_FORMA_PAGO_0').selectedIndex].value;
		var nom_forma_pago_otro = document.getElementById('NOM_FORMA_PAGO_OTRO_0').value;
		
		if (parseFloat(cod_forma_pago) == 1 && nom_forma_pago_otro == ''){
			alert ('Debe ingresar la Descripción de la forma de pago seleccionada.');
			document.getElementById('NOM_FORMA_PAGO_OTRO_0').focus();
			return false;
		}
	}	
	var porc_dscto1 = get_value('PORC_DSCTO1_0');
	var monto_dscto1 = get_value('MONTO_DSCTO1_0');
	var monto_dscto2 = get_value('MONTO_DSCTO2_0');
	var sum_total = document.getElementById('SUM_TOTAL_H_0');		
	var porc_dscto_max = document.getElementById('PORC_DSCTO_MAX_0');
	if (sum_total.value=='') sum_total.value = 0;
	if (monto_dscto1=='') monto_dscto2 = 0;
	if (monto_dscto2=='') monto_dscto2 = 0;
	if (((parseFloat(monto_dscto1) + parseFloat(monto_dscto2))/parseFloat(sum_total.value))*100 > parseFloat(porc_dscto_max.value)) {
		var monto_permitido = (parseFloat(sum_total.value) * parseFloat(porc_dscto_max.value)) / 100 ;
		alert('La suma de los descuentos es mayor al permitido (máximo '+number_format(porc_dscto_max.value, 0, ',', '.')+' % entre los dos descuentos, equivalente a '+number_format(monto_permitido, 0, ',', '.')+')');
		document.getElementById('PORC_DSCTO1_0').focus();
		return false;
	}
	var aTR = get_TR('BITACORA_FACTURA');
	for (var i = 0; i < aTR.length; i++){
		var tiene_compromiso = document.getElementById('TIENE_COMPROMISO_' + i).checked;
		if (tiene_compromiso == true){
			var fecha_compromiso = document.getElementById('FECHA_COMPROMISO_E_' + i).value;
			var hora_compromiso = document.getElementById('HORA_COMPROMISO_E_' + i).value;
			var glosa_compromiso = document.getElementById('GLOSA_COMPROMISO_E_' + i).value;
			if(fecha_compromiso == ''){
				alert('Debe ingresar la fecha del compromiso');
				return false;
			}
			else if (hora_compromiso == ''){
				alert('Debe ingresar la hora del compromiso');
				return false;
			}
			else if (glosa_compromiso == ''){
				alert('Debe ingresar la descripción del compromiso');
				return false;
			}
		}
	}
	return true;
}

function add_line_fa(ve_tabla_item, nomTabla) {
	var aTR = get_TR(ve_tabla_item);
	var VALOR_FA_H = document.getElementById('VALOR_FA_H_0').value;
	if (aTR.length >= VALOR_FA_H){
		alert('¡No se pueden agregar más ítems, se ha llegado al máximo permitido!');
		return false;
		}
	else
		add_line(ve_tabla_item,nomTabla);
}

function change_item_factura(ve_valor, ve_campo) {
	var record_item_f = get_num_rec_field(ve_valor.id);
	var item_value = document.getElementById('ITEM_' + record_item_f).value;
	var cod_producto_old = document.getElementById('COD_PRODUCTO_OLD_' + record_item_f);
	var cod_producto = document.getElementById('COD_PRODUCTO_' + record_item_f);
	if(ve_campo == 'COD_PRODUCTO' | ve_campo == 'NOM_PRODUCTO'){
		help_producto(ve_valor, 0);	
		if(cod_producto.value == 'T'){
			alert('No se pueden agregar Títulos.');
			if(cod_producto_old.value=='T'){ //es la primera vez que se ingresa el código
				document.getElementById('COD_PRODUCTO_' + record_item_f).value = '';
				document.getElementById('NOM_PRODUCTO_' + record_item_f).value = '';
			}
			else{
				cod_producto.value = cod_producto_old; 
				help_producto(cod_producto, 0); 
			}	
		}
	}	
}

// funcion que despliega un tipo texto si es que el cod_doc_sii='anulada' 
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

// funcion que despliega un tipo texto si es que la forma de pago =='otro'
function mostrarOcultar(ve_cod_forma_pago) {
	var cod_forma_pago = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].value; 	
	if (parseFloat(cod_forma_pago) == 1){
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");					
   
    }
    else{
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').value='';
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='hidden';
    	document.getElementById('AA').type='hidden';
  
    }
}

function change_forma_pago(ve_tipo_forma_pago, ve_cod_forma_pago) {
	if (ve_tipo_forma_pago == 'OTRO')  // forma de pago = OTRO
		var cant_docs = document.getElementById('CANTIDAD_DOC_FORMA_PAGO_OTRO_0').value;			
	else{
		mostrarOcultar(ve_cod_forma_pago);
		var cant_docs = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].label;
	}
}

function valida_ct_x_facturar(ve_campo) {
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var record = get_num_rec_field(ve_campo.id);
		var cant_por_facturar = to_num(document.getElementById('CANTIDAD_POR_FACTURAR_' + record).innerHTML);
		var cant_ingresada = to_num(ve_campo.value);
			if (parseFloat(cant_por_facturar) < parseFloat(cant_ingresada)) {
				alert('El valor ingresado no puede ser mayor que la cantidad "por Facturar": '+ number_format(cant_por_facturar, 1, ',', '.'));
				return number_format(cant_por_facturar, 1, ',', '.');
			}
			else
				return ve_campo.value;
	}
	else
		return ve_campo.value;
}

//////////////////////////
/// bitacora cobranza
function compromiso_realizado(ve_compromiso_realizado) {
	var vl_record = get_num_rec_field(ve_compromiso_realizado.id);
	if (ve_compromiso_realizado.checked) {
		var currentTime = new Date();
		var day = currentTime.getDate();
		var month = currentTime.getMonth() + 1;
		var year = currentTime.getFullYear();
		var hora = currentTime.getHours();
		var minuto = currentTime.getMinutes();
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = sprintf("%02d/%02d/%04d", day, month, year);
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = sprintf("%02d:%02d", hora, minuto);

		//Ajax para obtener el usuario actual
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_ini_usuario_actual.php", false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = resp;
	}
	else {
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = '';
	}
}
function tiene_compromiso(ve_tiene_compromiso) {
	var vl_record = get_num_rec_field(ve_tiene_compromiso.id);
	if (ve_tiene_compromiso.checked) {
		// hace entrables los campos
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).type = 'text'; 
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).style.display = 'none'; 
		
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).removeAttribute("disabled");
	}
	else {
		// inicializa en vacio todos los campos que quedan no entrables
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).innerHTML = '';
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).innerHTML = '';
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).value = '';
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).innerHTML = '';

		// si no tiene compromiso, deja no visibel Realizado	
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).setAttribute("disabled",0);
		document.getElementById("COMPROMISO_REALIZADO_" + vl_record).checked = false;
		document.getElementById('FECHA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('HORA_REALIZADO_' + vl_record).innerHTML = '';
		document.getElementById('INI_USUARIO_REALIZADO_' + vl_record).innerHTML = '';

		// deja no entrables los campos
		document.getElementById("FECHA_COMPROMISO_E_" + vl_record).type = 'hidden'; 
		document.getElementById("FECHA_COMPROMISO_S_" + vl_record).style.display = ''; 
		document.getElementById("HORA_COMPROMISO_E_" + vl_record).type = 'hidden' 
		document.getElementById("HORA_COMPROMISO_S_" + vl_record).style.display = ''; 
		document.getElementById("GLOSA_COMPROMISO_E_" + vl_record).type = 'hidden' 
		document.getElementById("GLOSA_COMPROMISO_S_" + vl_record).style.display = ''; 
	}
}
function change_protected(ve_campo) {
	var vl_record = get_num_rec_field(ve_campo.id);
	var vl_field = get_nom_field(ve_campo.id);
	document.getElementById(vl_field.substr(0, vl_field.length - 1) + "S_" + vl_record).innerHTML = ve_campo.value;
}

function change_item_factura_anticipo(ve_cod_producto) {
	ve_cod_producto.value = ve_cod_producto.value.toUpperCase();
	if (ve_cod_producto.value != 'TE') {
		alert('Debe ingresar el anticipo como TE');
		ve_cod_producto.value = 'TE';
	}
	help_producto(ve_cod_producto, 0);	
}
