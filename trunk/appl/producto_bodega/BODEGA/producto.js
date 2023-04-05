function validate() {	
	var compuesto 	= document.getElementById('ES_COMPUESTO_0').checked;
	if(compuesto == true){		
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
	var total_costo_base 	= document.getElementById('SUM_TOTAL_PRECIO_INTERNO_0').innerHTML;
		
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
	var ve_fac_int = get_value('FACTOR_VENTA_INTERNO_0');
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
	const ve_pre_vta_int			= get_value('PRECIO_VENTA_INTERNO_0');
	const ve_fac_vta_pub			= to_num(get_value('FACTOR_VENTA_PUBLICO_0'));
	let precio_vta_pub_sugerido		= get_value('PRECIO_VENTA_PUB_SUG_0');
	const margen_precio_interno		= to_num(get_value('MARGEN_PRECIO_INTERNO_0'));
	const precio_vta_int_sugerido 	= to_num(get_value('PRECIO_VENTA_INT_SUG_0'));

	precio_vta_pub_sugerido = to_num(ve_pre_vta_int) * ve_fac_vta_pub;
	set_value('PRECIO_VENTA_INTERNO_NO_ING_0', number_format(ve_pre_vta_int, 0, ',', '.'), number_format(ve_pre_vta_int, 0, ',', '.'));
	set_value('PRECIO_VENTA_PUB_SUG_0', number_format(precio_vta_pub_sugerido, 0, ',', '.'), number_format(precio_vta_pub_sugerido, 0, ',', '.'));

	if(ve_pre_vta_int <= (precio_vta_int_sugerido - margen_precio_interno)){
		document.getElementById('PRECIO_INTERNO_BAJO').style.display = '';
	}else{
		document.getElementById('PRECIO_INTERNO_BAJO').style.display = 'none';
	}
}


function select_1_producto(valores, record) {
	set_values_producto(valores, record);
	 
	var cod_producto_value = document.getElementById('COD_PRODUCTO_' + record).value;
	 
	var ajax = nuevoAjax();	
    ajax.open("GET", "get_valores_producto.php?cod_producto="+cod_producto_value, false);    
    ajax.send(null);    
	var resp = ajax.responseText.split('|');
	var precio_vta_int = resp[1];

	document.getElementById('PRECIO_VENTA_INTERNO_PC_'+record).innerHTML = number_format(precio_vta_int, 0, ',', '.');
	calculo_producto();
}

function tot_costo_base(){
	/* copia el costo base desde la suma total */ 
	const total_costo_base = get_value('SUM_TOTAL_PI_0');
	set_value('COSTO_BASE_PI_0', total_costo_base, total_costo_base);
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

function calculo_total_pr(){
	const aTR = get_TR('PRODUCTO_COMPUESTO');
	let totalPi = 0;

	for (let i = 0; i < aTR.length; i++) {
		let vlRecord	= get_num_rec_field(aTR[i].id);

		if(document.getElementById('GENERA_COMPRA_'+vlRecord).checked){
			let vlCantidad	= get_value('CANTIDAD_'+vlRecord);
			let vlPrecioPi	= get_value('PRECIO_VENTA_INTERNO_PC_'+vlRecord).replaceAll('.', '');
			
			let total = number_format(vlCantidad * vlPrecioPi, 0, ',', '.');
			set_value('TOTAL_PRECIO_INTERNO_'+vlRecord, total, total);
			totalPi += vlCantidad * vlPrecioPi;
		}
	}

	totalPi = number_format(totalPi, 0, ',', '.');
	set_value('SUM_TOTAL_PI_0', totalPi, totalPi);
}

function calculo_producto(){
	calculo_total_pr();
	tot_costo_base();
	calc_precio_int_pub();
	redondeo_biggi();
}

function del_line(ve_tr_id, ve_nom_mantenedor) {
	del_line_standard(ve_tr_id, ve_nom_mantenedor);

	calculo_producto();
}