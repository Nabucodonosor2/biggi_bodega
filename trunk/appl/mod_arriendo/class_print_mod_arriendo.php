<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
class print_mod_arriendo extends reporte {	
	var $cod_mod_arriendo;
	function print_mod_arriendo($cod_mod_arriendo, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		$this->cod_mod_arriendo = $cod_mod_arriendo;
		
		
		$sql ="SELECT MA.COD_MOD_ARRIENDO
							,convert(varchar(20), GETDATE(), 103) FECHA_ACTUAL
							,MA.FECHA_MOD_ARRIENDO
							,MA.COD_USUARIO
							,MA.COD_ARRIENDO
							,MA.REFERENCIA
							,MA.TOTAL_NETO
							,ITMA.ITEM
							,ITMA.NOM_PRODUCTO
							,ITMA.COD_PRODUCTO
							,ITMA.CANTIDAD
							,ITMA.PRECIO
							,ITMA.PRECIO * ITMA.CANTIDAD TOTAL
							,MA.TOTAL_NETO
							,A.NRO_MESES
							,A.COD_EMPRESA
							,E.NOM_EMPRESA
							,E.RUT
							,E.DIG_VERIF
							,MA.COD_USUARIO
							,U.NOM_USUARIO
					FROM
							MOD_ARRIENDO MA
							,ITEM_MOD_ARRIENDO ITMA
							,ARRIENDO A	
							,EMPRESA E	
							,USUARIO U							
					WHERE
							MA.COD_MOD_ARRIENDO = $cod_mod_arriendo
							AND ITMA.COD_MOD_ARRIENDO = MA.COD_MOD_ARRIENDO
							AND A.COD_ARRIENDO = MA.COD_ARRIENDO
							AND E.COD_EMPRESA = A.COD_EMPRESA
							AND U.COD_USUARIO = MA.COD_USUARIO";
	
		
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>