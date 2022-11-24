<?php
require_once(dirname(__FILE__) . "/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_ajuste_bodega extends datawindow {
	function dw_item_ajuste_bodega() {		
		$sql = "SELECT	IAB.COD_ITEM_AJUSTE_BODEGA
						,IAB.COD_AJUSTE_BODEGA
						,P.COD_PRODUCTO
						,P.COD_PRODUCTO COD_PRODUCTO_OLD
						,P.NOM_PRODUCTO
						,P.NOM_PRODUCTO NOM_PRODUCTO_STATIC 
						,IAB.CANTIDAD
				FROM	ITEM_AJUSTE_BODEGA IAB, PRODUCTO P
				WHERE	IAB.COD_AJUSTE_BODEGA = {KEY1}
				AND		IAB.COD_PRODUCTO = P.COD_PRODUCTO
				ORDER BY IAB.COD_ITEM_AJUSTE_BODEGA";

		parent::datawindow($sql, 'ITEM_AJUSTE_BODEGA', true, true);	
		$this->add_control(new edit_cantidad('CANTIDAD',15, 15));
		$this->controls['CANTIDAD']->set_onChange("valida_cantidad(this, 'CANTIDAD');");
		
		
		$this->add_control(new edit_text_upper('COD_ITEM_AJUSTE_BODEGA',10, 10, 'hidden'));

		$this->add_controls_producto_help();
		$this->add_control(new edit_text('NOM_PRODUCTO', 10,10, 'hidden'));
		$this->add_control(new static_text('NOM_PRODUCTO_STATIC'));
		
		
		// Agrega script adicional a COD_PRODUCTO 
		$this->controls['COD_PRODUCTO']->set_onChange("change_item_ajuste_bodega(this, 'COD_PRODUCTO');");
		$this->set_first_focus('COD_PRODUCTO');
		
		//campos obligatorios cod_producto, cantidad.
		$this->set_mandatory('COD_PRODUCTO', 'Código del producto');
		$this->set_mandatory('CANTIDAD', 'Cantidad');
	}
	
	function update($db, $COD_AJUSTE_BODEGA)	{
		$sp = 'spu_item_ajuste_bodega';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$COD_ITEM_AJUSTE_BODEGA		= $this->get_item($i, 'COD_ITEM_AJUSTE_BODEGA');
			$COD_AJUSTE_BODEGA			= $this->get_item($i, 'COD_AJUSTE_BODEGA');
			$COD_PRODUCTO	 			= $this->get_item($i, 'COD_PRODUCTO');
			$CANTIDAD 					= $this->get_item($i, 'CANTIDAD');
			
			if ($CANTIDAD=='')
				$CANTIDAD = 0;
			
			$COD_ITEM_AJUSTE_BODEGA  = ($COD_ITEM_AJUSTE_BODEGA =='') ? "null" : $COD_ITEM_AJUSTE_BODEGA;
			$COD_AJUSTE_BODEGA  = ($COD_AJUSTE_BODEGA =='') ? "null" : $COD_AJUSTE_BODEGA;
			$COD_PRODUCTO		= ($COD_PRODUCTO =='') ? "null" : $COD_PRODUCTO;
			$CANTIDAD			= ($CANTIDAD =='') ? "null" : $CANTIDAD;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';

			$param = "'$operacion', $COD_ITEM_AJUSTE_BODEGA, $COD_AJUSTE_BODEGA, '$COD_PRODUCTO', $CANTIDAD";	
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			else {
				if ($statuts == K_ROW_NEW_MODIFIED) {
					$COD_ITEM_AJUSTE_BODEGA = $db->GET_IDENTITY();
					$this->set_item($i, 'COD_ITEM_AJUSTE_BODEGA', $COD_ITEM_AJUSTE_BODEGA);		
				}
			}
		}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_ITEM_AJUSTE_BODEGA = $this->get_item($i, 'COD_ITEM_AJUSTE_BODEGA', 'delete');
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_AJUSTE_BODEGA")){
				return false;				
			}			
		}
		return true;
	}
}

class dw_ajuste_bodega extends dw_help_empresa {
	function dw_ajuste_bodega() {
		$sql = "SELECT	AB.COD_AJUSTE_BODEGA,
						convert(varchar(20), AB.FECHA_AJUSTE_BODEGA, 103) FECHA_AJUSTE_BODEGA,
						AB.COD_USUARIO,
						U.NOM_USUARIO,
						B.COD_BODEGA,
						B.NOM_BODEGA,
						AB.OBS,
				FROM AJUSTE_BODEGA AB, USUARIO U, BODEGA B
				WHERE	AB.COD_AJUSTE_BODEGA = {KEY1}
				AND		AB.COD_USUARIO = U.COD_USUARIO
				AND		AB.COD_BODEGA = B.COD_BODEGA";
						
		parent::dw_help_empresa($sql);
		
		// asigna los formatos
		$sql	= "select 	 COD_BODEGA
							,NOM_BODEGA
					from 	 BODEGA
					order by COD_BODEGA";
		$this->add_control(new drop_down_dw('COD_BODEGA',$sql,150));
		
		//USUARIO_ANULA
		$sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
								
		$this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));	
		$this->set_entrable('COD_USUARIO_ANULA', false);
		
		$this->add_control(new edit_text_upper('OBS',120, 100));
		$this->add_control(new edit_text_upper('MOTIVO_ANULA',120, 100));
	}
}

