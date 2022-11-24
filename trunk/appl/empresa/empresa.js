function load_sucursal_persona(nom_sucursal_id) {
	var nom_sucursal = document.getElementById(nom_sucursal_id);
	var nom_field = get_nom_field(nom_sucursal_id);
	var num_rec = get_num_rec_field(nom_sucursal_id);
	var cod_sucursal = document.getElementById('COD_SUCURSAL_' + num_rec);
	
	var aTR = get_TR('PERSONA');
	var opcion;
	for (var i=0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var p_cod_sucursal = document.getElementById('P_COD_SUCURSAL_' + record);
		for (k=0; k < p_cod_sucursal.length; k++) {
			opcion = p_cod_sucursal.options[k]
			if (opcion.value==cod_sucursal.value) {
				opcion.innerHTML = nom_sucursal.value;
				break;
			}
		}
		if (opcion.innerHTML == nom_sucursal.value)
			continue;
		
		if (cod_sucursal.value < 0) {
			opcion=document.createElement("option"); 
			opcion.value = cod_sucursal.value; 
			opcion.innerHTML= nom_sucursal.value;
			p_cod_sucursal.appendChild(opcion);								
		}
	}
}
function change_nom_persona(ve_nom_persona_id) {
	var nom_persona = document.getElementById(ve_nom_persona_id).value;
	var cod_persona = document.getElementById('COD_PERSONA_' + get_num_rec_field(ve_nom_persona_id)).value;
	var cod_persona_defecto = document.getElementById('COD_PERSONA_DEFECTO_0');
	
	for (var i=0; i < cod_persona_defecto.options.length; i++) {
		if (cod_persona_defecto.options[i].value==cod_persona) {
			cod_persona_defecto.options[i].innerHTML = nom_persona;
			break;
		}
	}
}
function add_line_persona(tabla_id, nom_tabla) {	
	add_line(tabla_id, nom_tabla);
	
	var aTR = get_TR('PERSONA');
	var tr_id = aTR[aTR.length - 1].id;	
	var opcion;
 	
	var p_cod_sucursal = document.getElementById('P_COD_SUCURSAL_' + get_num_rec_field(tr_id));
	if (p_cod_sucursal.length==0) {
		opcion=document.createElement("option"); 
		opcion.value = ''; 
		opcion.innerHTML= '';
		p_cod_sucursal.appendChild(opcion);								
	}
	
	aTR = get_TR('SUCURSAL');
	for (var i=0; i < aTR.length; i++) {
		var record = get_num_rec_field(aTR[i].id);
		var cod_sucursal = document.getElementById('COD_SUCURSAL_' + record);
		var nom_sucursal = document.getElementById('NOM_SUCURSAL_' + record);
		if (cod_sucursal.value < 0) {
			opcion=document.createElement("option"); 
			opcion.value = cod_sucursal.value; 
			opcion.innerHTML= nom_sucursal.value;
			p_cod_sucursal.appendChild(opcion);								
		}
	}
	
	// Agrega la persona al drop down de COD_PERSONA_DEFECTO si es proveedor
	var es_proveedor_interno = document.getElementById('ES_PROVEEDOR_INTERNO_0');
	var es_proveedor_externo = document.getElementById('ES_PROVEEDOR_EXTERNO_0');
	var cod_persona_defecto = document.getElementById('COD_PERSONA_DEFECTO_0');
	opcion = document.createElement("option"); 
	opcion.value = document.getElementById('COD_PERSONA_' + get_num_rec_field(tr_id)).value;
	opcion.innerHTML= document.getElementById('NOM_PERSONA_' + get_num_rec_field(tr_id)).value;
	cod_persona_defecto.appendChild(opcion);								
}
function valida_mandatory_proveedor(ve_id, ve_nom_campo) {
	var element = document.getElementById(ve_id);
	if (element.value=='' || element.value==' ') {
		TabbedPanels1.showPanel(4);
		element.focus();
		alert('Debe ingresar "'+ve_nom_campo+'" antes de grabar.');
		return false;
	}
	return true;
}
function valida_personal(){
	var personal = document.getElementById('ES_PERSONAL_0').checked; 
	var tipo_participacion	= document.getElementById('TIPO_PARTICIPACION_0');

	if (personal == true) {
		document.getElementById('TD_CATEGORIA').style.display = '';
		document.getElementById('TD_ESPACIO').style.display = 'none';
		tipo_participacion.value = '';
		document.getElementById('TIPO_PARTICIPACION_0').focus();
	}	
	else{
		document.getElementById('TD_CATEGORIA').style.display = 'none';
		document.getElementById('TD_ESPACIO').style.display = '';
		tipo_participacion.value = '';
	}
}
function validate() {
	// VALIDA SI TIENE SUCURSALES
	var aTR = get_TR('SUCURSAL');
	if (!aTR.length){
		alert('Debe ingresar una sucursal antes de grabar');
		return false;
	}
	// SI TIENE SUCURSALES DEBE TENER MARCADA UNA DIRECCION DE FACTURA MARCADA
	var marca_factura;
	var marca_despacho;
	marca_factura = 0;
	marca_despacho = 0;
	for (var i=0; i < aTR.length; i++) {
		var direccion_factura = document.getElementById('DIRECCION_FACTURA_' + get_num_rec_field(aTR[i].id));
		var direccion_despacho = document.getElementById('DIRECCION_DESPACHO_' + get_num_rec_field(aTR[i].id));
		
		if (direccion_factura.checked)
			marca_factura ++;
		if (direccion_despacho.checked)
			marca_despacho ++;
	}
	if (marca_factura == 0){
		alert('Debe seleccionar la sucursal de Facturacion antes de grabar'); 
		return false;
	}
	else if (marca_despacho == 0){
		alert('Debe seleccionar la sucursal de Despacho antes de grabar'); 
		return false;
	}
	// VALIDA SI TIENE PERSONAS
	var aTR = get_TR('PERSONA');
	if (!aTR.length){
		alert('Debe ingresar una persona antes de grabar');
		return false;
	}
	
	// valida los mails de las personas
	for (var i=0; i < aTR.length; i++) {
		var email = document.getElementById('EMAIL_' + get_num_rec_field(aTR[i].id));
		if (!validate_mail(email)) {
			TabbedPanels1.showPanel(0);
			email.focus();
			return false;
		}
	}
	
	// VALIDA QUE SE MARQUE SI LA EMPRESA ES CLIENTE Y/O PROVEEDOR Y/0 PERSONAL
	var es_cliente = document.getElementById('ES_CLIENTE_0');
	var es_proveedor_interno = document.getElementById('ES_PROVEEDOR_INTERNO_0');
	var es_proveedor_externo = document.getElementById('ES_PROVEEDOR_EXTERNO_0');
	var es_personal = document.getElementById('ES_PERSONAL_0');
	if (!es_cliente.checked && !es_proveedor_interno.checked && !es_proveedor_externo.checked && !es_personal.checked) {
		alert('Debe marcar si es Cliente y/o Proveedor y/o Personal');
		return false;	
	}	
	
	// Si es proveedor hace mandatory los campos de OC por default
	var prov_ext = document.getElementById('ES_PROVEEDOR_EXTERNO_0').checked;
	var prov_int = document.getElementById('ES_PROVEEDOR_INTERNO_0').checked;
	
	if (prov_ext || prov_int) {
		if (!valida_mandatory_proveedor('COD_PERSONA_DEFECTO_0', 'ATENCION'))
			return false;
		if (!valida_mandatory_proveedor('COD_FORMA_PAGO_0', 'FORMA PAGO'))
			return false;
	}
	
	// valida el ingreso de tipo de participacion
	var personal = document.getElementById('ES_PERSONAL_0').checked; 
	var doc_participacion = document.getElementById('TIPO_PARTICIPACION_0');	

	if (personal == true) {
		if(doc_participacion.value == ''){
				alert('Debe ingresar la Categoria antes de grabar');
				return false;
		}
	}
	
	return true;
}	
function existe_rut(rut){
	var ajax = nuevoAjax();
	ajax.open("GET", "existe_rut.php?rut="+URLEncode(rut.value)+"&cod_empresa="+URLEncode(get_value('COD_EMPRESA_0')), true);
	
	// RESPUESTAS DEL AJAX:
	// O = EL RUT NO EXISTE EN TABLA EMPRESA (PUEDE INGRESARLO)
	// 1 = EL RUT EXISTE SOLO 1 EN TABLA EMPRESA Y ES EL MISMO DE LA EMPRESA QUE SE ESTA MODIFICANDO (PUEDE INGRESARLO)
	// 2 = EL RUT EXISTE SOLO 1 EN TABLA EMPRESA Y PERTENECE A OTRA EMPRESA (NO PUEDE INGRESARLO)
	// 3 = EL RUT EXISTE MAS DE 1 EN TABLA EMPRESA DEBE REGULARIZARSE (NO PUEDE INGRESARLO)
	
	ajax.onreadystatechange=function() {
		if (ajax.readyState==4) {
			var existe = URLDecode(ajax.responseText);	
			if (existe == 2) {
				alert('Ya existe una empresa con ese Rut');
				rut.value = '';
				rut.focus();
			}	
			if (existe == 3) {
				alert('Existe mas de una empresa con ese Rut, (regularizar)');
				rut.value = '';
				rut.focus();
			}			
		}
	}
	ajax.send(null);		
	clear_dig_verif(rut, 'DIG_VERIF');
}
function del_line_sucursal(tr_id, nom_tabla) {
	var cod_sucursal = document.getElementById('COD_SUCURSAL_' + get_num_rec_field(tr_id));
	var ajax = nuevoAjax();
	ajax.open("GET", "sucursal_esta_utilizada.php?cod_sucursal="+URLEncode(cod_sucursal.value), false);						  
	ajax.send(null);
	var usada = trim(URLDecode(ajax.responseText));
	// SE USA LA FUNCION TRIM PORQUE EL AJAXRESPONSE AL RETORNAR CONCATENA UN TABULADOR
	if (usada != 'noencontrada') {
		alert('La sucursal esta siendo usada en : ' + usada);	
		return false;
	}
	

	// recorre todas las personas para desseleccionar la suc qu se esta borrandpo (si esta siendo usada)
	var aTR = get_TR('PERSONA');
	for (var i=0; i < aTR.length; i++) {
		var p_cod_sucursal = document.getElementById('P_COD_SUCURSAL_' + get_num_rec_field(aTR[i].id));
		if (p_cod_sucursal.options[p_cod_sucursal.selectedIndex].value == cod_sucursal.value) {
			p_cod_sucursal.removeChild(p_cod_sucursal.options[p_cod_sucursal.selectedIndex]);
			p_cod_sucursal.selectedIndex = 0;
		}
		var j=0; 
		while (j < p_cod_sucursal.options.length) {
			if (p_cod_sucursal.options[j].value == cod_sucursal.value)
				p_cod_sucursal.removeChild(p_cod_sucursal.options[j]);
			else
				j++;
		}	
	}
	del_line(tr_id, nom_tabla);	
}
function del_line_persona(tr_id, nom_tabla) {
	var cod_persona = document.getElementById('COD_PERSONA_' + get_num_rec_field(tr_id));
	var ajax = nuevoAjax();
	ajax.open("GET", "persona_esta_utilizada.php?cod_persona="+URLEncode(cod_persona.value), false);						  
	ajax.send(null);
	var usada = trim(URLDecode(ajax.responseText));
	// SE USA LA FUNCION TRIM PORQUE EL AJAXRESPONSE AL RETORNAR CONCATENA UN TABULADOR
	if (usada != 'noencontrada') {
		alert('La persona esta siendo usada en : ' + usada);	
		return false;
	}
	del_line(tr_id, nom_tabla);	
}

function muestra_nuevo_tab() {
	var prov_ext = document.getElementById('ES_PROVEEDOR_EXTERNO_0').checked;
	var prov_int = document.getElementById('ES_PROVEEDOR_INTERNO_0').checked;
	
	if (prov_ext || prov_int) {
		document.getElementById('val').style.display = '';
	}	
	else{	
		document.getElementById('val').style.display = 'none';
	}
	
	document.getElementById('val').focus();
}
function muestra_tab_cliente() {
	var cliente = document.getElementById('ES_CLIENTE_0').checked;
	//var prov_int = document.getElementById('ES_PROVEEDOR_INTERNO_0').checked;
	
	if (cliente == true ) {
		document.getElementById('cliente').style.display = '';
	}	
	else{	
		document.getElementById('cliente').style.display = 'none';
	}
	
	//document.getElementById('cliente').focus();
}
function select_1_producto(valores, record) {
/**
esta función se reimplementa ya que al agregar productos en tab "Lista de Precio", el precio queda con formato 
y al grabar falla. 17/11/09  IS.
*/
	 set_values_producto(valores, record);
	 var precio = valores[3].replace(".","");
	set_value('PRECIO_' + record, precio, precio);
}
