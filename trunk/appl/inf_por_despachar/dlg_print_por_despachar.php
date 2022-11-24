<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$temp = new Template_appl('dlg_print_por_despachar.htm');	

$K_ESTADO_DOC_SII_IMPRESO= 2;
$K_ESTADO_DOC_SII_ENVIADA = 3;
$sql = "select 0 COD_USUARIO
				,'Todos' NOM_USUARIO
		union
		select COD_USUARIO
				,NOM_USUARIO
		from USUARIO
		where ES_VENDEDOR = 'S'
		order by NOM_USUARIO";

$dd = new drop_down_dw('COD_USUARIO', $sql, 0, '', false);
$html = $dd->draw_entrable('0', 0);
$temp->setVar("COD_USUARIO", $html);
print $temp->toString();
?>