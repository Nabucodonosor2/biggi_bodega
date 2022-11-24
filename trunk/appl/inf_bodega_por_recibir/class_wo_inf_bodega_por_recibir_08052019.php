<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_bodega_por_recibir extends w_informe_pantalla {
	
   
   function wo_inf_bodega_por_recibir() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spu_bodega_por_recibir", "$cod_usuario"); 
   		$sql = "select	COD_SOL_COMPRA
            		,FECHA_SOL_COMPRA	
            		,TIPO
            		,NRO_OC
            		,ORDEN
            		,ALIAS_PROV
            		,COD_PRODUCTO				
            		,NOM_PRODUCTO				
            		,CANT_SOLICITADA			
            		,CANT_RECIBIDA				
            		,CANT_POR_RECIBIR	
            		,TERMINADO_COMPUESTO
                    ,COD_PRODUCTO_COMPUESTO
                    ,PRECIO_COMPRA
                    ,CASE  
                        WHEN COD_PRODUCTO_COMPUESTO IS NULL THEN 'NEGRITA'
                    END CLASS_NEGRITA
				FROM inf_bodega_por_recibir 
				where COD_USUARIO = $cod_usuario
                AND CANT_POR_RECIBIR > 0
                order by orden asc, tipo desc, COD_SOL_COMPRA asc";
				
		parent::w_informe_pantalla('inf_bodega_por_recibir', $sql, $_REQUEST['cod_item_menu']);
		
		//$this->dw->add_control(new edit_precio('PRECIO_COMPRA'));
		// headers
		$this->add_header(new header_num('COD_SOL_COMPRA', 'COD_SOL_COMPRA', 'Cod.'));
		$this->add_header($control = new header_date('FECHA_SOL_COMPRA', 'FECHA_SOL_COMPRA', 'Fecha'));
		//$control->field_bd_order = 'DATE_FACTURA';
		$this->add_header(new header_text('TIPO', "TIPO", 'Tipo'));
		$this->add_header(new header_num('NRO_OC', 'NRO_OC', 'OC'));
		$this->add_header(new header_text('ALIAS_PROV', "ALIAS_PROV", 'Proveedor'));
		$this->add_header(new header_text('COD_PRODUCTO', "COD_PRODUCTO", 'EQ Terminado'));
		$this->add_header(new header_text('COD_PRODUCTO_COMPUESTO', "COD_PRODUCTO_COMPUESTO", 'EQ Compuesto'));
		$this->add_header(new header_text('NOM_PRODUCTO', "NOM_PRODUCTO", 'Descripción'));
		$this->add_header(new header_num('CANT_SOLICITADA', 'CANT_SOLICITADA', 'CT Solicitada', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_RECIBIDA', 'CANT_RECIBIDA', 'CT Recibida', 0, true, 'SUM'));
		$this->add_header(new header_num('CANT_POR_RECIBIR', 'CANT_POR_RECIBIR', 'CT X Recibir', 0, true, 'SUM'));
		$this->add_header(new header_num('PRECIO_COMPRA', 'PRECIO_COMPRA', 'Costo $', 0, true, 'SUM'));
		
		$this->row_per_page = 230;	
		$this->dw->add_control(new static_num('CANT_SOLICITADA'));
		$this->dw->add_control(new static_num('CANT_RECIBIDA'));
		$this->dw->add_control(new static_num('CANT_POR_RECIBIR'));
		$this->dw->add_control(new static_num('PRECIO_COMPRA'));
   	}
   	function redraw($temp){
   		parent::redraw($temp);
   		
		$this->habilita_boton($temp, 'print', true);	
   	}
   	function habilita_boton(&$temp, $boton, $habilita) {
		parent::habilita_boton($temp, $boton, $habilita);
		if ($boton=='print') {
			if ($habilita){
				$temp->setVar("WO_PRINT", '<input name="b_print" id="b_print" src="../../../../commonlib/trunk/images/b_print.jpg" type="image" '.
														'onMouseDown="MM_swapImage(\'b_print\',\'\',\'../../../../commonlib/trunk/images/b_print_click.jpg\',1)" '.
														'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
														'onMouseOver="MM_swapImage(\'b_print\',\'\',\'../../../../commonlib/trunk/images/b_print_over.jpg\',1)" '.
														'onClick="dlg_print(); return false;" '.
														'/>');
			}else{
				$temp->setVar("WO_PRINT", '<img src="../../images_appl/b_print_seleccion_d.jpg"/>');
			}
		}
	}
	function print_inf($solicitud_compra = '') {
	    
	    if($solicitud_compra == ''){
			$solicitud_compra = 0;
			$param_solicitud =  'Todas';
		}
		else{
			$param_solicitud = $solicitud_compra;
		}
		
	    $xml = session::get('K_ROOT_DIR').'appl/inf_bodega_por_recibir/inf_bodega_por_recibir.xml';
	    
		// reporte
		$sql = "EXEC spi_bodega_por_recibir $solicitud_compra";
		
		$this->filtro .= "Fecha impresión = ".$this->current_date()."; ";		
		$this->filtro .= "Cod. Solicitud Compra = $param_solicitud";		
		
		$labels = array();
		$labels['str_filtro'] = $this->nom_filtro;
		$rpt = new reporte($sql, $xml, $labels, "Bodega por Recibir.pdf", true);

		$this->_redraw();
	}
/*	function detalle_record($rec_no) {
		session::set('DESDE_wo_factura', 'desde output');	// para indicar que viene del output
		session::set('DESDE_wo_inf_facturas_por_cobrar', 'true');
		$ROOT = $this->root_url;
		$url = $ROOT.'appl/factura';
		header ('Location:'.$url.'/wi_factura.php?rec_no='.$rec_no.'&cod_item_menu=1535');
	}*/
	
    
	function procesa_event() {
		if(isset($_POST['b_print_x'])){
		   $this->print_inf($_POST['wo_hidden']);
		}else{ 
			parent::procesa_event();
		}
	}
	
}

?>