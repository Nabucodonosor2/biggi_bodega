<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nom_tabla = $_REQUEST['nom_tabla'];
$codes = $_REQUEST['codes'];

$nv = session::get('wi_nota_venta');
$nv->dws['dw_pre_orden_compra']->para_comprar($codes);
session::set('wi_nota_venta', $nv);

?>