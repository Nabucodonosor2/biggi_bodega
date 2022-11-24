<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_participacion extends w_output {
   	function wo_participacion() {
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::w_base('orden_pago', $_REQUEST['cod_item_menu']);
   		$sql = "SELECT COD_PARTICIPACION
				      ,convert(varchar(20), FECHA_PARTICIPACION, 103) FECHA_PARTICIPACION
				      ,FECHA_PARTICIPACION DATE_PARTICIPACION
				      ,COD_USUARIO_VENDEDOR
					  ,U.NOM_USUARIO
				      ,P.COD_ESTADO_PARTICIPACION
					  ,E.NOM_ESTADO_PARTICIPACION
				      ,TIPO_DOCUMENTO
				      ,TOTAL_NETO
				FROM PARTICIPACION P, ESTADO_PARTICIPACION E, USUARIO U
				where P.COD_ESTADO_PARTICIPACION = E.COD_ESTADO_PARTICIPACION
					and U.COD_USUARIO = P.COD_USUARIO_VENDEDOR
					and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'PARTICIPACION',COD_USUARIO_VENDEDOR, null) = 1 
				order by COD_PARTICIPACION desc";		
			
   		parent::w_output('participacion', $sql, $_REQUEST['cod_item_menu']);

   		$this->dw->add_control(new edit_nro_doc('COD_PARTICIPACION','PARTICIPACION'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
   		
		// headers 
		$this->add_header(new header_num('COD_PARTICIPACION', 'COD_PARTICIPACION', 'Código'));
		$this->add_header($control = new header_date('FECHA_PARTICIPACION', 'FECHA_PARTICIPACION', 'Fecha '));
		$control->field_bd_order = 'DATE_PARTICIPACION';
		
		$sql_usuario_vendedor = "select COD_USUARIO, NOM_USUARIO from USUARIO order by	COD_USUARIO";
      	$this->add_header(new header_drop_down('NOM_USUARIO', 'COD_USUARIO_VENDEDOR', 'Vendedor', $sql_usuario_vendedor));
      	
      	$sql_estado_participacion = "select COD_ESTADO_PARTICIPACION, NOM_ESTADO_PARTICIPACION from ESTADO_PARTICIPACION order by COD_ESTADO_PARTICIPACION";
      	$this->add_header(new header_drop_down('NOM_ESTADO_PARTICIPACION', 'P.COD_ESTADO_PARTICIPACION', 'Estado', $sql_estado_participacion));
		
      	$this->add_header(new header_text('TIPO_DOCUMENTO', 'TIPO_DOCUMENTO', 'Tipo Docto.'));
      	
		$this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
	}
	function detalle_record_desde($modificar, $cant_participacion_a_hacer) 
	{
		// No se llama al ancestro porque se reimplementa toda la rutina
		session::set("cant_participacion_a_hacer", $cant_participacion_a_hacer);

		// retrieve
		$this->set_count_output();
		$this->last_page = Ceil($this->row_count_output / $this->row_per_page);
		$this->set_current_page(0);
		$this->save_SESSION();

		$pag_a_mostrar=$cant_participacion_a_hacer -1;

		$this->detalle_record($pag_a_mostrar);	// Se va al primer registro
	}
	
	function crear_desde($cod_usuario_tipo_op) {
		session::set('CREA_PARTICIPACION', $cod_usuario_tipo_op);
		$this->add();	
	}
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_desde($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>
