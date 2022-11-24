function validate_cot_nv(ve_tabla) {
	var cod_usuario_vend2 = document.getElementById('COD_USUARIO_VENDEDOR2_0');
	var porc_vendedor2 = document.getElementById('PORC_VENDEDOR2_0');
	if (cod_usuario_vend2.value != ''&& (porc_vendedor2.value =='' || porc_vendedor2.value ==0)) {
		alert('Debe ingresar "Porcentaje a Vendedor 2" antes de grabar.');
		porc_vendedor2.focus();
		return false;
	}
	else if (cod_usuario_vend2.value == ''&& porc_vendedor2.value != '' && porc_vendedor2.value != 0) {
		alert('Debe ingresar "Vendedor 2" antes de grabar.');
		cod_usuario_vend2.focus();
		return false;
	}
	if (porc_vendedor2=='') porc_vendedor2 = 0
	var porc_vendedor1 = document.getElementById('PORC_VENDEDOR1_0');
	var porc_max_vendedor1 = roundNumber(document.getElementById('COD_USUARIO_VENDEDOR1_0').options[document.getElementById('COD_USUARIO_VENDEDOR1_0').selectedIndex].label, 0);
	
	if (parseFloat(porc_vendedor1.value) + parseFloat(porc_vendedor2.value) > parseFloat(porc_max_vendedor1)) {
		alert('La suma de la comisión del "Vendedor1" más "Vendedor2", no puede ser mayor que la autorizada al vendedor1 (máximo'+number_format(porc_max_vendedor1, 0, ',', '.')+' %)');
		porc_vendedor2.focus();
		return false;
	}
	var monto_dscto1 = get_value('MONTO_DSCTO1_0');
	if (monto_dscto1=='') monto_dscto1 = 0
	var monto_dscto2 = get_value('MONTO_DSCTO2_0');
	if (monto_dscto2=='') monto_dscto2 = 0
	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		var sum_total = document.getElementById('SUM_TOTAL_H_0');		
		//var porc_dscto_max = document.getElementById('PORC_DSCTO_MAX_0');
		var porc_dscto_max = document.getElementById('PORC_DESC_PERMITIDO_0');
	
		if (sum_total.value=='') sum_total.value = 0	
			
		var cod_estado_nota_venta = get_value('COD_ESTADO_NOTA_VENTA_H_0'); 
		if (to_num(cod_estado_nota_venta) != 3){	
			if (((parseFloat(monto_dscto1) + parseFloat(monto_dscto2))/parseFloat(sum_total.value))*100 > parseFloat(porc_dscto_max.value)){
				
				var vl_cod_nota_venta = get_value('COD_NOTA_VENTA_H_0');
				if(vl_cod_nota_venta != ''){
					var ajax = nuevoAjax();
					ajax.open("GET", "../common_appl/ajax_porc_desc_esp.php?cod_nota_venta="+vl_cod_nota_venta+"&porc_dscto_max="+porc_dscto_max.value, false);
					ajax.send(null);
					var resp = ajax.responseText;
					
					if(resp != 'PERMITIDO'){
						var monto_permitido = (parseFloat(sum_total.value) * parseFloat(porc_dscto_max.value)) / 100 ;
						alert('La suma de los descuentos es mayor al permitido (máximo '+number_format(porc_dscto_max.value, 1, ',', '.')+' % entre los dos descuentos, equivalente a '+number_format(monto_permitido, 0, ',', '.')+')');
						document.getElementById('PORC_DSCTO1_0').focus();
						return false;
					}	
				}else{
					var monto_permitido = (parseFloat(sum_total.value) * parseFloat(porc_dscto_max.value)) / 100 ;
					alert('La suma de los descuentos es mayor al permitido (máximo '+number_format(porc_dscto_max.value, 1, ',', '.')+' % entre los dos descuentos, equivalente a '+number_format(monto_permitido, 0, ',', '.')+')');
					document.getElementById('PORC_DSCTO1_0').focus();
					return false;
				}
			}
		}
	}
	
	if (ve_tabla == 'ITEM_COTIZACION'){
		var sum_total = document.getElementById('SUM_TOTAL_H_0');	
		var porc_dscto_max = document.getElementById('PORC_DSCTO_MAX_0');
		
		if (sum_total.value=='') sum_total.value = 0;
		
		var porc_monto_dscto = ((parseFloat(monto_dscto1) + parseFloat(monto_dscto2))/parseFloat(sum_total.value)) * 100;
		var monto_permitido = (parseFloat(sum_total.value) * parseFloat(porc_dscto_max.value)) / 100 ;
		
		if(parseFloat(porc_monto_dscto) > parseFloat(porc_dscto_max.value)){
			alert('La suma de los descuentos es mayor al permitido (máximo '+number_format(porc_dscto_max.value, 1, ',', '.')+' % entre los dos descuentos, equivalente a '+number_format(monto_permitido, 0, ',', '.')+')');
			document.getElementById('PORC_DSCTO1_0').focus();
			return false;
		}
	}
	
	var cod_forma_pago = document.getElementById('COD_FORMA_PAGO_0').options[document.getElementById('COD_FORMA_PAGO_0').selectedIndex].value;
	var nom_forma_pago_otro = document.getElementById('NOM_FORMA_PAGO_OTRO_0').value;
	
	if (parseFloat(cod_forma_pago) == 1 && nom_forma_pago_otro == ''){
		alert ('Debe ingresar la Descripción de la forma de pago seleccionada.');
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').focus();
		return false;
	}	
	
	var aTR = get_TR(ve_tabla);
	if (aTR.length==0) {
		alert('Debe ingresar al menos 1 item antes de grabar.');
		return false;
	}
	var total_neto = document.getElementById('TOTAL_NETO_H_0');
	if (parseFloat(total_neto.value) < 0) {
		alert('Los descuentos no pueden superar el monto vendido.');
		document.getElementById('MONTO_DSCTO1_0').focus();
		return false;
	}		
	
	///revisar porque se debería validar tambien para NV, pero falla esta validación al hacer el cierre
	if (ve_tabla == 'ITEM_COTIZACION'){
		var aTR_cot = get_TR(ve_tabla);
		for(var i = 0; i < aTR_cot.length; i++){
			var record = get_num_rec_field(aTR_cot[i].id);
			var precio = get_value('PRECIO_H_' + record);
			var producto = get_value('COD_PRODUCTO_' + record);
			if(producto != 'T'){	
				if(precio == 0){
					alert('El precio del Producto no puede ser $0');	
					document.getElementById('COD_PRODUCTO_' + record).focus();		
					return false;
				}		
			}	
		}
	}
	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		var total_monto_doc = to_num(document.getElementById('SUM_MONTO_DOC_H_H_0').value);
		var total_con_iva = to_num(document.getElementById('STATIC_TOTAL_CON_IVA2_H_0').value);
		var es_por_definir = true;
		var aTR = get_TR('DOC_NOTA_VENTA');
		for (var i = 0; i < aTR.length; i++) {
			var record = get_num_rec_field(aTR[i].id);
			var tipo_doc_pago = document.getElementById('COD_TIPO_DOC_PAGO_'+record).options[document.getElementById('COD_TIPO_DOC_PAGO_'+record).selectedIndex].value;
			if (to_num(tipo_doc_pago) != 8) //distinto a "por definir"
				var es_por_definir = false;
		}
		if (es_por_definir == true){
			var sum_monto_doc = 0;
			var aTR = get_TR('DOC_NOTA_VENTA');
			for (var i = 0; i < aTR.length; i++){
				var record = get_num_rec_field(aTR[i].id);
				if (i == aTR.length - 1){//es el utilimo documento y para el caso del total c/iva impar debe forzar cuadratura en caso que sean dos pagos
					var monto_doc = total_con_iva - sum_monto_doc;
					var monto_doc = Math.abs(monto_doc);
					document.getElementById('MONTO_DOC_'+i).value = monto_doc;
				}else{
					var factor = total_con_iva / total_monto_doc;
					var old_monto_doc = to_num(document.getElementById('MONTO_DOC_'+i).value);
					var new_monto_doc = old_monto_doc * factor;	
					document.getElementById('MONTO_DOC_'+i).value = roundNumber(new_monto_doc, 0);
					var sum_monto_doc = sum_monto_doc + roundNumber(new_monto_doc, 0);
				}
			}	
		}else{			
			var aTR = get_TR('DOC_NOTA_VENTA');
			var total_doc = 0;
			var total_con_iva = document.getElementById('TOTAL_CON_IVA_H_0').value;
			for (var i = 0; i < aTR.length; i++) {
				var record = get_num_rec_field(aTR[i].id);
				total_doc = parseInt(total_doc) + parseInt(document.getElementById('MONTO_DOC_'+ record).value);
			}
			if (total_doc != total_con_iva){
				alert("El total de Pagos, es distinto al total c/IVA. \n \nTotal Pago: "+number_format(total_doc, 0, ',', '.')+"\nTotal Nota Venta c/IVA: "+number_format(total_con_iva, 0, ',', '.'));	
				return false;
			}
		}
	}

	var aTR = get_TR('DOC_NOTA_VENTA');
	for (var i = 0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		
		var cod_tipo_doc_pago = document.getElementById('COD_TIPO_DOC_PAGO_'+ record).value;
		var fecha_doc = document.getElementById('FECHA_DOC_'+ record).value;
		var nro_doc = document.getElementById('NRO_DOC_'+ record).value;
		var cod_banco = document.getElementById('COD_BANCO_'+ record).value;
		var cod_plaza = document.getElementById('COD_PLAZA_'+ record).value;
		var monto_doc = document.getElementById('MONTO_DOC_'+ record).value;		
		
		if (cod_tipo_doc_pago == ''){
			alert ("Debe ingresar el Tipo de Documento.");
			document.getElementById('COD_TIPO_DOC_PAGO_'+ record).focus();
			return false;
		}
		else if(fecha_doc == '' && cod_tipo_doc_pago != 8){
			alert ("Debe ingresar la Fecha de pago.");
			document.getElementById('FECHA_DOC_'+ record).focus();
			return false;
		}
		else if (monto_doc == ''){
			alert ("Debe ingresar el Monto del Documento.");
			document.getElementById('MONTO_DOC_'+ record).focus();
			return false;			
		}
		else if (cod_tipo_doc_pago != 1 && cod_tipo_doc_pago != 8){ //forma de pago <> efectivo y por definir
			if (nro_doc == 0){
				alert ("Debe ingresar el Número de Documento.");
				document.getElementById('NRO_DOC_' + record).focus();
				return false;			
			}
			else if (cod_banco == ''){
				alert ("Debe ingresar el Banco.");
				document.getElementById('COD_BANCO_' + record).focus();
				return false;			
			}
			else if (cod_plaza == ''){
				alert ("Debe ingresar la Plaza.");
				document.getElementById('COD_PLAZA_' + record).focus();
				return false;				
			}
		}	
	}// end for

	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		var cod_estado_nota_venta = get_value('COD_ESTADO_NOTA_VENTA_H_0'); 
		if (to_num(cod_estado_nota_venta) == 3){	
			var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
			if (motivo_anula.value == '') {
				alert('Debe ingresar el motivo de Anulación antes de grabar.');
				motivo_anula.focus();
				return false;
			}
		}
	}	

	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		
		var valida_motivo = document.getElementById('VALIDA_MOTIVO_CIERRE_H_0').value;
		if (valida_motivo == 'S'){
			var aTR = get_TR('TIPO_PENDIENTE_NOTA_VENTA');
			for (var i = 0; i < aTR.length; i++){
				var autoriza = document.getElementById('AUTORIZA_' + i).checked;
				var motivo = document.getElementById('MOTIVO_AUTORIZA_' + i).value;
				if (autoriza == true && motivo == ' '){
					alert('¡Debe ingresar el motivo para autorizar el cierre de la Nota de Venta!');
					document.getElementById('MOTIVO_AUTORIZA_' + i).focus();
					return false;
				}
			}	
		}
	}
		
	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		var cod_usuario_confirma = to_num(document.getElementById('COD_USUARIO_CONFIRMA_H_0').value);
		if(cod_usuario_confirma == ''){//solo se valida la primera vez que se confirma
			var cod_estado_nota_venta = to_num(document.getElementById('COD_ESTADO_NOTA_VENTA_H_0').value);		
			if (cod_estado_nota_venta == 4){ //cod_estado_nota_venta = '4'-> confirmada 
				var total_compra = 0;
				var aTR = get_TR('PRE_ORDEN_COMPRA');
				for (var i = 0; i < aTR.length; i++){
					genera_compra = document.getElementById('CC_GENERA_COMPRA_'+ i).checked;
					if(genera_compra == true){
						total_compra = total_compra + 1;
						
						// Si no existe el control es porque estaba confirmada de antes y en load_record no se creo el control
						cod_proveedor = document.getElementById('CC_COD_PROVEEDOR_' + i);
						if (cod_proveedor) {
							if(cod_proveedor.value == ''){
								alert('¡Hay registros de Pre-Orden Compra sin proveedor.\n \n Para confirmar la nota de venta debe asociar las compras a un proveedor!');
								document.getElementById('CC_COD_PROVEEDOR_' + i).focus();
								return false;
							}
						}
					}			
				}
				//no se marcan items para comprar
				if (total_compra == 0){
					alert('¡No se puede confirmar la nota de venta, ninguna Pre-Orden Compra está marcada para generar compras!');
					return false;
				}
			}
		}
	}
	
	if (ve_tabla == 'ITEM_NOTA_VENTA'){
		var cod_estado_nota_venta = get_value('COD_ESTADO_NOTA_VENTA_H_0'); 
		if (to_num(cod_estado_nota_venta) == 4){
			var aTR = get_TR('ITEM_NOTA_VENTA');
			for (var i = 0; i < aTR.length; i++){
				var cod_producto = document.getElementById('COD_PRODUCTO_' + i);
				if (cod_producto){
					var motivo_autoriza_te = document.getElementById('MOTIVO_AUTORIZA_TE_' + i).value;
					if(cod_producto.value == 'TE' && motivo_autoriza_te == ' '){
						alert("¡Hay ítems en la nota de venta sin autorización de 'TE'.\n \n Para confirmar la nota de venta debe autorizar todos los 'TE'!");
						document.getElementById('COD_ESTADO_NOTA_VENTA_0').selectedIndex = 1;
						document.getElementById('BOTON_PRECIO_' + i).focus();
						return false;
					}
				}				
			}
		}
	}
	
	return true;
}
function change_vendedor(ve_cod_usuario_vendedor) {
	var vl_label = ve_cod_usuario_vendedor.options[ve_cod_usuario_vendedor.selectedIndex].label.split('-');
	var vl_porc = vl_label[0]; 
	if (ve_cod_usuario_vendedor.id=='COD_USUARIO_VENDEDOR1_0') {
		document.getElementById('PORC_VENDEDOR1_0').value = number_format(vl_porc, 2,',','.');
		document.getElementById('PORC_DSCTO_MAX_0').value = vl_label[1];				
	}
	else {
		document.getElementById('PORC_VENDEDOR2_0').value = number_format(vl_porc, 2,',','.');
	}
}
function change_porc_vendedor(ve_porc_vendedor) {
	var vl_nom_porc = get_nom_field(ve_porc_vendedor.id);
	if (vl_nom_porc=='PORC_VENDEDOR1')
		var vl_cod_usuario_vendedor = document.getElementById('COD_USUARIO_VENDEDOR1_0');
	else
		var vl_cod_usuario_vendedor = document.getElementById('COD_USUARIO_VENDEDOR2_0');
	var vl_label = vl_cod_usuario_vendedor.options[vl_cod_usuario_vendedor.selectedIndex].label.split('-');
	var vl_porc = vl_label[0]; 
	
	return Math.min(to_num(ve_porc_vendedor.value), vl_porc);
}