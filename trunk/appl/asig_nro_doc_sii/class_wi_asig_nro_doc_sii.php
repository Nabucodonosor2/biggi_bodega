<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

class wi_asig_nro_doc_sii extends w_input {
	function wi_asig_nro_doc_sii($cod_item_menu) {
		parent::w_input('asig_nro_doc_sii', $cod_item_menu);

		$sql = "select COD_ASIG_NRO_DOC_SII
					  ,convert(nvarchar, FECHA_ASIG, 103) FECHA_ASIG 	
				      ,AN.COD_USUARIO
				      ,U.NOM_USUARIO
				      ,COD_TIPO_DOC_SII
				      ,NRO_INICIO
				      ,NRO_TERMINO
				      ,COD_USUARIO_RECEPTOR
				      ,convert(nvarchar, FECHA_DEVOL, 103) FECHA_DEVOL
				      ,NRO_INICIO_DEVOL
				      ,NRO_TERMINO_DEVOL
				      ,dbo.f_asig_cant_disponible(COD_ASIG_NRO_DOC_SII) CANT_DISPONIBLE 
				      ,'N' DEVOLUCION_H 
				      ,'' TABLE_DISPLAY_DEVOLUCION	
				from ASIG_NRO_DOC_SII AN, USUARIO U
				where COD_ASIG_NRO_DOC_SII = {KEY1} and
					  AN.COD_USUARIO = U.COD_USUARIO";
		
		$this->dws['dw_asig_nro_doc_sii'] = new datawindow($sql);

		// asigna los formatos		
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_nro_doc('COD_ASIG_NRO_DOC_SII','ASIG_NRO_DOC_SII'));
		
		//factura normal, factura Excenta y nota de crédito
		$sql = "select COD_TIPO_DOC_SII
				      ,NOM_TIPO_DOC_SII
				from TIPO_DOC_SII
				where	COD_TIPO_DOC_SII in (1, 3, 5)
				order by ORDEN";
		
		$this->dws['dw_asig_nro_doc_sii']->add_control(new drop_down_dw('COD_TIPO_DOC_SII', $sql, 120));

