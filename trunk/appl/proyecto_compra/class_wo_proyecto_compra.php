<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_proyecto_compra extends w_parametrica
{
   function wo_proyecto_compra()
   {   	
      $sql = "SELECT	CC.COD_CUENTA_COMPRA
						,CC.NOM_CUENTA_COMPRA
						,dbo.f_get_parametro_nom_cta_contable (CC.COD_CUENTA_CONTABLE_COMPRA) CONTABLE_COMPRA
						,dbo.f_get_parametro_nom_cta_contable (CC.COD_CUENTA_CONTABLE_IVA) CONTABLE_IVA
						,dbo.f_get_parametro_nom_cta_contable (CC.COD_CUENTA_CONTABLE_POR_PAGAR) CONTABLE_POR_PAGAR
						,(SELECT CTC.NOM_CENTRO_COSTO FROM CENTRO_COSTO CTC WHERE CTC.COD_CENTRO_COSTO = CC.COD_CENTRO_COSTO) NOM_CENTRO_COSTO 
				FROM	CUENTA_COMPRA CC
				ORDER BY CC.COD_CUENTA_COMPRA DESC";
			
      parent::w_parametrica('proyecto_compra', $sql, $_REQUEST['cod_item_menu'], '1025');

      // headers
	$this->add_header(new header_num('COD_CUENTA_COMPRA', 'COD_CUENTA_COMPRA', 'Código'));
	$this->add_header(new header_text('NOM_CUENTA_COMPRA', 'NOM_CUENTA_COMPRA', 'Cuenta Compra'));

	$sql_contable_compra = "SELECT	COD_CUENTA_CONTABLE,
    								NOM_CUENTA_CONTABLE
							FROM	CUENTA_CONTABLE
							ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_COMPRA', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_COMPRA))', 'Contable Compra', $sql_contable_compra));
	$control->field_bd_order = "CONTABLE_COMPRA";

	$sql_contable_iva = "SELECT	COD_CUENTA_CONTABLE,
								NOM_CUENTA_CONTABLE
						 FROM	CUENTA_CONTABLE
						 ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_IVA', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_IVA))', 'Contable Iva', $sql_contable_iva));
	$control->field_bd_order = "CONTABLE_IVA";

	$sql_contable_pagar = 	"SELECT	COD_CUENTA_CONTABLE,
									NOM_CUENTA_CONTABLE
							FROM	CUENTA_CONTABLE
							ORDER BY	COD_CUENTA_CONTABLE";

	$this->add_header($control = new header_drop_down('CONTABLE_POR_PAGAR', '(SELECT dbo.f_get_parametro_cod_cta_contable (COD_CUENTA_CONTABLE_POR_PAGAR))', 'Contable por Pagar', $sql_contable_pagar));
	$control->field_bd_order = "CONTABLE_POR_PAGAR";

	$sql_centro_costo	= 	"SELECT	COD_CENTRO_COSTO,
									NOM_CENTRO_COSTO
							FROM	CENTRO_COSTO
							ORDER BY	COD_CENTRO_COSTO";

	$this->add_header($control = new header_drop_down('NOM_CENTRO_COSTO', 'CTC.COD_CENTRO_COSTO', 'Centro Costo', $sql_centro_costo));
	$control->field_bd_order = "NOM_CENTRO_COSTO";
   }
}
?>