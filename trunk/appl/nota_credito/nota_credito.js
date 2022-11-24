function request_nota_credito(ve_prompt, ve_valor) 
{	
	var url = "../../../trunk/appl/nota_credito/request_nota_credito.php?prompt="+URLEncode(ve_prompt)+"&valor="+URLEncode(ve_valor);
	$.showModalDialog({
		 url: url,
		 dialogArguments: '',
		 height: 280,
		 width: 320,
		 scrollable: false,
		 onClose: function(){ 
		 	var returnVal = this.returnValue;
		 	if (returnVal == null)		
				return false;		
			else 
			{		
				var input = document.createElement("input");
				input.setAttribute("type", "hidden");
				input.setAttribute("name", "b_create_x");
				input.setAttribute("id", "b_create_x");
				document.getElementById("output").appendChild(input);
			
				 var dato = returnVal.split("|"); 
				 var nro_docto= dato[1];
				 var opcion= dato[0];
				 
				if (opcion == 'desde_fa' || opcion == 'desde_fa_adm'){
					document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
					return true;
				}else if (opcion == 'desde_gr'){ 
					document.getElementById('wo_hidden').value = returnVal;
					document.output.submit();
		  			return true;
		  		}
			}
		}
	});	
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

function validate(){
	var cod_estado_doc_sii_value = get_value('COD_ESTADO_DOC_SII_0');
	var vl_cod_tipo_nc_interno_sii = get_value('COD_TIPO_NC_INTERNO_SII_0');
	var vl_cod_tipo_nota_credito = get_value('COD_TIPO_NOTA_CREDITO_0');
		
	if(vl_cod_tipo_nota_credito == 0){
		alert('Debe ingresar "Tipo NC Interno SII" antes de grabar.');
		return false
	}
	
	// cod_estado_doc_sii_value = 1 = emitida
	if (to_num(cod_estado_doc_sii_value) == 1){
		var aTR = get_TR('ITEM_NOTA_CREDITO');
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
	// cod_estado_doc_sii_value = 4 = anulada
	if (to_num(cod_estado_doc_sii_value) == 4){	
		var motivo_anula = document.getElementById('MOTIVO_ANULA_0');
		if (motivo_anula.value == '') {
			alert('Debe ingresar el motivo de Anulación antes de grabar.');
			motivo_anula.focus();
			return false;
		}
	}
	
	if(vl_cod_tipo_nc_interno_sii == 3){//Devolución Factura Parcial productos
		var aTR		 = get_TR('ITEM_NOTA_CREDITO');
		var vl_total = 0;
		var vl_nc_total = 0;
		for(i=0 ; i < aTR.length ; i++){
			var vl_cantidad = get_value('CANTIDAD_'+ i);
			var vl_cantidad_nc = get_value('CANTIDAD_POR_NC_'+ i);
			vl_total = parseFloat(vl_total) + parseFloat(vl_cantidad);
			vl_nc_total = parseFloat(vl_nc_total) + parseFloat(vl_cantidad_nc);
		}
		
		vl_val_cantidad = ((parseFloat(vl_total) * 100) / parseFloat(vl_nc_total))
		
		if(vl_val_cantidad >= 100){
			alert('No puede facturar las cantidades en 100%');
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
	
	vl_precio = findAndReplace(vl_precio, '.', '');	// borra los puntos en los miles
	set_value('PRECIO_' + record, vl_precio, vl_precio);
}
function change_item_nc_adm(ve_cod_producto) {
	ve_cod_producto.value = ve_cod_producto.value.toUpperCase();
	if (ve_cod_producto.value != 'TE') {
		alert('Debe ingresar la NC como TE');
		ve_cod_producto.value = 'TE';
	}
	help_producto(ve_cod_producto, 0);	
}

function change_tipo_motivo_nc(){
	var vl_cod_tipo_nc_interno_sii = get_value('COD_TIPO_NC_INTERNO_SII_0');
	var aTR	 = get_TR('ITEM_NOTA_CREDITO');
	if(vl_cod_tipo_nc_interno_sii != ''){
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_change_tipo_motivo_nc.php?cod_tipo_nc_interno_sii="+vl_cod_tipo_nc_interno_sii, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");
		
		var vl_cod_tipo_nota_credito = result[0]['COD_TIPO_NOTA_CREDITO'];
		var vl_nom_tipo_nota_credito = URLDecode(result[0]['NOM_TIPO_NOTA_CREDITO']);
		var vl_cod_motivo_nota_credito = result[0]['COD_MOTIVO_NOTA_CREDITO'];
		var vl_nom_motivo_nota_credito = URLDecode(result[0]['NOM_MOTIVO_NOTA_CREDITO']);
		
		set_value('COD_TIPO_NOTA_CREDITO_0', vl_cod_tipo_nota_credito, vl_cod_tipo_nota_credito);
		set_value('NOM_TIPO_NOTA_CREDITO_0', vl_nom_tipo_nota_credito, vl_nom_tipo_nota_credito);
		set_value('COD_MOTIVO_NOTA_CREDITO_0', vl_cod_motivo_nota_credito, vl_cod_motivo_nota_credito);
		set_value('NOM_MOTIVO_NOTA_CREDITO_0', vl_nom_motivo_nota_credito, vl_nom_motivo_nota_credito);
		
	}else{
		set_value('COD_TIPO_NOTA_CREDITO_0', 0, 0);
		set_value('NOM_TIPO_NOTA_CREDITO_0', '', '');
		set_value('COD_MOTIVO_NOTA_CREDITO_0', 0, 0);
		set_value('NOM_MOTIVO_NOTA_CREDITO_0', '', '');
	}	
}