<?php
require_once(dirname(__FILE__)."/../../../../../commonlib/trunk/php/auto_load.php");

class dw_valor_defecto_compra extends dw_valor_defecto_compra_base {
		function dw_valor_defecto_compra() {
			parent::dw_valor_defecto_compra_base(); 
		}
}

class wi_empresa extends wi_empresa_base {
	function wi_empresa($cod_item_menu) {
		parent::wi_empresa_base($cod_item_menu);
	}
		
	function save_record($db) {
		$COD_EMPRESA = $this->get_key();
		$RUT = $this->dws['dw_empresa']->get_item(0, 'RUT');
		$DIG_VERIF = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF');
		$ALIAS = $this->dws['dw_empresa']->get_item(0, 'ALIAS');
		$ALIAS_CONTABLE = $this->dws['dw_empresa']->get_item(0, 'ALIAS_CONTABLE');
		$NOM_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'NOM_EMPRESA');
		$NOM_EMPRESA = str_replace("'", "''", $NOM_EMPRESA);
		$GIRO = $this->dws['dw_empresa']->get_item(0, 'GIRO');
		$GIRO = str_replace("'", "''", $GIRO);
		$COD_CLASIF_EMPRESA = $this->dws['dw_empresa']->get_item(0, 'COD_CLASIF_EMPRESA');
		$DIRECCION_INTERNET = $this->dws['dw_empresa']->get_item(0, 'DIRECCION_INTERNET');
		$RUT_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'RUT_REPRESENTANTE');
		$DIG_VERIF_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'DIG_VERIF_REPRESENTANTE');
		$NOM_REPRESENTANTE = $this->dws['dw_empresa']->get_item(0, 'NOM_REPRESENTANTE');	
		$ES_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'ES_CLIENTE');
		$ES_PROVEEDOR_INTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
		$ES_PROVEEDOR_EXTERNO = $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		$ES_PERSONAL = $this->dws['dw_empresa']->get_item(0, 'ES_PERSONAL');
		$IMPRIMIR_EMP_MAS_SUC = $this->dws['dw_empresa']->get_item(0, 'IMPRIMIR_EMP_MAS_SUC');
		$SUJETO_A_APROBACION = $this->dws['dw_empresa']->get_item(0, 'SUJETO_A_APROBACION');
		$VENDEDOR_CABECERA = $this->dws['dw_empresa']->get_item(0, 'COD_USUARIO');
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = $this->dws['dw_empresa']->get_item(0, 'PORC_DSCTO_CORPORATIVO');
		$DSCTO_PROVEEDOR = $this->dws['dw_valor_defecto_compra']->get_item(0, 'DSCTO_PROVEEDOR');
		if($DSCTO_PROVEEDOR == ''){
		$DSCTO_PROVEEDOR = 0 ;	
		}

		$DIRECCION_INTERNET = ($DIRECCION_INTERNET=='') ? "null" : "'$DIRECCION_INTERNET'";
		$RUT_REPRESENTANTE = ($RUT_REPRESENTANTE=='') ? "null" : $RUT_REPRESENTANTE;
		$DIG_VERIF_REPRESENTANTE = ($DIG_VERIF_REPRESENTANTE=='') ? "null" : "'$DIG_VERIF_REPRESENTANTE'";
		$NOM_REPRESENTANTE = ($NOM_REPRESENTANTE=='') ? "null" : "'$NOM_REPRESENTANTE'";
		
		//se valida en la función validate que se ingresen los demás campos mandatory
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$PORC_DSCTO_CORPORATIVO = ($PORC_DSCTO_CORPORATIVO=='') ? "0" : $PORC_DSCTO_CORPORATIVO;

		$COD_EMPRESA = ($COD_EMPRESA=='') ? "null" : $COD_EMPRESA;
		if ($ALIAS_CONTABLE=='')
			$ALIAS_CONTABLE = $ALIAS;
		
		$TIPO_PARTICIPACION = $this->dws['dw_empresa']->get_item(0, 'TIPO_PARTICIPACION');
		$TIPO_PARTICIPACION			= ($TIPO_PARTICIPACION =='') ? "null" : "'$TIPO_PARTICIPACION'";
	   	
		$COD_FORMA_PAGO_CLIENTE = $this->dws['dw_empresa']->get_item(0, 'COD_FORMA_PAGO_CLIENTE');
		$COD_FORMA_PAGO_CLIENTE	= ($COD_FORMA_PAGO_CLIENTE =='') ? "null" : $COD_FORMA_PAGO_CLIENTE;
		

		$sp = 'spu_empresa';		
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
		
		
		// EL CAMPO PORC_DSCTO_CORPORATIVO FUE ELIMINADO DE LA BD	
		$param = "'$operacion',$COD_EMPRESA, $RUT, '$DIG_VERIF', '$ALIAS', '$ALIAS_CONTABLE','$NOM_EMPRESA', '$GIRO', $COD_CLASIF_EMPRESA, $DIRECCION_INTERNET, $RUT_REPRESENTANTE, $DIG_VERIF_REPRESENTANTE, $NOM_REPRESENTANTE, '$ES_CLIENTE', '$ES_PROVEEDOR_INTERNO', '$ES_PROVEEDOR_EXTERNO', '$ES_PERSONAL', '$IMPRIMIR_EMP_MAS_SUC', '$SUJETO_A_APROBACION', $PORC_DSCTO_CORPORATIVO,$VENDEDOR_CABECERA, $TIPO_PARTICIPACION,$DSCTO_PROVEEDOR,$COD_FORMA_PAGO_CLIENTE";
		 
		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$COD_EMPRESA = $db->GET_IDENTITY();
				$this->dws['dw_empresa']->set_item(0, 'COD_EMPRESA', $COD_EMPRESA);		
				
				$param = "'DSCTO_CORPORATIVO_EMPRESA',$COD_EMPRESA, $PORC_DSCTO_CORPORATIVO";
				$sp = 'spu_empresa';
				if (!$db->EXECUTE_SP($sp, $param))
					return false;
			}
			for ($i=0; $i<$this->dws['dw_sucursal']->row_count(); $i++)
				$this->dws['dw_sucursal']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);
				
				if (!$this->dws['dw_sucursal']->update($db))
					return false;
			
			for ($i=0; $i<$this->dws['dw_persona']->row_count(); $i++) {
				$COD_SUCURSAL = $this->dws['dw_persona']->get_item($i, 'P_COD_SUCURSAL');
				if ($COD_SUCURSAL < 0) {
						$row = $this->dws['dw_sucursal']->un_redirect(- $COD_SUCURSAL - 100);		// el -100 viene del insert_row
						$COD_SUCURSAL = $this->dws['dw_sucursal']->get_item($row, 'COD_SUCURSAL');
						$this->dws['dw_persona']->set_item($i, 'P_COD_SUCURSAL', $COD_SUCURSAL);		
				}
			}
			if (!$this->dws['dw_persona']->update($db))
				return false;
				
			for ($i=0; $i<$this->dws['dw_costo_producto']->row_count(); $i++)
				$this->dws['dw_costo_producto']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_costo_producto']->update($db))
				return false;
								
			for ($i=0; $i<$this->dws['dw_bitacora_empresa']->row_count(); $i++)
				$this->dws['dw_bitacora_empresa']->set_item($i, 'COD_EMPRESA', $COD_EMPRESA);				
			if (!$this->dws['dw_bitacora_empresa']->update($db))
				return false;

			// TAB VALOR DEFECTO COMPRA //	
			$prov_int			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_INTERNO');
			$prov_ext			 	= $this->dws['dw_empresa']->get_item(0, 'ES_PROVEEDOR_EXTERNO');
		
			$cod_persona_defecto 	= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_PERSONA_DEFECTO');
			$cod_persona_defecto	= ($cod_persona_defecto=='') ? "null" : $cod_persona_defecto;
			$cod_forma_pago 		= $this->dws['dw_valor_defecto_compra']->get_item(0, 'COD_FORMA_PAGO');
			$cod_forma_pago			= ($cod_forma_pago=='') ? "null" : $cod_forma_pago;
			
			if ($cod_persona_defecto < 0) {
				$row = $this->dws['dw_persona']->un_redirect(- $cod_persona_defecto - 100);
				$cod_persona_defecto = $this->dws['dw_persona']->get_item($row, 'COD_PERSONA');
				$this->dws['dw_valor_defecto_compra']->set_item($i, 'COD_PERSONA_DEFECTO', $cod_persona_defecto);		
			}
			
			if($prov_int == 'S' or $prov_ext == 'S')			
	    		$operacion = 'INSERT';	    		
	    	else	    		
	    		$operacion = 'DELETE';
			$sp = 'spu_valor_defecto_compra';

			$param = "'$operacion',$COD_EMPRESA,$cod_persona_defecto,$cod_forma_pago";
			 			 
			if (!$db->EXECUTE_SP($sp, $param))
				return false;
			 
			return true;
		}
		else
			return false;						
	}
}
?>