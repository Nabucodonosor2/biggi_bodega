<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class dw_mantencion extends datawindow {
	const K_ESTADO_TERMINADO = 80;
	
	function dw_mantencion() {
		$cod_usuario = session::get("COD_USUARIO");	// viene del login
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		
		$sql= "SELECT	MA.COD_MANTENCION_SW
						,CONVERT(VARCHAR(20), MA.FECHA_MANTENCION_SW, 103) FECHA_MANTENCION_SW
						,MA.COD_USUARIO
						,U.NOM_USUARIO
						,MA.COD_ITEM_MENU
						,MA.COD_USUARIO_SOLICITA
						,MA.ES_GARANTIA
						,MA.REFERENCIA
						,MA.DESCRIPCION
						,case $cod_perfil
							when 1 then ''
							else 'none'
						end DISPLAY_INTERNO
						,ES.NOM_ESTADO_SOLUCION_SW
						,AUTORIZA
						,COD_USUARIO_AUTORIZA					
				FROM	MANTENCION_SW MA
						,USUARIO U						
						,ESTADO_SOLUCION_SW ES
				WHERE	MA.COD_MANTENCION_SW = {KEY1}
						AND U.COD_USUARIO = MA.COD_USUARIO
						AND ES.COD_ESTADO_SOLUCION_SW = dbo.f_mant_estado_solucion(MA.COD_MANTENCION_SW)";
		parent::datawindow($sql);
		$sql = "SELECT	COD_ITEM_MENU
     					,NOM_ITEM_MENU
				FROM ITEM_MENU
				WHERE LEN(COD_ITEM_MENU)=4
				ORDER BY COD_ITEM_MENU";
		$this->add_control(new drop_down_dw('COD_ITEM_MENU', $sql, 165));
		
		$sql = "SELECT	COD_USUARIO
						,NOM_USUARIO
				FROM USUARIO";
		$this->add_control(new drop_down_dw('COD_USUARIO_SOLICITA', $sql, 165));
		$this->add_control(new edit_text_upper('REFERENCIA',100, 100));
		$this->add_control(new edit_text_multiline('DESCRIPCION',100,4));
		$this->add_control(new edit_check_box('AUTORIZA', 'S', 'N'));
		$this->add_control(new drop_down_dw('COD_USUARIO_AUTORIZA', $sql, 165));
		$this->set_entrable('COD_USUARIO_AUTORIZA', false);
		
		// mandatorys
		$this->set_mandatory('COD_USUARIO_SOLICITA', 'Solicitante');
		$this->set_mandatory('REFERENCIA', 'Referencia');
		$this->set_mandatory('DESCRIPCION', 'Descripcion');
		$this->set_mandatory('COD_USUARIO_SOLICITA', 'Solicitante');
	}
	function new_mantencion() {
		$this->insert_row();
		$this->set_item(0, 'FECHA_MANTENCION_SW', $this->current_date());
		$this->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->set_item(0, 'ES_GARANTIA', 'N');
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		if ($cod_perfil==1)
			$this->set_item(0, 'DISPLAY_INTERNO', '');
		else
			$this->set_item(0, 'DISPLAY_INTERNO', 'none');
	}
}
class dw_solucion extends datawindow {
	const K_INGRESADO = 10;
	const K_TERMINADO = 80;
	const K_APROBADO = 90;
	const K_RECHAZADO = 100;	

