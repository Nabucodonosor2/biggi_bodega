<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../pago_faprov/class_wi_pago_faprov.php");

class wi_pago_directorio extends wi_pago_faprov{
	const K_PARAM_DIRECTORIO = 31;
	
	function wi_pago_directorio ($cod_item_menu){
	
		parent::w_input('pago_directorio', $cod_item_menu);
		
		
		$this->dws['dw_pago_faprov'] = new dw_pago_faprov();
		
		// DATAWINDOWS NCPROV_FAPROV
		$this->dws['dw_pago_faprov_faprov'] = new dw_pago_faprov_faprov();

		//auditoria Solicitado por IS.
		$this->add_auditoria('COD_TIPO_PAGO_FAPROV');
		$this->add_auditoria('COD_CUENTA_CORRIENTE');
		$this->add_auditoria('NRO_DOCUMENTO');
		$this->add_auditoria('PAGUESE_A');
		$this->add_auditoria('FECHA_DOCUMENTO');		
		$this->add_auditoria('COD_ESTADO_PAGO_FAPROV');		

		//al momento de crear un nuevo registro se iniciara en rut de empresa proveedor
		$this->set_first_focus('RUT');

		$this->dws['dw_pago_faprov']->set_entrable('COD_EMPRESA', false);
		$this->dws['dw_pago_faprov']->set_entrable('RUT', false);
		$this->dws['dw_pago_faprov']->set_entrable('ALIAS', false);
		$this->dws['dw_pago_faprov']->set_entrable('NOM_EMPRESA', false);
	}
	
	function new_record() {
		parent::new_record();
				
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
   		//obtiene el codigo de usuario asignado como directorio		
   		$sql_cod_usuario_dir = "SELECT VALOR FROM PARAMETRO WHERE COD_PARAMETRO = ".self::K_PARAM_DIRECTORIO;
   	 	$result = $db->build_results($sql_cod_usuario_dir);			
		$cod_usuario_dir = $result[0]['VALOR'];
		
		//obtiene el codigo de la empresa asociada al usuario directorio
		$sql_cod_empresa = "SELECT E.COD_EMPRESA, NOM_EMPRESA, ALIAS, RUT, DIG_VERIF 
							FROM USUARIO U, EMPRESA E 
							WHERE U.COD_USUARIO = $cod_usuario_dir
								AND U.COD_EMPRESA = E.COD_EMPRESA";

		$result = $db->build_results($sql_cod_empresa);			
		$cod_empresa = $result[0]['COD_EMPRESA'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$alias = $result[0]['ALIAS'];
		$rut = $result[0]['RUT'];
		$dig_verif = $result[0]['DIG_VERIF'];
		
		$this->dws['dw_pago_faprov']->set_item(0, 'COD_EMPRESA', $cod_empresa);
		$this->dws['dw_pago_faprov']->set_item(0, 'NOM_EMPRESA', $nom_empresa);
		$this->dws['dw_pago_faprov']->set_item(0, 'ALIAS', $alias);
		$this->dws['dw_pago_faprov']->set_item(0, 'RUT', $rut);
		$this->dws['dw_pago_faprov']->set_item(0, 'DIG_VERIF', $dig_verif);
		$this->dws['dw_pago_faprov']->set_item(0, 'PAGUESE_A', 'COMERCIAL BIGGI CHILE S.A.');

		$sql_original = $this->dws['dw_pago_faprov_faprov']->get_sql();		
		$sql = "SELECT 'N' SELECCION
				,0 COD_PAGO_FAPROV_FAPROV
				,0 COD_PAGO_FAPROV
				,F.COD_FAPROV
				,F.NRO_FAPROV
				,convert(varchar(20), F.FECHA_FAPROV, 103) FECHA_FAPROV
				,F.TOTAL_CON_IVA TOTAL_CON_IVA_FA
				,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV
				,dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) SALDO_SIN_PAGO_FAPROV_H
				,0 MONTO_ASIGNADO
				,0 MONTO_ASIGNADO_C 
				,dbo.f_pago_faprov_get_pago_ant(F.COD_FAPROV) PAGO_ANTERIOR
		FROM 	FAPROV F
		WHERE 	F.COD_EMPRESA = $cod_empresa and 
				dbo.f_pago_faprov_get_por_asignar(F.COD_FAPROV) > 0
				order by F.NRO_FAPROV desc";

		$this->dws['dw_pago_faprov_faprov']->set_sql($sql);
		$row = $this->dws['dw_pago_faprov_faprov']->retrieve();
		$this->dws['dw_pago_faprov_faprov']->set_sql($sql_original);
				
	}
	function make_sql_auditoria() {
		// Se debe cambiar el codigo para que apunte a la tabla real PAGO_FAPROV y no a PAGO DIRECTORIO
		$nom_tabla_original = $this->nom_tabla;
		$this->nom_tabla = 'pago_faprov';
		$sql = parent::make_sql_auditoria();
		$this->nom_tabla = $nom_tabla_original;
		return $sql;
	}
	function make_sql_auditoria_relacionada($tabla) {
		// Se debe cambiar el codigo para que apunte a la tabla real PAGO_FAPROV y no a PAGO DIRECTORIO
		$nom_tabla_original = $this->nom_tabla;
		$this->nom_tabla = 'pago_faprov';
		$sql = parent::make_sql_auditoria_relacionada($tabla);
		$this->nom_tabla = $nom_tabla_original;
		return $sql;
	}
}
?>