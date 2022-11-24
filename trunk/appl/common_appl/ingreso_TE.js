function ingreso_TE(cod_producto) {	

	var record = get_num_rec_field(cod_producto.id);
	// datos del TE		
	var nom_te_value = URLEncode(get_value('NOM_PRODUCTO_' + record));
	var previo_value = URLEncode(document.getElementById('PRECIO_H_' + record).value);
	var cod_tipo_te_value = URLEncode(document.getElementById('COD_TIPO_TE_' + record).value);
	var motivo_te_value = URLEncode(document.getElementById('MOTIVO_TE_' + record).value);

	// solo para NV, existe nom_usuario_autoriza_te 
	var nom_usuario_autoriza_te = document.getElementById('NOM_USUARIO_AUTORIZA_TE_' + record);
	if (nom_usuario_autoriza_te) {
	
		var nom_usuario_autoriza_te_value = URLEncode(document.getElementById('NOM_USUARIO_AUTORIZA_TE_' + record).value);
		var fecha_autoriza_te_value = URLEncode(document.getElementById('FECHA_AUTORIZA_TE_' + record).value);
		var motivo_autoriza_te_value = URLEncode(document.getElementById('MOTIVO_AUTORIZA_TE_' + record).value);
		
		var url = "../common_appl/ingreso_TE.php?nom_te="+nom_te_value+"&precio="+previo_value+"&cod_tipo_te="+cod_tipo_te_value+"&motivo_te="+motivo_te_value+"&nom_usuario_autoriza_te="+nom_usuario_autoriza_te_value+"&fecha_autoriza_te="+fecha_autoriza_te_value+"&motivo_autoriza_te="+motivo_autoriza_te_value;		
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 430,
			 width: 550,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	
				if (returnVal == null){
					if (cod_tipo_te_value!='')	// mantiene los valores anteriores.
		 			return;
		 				
		 		cod_producto.value = '';
				
				var precio 			= document.getElementById('PRECIO_'+ record);
				var precio_h 		= document.getElementById('PRECIO_H_' + record);
				var motivo  		= document.getElementById('MOTIVO_TE_' + record);
				var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
				var cantidad  		= document.getElementById('CANTIDAD_' + record);
				
				
				set_value('NOM_PRODUCTO_' + record, '', '');
				precio.innerHTML 	= '';
				precio_h.value 		= '';
				motivo.value		= '';
				tipo_TE.value		= '';
				cantidad.value		= '';		
				
				recalc_computed_relacionados(record, 'PRECIO');	
				}		
				else {
					var vl_boton_precio = document.getElementById('BOTON_PRECIO_' + record)
					if (vl_boton_precio)
						vl_boton_precio.value = 'TE';
					var res = returnVal.split('|');	
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					var motivo_autoriza_te	= document.getElementById('MOTIVO_AUTORIZA_TE_' + record);	
					
					set_value('NOM_PRODUCTO_' + record, res[0], res[0]);
					set_value('PRECIO_' + record, res[1], number_format(res[1], 0, ',', '.'));
					precio_h.value 		= res[1];
					motivo.value		= res[2];
					tipo_TE.value		= res[3];
					if (nom_usuario_autoriza_te)
						motivo_autoriza_te.value  = res[4];
			
					cantidad.focus();		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}
				
				return true;	
			}
		});
	}
	else {
		var url = "../common_appl/ingreso_TE.php?nom_te="+nom_te_value+"&precio="+previo_value+"&cod_tipo_te="+cod_tipo_te_value+"&motivo_te="+motivo_te_value;		
		$.showModalDialog({
			 url: url,
			 dialogArguments: '',
			 height: 430,
			 width: 550,
			 scrollable: false,
			 onClose: function(){ 
			 	var returnVal = this.returnValue;
			 	
				if (returnVal == null){
					if (cod_tipo_te_value!='')	// mantiene los valores anteriores.
		 				return;
		 				
			 		cod_producto.value = '';
					
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					
					
					set_value('NOM_PRODUCTO_' + record, '', '');
					precio.innerHTML 	= '';
					precio_h.value 		= '';
					motivo.value		= '';
					tipo_TE.value		= '';
					cantidad.value		= '';		
					
					recalc_computed_relacionados(record, 'PRECIO');	
				}		
				else {
					var vl_boton_precio = document.getElementById('BOTON_PRECIO_' + record)
					if (vl_boton_precio)
						vl_boton_precio.value = 'TE';
					var res = returnVal.split('|');	
					var precio 			= document.getElementById('PRECIO_'+ record);
					var precio_h 		= document.getElementById('PRECIO_H_' + record);
					var motivo  		= document.getElementById('MOTIVO_TE_' + record);
					var tipo_TE  		= document.getElementById('COD_TIPO_TE_' + record);		
					var cantidad  		= document.getElementById('CANTIDAD_' + record);
					var motivo_autoriza_te	= document.getElementById('MOTIVO_AUTORIZA_TE_' + record);	
					
					set_value('NOM_PRODUCTO_' + record, res[0], res[0]);
					set_value('PRECIO_' + record, res[1], number_format(res[1], 0, ',', '.'));
					precio_h.value 		= res[1];
					motivo.value		= res[2];
					tipo_TE.value		= res[3];
					if (nom_usuario_autoriza_te)
						motivo_autoriza_te.value  = res[4];
			
					cantidad.focus();		
					
					recalc_computed_relacionados(record, 'PRECIO');
				}
				
				return true;	
			}
		});
	}
}