<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
/*
Clase : WI_USUARIO
*/
class wi_usuario extends w_input {
	function wi_usuario($cod_item_menu) {
		parent::w_input('usuario', $cod_item_menu);

		$sql = "select  U.COD_USUARIO, 
						U.NOM_USUARIO,
						U.LOGIN,
						'*--SinCambio--*' PASSWORD,	
						U.COD_PERFIL,
						P.NOM_PERFIL,																		
						U.AUTORIZA_INGRESO,
						U.TELEFONO,
						U.CELULAR,
						U.MAIL,
						U.ES_VENDEDOR,
						U.PORC_PARTICIPACION,
						U.COD_EMPRESA,						
						E.ALIAS,
						E.RUT,
						E.DIG_VERIF,
						E.NOM_EMPRESA,
						E.GIRO,
						U.PORC_MODIFICA_PRECIO_OC,
						U.PORC_MODIFICA_PRECIO,
						U.PORC_DESCUENTO_PERMITIDO,
						U.ACCESO_LIBRE_NV,
						U.INI_USUARIO,
						U.VENDEDOR_VISIBLE_FILTRO		
				from 	USUARIO U left outer join EMPRESA E on U.COD_EMPRESA = E.COD_EMPRESA, PERFIL P
				where 	U.COD_USUARIO = {KEY1} AND
						P.COD_PERFIL = U.COD_PERFIL";
						
						
		$sql_perfil = "select 	  COD_PERFIL,
								  NOM_PERFIL
						from	  PERFIL
						order by  COD_PERFIL";
		
		$this->dws['dw_usuario'] = new datawindow($sql);
		
		$this->dws['dw_usuario'] = new dw_help_empresa_usuario($sql);
		
		$this->dws['dw_usuario']->add_control(new edit_text_upper('NOM_USUARIO', 80, 100));
		$this->dws['dw_usuario']->add_control(new edit_text_upper('INI_USUARIO',5, 3));
		$this->dws['dw_usuario']->add_control(new edit_text_upper('LOGIN', 80, 100));	
		$this->dws['dw_usuario']->add_control(new edit_password('PASSWORD', 80, 100));		
		$this->dws['dw_usuario']->add_control(new drop_down_dw('COD_PERFIL',$sql_perfil,180));
		$this->dws['dw_usuario']->add_control(new edit_text('TELEFONO', 14, 100));
		$this->dws['dw_usuario']->add_control(new edit_text('CELULAR', 14, 100));
		$this->dws['dw_usuario']->add_control(new edit_mail('MAIL', 80, 100));	
		$this->dws['dw_usuario']->add_control(new edit_check_box('AUTORIZA_INGRESO','S','N',''));			
		$this->dws['dw_usuario']->add_control(new edit_check_box('ES_VENDEDOR','S','N',''));
		$this->dws['dw_usuario']->add_control(new edit_porcentaje('PORC_PARTICIPACION',5,5, 2));
		$this->dws['dw_usuario']->add_control(new edit_porcentaje('PORC_MODIFICA_PRECIO',5,5));
		$this->dws['dw_usuario']->add_control(new edit_porcentaje('PORC_DESCUENTO_PERMITIDO',5,5));
		$this->dws['dw_usuario']->add_control(new edit_porcentaje('PORC_MODIFICA_PRECIO_OC',5,5));
		$this->dws['dw_usuario']->add_control(new edit_check_box('ACCESO_LIBRE_NV','S','N',''));
		$sql="SELECT 1 COD_VENDEDOR_VISIBLE_FILTRO,
							   'VIGENTE' NOM_VENDEDOR_VISIBLE_FILTRO
						UNION
						SELECT 2 COD_VENDEDOR_VISIBLE_FILTRO,
							   'NO VIGENTE' NOM_VENDEDOR_VISIBLE_FILTRO
						UNION
						SELECT 3 COD_VENDEDOR_VISIBLE_FILTRO,
							   'NUNCA VISIBLE' NOM_VENDEDOR_VISIBLE_FILTRO";
		$this->dws['dw_usuario']->add_control(new drop_down_dw('VENDEDOR_VISIBLE_FILTRO',$sql,180));
		
		//auditoria
		$this->add_auditoria('NOM_USUARIO');
		$this->add_auditoria('INI_USUARIO');
		$this->add_auditoria('LOGIN');
		$this->add_auditoria('PASSWORD');
		$this->add_auditoria('TELEFONO');
		$this->add_auditoria('CELULAR');
		$this->add_auditoria('MAIL');
		$this->add_auditoria('VENDEDOR_VISIBLE_FILTRO');
		$this->add_auditoria('COD_PERFIL');
		$this->add_auditoria('AUTORIZA_INGRESO');
		$this->add_auditoria('PORC_PARTICIPACION');
		$this->add_auditoria('PORC_MODIFICA_PRECIO');
		$this->add_auditoria('PORC_DESCUENTO_PERMITIDO');
		$this->add_auditoria('PORC_MODIFICA_PRECIO_OC');
		$this->add_auditoria('ACCESO_LIBRE_NV');
		$this->add_auditoria('ES_VENDEDOR');
		
		// asigna los mandatorys		
		$this->dws['dw_usuario']->set_mandatory('NOM_USUARIO', 'Nombre de Usuario');
		$this->dws['dw_usuario']->set_mandatory('LOGIN', 'Login');
		$this->dws['dw_usuario']->set_mandatory('PASSWORD', 'Password');
		$this->dws['dw_usuario']->set_mandatory('COD_PERFIL', 'Perfil');
		$this->dws['dw_usuario']->set_mandatory('TELEFONO', 'Teléfono');
		$this->dws['dw_usuario']->set_mandatory('MAIL', 'Mail');
		$this->dws['dw_usuario']->set_mandatory('PORC_PARTICIPACION', 'Porc. de Participación');
		$this->dws['dw_usuario']->set_mandatory('INI_USUARIO', 'Siglas de Usuario');
		$this->dws['dw_usuario']->set_mandatory('VENDEDOR_VISIBLE_FILTRO', 'Visible en Listados');		
	}
	
