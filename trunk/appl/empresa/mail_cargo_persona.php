<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$cod_persona = $_REQUEST['cod_persona'];
$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
$sql = "select	dbo.f_emp_get_mail_cargo_persona(COD_PERSONA, '[EMAIL] - [NOM_CARGO]') MAIL_CARGO_PERSONA
				from		PERSONA
				where		COD_PERSONA = ".$cod_persona;	
$result = $db-> build_results($sql);
print urlencode($result[0]['MAIL_CARGO_PERSONA']);
?>