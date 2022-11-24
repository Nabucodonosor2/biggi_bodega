<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

/*
Clase : DW_OPCIONES_ESPECIALES
*/
class dw_opciones_especiales extends datawindow {
	function dw_opciones_especiales() {		
		$sql = "SELECT			I.COD_ITEM_MENU,
										I.NOM_ITEM_MENU,
										A.COD_PERFIL, 
										CASE A.AUTORIZA_MENU 
											when 'E'then 'S' 
											else 'N'
										end AUTORIZA,
										A.IMPRESION,
										(select count(*) from	ITEM_MENU II 
										where	II.COD_ITEM_MENU like I.COD_ITEM_MENU + '%' 
										and    II.COD_ITEM_MENU <> I.COD_ITEM_MENU) HIJOS						
						FROM			ITEM_MENU I, AUTORIZA_MENU A
						WHERE			I.COD_ITEM_MENU like '99%'AND
										A.COD_PERFIL = {KEY1} AND
										I.COD_ITEM_MENU = A.COD_ITEM_MENU
						order by I.COD_ITEM_MENU";
					
		parent::datawindow($sql, 'OPCIONES_ESPECIALES', true, true);	
				
		$this->add_control(new edit_check_box('AUTORIZA','S', 'N'));				
	}	
	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);		
		$cod_item_menu = $this->get_item($record, 'COD_ITEM_MENU');
		$indent = strlen($cod_item_menu)- 4;
		$nom_item_menu = $this->get_item($record, 'NOM_ITEM_MENU');				
		$nom_item_menu = str_repeat("&nbsp;", $indent).$nom_item_menu;
		$hijos = $this->get_item($record, 'HIJOS');						
		if ($hijos != 0) {
			$nom_item_menu = '<strong>'.$nom_item_menu.'</strong>';
			$temp->setVar($this->label_record.'.DW_TR_CSS', 'titulo_detalle');			
			$temp->setVar($this->label_record.'.AUTORIZA', '');
		}		
		$temp->setVar($this->label_record.'.NOM_ITEM_MENU', $nom_item_menu);		
	}
	function new_record() {
		$sql = $this->sql;
		$sql = str_replace("{KEY1}", '2', $sql);
		$this->retrieve_bd($sql);
	}
	function validate() {
	
	}
	function update($db) {
		for ($i=0; $i < $this->row_count(); $i++) {
			$cod_perfil = $this->get_item($i, 'COD_PERFIL');
			$cod_item_menu = $this->get_item($i, 'COD_ITEM_MENU');			
			$autoriza = $this->get_item($i, 'AUTORIZA');			
			$sp = 'spu_opciones_especiales';
			$param = "$cod_perfil,$cod_item_menu,$autoriza";
			if (!$db->EXECUTE_SP($sp,$param))			
				return false;
		}
		return true;
	}
}		


