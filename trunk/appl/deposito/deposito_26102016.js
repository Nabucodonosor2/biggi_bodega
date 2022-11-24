function marcar_todo() {
	var aTR = get_TR('ITEM_DEPOSITO');
	var vl_suma = 0;
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = true;

		// copia el monto doc
		var vl_monto_doc = document.getElementById('MONTO_DOC_' + vl_record).innerHTML;
		vl_monto_doc = vl_monto_doc.replace('.', '', 'g');	// borra los puntos en los miles
		vl_monto_doc = vl_monto_doc.replace(',', '.', 'g');	// cambia coma decimal por punto
		vl_suma = vl_suma + parseFloat(vl_monto_doc);
		document.getElementById('MONTO_SELECCION_' + vl_record).innerHTML = number_format(vl_monto_doc, 0, ',', '.');
		document.getElementById('MONTO_SELECCION_H_' + vl_record).value = vl_monto_doc;
	}
	document.getElementById('SUM_MONTO_SELECCION_0').innerHTML = number_format(vl_suma, 0, ',', '.');
	document.getElementById('SUM_MONTO_SELECCION_H_0').value = vl_suma;
}
function desmarcar_todo(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR('ITEM_DEPOSITO');
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = false;

		// deja en cero el monto seleccionado
		document.getElementById('MONTO_SELECCION_' + vl_record).innerHTML = '0';
		document.getElementById('MONTO_SELECCION_H_' + vl_record).value = 0;
	}
	document.getElementById('SUM_MONTO_SELECCION_0').innerHTML = '0';
	document.getElementById('SUM_MONTO_SELECCION_H_0').value = 0;
}
function dejar_seleccion(ve_tabla_id, ve_prefijo) {
	var aTR = get_TR('ITEM_DEPOSITO');
	for (var i=0; i < aTR.length; i++)	{
		vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == false)
			del_line(aTR[i].id, 'deposito');
	}
}
function selecciona_documento(ve_seleccion) {
	var vl_record = get_num_rec_field(ve_seleccion.id);
	
	var acum = document.getElementById('SUM_MONTO_SELECCION_0'); 
	var acum_h = document.getElementById('SUM_MONTO_SELECCION_H_0'); 
	var valor_sum = to_num(get_value(acum.id));
	var vl_monto_doc = document.getElementById('MONTO_DOC_' + vl_record).innerHTML; 
	vl_monto_doc = vl_monto_doc.replace('.', '', 'g');	// borra los puntos en los miles
	vl_monto_doc = vl_monto_doc.replace(',', '.', 'g');	// cambia coma decimal por punto
	if (ve_seleccion.checked) {		
		document.getElementById('MONTO_SELECCION_' + vl_record).innerHTML = number_format(vl_monto_doc, 0, ',', '.');
		document.getElementById('MONTO_SELECCION_H_' + vl_record).value = vl_monto_doc;
		
		// actualiza el acum
		document.getElementById('SUM_MONTO_SELECCION_0').innerHTML = number_format(parseFloat(valor_sum) + parseFloat(vl_monto_doc), 0, ',', '.');
		document.getElementById('SUM_MONTO_SELECCION_H_0').value = parseFloat(valor_sum) + parseFloat(vl_monto_doc);
	}
	else {
		document.getElementById('MONTO_SELECCION_' + vl_record).innerHTML = '0';
		document.getElementById('MONTO_SELECCION_H_' + vl_record).value = 0;

		// actualiza el acum
		document.getElementById('SUM_MONTO_SELECCION_0').innerHTML = number_format(parseFloat(valor_sum) - parseFloat(vl_monto_doc), 0, ',', '.');
		document.getElementById('SUM_MONTO_SELECCION_H_0').value = parseFloat(valor_sum) - parseFloat(vl_monto_doc);
	}
}