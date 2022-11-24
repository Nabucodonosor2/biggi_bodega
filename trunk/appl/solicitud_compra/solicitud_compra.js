function validate() {
	var vl_cod_estado	= document.getElementById('COD_ESTADO_SOLICITUD_COMPRA_0').value;
	var vl_cod_estado_h	= document.getElementById('COD_ESTADO_SOLICITUD_COMPRA_H_0').value;
	if (vl_cod_estado==2){ // confirmada
		var vl_sel_armado = false;
		var vl_tabla = document.getElementById('ITEM_SOLICITUD_COMPRA');
		var aTR = vl_tabla.getElementsByTagName("tr");
		for (var i=0; i<aTR.length; i++) {
			var vl_record = get_num_rec_field(aTR[i].id); 
			if (document.getElementById('IT_ARMA_COMPUESTO_' + vl_record).checked) {
				vl_sel_armado = true;
			}

			if (document.getElementById('IT_GENERA_COMPRA_' + vl_record).checked) {
				var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_record).value;
				if (vl_cod_empresa=='') {
					alert('Debe indicar el proveedor quien se compra');
					document.getElementById('IT_COD_EMPRESA_' + vl_record).focus();
					return false;
				}
			}
		}
		if (!vl_sel_armado) {
			alert('Debe seleccionar quien arma el equipo.');
			return false;
		}
	}
	if(vl_cod_estado==3 && vl_cod_estado_h == 2){	//2.- Confirmada, 3.- Anulada
		var vl_cod_orden_compra_s = '';
		var aTR = get_TR('ITEM_ORDEN_COMPRA_SOLICITUD');
		var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').innerHTML;
		for(i=0 ; i < aTR.length ; i++){
			var vl_cod_orden_compra = document.getElementById('COD_ORDEN_COMPRA_' + i).innerHTML;
			vl_cod_orden_compra_s = vl_cod_orden_compra_s + vl_cod_orden_compra + ', ';
		}
		vl_cod_orden_compra_s = vl_cod_orden_compra_s.substring(0, vl_cod_orden_compra_s.length-2);
		
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_valida_entrada.php?cod_orden_compra_s="+vl_cod_orden_compra_s, false);
		ajax.send(null);
		var resp = ajax.responseText;
		
		if(resp == 'ALERTA'){
			alert('No se puede anular esta Solicitud de Compra, anularla representa afectar el stock actual del producto '+vl_cod_producto+'\nFavor contactar a Integrasystem.');
			return false;
		}else{
			if(resp != 'NO_ENTRADA'){
				var vl_confirm = confirm('La(s) OC Nº '+resp+'; presentan entradas a Bodega!.\nRecuerde que debe regularizar esta situación respecto a las Recepciones de Facturas que se generaron.');
				if(!vl_confirm)
					return false;
			}else{
				var vl_confirm = confirm('Confirma que ha dado aviso a los proveedores para que no sean facturadas las OC Nº: '+vl_cod_orden_compra_s+' ?');
				if(!vl_confirm)
					return false;
			}
		}			
	}
	var vl_aTR = get_TR('ITEM_SOLICITUD_COMPRA');
	var alerta = '';
	for(j=0 ; j < vl_aTR.length ; j++){
		var vl_record = get_num_rec_field(vl_aTR[j].id);
		var vl_cod_empresa	= get_value('IT_COD_EMPRESA_'+vl_record);
		if(document.getElementById('IT_GENERA_COMPRA_'+vl_record).checked){
			if(vl_cod_empresa == ''){
				alert('Debe ingresar "Empresa" antes de grabar.');
				document.getElementById('IT_COD_EMPRESA_'+vl_record).focus();
				return false;
			}
		
		}
		var vl_cod_producto	= get_value('IT_COD_PRODUCTO_'+vl_record);
		var vl_cantidad = get_value('IT_CANTIDAD_TOTAL_'+vl_record);;
		if(vl_cod_empresa == 4 && document.getElementById('IT_GENERA_COMPRA_'+vl_record).checked){
			var ajax = nuevoAjax();
			ajax.open("GET", "ajax_valida_stock_inf.php?cod_producto="+vl_cod_producto+"&cod_empresa="+vl_cod_empresa+"&cantidad="+vl_cantidad, false);
			ajax.send(null);
			var resp = ajax.responseText;
			if(resp != 'OK')
				alerta = alerta+resp;
		}
	}
	
	if(alerta != ''){
		alerta = alerta.split("|");

		if(alerta[1] != 'N'){
			vl_confirm = confirm("Los siguientes productos no están en stock:\n\n"+alerta[0]+"¿Deséa guardar de todas formas?");
			if(vl_confirm == true)
				return true;
			else
				return false;	
		}else{
			alert("Los siguientes productos no están en stock:\n\n"+alerta[0]+"Usted no está autorizado a realizar la Solicitud OC sin stock");
			return false;
		}	
	}
	
}
function select_1_producto(valores, record) {
	// Se reimpleneta es funcion para adionar codigo
	 set_values_producto(valores, record);

	 /////////////
	var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
	if (vl_cantidad=='')
		vl_cantidad = 0;

	busca_items(false);
}
function busca_items(ve_siempre_terminado) {
	// borrar los items	 
	var aTR = get_TR('ITEM_SOLICITUD_COMPRA');
	for (i=0; i<aTR.length; i++)
		del_line(aTR[i].id, 'solicitud_compra'); 

 	// agrega a los items los productos relacionados
	var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').value;
	if (vl_cod_producto=='')
		return;
	
	/////////////
	var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
	if (vl_cantidad=='')
		vl_cantidad = 0;
		
	// Si viene en true siempre lo maneja como equipo terminado, sino lo maneja como compuesto si es compuesto
	if (ve_siempre_terminado) 
		var result = new Array();
	else {
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_producto_compuesto.php?cod_producto="+URLEncode(vl_cod_producto), false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");

		if(result.length > 0){
			// es compuesto
			// Habilita la opcion de comprar como compuesto
			document.getElementById("COMPUESTO_0").removeAttribute("disabled");
			document.getElementById("COMPUESTO_0").checked = true;
			document.getElementById("TERMINADO_0").checked = false;
		}
		else {
			// NO es compuesto
			// DESHabilita la opcion de comprar como compuesto
			document.getElementById("COMPUESTO_0").setAttribute("disabled", 0);
			document.getElementById("COMPUESTO_0").checked = false;
			document.getElementById("TERMINADO_0").checked = true;
		}
	}
	
	if(result.length > 0){
		// es compuesto
		// Habilita la opcion de comprar como compuesto
		document.getElementById("COMPUESTO_0").removeAttribute("disabled");
		document.getElementById("COMPUESTO_0").checked = true;
		document.getElementById("TERMINADO_0").checked = false;
		 
		for (var i=0; i< result.length; i++) {
			var vl_row = add_line('ITEM_SOLICITUD_COMPRA', 'solicitud_compra');
			document.getElementById('IT_COD_PRODUCTO_' + vl_row).innerHTML = result[i]['COD_PRODUCTO_HIJO'];
			document.getElementById('IT_COD_PRODUCTO_H_' + vl_row).value = result[i]['COD_PRODUCTO_HIJO'];
			document.getElementById('IT_NOM_PRODUCTO_' + vl_row).innerHTML = URLDecode(result[i]['NOM_PRODUCTO']);
			document.getElementById('IT_CANTIDAD_' + vl_row).value = result[i]['CANTIDAD'];
			document.getElementById('IT_CANTIDAD_TOTAL_' + vl_row).innerHTML = vl_cantidad * result[i]['CANTIDAD'];
			document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_row).value = vl_cantidad * result[i]['CANTIDAD'];
			document.getElementById('IT_GENERA_COMPRA_' + vl_row).checked = (result[i]['GENERA_COMPRA']=='S'); 
	
			var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_row);
			vl_cod_empresa.length = 0;
			// item vacio
			var vl_opcion = document.createElement("option");
			vl_opcion.value = '';
			vl_opcion.innerHTML = '';
			vl_cod_empresa.appendChild(vl_opcion);
			for (var j=0; j < result[i]['COD_EMPRESA'].length; j++) {
				var vl_opcion = document.createElement("option");
				vl_opcion.value = result[i]['COD_EMPRESA'][j]['IT_COD_EMPRESA'];
				vl_opcion.innerHTML = URLDecode(result[i]['COD_EMPRESA'][j]['IT_ALIAS']);
				vl_opcion.dataset.dropdown = result[i]['COD_EMPRESA'][j]['PRECIO_COMPRA'];
				vl_cod_empresa.appendChild(vl_opcion);
			}
		}	
		
	}else{
		var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').value;
			
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_producto.php?cod_producto="+URLEncode(vl_cod_producto), false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		var result = eval("(" + resp + ")");
		
		var vl_row = add_line('ITEM_SOLICITUD_COMPRA', 'solicitud_compra');
		var vl_cantidad = to_num(document.getElementById('CANTIDAD_0').value);
		
		for (var i=0; i< result.length; i++) {
			document.getElementById('IT_COD_PRODUCTO_' + vl_row).innerHTML = result[i]['COD_PRODUCTO'];
			document.getElementById('IT_COD_PRODUCTO_H_' + vl_row).value = result[i]['COD_PRODUCTO'];
			document.getElementById('IT_NOM_PRODUCTO_' + vl_row).innerHTML = URLDecode(result[i]['NOM_PRODUCTO']);
			document.getElementById('IT_CANTIDAD_' + vl_row).value = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_CANTIDAD_TOTAL_' + vl_row).innerHTML = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_row).value = to_num(document.getElementById('CANTIDAD_0').value);
			document.getElementById('IT_GENERA_COMPRA_' + vl_row).checked = (result[i]['GENERA_COMPRA']=='S'); 
	
			var vl_cod_empresa = document.getElementById('IT_COD_EMPRESA_' + vl_row);
			vl_cod_empresa.length = 0;
			// item vacio
			var vl_opcion = document.createElement("option");
			vl_opcion.value = '';
			vl_opcion.innerHTML = '';
			vl_cod_empresa.appendChild(vl_opcion);
			for (var j=0; j < result[i]['COD_EMPRESA'].length; j++) {
				var vl_opcion = document.createElement("option");
				vl_opcion.value = result[i]['COD_EMPRESA'][j]['IT_COD_EMPRESA'];
				vl_opcion.innerHTML = URLDecode(result[i]['COD_EMPRESA'][j]['IT_ALIAS']);
				vl_opcion.dataset.dropdown = result[i]['COD_EMPRESA'][j]['PRECIO_COMPRA'];
				vl_cod_empresa.appendChild(vl_opcion);
			}
		}	 
	}
}
function change_empresa(ve_cod_empresa) {
	var vl_precio_compra = ve_cod_empresa.options[ve_cod_empresa.selectedIndex].dataset.dropdown;
	var vl_record = get_num_rec_field(ve_cod_empresa.id); 
	
	document.getElementById('IT_PRECIO_COMPRA_' + vl_record).innerHTML = number_format(vl_precio_compra, 0, ',', '.'); 
	document.getElementById('IT_PRECIO_COMPRA_H_' + vl_record).value = vl_precio_compra; 
}
function change_cantidad(ve_cantidad) {
	var vl_cantidad = to_num(ve_cantidad.value);
	if (vl_cantidad=='')
		vl_cantidad = 0;
	var vl_tabla = document.getElementById('ITEM_SOLICITUD_COMPRA');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i<aTR.length; i++) {
		var vl_record = get_num_rec_field(aTR[i].id); 
		var vl_cantidad_unitaria = document.getElementById('IT_CANTIDAD_' + vl_record).value;
		document.getElementById('IT_CANTIDAD_TOTAL_' + vl_record).innerHTML = vl_cantidad * vl_cantidad_unitaria;
		document.getElementById('IT_CANTIDAD_TOTAL_H_' + vl_record).value = vl_cantidad * vl_cantidad_unitaria;
	}
}
function terminado_compuesto(ve_terminado_compuesto) {
	if(document.getElementById('TERMINADO_0').checked){
		var vl_cod_producto = document.getElementById('COD_PRODUCTO_0').value;
		var ajax = nuevoAjax();
		ajax.open("GET", "ajax_valida_compuesto.php?cod_producto="+URLEncode(vl_cod_producto), false);
		ajax.send(null);
		var resp = ajax.responseText;
		
		if(resp == 0){
			alert('El equipo '+vl_cod_producto+' no se puede comprar como equipo terminado, porque no tiene proveedores asignados.');
			document.getElementById('TERMINADO_0').checked = false;
			document.getElementById('COMPUESTO_0').checked = true;
			return;
		}
	}
	
	var vl_field = get_nom_field(ve_terminado_compuesto.id);
	if (vl_field == 'TERMINADO') 
		busca_items(true);
	else 
		busca_items(false);
}

function detalle_stock(ve_control){
	var vl_record = get_num_rec_field(ve_control.id);
	var vl_cod_empresa	= get_value('IT_COD_EMPRESA_'+vl_record);
	var vl_cod_producto	= get_value('IT_COD_PRODUCTO_'+vl_record);
	var vl_cantidad_total = get_value('IT_CANTIDAD_TOTAL_'+vl_record);

	if(vl_cod_empresa != '' && vl_cod_empresa == 4){
		var url = "dlg_detalle_stock.php?cod_producto="+vl_cod_producto+"&cod_empresa="+vl_cod_empresa+"&cantidad_total="+vl_cantidad_total;
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 265,
			 width: 490,
			 scrollable: false
		});	
		
	}
}