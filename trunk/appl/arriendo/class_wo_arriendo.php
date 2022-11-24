  <?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_arriendo extends w_output {
   function wo_arriendo() {
   		$sql = "select	COD_ARRIENDO
						,convert(varchar(20), FECHA_ARRIENDO, 103) FECHA_ARRIENDO
						,FECHA_ARRIENDO DATE_ARRIENDO
						,E.RUT
						,E.DIG_VERIF
						,NOM_EMPRESA
						,REFERENCIA
						,EA.COD_ESTADO_ARRIENDO
						,NOM_ESTADO_ARRIENDO
						,TOTAL_NETO
			from 		ARRIENDO A,
						EMPRESA E,
						USUARIO U,
						ESTADO_ARRIENDO EA
			where		A.COD_EMPRESA = E.COD_EMPRESA and 
						A.COD_USUARIO = U.COD_USUARIO and 
						A.COD_ESTADO_ARRIENDO = EA.COD_ESTADO_ARRIENDO
			order by	COD_ARRIENDO desc";

   		parent::w_output('arriendo', $sql, $_REQUEST['cod_item_menu']);
				
		$this->dw->add_control(new edit_nro_doc('COD_ARRIENDO','ARRIENDO'));
		$this->dw->add_control(new static_num('TOTAL_NETO'));
      	$this->dw->add_control(new static_num('RUT'));
			
	    // headers
      	$this->add_header($control = new header_date('FECHA_ARRIENDO', 'FECHA_ARRIENDO', 'Fecha'));
      	$control->field_bd_order = 'DATE_ARRIENDO';
	    $this->add_header(new header_num('COD_ARRIENDO', 'COD_ARRIENDO', 'Código'));
	    $this->add_header(new header_rut('RUT', 'E', 'Rut'));
	      
	    $this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
	    $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
		$sql = "select distinct U.COD_USUARIO, U.NOM_USUARIO from ARRIENDO A, USUARIO U where A.COD_USUARIO = U.COD_USUARIO order by NOM_USUARIO";
		$this->add_header(new header_drop_down('INI_USUARIO', 'U.COD_USUARIO', 'Vend', $sql));
		
		$sql_estado_arr = "select COD_ESTADO_ARRIENDO ,NOM_ESTADO_ARRIENDO from ESTADO_ARRIENDO order by COD_ESTADO_ARRIENDO";
	    $this->add_header(new header_drop_down('NOM_ESTADO_ARRIENDO', 'EA.COD_ESTADO_ARRIENDO', 'Estado', $sql_estado_arr));
	    $this->add_header(new header_num('TOTAL_NETO', 'TOTAL_NETO', 'Total Neto'));
  	}
}
?>