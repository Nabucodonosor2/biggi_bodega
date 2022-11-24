<?
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/nusoap-0.7.3/lib/nusoap.php");
require_once(dirname(__FILE__)."/../../appl.ini");

$ws_function = 'ws_item_nv';
$cod_nota_venta = 52002;
$soapclient = new soapclient(K_ROOT_URL.'appl/web_service/web_service.php');
$res = $soapclient->call( $ws_function, array('cod_nota_venta' => $cod_nota_venta)); 
echo "*$res*"; 
?>