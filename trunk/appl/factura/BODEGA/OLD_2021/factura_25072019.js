function dlg_print_dte_wo(ve_control, ve_cod_factura){
	var vl_rec = get_num_rec_field(ve_control.id);
	if(ve_cod_factura > 12833){
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
					input.setAttribute("name", "b_printDTE_"+vl_rec+"_x");
					input.setAttribute("id", "b_printDTE_"+vl_rec+"_x");
					document.getElementById("output").appendChild(input);
								
					document.getElementById('wo_hidden2').value = returnVal;
					document.output.submit();
					return true;
				}
			}
		});
	}else{
		var input = document.createElement("input");
		input.setAttribute("type", "hidden");
		input.setAttribute("name", "b_printDTE_"+vl_rec+"_x");
		input.setAttribute("id", "b_printDTE_"+vl_rec+"_x");
		document.getElementById("output").appendChild(input);
		
		document.output.submit();
		return true;
	}
}

function dlg_crear_desde(ve_prompt, ve_valor){
	var url = "../../../trunk/appl/factura/BODEGA/dlg_crear_desde.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 360,
		 width: 360,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else {
			 	var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_crear_desde_x");
				input.setAttribute("id", "b_crear_desde_x");
				document.getElementById("output").appendChild(input);
				
				document.getElementById('wo_hidden').value = returnVal;
				document.output.submit();
		   		
		   		return true;
		   	}	
		}
	});	
}
function select_1_empresa(valores, record) {
	if(valores[1] != '9'){
		set_values_empresa(valores, record);
	}else{
		alert('Usted no puede generar una factura para: BIGGI CHILE SOC LTDA.\n\nFavor asegúrese de indicar el cliente correcto de esta factura');
		set_value('COD_EMPRESA_' + record, '', '');
		set_value('RUT_' + record, '', '');
		set_value('ALIAS_' + record, '', '');
		set_value('NOM_EMPRESA_' + record, '', '');
		set_value('DIG_VERIF_' + record, '', '');
		set_value('DIRECCION_FACTURA_' + record, '', '');
		set_value('DIRECCION_DESPACHO_' + record, '', '');
		set_value('GIRO_' + record, '', '');
		set_value('SUJETO_A_APROBACION_' + record, '', '');
		set_drop_down_vacio('COD_SUCURSAL_FACTURA_' + record);
		set_drop_down_vacio('COD_SUCURSAL_DESPACHO_' + record);
		set_drop_down_vacio('COD_PERSONA_' + record);
		set_value('MAIL_CARGO_PERSONA_' + record, '', '');
		set_value('COD_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NOM_CUENTA_CORRIENTE_' + record, '', '');
		set_value('NRO_CUENTA_CORRIENTE_' + record, '', '');
	}
}

