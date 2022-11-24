<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
include(dirname(__FILE__)."/../../appl.ini");

class wo_gasto_fijo_base extends w_output_biggi {
   	const K_AUTORIZA_SUMAR = '994020';
   	const K_AUTORIZA_CREAR = '994025';
	var $checkbox_sumar;
	
	function wo_gasto_fijo_base() {
   		parent::w_base('gasto_fijo', $_REQUEST['cod_item_menu']);
   		$this->checkbox_sumar = false;
   		
		$sql = "select		COD_ORDEN_COMPRA                
							,convert(varchar(20), FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
							,FECHA_ORDEN_COMPRA DATE_GASTO_FIJO             							                      
							,E.NOM_EMPRESA              
							,REFERENCIA  
							,EOC.COD_ESTADO_ORDEN_COMPRA     
							,NOM_ESTADO_ORDEN_COMPRA			
							,TOTAL_NETO
							,E.RUT
							,E.DIG_VERIF   
				from 		ORDEN_COMPRA O
							,EMPRESA E
							,ESTADO_ORDEN_COMPRA EOC
				where		O.COD_EMPRESA = E.COD_EMPRESA and
							O.COD_ESTADO_ORDEN_COMPRA = EOC.COD_ESTADO_ORDEN_COMPRA and
							TIPO_ORDEN_COMPRA = 'GASTO_FIJO'";
		
		if($this->get_privilegio_opcion_usuario('2550', $this->cod_usuario)=='L')
			$sql .= " and O.COD_USUARIO = ".$this->cod_usuario;			
		
		$sql .= " order by	COD_ORDEN_COMPRA desc";								
   		parent::w_output_biggi('gasto_fijo', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_ORDEN_COMPRA','ORDEN_COMPRA'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
		$this->dw->add_control(new static_num('RUT'));
		// headers
		$this->add_header(new header_num('COD_ORDEN_COMPRA', 'COD_ORDEN_COMPRA', 'Nº GF'));
		$this->add_header(new header_rut('RUT', 'E', 'Rut'));
		$this->add_header($control = new header_date('FECHA_ORDEN_COMPRA', 'O.FECHA_ORDEN_COMPRA', 'Fecha'));
		$control->field_bd_order = 'DATE_GASTO_FIJO';
		$this->add_header(new header_text('NOM_EMPRESA', 'E.NOM_EMPRESA', 'Proveedor'));
		$this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$sql_estado_oc = "select COD_ESTADO_ORDEN_COMPRA, NOM_ESTADO_ORDEN_COMPRA from ESTADO_ORDEN_COMPRA order by ORDEN";
		$this->add_header(new header_drop_down('NOM_ESTADO_ORDEN_COMPRA', 'EOC.COD_ESTADO_ORDEN_COMPRA', 'Estado', $sql_estado_oc));
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));  
	
		// dw checkbox
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_SUMAR, $this->cod_usuario);
		if ($priv=='E')
			$DISPLAY_SUMAR = '';
      	else
			$DISPLAY_SUMAR = 'none';
		
			
		$sql = "select '$DISPLAY_SUMAR' DISPLAY_SUMAR
						,'N' CHECK_SUMAR
					   ,'N' HIZO_CLICK";
		$this->dw_check_box = new datawindow($sql);
		$this->dw_check_box->add_control($control = new edit_check_box('CHECK_SUMAR','S','N'));
		$control->set_onClick("sumar(); document.getElementById('loader').style.display='';");
		$this->dw_check_box->add_control(new edit_text_hidden('HIZO_CLICK'));
		$this->dw_check_box->retrieve();
	}
	function redraw(&$temp){
		parent::redraw(&$temp);
		$this->dw_check_box->habilitar($temp, true);
		
		$this->habilita_boton($temp, 'create', $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_CREAR, $this->cod_usuario)=='E');
	}
	
	function habilita_boton(&$temp, $boton, $habilita){
		if ($boton=='create'){
			if ($habilita)
				$temp->setVar("WO_CREATE", '<input name="b_create" id="b_create" src="../../../../commonlib/trunk/images/b_create.jpg" type="image" '.
											'onMouseDown="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_click.jpg\',1)" '.
											'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
											'onMouseOver="MM_swapImage(\'b_create\',\'\',\'../../../../commonlib/trunk/images/b_create_over.jpg\',1)" '.
											'onClick="return crear_gf();"'.
											'/>');
			else
				$temp->setVar("WO_".strtoupper($boton), '<img src="../../../../commonlib/trunk/images/b_create_d.jpg"/>');
		}else
			parent::habilita_boton($temp, $boton, $habilita);
	}
	
	
	function procesa_event(){
		if ($_POST['HIZO_CLICK_0'] == 'S'){
			$this->checkbox_sumar = isset($_POST['CHECK_SUMAR_0']);
			
			// obtiene los datos del filtro aplicado
			$valor_filtro = $this->headers['TOTAL_NETO']->valor_filtro;
			$valor_filtro2 = $this->headers['TOTAL_NETO']->valor_filtro2;
			
			if($this->checkbox_sumar){
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
		}else if(isset($_POST['b_create_x'])){
			$this->crear_gf_especial($_POST['wo_hidden']);
		}else{
			$this->checkbox_sumar = 0;
			parent::procesa_event();
		}
	}
	
	function crear_gf_especial($valor_devuelto){
		$alert = false;
		$arr_valor_devuelto = explode('|', $valor_devuelto);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$referencia = $arr_valor_devuelto[0];
		$obs		= $arr_valor_devuelto[1];
		
		$referencia = str_replace("'", "''", $referencia);
		$obs		= str_replace("'", "''", $obs);
		
		$obs		= ($obs =='') ? "null" : "'$obs'";
		
		$db->BEGIN_TRANSACTION();
		
		$sp = 'spu_orden_compra';
		$param	= "'INSERT'
					,null				
					,$this->cod_usuario		
					,$this->cod_usuario 									
					,1		
					,4
					,null			
					,1
					,'$referencia'																						
					,1337
					,1337
					,17353
					,0	
					,0		
					,0		
					,0		
					,0		
					,0		
					,0		
					,0		
					,0				
					,$obs
					,null
					,null
					,null
					,null
					,'GASTO_FIJO'
					,null
					,'S'
					,'S'
					,null
					,'S'";
					
		if(!$db->EXECUTE_SP($sp, $param)){
			$db->ROLLBACK_TRANSACTION();
		}else{
			$cod_orden_compra = $db->GET_IDENTITY();
			$sp = 'spu_item_orden_compra';
			
			$param = "'INSERT'
					 ,null
					 ,$cod_orden_compra
					 ,10
					 ,'1'
					 ,'GF-183'
					 ,'DISEÑO DE PLANO'
					 ,1
					 ,1000
					 ,null
					 ,null";
				 
			if(!$db->EXECUTE_SP($sp, $param)){
				$db->ROLLBACK_TRANSACTION();
			}else{
				$parametros_sp = "'RECALCULA',$cod_orden_compra";	
				if(!$db->EXECUTE_SP('spu_orden_compra', $parametros_sp)){
					$db->ROLLBACK_TRANSACTION();
				}else{
					$alert = true;
					$db->COMMIT_TRANSACTION();
				}	
			}	
			
			$this->retrieve();
			if($alert == true)
				$this->alert("Gestión Realizada con exíto.");
			else
				$this->alert("No se pudo guardar Gasto Fijo, Por favor contacte a IntegraSystem.");
				
		}
			
	}
}

$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_gasto_fijo.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_gasto_fijo extends wo_gasto_fijo_base {
		function wo_gasto_fijo() {
			parent::wo_gasto_fijo_base(); 
		}
	}
}
?>