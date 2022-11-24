<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");


class dw_lock_table extends datawindow {
	function dw_lock_table() 	{		
		if (K_TIPO_BD=='mssql')
			$sql = "SELECT	NOM_MODULO + '/' + convert(varchar, NRO_MODULO) ELIMINAR,
								NRO_MODULO,
								UPPER(NOM_MODULO) NOM_MODULO,
								LK.COD_USUARIO,
								U.NOM_USUARIO,
								CONVERT(varchar, FECHA_REGISTRO, 103) + ' ' + CONVERT(varchar, FECHA_REGISTRO, 108) FECHA_REGISTRO
						FROM	LOCK_TABLE LK, USUARIO U
						WHERE	LK.COD_USUARIO = U.COD_USUARIO
							and NOM_MODULO <> 'PARAMETRO'";
		elseif (K_TIPO_BD=='mysql')
			$sql = "SELECT	CONCAT(NOM_MODULO, '/', NRO_MODULO) ELIMINAR,
							NRO_MODULO,
							UPPER(NOM_MODULO) NOM_MODULO,
							LK.COD_USUARIO,
							U.NOM_USUARIO,
							DATE_FORMAT(FECHA_REGISTRO, '%d/%m/%Y %T') FECHA_REGISTRO
					FROM	LOCK_TABLE LK, USUARIO U
					WHERE	LK.COD_USUARIO = U.COD_USUARIO
						and NOM_MODULO <> 'PARAMETRO";
		parent::datawindow($sql, 'LOCK_TABLE', false, true);
	}
	function update($db)	{
		$sp = 'spd_lock_table';		
		for ($i = 0; $i < $this->row_count('delete'); $i++) {
			$statuts = $this->get_status_row($i, 'delete');			
			if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
				continue;
			
			$dato = $this->get_item($i, 'ELIMINAR', 'delete');
			$largo = strlen($dato);
			$pos = strpos($dato, '/');
			$nom_modulo = substr($dato, 0, $pos);
			$nro_modulo = substr($dato, $pos + 1, $largo);
			$param = "'" . $nro_modulo . "', '" . $nom_modulo . "',".$this->get_item($i, 'COD_USUARIO', 'delete');
			if (!$db->EXECUTE_SP($sp, $param))			
				return false;				
		}
		return true;
	}
	
}

class dw_parametro_porc extends datawindow {
	function dw_parametro_porc($tipo_parametro) {
		$sql = "SELECT 		  	PORC_PARAMETRO,
								convert(varchar(20),FECHA_INICIO_VIGENCIA, 103) FECHA_INICIO_VIGENCIA 
				FROM 			PARAMETRO_PORC 
				WHERE 			TIPO_PARAMETRO = '".$tipo_parametro."' 
				ORDER BY 		FECHA_INICIO_VIGENCIA DESC";
		parent::datawindow($sql, 'PORCENTAJE_'.$tipo_parametro, true);
	}
}



/*******************************************************************************************************************************/
 
class wi_parametro_adm extends w_input {
	const K_PARAM_IVA = 1;
	const K_PARAM_RET_BOLETA_H = 2;
	const K_PARAM_SISTEMA = 3;
	const K_PARAM_VERSION = 4;
	const K_PARAM_DOLAR_COMERCIAL = 5;
	const K_PARAM_NOMBRE_EMPRESA = 6;
	const K_PARAM_VALIDEZ_OFERTA_COT = 7;
	const K_PARAM_ENTREGA_COTIZACION = 8;
	const K_PARAM_GARANTIA_COTIZACION = 9;
	const K_PARAM_DIRECCION_EMPRESA = 10;
	const K_PARAM_FONO_EMPRESA = 11;
	const K_PARAM_FAX_EMPRESA = 12;
	const K_PARAM_MAIL_EMPRESA = 13;
	const K_PARAM_CIUDAD_EMPRESA = 14;
	const K_PARAM_PAIS_EMPRESA = 15;
	const K_PARAM_GERENTE_VENTA = 16;
	const K_PARAM_SMTP = 17;
	const K_PARAM_USER_AUTENTICATION = 18;
	const K_PARAM_PASS_AUTENTICATION = 19;
	const K_PARAM_RUT_EMPRESA= 20;
	const K_PARAM_GIRO_EMPRESA = 21;	
	const K_PARAM_FACTOR_PRE_INT_X_DEFINIR = 22;
	const K_PARAM_FACTOR_PRE_PUB_X_DEFINIR = 23;
	const K_PARAM_PLAZO_PARA_CIERRE_NV = 24;
	const K_PARAM_WEB_EMPRESA = 25;
	const K_PARAM_PORC_DSCTO_MAX =26;
	const K_PARAM_MAX_CANT_GD =28;
	const K_PARAM_MAX_CANT_FA =29;
	const K_PARAM_EMP_GV =30;
	const K_PARAM_EMP_AA =31;
	const K_PARAM_FACTOR_PRE_INT_BAJO =34;
	const K_PARAM_FACTOR_PRE_INT_ALTO =35;
	const K_PARAM_FACTOR_PRE_PUB_BAJO =36;
	const K_PARAM_FACTOR_PRE_PUB_ALTO =37;
	const K_PARAM_ACCESO_LIBRE_A_COT =38;
	const K_PARAM_ACCESO_LIBRE_A_NV =39;
	const K_PARAM_MAX_CANT_NC =40;
	const K_PARAM_EMP_ADM =41;
	const K_PARAM_DIREC_FTP =42;
	const K_PARAM_USUARIO_FTP =43;
	const K_PARAM_PASSWORD_FTP =44;
			
