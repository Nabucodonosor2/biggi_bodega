<?php
require("simple_restserver.php");
require("class_server_biggi.php");
require("server_lista_users.php");

$RestServer =new simple_restserver();	
$RestServer->
	SetAuth($users)->			# SETS THE SERVICE AUTHENTCATIONS <-OPTIONAL
		SetClass('server_biggi')->	# SETS THE YOUR CLASS
			ClassResponse();	# METHOD FOR THE SERVICE RESPONSE
?>