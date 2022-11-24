<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_vendedor.php");

class wo_factura_rechazada extends w_output_biggi{
   	function wo_factura_rechazada(){
		$sql = "SELECT COD_FACTURA_RECHAZADA
					  ,NRO_FACTURA
					  ,CONVERT(VARCHAR, FECHA_RECHAZO, 103) FECHA_RECHAZO
					  ,FECHA_RECHAZO DATE_FECHA_RECHAZO
					  ,RESUELTA
					  ,COD_USUARIO_RESUELTA
					  ,NOM_USUARIO
				FROM FACTURA_RECHAZADA FR LEFT OUTER JOIN USUARIO U ON U.COD_USUARIO = FR.COD_USUARIO_RESUELTA
					,FACTURA F
				WHERE FR.COD_FACTURA = F.COD_FACTURA
				ORDER BY COD_FACTURA_RECHAZADA DESC";		
	
   		parent::w_output_biggi('factura_rechazada', $sql, $_REQUEST['cod_item_menu']);
		
   		$this->add_header(new header_num('COD_FACTURA_RECHAZADA', 'COD_FACTURA_RECHAZADA', 'Código'));
   		$this->add_header(new header_num('NRO_FACTURA', 'NRO_FACTURA', 'Nro. Factura'));
   		$this->add_header($control = new header_date('FECHA_RECHAZO', 'FECHA_RECHAZO', 'Fecha Rechazo'));
		$control->field_bd_order = 'DATE_FECHA_RECHAZO';
		$sql_s_n = "select 'S' RESUELTA,
							'Si' NOM_RESUELTA
					UNION 
					select 'N' RESUELTA,
						   'No' NOM_RESUELTA";
		$this->add_header(new header_drop_down_string('RESUELTA', 'RESUELTA', 'Resuelta',$sql_s_n));
   		$sql = "SELECT COD_USUARIO COD_USUARIO_RESUELTA
   					  ,NOM_USUARIO
   				FROM USUARIO";
		$this->add_header(new header_drop_down('NOM_USUARIO', 'COD_USUARIO_RESUELTA', 'Responsable', $sql));
	}
}
?>