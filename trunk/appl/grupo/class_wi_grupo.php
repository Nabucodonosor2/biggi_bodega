<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");


class dw_grupo_usuario extends datawindow {
	function dw_grupo_usuario() {		
		$sql = "select COD_GRUPO_USUARIO,
						COD_GRUPO,
						COD_USUARIO GU_COD_USUARIO
				from GRUPO_USUARIO
				where COD_GRUPO = {KEY1}
				order by COD_GRUPO_USUARIO";	
					
		parent::datawindow($sql, 'GRUPO_USUARIO', true, true);	
		
		$sql = "select COD_USUARIO,
						NOM_USUARIO
				from USUARIO 
				order by NOM_USUARIO";		
		$this->add_control(new drop_down_dw('GU_COD_USUARIO', $sql, 250));
			
	}
	
	function update($db)	{
		$sp = 'spu_grupo_usuario';
		
		for ($i = 0; $i < $this->row_count(); $i++)		{
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$COD_GRUPO_USUARIO = $this->get_item($i, 'COD_GRUPO_USUARIO');
			$COD_GRUPO = $this->get_item($i, 'COD_GRUPO');
			$COD_USUARIO = $this->get_item($i, 'GU_COD_USUARIO');
			
			
			$COD_GRUPO_USUARIO = ($COD_GRUPO_USUARIO=='') ? "null" : $COD_GRUPO_USUARIO;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
			$param = "'$operacion', $COD_GRUPO_USUARIO,$COD_GRUPO,$COD_USUARIO";
			
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			}
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
				
			$COD_GRUPO_USUARIO = $this->get_item($i, 'COD_GRUPO_USUARIO', 'delete');						
			if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_GRUPO_USUARIO"))
				return false;			
		}
		
		return true;
	}
	
}

class wi_grupo extends w_input {
	function wi_grupo($cod_item_menu) {
		parent::w_input('grupo', $cod_item_menu);

		$sql = "select COD_GRUPO, 
						NOM_GRUPO, 
						COD_USUARIO
				from GRUPO
				where COD_GRUPO = {KEY1}";
				
		$this->dws['dw_grupo'] = new datawindow($sql);
		
				// asigna los formatos
		$this->dws['dw_grupo']->add_control(new edit_text_upper('NOM_GRUPO', 80, 100));		
		
		$sql = "select COD_USUARIO,
					NOM_USUARIO
				from USUARIO
				order by NOM_USUARIO ";
		
		$this->dws['dw_grupo']->add_control(new drop_down_dw('COD_USUARIO', $sql, 140));	
		
		// asigna los mandatorys		
		$this->dws['dw_grupo']->set_mandatory('NOM_GRUPO', 'Grupo');
		$this->dws['dw_grupo']->set_mandatory('COD_USUARIO', 'Jefe');
		
		$this->dws['dw_grupo_usuario'] = new dw_grupo_usuario();
			
	}
	function new_record() {
		$this->dws['dw_grupo']->insert_row();
	}
	function load_record() {
		$cod_grupo = $this->get_item_wo($this->current_record, 'COD_GRUPO');
		$this->dws['dw_grupo']->retrieve($cod_grupo);
		
		$this->dws['dw_grupo_usuario']->retrieve($cod_grupo);
	}
	function get_key() {
		return $this->dws['dw_grupo']->get_item(0, 'COD_GRUPO');
	}
	
	function save_record($db) {
		$COD_GRUPO = $this->get_key();
		$NOM_GRUPO = $this->dws['dw_grupo']->get_item(0, 'NOM_GRUPO');
		$COD_USUARIO = $this->dws['dw_grupo']->get_item(0, 'COD_USUARIO');

		$COD_GRUPO = ($COD_GRUPO=='') ? "null" : $COD_GRUPO;		
    
		$sp = 'spu_grupo';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
	    $param	= "'$operacion', $COD_GRUPO, '$NOM_GRUPO', $COD_USUARIO";	 
    
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_GRUPO = $db->GET_IDENTITY();
				$this->dws['dw_grupo']->set_item(0, 'COD_GRUPO', $COD_GRUPO);
			}
			for ($i=0; $i<$this->dws['dw_grupo_usuario']->row_count(); $i++)
				$this->dws['dw_grupo_usuario']->set_item($i, 'COD_GRUPO', $this->dws['dw_grupo']->get_item(0, 'COD_GRUPO'), 'primary', false);				
			 $this->dws['dw_grupo_usuario']->update($db);
			return true;
		}
		return false;			
	}
}
?>