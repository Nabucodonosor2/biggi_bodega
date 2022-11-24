<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_orden_pago extends w_output
{
   function wo_orden_pago()
   {   	
		// Llama a w_base para que $this->cod_usuario contenga al usuario actual 
   		parent::w_base('orden_pago', $_REQUEST['cod_item_menu']);
		$sql = "SELECT distinct COD_ORDEN_PAGO
						,convert(varchar(20), FECHA_ORDEN_PAGO, 103) FECHA_ORDEN_PAGO
						,FECHA_ORDEN_PAGO DATE_ORDEN_PAGO
						,OP.COD_NOTA_VENTA
						,NOM_TIPO_ORDEN_PAGO
						,NOM_EMPRESA
						,OP.TOTAL_NETO
				FROM	ORDEN_PAGO OP, EMPRESA E, TIPO_ORDEN_PAGO T_OP, NOTA_VENTA NV, USUARIO U
				WHERE	E.COD_EMPRESA = OP.COD_EMPRESA
						AND T_OP.COD_TIPO_ORDEN_PAGO = OP.COD_TIPO_ORDEN_PAGO
						AND NV.COD_NOTA_VENTA = OP.COD_NOTA_VENTA
						AND U.COD_EMPRESA = E.COD_EMPRESA 
						AND dbo.f_get_tiene_acceso (".$this->cod_usuario.", 'ORDEN_PAGO', U.COD_USUARIO, null) = 1
				ORDER BY COD_ORDEN_PAGO DESC"; 
			
		
		
      parent::w_output('orden_pago', $sql, $_REQUEST['cod_item_menu']);
      
      $this->dw->add_control(new edit_precio('TOTAL_NETO'));
      
      // headers
      $this->add_header(new header_num('COD_ORDEN_PAGO', 'COD_ORDEN_PAGO', 'Código'));
      $this->add_header($control = new header_date('FECHA_ORDEN_PAGO', 'FECHA_ORDEN_PAGO', 'Fecha'));
      $control->field_bd_order = 'DATE_ORDEN_PAGO';
      $this->add_header(new header_num('COD_NOTA_VENTA', 'OP.COD_NOTA_VENTA', 'Nº NV.'));
      $sql = "select distinct T_OP.COD_TIPO_ORDEN_PAGO, T_OP.NOM_TIPO_ORDEN_PAGO from ORDEN_PAGO OP, TIPO_ORDEN_PAGO T_OP where OP.COD_TIPO_ORDEN_PAGO = T_OP.COD_TIPO_ORDEN_PAGO";  
	  $this->add_header(new header_drop_down('NOM_TIPO_ORDEN_PAGO', 'OP.COD_TIPO_ORDEN_PAGO', 'Tipo', $sql));
	  $this->add_header(new header_text('NOM_EMPRESA', 'NOM_EMPRESA', 'Razón Social'));
      $this->add_header(new header_num('TOTAL_NETO', 'OP.TOTAL_NETO', 'Total Comisión'));
   }
}
?>