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
$DESTINATARIO = $DESTINATARIO_ARRAY->{'Intercambio DTE'};

$trigeer = $_REQUEST["TRIGGER"]; //SIRBE AL DEBAGUEAR Y VER DESDE DONDE SE GATILLO
$nro_doc = $_REQUEST["NRO_DOC"];
$tipo_documento = $_REQUEST["TIPO_DOCUMENTO"];
$rut = $_REQUEST["RUT_EMISOR"];//RUT BIGGI

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
	$rut = '91.462.001-5';
	$nombre_emisor = 'DTE Comercial Biggi Chile SA';
	$pie_pagina = 'COMERCIAL BIGGI CHILE S.A.';
	$emisor = 'comercial_dte@integrasystem.cl';
	$username = 'dte_914620015@biggi.cl';
}else if($rut == '80112900'){//BODEGA
	$rut = '80.112.900-5';
	$nombre_emisor = 'DTE Biggi Chile Soc Ltda';
	$pie_pagina = 'BIGGI CHILE SOC LTDA';
	$emisor = 'bodega_biggi@integrasystem.cl';
	$username = 'dte_801129005@biggi.cl';
}else if($rut == '89257000'){
	$rut = '89.257.000-0';
	$nombre_emisor = 'DTE Comercial Todoinox Ltda';
	$pie_pagina = 'COMERCIAL TODOINOX LTDA.';
	$emisor = 'todoinox_dte@integrasystem.cl';
	$username = 'dte_892570000@biggi.cl';
}

$nom_from = $nombre_emisor;
$mail_from = $emisor;
$body = $pie_pagina;

$asunto = "Envío DTE: $rut / $nom_tipo_documento N° $nro_doc.";//Ej: EnvioDTE: 80.112.900-5 - Factura electrónica N° 171115

if($DESTINATARIO == '')
	$mail_para = 'sincasilla@biggi.cl';
else
	$mail_para = $DESTINATARIO;

$sp = "spu_envio_mail";

$param = "
		'ACUSE_DTE'
		,null
		,null
		,null
	 	,'$mail_from'
	 	,'$nom_from'
	 	,null
	 	,null
	 	,'backupxmldte@gmail.com;backupxmldte@biggi.cl;vmelo@integrasystem.cl;evergara@integrasystem.cl;ecastillo@integrasystem.cl'
	 	,'backupxmldte;backupxmldte;Victor Melo;Erick Vergara;Eduardo Castillo'
	 	,'$mail_para'
	 	,null
	 	,'$asunto'
	 	,'".str_replace("'","''",$body)."'
	 	,NULL
	 	,'$tipo_doc_mail'
	 	,$nro_doc
	 	,'$username'";

$db->query("exec $sp $param");		 	
$spu = "spu_acuse_dte";
	
$params = "'INSERT'
			,'$username'
			,'$mail_para'
			,$nro_doc";
			
$db->query("exec $spu $params");
		
?>