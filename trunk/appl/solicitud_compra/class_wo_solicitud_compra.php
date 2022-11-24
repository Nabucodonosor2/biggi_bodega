<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");

class wo_solicitud_compra extends w_output_biggi
{
   function wo_solicitud_compra()
   {   	
      $sql = "SELECT COD_SOLICITUD_COMPRA
				,convert(varchar(20), FECHA_SOLICITUD_COMPRA, 103) FECHA_SOLICITUD_COMPRA
				,SC.COD_USUARIO
				,NOM_USUARIO
				,SC.COD_ESTADO_SOLICITUD_COMPRA
				,NOM_ESTADO_SOLICITUD_COMPRA
				,REFERENCIA
				,COD_PRODUCTO
				,CANTIDAD
			FROM SOLICITUD_COMPRA SC, USUARIO U, ESTADO_SOLICITUD_COMPRA ESC
			WHERE U.COD_USUARIO = SC.COD_USUARIO
				AND ESC.COD_ESTADO_SOLICITUD_COMPRA = SC.COD_ESTADO_SOLICITUD_COMPRA
			ORDER BY COD_SOLICITUD_COMPRA DESC";

      parent::w_output_biggi('solicitud_compra', $sql, $_REQUEST['cod_item_menu']);

      // headers
      $this->add_header(new header_num('COD_SOLICITUD_COMPRA', 'COD_SOLICITUD_COMPRA', 'Código'));
      $this->add_header(new header_date('FECHA_SOLICITUD_COMPRA', 'FECHA_SOLICITUD_COMPRA', 'Fecha'));
      
      $this->add_header(new header_modelo('COD_PRODUCTO', 'COD_PRODUCTO', 'Modelo'));
      $this->add_header(new header_num('CANTIDAD', 'CANTIDAD', 'Cantidad'));

      $sql_estado = "select COD_ESTADO_SOLICITUD_COMPRA ,NOM_ESTADO_SOLICITUD_COMPRA from ESTADO_SOLICITUD_COMPRA order by	COD_ESTADO_SOLICITUD_COMPRA";
      $this->add_header(new header_drop_down('NOM_ESTADO_SOLICITUD_COMPRA', 'SC.COD_ESTADO_SOLICITUD_COMPRA', 'Estado', $sql_estado));
      
      $this->add_header(new header_text('REFERENCIA', 'REFERENCIA', 'Referencia'));
   }
}
?>