	function dw_solucion() {
		$cod_usuario = session::get("COD_USUARIO");	// viene del login
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		
		$sql = "select COD_SOLUCION_SW 
						,COD_MANTENCION_SW
						,NRO_ITERACION
						,COD_DESARROLLADOR_SW
						,SOLUCION_INTERNA
						,SOLUCION_CLIENTE
						,MINUTOS
						,COD_ESTADO_SOLUCION_SW
						,RESPUESTA_CLIENTE
						,case $cod_perfil
							when 1 then ''
							else 'none'
						end DISPLAY_INTERNO
						,case COD_ESTADO_SOLUCION_SW
							when ".self::K_RECHAZADO." then ''
							else 'none'
						end DISPLAY_RESPUESTA_CLIENTE
						,convert(varchar, FECHA_INICIO, 103) FECHA_INICIO
						,convert(varchar, FECHA_TERMINO, 103) FECHA_TERMINO
						,'none' DISPLAY_MINUTOS
				from SOLUCION_SW
				where COD_MANTENCION_SW = {KEY1}
				order by NRO_ITERACION desc";
		parent::datawindow($sql, 'SOLUCION');
		
		$sql = "select COD_DESARROLLADOR_SW
						,NOM_DESARROLLADOR_SW
				from DESARROLLADOR_SW
				order by COD_DESARROLLADOR_SW";
		$this->add_control(new drop_down_dw('COD_DESARROLLADOR_SW', $sql, 150));
		
		$sql = "select COD_ESTADO_SOLUCION_SW
						,NOM_ESTADO_SOLUCION_SW
				from ESTADO_SOLUCION_SW
				order by COD_ESTADO_SOLUCION_SW";
		$this->add_control(new drop_down_dw('COD_ESTADO_SOLUCION_SW', $sql, 150, '', false));
		
		$this->add_control(new edit_text_multiline('SOLUCION_INTERNA', 90, 3));
		$this->add_control(new edit_text_multiline('SOLUCION_CLIENTE', 90, 3));
		$this->add_control(new edit_num('MINUTOS', 4, 4));
		$this->add_control(new edit_text_multiline('RESPUESTA_CLIENTE', 90, 3));
	}
	function new_mantencion() {
		$this->insert_row();
		$this->set_item(0, 'NRO_ITERACION', 1);
		$this->set_item(0, 'MINUTOS', 0);
		$this->set_item(0, 'COD_ESTADO_SOLUCION_SW', self::K_INGRESADO);
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		if ($cod_perfil==1) {
			$this->set_item(0, 'DISPLAY_INTERNO', '');
			$this->set_entrable_dw(true);
		}
		else {
			$this->set_item(0, 'DISPLAY_INTERNO', 'none');
			$this->set_entrable_dw(false);
		}
	}
	function load_mantencion($cod_mantencion_sw) {
		$this->retrieve($cod_mantencion_sw);
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		if ($cod_perfil==1) {
			$this->set_item(0, 'DISPLAY_MINUTOS', '');
			$this->set_entrable_dw(true);
		}
		else {
			$this->set_entrable('COD_DESARROLLADOR_SW', false);
			$this->set_entrable('SOLUCION_INTERNA', false);
			$this->set_entrable('SOLUCION_CLIENTE', false);
			$this->set_entrable('MINUTOS', false);
			$this->set_entrable('COD_ESTADO_SOLUCION_SW', false);
			$this->set_entrable('RESPUESTA_CLIENTE', false);
			
			$cod_estado_solucion_sw = $this->get_item(0, 'COD_ESTADO_SOLUCION_SW');
			if ($cod_estado_solucion_sw >= self::K_TERMINADO)
				$this->set_item(0, 'DISPLAY_MINUTOS', '');
			else
				$this->set_item(0, 'DISPLAY_MINUTOS', 'none');
			if ($cod_estado_solucion_sw==self::K_TERMINADO) {
				$this->set_entrable('COD_ESTADO_SOLUCION_SW', true);
				$this->set_entrable('RESPUESTA_CLIENTE', true);
				// solo puede aprobar o rechazar
				$sql = "select COD_ESTADO_SOLUCION_SW
								,NOM_ESTADO_SOLUCION_SW
						from ESTADO_SOLUCION_SW
						where COD_ESTADO_SOLUCION_SW in (".self::K_TERMINADO.",".self::K_APROBADO.",".self::K_RECHAZADO.")
						order by COD_ESTADO_SOLUCION_SW";
				$this->controls['COD_ESTADO_SOLUCION_SW']->set_sql($sql);
				$this->controls['COD_ESTADO_SOLUCION_SW']->retrieve();
				$this->controls['COD_ESTADO_SOLUCION_SW']->set_onChange("cambia_estado(this);");
			}
		}
	}
	function update($db, $cod_mantencion_sw) {
		$sp = 'spu_solucion_sw';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;
			
			$cod_solucion_sw = $this->get_item($i, 'COD_SOLUCION_SW');			
			$cod_desarrollador_sw = $this->get_item($i, 'COD_DESARROLLADOR_SW');			
			$cod_estado_solucion_sw = $this->get_item($i, 'COD_ESTADO_SOLUCION_SW');			
			$nro_iteracion = $this->get_item($i, 'NRO_ITERACION');			
			$solucion_cliente = $this->get_item($i, 'SOLUCION_CLIENTE');			
			$solucion_interna = $this->get_item($i, 'SOLUCION_INTERNA');			
			$minutos = $this->get_item($i, 'MINUTOS');		
			$respuesta_cliente = $this->get_item($i, 'RESPUESTA_CLIENTE');			
			
			$cod_solucion_sw = ($cod_solucion_sw=='') ? "null" : $cod_solucion_sw;
			$cod_desarrollador_sw = ($cod_desarrollador_sw=='') ? "null" : $cod_desarrollador_sw;
			$solucion_cliente = ($solucion_cliente=='') ? "null" : "'$solucion_cliente'";
			$solucion_interna = ($solucion_interna=='') ? "null" : "'$solucion_interna'";
			$respuesta_cliente = ($respuesta_cliente=='') ? "null" : "'$respuesta_cliente'";
			
			if ($statuts == K_ROW_NEW_MODIFIED || $cod_solucion_sw == 'null') {
				$operacion = 'INSERT';
			}
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
				
			$param = "'$operacion'
					,$cod_solucion_sw
					,$cod_mantencion_sw
					,$cod_desarrollador_sw
					,$cod_estado_solucion_sw
					,$nro_iteracion
					,$solucion_cliente
					,$solucion_interna
					,$minutos
					,$respuesta_cliente";			
			
			if (!$db->EXECUTE_SP($sp, $param)) 
				return false;
		}

