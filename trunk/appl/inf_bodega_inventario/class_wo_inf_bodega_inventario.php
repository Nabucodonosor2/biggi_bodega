<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_param_informe_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_reporte_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_inf_bodega_inventario extends w_informe_pantalla {
	
   
   function wo_inf_bodega_inventario() {
   		/* El cod_usuario debe leerese desde la sesion porque no se puede usar $this->cod_usuario
   		   hasta despues de llamar al ancestro
   		 */  
   		$cod_usuario =  session::get("COD_USUARIO");
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		$db->EXECUTE_SP("spu_bodega_inventario", "$cod_usuario"); 

   		$sql = "select	COD_PRODUCTO				
                		,NOM_PRODUCTO				
                		,COD_MARCA					
                		,NOM_MARCA					
                		,CANTIDAD
                		,POR_RECIBIR
						,CASE dbo.f_bodega_eq_et(COD_PRODUCTO) when -999 
						THEN '-/-'
						ELSE convert(varchar,dbo.f_bodega_eq_et(COD_PRODUCTO))
						END STOCKET
						,CASE  when dbo.f_bodega_eq_et(COD_PRODUCTO) <= 10 
                         THEN 'RED'
                         ELSE  'BLACK'
                         END AUXCOLOR
				FROM inf_bodega_inventario 
				where COD_USUARIO = $cod_usuario
                order by NOM_PRODUCTO";


				
		parent::w_informe_pantalla('inf_bodega_inventario', $sql, $_REQUEST['cod_item_menu']);
		
		// headers
		$this->add_header(new header_text('COD_PRODUCTO', "COD_PRODUCTO", 'Modelo'));
		$this->add_header(new header_text('NOM_PRODUCTO', "NOM_PRODUCTO", 'Equipo'));
		
		$this->add_header(new header_num('CANTIDAD', 'CANTIDAD', 'Stock'));
		$this->add_header(new header_num('POR_RECIBIR', 'POR_RECIBIR', 'Por Recibir'));
		$this->add_header(new header_text('AUXCOLOR', 'AUXCOLOR', 'AUXCOLOR'));
		
		$this->add_header($control=new header_num('STOCKET', "dbo.f_bodega_eq_et(COD_PRODUCTO)", 'STOCKET'));
		
				
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
	function print_inf($cod_bodega,$nom_bodega,$fecha) {
	    
	    $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
	    $xml = session::get('K_ROOT_DIR').'appl/inf_bodega_inventario/inf_bodega_inventario.xml';
	    
		// reporte
		$fecha = $this->str2date($fecha, '23:59:59');
		
		$sql = "EXEC spi_bodega_inventario $cod_bodega,$fecha";
		
		$fecha1 = substr($fecha, 13, 2);
		$fecha2 = substr($fecha, 9, 3);
		$fecha3 = substr($fecha, 5, 4);
		
		$fechainf = $fecha1 . $fecha2 . '-' . $fecha3;
		
		$this->filtro = "Fecha = $fechainf \n \n";
		$this->filtro .= "Bodega = $nom_bodega";	
		
		$labels = array();
		$labels['str_filtro'] = $this->filtro;
		$rpt = new reporte($sql, $xml, $labels, "Inventario no Valorizado.pdf", true);

		$this->_redraw();
		//echo $sql;
		
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
		    $var = explode('|',$_POST['wo_hidden']);
		    $cod_bodega = $var[0];
		    $nom_bodega = $var[1];
		    $fecha      = $var[2];
		   $this->print_inf($cod_bodega,$nom_bodega,$fecha);
		}else{ 
			parent::procesa_event();
		}
	}
	
}

?>