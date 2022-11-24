<?php
class dw_item extends datawindow {
	private	$field_cod_producto;
	
	function dw_item($sql, $label_record, $b_add_line_visible, $b_del_line_visible, $field_cod_producto) {
		parent::datawindow($sql, $label_record, $b_add_line_visible, $b_del_line_visible);
		$this->field_cod_producto = $field_cod_producto;
	}
	function fill_record(&$temp, $record) {
		$cod_producto = $this->get_item($record, $this->field_cod_producto);
			
		// Esconde CANTIDAD para TITULO
		if ($cod_producto=='T') 
			$this->controls['CANTIDAD']->type = 'hidden';
		else
			$this->controls['CANTIDAD']->type = 'text';
			
		// cambiae l nombre del boton PRECIO para TE
		if ($cod_producto=='TE')
			$temp->setVar($this->label_record.'.NOM_BOTON_PRECIO', 'TE');
		else
			$temp->setVar($this->label_record.'.NOM_BOTON_PRECIO', 'Precio');

		// llama al ancestro
		parent::fill_record($temp, $record);
		
		// Vuelve a setear los  DISABLE_BUTTON para los TITULO
		if ($this->entrable && $cod_producto!='T')
			$temp->setVar($this->label_record.'.DISABLE_BUTTON', '');
		else
			$temp->setVar($this->label_record.'.DISABLE_BUTTON', 'disabled="disabled"');
	}
	function fill_template(&$temp) {
		parent::fill_template($temp);
		if ($this->entrable) {
			$agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
			$temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
		}
	}
}
class w_cot_nv extends w_input {
	const K_PARAM_PORC_DSCTO_MAX 	=26;
	
	var $dw_tabla;
	var $dw_tabla_item;
	