		// Eliminaciones NO hay !

		return true;		
	}	
}
class wi_mantencion_sw extends w_input {
	const K_INGRESADA = 10;
	const K_TERMINADO = 80;
	const K_AUTORIZA_MANTENCION = '990205';
	
	function wi_mantencion_sw($cod_item_menu) {
		parent::w_input('mantencion_sw', $cod_item_menu);
		
		$this->dws['dw_mantencion'] =new dw_mantencion(); 
		$this->dws['dw_solucion'] =new dw_solucion(); 

		// Auditoria
		$this->add_auditoria_relacionada('SOLUCION_SW', 'COD_ESTADO_SOLUCION_SW');
	}
	function new_record() {
		$this->dws['dw_mantencion']->new_mantencion();
		$this->dws['dw_solucion']->new_mantencion();
		
		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MANTENCION, $this->cod_usuario);
		if ($priv=='E')
			$this->dws['dw_mantencion']->set_entrable('AUTORIZA', true);
		else
			$this->dws['dw_mantencion']->set_entrable('AUTORIZA', false);
	}
	
	function load_record() {
		$cod_mantencion_sw = $this->get_item_wo($this->current_record, 'COD_MANTENCION_SW');
		$this->dws['dw_mantencion']->retrieve($cod_mantencion_sw);

		// no modificable
		$this->b_save_visible = false;
		$this->b_no_save_visible = false;
		$this->b_modify_visible = false;
		$this->dws['dw_mantencion']->set_entrable_dw(false);// no es modificable la la solicitud
		
		// load solucion
		$this->dws['dw_solucion']->load_mantencion($cod_mantencion_sw);
		$cod_estado_solucion_sw = $this->dws['dw_solucion']->get_item(0, 'COD_ESTADO_SOLUCION_SW');
		if ($cod_estado_solucion_sw==self::K_INGRESADA) {
			$this->b_save_visible = true;
			$this->b_no_save_visible = true;
			$this->b_modify_visible = true;
		}
		else if ($cod_estado_solucion_sw==self::K_TERMINADO) {
			$this->b_save_visible = true;
			$this->b_no_save_visible = true;
			$this->b_modify_visible = true;
		}
		
		/*
		si esta autorizado la datawindow "dw_mantencion" no es accesible
		si NO esta autorizado la datawindow es accesible
		*/
		$autoriza = $this->dws['dw_mantencion']->get_item(0, 'AUTORIZA');
		if ($autoriza == 'N'){ // no es accesible el campo AUTORIZA
			$this->dws['dw_mantencion']->set_entrable_dw(true);
			$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MANTENCION, $this->cod_usuario);
			if ($priv=='E')
				$this->dws['dw_mantencion']->set_entrable('AUTORIZA', true);
			else
				$this->dws['dw_mantencion']->set_entrable('AUTORIZA', false);				
		}
			
		// para integrasystem siempre es modificable
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select COD_PERFIL
				from USUARIO
				where COD_USUARIO = $this->cod_usuario";
		$result = $db->build_results($sql);
		$cod_perfil = $result[0]['COD_PERFIL'];
		if ($cod_perfil == 1) {
			$this->b_save_visible = true;
			$this->b_no_save_visible = true;
			$this->b_modify_visible = true;
		}
	}
	function get_key(){
		return  $this->dws['dw_mantencion']->get_item(0, 'COD_MANTENCION_SW');
	}
	function save_record($db) {
		//$db->debug=1;
		
		$cod_mantencion_sw = $this->get_key();
		$fecha_mantencion_sw = $this->dws['dw_mantencion']->get_item(0, 'FECHA_MANTENCION_SW');
		$cod_usuario = $this->dws['dw_mantencion']->get_item(0, 'COD_USUARIO');
		$cod_usuario_solicita = $this->dws['dw_mantencion']->get_item(0, 'COD_USUARIO_SOLICITA');
		$referencia = $this->dws['dw_mantencion']->get_item(0, 'REFERENCIA');
		$descripcion = $this->dws['dw_mantencion']->get_item(0, 'DESCRIPCION');
		$es_garantia = $this->dws['dw_mantencion']->get_item(0, 'ES_GARANTIA');
		$cod_item_menu = $this->dws['dw_mantencion']->get_item(0, 'COD_ITEM_MENU');
		$autoriza = $this->dws['dw_mantencion']->get_item(0, 'AUTORIZA');
		$cod_usuario_autoriza = $this->dws['dw_mantencion']->get_item(0, 'COD_USUARIO_AUTORIZA');
		
		if ($autoriza == 'S' && $cod_usuario_autoriza == ''){ //se autoriza
			$fecha_autoriza = $this->str2date($this->current_date());
			$cod_usuario_autoriza = $this->cod_usuario;
		}else{
			$fecha_autoriza = "null";
			$cod_usuario_autoriza = "null";
		}
		
		$cod_mantencion_sw = ($cod_mantencion_sw=='') ? "null" : $cod_mantencion_sw;
		$fecha_mantencion_sw = $this->str2date($fecha_mantencion_sw);
		$cod_item_menu = ($cod_item_menu=='') ? "null" : "'$cod_item_menu'";

		$sp = 'spu_mantencion_sw';

	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    $param = "'$operacion'
	    		,$cod_mantencion_sw
	    		,$fecha_mantencion_sw
	    		,$cod_usuario
	    		,$cod_usuario_solicita
	    		,'$referencia'
	    		,'$descripcion'
	    		,'$es_garantia'
	    		,$cod_item_menu
	    		,'$autoriza'
	    		,$cod_usuario_autoriza
	    		,$fecha_autoriza";

	    if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_mantencion_sw = $db->GET_IDENTITY();
				$this->dws['dw_mantencion']->set_item(0, 'COD_MANTENCION_SW', $cod_mantencion_sw);
			}
			
			if (!$this->dws['dw_solucion']->update($db, $cod_mantencion_sw))
				return false;
			
			return true;
		}
		return false;							
	}
}
?>