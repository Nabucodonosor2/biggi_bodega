<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
$K_ESTADO_SII_IMPRESA 	= 2;
$K_ESTADO_SII_ENVIADA	= 3;

	$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

	//select me trae todos lo usuarios con saldo en factura
	$sql	=	"SELECT DISTINCT F.COD_USUARIO_VENDEDOR1 COD_USUARIO
						,U.NOM_USUARIO
						,U.MAIL
				FROM	FACTURA F, USUARIO U
				WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
				AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
				AND		U.COD_USUARIO = COD_USUARIO_VENDEDOR1
				ORDER BY COD_USUARIO_VENDEDOR1 asc";
	$sql_cod_vendedor	= $db->build_results($sql);
	
	for($i=0;$i < count($sql_cod_vendedor); $i++) {
		$cod_vendedor = $sql_cod_vendedor[$i]['COD_USUARIO'];
		$nom_vendedor = $sql_cod_vendedor[$i]['NOM_USUARIO'];
		$mail_vendedor = $sql_cod_vendedor[$i]['MAIL'];
		
		//creando el template enviando nombre y cod_usuario
		$temp = new Template_appl('facturas_por_cobrar.htm');
		$temp->setVar("NOM_VENDEDOR", $nom_vendedor);		
		
		// Calculando el total de sus facturas con saldos
		$sql_total	= 	"SELECT sum(dbo.f_fa_saldo(F.COD_FACTURA)) TOTAL
								FROM	FACTURA F
								WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
								AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
								AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor";
		$total_fa	= $db->build_results($sql_total);
		$total_fa	= $total_fa[0]['TOTAL'];
	
		$temp->setVar("TOTAL_SALDO", number_format($total_fa, 0, ',','.'));
		
		//ENVIANDO LOS DATOS AL HTML. 5 FACTURAS MAS ANTIGUAS
		$sql_antigua = "SELECT TOP 5 F.NRO_FACTURA
								,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
								,F.NOM_EMPRESA
								,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
								,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
						FROM	FACTURA F
						WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
						AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
						AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
						ORDER BY F.FECHA_FACTURA asc";	
		$dw_fa_antigua = new datawindow($sql_antigua, "FA_ANTIGUA");
		$dw_fa_antigua->add_control(new static_num('MONTO'));
		$dw_fa_antigua->add_control(new static_num('PORC'));
		$dw_fa_antigua->retrieve();
		
		//ENVIANDO DATOS AL HTML. 5 FACTURAS MAS ALTAS
		$sql_altas = "SELECT TOP 5 F.NRO_FACTURA
									,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
									,F.NOM_EMPRESA
									,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
									,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
							FROM	FACTURA F
							WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
							AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
							AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
							ORDER BY dbo.f_fa_saldo(F.COD_FACTURA) desc";	
		$dw_fa_altas = new datawindow($sql_altas, "FA_ALTAS");
		$dw_fa_altas->add_control(new static_num('MONTO'));
		$dw_fa_altas->add_control(new static_num('PORC'));
		$dw_fa_altas->retrieve();

		//ENVIANDO DATOS AL HTML. TODAS LAS FACTURAS CON SALDO
		$sql_todas = "SELECT 	F.NRO_FACTURA
									,dbo.f_format_date(F.FECHA_FACTURA, 1) FECHA_FACTURA
									,F.NOM_EMPRESA
									,dbo.f_fa_saldo(F.COD_FACTURA) MONTO
									,dbo.f_fa_saldo(F.COD_FACTURA) * 100/$total_fa PORC
							FROM	FACTURA F
							WHERE	dbo.f_fa_saldo(F.COD_FACTURA) > 0
							AND		F.COD_ESTADO_DOC_SII in (".$K_ESTADO_SII_IMPRESA.", ".$K_ESTADO_SII_ENVIADA.")
							AND		F.COD_USUARIO_VENDEDOR1 = $cod_vendedor
							ORDER BY  F.FECHA_FACTURA desc";	
		$dw_fa_todas = new datawindow($sql_todas, "FA_TODAS");
		$dw_fa_todas->add_control(new static_num('MONTO'));
		$dw_fa_todas->add_control(new static_num('PORC'));			
		$dw_fa_todas->retrieve();

		// habilitando el template
		$dw_fa_antigua->habilitar($temp, false);
		$dw_fa_altas->habilitar($temp, false);
		$dw_fa_todas->habilitar($temp, false);

		
		$html = $temp->toString();
	
		//codigo de aqui llega el mail
		$para = $mail_vendedor . ', '; // atención a la coma
		$para .= 'ascianca@biggi.cl, jjofre@biggi.cl, sergio.pechoante@biggi.cl, kverdugo@biggi.cl, rescudero@biggi.cl';
		
	    // Envio de mail
	    $asunto = ' Facturas por cobrar '.$nom_vendedor;
		$cabeceras = "From: sergio.pechoante@biggi.cl\n";		
		$cabeceras .= "CC: cscianca@biggi.cl\n";
		$cabeceras .= "BCC: mherrera@biggi.cl, imeza@biggi.cl, victor.melo@biggi.cl, sanchez@biggi.cl\n";
		$cabeceras .= 'Content-type: text/html; charset=iso-8859-1';	
		
		mail($para, $asunto, $html, $cabeceras);
		
	}
	header('Location:mail_masivo.htm');
?>
