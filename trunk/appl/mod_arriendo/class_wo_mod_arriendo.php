<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_mod_arriendo extends w_output {
	const K_ARRIENDO_APROBADO = 2;
	
	function wo_mod_arriendo() {   	
      $sql = "SELECT COD_MOD_ARRIENDO
					,convert(varchar(10), FECHA_MOD_ARRIENDO, 103) FECHA_MOD_ARRIENDO
					,FECHA_MOD_ARRIENDO DATE_MOD_ARRIENDO
					,A.COD_ARRIENDO
					,convert(varchar(10), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
					,RUT
					,DIG_VERIF
					,E.NOM_EMPRESA
					,MA.REFERENCIA
					,EM.NOM_ESTADO_MOD_ARRIENDO

				FROM MOD_ARRIENDO MA, ARRIENDO A, EMPRESA E, ESTADO_MOD_ARRIENDO EM
				WHERE A.COD_ARRIENDO = MA.COD_ARRIENDO
					AND E.COD_EMPRESA = A.COD_EMPRESA
					AND EM.COD_ESTADO_MOD_ARRIENDO = MA.COD_ESTADO_MOD_ARRIENDO
				ORDER BY COD_MOD_ARRIENDO DESC";
			
      parent::w_output('mod_arriendo', $sql, $_REQUEST['cod_item_menu']);
      
      $this->dw->add_control(new static_num('RUT'));
      
      // headers
      $this->add_header(new header_num('COD_MOD_ARRIENDO', 'COD_MOD_ARRIENDO', 'Código'));
      $this->add_header($control = new header_date('FECHA_MOD_ARRIENDO', 'FECHA_MOD_ARRIENDO', 'Fecha'));
      $control->field_bd_order = 'DATE_MOD_ARRIENDO';
      $this->add_header(new header_num('COD_ARRIENDO', 'A.COD_ARRIENDO', 'Código Arriendo'));
      $this->add_header(new header_rut('RUT', 'E', 'Rut'));
      $this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Cliente'));
      $this->add_header(new header_text('REFERENCIA', 'MA.REFERENCIA', 'Referencia'));
      $sql = "select COD_ESTADO_MOD_ARRIENDO ,NOM_ESTADO_MOD_ARRIENDO from ESTADO_MOD_ARRIENDO order by COD_ESTADO_MOD_ARRIENDO";
	  $this->add_header(new header_drop_down('NOM_ESTADO_MOD_ARRIENDO', 'MA.COD_ESTADO_MOD_ARRIENDO', 'Estado', $sql));
   
	}
	function crear_desde_arriendo($cod_arriendo) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_ESTADO_ARRIENDO
				from ARRIENDO
				where COD_ARRIENDO = $cod_arriendo";
		$result = $db->build_results($sql);
		if (count($result)==0) {
			$this->_redraw();
			$this->alert("Contrato de arriendo no existe, contrato Nº: $cod_arriendo");
			return;
		}
		$cod_estado_arriendo = $result[0]['COD_ESTADO_ARRIENDO'];
		if ($cod_estado_arriendo !=self:: K_ARRIENDO_APROBADO) {
			$this->_redraw();
 			$this->alert("El arriendo $cod_arriendo, no esta aprobado.");
			return;
		}
		
		// llama a un add 
		session::set('MOD_ARRIENDO.CREAR_DESDE_ARRIENDO', $cod_arriendo);
		$this->add();
	}
	function procesa_event() {		
		if(isset($_POST['b_add_x'])) {
			$this->crear_desde_arriendo($_POST['wo_hidden']);
		}
		else
			parent::procesa_event();
	}
}
?>