function select_1_producto(valores, record) {
	// para BODEGA se debe usar el precio INTERNO
	var ajax = nuevoAjax();
	var vl_cod_producto_value = URLEncode(valores[1]);
	ajax.open("GET", "BODEGA/ajax_producto_precio.php?cod_producto="+vl_cod_producto_value, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	valores[3] = resp; 
	set_values_producto(valores, record);
	
	if(document.getElementById('CANTIDAD_' + record).value != '')
		valida_cantidad(document.getElementById('COD_PRODUCTO_' + record));
}
function valida_ct_x_facturar(ve_campo) {
	// SE REEMPLZA LA ORIGINAL PARA ADICIONAR QUE VALIDE EL STOCK
	 
	// valida solo si la GD es creada desde
	var cod_doc = to_num(document.getElementById('COD_DOC_0').innerHTML);
	
	if (cod_doc != 0){
		var vl_error = false;
		var record = get_num_rec_field(ve_campo.id);
		var cant_ingresada = to_num(ve_campo.value);
		var cant_por_facturar = to_num(document.getElementById('CANTIDAD_POR_FACTURAR_' + record).innerHTML);
		if (parseFloat(cant_por_facturar) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que la cantidad "por Facturar": '+ number_format(cant_por_facturar, 1, ',', '.'));
			cant_ingresada = cant_por_facturar;
			vl_error = true;
		}

		var ajax = nuevoAjax();
		var vl_cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
		ajax.open("GET", "BODEGA/ajax_producto_stock.php?cod_producto="+vl_cod_producto_value, false);
		ajax.send(null);		

		var vl_stock = ajax.responseText;
		if (parseFloat(vl_stock) < parseFloat(cant_ingresada)) {
			alert('El valor ingresado no puede ser mayor que el stock actual: '+ number_format(vl_stock, 1, ',', '.'));
			cant_ingresada = vl_stock;
			vl_error = true;
		}


		if (vl_error)
			return cant_ingresada;
		else
			return ve_campo.value;
	}
	else
		return ve_campo.value;
}

function valida_cantidad(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_cod_item_doc = document.getElementById('COD_ITEM_DOC_' + vl_record).value;
	var vl_cod_item_fac = document.getElementById('COD_ITEM_FACTURA_' + vl_record).value;
	var vl_ws_origen = document.getElementById('WS_ORIGEN_0').value;
	var vl_alert = '';
	var vl_cantidad_total = 0;
	
	var vl_cod_producto = get_value('COD_PRODUCTO_'+vl_record);
	var aTR = get_TR('ITEM_FACTURA');
	for (var k = 0; k < aTR.length; k++){
		var vl_rec = get_num_rec_field(aTR[k].id);
		var vl_producto_it = get_value('COD_PRODUCTO_'+vl_rec);
		if(vl_cod_producto == vl_producto_it){
			var vl_cantidad = get_value('CANTIDAD_'+vl_rec);
			if(vl_cantidad == '')
				vl_cantidad = 0;
			
			vl_cantidad_total = parseInt(vl_cantidad_total) + parseInt(vl_cantidad);
		}
	}
	
	if(vl_cod_producto != ''){
		var vl_ajax = nuevoAjax();
		vl_ajax.open("GET", "../factura/BODEGA/ajax_valida_stock.php?cod_producto="+vl_cod_producto+"&cantidad="+vl_cantidad_total, false);
		vl_ajax.send(null);		
		var resp = vl_ajax.responseText;
		if(resp == 'ALERTA_MAYOR_CANTIDAD')
			alert('Este item se le esta ingresando una cantidad mayor a lo disponible');
		else if(resp == 'MAYOR_CANTIDAD'){
			alert('Este item se le esta ingresando una cantidad mayor a lo disponible, Favor de ingresar una cantidad correcta');
			document.getElementById('CANTIDAD_' + vl_record).value = 0;
			return;
		}else if(resp == 'ALERTA_NO_TIENE_STOCK')
			alert('Este producto no tiene stock disponible para facturar.\nSin embargo, usted está autorizado para facturar sin stock.');
		else if(resp == 'NO_TIENE_STOCK'){
			alert('Este producto no tiene stock disponible para facturar.\nUsted NO está autorizado para facturar sin stock.');
			document.getElementById('CANTIDAD_' + vl_record).value = 0;
			return;
		}
	}
	
	if(vl_ws_origen == 'TODOINOX' || vl_ws_origen == 'COMERCIAL' || vl_ws_origen == 'RENTAL'){
		var vl_cod_oc = document.getElementById('NRO_ORDEN_COMPRA_0').innerHTML;
		var vl_ajax = nuevoAjax();
		vl_ajax.open("GET", "../factura/BODEGA/ajax_valida_cantidad.php?cantidad="+vl_cantidad+"&cod_item_doc="+vl_cod_item_doc+"&cod_item_factura="+vl_cod_item_fac+"&cod_orden_compra="+vl_cod_oc+"&ws_origen="+vl_ws_origen+"&cod_producto="+vl_cod_producto, false);
		vl_ajax.send(null);		
		var resp = vl_ajax.responseText;
		resp = resp.split('|');
		
		if(resp[0] == 'STOCK'){
			alert('La cantidad ingresada es mayor a la cantidad en stock.\nUsted NO está autorizado para facturar sin stock., Favor de ingresar una cantidad correcta');
			document.getElementById('CANTIDAD_' + vl_record).value = 0;
			return;
		}
		
		if(resp[0] == 'ES_MAYOR'){
			alert('No puede ingresar una cantidad superior a la cantidad indicada en la orden de compra.\n\nCantidad indicada según OC: '+resp[1]);
			document.getElementById('CANTIDAD_' + vl_record).value = 0;
			return;
		}
	}
}

function validate() {
	var vl_aTR = get_TR('ITEM_FACTURA');
	var vl_count = 0;
	for(j=0 ; j < vl_aTR.length ; j++){
		var vl_cantidad = get_value('CANTIDAD_'+j);
		if(vl_cantidad != 0)
			vl_count++;
	}
	
	//Validacion de los item (Segun parametro 29)
	var ajax = nuevoAjax();
	ajax.open("GET", "../factura/BODEGA/ajax_valida_item.php?cantidad="+vl_count, false);
	ajax.send(null);
	var resp = ajax.responseText;
	
	if(resp == 'ALERTA'){
		alert('ERROR: No puede ingresar mas de 18 item en una Factura.');
		return false;
	}

	var vl_cod_tipo_factura = document.getElementById('COD_TIPO_FACTURA_H_0').value;
	var K_TIPO_ARRIENDO = 2;
	
	if (vl_cod_tipo_factura != K_TIPO_ARRIENDO) {
		if (vl_aTR.length==0) {
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
	
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
	var vl_orden_compra = document.getElementById('NRO_ORDEN_COMPRA_0').value;
	var vl_fecha_orden_compra = document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value;

	if (!vl_no_tiene_OC.checked) {
		if(vl_orden_compra == ''){
			alert('Debe Ingresar Orden Compra Cliente');
			return false;
		}
		
		if(vl_fecha_orden_compra == ''){
			alert('Debe Ingresar Fecha Orden Compra Cliente');
			return false;
		}
	}	
	//valida campos  de observaciones
	var retirado_por = document.getElementById('RETIRADO_POR_0').value;
	var rut_rp = document.getElementById('RUT_RETIRADO_POR_0').value;
	var df_rp =document.getElementById('DIG_VERIF_RETIRADO_POR_0').value;
	var gia_trans =document.getElementById('GUIA_TRANSPORTE_0').value;
	var patente =document.getElementById('PATENTE_0').value;
		
	if((retirado_por == "") || (rut_rp == "") || (df_rp == "")|| (gia_trans == "")|| (patente == "") ){
		if((retirado_por == "") && (rut_rp == "") && (df_rp == "")&& (gia_trans == "")&& (patente == "") ){
			return true;
		}else{
			alert('Debe ingresar los datos de los campos RETIRA, RUT, GUIA TRANSPORTE, PATENTE en el área de observaciones.Si no desea especificarlos, debe dejarlos todos en blanco');
			return false;
		}			
	}else{
		return true;
	}
	
	return true;
}

function f_valida_oc(){
	var vl_no_tiene_OC = document.getElementById('NO_TIENE_OC_0');
		
	if (vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').value = '';
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = true;
		
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').value = '';
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = true;
				
	}
	if (!vl_no_tiene_OC.checked) {
		document.getElementById('NRO_ORDEN_COMPRA_0').readOnly = false;
		document.getElementById('FECHA_ORDEN_COMPRA_CLIENTE_0').readOnly = false;
	}
}

$(document).ready(function () {
	$('#NRO_ORDEN_COMPRA_0').keypress(function (e) {
	    var regex =  new RegExp("^[a-zA-Z0-9\/-]+$");
	    var str = String.fromCharCode(!e.charCode ? e.which : e.charCode);
	    if (regex.test(str)) {
	        return true;
	    }

	    e.preventDefault();
	    return false;
	});
});