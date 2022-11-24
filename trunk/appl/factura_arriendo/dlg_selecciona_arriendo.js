function select_1_empresa(valores, record) {
	set_values_empresa(valores, record);

	// borra todos los tr
	var vl_tabla = document.getElementById('ARRIENDO');
	while (vl_tabla.firstChild) {
	  vl_tabla.removeChild(vl_tabla.firstChild);
	}

	// Mientras carga elimino la opcion "Selecciona Opcion..." y pongo una que dice "Cargando..."
	var vl_tr = document.createElement("tr");
	vl_tr.setAttribute("class", "claro");
	vl_tabla.appendChild(vl_tr);
		var vl_td = document.createElement("td");
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			var vl_label = document.createElement("label");
			vl_label.innerHTML = 'Cargando...';
			vl_td.appendChild(vl_label);

	// obtiene los contratos de la empresa
	var vl_cod_empresa = valores[1]; 
	ajax = nuevoAjax();
	ajax.open("GET", "ajax_load_arriendo.php?cod_empresa=" + vl_cod_empresa, false);
	ajax.send(null);  

	// elimina el mensaje de cargando
	vl_tabla.removeChild(vl_tabla.firstChild);

	var vl_resp = URLDecode(ajax.responseText);
	var vl_result = eval("(" + vl_resp + ")");	
	var vl_suma = 0;
	for(var i = 0; i < vl_result.length; i++) {
		vl_suma = vl_suma + vl_result[i]['TOTAL'];
		
		vl_tr = document.createElement("tr");
		if (i%2==0)
			vl_tr.setAttribute("class", "claro");
		else
			vl_tr.setAttribute("class", "oscuro");
		vl_tr.id = "ARRIENDO_" + parseInt(i);
		vl_tabla.appendChild(vl_tr);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			var vl_input = document.createElement("input");
			vl_input.type = 'checkbox';
			vl_input.id = 'SELECCION_' + i;
			vl_input.checked = true;
			vl_input.onchange = function(){selecciona_contrato(this);}
			vl_td.appendChild(vl_input);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "center";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'COD_ARRIENDO_' + i;
			vl_label.innerHTML = vl_result[i]['COD_ARRIENDO'];
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "20";
		vl_td.align = "left";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'NOM_ARRIENDO_' + i;
			vl_label.innerHTML = URLDecode(vl_result[i]['NOM_ARRIENDO']);
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "50%";
		vl_td.align = "left";
		vl_tr.appendChild(vl_td);
			vl_label = document.createElement("label");
			vl_label.id = 'REFERENCIA_' + i;
			vl_label.innerHTML = URLDecode(vl_result[i]['REFERENCIA']);
			vl_td.appendChild(vl_label);

		vl_td = document.createElement("td");
		vl_td.width = "10%";
		vl_td.align = "right";
		vl_tr.appendChild(vl_td);
			var vl_div = document.createElement("div");
			vl_div.setAttribute("class", "margenDerecho");
			vl_td.appendChild(vl_div);
				vl_label = document.createElement("label");
				vl_label.id = 'TOTAL_' + i;
				vl_label.innerHTML = number_format(vl_result[i]['TOTAL'], 0, ',', '.');
				vl_div.appendChild(vl_label);
	}

	document.getElementById('SUMA_TOTAL_0').innerHTML = number_format(vl_suma, 0, ',', '.');;
	
	//Turn hourglass off
	document.body.style.cursor = "default";
}
function get_return_value() {
	var vl_res = '';
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i<aTR.length; i++) {
		var vl_record = get_num_rec_field(aTR[i].id);
		var vl_seleccion = document.getElementById('SELECCION_' + vl_record);
		var vl_cod_arriendo = document.getElementById('COD_ARRIENDO_' + vl_record);
		if (vl_seleccion.checked)
			vl_res = vl_res + vl_cod_arriendo.innerHTML + '|'; 
	}
	return vl_res;
}
function marcar_todo() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = true;
	}
}
function desmarcar_todo() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		document.getElementById('SELECCION_' + vl_record).checked = false;
	}
}
function dejar_seleccion() {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	
	// aTR esta VIVO !, por eso no se porne i++ en el for
	for (var i=0; i < aTR.length; )	{
		var vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == false) {
			var vl_tr = document.getElementById('ARRIENDO_' + vl_record);
		 	vl_tabla.removeChild(vl_tr);
		 }
		 else 
		 	i++;
	}
}
function selecciona_contrato(ve_seleccion) {
	var vl_tabla = document.getElementById('ARRIENDO');
	var aTR = vl_tabla.getElementsByTagName("tr");
	var vl_suma = 0;
	for (var i=0; i < aTR.length; i++)	{
		var vl_record = get_num_rec_field(aTR[i].id);
		if (document.getElementById('SELECCION_' + vl_record).checked == true) {
			vl_suma = vl_suma + parseInt(to_num(document.getElementById('TOTAL_' + vl_record).innerHTML));
		 }
	}
	document.getElementById('SUMA_TOTAL_0').innerHTML = number_format(vl_suma, 0, ',', '.');;
}