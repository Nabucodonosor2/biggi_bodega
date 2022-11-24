<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wo_ajuste_bodega extends w_output
{
   function wo_ajuste_bodega()
   {      
		$sql = "SELECT	AB.COD_AJUSTE_BODEGA,
						convert(varchar(20), AB.FECHA_AJUSTE_BODEGA, 103) FECHA_AJUSTE_BODEGA,
						U.NOM_USUARIO,
						B.NOM_BODEGA,
						AB.OBS,
						EAB.COD_ESTADO_AJUSTE_BODEGA,
						EAB.NOM_ESTADO_AJUSTE_BODEGA
				FROM AJUSTE_BODEGA AB, USUARIO U, BODEGA B, ESTADO_AJUSTE_BODEGA EAB
				WHERE	AB.COD_USUARIO = U.COD_USUARIO
				AND		AB.COD_BODEGA = B.COD_BODEGA
				AND		AB.COD_ESTADO_AJUSTE_BODEGA = EAB.COD_ESTADO_AJUSTE_BODEGA
				ORDER BY COD_AJUSTE_BODEGA DESC";
			
		parent::w_output('ajuste_bodega', $sql, $_REQUEST['cod_item_menu']);
		      
		// headers
		$this->add_header(new header_num('COD_AJUSTE_BODEGA', 'COD_AJUSTE_BODEGA', 'Código'));
		$this->add_header(new header_date('FECHA_AJUSTE_BODEGA', 'FECHA_AJUSTE_BODEGA', 'Fecha'));
		$this->add_header(new header_text('NOM_USUARIO', 'NOM_USUARIO', 'Usuario'));
		$this->add_header(new header_text('NOM_BODEGA', 'NOM_BODEGA', 'Bodega'));
		$this->add_header(new header_text('OBS', 'OBS', 'Observación'));
		$sql_tipo_estado = "SELECT COD_ESTADO_AJUSTE_BODEGA, NOM_ESTADO_AJUSTE_BODEGA FROM ESTADO_AJUSTE_BODEGA ORDER BY COD_ESTADO_AJUSTE_BODEGA";
      	$this->add_header(new header_drop_down('NOM_ESTADO_AJUSTE_BODEGA', 'EAB.COD_ESTADO_AJUSTE_BODEGA', 'Estado', $sql_tipo_estado));
   }
}
?>