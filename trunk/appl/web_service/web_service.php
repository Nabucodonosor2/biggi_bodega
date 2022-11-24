<?
require_once(dirname(__FILE__)."/../../appl.ini");

$error_reporting = error_reporting();
error_reporting(0);
$rawPost = strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') == 0? (isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : file_get_contents("php://input")) : NULL;

require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/nusoap-0.7.3/lib/nusoap.php");
$server = new soap_server;
$server->debug_flag = 1;
$server->register("ws_item_nv");
$server->service($rawPost);

function ws_item_nv($cod_nota_venta){
	$conn = mssql_connect(K_SERVER, K_USER, K_PASS);
	mssql_select_db(K_BD, $conn);
	$sql = "select COD_PRODUCTO
					,NOM_PRODUCTO
					,COD_ITEM_NOTA_VENTA
			from ITEM_NOTA_VENTA
			where COD_NOTA_VENTA = $cod_nota_venta";
			
			
	$query = mssql_query($sql);
	//return $sql;
	$result = '';
	//return 'conn '.$conn ;
	$row = mssql_fetch_array($query);
	//$row = mssql_fetch_row($query);
	
	$aux = "abcdefg";
	$aux = str_replace('ab',$row['COD_ITEM_NOTA_VENTA'],$aux); 
	$aux2 = substr($row['COD_ITEM_NOTA_VENTA'], 0, 2);
	//$aux = $row['COD_ITEM_NOTA_VENTA'];
	/*
	$aux2 = "";
	for ($i=0; $i<2;$i++)
		$aux2 .= substr($aux,$i,1); 
	*/
	$var= "hola $aux2 chao";
	return $var;
	
	if (count($row)==0)
		return 'cero';
	else
		return  $row[0];
	/*
	while ($row = mssql_fetch_array($query)) {
    	//$result .= $row['COD_PRODUCTO'].'|'.$row['NOM_PRODUCTO'].'|';
    	return  'chao';
		return  $row['NOM_PRODUCTO'];
		$result .= $row['NOM_PRODUCTO']; 
    }
*/
	return urlencode($result);
    
}			

error_reporting($error_reporting);
?>