<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class wo_orden_compra extends w_output {
	const K_AUTORIZA_SUMAR = '991535';
	var $checkbox_sumar;
	
   	function wo_orden_compra() {
		parent::w_base('orden_compra', $_REQUEST['cod_item_menu']);
		
		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA 
							,FECHA_ORDEN_COMPRA DATE_ORDEN_COMPRA             							                      
							,E.NOM_EMPRESA              
							,NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO
							,COD_DOC
							,SC.COD_PRODUCTO
							,SC.CANTIDAD
							,E.RUT
							,E.DIG_VERIF
							,EOC.COD_ESTADO_ORDEN_COMPRA
				from 		ORDEN_COMPRA O LEFT OUTER JOIN SOLICITUD_COMPRA SC ON O.COD_DOC = SC.COD_SOLICITUD_COMPRA
							,EMPRESA E
							,ESTADO_ORDEN_COMPRA EOC
				where									
							O.COD_EMPRESA = E.COD_EMPRESA and 
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA IN ('NOTA_VENTA', 'SOLICITUD_COMPRA') ";
		
		if($this->cod_usuario == 22)
			$sql .= "and O.COD_EMPRESA = 5 ";
							
		$sql .=" order by	COD_ORDEN_COMPRA desc";		
			
   		parent::w_output('orden_compra', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'N� OC'));
		$this->add_header($control = new header_date('FECHA_ORDEN_COMPRA', 'O.FECHA_ORDEN_COMPRA', 'Fecha'));
		$control->field_bd_order = 'DATE_ORDEN_COMPRA';
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by COD_ESTADO_ORDEN_COMPRA";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
		$this->add_header(new header_num('COD_DOC', 'COD_DOC', 'Cod. Solicitud'));
		$this->add_header(new header_text('COD_PRODUCTO', 'SC.COD_PRODUCTO', 'Modelo'));
		$this->add_header(new header_num('CANTIDAD', 'SC.CANTIDAD', 'Cant', 1));
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		
		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E') {
			$DISPLAY_SUMAR = '';
      	}
      	else {
			$DISPLAY_SUMAR = 'none';
      	}
		
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	
	function habilita_boton(&$temp, $boton, $habilita) {
		if ($boton=='add') {
			if ($habilita)
				$temp->setVar("WO_ADD", '<input name="b_'.$boton.'" id="b_'.$boton.'" type="button" onmouseover="entrada(this, \'../../../../commonlib/trunk/images/b_add_over.jpg\')" onmouseout="salida(this, \'../../../../commonlib/trunk/images/b_add.jpg\')" onmousedown="down(this, \'../../../../commonlib/trunk/images/b_add_click.jpg\')"'.
							'style="cursor:pointer;height:68px;width:66px;border: 0;background-image:url(../../../../commonlib/trunk/images/b_add.jpg);background-repeat:no-repeat;background-position:center;border-radius: 15px;"'.
							'onClick="dlg_add_orden_compra();" />');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_add_d.jpg"/>');
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
	function redraw(&$temp){
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
	}
	
   	function procesa_event(){
		if($_POST['HIZO_CLICK_0'] == 'S') {
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_NETO']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_NETO']->valor_filtro2;
			
			if ($this->checkbox_sumar) {
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'S');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto', 0, true, 'SUM'));
			}
			else{
				$this->dw_check_box->set_item(0, 'CHECK_SUMAR', 'N');
				$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
			}

			// vuelve a setear el friltro aplicado
			$this->headers['TOTAL_NETO']->valor_filtro = $valor_filtro;
			$this->headers['TOTAL_NETO']->valor_filtro2 = $valor_filtro2;
			
			$this->save_SESSION();	
			$this->make_filtros();
			$this->retrieve();	
		}else if(isset($_POST['b_add_x'])){
			if($_POST['wo_hidden'] <> ''){
				
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql = "SELECT COD_SOLICITUD_COMPRA
				        FROM SOLICITUD_COMPRA
				        WHERE COD_SOLICITUD_COMPRA = ".$_POST['wo_hidden'];
				$result = $db->build_results($sql);
				
				if(count($result) == 0){
					$this->alert("El n�mero de solicitud de Orden de Compra ingresado no existe.");
					$this->retrieve();
					return;
				}
				
				session::set('CREADO_DESDE_SOL_OC', $_POST['wo_hidden']);
			}
			
			$this->add();
		}else{ 
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}
	}
}

?>