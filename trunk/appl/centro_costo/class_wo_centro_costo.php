<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_header_modelo.php");
/*
Clase : wo_centro_costo
en este modulo se ocupa 2 funciones que fueron creadas en proyecto compra
-	f_get_parametro_cod_cta_contable	=	devuelve el codigo de cuenta_contable
-	f_get_parametro_nom_cta_contable	=	devuelve el nombre de cuenta_contable
*/
class wo_centro_costo extends w_parametrica
{
   function wo_centro_costo()
   {   	
      $sql = "SELECT COD_CENTRO_COSTO
					,NOM_CENTRO_COSTO
					,dbo.f_get_parametro_nom_cta_contable (COD_CUENTA_CONTABLE_VENTAS) CONTABLE_VENTAS
					,dbo.f_get_parametro_nom_cta_contable (COD_CUENTA_CONTABLE_IVA) CONTABLE_IVA
					,dbo.f_get_parametro_nom_cta_contable (COD_CUENTA_CONTABLE_POR_COBRAR) CONTABLE_POR_COBRAR
			  FROM CENTRO_COSTO 
			  ORDER BY COD_CENTRO_COSTO DESC";
			
      parent::w_parametrica('centro_costo', $sql, $_REQUEST['cod_item_menu'], '1025');

    // headers
	$this->add_header(new header_modelo('COD_CENTRO_COSTO', 'COD_CENTRO_COSTO', 'Código'));
	$this->add_header(new header_text('NOM_CENTRO_COSTO', 'NOM_CENTRO_COSTO', 'Centro Costo'));

	$sql_contable_ventas = "SELECT	COD_CUENTA_CONTABLE,
    								NOM_CUENTA_CONTABLE
							FROM	CUENTA_CONTABLE
							ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_VENTAS', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_VENTAS))', 'Contable Ventas', $sql_contable_ventas));
	$control->field_bd_order = "CONTABLE_VENTAS";

	$sql_contable_iva = "SELECT	COD_CUENTA_CONTABLE,
								NOM_CUENTA_CONTABLE
						 FROM	CUENTA_CONTABLE
						 ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_IVA', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_IVA))', 'Contable Iva', $sql_contable_iva));
	$control->field_bd_order = "CONTABLE_IVA";

	$sql_contable_cobrar = 	"SELECT	COD_CUENTA_CONTABLE,
									NOM_CUENTA_CONTABLE
							FROM	CUENTA_CONTABLE
							ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_POR_COBRAR', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_POR_COBRAR))', 'Contable por Cobrar', $sql_contable_cobrar));
	$control->field_bd_order = "CONTABLE_POR_COBRAR";
   }
}
?>