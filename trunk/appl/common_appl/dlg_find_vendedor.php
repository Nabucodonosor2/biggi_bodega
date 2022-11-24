<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

$nom_header = $_REQUEST['nom_header'];
$valor_filtro = $_REQUEST['valor_filtro'];
$sql = $_REQUEST['sql'];

$so = base::get_SO();
if ($so == 'windows')
	$sql = str_replace("\'", "'",$sql);

$temp = new Template_appl(session::get('K_ROOT_DIR').'html/dlg_find_vendedor.htm');	
$temp->setVar("PROMPT", 'Filtrar por '.$nom_header);
$drop_down = new drop_down_dw('VALOR', $sql);
$html = $drop_down->draw_entrable($valor_filtro, 0);
$temp->setVar("VALOR", $html);

// incluye vendedores no vigentes
$control = new edit_check_box("INCLUYE_NO_VIGENTE", 'S', 'N', 'Incluir no vigentes');
$control->set_onChange('incluye_no_vigente(this);');
$html = $control->draw_entrable('N', 0);
$temp->setVar("INCLUYE_NO_VIGENTE", $html);

print $temp->toString();
?>