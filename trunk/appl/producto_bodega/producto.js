function validate() {	
	var compuesto 	= document.getElementById('ES_COMPUESTO_0').checked;
	if(compuesto == false){		
		var aTR = get_TR('PRODUCTO_PROVEEDOR');		
		if(aTR.length == 0){
			alert('Debe ingresar al menos 1 Proveedor antes de grabar.');			
			return false;
		}		
	}
	else{
		var aTR = get_TR('PRODUCTO_COMPUESTO');
		if(aTR.length == 0){
			alert('Debe ingresar al menos 1 Producto Compuesto antes de grabar.');		
			return false;
		}
	}
	
	var es_compuesto = document.getElementById('ES_COMPUESTO_0').value;

	if (es_compuesto.checked){
		var aux_autoriza = 0;
		var aTR = get_TR('PRODUCTO_COMPUESTO');
		for (var i = 0; i < aTR.length; i++){
			var arma_compuesto = document.getElementById('ARMA_COMPUESTO_' + i).checked;
			if (arma_compuesto == true)
				var aux_autoriza = aux_autoriza + 1;
		}
		if (aux_autoriza != 1){
			TabbedPanels1.showPanel(1);
			alert('Debe marcar un Armador para los productos compuestos');	
			return false;
		}
	}
	
	return true;
}
function set_costo_base_proveedor(){	
	var aTR = get_TR('PRODUCTO_PROVEEDOR');
	var i=aTR.length-1;
	var j=0;		
	while(j<i+1){		
		var record = get_num_rec_field(aTR[i].id);
		i = i-1;
	}
	document.getElementById('COSTO_BASE_PI_0').innerHTML = document.getElementById('PRECIO_'+record).value;
}

function calc_volumen(embalado) {
	var largo 	= document.getElementById('LARGO' + embalado + '_0').value; 
	var ancho 	= document.getElementById('ANCHO' + embalado + '_0').value; 
	var alto 	= document.getElementById('ALTO' + embalado + '_0').value; 
	var volumen = document.getElementById('VOLUMNE' + embalado + '_0'); 
	volumen.value = largo * alto * ancho;
}

function checked_checkbox() {	
	var checkbox			= document.getElementById('ES_COMPUESTO_0').checked;
	//var tab_proveedores		= document.getElementById('TAB_PROVEEDORES');
	var div_pc				= document.getElementById('pc');
	var div_uri				= document.getElementById('uri');	
	var total_costo_base 	= document.getElementById('SUM_TOTAL_COSTO_BASE_0').innerHTML;
		
	if (checkbox == true){
		div_pc.style.display			= '';
		div_uri.style.display			= 'none';
		//tab_proveedores.style.display	= 'none';		
		document.getElementById('COSTO_BASE_PI_0').innerHTML = total_costo_base;
	}
	else {	
		div_pc.style.display			= 'none';
		div_uri.style.display			= '';	
		//tab_proveedores.style.display 	= '';	
		document.getElementById('COSTO_BASE_PI_0').innerHTML = document.getElementById('PRECIO_0').value;				
	}
	
}

function redondeo_biggi() {
	// si se modifica esta funcion tambien debe modificarse en f_redondeo_biggi de BD
	var ve_base = document.getElementById('COSTO_BASE_PI_0').innerHTML;
	var ve_fac_int = document.getElementById('FACTOR_VENTA_INTERNO_0').value;
	var precio_vta_sugerido = document.getElementById('PRECIO_VENTA_INT_SUG_0');
	
	ve_base = parseInt(to_num(ve_base));	
	ve_fac_int = to_num(ve_fac_int);
		
	var precio_vta_sug = precio_vta_sugerido.innerHTML; 
	precio_vta_sug = ve_base * ve_fac_int;
	
	if (precio_vta_sug < 1000)
		precio_vta_sug = roundNumber(precio_vta_sug,-1);
	else if(precio_vta_sug < 20000)
		precio_vta_sug = roundNumber(precio_vta_sug,-2); 				
	else if(precio_vta_sug < 100000)
		precio_vta_sug = roundNumber(precio_vta_sug,-3);
	else
		precio_vta_sug = roundNumber((precio_vta_sug * 2),-4)/2;
		
	precio_vta_sugerido.innerHTML = number_format(precio_vta_sug, 0, ',', '.');
	document.getElementById('PRECIO_VENTA_INTERNO_0').focus();				
}

