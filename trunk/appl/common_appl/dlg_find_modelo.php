<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nom_header = $_REQUEST['nom_header'];
$valor_filtro = $_REQUEST['valor_filtro'];
$lista = explode('|', $valor_filtro);

$temp = new Template_appl(session::get('K_ROOT_DIR').'html/dlg_find_modelo.htm');	
$temp->setVar("PROMPT", 'Filtrar por '.$nom_header);

$control = new edit_text('VALOR', 40, 50);
$html = $control->draw_entrable($lista[0], 0);
$temp->setVar("VALOR", $html);

$control = new edit_check_box("FIND_EXACTO", 'S', 'N', 'Búsqueda exacta');
if (count($lista)==1)
	$lista[1] = 'N';
$html = $control->draw_entrable($lista[1], 0);
$temp->setVar("FIND_EXACTO", $html);

print $temp->toString();

?>