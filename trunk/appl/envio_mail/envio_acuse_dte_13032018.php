<?php
// primero hay que incluir la clase phpmailer para poder instanciar
//un objeto de la misma
require_once("class_PHPMailer.php");
require_once("class_database.php");
require_once("../../appl.ini");
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

//ini_set("display_errors", "On");
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$sql = "select COD_ENVIO_MAIL
				,MAIL_FROM
				,MAIL_FROM_NAME
				,MAIL_CC
				,MAIL_CC_NAME
				,MAIL_BCC
				,MAIL_BCC_NAME
				,MAIL_TO
				,MAIL_TO_NAME
				,MAIL_SUBJECT
				,MAIL_BODY
				,MAIL_ALTBODY
				,'' XML_DTE
				,USUARIO_DTE
				,COD_DOC
				,TIPO_DOC 
				,COD_ESTADO_ENVIO_MAIL
		from ENVIO_MAIL
		where COD_ESTADO_ENVIO_MAIL = 1
		AND USUARIO_DTE IS NOT NULL";	// por enviar
$result = $db->build_results($sql);


// 1ero marca como enviandose todos los registros que va a procesar
for($i=0; $i < count($result); $i++) {
	
	$COD_ENVIO_MAIL = $result[$i]['COD_ENVIO_MAIL'];
	$NRO_DTE 		= $result[$i]['COD_DOC'];
	$TIPO_DOC 		= $result[$i]['TIPO_DOC'];
	
	if($TIPO_DOC == 'ENVIO_DTE_FA')
		$tipodte = 33;
	else if($TIPO_DOC == 'ENVIO_DTE_FAE')	
		$tipodte = 34;
	else if($TIPO_DOC == 'ENVIO_DTE_GD')	
		$tipodte = 52;
	else if($TIPO_DOC == 'ENVIO_DTE_NC')	
		$tipodte = 61;	
	
	if($tipodte == 33 || $tipodte == 34){
		
		$sql = "SELECT F.COD_FACTURA 
						,F.XML_DTE
						,CONVERT(VARCHAR,F.FECHA_FACTURA,103)FECHA
						,E.NOM_EMPRESA
						,dbo.f_get_separador_miles(F.TOTAL_CON_IVA)MONTO	
				FROM FACTURA F 
					,EMPRESA E
				WHERE F.NRO_FACTURA = $NRO_DTE
				AND E.COD_EMPRESA = F.COD_EMPRESA";
				
		$result_dte = $db->build_results($sql);
		if(count($result_dte)== 0)
			continue;
			
		if($tipodte == 33)
			$nom_tipo_documento = "Factura Electrónica";
		else if($tipodte == 34)
			$nom_tipo_documento = "Factura Electrónica Exenta";	
			
		$cod_doc_dte = $result_dte[0]['COD_FACTURA'];
		$result[$i]['XML_DTE'] = $result_dte[0]['XML_DTE'];
		$fecha_documento = $result_dte[0]['FECHA'];
		$NOM_EMPRESA_DETINATARIO = $result_dte[0]['NOM_EMPRESA'];
		$monto = $result_dte[0]['MONTO'];
		$pie_pagina = $result[$i]['MAIL_BODY'];
		
		$result[$i]['MAIL_BODY'] = arma_body($NOM_EMPRESA_DETINATARIO,$nom_tipo_documento,$fecha_documento,$monto,$pie_pagina,$NRO_DTE);
				
		$operacion_sp_dte = 'UPDATE_ESTADO_DTE_FA'; 	
	}else if($tipodte == 52){
	
		$sql = "SELECT G.COD_GUIA_DESPACHO
						,G.XML_DTE
						,CONVERT(VARCHAR,G.FECHA_GUIA_DESPACHO,103)FECHA
						,E.NOM_EMPRESA
						,dbo.f_get_separador_miles((SELECT SUM(i.CANTIDAD*i.PRECIO) FROM ITEM_GUIA_DESPACHO i	WHERE i.COD_GUIA_DESPACHO = G.COD_GUIA_DESPACHO))MONTO
				FROM GUIA_DESPACHO G 
					,EMPRESA E
				WHERE NRO_GUIA_DESPACHO = $NRO_DTE
				AND E.COD_EMPRESA = G.COD_EMPRESA";
				
		$result_dte = $db->build_results($sql);
		if(count($result_dte)== 0)
			continue;
			
		$nom_tipo_documento = "Guía de Despacho Electrónica";
		$cod_doc_dte = $result_dte[0]['COD_GUIA_DESPACHO'];
		$result[$i]['XML_DTE'] = $result_dte[0]['XML_DTE'];
		$fecha_documento = $result_dte[0]['FECHA'];
		$NOM_EMPRESA_DETINATARIO = $result_dte[0]['NOM_EMPRESA'];
		$monto = $result_dte[0]['MONTO'];
		$pie_pagina = $result[$i]['MAIL_BODY'];
		
		$result[$i]['MAIL_BODY'] = arma_body($NOM_EMPRESA_DETINATARIO,$nom_tipo_documento,$fecha_documento,$monto,$pie_pagina,$NRO_DTE);
		
		$operacion_sp_dte = 'UPDATE_ESTADO_DTE_GD'; 	
	}else if($tipodte == 61){
		$sql = "SELECT N.COD_NOTA_CREDITO
						,N.XML_DTE
						,CONVERT(VARCHAR,N.FECHA_NOTA_CREDITO,103)FECHA
						,E.NOM_EMPRESA
						,dbo.f_get_separador_miles(N.TOTAL_CON_IVA)MONTO
				FROM NOTA_CREDITO N
					,EMPRESA E
				WHERE NRO_NOTA_CREDITO = $NRO_DTE
				AND E.COD_EMPRESA = N.COD_EMPRESA";
				
		$result_dte = $db->build_results($sql);
		if(count($result_dte)== 0)
			continue;
			
		$nom_tipo_documento = "Nota de Crédito Electrónica";	
		$cod_doc_dte = $result_dte[0]['COD_NOTA_CREDITO'];
		$result[$i]['XML_DTE'] = $result_dte[0]['XML_DTE'];
		$fecha_documento = $result_dte[0]['FECHA'];
		$NOM_EMPRESA_DETINATARIO = $result_dte[0]['NOM_EMPRESA'];
		$monto = $result_dte[0]['MONTO'];
		$pie_pagina = $result[$i]['MAIL_BODY'];
		
		$result[$i]['MAIL_BODY'] = arma_body($NOM_EMPRESA_DETINATARIO,$nom_tipo_documento,$fecha_documento,$monto,$pie_pagina,$NRO_DTE);
		
		$operacion_sp_dte = 'UPDATE_ESTADO_DTE_NC'; 
	}
	
	$sql = "SELECT REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-7','') as RUTEMISOR
			,dbo.f_get_parametro(200) K_HASH";
	
	$consultar 		= $db->build_results($sql);
	$rutemisor		= $consultar[0]['RUTEMISOR'];	
	$Datos_Hash		= $consultar[0]['K_HASH'];	
	
	//Llamamos a dte.
	$dte = new dte();
	$dte->hash = $Datos_Hash;
	//Llamamos al envio consultar estado de socumento.
	$response = $dte->actualizar_estado($tipodte,$NRO_DTE,$rutemisor);
	
	$actualizar_estado	= $dte->respuesta_actualizar_estado($response);
	$revision_estado	= substr($actualizar_estado[6], 0, 3); //respuesta de rechazado.
	
	if($revision_estado <> ''){
		if($revision_estado == 'EPR')
			$cod_estado_libre_dte = 1; //Aceptada	
		else if($revision_estado == 'RPR')
			$cod_estado_libre_dte = 2; //Aceptado con Reparos
		else if($revision_estado == 'RLV')
			$cod_estado_libre_dte = 3; //Aceptada con Reparos Leves
		else if($revision_estado == 'RCH')
			$cod_estado_libre_dte = 4; //Rechazado
		else if($revision_estado == 'RCT')
			$cod_estado_libre_dte = 5; //Rechazado por Error en Carátula
		else if($revision_estado == 'RFR')
			$cod_estado_libre_dte = 6; //Rechazado por Error en Firma
		else if($revision_estado == 'RSC')
			$cod_estado_libre_dte = 7; //Rechazado por Error en Schema
		else	
			$cod_estado_libre_dte = 99; //ESTADO NO DEFINIDO
			
		$db->EXECUTE_SP('spu_libre_dte', "'$operacion_sp_dte', $cod_doc_dte, $cod_estado_libre_dte");	
	}
	
	if ($cod_estado_libre_dte == 1 || $cod_estado_libre_dte == 2 || $cod_estado_libre_dte == 3) {
		$db->EXECUTE_SP('spu_envio_mail', "'ENVIANDOSE', $COD_ENVIO_MAIL");
		$result[$i]['COD_ESTADO_ENVIO_MAIL'] = 2;
	}
}

