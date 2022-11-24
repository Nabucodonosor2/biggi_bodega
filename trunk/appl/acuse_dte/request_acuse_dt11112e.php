<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
include(dirname(__FILE__)."/../../appl.ini");
session::set('K_ROOT_DIR',K_ROOT_DIR);

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/class_PHPMailer.php");

ini_set("display_errors", "On");
//http://www.biggi.cl/sysbiggi_new/bodega_biggi/biggi/trunk/appl/acuse_dte/request_acuse_dte.php?NRO_DOC=111&TIPO_DOCUMENTO=33&DESTINATARIO=%7B%22Email+intercambio%22%3A%22sii_dte%40integrasystem.cl%22%2C%22Email+receptor%22%3A%22contacto%40ingtec.cl%22%7D&RUT_EMISOR=80112900&TRIGGER=DocumentoGenerado

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);


$DESTINATARIO_ARRAY = $_REQUEST["DESTINATARIO"];
$DESTINATARIO_ARRAY = urldecode($DESTINATARIO_ARRAY);
$DESTINATARIO_ARRAY = json_decode($DESTINATARIO_ARRAY) ;
$DESTINATARIO = $DESTINATARIO_ARRAY->{'Email intercambio'};


$trigeer = $_REQUEST["TRIGGER"];
$nro_doc = $_REQUEST["NRO_DOC"];
$tipo_documento = $_REQUEST["TIPO_DOCUMENTO"];
$rut = $_REQUEST["RUT_EMISOR"];//RUT BIGGI

$spu = "spu_acuse_dte";
	$params = "'INSERT'
				,'prueba'
				,'$DESTINATARIO|$rut|$trigeer|$tipo_documento'
				,$nro_doc";
			
$db->query("exec $spu $params");


if($tipo_documento == 33){
	$nom_tipo_documento = "Factura Electrónica";
	$tipo_doc_mail = 'ENVIO_DTE_FA';
}else if($tipo_documento == 34){
	$nom_tipo_documento = "Factura Electrónica Exenta";
	$tipo_doc_mail = 'ENVIO_DTE_FAE';
}else if($tipo_documento == 52){
	$nom_tipo_documento = "Guía de Despacho Electrónica";
	$tipo_doc_mail = 'ENVIO_DTE_GD';		
}else if($tipo_documento == 61){
	$nom_tipo_documento = "Nota de Crédito Electrónica";	
	$tipo_doc_mail = 'ENVIO_DTE_NC';
}


if($rut == '91462001'){//COMERCIAL Y RENTAL SON IGUALES
	$nombre_emisor = 'DTE Comercial Biggi Chile SA';
	$pie_pagina = 'BIGGI CHILE SOC LTDA FABRICACIÓN DE MAQUINARIA PARA LA ELABORACIÓN DE ALIMENTOS, BEBIDAS Y TABACOS';
	$emisor = 'comercial_dte@integrasystem.cl';
	$username = 'dte_914620015@biggi.cl';
}else if($rut == '80112900'){//BODEGA
	$nombre_emisor = 'DTE Biggi Chile Soc Ltda';
	$pie_pagina = 'BIGGI CHILE SOC LTDA FABRICACIÓN DE MAQUINARIA PARA LA ELABORACIÓN DE ALIMENTOS, BEBIDAS Y TABACOS';
	$emisor = 'bodega_biggi@integrasystem.cl';
	$username = 'dte_801129005@biggi.cl';
}else if($rut == '89257000'){
	$nombre_emisor = 'DTE Comercial Todoinox Ltda';
	$pie_pagina = 'VENTAS AL POR MAYOR DE PRODUCTOS DE COCINA';
	$emisor = 'todoinox_dte@integrasystem.cl';
	$username = 'dte_892570000@biggi.cl';
}


$password = '1726Biggi';

$nom_from = $nombre_emisor;
$mail_from = $emisor;

$spu = "spu_acuse_dte";
$params = "'INSERT'
			,'prueba'
			,'LINEA 74'
			,$nro_doc";