/*
Clase : DW_AUTORIZA_MENU
*/
class dw_autoriza_menu extends datawindow {
	function dw_autoriza_menu() {		
		$sql = "SELECT		i.COD_ITEM_MENU,
											i.NOM_ITEM_MENU, 
											a.COD_PERFIL,  
											case a.AUTORIZA_MENU when 'N'then 'N' else 'S'end LECTURA,				
											case a.AUTORIZA_MENU when 'E' then 'S' else 'N' end ESCRITURA,
											a.IMPRESION,
											a.EXPORTAR,
											I.TIENE_IMPRESION,
											(select count(*) from	ITEM_MENU ii 
												where	ii.COD_ITEM_MENU like i.COD_ITEM_MENU + '%' 
													and ii.COD_ITEM_MENU <> i.COD_ITEM_MENU
													and ii.TIPO_ITEM_MENU in ('M', 'TP')) HIJOS,
											dbo.f_get_tabs_por_modulo (i.COD_ITEM_MENU, a.COD_PERFIL) TAB,
											TIPO_ITEM_MENU
								FROM		ITEM_MENU i, AUTORIZA_MENU a  
								WHERE		a.COD_PERFIL = {KEY1} and
											a.COD_ITEM_MENU not like '99%' and
											i.COD_ITEM_MENU = a.COD_ITEM_MENU and
											i.TIPO_ITEM_MENU in ('M', 'P', 'TP')
								ORDER BY	a.COD_ITEM_MENU";
					
					
		parent::datawindow($sql, 'AUTORIZA_MENU', true, true);		
		
		$this->add_control(new edit_check_box('LECTURA','S', 'N'));
		$this->add_control(new edit_check_box('ESCRITURA','S', 'N'));
		$this->add_control(new edit_check_box('IMPRESION','S', 'N'));
		$this->add_control(new edit_check_box('EXPORTAR','S', 'N'));
				
	}	
	function fill_record(&$temp, $record) {
		parent::fill_record($temp, $record);	
		$cod_item_menu = $this->get_item($record, 'COD_ITEM_MENU');
		$indent = strlen($cod_item_menu) - 2;
		$nom_item_menu = $this->get_item($record, 'NOM_ITEM_MENU');				
		$nom_item_menu = str_repeat("&nbsp;", $indent).$nom_item_menu;
		$hijos = $this->get_item($record, 'HIJOS');		
		
		if ($cod_item_menu=='1025')		 $hijos = 0;
				
		if ($hijos != 0) {
			$nom_item_menu = '<strong>'.$nom_item_menu.'</strong>';
			$temp->setVar($this->label_record.'.DW_TR_CSS', 'titulo_detalle');
			$temp->setVar($this->label_record.'.LECTURA', '');
			$temp->setVar($this->label_record.'.ESCRITURA', '');
			$temp->setVar($this->label_record.'.IMPRESION', '');
			$temp->setVar($this->label_record.'.EXPORTAR', '');
		}		
		
		// para las parametricas
		$tipo_item_menu = $this->get_item($record, 'TIPO_ITEM_MENU');
		$string = strlen($cod_item_menu);
		if ($tipo_item_menu=='P') {		// parametricas
			$indent += 2;
			$nom_item_menu = str_repeat("&nbsp;", 2).$nom_item_menu;			
		}
		elseif ($tipo_item_menu=='TP') {	// titulo parametrica
			$nom_item_menu = '<strong>'.$nom_item_menu.'</strong>';
			
			if ($record % 2 == 0)				
				$temp->setVar($this->label_record.'.DW_TR_CSS', 'claro');												
			else
				$temp->setVar($this->label_record.'.DW_TR_CSS', 'oscuro');		
		}
		$temp->setVar($this->label_record.'.NOM_ITEM_MENU', $nom_item_menu);		

		// agregar checkbox de impresion solo si tiene impresion
		$tiene_impresion = $this->get_item($record, 'TIENE_IMPRESION');						
		if ($tiene_impresion=='N')
			$temp->setVar($this->label_record.'.IMPRESION', '');
			
		$tabs = $this->get_item($record, 'TAB');
		if ($tabs=='')
			$temp->setVar($this->label_record.'.TAB', '');
		else{
			if ($tabs!=' ') {	
				$tab = '<BR>'.str_repeat("&nbsp;", $indent + 10);
				$res = explode('|', $tabs);
	    		$tope = count($res);
	    		for ($i = 0; $i < $tope; $i=$i+3){
					for ($j=$i; $j<$i+3; $j++){
						if ($j==$i)  //codigo
							$cod_item = $res[$j];
						else if ($j==$i+1) // label
							$label = $res[$j];
						else if ($j==$i+2){  //valor
							$valor = $res[$j];
							if ($this->entrable)
								$disable = '';
							else
								$disable =  'disabled="disabled"';
						} // end if ($j=$i+2)		
					}	// end for ($j)
					if ($valor=='N')
						$tab .= '<label><input type="checkbox" id="TAB_'.$cod_item.'"'.$disable.' value="N" name="TAB_'.$cod_item.'"'.$disable.'>'.$label.'</label>  ';
					else
						$tab .= '<label><input type="checkbox" id="TAB_'.$cod_item.'"'.$disable.' value="S" name="TAB_'.$cod_item.'" CHECKED '.$disable.'>'.$label.'</label>  ';
				}	// end for ($i)
				$temp->setVar($this->label_record.'.TAB', $tab);
					
			}	
		}	
	}	
	function new_record() {
		$sql = $this->sql;
		$sql = str_replace("{KEY1}", '2', $sql);
		$this->retrieve_bd($sql);
	}
	function validate() {
		for ($i=0; $i < $this->row_count(); $i++) {
			$lectura = $this->get_item($i, 'LECTURA');
			$escritura = $this->get_item($i, 'ESCRITURA');
			$impresion = $this->get_item($i, 'IMPRESION');
			$exportar = $this->get_item($i, 'EXPORTAR');
			if ($lectura=='N' && ($escritura=='S' || $impresion=='S'))
				$this->set_item($i, 'LECTURA', 'S');
		}		
		return parent::validate();
	}
	function get_values_from_POST() {
		if (!$this->entrable)
			return ;		
		
		parent::get_values_from_POST();
		
		// Para los TAB
		for ($i=0; $i < $this->row_count(); $i++) {
			$tabs = $this->get_item($i, 'TAB');
			$tabs_new = '';
			
			if ($tabs!=' '){
				$res = explode('|', $tabs);
		    	$tope = count($res);
		    	$tabs_new = '';
		    	for ($j = 0; $j < $tope; $j=$j+3){
					for ($k=$j; $k<$j+3; $k++){
						if ($k==$j)  //codigo
							$cod_item = $res[$k];
						else if ($k==$j+2){  //valor
							$id_tab = 'TAB_'.$cod_item;
							if (isset($_POST[$id_tab]))
								$valor = 'E';
							else
								$valor = 'N';
							$tabs_new = $tabs_new."$cod_item|$valor|";
						}
					}	
				}	// end for $j 
			}
			$this->set_item($i, 'TAB', $tabs_new);
		}
	}
	function update($db) {
		for ($i=0; $i < $this->row_count(); $i++) {
			$cod_perfil = $this->get_item($i, 'COD_PERFIL');
			$cod_item_menu = $this->get_item($i, 'COD_ITEM_MENU');
			$lectura = $this->get_item($i, 'LECTURA');
			$escritura = $this->get_item($i, 'ESCRITURA');
			$impresion = $this->get_item($i, 'IMPRESION');
			$exportar = $this->get_item($i, 'EXPORTAR');
			$tabs = $this->get_item($i, 'TAB');

			$sp = 'spu_autoriza_menu';
			$param = $cod_perfil.",'".$cod_item_menu."','".$lectura."','".$escritura."','".$impresion."','".$exportar."','".$tabs."'";
			if (!$db->EXECUTE_SP($sp,$param))
				return false;
		}
		return true;
	}
}		


