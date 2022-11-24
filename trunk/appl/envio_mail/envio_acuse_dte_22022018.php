<?php
// primero hay que incluir la clase phpmailer para poder instanciar
//un objeto de la misma
require_once("class_PHPMailer.php");
require_once("class_database.php");
require_once("../../appl.ini");
 
//return;

$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

$db->EXECUTE_SP('sp_crontab_noche', "'MAIL_TO_VACIO'");
$db->EXECUTE_SP('sp_crontab_noche', "'REVISA_COSTO_20000'");
		
$sql = "select dbo.f_get_parametro(53) 		URL_SMTP
				,dbo.f_get_parametro(54) 	USER_SMTP
				,dbo.f_get_parametro(55) 	PASS_SMTP";
$result = $db->build_results($sql);
$URL_SMTP 	= $result[0]['URL_SMTP']; 
$USER_SMTP	= $result[0]['USER_SMTP']; 
$PASS_SMTP	= $result[0]['PASS_SMTP']; 

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
				,XML_DTE
				,USUARIO_DTE
				,COD_DOC
		from ENVIO_MAIL
		where COD_ESTADO_ENVIO_MAIL = 1
		AND XML_DTE IS NOT NULL
		AND USUARIO_DTE IS NOT NULL";	// por enviar
$result = $db->build_results($sql);
// 1ero marca como enviandose todos los registros que va a procesar
for($i=0; $i < count($result); $i++) {
	$COD_ENVIO_MAIL = $result[$i]['COD_ENVIO_MAIL'];
	$db->EXECUTE_SP('spu_envio_mail', "'ENVIANDOSE', $COD_ENVIO_MAIL");
}

for($i=0; $i < count($result); $i++) {
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
	  
	  $mail->Host = $URL_SMTP;
	  $mail->Username = $USUARIO;
	  $mail->Password = '1726Biggi'; 
	  
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
	  $mail->AddAttachment($fname, $name_archivo);//se adjunta el xml
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
?>