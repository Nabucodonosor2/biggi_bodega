<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
class print_arriendo extends reporte {	
	var $cod_arriendo;
	function print_arriendo($cod_arriendo, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
		$this->cod_arriendo = $cod_arriendo;
		
		$sql = "SELECT    A.COD_ARRIENDO
                         ,convert(varchar(20), GETDATE(), 103) FECHA_ACTUAL
                         ,E.NOM_EMPRESA
                         ,E.COD_EMPRESA
                         ,E.RUT
                         ,E.DIG_VERIF
                         ,SU.DIRECCION
                         ,CO.NOM_COMUNA
                         ,CI.NOM_CIUDAD
                         ,PA.NOM_PAIS
                         ,SU.TELEFONO
                         ,SU.FAX
                         ,P.NOM_PERSONA
                         ,A.REFERENCIA
                         ,A.NRO_MESES
                         ,A.MONTO_ADICIONAL_RECUPERACION
                         ,(A.MONTO_ADICIONAL_RECUPERACION + A.SUBTOTAL) TOTAL_ADICIONAL
                         ,IT.ITEM
                         ,IT.NOM_PRODUCTO
                         ,IT.COD_PRODUCTO
                         ,IT.CANTIDAD
                         ,IT.PRECIO
                         ,IT.PRECIO * IT.CANTIDAD TOTAL
                 FROM     ARRIENDO A
                         ,EMPRESA E
                         ,USUARIO U
                         ,ESTADO_ARRIENDO EA
                         ,PERSONA P
                         ,ITEM_ARRIENDO IT
                         ,SUCURSAL SU LEFT OUTER JOIN COMUNA CO ON SU.COD_COMUNA = CO.COD_COMUNA
                                     LEFT OUTER JOIN CIUDAD CI ON SU.COD_CIUDAD = CI.COD_CIUDAD
                                     LEFT OUTER JOIN PAIS PA ON SU.COD_PAIS = PA.COD_PAIS
                 WHERE A.COD_ARRIENDO = $cod_arriendo
                     AND E.COD_EMPRESA = A.COD_EMPRESA
                     AND U.COD_USUARIO = A.COD_USUARIO
                     AND EA.COD_ESTADO_ARRIENDO = A.COD_ESTADO_ARRIENDO
                     AND P.COD_PERSONA = A.COD_PERSONA
                     AND IT.COD_ARRIENDO = A.COD_ARRIENDO
                     AND IT.COD_ITEM_MOD_ARRIENDO IS NULL
                     AND SU.COD_SUCURSAL = A.COD_SUCURSAL
                     AND P.COD_PERSONA = A.COD_PERSONA";
		
		parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);			
	}
}
?>