for($i=0; $i < count($result); $i++) {
	if ($result[$i]['COD_ESTADO_ENVIO_MAIL'] <> 2)
		continue;
		
	$COD_ENVIO_MAIL = $result[$i]['COD_ENVIO_MAIL']; 
	$MAIL_FROM		= $result[$i]['MAIL_FROM']; 
	$MAIL_FROM_NAME	= $result[$i]['MAIL_FROM_NAME']; 
	$MAIL_CC		= $result[$i]['MAIL_CC']; 
	$MAIL_CC_NAME	= $result[$i]['MAIL_CC_NAME']; 
	$MAIL_BCC 		= $result[$i]['MAIL_BCC']; 
	$MAIL_BCC_NAME	= $result[$i]['MAIL_BCC_NAME']; 
	$MAIL_TO 		= $result[$i]['MAIL_TO'];
	$MAIL_TO_NAME	= $result[$i]['MAIL_TO_NAME'];
	$MAIL_SUBJECT	= $result[$i]['MAIL_SUBJECT'];
	$MAIL_BODY 		= $result[$i]['MAIL_BODY'];
	$MAIL_ALTBODY 	= $result[$i]['MAIL_ALTBODY'];
	$XML			= $result[$i]['XML_DTE'];
	$USUARIO		= $result[$i]['USUARIO_DTE'];
	$NRO_DOCUMENTO	= $result[$i]['COD_DOC'];
	
	$XML_DTE = base64_decode($XML);
	$XML_DTE = urldecode($XML_DTE);
	$name_archivo = "XML_DTE_$NRO_DOCUMENTO.xml";
	$fname = tempnam("/tmp", $name_archivo);
	file_put_contents($fname, $XML_DTE);
	
	//instanciamos un objeto de la clase phpmailer al que llamamos 
	//por ejemplo mail
	  $mail = new phpmailer();
	
	  //Definimos las propiedades y llamamos a los métodos 
	  //correspondientes del objeto mail
	
	  //Con PluginDir le indicamos a la clase phpmailer donde se 
	  //encuentra la clase smtp que como he comentado al principio de 
	  //este ejemplo va a estar en el subdirectorio includes
	  $mail->PluginDir = dirname(__FILE__)."/";
	
	  //Con la propiedad Mailer le indicamos que vamos a usar un 
	  //servidor smtp
	  $mail->Mailer = "smtp";
	
	  //Asignamos a Host el nombre de nuestro servidor smtp
	 // $mail->Host = "smtp.hotpop.com";
	
	  //Le indicamos que el servidor smtp requiere autenticación
	  $mail->SMTPAuth = true;
	  
	  $mail->Host = 'Mail.biggi.cl';
	  $mail->Username = $USUARIO;
	  $mail->Password = '1726Biggi';  //CLAVE PARA LOGUEARSE CON  CUALQUIER DE LAS 3 CUENTAS
	  
	  $mail->ClearAddresses();
	  $mail->ContentType="text/html";

	  //Indicamos cual es nuestra dirección de correo y el nombre que 
	  //queremos que vea el usuario que lee nuestro correo
	  $mail->From = $MAIL_FROM;			
	  $mail->FromName = $MAIL_FROM_NAME;
	
	  //el valor por defecto 10 de Timeout es un poco escaso dado que voy a usar 
	  //una cuenta gratuita, por tanto lo pongo a 30  
	  $mail->Timeout=30;
	
	  //Indicamos cual es la dirección de destino del correo
	  $aMAIL_TO = explode(';', $MAIL_TO);
	  $aMAIL_TO_NAME = explode(';', $MAIL_TO_NAME);
	  for ($j=0; $j < count($aMAIL_TO); $j++){
	  	//if($aMAIL_TO[$j] <> 'rescudero@biggi.cl')
			$mail->AddAddress($aMAIL_TO[$j], $aMAIL_TO_NAME[$j]);
	  }
	  if ($MAIL_CC != '') {
		  $aMAIL_CC = explode(';', $MAIL_CC);
		  $aMAIL_CC_NAME = explode(';', $MAIL_CC_NAME);
		  for ($j=0; $j < count($aMAIL_CC); $j++){
		  	//if($aMAIL_CC[$j] <> 'rescudero@biggi.cl')
			  $mail->AddCC($aMAIL_CC[$j], $aMAIL_CC_NAME[$j]);
		  }	  
	  }

	  if ($MAIL_BCC != '') {
		  $aMAIL_BCC = explode(';', $MAIL_BCC);
		  $aMAIL_BCC_NAME = explode(';', $MAIL_BCC_NAME);
		  for ($j=0; $j < count($aMAIL_BCC); $j++){
		  	//if($aMAIL_BCC[$j] <> 'rescudero@biggi.cl')
			  	$mail->AddBCC($aMAIL_BCC[$j], $aMAIL_BCC_NAME[$j]);
		  }	  
	  }
	  
	  //Asignamos asunto y cuerpo del mensaje
	  //El cuerpo del mensaje lo ponemos en formato html, haciendo 
	  //que se vea en negrita
	  $mail->Subject = $MAIL_SUBJECT;
	  $mail->Body = $MAIL_BODY;
	
	  //Definimos AltBody por si el destinatario del correo no admite email con formato html 
	  $mail->AltBody = $MAIL_ALTBODY;
	
	  //se envia el mensaje, si no ha habido problemas 
	  //la variable $exito tendra el valor true
	  //se adjunta el xml
	  $mail->AddAttachment($fname, $name_archivo);
	  $exito = $mail->Send();
	
	  //Si el mensaje no ha podido ser enviado se realizaran 4 intentos mas como mucho 
	  //para intentar enviar el mensaje, cada intento se hara 5 segundos despues 
	  //del anterior, para ello se usa la funcion sleep	
	  $intentos=1; 
	  while ((!$exito) && ($intentos < 5)) {
		sleep(5);
	     	//echo $mail->ErrorInfo;
	     	$exito = $mail->Send();
	     	$intentos=$intentos+1;	
		
	   }
	 
			
	   if(!$exito)
	   {
		echo "Problemas enviando correo electrónico a ".$valor;
		echo "<br/>".$mail->ErrorInfo;	
	   }
	   else
	   {
		//echo "Mensaje enviado correctamente 150";
		$db->EXECUTE_SP('spu_envio_mail', "'ENVIANDO', $COD_ENVIO_MAIL");
	   }
}

function arma_body($NOM_EMPRESA_DETINATARIO,$nom_tipo_documento,$fecha_documento,$monto,$pie_pagina,$nro_doc){
	$body = "Estimados: $NOM_EMPRESA_DETINATARIO. <br>\n <br>\n
			Se adjunta $nom_tipo_documento N° $nro_doc del día $fecha_documento por un monto total de $$monto.- <br>\n <br>\n
			Saluda atentamente, <br>\n <br>\n
			$pie_pagina <br>\n
			Avenida Portugal Nº 1726, Santiago - Chile. <br>\n
			www.biggi.cl";
	return $body;		
	
}
?>