<?php
///////////////////////
// php llamado con ajax en el javascript function help_empresa()
////////////////////////
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once("class_help_empresa.php");

$cod_empresa = $_REQUEST['cod_empresa'];
$rut = $_REQUEST['rut'];
$alias = $_REQUEST['alias'];
$nom_empresa = $_REQUEST['nom_empresa'];
$tipo_empresa = $_REQUEST['tipo_empresa'];

help_empresa::find_empresa($cod_empresa, $rut, $alias, $nom_empresa, $tipo_empresa);
?>