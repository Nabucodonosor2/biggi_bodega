function actualizar_precio(ve_tabla) {	
	// se llama desde el boton "actualizar"
	var aTR = get_TR(ve_tabla);
	for (var i=0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var cod_producto = document.getElementById('COD_PRODUCTO_' + record);						
		
		// buscar valor en la BD
		var ajax = nuevoAjax();
		ajax.open("GET", "../common_appl/actualizar_precio.php?cod_producto="+URLEncode(cod_producto.value), false);
		ajax.send(null);	
		
		var precio = document.getElementById('PRECIO_' + record);
		var precio_h = document.getElementById('PRECIO_H_' + record);
		
		precio.innerHTML = number_format(ajax.responseText, 0, ',', '.');
		precio_h.value = URLDecode(ajax.responseText);

		recalc_computed_relacionados(record, 'PRECIO');
	}
}