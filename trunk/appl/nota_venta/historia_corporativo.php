<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_nota_venta = $_REQUEST['cod_nota_venta'];

$temp = new Template_appl('historia_corporativo.htm');	
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	// Historial de modificaciones de descuento corporativo
	$sql = "select U.NOM_USUARIO,
				convert(varchar(20), LG.FECHA_CAMBIO, 103) +'  '+ convert(varchar(20), LG.FECHA_CAMBIO, 8) FECHA_CAMBIO,
				DC.VALOR_ANTIGUO,
				DC.VALOR_NUEVO	
			from LOG_CAMBIO LG, DETALLE_CAMBIO DC, USUARIO U
			where LG.NOM_TABLA = 'NOTA_VENTA' and
				LG.KEY_TABLA = '$cod_nota_venta' and
				LG.COD_LOG_CAMBIO = DC.COD_LOG_CAMBIO and
				LG.COD_USUARIO = U.COD_USUARIO and 
				DC.NOM_CAMPO = 'PORC_DSCTO_CORPORATIVO'
				order by LG.FECHA_CAMBIO desc";		
		
	$result = $db->build_results($sql);

	for ($i=0 ; $i <count($result); $i++) {
		$temp->gotoNext("DETALLE_CAMBIO");		

		if ($i % 2 == 0)
			$temp->setVar("DETALLE_CAMBIO.DW_TR_CSS", datawindow::css_claro);
		else
			$temp->setVar("DETALLE_CAMBIO.DW_TR_CSS", datawindow::css_oscuro);

		$usuario = $result[$i]['NOM_USUARIO'];
		$temp->setVar("DETALLE_CAMBIO.NOM_USUARIO", $usuario);
		
		$fecha_mod = $result[$i]['FECHA_CAMBIO'];
		$temp->setVar("DETALLE_CAMBIO.FECHA_CAMBIO", $fecha_mod);
		
		$valor_old = $result[$i]['VALOR_ANTIGUO'];
		$temp->setVar("DETALLE_CAMBIO.VALOR_ANTIGUO", number_format($valor_old, 1, ',', '.'));
		
		$valor_new = $result[$i]['VALOR_NUEVO'];
		$temp->setVar("DETALLE_CAMBIO.VALOR_NUEVO", number_format($valor_new, 1, ',', '.'));	
					
	}
	print $temp->toString();
?>