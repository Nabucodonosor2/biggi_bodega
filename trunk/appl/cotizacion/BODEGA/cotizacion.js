function select_1_producto(valores, record) {
	// para BODEGA se debe usar el precio INTERNO
	cod_empresa = document.getElementById('COD_EMPRESA_0').value;
	var ajax = nuevoAjax();
	var vl_cod_producto_value = URLEncode(valores[1]);
	ajax.open("GET", "../cotizacion/BODEGA/ajax_producto_precio.php?cod_producto="+vl_cod_producto_value+"&cod_empresa="+cod_empresa, false);
	ajax.send(null);		

	var resp = URLDecode(ajax.responseText);
	valores[3] = resp; 
	
	set_values_producto(valores, record);
}

function select_1_empresa(valores, record) {
	 set_values_empresa(valores, record);
	 precio_prod_empresa();
}

function precio_prod_empresa(){
	var aTR = get_TR('ITEM_COTIZACION');
	for (var i = 0; i < aTR.length; i++){
		var cod_prod = document.getElementById('COD_PRODUCTO_' + i).value;
		var cod_empresa = document.getElementById('COD_EMPRESA_0').value;
		var cantidad = document.getElementById('CANTIDAD_'+ i).value;
		
		var ajax = nuevoAjax();
		ajax.open("GET", "../cotizacion/BODEGA/ajax_producto_precio.php?cod_producto="+cod_prod+"&cod_empresa="+cod_empresa, false);
		ajax.send(null);
		var resp = URLDecode(ajax.responseText);
		
		precio = resp.replace(".","");
		set_value('PRECIO_'+i, resp, resp);
		set_value('PRECIO_H_'+i, precio, precio);
		document.getElementById('PRECIO_' + i).innerHTML = resp;
		computed(i, 'TOTAL');
	} 
}