$db->query("exec $spu $params");
	/*		
if($tipo_documento == 33 || $tipo_documento == 34){
	if($tipo_documento == 33)
		$tipo_doc_mail = 'ENVIO_DTE_FA';
	else if($tipo_documento == 34)	
		$tipo_doc_mail = 'ENVIO_DTE_FAE';
	
	$sql_count = "SELECT COUNT (*)CONT FROM FACTURA WHERE NRO_FACTURA = $nro_doc
					AND COD_ENVIO_MAIL_DTE IS NULL";
	$result_c = $db->build_results($sql_count);
	$cont = $result_c[0]['CONT'];
	if($cont == 0){
		return;
	}
	$sql = 	"SELECT F.XML_DTE
			,CONVERT(VARCHAR,F.FECHA_FACTURA,103)FECHA
			,E.NOM_EMPRESA
			,dbo.f_get_separador_miles(F.TOTAL_CON_IVA)MONTO
	   		FROM FACTURA F
	   			,EMPRESA E
	   		WHERE F.NRO_FACTURA = $nro_doc
	   		AND E.COD_EMPRESA = F.COD_EMPRESA";
}else if($tipo_documento == 52){
	$spu = "spu_acuse_dte";
	$params = "'INSERT'
				,'prueba'
				,'LINEA 103'
				,$nro_doc";
	$db->query("exec $spu $params");
	$tipo_doc_mail = 'ENVIO_DTE_GD';
	
	$sql_count_gd = "SELECT COUNT (*)CONT FROM GUIA_DESPACHO WHERE NRO_GUIA_DESPACHO = $nro_doc
					AND COD_ENVIO_MAIL_DTE IS NULL";
	$result_gd = $db->build_results($sql_count_gd);
	$cont_gd = $result_gd[0]['CONT'];
	$cont_gd = (int)$cont_gd;
	if($cont_gd < 1){
		$spu = "spu_acuse_dte";
		$params = "'INSERT'
					,'prueba $cont_gd'
					,'contador = 0'
					,$nro_doc";
		$db->query("exec $spu $params");
		return;
	}else{	
		$sql = "SELECT G.XML_DTE
				,CONVERT(VARCHAR,G.FECHA_GUIA_DESPACHO,103)FECHA
				,E.NOM_EMPRESA
				,dbo.f_get_separador_miles((SELECT SUM(i.CANTIDAD*i.PRECIO) FROM ITEM_GUIA_DESPACHO i	WHERE i.COD_GUIA_DESPACHO = G.COD_GUIA_DESPACHO))MONTO
		   FROM GUIA_DESPACHO G
		   		,EMPRESA E
		   WHERE G.NRO_GUIA_DESPACHO = $nro_doc
		   AND E.COD_EMPRESA = G.COD_EMPRESA";	
		   
		$spu = "spu_acuse_dte";
		$params = "'INSERT'
						,'prueba'
						,'linea 133'
						,$nro_doc";
		$db->query("exec $spu $params");
	}	
}else if($tipo_documento == 61){
	$tipo_doc_mail = 'ENVIO_DTE_NC';
	
	$sql_count = "SELECT COUNT (*)CONT FROM NOTA_CREDITO WHERE NRO_NOTA_CREDITO = $nro_doc
					AND COD_ENVIO_MAIL_DTE IS NULL";
	$result_c = $db->build_results($sql_count);
	$cont = $result_c[0]['CONT'];
	if($cont == 0)
		return;
		
	$sql = 	"SELECT N.XML_DTE
			,CONVERT(VARCHAR,N.FECHA_NOTA_CREDITO,103)FECHA
			,E.NOM_EMPRESA
			,dbo.f_get_separador_miles(N.TOTAL_CON_IVA)MONTO
   			FROM NOTA_CREDITO N
   				,EMPRESA E
	   		WHERE N.NRO_NOTA_CREDITO = $nro_doc
	   		AND E.COD_EMPRESA = N.COD_EMPRESA";	

}

$result = $db->build_results($sql);
$XML_ORIGINAL = $result[0]['XML_DTE'];
$fecha_documento = $result[0]['FECHA'];
$NOM_EMPRESA_DETINATARIO = $result[0]['NOM_EMPRESA'];
$monto = $result[0]['MONTO'];
*/

$body = $pie_pagina;

$spu = "spu_acuse_dte";
$params = "'INSERT'
			,'prueba'
			,'LINEA 155'
			,$nro_doc";
				
$db->query("exec $spu $params");

$mail = new phpmailer();
$mail->PluginDir = dirname(__FILE__)."/../../../../commonlib/trunk/php/clases/";
$mail->Mailer = "smtp";
$mail->SMTPAuth = true;
$mail->Host = 'Mail.biggi.cl';
$mail->Username = $username;
$mail->Password = $password; 
$mail->From = $mail_from;
$mail->FromName = $nom_from;
$mail->Timeout=30;
$mail->Subject = "EnvioDTE: $rut - $nom_tipo_documento N° $nro_doc";//Ej: EnvioDTE: 80.112.900-5 - Factura electrónica N° 171115
$mail_para = 'ibrito@integrasystem.cl';//$DESTINATARIO;
$mail->AddAddress($mail_para);
$mail->AddBCC('mherrera@biggi.cl','Marcelo Herrera');
$mail->Body = $body;


//$mail->AddAttachment($fname, $name_archivo);


// cuando se envia por CRONTAB cambiar true la linea siguiente
$CRONTAB = true;
if ($CRONTAB)
	$exito = true;
else
	$exito = $mail->Send();	

$spu = "spu_acuse_dte";
	$params = "'INSERT'
				,'prueba'
				,'LINEA 189'
				,$nro_doc";
				
	$db->query("exec $spu $params");	
	
	$sp = "spu_envio_mail";
	
	$param = "
			'ACUSE_DTE'
			,null
			,null
			,null
		 	,'$mail->From'
		 	,'$nom_from'
		 	,null
		 	,null
		 	,'mherrera@biggi.cl'
		 	,'Marcelo Herrera'
		 	,'$mail_para'
		 	,null
		 	,'$mail->Subject'
		 	,'".str_replace("'","''",$mail->Body)."'
		 	,NULL
		 	,'$tipo_doc_mail'
		 	,$nro_doc
		 	,'$username'";

	$db->query("exec $sp $param");		 	
	$spu = "spu_acuse_dte";
	
	$params = "'INSERT'
				,'$username'
				,'$mail->From'
				,$nro_doc";
				
	$db->query("exec $spu $params");
		
?>