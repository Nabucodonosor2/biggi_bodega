<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../factura/class_wi_factura.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");


class dw_arriendo extends datawindow {
	function dw_arriendo() {
		$sql = "select A.COD_ARRIENDO
						,A.NOM_ARRIENDO
						,A.REFERENCIA
						,isnull(sum(I.CANTIDAD * I.PRECIO), 0)	TOTAL_ARRIENDO
				from ITEM_FACTURA I, ITEM_ARRIENDO IA, ARRIENDO A
				where I.COD_FACTURA = {KEY1}
				  and IA.COD_ITEM_ARRIENDO = I.COD_ITEM_DOC
				  and A.COD_ARRIENDO = IA.COD_ARRIENDO
				group by A.COD_ARRIENDO, A.NOM_ARRIENDO, A.REFERENCIA";
		parent::datawindow($sql, 'ARRIENDO');
		
		$this->add_control(new static_num('TOTAL_ARRIENDO'));
	}
}
class wi_factura_arriendo extends wi_factura {
	function wi_factura_arriendo($cod_item_menu) {
		parent::wi_factura($cod_item_menu);
		$this->nom_tabla = 'factura_arriendo';
		$this->nom_template = "wi_".$this->nom_tabla.".htm";

		// no se usa los item_fa, se cambia un el select para evitar que haga un load de muchos registros
		$sql = " SELECT null COD_ITEM_FACTURA,
						null COD_FACTURA,
						null ORDEN,
						null ITEM,
						null COD_PRODUCTO,
						null COD_PRODUCTO_OLD,
						null NOM_PRODUCTO,
						null CANTIDAD,
						null PRECIO,
						null COD_ITEM_DOC,
						null CANTIDAD_POR_FACTURAR,
						null TD_DISPLAY_CANT_POR_FACT,	
						null TD_DISPLAY_ELIMINAR,
						null COD_TIPO_TE,
						null MOTIVO_TE,
						null BOTON_PRECIO
					where 1=2";
		$this->dws['dw_item_factura']->set_sql($sql);
		////////
		
		$this->dws['dw_arriendo'] = new dw_arriendo();
	}
	function load_wo() {
		if ($this->tiene_wo)
			$this->wo = session::get("wo_factura_arriendo");
	}
	function habilitar(&$temp, $habilita) {
		$nro_factura = $this->dws['dw_factura']->get_item(0, 'NRO_FACTURA');
		if ($this->priv_impresion=='S' && $nro_factura!='') {
			$control = '<input name="b_print_anexo" id="b_print_anexo" src="../../images_appl/b_print_anexo.jpg" type="image" '.
								 'onMouseDown="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_click.jpg\',1)" '.
								 'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
								 'onMouseOver="MM_swapImage(\'b_print_anexo\',\'\',\'../../images_appl/b_print_anexo_over.jpg\',1)" '.
								 'onClick = "dlg_print_anexo();"';
			$temp->setVar("WI_PRINT_ANEXO", $control);
		}
		else
			$temp->setVar("WI_PRINT_ANEXO", '<img src="../../images_appl/b_print_anexo_d.jpg"/>');
	}
	function make_sql_auditoria() {
		// cambia de factura_arriendo a factura y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'factura';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		// cambia de factura_arriendo a factura y luego lo devuelve
		$nom_tabla = $this->nom_tabla;
		$this->nom_tabla = 'factura';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla;
		return $sql;
	}
	function delete_record($db) {
		return $db->EXECUTE_SP("spu_factura", "'DELETE', ".$this->get_key());
	}
	function load_record() {
		parent::load_record();
		$cod_factura = $this->get_key();
		$this->dws['dw_arriendo']->retrieve($cod_factura);

		// nada modificable
		$this->dws['dw_factura']->set_entrable('NRO_ORDEN_COMPRA'      	 , false);
		$this->dws['dw_factura']->set_entrable('FECHA_FACTURA'			 , false);
		$this->dws['dw_factura']->set_entrable('REFERENCIA'				 , false);
		$this->dws['dw_factura']->set_entrable('RETIRADO_POR'			 , false);
		$this->dws['dw_factura']->set_entrable('GUIA_TRANSPORTE'		 , false);
		$this->dws['dw_factura']->set_entrable('PATENTE'				 , false);
		$this->dws['dw_factura']->set_entrable('OBS'					 , false);
		$this->dws['dw_factura']->set_entrable('RUT_RETIRADO_POR'		 , false);
		$this->dws['dw_factura']->set_entrable('DIG_VERIF_RETIRADO_POR'	 , false);
		
		$this->dws['dw_factura']->set_entrable('NOM_EMPRESA'			, false);
		$this->dws['dw_factura']->set_entrable('ALIAS'					, false);
		$this->dws['dw_factura']->set_entrable('COD_EMPRESA'			, false);
		$this->dws['dw_factura']->set_entrable('RUT'					, false);
		$this->dws['dw_factura']->set_entrable('COD_SUCURSAL_FACTURA'	, false);
		$this->dws['dw_factura']->set_entrable('COD_PERSONA'			, false);

		$this->dws['dw_factura']->set_entrable('COD_USUARIO_VENDEDOR2'	, false);
		$this->dws['dw_factura']->set_entrable('COD_ORIGEN_VENTA'		, false);
		$this->dws['dw_factura']->set_entrable('CANCELADA'				, false);
		$this->dws['dw_factura']->set_entrable('GENERA_SALIDA'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_DSCTO1'			, false);
		$this->dws['dw_factura']->set_entrable('MONTO_DSCTO1'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_DSCTO2'			, false);
		$this->dws['dw_factura']->set_entrable('MONTO_DSCTO2'			, false);
		$this->dws['dw_factura']->set_entrable('PORC_IVA'				, false);
	}
	function print_anexo() {
		$tipo_print = $_POST['wi_hidden'];
		$cod_factura = $this->get_key();
		$nro_factura = $this->dws['dw_factura']->get_item(0, 'NRO_FACTURA');
		$fecha_factura = $this->dws['dw_factura']->get_item(0, 'FECHA_FACTURA');
		$nom_empresa = $this->dws['dw_factura']->get_item(0, 'NOM_EMPRESA');
		$direccion_empresa = $this->dws['dw_factura']->get_item(0, 'DIRECCION_FACTURA');
		$referencia = $this->dws['dw_factura']->get_item(0, 'REFERENCIA');
		
		if ($tipo_print=='PDF') {
			$nom_empresa = $this->dws['dw_factura']->get_item(0, 'NOM_EMPRESA');
			$sql = "select F.NOM_EMPRESA
							,A.COD_ARRIENDO
							,A.NOM_ARRIENDO
							,A.REFERENCIA
							,I.ITEM
							,I.COD_PRODUCTO
							,I.NOM_PRODUCTO
							,I.CANTIDAD
							,I.PRECIO
							,Round(I.CANTIDAD * I.PRECIO, 0) TOTAL
					from ITEM_FACTURA I, FACTURA F, ITEM_ARRIENDO IA, ARRIENDO A
					where I.COD_FACTURA = $cod_factura
					  and F.COD_FACTURA = I.COD_FACTURA
					  and IA.COD_ITEM_ARRIENDO = I.COD_ITEM_DOC
					  and A.COD_ARRIENDO = IA.COD_ARRIENDO
					order by IA.COD_ARRIENDO, I.ORDEN";
	
			// reporte
			$labels = array();
			$labels['strNRO_FACTURA'] = $nro_factura;					
			$labels['strNOM_EMPRESA'] = $nom_empresa;					
			$xml = $this->find_file('factura_arriendo', 'factura_anexo.xml');					
			$rpt = new reporte($sql, $xml, $labels, "Factura_anexo".$cod_factura, 0);
		}
		else if ($tipo_print=='XLS') {
			
			error_reporting(E_ALL & ~E_NOTICE);
			require_once dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_workbook.inc.php";
			require_once dirname(__FILE__)."/../../../../commonlib/trunk/php/php_writeexcel-0.3.0/class.writeexcel_worksheet.inc.php";		
			
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);				 	
			$sql = "select F.NRO_FACTURA
							,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA
							,F.NOM_EMPRESA
							,F.RUT
							,F.DIG_VERIF
							,F.DIRECCION
							,COM.NOM_COMUNA
							,CIU.NOM_CIUDAD
							,PAI.NOM_PAIS
							,F.TELEFONO
							,F.FAX
							,F.REFERENCIA
							,A.COD_ARRIENDO
							,I.COD_PRODUCTO
							,I.NOM_PRODUCTO
							,I.CANTIDAD
							,I.PRECIO							
							,Round(I.CANTIDAD * I.PRECIO, 0) TOTAL
							,A.CENTRO_COSTO_CLIENTE
							,A.NOM_ARRIENDO
					from ITEM_FACTURA I, FACTURA F left outer join COMUNA COM on F.COD_COMUNA = COM.COD_COMUNA, CIUDAD CIU, PAIS PAI, ITEM_ARRIENDO IA, ARRIENDO A
					where I.COD_FACTURA = $cod_factura
					  and F.COD_FACTURA = I.COD_FACTURA
					  and CIU.COD_CIUDAD = F.COD_CIUDAD
					  and PAI.COD_PAIS = F.COD_PAIS
					  and IA.COD_ITEM_ARRIENDO = I.COD_ITEM_DOC
					  and A.COD_ARRIENDO = IA.COD_ARRIENDO
					order by IA.COD_ARRIENDO, I.ORDEN";

			$res = $db->query($sql);


			$fname = tempnam("/tmp", "export.xls");
			$workbook = &new writeexcel_workbook($fname);		
			$worksheet = $workbook->addworksheet('FACTURA_'.$nro_factura);
			
			//AYUDA FUNCIONES
			//set_row($row, $height, $XF) OJO $row parte en cero	
			//set_column($firstcol, $lastcol, $width, $format, $hidden)
						
			//SETEA TAMAÑOS DE COLUMNAS
			$worksheet->set_row(0, 60);
			$worksheet->set_column(0, 4, 15);
			$worksheet->set_column(5, 5, 15);
			$worksheet->set_column(6, 6, 65);
			$worksheet->set_column(7, 7, 14);
			$worksheet->set_column(8, 8, 15);
			$worksheet->set_column(9, 9, 15);
			$worksheet->set_column(10, 10, 10);
			$worksheet->set_column(11, 11, 35);
			
			
			//INICIO FORMATOS DE CELDA
			$text =& $workbook->addformat();
			$text->set_font("Verdana");
			$text->set_valign('vcenter');
	    
			$text_bold =& $workbook->addformat();
			$text_bold->copy($text);
			$text_bold->set_bold(1);
		
			$text_blue_bold_left =& $workbook->addformat();
			$text_blue_bold_left->copy($text_bold);
			$text_blue_bold_left->set_align('left');
			$text_blue_bold_left->set_color('blue_0x20');
	
			$text_blue_bold_center =& $workbook->addformat();
			$text_blue_bold_center->copy($text_bold);
			$text_blue_bold_center->set_align('center');
			$text_blue_bold_center->set_color('blue_0x20');
			
			$text_blue_bold_right =& $workbook->addformat();
			$text_blue_bold_right->copy($text_bold);
			$text_blue_bold_right->set_align('right');
			$text_blue_bold_right->set_color('blue_0x20');
	
			$text_nro_docto =& $workbook->addformat();
			$text_nro_docto->copy($text_blue_bold_right);
			$text_nro_docto->set_size(13);
			
			$text_pie_de_pagina =& $workbook->addformat();
			$text_pie_de_pagina->copy($text_blue_bold_left);
			$text_pie_de_pagina->set_size(8);
			
			$text_normal_left =& $workbook->addformat();
			$text_normal_left->copy($text);
			$text_normal_left->set_align('left');
			
			$text_normal_center =& $workbook->addformat();
			$text_normal_center->copy($text);
			$text_normal_center->set_align('center');
			
			$text_normal_right =& $workbook->addformat();
			$text_normal_right->copy($text);
			$text_normal_right->set_align('right');
					
			$text_normal_bold_left =& $workbook->addformat();
			$text_normal_bold_left->copy($text_bold);
			$text_normal_bold_left->set_align('left');
			
			
			$text_normal_bold_center =& $workbook->addformat();
			$text_normal_bold_center->copy($text_bold);
			$text_normal_bold_center->set_align('center');
		
			$text_normal_bold_right =& $workbook->addformat();
			$text_normal_bold_right->copy($text_bold);
			$text_normal_bold_right->set_align('right');
		
			
			$titulo_item_border_all =& $workbook->addformat();
			$titulo_item_border_all->copy($text_blue_bold_center);
			$titulo_item_border_all->set_border_color('black');
			$titulo_item_border_all->set_top(2);
			$titulo_item_border_all->set_bottom(2);
			$titulo_item_border_all->set_right(2);
			$titulo_item_border_all->set_left(2);
			
			$titulo_item_border_all_right =& $workbook->addformat();
			$titulo_item_border_all_right->copy($titulo_item_border_all);
			$titulo_item_border_all_right->set_align('right');
			
			$titulo_item_border_all_merge =& $workbook->addformat();
			$titulo_item_border_all_merge->copy($titulo_item_border_all);
			$titulo_item_border_all_merge->set_merge();
					
		
			$border_item_left = & $workbook->addformat();
			$border_item_left->copy($text_normal_left);
			$border_item_left->set_border_color('black');
			$border_item_left->set_left(2);
			
			$border_item_left_nom_producto = & $workbook->addformat();
			$border_item_left_nom_producto->copy($border_item_left);
			$border_item_left_nom_producto->set_right(2);
			
			
			
			$border_item_left_bold = & $workbook->addformat();
			$border_item_left_bold->copy($text_bold);
			$border_item_left_bold->set_border_color('black');
			$border_item_left_bold->set_left(2);
			
			$border_item_center = & $workbook->addformat();
			$border_item_center->copy($text_normal_center);
			$border_item_center->set_border_color('black');
			$border_item_center->set_left(2);
			$border_item_center->set_right(2);
			
			$border_item_right = & $workbook->addformat();
			$border_item_right->copy($text_normal_right);
			$border_item_right->set_border_color('black');
			$border_item_right->set_right(2);		
			
			$cant_normal =& $workbook->addformat();
			$cant_normal->copy($border_item_right);
			$cant_normal->set_num_format('0.0');
			
					
			$monto_normal =& $workbook->addformat();
			$monto_normal->copy($border_item_right);
			$monto_normal->set_num_format('#,##0');

			$total_por_contrato =& $workbook->addformat();
			$total_por_contrato->set_num_format('#,##0');
			$total_por_contrato->set_top(2);
			
			
			$border_item_top = & $workbook->addformat();
			$border_item_top->copy($text);
			$border_item_top->set_border_color('black');
			$border_item_top->set_top(2);
			
			$border_item_bottom = & $workbook->addformat();
			$border_item_bottom->copy($text);
			$border_item_bottom->set_border_color('black');
			$border_item_bottom->set_bottom(2);
			
			$border_item_especial_left = & $workbook->addformat();
			$border_item_especial_left->copy($text_normal_left);
			$border_item_especial_left->set_border_color('black');
			$border_item_especial_left->set_left(2);
			$border_item_especial_left->set_right(2);

			$border_item_vacio_bottom_right = & $workbook->addformat();
			$border_item_vacio_bottom_right->copy($text);
			$border_item_vacio_bottom_right->set_bottom(2);
			//$border_item_vacio_bottom_right->set_right(2);
			//FIN FORMATOS DE CELDA
			
			//LOGO
			$worksheet->insert_bitmap('A1',$this->root_dir."images_appl/logo_reporte_excel.bmp");
			
			//LINEA ROJA
			$i = 1;
			$worksheet->set_row(1, 5);
			$text_linea_roja =& $workbook->addformat();
			$text_linea_roja->set_bg_color("red");
			$worksheet->write($i, 0,'', $text_linea_roja);	
			$worksheet->write($i, 1,'', $text_linea_roja);		
			$worksheet->write($i, 2,'', $text_linea_roja);	
			$worksheet->write($i, 3,'', $text_linea_roja);	
			$worksheet->write($i, 4,'', $text_linea_roja);	
			$worksheet->write($i, 5,'', $text_linea_roja);	
			$worksheet->write($i, 6,'', $text_linea_roja);	
			$worksheet->write($i, 7,'', $text_linea_roja);
			$worksheet->write($i, 8,'', $text_linea_roja);
			$worksheet->write($i, 9,'', $text_linea_roja);			
			$worksheet->write($i, 10,'', $text_linea_roja);	
			$worksheet->write($i, 11,'', $text_linea_roja);	
				
			
			//ESCRIBE ENCABEZADOS		
			$header =& $workbook->addformat();
			$header->set_bold();
			$header->set_color('blue');	
			
			
			$text =& $workbook->addformat();
			$text->set_font("Verdana");
			$text->set_valign('vcenter');
			$text->set_bold();
			$text->set_color('blue');
			
			$text_subrayado =& $workbook->addformat();
			$text_subrayado->copy($text);
			$text_subrayado->set_underline();
		
					
			//CABECERA
			$i = 3;
			$worksheet->write($i, 0, 'Nº FACTURA: '.$nro_factura, $text);
			$i++;
			$worksheet->write($i, 0, 'FECHA : '.$fecha_factura, $text);
			$i++;
			$worksheet->write($i, 0, 'RAZON SOCIAL : '.$nom_empresa, $text);
			$i++;
			$worksheet->write($i, 0, '                              '.$direccion_empresa, $text_subrayado);
			$i++;
			$worksheet->write($i, 0, 'REFERENCIA : '.$referencia, $text);
			$i++;
			
			//TITULOS DE COLUMNA
			$i++;
			$worksheet->write($i, 0, 'Sociedad', $titulo_item_border_all);
			$worksheet->write($i, 1, 'Rut Proveedor', $titulo_item_border_all);
			$worksheet->write($i, 2, 'Nº Factura', $titulo_item_border_all_right);
			$worksheet->write($i, 3, 'Fecha Factura', $titulo_item_border_all);
			$worksheet->write($i, 4, 'Nº Contrato', $titulo_item_border_all);
			$worksheet->write($i, 5, 'Modelo', $titulo_item_border_all);		
			$worksheet->write($i, 6, 'Descripción Equipo o Servicio', $titulo_item_border_all);		
			$worksheet->write($i, 7, 'Cantidad', $titulo_item_border_all_right);		
			$worksheet->write($i, 8, 'Precio $', $titulo_item_border_all_right);
			$worksheet->write($i, 9, 'Total $', $titulo_item_border_all_right);			
			$worksheet->write($i, 10, 'CeCo', $titulo_item_border_all);		
			$worksheet->write($i, 11, 'Nombre CeCo', $titulo_item_border_all);		
			
			//ITEMS
			$i++;
			$cod_arriendo_ant = -1;
			$tot_arriendo = 0;
			while($my_row = $db->get_row()){
				
				if ($worksheet->_datasize > 7000000)
					break;
				
				$worksheet->write($i, 0, $my_row['RUT'].'-'.$my_row['DIG_VERIF'], $border_item_right);
				$worksheet->write($i, 1, '914620015', $border_item_right);
				$worksheet->write($i, 2, $my_row['NRO_FACTURA'], $border_item_right);
				$worksheet->write($i, 3, $my_row['FECHA_FACTURA'], $border_item_right);
				$worksheet->write($i, 4, $my_row['COD_ARRIENDO'], $border_item_right);
				$worksheet->write($i, 5, $my_row['COD_PRODUCTO'], $border_item_right);						
				$worksheet->write($i, 6, $my_row['NOM_PRODUCTO'], $border_item_left_nom_producto);						
				$worksheet->write($i, 7, $my_row['CANTIDAD'], $cant_normal);						
				$worksheet->write($i, 8, $my_row['PRECIO'], $monto_normal);	
				
				$worksheet->write($i, 9, $my_row['TOTAL'], $monto_normal);					
				
				$worksheet->write($i, 10, $my_row['CENTRO_COSTO_CLIENTE'], $border_item_right);						
				$worksheet->write($i, 11, $my_row['NOM_ARRIENDO'], $border_item_left_nom_producto);	
				
				$tot_arriendo = $tot_arriendo + $my_row['TOTAL'];			
				
				$i++;
				
				if ($cod_arriendo_ant != $my_row['COD_ARRIENDO']) {
					
					//// CIERRA LOS BORDES INFERIORES DEL GRUPO
					$worksheet->write($i, 0, '', $border_item_top);
					$worksheet->write($i, 1, '', $border_item_top);
					$worksheet->write($i, 2, '', $border_item_top);
					$worksheet->write($i, 3, '', $border_item_top);
					$worksheet->write($i, 4, '', $border_item_top);
					$worksheet->write($i, 5, '', $border_item_top);						
					$worksheet->write($i, 6, '', $border_item_top);						
					$worksheet->write($i, 7, '', $border_item_top);						
					$worksheet->write($i, 8, 'Total Contrato', $border_item_top);

					$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);					
					
					$worksheet->write($i, 10, '', $border_item_top);						
					$worksheet->write($i, 11, '', $border_item_top);
					
					// ABRE LOS BORDES DEL GRUPO SIGUIENTE
					$i += 2;
					$worksheet->write($i, 0, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 1, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 2, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 3, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 4, '', $border_item_vacio_bottom_right);
					$worksheet->write($i, 5, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 6, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 7, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 8, '', $border_item_vacio_bottom_right);

					$worksheet->write($i, 9, '', $border_item_vacio_bottom_right);					
					
					$worksheet->write($i, 10, '', $border_item_vacio_bottom_right);						
					$worksheet->write($i, 11, '', $border_item_vacio_bottom_right);
					
					
					
					//////
									
					$cod_arriendo_ant = $my_row['COD_ARRIENDO'];
					$tot_arriendo = 0;
					$i += 1;
				}
			}

			if ($worksheet->_datasize > 7000000) {
				$worksheet->write($i, 0, 'No se completo la exportación de datos porque excede el máximo del tamaño de archivo 7 MB', $header);
			}else{
					//// CIERRA LA ULTIMA COLUMNA DEL EXCEL
					$worksheet->write($i, 0, '', $border_item_top);
					$worksheet->write($i, 1, '', $border_item_top);
					$worksheet->write($i, 2, '', $border_item_top);
					$worksheet->write($i, 3, '', $border_item_top);
					$worksheet->write($i, 4, '', $border_item_top);
					$worksheet->write($i, 5, '', $border_item_top);						
					$worksheet->write($i, 6, '', $border_item_top);						
					$worksheet->write($i, 7, 'Total Contrato', $border_item_top);						
					$worksheet->write($i, 8, $tot_arriendo, $total_por_contrato);	

					$worksheet->write($i, 9, $tot_arriendo, $total_por_contrato);					
					
					$worksheet->write($i, 10, '', $border_item_top);						
					$worksheet->write($i, 11,'', $border_item_top);
			}
				
			$workbook->close();
	
			header("Content-Type: application/x-msexcel; name=\"FACTURA_".$nro_factura."\"");
			header("Content-Disposition: inline; filename=\"FACTURA_".$nro_factura.".xls\"");
			$fh=fopen($fname, "rb");
			fpassthru($fh);
			unlink($fname);	
		}
		$this->_load_record();
	}
	function procesa_event() {
		if(isset($_POST['b_print_anexo_x']))
			$this->print_anexo();
		else
			parent::procesa_event();
	}
}
?>