	function new_record() {
		$this->dws['dw_usuario']->insert_row();		
	}
	function load_record() {	
		$cod_usuario = $this->get_item_wo($this->current_record, 'COD_USUARIO');	
		$this->dws['dw_usuario']->retrieve($cod_usuario);
		
		$COD_EMPRESA = $this->dws['dw_usuario']->get_item(0, 'COD_EMPRESA');
	}
	function get_key() {
		return $this->dws['dw_usuario']->get_item(0, 'COD_USUARIO');		
	}
	
	function habilitar(&$temp, $habilita) { 
		//*************  dejar solo el perfil 1, usar consulta aBD
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "SELECT COD_PERFIL from USUARIO where COD_USUARIO =".$this->cod_usuario;
        $result = $db->build_results($sql);
        $cod_perfil = $result[0]['COD_PERFIL'];
         
		if ($cod_perfil == 1) //perfil administrados siempre tendrá privilegios de escritura
			$this->habilita_boton($temp, 'modify', (true));
		else
			$this->habilita_boton($temp, 'modify', (false));
	}
		
	function save_record($db) {
		$cod_usuario		 		= $this->get_key();
		$nom_usuario				= $this->dws['dw_usuario']->get_item(0, 'NOM_USUARIO');
		$login						= $this->dws['dw_usuario']->get_item(0, 'LOGIN');
		$password					= $this->f_encriptar($this->dws['dw_usuario']->get_item(0, 'PASSWORD'));
		$perfil			 			= $this->dws['dw_usuario']->get_item(0, 'COD_PERFIL');
		$aut_ingreso				= $this->dws['dw_usuario']->get_item(0, 'AUTORIZA_INGRESO');
		$telefono					= $this->dws['dw_usuario']->get_item(0, 'TELEFONO');
		$celular					= $this->dws['dw_usuario']->get_item(0, 'CELULAR');
		$mail						= $this->dws['dw_usuario']->get_item(0, 'MAIL');		
		$vendedor					= $this->dws['dw_usuario']->get_item(0, 'ES_VENDEDOR');
		$porc_part					= $this->dws['dw_usuario']->get_item(0, 'PORC_PARTICIPACION');
		$por_mod_precio				= $this->dws['dw_usuario']->get_item(0, 'PORC_MODIFICA_PRECIO');
		$por_descuento				= $this->dws['dw_usuario']->get_item(0, 'PORC_DESCUENTO_PERMITIDO');
		$por_mod_precio_oc			= $this->dws['dw_usuario']->get_item(0, 'PORC_MODIFICA_PRECIO_OC');
		$acceso_libre_nv			= $this->dws['dw_usuario']->get_item(0, 'ACCESO_LIBRE_NV');
		$ini_usuario				= $this->dws['dw_usuario']->get_item(0, 'INI_USUARIO');
		$vendedor_visible_filtro	= $this->dws['dw_usuario']->get_item(0, 'VENDEDOR_VISIBLE_FILTRO');
		
		//EMPRESA
		$cod_empresa		= $this->dws['dw_usuario']->get_item(0, 'COD_EMPRESA');
					
		$mail 				= ($mail=='') ? "null" : "'$mail'";	
		$por_mod_precio 	= ($por_mod_precio=='') ? "null" : $por_mod_precio;	
		$por_mod_precio_oc 	= ($por_mod_precio_oc=='') ? "null" : $por_mod_precio_oc;	
		$telefono 			= ($telefono=='') ? "null" : $telefono;		
		$celular 			= ($celular=='') ? "null" : "'$celular'";			
		$cod_usuario 		= ($cod_usuario=='') ? "null" : $cod_usuario;
		$cod_empresa 		= ($cod_empresa=='') ? "null" : $cod_empresa;
    
		$sp = 'spu_usuario';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    	
	    $param	= "'$operacion'
	    			,$cod_usuario
	    			,'$nom_usuario'
					,'$login'
					,'$password'	
					,$perfil
					,'$aut_ingreso'	
	 				,$mail
					,'$vendedor'	
					,$porc_part	
					,$por_mod_precio
					,'$telefono'
					,$celular 
					,$cod_empresa
					,$por_mod_precio_oc
					,'$acceso_libre_nv'
					,'$ini_usuario'
					,$por_descuento
					,$vendedor_visible_filtro";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_usuario = $db->GET_IDENTITY();
				$this->dws['dw_usuario']->set_item(0, 'COD_USUARIO', $cod_usuario);
			}
			return true;

		}
		return false;	
	}
}