/*
Clase : WI_PERFIL
*/
class wi_perfil extends w_input
{
  function wi_perfil($cod_item_menu)
  {
    parent::w_input('perfil', $cod_item_menu);
		$this->add_FK_delete_cascada('AUTORIZA_MENU');					
	
    $sql = "select COD_PERFIL, 
				NOM_PERFIL,
				NOM_PERFIL NOM_PERFIL_H
				from PERFIL
				where COD_PERFIL = {KEY1}";
    $this->dws['dw_perfil'] = new datawindow($sql);

    // asigna los formatos
    $this->dws['dw_perfil']->add_control(new edit_text_upper('NOM_PERFIL', 80, 100));
	
	
	// asigna los mandatorys		
	$this->dws['dw_perfil']->set_mandatory('NOM_PERFIL', 'Perfil');
	
	$this->dws['dw_autoriza_menu'] = new dw_autoriza_menu();
	$this->dws['dw_opciones_especiales'] = new dw_opciones_especiales();
	
	$this->add_auditoria_relacionada('AUTORIZA_MENU', 'AUTORIZA_MENU');
	$this->add_auditoria_relacionada('AUTORIZA_MENU', 'IMPRESION');
	$this->add_auditoria_relacionada('AUTORIZA_MENU', 'EXPORTAR');
  }

  function new_record()
  {
    $this->dws['dw_perfil']->insert_row();
	$this->dws['dw_autoriza_menu']->new_record();
	$this->dws['dw_opciones_especiales']->new_record();
	   
  }
  function load_record()
  {
    $cod_perfil = $this->get_item_wo($this->current_record, 'COD_PERFIL');
    $this->dws['dw_perfil']->retrieve($cod_perfil);
	$this->dws['dw_autoriza_menu']->retrieve($cod_perfil);
	$this->dws['dw_opciones_especiales']->retrieve($cod_perfil);
	if($cod_perfil == 1 or $cod_perfil == 2){	
		$this->b_delete_visible = false;
		$this->b_save_visible = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible = false;
	}	
	else {
		$this->b_delete_visible = true;
		$this->b_save_visible = true;
		$this->b_no_save_visible = true;
		$this->b_modify_visible = true;
	}
	
  }
  function get_key()
  {
    return $this->dws['dw_perfil']->get_item(0, 'COD_PERFIL');
  }
  
  function save_record($db)
  {
    $COD_PERFIL = $this->get_key();
    $NOM_PERFIL = $this->dws['dw_perfil']->get_item(0, 'NOM_PERFIL');
	
    
    $COD_PERFIL = ($COD_PERFIL=='') ? "null" : $COD_PERFIL;		
    
	$sp = 'spu_perfil';
    if ($this->is_new_record())
    	$operacion = 'INSERT';
    else
    	$operacion = 'UPDATE';
    
    $param	= "'$operacion', $COD_PERFIL, '$NOM_PERFIL'"; 
    

    if ($db->EXECUTE_SP($sp, $param))
    {
      if ($this->is_new_record())
      {
        $cod_perfil = $db->GET_IDENTITY();
        $this->dws['dw_perfil']->set_item(0, 'COD_PERFIL', $cod_perfil);
      }	  
	    $COD_PERFIL = $this->get_key();
	  	for ($i=0; $i<$this->dws['dw_autoriza_menu']->row_count(); $i++)
				$this->dws['dw_autoriza_menu']->set_item($i, 'COD_PERFIL', $COD_PERFIL, 'primary', false);				
			if ($this->dws['dw_autoriza_menu']->update($db)) {	
				for ($i=0; $i<$this->dws['dw_opciones_especiales']->row_count(); $i++)
					$this->dws['dw_opciones_especiales']->set_item($i, 'COD_PERFIL', $COD_PERFIL, 'primary', false);				
				if ($this->dws['dw_opciones_especiales']->update($db))			
					return true;
			}
		}
    return false;
  }

  
}
?>