	function w_cot_nv($tabla, $cod_item_menu) {
		parent::w_input($tabla, $cod_item_menu);
		$this->dw_tabla = 'dw_'.$this->nom_tabla;
		$this->dw_tabla_item = 'dw_item_'.$this->nom_tabla;
	}
	function add_controls_dscto($nro_dscto) {
		/* Agresa los controls y los java script necesarios para manejar el dscto1 y dscto2.  
		   Estos dsctos son usados en la cotizacion, nota_venta_ factura, etc
		   
		   Se asumen record == 0 siempre!!
		*/
		$this->dws[$this->dw_tabla]->add_control($control = new edit_porcentaje('PORC_DSCTO'.$nro_dscto,4,4));
		$java_script = "document.getElementById('MONTO_DSCTO".$nro_dscto."_0').value = Math.round(to_num(document.getElementById('PORC_DSCTO".$nro_dscto."_0').value)/100 * ";
		if ($nro_dscto==1)
			$java_script .= "document.getElementById('SUM_TOTAL_H_0').value, 0);";
		else
			$java_script .= "(document.getElementById('SUM_TOTAL_H_0').value - document.getElementById('MONTO_DSCTO1_0').value), 0);";		
		$java_script .= "document.getElementById('INGRESO_USUARIO_DSCTO".$nro_dscto."_0').value = 'P';";
		$java_script .= "computed(0, 'TOTAL_NETO');";
		$control->set_onChange($java_script);
		
		$this->dws[$this->dw_tabla]->add_control($control =new edit_precio('MONTO_DSCTO'.$nro_dscto));
		$java_script = "var valor = to_num(document.getElementById('MONTO_DSCTO".$nro_dscto."_0').value) * 100/";
		if ($nro_dscto==1)
			$java_script .= "document.getElementById('SUM_TOTAL_H_0').value;";
		else
			$java_script .= "(document.getElementById('SUM_TOTAL_H_0').value - document.getElementById('MONTO_DSCTO1_0').value);";
		$java_script .= "valor = number_format(roundNumber(valor, 1), 1, ',', '');";		// redondea a 1 decimal
		$java_script .= "document.getElementById('PORC_DSCTO".$nro_dscto."_0').value = valor;";
		$java_script .= "document.getElementById('INGRESO_USUARIO_DSCTO".$nro_dscto."_0').value = 'M';";		
		$control->set_onChange($java_script);
		$this->dws[$this->dw_tabla]->add_control(new edit_text('INGRESO_USUARIO_DSCTO'.$nro_dscto,1 , 1, 'hidden'));
	}
	function add_controls_cot_nv() {
		// vendedores
		$sql_usuario_vendedor =  "select 	COD_USUARIO
											,NOM_USUARIO
											,convert(varchar, PORC_PARTICIPACION) + '-' + convert(varchar, PORC_DESCUENTO_PERMITIDO) PORC 
									from USUARIO
									where ES_VENDEDOR = 'S'
									  and AUTORIZA_INGRESO = 'S'
									order by NOM_USUARIO asc";
		$this->dws[$this->dw_tabla]->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR1',$sql_usuario_vendedor,110));
		$this->dws[$this->dw_tabla]->controls['COD_USUARIO_VENDEDOR1']->set_onChange("change_vendedor(this)");
		$this->dws[$this->dw_tabla]->add_control(new edit_porcentaje('PORC_VENDEDOR1',2.5,5,2));	
		$this->dws[$this->dw_tabla]->controls['PORC_VENDEDOR1']->set_onChange("this.value = change_porc_vendedor(this);");
	
		$this->dws[$this->dw_tabla]->add_control(new drop_down_dw('COD_USUARIO_VENDEDOR2',$sql_usuario_vendedor,110));
		$this->dws[$this->dw_tabla]->controls['COD_USUARIO_VENDEDOR2']->set_onChange("change_vendedor(this)");
		$this->dws[$this->dw_tabla]->add_control(new edit_porcentaje('PORC_VENDEDOR2',2.5,5,2));
		//Math.min(to_num(this.value), roundNumber(document.getElementById('COD_USUARIO_VENDEDOR2_0').options[document.getElementById('COD_USUARIO_VENDEDOR2_0').selectedIndex].label, 2));
		$this->dws[$this->dw_tabla]->controls['PORC_VENDEDOR2']->set_onChange("this.value =  = change_porc_vendedor(this);");		

		// moneda
		$sql_moneda 			= "	select			COD_MONEDA
													,NOM_MONEDA
													,ORDEN
									from 			MONEDA
									order by		ORDEN";
		$this->dws[$this->dw_tabla]->add_control(new drop_down_dw('COD_MONEDA', $sql_moneda,150));
		$this->dws[$this->dw_tabla]->set_entrable('COD_MONEDA', false);
		
		// referencia
		$this->dws[$this->dw_tabla]->add_control(new edit_text_upper('REFERENCIA',100,150));
		
		// TOTALES
		$this->add_controls_dscto(1);
		$this->add_controls_dscto(2);

		$this->dws[$this->dw_tabla]->set_computed('TOTAL_NETO', '[SUM_TOTAL] - [MONTO_DSCTO1] - [MONTO_DSCTO2]');
		
		//************
		// elimina las referencias a this.id de los script de MONTO_DSCTO1 y MONTO_DSCTO2, para que funcione el js calc_dscto()
		$java = $this->dws[$this->dw_tabla]->controls['MONTO_DSCTO1']->get_onChange();
		$java = str_replace("this.id", "'MONTO_DSCTO1_0'", $java);
		$this->dws[$this->dw_tabla]->controls['MONTO_DSCTO1']->set_onChange($java);

		$java = $this->dws[$this->dw_tabla]->controls['MONTO_DSCTO2']->get_onChange();
		$java = str_replace("this.id", "'MONTO_DSCTO2_0'", $java);
		$this->dws[$this->dw_tabla]->controls['MONTO_DSCTO2']->set_onChange($java);
		//****************

		$this->dws[$this->dw_tabla]->add_control(new drop_down_iva());
		$this->dws[$this->dw_tabla]->set_computed('MONTO_IVA', '[TOTAL_NETO] * [PORC_IVA] / 100');
		$this->dws[$this->dw_tabla]->set_computed('TOTAL_CON_IVA', '[TOTAL_NETO] + [MONTO_IVA]');

		// asigna los mandatorys
		$this->dws[$this->dw_tabla]->set_mandatory('COD_USUARIO_VENDEDOR1', 'Vendedor 1');
		$this->dws[$this->dw_tabla]->set_mandatory('PORC_VENDEDOR1', 'Porcentaje a Vendedor 1');
		$this->dws[$this->dw_tabla]->set_mandatory('COD_MONEDA', 'Moneda');
		$this->dws[$this->dw_tabla]->set_mandatory('REFERENCIA', 'Referencia');
	}
	function valores_default_vend() {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_USUARIO
						, PORC_PARTICIPACION
						, PORC_DESCUENTO_PERMITIDO
				from USUARIO 
				where COD_USUARIO = $this->cod_usuario 
				  and es_vendedor = 'S'";
		$result = $db->build_results($sql);
		if (count($result)>0) {
			$this->dws[$this->dw_tabla]->set_item(0, 'COD_USUARIO_VENDEDOR1',$this->cod_usuario);
			$this->dws[$this->dw_tabla]->set_item(0, 'PORC_VENDEDOR1', $result[0]['PORC_PARTICIPACION']);
			$this->dws[$this->dw_tabla]->set_item(0, 'PORC_DSCTO_MAX', $result[0]['PORC_DESCUENTO_PERMITIDO']);
		}
	}
	function get_values_from_POST() {
		/* el SUBTOTAL se debe copiar de SUM_TOTAL y forzar reclacular los computed, porque ambos valores estan dw distintas
		*/
		parent::get_values_from_POST();
		$this->dws[$this->dw_tabla]->set_item(0, 'SUM_TOTAL', $this->dws[$this->dw_tabla_item]->get_item(0, 'SUM_TOTAL'));
		$this->dws[$this->dw_tabla]->calc_computed();
	}
	function que_precio_usa($cod_cotizacion) {
		$this->redraw();
		$this->save_SESSION();
		$this->need_redraw();
		session::set('usa_precio_prod', 'usa_precio_prod');
		print "<script type='text/javascript'>
						var args = 'location:no;dialogLeft:100px;dialogTop:300px;dialogWidth:700px;dialogHeight:300px;dialogLocation:0;';
						var returnVal = window.showModalDialog('../common_appl/que_precio_usa.php?cod_cotizacion=$cod_cotizacion', '_blank', args);
						if (returnVal==null)
							document.location='".$this->root_url."appl/".$this->nom_tabla."/wi_".$this->nom_tabla.".php';
						else
							document.location='".$this->root_url."appl/".$this->nom_tabla."/wi_".$this->nom_tabla.".php?usa_precio_prod=1';
				</script>";	 
	}
	function usa_precio_prod() {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$suma = 0;			
		for($i=0; $i<$this->dws[$this->dw_tabla_item]->row_count(); $i++){							
			$cod_producto 	= $this->dws[$this->dw_tabla_item]->get_item($i, 'COD_PRODUCTO');
			$result			= $db->build_results("select PRECIO_VENTA_PUBLICO, PRECIO_LIBRE from PRODUCTO where COD_PRODUCTO = '$cod_producto'");
			
			// para los TE, E, I, etc Se los salta
			if ($result[0]['PRECIO_LIBRE']=='S') 
				continue;
			
			$precio_bd		= $result[0]['PRECIO_VENTA_PUBLICO'];					
			$this->dws[$this->dw_tabla_item]->set_item($i, 'PRECIO', $precio_bd);
			$cantidad 		= $this->dws[$this->dw_tabla_item]->get_item($i, 'CANTIDAD');
			$total			= $cantidad * $precio_bd;
			$this->dws[$this->dw_tabla_item]->set_item($i, 'TOTAL', $total);
			$suma 			+= $total;								
		}	
										
		$this->dws[$this->dw_tabla_item]->set_item(0,'SUM_TOTAL', $suma);					
				
		/* DESCUENTO 1 */ 
		$tipo_dcto1	 	= $this->dws[$this->dw_tabla]->get_item(0, 'INGRESO_USUARIO_DSCTO1');
		$porc_dcto1		= $this->dws[$this->dw_tabla]->get_item(0, 'PORC_DSCTO1');
		$mto_porc1 		= $this->dws[$this->dw_tabla]->get_item(0, 'MONTO_DSCTO1');		
				
		if($tipo_dcto1== 'P'){
			$mto_porc1 = round($suma*($porc_dcto1/100), 0);			
			$this->dws[$this->dw_tabla]->set_item(0,'MONTO_DSCTO1', $mto_porc1);										
		}
		else{
			if ($suma==0)
				$porc_dcto1 = 0;			
			else
				$porc_dcto1 = $mto_porc1*(100/$suma);			
			$this->dws[$this->dw_tabla]->set_item(0,'PORC_DSCTO1', $porc_dcto1);							
		}				
		
		
		$suma 			= $suma - $mto_porc1;
		
		/* DESCUENTO 2*/			
		$tipo_dcto2	 	= $this->dws[$this->dw_tabla]->get_item(0, 'INGRESO_USUARIO_DSCTO2');
		$porc_dcto2		= $this->dws[$this->dw_tabla]->get_item(0, 'PORC_DSCTO2');
		$mto_porc2		= $this->dws[$this->dw_tabla]->get_item(0, 'MONTO_DSCTO2');
							
		if($tipo_dcto2 == 'P'){
			$mto_porc2 = round($suma*($porc_dcto2/100), 0);			
			$this->dws[$this->dw_tabla]->set_item(0,'MONTO_DSCTO2', $mto_porc2);										
		}
		else{
			if ($suma==0)
				$porc_dcto2 = 0;			
			else
				$porc_dcto2 = $mto_porc2*(100/$suma);			
			$this->dws[$this->dw_tabla]->set_item(0,'PORC_DSCTO2', $porc_dcto2);							
		}	
		
		
		$t_neto 	= $suma-($mto_porc1-$mto_porc2);
		$monto_iva 	= round($t_neto * ($this->dws[$this->dw_tabla]->get_item(0, 'PORC_IVA')/100), 0);
		$t_iva 		= $t_neto+$monto_iva;
		
		$this->dws[$this->dw_tabla]->set_item(0,'TOTAL_NETO', $t_neto);
		$this->dws[$this->dw_tabla]->set_item(0,'MONTO_IVA', $monto_iva);
		$this->dws[$this->dw_tabla]->set_item(0,'TOTAL_CON_IVA', $t_iva);
	}
	function procesa_event() {
		if (session::is_set('usa_precio_prod')) {			
			session::un_set('usa_precio_prod');
			if (isset($_REQUEST['usa_precio_prod']))
				$this->usa_precio_prod();
		}
		parent::procesa_event();
	}

	function get_orden_min($nom_tabla) {		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT TOP 1 COD_$nom_tabla AS CODIGO FROM $nom_tabla ORDER BY ORDEN";
		$result = $db->build_results($sql);
		
		$orden = $result[0]['CODIGO'];
		return $orden;		  
	}
	
	
}
?>