class dw_help_empresa_usuario extends dw_help_empresa {
	function dw_help_empresa_usuario($sql) {
		parent::dw_help_empresa($sql, '', false, false, 'T');
	}
	function add_controls_empresa_help($tipo_empresa = 'T') {
		/* Agrega los constrols standar para manejar la selección de empresa con help					
			 Los anchos y maximos de cada campo quedan fijos, la idea es que sean iguales en todos los formularios
			 si se desean tamaños distintos se debe reiimplementar esta función
			 
			 $tipo_empresa: Es un string con alguna combinacion de 'C', 'P' y 'T'.  Para indicar el tipo de empresas que se deben desplegar
			 								'C' clientes
			 								'P' proveedores
			 								'T' Trabajador o personal
		*/
		$java_script = "help_empresa(this, '".$tipo_empresa."');";
		$this->add_control($control = new edit_num('COD_EMPRESA', 10, 10));
		$control->set_onChange($java_script);
		$control->con_separador_miles = false;

		$this->add_control($control = new edit_num('RUT', 10, 10));
		$control->set_onChange($java_script);

		$this->add_control(new static_text('DIG_VERIF'));
		
		$this->add_control($control = new edit_text_upper('ALIAS', 37, 100));
		$control->set_onChange($java_script);

		$this->add_control($control = new edit_text_upper('NOM_EMPRESA', 121, 100));
		$control->set_onChange($java_script);

		$this->add_control(new static_text('GIRO'));
	}
}
?>