		$sql = "select COD_USUARIO,
						NOM_USUARIO
				from USUARIO 
				where AUTORIZA_INGRESO = 'S'
				order by NOM_USUARIO";
		$this->dws['dw_asig_nro_doc_sii']->add_control(new drop_down_dw('COD_USUARIO_RECEPTOR', $sql, 120));
		
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_num('NRO_INICIO', 16, 16, 0, true, false, false));
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_num('NRO_TERMINO', 16, 16, 0, true, false, false));
		
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_text('FECHA_DEVOL', 16, 16));
		$this->dws['dw_asig_nro_doc_sii']->set_entrable('FECHA_DEVOL', false);
		
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_nro_doc('NRO_INICIO_DEVOL', 'ASIG_NRO_DOC_SII'));
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_nro_doc('NRO_TERMINO_DEVOL', 'ASIG_NRO_DOC_SII'));
		//CREA HIDDEN PARA VALIDACION DE DEVOLUCION - PC
		$this->dws['dw_asig_nro_doc_sii']->add_control(new edit_text('DEVOLUCION_H',10, 10, 'hidden'));
		
			
		// asigna los mandatorys
		$this->dws['dw_asig_nro_doc_sii']->set_mandatory('COD_TIPO_DOC_SII', 'Tipo de Documento');
		$this->dws['dw_asig_nro_doc_sii']->set_mandatory('NRO_INICIO', 'número Inicial');
		$this->dws['dw_asig_nro_doc_sii']->set_mandatory('NRO_TERMINO', 'número Final');
		$this->dws['dw_asig_nro_doc_sii']->set_mandatory('COD_USUARIO_RECEPTOR', 'Usuario Responsable');
		
	}
	
	function get_orden_min($nom_tabla) {		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "SELECT TOP 1 COD_$nom_tabla AS CODIGO FROM $nom_tabla ORDER BY ORDEN";
		$result = $db->build_results($sql);
		
		$orden = $result[0]['CODIGO'];
		return $orden;		  
	}
	
	function new_record() {
		$this->dws['dw_asig_nro_doc_sii']->insert_row();
		
		$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'FECHA_ASIG', $this->current_date());
		$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		
		$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'COD_TIPO_DOC_SII', $this->get_orden_min('TIPO_DOC_SII'));
		//PARA DESPLEGAR ON O LA TABLA DE DEVOLUCION - PC
		$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'TABLE_DISPLAY_DEVOLUCION', 'none');
			
	}
	function load_record() {
		$COD_ASIG_NRO_DOC_SII = $this->get_item_wo($this->current_record, 'COD_ASIG_NRO_DOC_SII');
		$this->dws['dw_asig_nro_doc_sii']->retrieve($COD_ASIG_NRO_DOC_SII);
		$this->dws['dw_asig_nro_doc_sii']->set_entrable('NRO_INICIO', false);
		$this->dws['dw_asig_nro_doc_sii']->set_entrable('NRO_TERMINO', false);
		$this->dws['dw_asig_nro_doc_sii']->set_entrable('COD_TIPO_DOC_SII', false);
		$this->dws['dw_asig_nro_doc_sii']->set_entrable('COD_USUARIO_RECEPTOR', false);
	}
	function get_key() {
		return $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_ASIG_NRO_DOC_SII');
	}	
	function validate_record() {
		
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		//validar que usuario no tiene más de ese tipo de documento, asignados
		$usuario = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_USUARIO_RECEPTOR');
		$COD_TIPO_DOC_SII = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_TIPO_DOC_SII');
		$DEVOLUCION = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'DEVOLUCION_H');
		//return $DEVOLUCION;
		
		IF ($DEVOLUCION!='S')
		{
			//return 'entro validar rangos y usuario';
			$sql_usu="select COD_ASIG_NRO_DOC_SII
					from asig_nro_doc_sii
					where cod_usuario_receptor=".$usuario." and cod_tipo_doc_sii=".$COD_TIPO_DOC_SII." and 
					dbo.f_asig_cant_disponible(COD_ASIG_NRO_DOC_SII) > 0";
			$result_usu = $db->build_results($sql_usu);
			$cant_usu = count($result_usu);
			//$cant_usu=$cant_usu +1;
			//return $cant_usu;
			If ($cant_usu>0) 
			return 'Este usuario ya tiene documentos Asignados, Pendientes por Devolver o Imprimir';
				
		}
		else
			return '';	
	}	
	
	
	function save_record($db) {
		$COD_ASIG_NRO_DOC_SII = $this->get_key();
		$FECHA_ASIG = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'FECHA_ASIG');
		$COD_USUARIO = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_USUARIO');
		$COD_TIPO_DOC_SII = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_TIPO_DOC_SII');
		$NRO_INICIO = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'NRO_INICIO');
		$NRO_TERMINO = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'NRO_TERMINO');
		$COD_USUARIO_RECEPTOR = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'COD_USUARIO_RECEPTOR');
		$NRO_INICIO_DEVOL = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'NRO_INICIO_DEVOL');
		$NRO_TERMINO_DEVOL = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'NRO_TERMINO_DEVOL');		
		$FECHA_DEVOL = $this->dws['dw_asig_nro_doc_sii']->get_item(0, 'FECHA_DEVOL');
		if (($FECHA_DEVOL == '') && ($NRO_INICIO_DEVOL != '')){ // se hizo recien la devolucion
			$FECHA_DEVOL = $this->current_date();
		}
		$COD_ASIG_NRO_DOC_SII = ($COD_ASIG_NRO_DOC_SII=='') ? "null" : $COD_ASIG_NRO_DOC_SII;		
		$FECHA_DEVOL = ($FECHA_DEVOL=='') ? "null" : 'get_date';	
		$NRO_INICIO_DEVOL = ($NRO_INICIO_DEVOL=='') ? "null" : $NRO_INICIO_DEVOL;	
		$NRO_TERMINO_DEVOL = ($NRO_TERMINO_DEVOL=='') ? "null" : $NRO_TERMINO_DEVOL;	
		$sp = 'spu_asig_nro_doc_sii';

	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    $param = "'$operacion', $COD_ASIG_NRO_DOC_SII, '$FECHA_ASIG', $COD_USUARIO, $COD_TIPO_DOC_SII, $NRO_INICIO, $NRO_TERMINO, $COD_USUARIO_RECEPTOR, $FECHA_DEVOL, $NRO_INICIO_DEVOL, $NRO_TERMINO_DEVOL";
		
		if ($db->EXECUTE_SP($sp, $param)){
				if ($this->is_new_record()) {
					$COD_ASIG_NRO_DOC_SII = $db->GET_IDENTITY();
					$this->dws['dw_asig_nro_doc_sii']->set_item(0, 'COD_ASIG_NRO_DOC_SII', $COD_ASIG_NRO_DOC_SII);
				}				
				return true;
		}
		
		return false;							
	}
}
?>