function calc_precio_int_pub() {	
	/**	
	si se modifica esta funcion tambien debe modificarse en la funcion load_record()
	de class_wi_producto.php
	**/
	var cod_producto = document.getElementById('COD_PRODUCTO_H_0').value;	
	var ve_pre_vta_int = to_num(document.getElementById('PRECIO_VENTA_INTERNO_0').value);
	var pre_vta_int_ni = document.getElementById('PRECIO_VENTA_INTERNO_NO_ING_0');
	var ve_fac_vta_pub = to_num(document.getElementById('FACTOR_VENTA_PUBLICO_0').value);	
	var precio_vta_int_sugerido = document.getElementById('PRECIO_VENTA_INT_SUG_0');
	var precio_vta_pub_sugerido = document.getElementById('PRECIO_VENTA_PUB_SUG_0');
	
	///////////////////////////   LABEL PRECIO INTERNO    //////////////////////////////////////////	
	var ajax = nuevoAjax();	
    ajax.open("GET", "devolver_porcentajes.php?cod_producto="+cod_producto, false);    
    ajax.send(null);    
	var resp = ajax.responseText.split('|');
	
	var pre_int_bajo = resp[0];
	var pre_int_alto = resp[1];
	var pre_pub_bajo = resp[2];
	var pre_pub_alto = resp[3];	
		
	precio_vta_int_sugerido = parseInt(to_num(precio_vta_int_sugerido.innerHTML));
	ve_pre_vta_int = parseInt(to_num(ve_pre_vta_int));
	
	var variacion_int = (ve_pre_vta_int - precio_vta_int_sugerido)/precio_vta_int_sugerido;
			
	if(variacion_int > pre_int_alto){
		document.getElementById('PRECIO_INTERNO_ALTO').style.display = '';
		document.getElementById('PRECIO_INTERNO_BAJO').style.display = 'none';
	}		
	else{
		document.getElementById('PRECIO_INTERNO_ALTO').style.display = 'none';
		document.getElementById('PRECIO_INTERNO_BAJO').style.display = 'none';
	}	
	
	if(variacion_int < (pre_int_bajo * -1)){
		document.getElementById('PRECIO_INTERNO_BAJO').style.display = '';		
	}
			
	pre_vta_int_ni.innerHTML = number_format(ve_pre_vta_int, 0, ',', '.');	 
	precio_vta_pub_sugerido.innerHTML = to_num(pre_vta_int_ni.innerHTML) * ve_fac_vta_pub;
	precio_vta_pub_sugerido.innerHTML = number_format(precio_vta_pub_sugerido.innerHTML, 0, ',', '.');	
		
	
	///////////////////////////   LABEL PRECIO PUBLICO    //////////////////////////////////////////
	
	var ve_pre_vta_pub = document.getElementById('PRECIO_VENTA_PUBLICO_0').value;
	var precio_vta_pub_sugerido = document.getElementById('PRECIO_VENTA_PUB_SUG_0');
	
	precio_vta_pub_sugerido = parseInt(to_num(precio_vta_pub_sugerido.innerHTML));
	ve_pre_vta_pub = parseInt(to_num(ve_pre_vta_pub));	
	
	var variacion_pub = (ve_pre_vta_pub - precio_vta_pub_sugerido)/precio_vta_pub_sugerido;
	
	if(variacion_pub > pre_pub_alto){
		document.getElementById('PRECIO_PUBLICO_ALTO').style.display = '';
		document.getElementById('PRECIO_PUBLICO_BAJO').style.display = 'none';
	}		
	else{
		document.getElementById('PRECIO_PUBLICO_ALTO').style.display = 'none';
		document.getElementById('PRECIO_PUBLICO_BAJO').style.display = 'none';
	}	
	
	if(variacion_pub < (pre_pub_bajo * -1)){
		document.getElementById('PRECIO_PUBLICO_BAJO').style.display = '';		
	}
	
	document.getElementById('PRECIO_VENTA_INTERNO_0').style.border='';
	document.getElementById('PRECIO_VENTA_PUBLICO_0').style.border='';
	document.getElementById('PRECIO_VENTA_PUBLICO_0').focus();
}


function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	 
	var cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
	 
	var ajax = nuevoAjax();	
    ajax.open("GET", "get_valores_producto.php?cod_producto="+cod_producto_value, false);    
    ajax.send(null);    
	var resp = ajax.responseText.split('|');
	
	var costo_base = resp[0];
	var precio_vta_int = resp[1];
	var precio_vta_pub = resp[2];
	
	
	document.getElementById('COSTO_BASE_PC_'+record).innerHTML = number_format(costo_base, 0, ',', '.');
	document.getElementById('PRECIO_VENTA_INTERNO_PC_'+record).innerHTML = number_format(precio_vta_int, 0, ',', '.');
	document.getElementById('PRECIO_VENTA_PUBLICO_PC_'+record).innerHTML = number_format(precio_vta_pub, 0, ',', '.');
			 
}

function tot_costo_base(field){
	/* copia el costo base desde la suma total */ 
	var total_costo_base = document.getElementById('SUM_TOTAL_COSTO_BASE_0').innerHTML;
	document.getElementById('COSTO_BASE_PI_0').innerHTML = total_costo_base;
		
	var record = get_num_rec_field(field.id);
	document.getElementById('CANTIDAD_' + record).style.border='';
}
function actualiza_otros_tabs() {
	//valida que el equipo no exista
	var cod_producto = document.getElementById('COD_PRODUCTO_PRINCIPAL_0').value;
	var ajax = nuevoAjax();
	ajax.open("GET", "ajax_existe_producto.php?cod_producto="+cod_producto, false);
	ajax.send(null);
	var resp = URLDecode(ajax.responseText);
	var aDato = eval("(" + resp + ")");
	
	if (aDato[0]['CANT'] > 0){
		TabbedPanels1.showPanel(0);
		document.getElementById('COD_PRODUCTO_PRINCIPAL_0').value = '';
		document.getElementById('COD_PRODUCTO_PRINCIPAL_0').focus();
		alert('El codigo del producto ya existe.');
		return false;	
	}
	
	var vl_cod_producto = document.getElementById('COD_PRODUCTO_PRINCIPAL_0').value.toUpperCase();
	var vl_nom_producto = document.getElementById('NOM_PRODUCTO_PRINCIPAL_0').value.toUpperCase();
	var vl_cod_tipo_producto = document.getElementById('COD_TIPO_PRODUCTO_0');
	var vl_nom_tipo_producto = vl_cod_tipo_producto.options[vl_cod_tipo_producto.selectedIndex].innerHTML;
	
	
	for (var i=1; i <= 5; i++) {
		document.getElementById('cod_producto'+i).innerHTML = vl_cod_producto;
		document.getElementById('nom_producto'+i).innerHTML = vl_nom_producto;
		document.getElementById('nom_tipo_producto'+i).innerHTML = vl_nom_tipo_producto;
	}
}