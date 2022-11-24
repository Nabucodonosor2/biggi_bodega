<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_cotizacion_arriendo extends w_output
{
   function wo_cotizacion_arriendo(){
	// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::w_base('cotizacion_arriendo', $_REQUEST['cod_item_menu']);
		 
		$sql = "select		COD_COTIZACION
						,convert(varchar(20), FECHA_COTIZACION, 103) FECHA_COTIZACION
						,FECHA_COTIZACION DATE_COTIZACION
						,E.RUT
						,E.DIG_VERIF
						,NOM_EMPRESA
						,REFERENCIA
						,INI_USUARIO
						,NOM_ESTADO_COTIZACION
						,TOTAL_NETO
			from 		COTIZACION C
						,EMPRESA E
						,USUARIO U
						,ESTADO_COTIZACION EC
			where		C.COD_EMPRESA = E.COD_EMPRESA and 
						C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO and 
						C.COD_ESTADO_COTIZACION = EC.COD_ESTADO_COTIZACION
						and dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'COTIZACION',COD_USUARIO_VENDEDOR1, COD_USUARIO_VENDEDOR2) = 1
			order by	COD_COTIZACION desc";
			
     	parent::w_output('cotizacion_arriendo', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new edit_nro_doc('COD_COTIZACION','COTIZACION'));
		$this->dw->add_control(new edit_precio('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
			
	      // headers
      	$this->add_header($control = new header_date('FECHA_COTIZACION', 'FECHA_COTIZACION', 'Fecha'));
      	$control->field_bd_order = 'DATE_COTIZACION';
	    $this->add_header(new header_num('COD_COTIZACION', 'COD_COTIZACION', 'Nº Cot.'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	      
	    $this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
	    $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from COTIZACION C, USUARIO U where C.COD_USUARIO_VENDEDOR1 = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'C.COD_USUARIO_VENDEDOR1', 'Vend', $sql));
		$sql_estado_cot = "select COD_ESTADO_COTIZACION ,NOM_ESTADO_COTIZACION from ESTADO_COTIZACION order by	ORDEN";
	    $this->add_header(new header_drop_down('NOM_ESTADO_COTIZACION', 'EC.COD_ESTADO_COTIZACION', 'Estado', $sql_estado_cot));
	    $this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
   }
   
	function crear_cot_from_cot($cod_cotizacion) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT * FROM COTIZACION WHERE COD_COTIZACION = $cod_cotizacion";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La cotización Nº '.$cod_cotizacion.' no existe.');								
			return;
		}
			
		session::set('COT_CREADA_DESDE', $cod_cotizacion);
		$this->add();
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_cot_from_cot($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
   
}
?>