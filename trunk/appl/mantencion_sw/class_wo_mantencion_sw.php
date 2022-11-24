<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_mantencion_sw extends w_output {
   function wo_mantencion_sw() {   	
      	$sql = "SELECT	MS.COD_MANTENCION_SW
					,CONVERT(VARCHAR(20), MS.FECHA_MANTENCION_SW, 103) FECHA_MANTENCION
					,MS.FECHA_MANTENCION_SW DATE_MANTENCION_SW
					,US.NOM_USUARIO
					,MS.REFERENCIA
					,MS.ES_GARANTIA
					,ES.NOM_ESTADO_SOLUCION_SW
				FROM MANTENCION_SW MS, USUARIO US, ESTADO_SOLUCION_SW ES
				WHERE MS.COD_USUARIO_SOLICITA = US.COD_USUARIO
				  and ES.COD_ESTADO_SOLUCION_SW = dbo.f_mant_estado_solucion(MS.COD_MANTENCION_SW)
				ORDER BY COD_MANTENCION_SW desc"; 
			
      parent::w_output('mantencion_sw', $sql, $_REQUEST['cod_item_menu']);
      
      // headers
      $this->add_header(new header_num('COD_MANTENCION_SW', 'COD_MANTENCION_SW', 'Código'));
      $this->add_header($control = new header_text('FECHA_MANTENCION', 'FECHA_MANTENCION', 'Fecha'));
      $control->field_bd_order = 'DATE_MANTENCION_SW';
      $this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Solicitada Por:'));
      $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
      $this->add_header(new header_text('ES_GARANTIA', 'ES_GARANTIA', 'Garantía'));
      $sql = "select COD_ESTADO_SOLUCION_SW
      				,NOM_ESTADO_SOLUCION_SW
      		 from ESTADO_SOLUCION_SW
      		 order by COD_ESTADO_SOLUCION_SW";
      $this->add_header(new header_drop_down('NOM_ESTADO_SOLUCION_SW', 'dbo.f_mant_estado_solucion(MS.COD_MANTENCION_SW)', 'Estado', $sql));
   }
}
?>