	function wi_parametro_adm() 	{
		$this->tiene_wo = false;
		parent::w_input('parametro_adm', $_REQUEST['cod_item_menu']);
						

		$sql = "SELECT		dbo.f_get_parametro(".self::K_PARAM_IVA.") IVA ,
							dbo.f_get_parametro(".self::K_PARAM_RET_BOLETA_H.") RETENCION_B_H,
							dbo.f_get_parametro(".self::K_PARAM_SISTEMA.") SISTEMA,
							dbo.f_get_parametro(".self::K_PARAM_VERSION.") VERSION,
							dbo.f_get_parametro(".self::K_PARAM_DOLAR_COMERCIAL.") DOLAR_COMERCIAL,
							dbo.f_get_parametro(".self::K_PARAM_PORC_DSCTO_MAX.") PORC_MAX_DSCTO,
							dbo.f_get_parametro(".self::K_PARAM_NOMBRE_EMPRESA.") NOMBRE_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_VALIDEZ_OFERTA_COT.") VALIDEZ_OFERTA_COTIZACION,	
							dbo.f_get_parametro(".self::K_PARAM_ENTREGA_COTIZACION.") ENTREGA_COTIZACION,														
							dbo.f_get_parametro(".self::K_PARAM_GARANTIA_COTIZACION.") GARANTIA_COTIZACION,
							dbo.f_get_parametro(".self::K_PARAM_DIRECCION_EMPRESA.") DIRECCION_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_FONO_EMPRESA.") FONO_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_FAX_EMPRESA.") FAX_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_MAIL_EMPRESA.") MAIL_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_CIUDAD_EMPRESA.") CIUDAD_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_PAIS_EMPRESA.") PAIS_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_GERENTE_VENTA.") GERENTE_VENTA,
							dbo.f_get_parametro(".self::K_PARAM_SMTP.") SMTP,
							dbo.f_get_parametro(".self::K_PARAM_USER_AUTENTICATION.") USER_AUTENTICATION,
							dbo.f_get_parametro(".self::K_PARAM_PASS_AUTENTICATION.") PASSWORD_AUTENTICATION,
							dbo.f_get_parametro(".self::K_PARAM_RUT_EMPRESA.") RUT_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_GIRO_EMPRESA.") GIRO_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_INT_X_DEFINIR.") FACTOR_PRE_INT_X_DEFINIR,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_PUB_X_DEFINIR.") FACTOR_PRE_PUB_X_DEFINIR,
							dbo.f_get_parametro(".self::K_PARAM_PLAZO_PARA_CIERRE_NV.") PLAZO_PARA_CIERRE_NV,
							dbo.f_get_parametro(".self::K_PARAM_WEB_EMPRESA.") WEB_EMPRESA,
							dbo.f_get_parametro(".self::K_PARAM_MAX_CANT_GD.") MAXIMA_CANTIDAD_GD,							
							dbo.f_get_parametro(".self::K_PARAM_MAX_CANT_FA.") MAXIMA_CANTIDAD_FA,
							dbo.f_get_parametro(".self::K_PARAM_MAX_CANT_NC.") MAXIMA_CANTIDAD_NC,
							dbo.f_get_parametro(".self::K_PARAM_EMP_GV.") COD_EMPRESA_GV,
							dbo.f_get_parametro(".self::K_PARAM_EMP_AA.") COD_EMPRESA_AA,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_INT_BAJO.") FACTOR_PRE_INT_BAJO,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_INT_ALTO.") FACTOR_PRE_INT_ALTO,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_PUB_BAJO.") FACTOR_PRE_PUB_BAJO,
							dbo.f_get_parametro(".self::K_PARAM_FACTOR_PRE_PUB_ALTO.") FACTOR_PRE_PUB_ALTO,
							dbo.f_get_parametro(".self::K_PARAM_ACCESO_LIBRE_A_COT.") ACCESO_LIBRE_A_COT,
							dbo.f_get_parametro(".self::K_PARAM_ACCESO_LIBRE_A_NV.") ACCESO_LIBRE_A_NV,
							dbo.f_get_parametro(".self::K_PARAM_DIREC_FTP.") DIRECCION_FTP,
							dbo.f_get_parametro(".self::K_PARAM_USUARIO_FTP.") USUARIO_FTP,
							dbo.f_get_parametro(".self::K_PARAM_PASSWORD_FTP.") PASSWORD_FTP,

							-- datos empresa Aporte Directorio.
							(select e.cod_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_AA.")) COD_EMPRESA_AA,
							
							(select nom_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_AA.")) NOM_EMPRESA_AA,
							
							(select rut from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_AA.")) RUT_AA,
							
							(select DIG_VERIF from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_AA.")) DIG_VERIF_AA,
							
							(select NOM_USUARIO from USUARIO WHERE COD_USUARIO = dbo.f_get_parametro(".self::K_PARAM_EMP_AA.")) NOM_USUARIO_AA,
							
							-- datos empresa GV
							(select e.cod_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_GV.")) COD_EMPRESA_GV,
							
							(select nom_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_GV.")) NOM_EMPRESA_GV,
														
							(select rut from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_GV.")) RUT_GV,
							
							(select DIG_VERIF from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_GV.")) DIG_VERIF_GV,
							
							(select NOM_USUARIO from USUARIO WHERE COD_USUARIO = dbo.f_get_parametro(".self::K_PARAM_EMP_GV.")) NOM_USUARIO_GV,
							
							-- datos empresa Aporte Administracion
							(select e.cod_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_ADM.")) COD_EMPRESA_ADM,
							
							(select nom_empresa from empresa e, usuario u
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_ADM.")) NOM_EMPRESA_ADM,
														
							(select rut from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_ADM.")) RUT_ADM,
							
							(select DIG_VERIF from empresa e, usuario u 
							where	u.cod_empresa = e.cod_empresa
							and		u.cod_usuario = dbo.f_get_parametro(".self::K_PARAM_EMP_ADM.")) DIG_VERIF_ADM,
							
							(select NOM_USUARIO from USUARIO WHERE COD_USUARIO = dbo.f_get_parametro(".self::K_PARAM_EMP_ADM.")) NOM_USUARIO_ADM,
							
							0 APORTE_AA,
							convert(varchar(20),getdate(), 103) FECHA_AA,
							'N' VISIBLE_PORCENTAJE_AA,
							0 APORTE_GF,
							convert(varchar(20),getdate(), 103) FECHA_GF,
							'N' VISIBLE_PORCENTAJE_GF,
							0 APORTE_GV,
							convert(varchar(20),getdate(), 103) FECHA_GV,
							'N' VISIBLE_PORCENTAJE_GV,
							0 APORTE_ADM,
							convert(varchar(20),getdate(), 103) FECHA_ADM,
							'N' VISIBLE_PORCENTAJE_ADM";
									 
		$this->dws['dw_parametro'] = new datawindow($sql);
		
		$this->dws['dw_parametro_porc_aa'] = new dw_parametro_porc('AA');
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('APORTE_AA'));
		$this->dws['dw_parametro']->add_control(new edit_date('FECHA_AA',10,10));
		$this->dws['dw_parametro']->add_control(new edit_text('VISIBLE_PORCENTAJE_AA',1, 1, 'hidden'));
		
		$this->dws['dw_parametro_porc_gf'] = new dw_parametro_porc('GF');
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('APORTE_GF'));
		$this->dws['dw_parametro']->add_control(new edit_date('FECHA_GF',10,10));
		$this->dws['dw_parametro']->add_control(new edit_text('VISIBLE_PORCENTAJE_GF',1, 1, 'hidden'));
		
		$this->dws['dw_parametro_porc_gv'] = new dw_parametro_porc('GV');
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('APORTE_GV'));
		$this->dws['dw_parametro']->add_control(new edit_date('FECHA_GV',10,10));
		$this->dws['dw_parametro']->add_control(new edit_text('VISIBLE_PORCENTAJE_GV',1, 1, 'hidden'));
		
		$this->dws['dw_parametro_porc_adm'] = new dw_parametro_porc('ADM');
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('APORTE_ADM'));
		$this->dws['dw_parametro']->add_control(new edit_date('FECHA_ADM',10,10));
		$this->dws['dw_parametro']->add_control(new edit_text('VISIBLE_PORCENTAJE_ADM',1, 1, 'hidden'));
		
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('IVA',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('RETENCION_B_H',3,4));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('SISTEMA',40,40));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('VERSION',40,40));
		$this->dws['dw_parametro']->add_control(new edit_num('DOLAR_COMERCIAL',10,10));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('PORC_MAX_DSCTO',3,4));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('NOMBRE_EMPRESA',100,100));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('GIRO_EMPRESA',100,100));
		$this->dws['dw_parametro']->add_control(new edit_text('RUT_EMPRESA',30,30));		
		$this->dws['dw_parametro']->add_control(new edit_num('VALIDEZ_OFERTA_COTIZACION',2,3));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('ENTREGA_COTIZACION',109,140));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('GARANTIA_COTIZACION',109,140));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('DIRECCION_EMPRESA',100,100));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('FONO_EMPRESA',30,30));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('FAX_EMPRESA',30,30));
		$this->dws['dw_parametro']->add_control(new edit_mail('MAIL_EMPRESA',100,100));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('CIUDAD_EMPRESA',30,30));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('PAIS_EMPRESA',30,30));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('GERENTE_VENTA',100,100));
		$this->dws['dw_parametro']->add_control(new edit_text('SMTP',30,30));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('USER_AUTENTICATION',30,30));
		$this->dws['dw_parametro']->add_control(new edit_text_upper('PASSWORD_AUTENTICATION',30,30));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_INT_X_DEFINIR',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_PUB_X_DEFINIR',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_INT_BAJO',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_INT_ALTO',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_PUB_BAJO',3,4));
		$this->dws['dw_parametro']->add_control(new edit_porcentaje('FACTOR_PRE_PUB_ALTO',3,4));
		$this->dws['dw_parametro']->add_control(new edit_check_box('ACCESO_LIBRE_A_COT','S','N'));
		$this->dws['dw_parametro']->add_control(new edit_check_box('ACCESO_LIBRE_A_NV','S','N'));
		$this->dws['dw_parametro']->add_control(new edit_num('PLAZO_PARA_CIERRE_NV',3,3));
		$this->dws['dw_parametro']->add_control(new edit_text_lower('WEB_EMPRESA',100,100));		
		$this->dws['dw_parametro']->add_control(new edit_num('MAXIMA_CANTIDAD_GD',3,3)); 
		$this->dws['dw_parametro']->add_control(new edit_num('MAXIMA_CANTIDAD_FA',3,3));
		$this->dws['dw_parametro']->add_control(new edit_num('MAXIMA_CANTIDAD_NC',3,3));
		$this->dws['dw_parametro']->add_control(new edit_text('DIRECCION_FTP',20,20));
		$this->dws['dw_parametro']->add_control(new edit_text('USUARIO_FTP',20,20));
		$this->dws['dw_parametro']->add_control(new edit_password('PASSWORD_FTP',20,20));

		$this->dws['dw_parametro']->add_control(new static_text('NOM_USUARIO_GV'));
		$this->dws['dw_parametro']->add_control(new static_text('NOM_USUARIO_AA'));

		//Datos de Empresa Aporte Directorio
		$this->dws['dw_parametro']->add_control(new static_num('RUT_AA',0)); 
		$this->dws['dw_parametro']->add_control(new static_text('NOM_EMPRESA_AA'));
		$this->dws['dw_parametro']->add_control(new static_num('DIG_VERIF_AA',0));
		$this->dws['dw_parametro']->add_control(new static_text('COD_EMPRESA_AA',10,10));
		
		//Datos de Empresa Aporte GV
		$this->dws['dw_parametro']->add_control(new static_num('RUT_GV',0)); 
		$this->dws['dw_parametro']->add_control(new static_text('NOM_EMPRESA_GV'));
		$this->dws['dw_parametro']->add_control(new static_num('DIG_VERIF_GV',0));
		$this->dws['dw_parametro']->add_control(new static_text('COD_EMPRESA_GV',10,10));
		
		//Datos de Empresa Aporte Admin
		$this->dws['dw_parametro']->add_control(new static_num('RUT_ADM',0)); 
		$this->dws['dw_parametro']->add_control(new static_text('NOM_EMPRESA_ADM'));
		$this->dws['dw_parametro']->add_control(new static_num('DIG_VERIF_ADM',0));
		$this->dws['dw_parametro']->add_control(new static_text('COD_EMPRESA_ADM',10,10));
		
		$this->dws['dw_lock_table'] = new dw_lock_table();
		
		$this->save_SESSION();
		$this->need_redraw();
		header("Location: wi_parametro_adm.php"); // para borrra el REQUEST
	}
	function new_record() {				
		$this->dws['dw_parametro']->insert_row();
	}	
	function load_record() 	{
		$this->current_record = 0;
		$this->dws['dw_parametro']->retrieve();
		$this->dws['dw_parametro_porc_aa']->retrieve();
		$this->dws['dw_parametro_porc_gf']->retrieve();
		$this->dws['dw_parametro_porc_gv']->retrieve();
		$this->dws['dw_parametro_porc_adm']->retrieve();
		$this->dws['dw_lock_table']->retrieve();
	}
	/*function habilitar(&$temp, $habilita) { 
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
	*/
	function get_key() 	{
		return 0;
	}

	function save_record($db) {
		$iva 				= $this->dws['dw_parametro']->get_item(0, 'IVA');
		$retencion_bh  		= $this->dws['dw_parametro']->get_item(0, 'RETENCION_B_H');
		$dolar_com			= $this->dws['dw_parametro']->get_item(0, 'DOLAR_COMERCIAL');
		$nom_empresa		= $this->dws['dw_parametro']->get_item(0, 'NOMBRE_EMPRESA');
		$giro_empresa		= $this->dws['dw_parametro']->get_item(0, 'GIRO_EMPRESA');
		$rut_empresa		= $this->dws['dw_parametro']->get_item(0, 'RUT_EMPRESA');		
		$validez_ofer_cot	= $this->dws['dw_parametro']->get_item(0, 'VALIDEZ_OFERTA_COTIZACION');
		$entrega_cot		= $this->dws['dw_parametro']->get_item(0, 'ENTREGA_COTIZACION');
		$garantia_cot		= $this->dws['dw_parametro']->get_item(0, 'GARANTIA_COTIZACION');
		$direccion_empresa	= $this->dws['dw_parametro']->get_item(0, 'DIRECCION_EMPRESA');
		$fono_empresa		= $this->dws['dw_parametro']->get_item(0, 'FONO_EMPRESA');
		$fax_empresa		= $this->dws['dw_parametro']->get_item(0, 'FAX_EMPRESA');
		$mail_empresa		= $this->dws['dw_parametro']->get_item(0, 'MAIL_EMPRESA');
		$ciudad_empresa		= $this->dws['dw_parametro']->get_item(0, 'CIUDAD_EMPRESA');
		$pais_empresa		= $this->dws['dw_parametro']->get_item(0, 'PAIS_EMPRESA');
		$gerente_venta		= $this->dws['dw_parametro']->get_item(0, 'GERENTE_VENTA');
		$f_precio_int		= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_INT_X_DEFINIR');
		$f_precio_pub		= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_PUB_X_DEFINIR');			
		$f_precio_int_bajo	= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_INT_BAJO');
		$f_precio_int_alto	= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_INT_ALTO');
		$f_precio_pub_bajo	= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_PUB_BAJO');
		$f_precio_pub_alto	= $this->dws['dw_parametro']->get_item(0, 'FACTOR_PRE_PUB_ALTO');		
		$web_empresa		= $this->dws['dw_parametro']->get_item(0, 'WEB_EMPRESA');
		$PORC_MAX_DSCTO 	= $this->dws['dw_parametro']->get_item(0, 'PORC_MAX_DSCTO');
		$acceso_libre_a_cot = $this->dws['dw_parametro']->get_item(0, 'ACCESO_LIBRE_A_COT');
		$acceso_libre_a_nv 	= $this->dws['dw_parametro']->get_item(0, 'ACCESO_LIBRE_A_NV');

		// TABLAS PORCENTAJE
		//APORTE DIRECTORIO.
		$aporte_aa			= $this->dws['dw_parametro']->get_item(0, 'APORTE_AA');		
		$fecha_aa			= $this->dws['dw_parametro']->get_item(0, 'FECHA_AA');		
		$visible_porc_aa	= $this->dws['dw_parametro']->get_item(0, 'VISIBLE_PORCENTAJE_AA');
			// GASTO FIJO 
		$aporte_gf			= $this->dws['dw_parametro']->get_item(0, 'APORTE_GF');		
		$fecha_gf			= $this->dws['dw_parametro']->get_item(0, 'FECHA_GF');		
		$visible_porc_gf	= $this->dws['dw_parametro']->get_item(0, 'VISIBLE_PORCENTAJE_GF');
			// GERENTE VENTA
		$aporte_gv			= $this->dws['dw_parametro']->get_item(0, 'APORTE_GV');		
		$fecha_gv			= $this->dws['dw_parametro']->get_item(0, 'FECHA_GV');		
		$visible_porc_gv	= $this->dws['dw_parametro']->get_item(0, 'VISIBLE_PORCENTAJE_GV');
		//APORTE ADMINISTRACION.
		$aporte_adm			= $this->dws['dw_parametro']->get_item(0, 'APORTE_ADM');		
		$fecha_adm			= $this->dws['dw_parametro']->get_item(0, 'FECHA_ADM');		
		$visible_porc_adm	= $this->dws['dw_parametro']->get_item(0, 'VISIBLE_PORCENTAJE_ADM');

		/* continuacion ideas:
		 * modificar  spu_parametro para que reciab 2 nuevos parametros y los grabe */

		$sp = 'spu_parametro_adm';
		$param = "		'$iva',
						'$retencion_bh',						
						'$dolar_com',	
						'$nom_empresa',	
						'$giro_empresa',
						'$rut_empresa',	
						'$validez_ofer_cot',
						'$entrega_cot',	
						'$garantia_cot',
						'$direccion_empresa',
						'$fono_empresa',
						'$fax_empresa',
						'$mail_empresa',
						'$ciudad_empresa',
						'$pais_empresa',
						'$gerente_venta',
						'$f_precio_int',
						'$f_precio_pub',
						'$f_precio_int_bajo',
						'$f_precio_int_alto',	
						'$f_precio_pub_bajo',	
						'$f_precio_pub_alto',
						'$aporte_aa',	
						'$fecha_aa',	
						'$visible_porc_aa',
						'$aporte_gf',	
						'$fecha_gf',	
						'$visible_porc_gf',
						'$aporte_gv',	
						'$fecha_gv',	
						'$visible_porc_gv',
						'$web_empresa',
						'$PORC_MAX_DSCTO',
						'$acceso_libre_a_cot',
						'$acceso_libre_a_nv',
						'$aporte_adm',	
						'$fecha_adm',	
						'$visible_porc_adm'";

		if ($db->EXECUTE_SP($sp,$param))
			return $this->dws['dw_lock_table']->update($db);
		else
			return false;
	}

	// Se reimplementa para que no se ejecute codigo respecto a los navegadores
	function navegacion(&$temp) 	{
		$temp->setVar("WI_RUTA_MENU", "Maestros->Parámetros->");
		$temp->setVar("WI_FECHA_ACTUAL", 'Fecha Actual: ' . $this->current_date());
		$key = $this->limpia_key($this->get_key());
		$temp->setVar("WI_FECHA_MODIF", '');		
		$this->habilita_boton($temp, 'back', true);
	}
	function goto_list() 	{
		$this->unlock_record();
		header('Location:' . $this->root_url . '../../commonlib/trunk/php/presentacion.php');		
	}
	function procesa_event(){		
		if (session::is_set('REDRAW_' . $this->nom_tabla)) {
			session::un_set('REDRAW_' . $this->nom_tabla);
			$this->load_record();
			$this->redraw();
		} 
		else
			parent::procesa_event();
	}
}
?>