class wi_ajuste_bodega extends w_input {
	const K_ESTADO_EMITIDA 			= 1;	
	const K_ESTADO_CONFIRMADO		= 2;
	const K_ESTADO_ANULADA			= 3;	

	function wi_ajuste_bodega($cod_item_menu) {
		parent::w_input('ajuste_bodega', $cod_item_menu);
		// tab ajuste de bodega
		// DATAWINDOWS AJUSTE_BODEGA
		$this->dws['dw_ajuste_bodega'] = new dw_ajuste_bodega();

		//tab items
		// DATAWINDOWS AJUSTE_BODEGA
		$this->dws['dw_item_ajuste_bodega'] = new dw_item_ajuste_bodega();
		
		// asigna los mandatorys/
		
		$this->dws['dw_ajuste_bodega']->set_mandatory('COD_BODEGA', 'Bodega');
		$this->dws['dw_ajuste_bodega']->set_mandatory('OBS', 'Observación');
		$this->dws['dw_item_ajuste_bodega']->set_mandatory('COD_PRODUCTO', 'Producto');
		$this->dws['dw_item_ajuste_bodega']->set_mandatory('CANTIDAD', 'Cantidad');
	}

	function new_record() {
		$this->b_delete_visible  = false; //cuando es un registro nuevo no muestra el boton eliminar
		$this->b_print_visible  = false; //cuando es un registro nuevo no muestra el boton PRINT
		$this->dws['dw_ajuste_bodega']->insert_row();
		
		
		$this->dws['dw_ajuste_bodega']->set_item(0, 'FECHA_AJUSTE_BODEGA', $this->current_date());
		$this->dws['dw_ajuste_bodega']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
	}
	
	function load_record() {
		$COD_AJUSTE_BODEGA = $this->get_item_wo($this->current_record, 'COD_AJUSTE_BODEGA');
		$this->dws['dw_ajuste_bodega']->retrieve($COD_AJUSTE_BODEGA);	
		$this->dws['dw_item_ajuste_bodega']->retrieve($COD_AJUSTE_BODEGA);
		
	}

	function get_key() {
		return $this->dws['dw_ajuste_bodega']->get_item(0, 'COD_AJUSTE_BODEGA');
	}

	function save_record($db) {
		$COD_AJUSTE_BODEGA = $this->get_key();
		$COD_USUARIO	= $this->dws['dw_ajuste_bodega']->get_item(0, 'COD_USUARIO');
		$COD_BODEGA = $this->dws['dw_ajuste_bodega']->get_item(0, 'COD_BODEGA');
		$OBS = $this->dws['dw_ajuste_bodega']->get_item(0, 'OBS');	

		$COD_AJUSTE_BODEGA = ($COD_AJUSTE_BODEGA=='') ? "null" : $COD_AJUSTE_BODEGA;
		$OBS = ($OBS=='') ? "null" : $OBS;

		$sp = 'spu_ajuste_bodega';

	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    $param	=	"'$operacion'
	    			,$COD_AJUSTE_BODEGA
	    			,$COD_USUARIO
	    			,$COD_BODEGA
	    			,'$OBS'";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()){
				$COD_AJUSTE_BODEGA = $db->GET_IDENTITY();
				$this->dws['dw_ajuste_bodega']->set_item(0, 'COD_AJUSTE_BODEGA', $COD_AJUSTE_BODEGA);
			}
			for ($i=0; $i<$this->dws['dw_item_ajuste_bodega']->row_count(); $i++){ 
				$this->dws['dw_item_ajuste_bodega']->set_item($i, 'COD_AJUSTE_BODEGA', $COD_AJUSTE_BODEGA);
			}
			if (!$this->dws['dw_item_ajuste_bodega']->update($db, $COD_AJUSTE_BODEGA)) return false;
			return true;
		}
		return false;
	}
	
	function print_record() {
		$cod_ajuste_bodega = $this->get_key();
		$sql = "SELECT	IAB.COD_ITEM_AJUSTE_BODEGA,
						P.COD_PRODUCTO,
						P.NOM_PRODUCTO,
						IAB.CANTIDAD
				FROM	ITEM_AJUSTE_BODEGA IAB, PRODUCTO P
				WHERE	IAB.COD_AJUSTE_BODEGA = $cod_ajuste_bodega
				AND		IAB.COD_PRODUCTO = P.COD_PRODUCTO
				ORDER BY IAB.COD_ITEM_AJUSTE_BODEGA";

		// reporte
		$labels = array();
		$labels['strCOD_ITEM_AJUSTE_BODEGA'] = $cod_ajuste_bodega;				
		$file_name = $this->find_file('ajuste_bodega', 'ajuste_bodega.xml');				
		$rpt = new print_ajuste_bodega($sql, $file_name, $labels, "Ajuste Bodega".$cod_ajuste_bodega, 0);
		$this->_load_record();
		return true;
	}
}

class print_ajuste_bodega extends reporte {	
	function print_ajuste_bodega($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false){
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
	function modifica_pdf(&$pdf) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($this->sql);
		$count = count($result);
		$cod_producto	=	$result[0]['COD_PRODUCTO'];
		
		$pdf->SetFont('Arial','',25);
		$pdf->Text(290, 120,$cod_producto,'L');
	}
}
?>