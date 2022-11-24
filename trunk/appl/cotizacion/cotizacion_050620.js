function dlg_print() {
	var tipo_dispositivo = document.getElementById('TIPO_DISPOSITIVO_0').innerHTML;
	if(tipo_dispositivo == 'IPAD'){
    	alert('Resumen Cotizacion');
    }else{
	
		var url = "dlg_print_cotizacion.php?cod_cotizacion="+document.getElementById('COD_COTIZACION_H_0').value;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 290,
			 width: 500,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	if (returnVal == null){		
					return false;
				}			
				else {
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "b_print_x");
					input.setAttribute("id", "b_print_x");
					document.getElementById("input").appendChild(input);
					
					document.getElementById('wi_hidden').value = returnVal;
					document.input.submit();
			   		return true;
				}
			}
		});	
	}		
}
function validate() {
	ve_cod_empresa = document.getElementById('COD_EMPRESA_0');
	ve_cod_usuario1 = document.getElementById('COD_USUARIO_VENDEDOR1_0').value;
	ve_cod_usuario2 = document.getElementById('COD_USUARIO_VENDEDOR2_0').value;
	 	
 	var ajax = nuevoAjax();
	ajax.open("GET", "../cotizacion/get_vendedor.php?cod_empresa="+URLEncode(ve_cod_empresa.value), false);
	ajax.send(null);
		
	var resp = ajax.responseText.split('|');
	var cod_usuario_empresa = resp[0]; 
	var nom_usuario_empresa = resp[1];
	
	/*
	VMC, 7-01-2011
	se elimina el envio de mail cuando se cotiza a un cliente no asignado 
	
	
	if(ve_cod_usuario1 != cod_usuario_empresa && ve_cod_usuario2 != cod_usuario_empresa)   
	{			
		if(!confirm("Este Cliente esta asignado a: "+nom_usuario_empresa+", esta cotización será informada a "+nom_usuario_empresa+".\n \n ¿Desea Continuar?")){
			document.getElementById('COD_USUARIO_VENDEDOR1_0').focus();
			return false;
		}   				
	}
	*/
	
	var aTR = get_TR('BITACORA_COTIZACION');
	for (var i = 0; i < aTR.length; i++){
	
			var tiene_compromiso = document.getElementById('BC_TIENE_COMPROMISO_' + i).checked;
		
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
		
	return validate_cot_nv('ITEM_COTIZACION');
}

function add_line_item(tabla_id, nom_tabla) {
	var row = add_line(tabla_id, nom_tabla);
	var aTR = get_TR('ITEM_COTIZACION');
	var item = 1;
	var letra = '';
	for (var i=aTR.length - 2; i >=0; i--, item++) {
		var cod_producto_value = document.getElementById('COD_PRODUCTO_' + get_num_rec_field(aTR[i].id)).value
		if (cod_producto_value=='T') {
			letra = document.getElementById('ITEM_' + get_num_rec_field(aTR[i].id)).value; 
			break;
		}
	}	
	document.getElementById('ITEM_' + row).value = letra + item;
}

function get_precio_compra(ve_cod_proveedor) {
	var sc_precio_compra_value = ve_cod_proveedor.options[ve_cod_proveedor.selectedIndex].label; 
	var record = get_num_rec_field(ve_cod_proveedor.id);
	set_value('PRECIO_COMPRA_' + record, sc_precio_compra_value, number_format(sc_precio_compra_value, 0, ',', '.'));
}

function mostrarOcultar(ve_cod_forma_pago) {
	var cod_forma_pago = ve_cod_forma_pago.options[ve_cod_forma_pago.selectedIndex].value; 	
	if (parseFloat(cod_forma_pago) == 1){
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='text';
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onblur', "this.style.border=''");				
		document.getElementById('NOM_FORMA_PAGO_OTRO_0').setAttribute('onfocus', "this.style.border='1px solid #FF0000'");				
    }
    else{
    	document.getElementById('NOM_FORMA_PAGO_OTRO_0').type='hidden';
    }
}
function request_crear_desde(ve_prompt,ve_valor){
	var url = "../../../../commonlib/trunk/php/request.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 140,
		 width: 400,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	
		 	if (returnVal == null){		
				return false;
			}			
			else {
				var ajax = nuevoAjax();
				ajax.open("GET", "ajax_que_precio_usa.php?regreso="+returnVal, false);
				ajax.send(null);
	
				var vl_resp = ajax.responseText;
				var resp = vl_resp.split('|');
				var cod_cotizacion = resp[0];
				var cambio_precio = resp[1];
				if(cambio_precio == 'SI'){
					var url = "../common_appl/que_precio_usa.php?cod_cotizacion="+cod_cotizacion;
					$.showModalDialog({
						 url: url,
						 dialogArguments: '',
						 height: 330,
						 width: 710,
						 scrollable: false,
						 onClose: function(){ 
						 	var returnVal2 = this.returnValue;
							 	if (returnVal2=='1'){
							 		var ajax = nuevoAjax();
									ajax.open("GET", "../common_appl/setear_sesion.php?", false);
									ajax.send(null);
							 	}
						 	var input = document.createElement("input");
							input.setAttribute("type", "hidden");
							input.setAttribute("name", "b_create_x");
							input.setAttribute("id", "b_create_x");
							document.getElementById("output").appendChild(input);
							
							document.getElementById('wo_hidden').value = returnVal;
							document.output.submit();
					   		return true;
						}
					});	
				}else{
					var input = document.createElement("input");
					input.setAttribute("type", "hidden");
					input.setAttribute("name", "b_create_x");
					input.setAttribute("id", "b_create_x");
					document.getElementById("output").appendChild(input);
					
					document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
			   		return true;
			   	}	
			}
		}
	});	
}
