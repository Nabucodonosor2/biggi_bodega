<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
ini_set('memory_limit', '480M');
ini_set('max_execution_time', 900); //900 seconds = 15 minutes


class dw_resumen extends datawindow {
	function dw_resumen() {
		$sql = "exec spdw_envio_resumen {KEY1}, {KEY2}";
		parent::datawindow($sql);
		
		$this->add_control(new static_num('RE_CANT_FA'));
		$this->add_control(new static_num('RE_TOTAL_NETO_FA'));
		$this->add_control(new static_num('RE_MONTO_IVA_FA'));
		$this->add_control(new static_num('RE_TOTAL_FA'));
		
		$this->add_control(new static_num('RE_CANT_NC'));
		$this->add_control(new static_num('RE_TOTAL_NETO_NC'));
		$this->add_control(new static_num('RE_MONTO_IVA_NC'));
		$this->add_control(new static_num('RE_TOTAL_NC'));

		$this->add_control(new static_text('RE_DIF_MESES'));	
	}
	function new_envio($cod_tipo_envio) {
		$this->retrieve('null', $cod_tipo_envio);
	}
}
class dw_lista_factura extends datawindow {
	function dw_lista_factura() {
		$sql = "select 'S' FA_SELECCION
					,F.NRO_FACTURA FA_NRO_FACTURA
					,convert(varchar, F.FECHA_FACTURA, 103) FA_FECHA_FACTURA 
					,case F.COD_ESTADO_DOC_SII 
						when 4 then 'NULA'
						else F.NOM_EMPRESA 
					end FA_NOM_EMPRESA
					,case F.COD_ESTADO_DOC_SII 
						when 4 then null
						else F.TOTAL_NETO
					 end  FA_TOTAL_NETO
					,case F.COD_ESTADO_DOC_SII 
						when 4 then null
						else F.MONTO_IVA 
					 end FA_MONTO_IVA
					,case F.COD_ESTADO_DOC_SII 
						when 4 then null
						else F.TOTAL_CON_IVA 
					 end FA_TOTAL_CON_IVA
					,E.COD_ENVIO_SOFTLAND FA_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_FACTURA FA_COD_ENVIO_FACTURA
					,E.COD_FACTURA FA_COD_FACTURA
					,F.COD_ESTADO_DOC_SII FA_COD_ESTADO_DOC_SII
				from ENVIO_FACTURA E, FACTURA F
				where E.COD_ENVIO_SOFTLAND = {KEY1}
				  and F.COD_FACTURA = E.COD_FACTURA
				order by F.NRO_FACTURA";
		parent::datawindow($sql, 'ENVIO_FACTURA');		
		
		$this->add_control($control = new edit_check_box('FA_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_documento(this);");
		
		$this->add_control(new static_text('FA_FECHA_FACTURA'));
		$this->add_control(new static_num('FA_TOTAL_NETO'));
		$this->add_control(new static_num('FA_MONTO_IVA'));
		$this->add_control(new static_num('FA_TOTAL_CON_IVA'));
	}
	function new_envio() {
		$sql_original = $this->get_sql();
		$sql = "select 'S' FA_SELECCION
					,NRO_FACTURA FA_NRO_FACTURA
					,convert(varchar, FECHA_FACTURA, 103) FA_FECHA_FACTURA 
					,case COD_ESTADO_DOC_SII 
						when 4 then 'NULA'
						else NOM_EMPRESA 
					end FA_NOM_EMPRESA
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else TOTAL_NETO
					 end  FA_TOTAL_NETO
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else MONTO_IVA 
					 end FA_MONTO_IVA
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else TOTAL_CON_IVA 
					 end FA_TOTAL_CON_IVA
					,null FA_COD_ENVIO_SOFTLAND
					,null FA_COD_ENVIO_FACTURA
					,COD_FACTURA FA_COD_FACTURA
					,COD_ESTADO_DOC_SII FA_COD_ESTADO_DOC_SII
				from FACTURA
				where COD_ESTADO_DOC_SII in (2,3,4)
				  and COD_FACTURA not in (select COD_FACTURA from ENVIO_FACTURA EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
				order by NRO_FACTURA";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_factura';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_factura = $this->get_item($i, 'FA_COD_ENVIO_FACTURA');
			$cod_envio_softland = $this->get_item($i, 'FA_COD_ENVIO_SOFTLAND');
			$seleccion_fa = $this->get_item($i, 'FA_SELECCION');
			$cod_factura = $this->get_item($i, 'FA_COD_FACTURA');
			
			$cod_envio_factura = $cod_envio_factura=='' ? 'null' : $cod_envio_factura;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_factura,$cod_envio_softland, '$seleccion_fa', $cod_factura";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_lista_nota_credito extends datawindow {
	function dw_lista_nota_credito() {
		$sql = "select 'S' NC_SELECCION
					,N.NRO_NOTA_CREDITO NC_NRO_NOTA_CREDITO
					,convert(varchar, N.FECHA_NOTA_CREDITO, 103) NC_FECHA_NOTA_CREDITO 
					,case N.COD_ESTADO_DOC_SII 
						when 4 then 'NULA'
						else N.NOM_EMPRESA 
					end NC_NOM_EMPRESA
					,case N.COD_ESTADO_DOC_SII 
						when 4 then null
						else N.TOTAL_NETO
					 end  NC_TOTAL_NETO
					,case N.COD_ESTADO_DOC_SII 
						when 4 then null
						else N.MONTO_IVA 
					 end NC_MONTO_IVA
					,case N.COD_ESTADO_DOC_SII 
						when 4 then null
						else N.TOTAL_CON_IVA 
					 end NC_TOTAL_CON_IVA
					,E.COD_ENVIO_SOFTLAND NC_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_NOTA_CREDITO NC_COD_ENVIO_NOTA_CREDITO
					,E.COD_NOTA_CREDITO NC_COD_NOTA_CREDITO
					,N.COD_ESTADO_DOC_SII NC_COD_ESTADO_DOC_SII
				from ENVIO_NOTA_CREDITO E, NOTA_CREDITO N
				where E.COD_ENVIO_SOFTLAND = {KEY1}
				  and N.COD_NOTA_CREDITO = E.COD_NOTA_CREDITO
				order by N.NRO_NOTA_CREDITO";
		parent::datawindow($sql, 'ENVIO_NOTA_CREDITO');		
		
		$this->add_control($control = new edit_check_box('NC_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_documento(this);");
		
		$this->add_control(new static_text('NC_FECHA_NOTA_CREDITO'));
		$this->add_control(new static_num('NC_TOTAL_NETO'));
		$this->add_control(new static_num('NC_MONTO_IVA'));
		$this->add_control(new static_num('NC_TOTAL_CON_IVA'));
	}
	function new_envio() {
		$sql_original = $this->get_sql();
		$sql = "select 'S' NC_SELECCION
					,NRO_NOTA_CREDITO NC_NRO_NOTA_CREDITO
					,convert(varchar, FECHA_NOTA_CREDITO, 103) NC_FECHA_NOTA_CREDITO 
					,case COD_ESTADO_DOC_SII 
						when 4 then 'NULA'
						else NOM_EMPRESA 
					end NC_NOM_EMPRESA
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else TOTAL_NETO
					 end  NC_TOTAL_NETO
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else MONTO_IVA 
					 end NC_MONTO_IVA
					,case COD_ESTADO_DOC_SII 
						when 4 then null
						else TOTAL_CON_IVA 
					 end NC_TOTAL_CON_IVA
					,null NC_COD_ENVIO_SOFTLAND
					,null NC_COD_ENVIO_NOTA_CREDITO
					,COD_NOTA_CREDITO NC_COD_NOTA_CREDITO
					,COD_ESTADO_DOC_SII NC_COD_ESTADO_DOC_SII
				from NOTA_CREDITO
				where COD_ESTADO_DOC_SII in (2,3,4)
				  and COD_NOTA_CREDITO not in (select COD_NOTA_CREDITO from ENVIO_NOTA_CREDITO EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
				order by NRO_NOTA_CREDITO";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_nota_credito';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_nota_credito = $this->get_item($i, 'NC_COD_ENVIO_NOTA_CREDITO');
			$cod_envio_softland = $this->get_item($i, 'NC_COD_ENVIO_SOFTLAND');
			$seleccion_nc = $this->get_item($i, 'NC_SELECCION');
			$cod_nota_credito = $this->get_item($i, 'NC_COD_NOTA_CREDITO');
			
			$cod_envio_nota_credito = $cod_envio_nota_credito=='' ? 'null' : $cod_envio_nota_credito;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_nota_credito,$cod_envio_softland, '$seleccion_nc', $cod_nota_credito";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_envio_empresa extends datawindow {
	function dw_envio_empresa() {
		$sql = "SELECT 'S' EM_SELECCION
						,EE.COD_EMPRESA EM_COD_EMPRESA
						,E.ALIAS_CONTABLE EM_ALIAS
						,E.RUT EM_RUT
						,E.DIG_VERIF EM_DIG_VERIF
						,E.NOM_EMPRESA EM_NOM_EMPRESA
						,EE.COD_ENVIO_EMPRESA EM_COD_ENVIO_EMPRESA
						,EE.COD_ENVIO_SOFTLAND EM_COD_ENVIO_SOFTLAND
				FROM ENVIO_EMPRESA EE, EMPRESA E
				WHERE EE.COD_ENVIO_SOFTLAND = {KEY1} AND
					E.COD_EMPRESA = EE.COD_EMPRESA
				ORDER BY E.NOM_EMPRESA ASC";
						
		parent::datawindow($sql, 'ENVIO_EMPRESA');		
		$this->add_control(new edit_check_box('EM_SELECCION', 'S', 'N'));
	}
	function new_envio($cod_tipo_envio) {
		$sql_original = $this->get_sql();
		
		if ($cod_tipo_envio==1)
			// trae todos los clientes de FA y NC que no han sido enviados
			$sql = "select 'S' EM_SELECCION
							,A.COD_EMPRESA EM_COD_EMPRESA
							,E.ALIAS_CONTABLE EM_ALIAS
							,E.RUT EM_RUT
							,E.DIG_VERIF EM_DIG_VERIF
							,E.NOM_EMPRESA EM_NOM_EMPRESA
							,null EM_COD_ENVIO_EMPRESA
							,null EM_COD_ENVIO_SOFTLAND
					from 	(select distinct COD_EMPRESA
							from FACTURA
							where COD_ESTADO_DOC_SII in (2,3,4)
							  and COD_FACTURA not in (select COD_FACTURA from ENVIO_FACTURA EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
							union
							select distinct COD_EMPRESA
							from NOTA_CREDITO
							where COD_ESTADO_DOC_SII in (2,3,4)
							  and COD_NOTA_CREDITO not in (select COD_NOTA_CREDITO from ENVIO_NOTA_CREDITO EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
					) A, EMPRESA E
					where A.COD_EMPRESA not in (select COD_EMPRESA from ENVIO_EMPRESA EM, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EM.COD_ENVIO_SOFTLAND)
					  and E.COD_EMPRESA = A.COD_EMPRESA";
		else if ($cod_tipo_envio==2)
			// trae todos los clientes de FA y NC que no han sido enviados
			$sql = "select 'S' EM_SELECCION
							,A.COD_EMPRESA EM_COD_EMPRESA
							,E.ALIAS_CONTABLE EM_ALIAS
							,E.RUT EM_RUT
							,E.DIG_VERIF EM_DIG_VERIF
							,E.NOM_EMPRESA EM_NOM_EMPRESA
							,null EM_COD_ENVIO_EMPRESA
							,null EM_COD_ENVIO_SOFTLAND
					from 	(select distinct COD_EMPRESA
							from FAPROV
							where COD_ESTADO_FAPROV = 2 -- aprobada
							  and COD_FAPROV not in (select COD_FAPROV from ENVIO_FAPROV EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
							union
							select distinct COD_EMPRESA
							from NCPROV
							where COD_ESTADO_NCPROV = 2 -- aprobada
							  and COD_NCPROV not in (select COD_NCPROV from ENVIO_NCPROV EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
					) A, EMPRESA E
					where A.COD_EMPRESA not in (select COD_EMPRESA from ENVIO_EMPRESA EM, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EM.COD_ENVIO_SOFTLAND)
					  and E.COD_EMPRESA = A.COD_EMPRESA";
		else if ($cod_tipo_envio==3)
			return;
		else if ($cod_tipo_envio==4)
			return;
		else
			$this->error("cod_tipo_envio: $cod_tipo_envio desconocido.");
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_empresa';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$em_cod_envio_empresa = $this->get_item($i, 'EM_COD_ENVIO_EMPRESA');
			$em_cod_envio_softland = $this->get_item($i, 'EM_COD_ENVIO_SOFTLAND');
			$em_seleccion = $this->get_item($i, 'EM_SELECCION');
			$em_cod_empresa = $this->get_item($i, 'EM_COD_EMPRESA');
			
			$em_cod_envio_empresa = $em_cod_envio_empresa=='' ? 'null' : $em_cod_envio_empresa;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$em_cod_envio_empresa,$em_cod_envio_softland, '$em_seleccion', $em_cod_empresa";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
// compras
class dw_lista_factura_compra extends datawindow {
	function dw_lista_factura_compra() {
		$sql = "select 'S' FC_SELECCION
					,F.NRO_FAPROV FC_NRO_FACTURA
					,convert(varchar, F.FECHA_FAPROV, 103) FC_FECHA_FACTURA 
					,EM.NOM_EMPRESA FC_NOM_EMPRESA
					,F.TOTAL_NETO FC_TOTAL_NETO
					,F.MONTO_IVA  FC_MONTO_IVA
					,F.TOTAL_CON_IVA FC_TOTAL_CON_IVA 
					,E.COD_ENVIO_SOFTLAND FC_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_FAPROV FC_COD_ENVIO_FAPROV
					,E.COD_FAPROV FC_COD_FAPROV
					,E.NRO_CORRELATIVO_INTERNO FC_CORRELATIVO
				from ENVIO_FAPROV E, FAPROV F LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = F.COD_CUENTA_COMPRA, EMPRESA EM
				where E.COD_ENVIO_SOFTLAND = {KEY1}
				  and F.COD_FAPROV = E.COD_FAPROV
				  and EM.COD_EMPRESA = F.COD_EMPRESA
				order by EM.NOM_EMPRESA, E.NRO_CORRELATIVO_INTERNO, F.NRO_FAPROV";
		parent::datawindow($sql, 'ENVIO_FAPROV');		
		
		$this->add_control($control = new edit_check_box('FC_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_documento(this);");
		
		$this->add_control(new static_text('FC_FECHA_FACTURA'));
		$this->add_control(new static_num('FC_TOTAL_NETO'));
		$this->add_control(new static_num('FC_MONTO_IVA'));
		$this->add_control(new static_num('FC_TOTAL_CON_IVA'));

		$this->add_control(new static_text('FC_CORRELATIVO'));
	}
	function new_envio() {
		$sql_original = $this->get_sql();
		$sql = "select 'S' FC_SELECCION
					,F.NRO_FAPROV FC_NRO_FACTURA
					,convert(varchar, F.FECHA_FAPROV, 103) FC_FECHA_FACTURA 
					,E.NOM_EMPRESA FC_NOM_EMPRESA
					,F.TOTAL_NETO FC_TOTAL_NETO
					,F.MONTO_IVA FC_MONTO_IVA
					,F.TOTAL_CON_IVA FC_TOTAL_CON_IVA
					,null FC_COD_ENVIO_SOFTLAND
					,null FC_COD_ENVIO_FAPROV
					,F.COD_FAPROV FC_COD_FAPROV
					,null FC_CORRELATIVO
				from FAPROV F LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = F.COD_CUENTA_COMPRA, EMPRESA E
				where F.COD_ESTADO_FAPROV = 2 -- aprobada
				  AND YEAR(F.FECHA_FAPROV) > 2014
				  and F.COD_CUENTA_COMPRA is not null
				  and F.COD_FAPROV not in (select COD_FAPROV from ENVIO_FAPROV EF, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EF.COD_ENVIO_SOFTLAND)
				  and E.COD_EMPRESA = F.COD_EMPRESA
				order by E.NOM_EMPRESA, F.NRO_FAPROV";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_faprov';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_factura = $this->get_item($i, 'FC_COD_ENVIO_FAPROV');
			$cod_envio_softland = $this->get_item($i, 'FC_COD_ENVIO_SOFTLAND');
			$seleccion_fa = $this->get_item($i, 'FC_SELECCION');
			$cod_factura = $this->get_item($i, 'FC_COD_FAPROV');
			
			$cod_envio_factura = $cod_envio_factura=='' ? 'null' : $cod_envio_factura;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_factura,$cod_envio_softland, '$seleccion_fa', $cod_factura";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_lista_nota_credito_compra extends datawindow {
	function dw_lista_nota_credito_compra() {
		$sql = "select 'S' NCC_SELECCION
					,N.NRO_NCPROV NCC_NRO_NOTA_CREDITO
					,convert(varchar, N.FECHA_NCPROV, 103) NCC_FECHA_NOTA_CREDITO 
					,EM.NOM_EMPRESA NCC_NOM_EMPRESA
					,N.TOTAL_NETO NCC_TOTAL_NETO
					,N.MONTO_IVA NCC_MONTO_IVA
					,N.TOTAL_CON_IVA NCC_TOTAL_CON_IVA
					,E.COD_ENVIO_SOFTLAND NCC_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_NCPROV NCC_COD_ENVIO_NCPROV
					,E.COD_NCPROV NCC_COD_NCPROV
					,E.NRO_CORRELATIVO_INTERNO NCC_CORRELATIVO
				from ENVIO_NCPROV E, NCPROV N LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = N.COD_CUENTA_COMPRA, EMPRESA EM
				where E.COD_ENVIO_SOFTLAND = {KEY1}
				  and N.COD_NCPROV = E.COD_NCPROV
				  and EM.COD_EMPRESA = N.COD_EMPRESA
				order by EM.NOM_EMPRESA, E.NRO_CORRELATIVO_INTERNO, N.NRO_NCPROV";
		parent::datawindow($sql, 'ENVIO_NCPROV');		
		
		$this->add_control($control = new edit_check_box('NCC_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_documento(this);");
		
		$this->add_control(new static_text('NCC_FECHA_NOTA_CREDITO'));
		$this->add_control(new static_num('NCC_TOTAL_NETO'));
		$this->add_control(new static_num('NCC_MONTO_IVA'));
		$this->add_control(new static_num('NCC_TOTAL_CON_IVA'));

		$this->add_control(new static_text('NCC_CORRELATIVO'));
	}
	function new_envio() {
		$sql_original = $this->get_sql();
		$sql = "select 'S' NCC_SELECCION
					,N.NRO_NCPROV NCC_NRO_NOTA_CREDITO
					,convert(varchar, N.FECHA_NCPROV, 103) NCC_FECHA_NOTA_CREDITO 
					,E.NOM_EMPRESA NCC_NOM_EMPRESA
					,N.TOTAL_NETO NCC_TOTAL_NETO
					,N.MONTO_IVA NCC_MONTO_IVA
					,N.TOTAL_CON_IVA NCC_TOTAL_CON_IVA
					,null NCC_COD_ENVIO_SOFTLAND
					,null NCC_COD_ENVIO_NCPROV
					,N.COD_NCPROV NCC_COD_NCPROV
					,null NCC_CORRELATIVO
				from NCPROV N LEFT OUTER JOIN CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = N.COD_CUENTA_COMPRA, EMPRESA E
				where N.COD_ESTADO_NCPROV = 2
				  and N.COD_CUENTA_COMPRA is not null
				  and N.COD_NCPROV not in (select COD_NCPROV from ENVIO_NCPROV EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
				  and E.COD_EMPRESA = N.COD_EMPRESA
				order by E.NOM_EMPRESA, N.NRO_NCPROV";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_ncprov';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_nota_credito = $this->get_item($i, 'NCC_COD_ENVIO_NCPROV');
			$cod_envio_softland = $this->get_item($i, 'NCC_COD_ENVIO_SOFTLAND');
			$seleccion_nc = $this->get_item($i, 'NCC_SELECCION');
			$cod_nota_credito = $this->get_item($i, 'NCC_COD_NCPROV');
			
			$cod_envio_nota_credito = $cod_envio_nota_credito=='' ? 'null' : $cod_envio_nota_credito;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_nota_credito,$cod_envio_softland, '$seleccion_nc', $cod_nota_credito";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_lista_pago_faprov extends datawindow {
	function dw_lista_pago_faprov() {
		$sql = "select 'S' 							EG_SELECCION		
						,P.COD_PAGO_FAPROV 			EG_COD_PAGO_FAPROV
						,convert(varchar, P.FECHA_PAGO_FAPROV, 103) EG_FECHA_PAGO_FAPROV
						,EM.NOM_EMPRESA 			EG_NOM_EMPRESA
						,TPF.NOM_TIPO_PAGO_FAPROV	EG_NOM_TIPO_PAGO_FAPROV
						,P.MONTO_DOCUMENTO			EG_MONTO_DOCUMENTO
						,E.NRO_CORRELATIVO_INTERNO	EG_CORRELATIVO
						,E.COD_ENVIO_SOFTLAND 		EG_COD_ENVIO_SOFTLAND
						,E.COD_ENVIO_PAGO_FAPROV	EG_COD_ENVIO_PAGO_FAPROV
						,P.NRO_DOCUMENTO 			EG_NRO_DOCUMENTO
					from ENVIO_PAGO_FAPROV E, PAGO_FAPROV P, EMPRESA EM, TIPO_PAGO_FAPROV TPF
					where E.COD_ENVIO_SOFTLAND = {KEY1}
					  and P.COD_PAGO_FAPROV = E.COD_PAGO_FAPROV
					  and EM.COD_EMPRESA = P.COD_EMPRESA
					  and TPF.COD_TIPO_PAGO_FAPROV = P.COD_TIPO_PAGO_FAPROV
					order by P.COD_PAGO_FAPROV";
		parent::datawindow($sql, 'ENVIO_PAGOFAPROV');		
		
		$this->add_control($control = new edit_check_box('EG_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_faprov(this);");
		
		$this->add_control(new static_text('EG_FECHA_PAGO_FAPROV'));
		$this->add_control(new static_num('EG_MONTO_DOCUMENTO'));

		$this->add_control(new static_text('EG_CORRELATIVO'));
	}
	function new_envio() {
		// considera traspados los muy antiguos
		if (K_CLIENTE == 'COMERCIAL')
			$COD_PAGO_FAPROV_MIN = 7100;
		else
			$COD_PAGO_FAPROV_MIN = 0;
		
		$sql_original = $this->get_sql();
		$sql = "select 'S' 						EG_SELECCION
					,P.COD_PAGO_FAPROV 			EG_COD_PAGO_FAPROV
					,convert(varchar, P.FECHA_PAGO_FAPROV, 103) EG_FECHA_PAGO_FAPROV
					,E.NOM_EMPRESA 			EG_NOM_EMPRESA
					,TPF.NOM_TIPO_PAGO_FAPROV	EG_NOM_TIPO_PAGO_FAPROV
					,P.MONTO_DOCUMENTO			EG_MONTO_DOCUMENTO
					,null						EG_CORRELATIVO
					,null						EG_COD_ENVIO_SOFTLAND
					,null						EG_COD_ENVIO_PAGO_FAPROV
					,P.NRO_DOCUMENTO 			EG_NRO_DOCUMENTO
				from PAGO_FAPROV P, EMPRESA E, TIPO_PAGO_FAPROV TPF
				where P.COD_ESTADO_PAGO_FAPROV = 2	--confirmado
				  and P.COD_EMPRESA <> 409 --AS
				  and P.COD_PAGO_FAPROV not in (select COD_PAGO_FAPROV from ENVIO_PAGO_FAPROV EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
				  and E.COD_EMPRESA = P.COD_EMPRESA
				  and TPF.COD_TIPO_PAGO_FAPROV = P.COD_TIPO_PAGO_FAPROV
				  and P.COD_PAGO_FAPROV > $COD_PAGO_FAPROV_MIN
				order by P.COD_PAGO_FAPROV";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_pago_faprov';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_pago_faprov = $this->get_item($i, 'EG_COD_ENVIO_PAGO_FAPROV');
			$cod_envio_softland = $this->get_item($i, 'EG_COD_ENVIO_SOFTLAND');
			$seleccion = $this->get_item($i, 'EG_SELECCION');
			$cod_pago_faprov = $this->get_item($i, 'EG_COD_PAGO_FAPROV');
			
			$cod_envio_pago_faprov = $cod_envio_pago_faprov=='' ? 'null' : $cod_envio_pago_faprov;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_pago_faprov,$cod_envio_softland, '$seleccion', $cod_pago_faprov";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_lista_ingreso_pago extends datawindow {
	function dw_lista_ingreso_pago() {
		$sql = "select 'S' 						IN_SELECCION		
					,I.COD_INGRESO_PAGO			IN_COD_INGRESO_PAGO
					,I.COD_PROYECTO_INGRESO		IN_COD_PROYECTO_INGRESO
					,convert(varchar, I.FECHA_INGRESO_PAGO, 103) IN_FECHA_INGRESO_PAGO
					,EM.NOM_EMPRESA 			IN_NOM_EMPRESA
					,dbo.f_ingreso_pago_get_cant_doc(I.COD_INGRESO_PAGO) IN_CANT_DOC
					,dbo.f_ingreso_pago_get_saldo_output(I.COD_INGRESO_PAGO) IN_MONTO_DOC
					,E.NRO_CORRELATIVO_INTERNO	IN_CORRELATIVO
					,E.COD_ENVIO_SOFTLAND 		IN_COD_ENVIO_SOFTLAND
					,E.COD_ENVIO_INGRESO_PAGO	IN_COD_ENVIO_INGRESO_PAGO
				from ENVIO_INGRESO_PAGO E, INGRESO_PAGO I, EMPRESA EM
				where E.COD_ENVIO_SOFTLAND = {KEY1}
				  and I.COD_INGRESO_PAGO = E.COD_INGRESO_PAGO
				  and EM.COD_EMPRESA = I.COD_EMPRESA
				order by I.COD_INGRESO_PAGO ASC";
		parent::datawindow($sql, 'ENVIO_INGRESOPAGO');		
		
		$this->add_control($control = new edit_check_box('IN_SELECCION', 'S', 'N'));
		$control->set_onChange("selecciona_ingreso_pago(this);");
		
		$this->add_control(new static_text('IN_FECHA_INGRESO_PAGO'));
		$this->add_control(new static_num('IN_MONTO_DOC'));

		$this->add_control(new static_text('IN_CORRELATIVO'));
	}
	function new_envio() {
		// considera traspados los muy antiguos
		if (K_CLIENTE == 'COMERCIAL')
			$COD_INGRESO_PAGO_MIN = 13400;
		else
			$COD_INGRESO_PAGO_MIN = 0;
			
		$sql_original = $this->get_sql();
		$sql = "select 'S' 					IN_SELECCION
						,I.COD_INGRESO_PAGO 	IN_COD_INGRESO_PAGO
						,I.COD_PROYECTO_INGRESO	IN_COD_PROYECTO_INGRESO
						,convert(varchar, I.FECHA_INGRESO_PAGO, 103) IN_FECHA_INGRESO_PAGO
						,E.NOM_EMPRESA 			IN_NOM_EMPRESA
						,dbo.f_ingreso_pago_get_cant_doc(I.COD_INGRESO_PAGO) IN_CANT_DOC
						,dbo.f_ingreso_pago_get_saldo_output(I.COD_INGRESO_PAGO) IN_MONTO_DOC
						,null					IN_CORRELATIVO
						,null 					IN_COD_ENVIO_SOFTLAND
						,null					IN_COD_ENVIO_INGRESO_PAGO
					from INGRESO_PAGO I, EMPRESA E
					where I.COD_ESTADO_INGRESO_PAGO = 2	--confirmado
					  and I.COD_INGRESO_PAGO not in (select COD_INGRESO_PAGO from ENVIO_INGRESO_PAGO EN, ENVIO_SOFTLAND E where E.COD_ESTADO_ENVIO <> 3 and E.COD_ENVIO_SOFTLAND = EN.COD_ENVIO_SOFTLAND)
					  and E.COD_EMPRESA = I.COD_EMPRESA
					  and I.COD_INGRESO_PAGO > $COD_INGRESO_PAGO_MIN
					  and I.COD_PROYECTO_INGRESO is not null
					order by I.COD_INGRESO_PAGO ASC";
		$this->set_sql($sql);
		$this->retrieve();		
		$this->set_sql($sql_original);
		for ($i = 0; $i < $this->row_count(); $i++)
			$this->set_status_row($i, K_ROW_NEW_MODIFIED);
	}
	function update($db) {
		$sp = 'spu_envio_ingreso_pago';
		for ($i = 0; $i < $this->row_count(); $i++){
			$statuts = $this->get_status_row($i);
			if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
				continue;

			$cod_envio_ingreso_pago = $this->get_item($i, 'IN_COD_INGRESO_PAGO');
			$cod_envio_softland = $this->get_item($i, 'IN_COD_ENVIO_SOFTLAND');
			$seleccion = $this->get_item($i, 'IN_SELECCION');
			$cod_ingreso_pago = $this->get_item($i, 'IN_COD_INGRESO_PAGO');
			
			$cod_envio_ingreso_pago = $cod_envio_ingreso_pago=='' ? 'null' : $cod_envio_ingreso_pago;
			
			if ($statuts == K_ROW_NEW_MODIFIED)
				$operacion = 'INSERT';
			elseif ($statuts == K_ROW_MODIFIED)
				$operacion = 'UPDATE';
		
			$param = "'$operacion',$cod_envio_ingreso_pago,$cod_envio_softland, '$seleccion', $cod_ingreso_pago";
			if (!$db->EXECUTE_SP($sp, $param))
				return false;		
		}
		return true;		
	}
}
class dw_envio_softland extends datawindow {
	const K_ENVIO_VENTAS	= 1;
	const K_ENVIO_COMPRAS	= 2;
	const K_ENVIO_EGRESOS	= 3;
	const K_ENVIO_INGRESOS	= 4;
	
	function dw_envio_softland() {
		$sql = "SELECT E.COD_ENVIO_SOFTLAND
						,convert(varchar(20), E.FECHA_ENVIO_SOFTLAND, 103) FECHA_ENVIO_SOFTLAND
						,E.COD_TIPO_ENVIO
						,T.NOM_TIPO_ENVIO
						,E.COD_USUARIO
						,U.NOM_USUARIO	
						,E.COD_ESTADO_ENVIO
						,E.NRO_COMPROBANTE
						,E.NRO_CORRELATIVO_INTERNO
						,E.NRO_CORRELATIVO_INTERNO NRO_CORRELATIVO_H
						,dbo.f_last_mod('NOM_USUARIO', 'ENVIO_SOFTLAND', 'COD_ESTADO_ENVIO', E.COD_ENVIO_SOFTLAND) NOM_USUARIO_CAMBIO
						,dbo.f_last_mod('FECHA_CAMBIO', 'ENVIO_SOFTLAND', 'COD_ESTADO_ENVIO', E.COD_ENVIO_SOFTLAND) FECHA_CAMBIO
						,case E.COD_TIPO_ENVIO when 1 then '' else 'none' end DISPLAY_FA
						,case E.COD_TIPO_ENVIO when 1 then '' else 'none' end DISPLAY_NC
						,case E.COD_TIPO_ENVIO when 2 then '' else 'none' end DISPLAY_FC
						,case E.COD_TIPO_ENVIO when 2 then '' else 'none' end DISPLAY_FC
						,case E.COD_TIPO_ENVIO when 2 then '' else 'none' end DISPLAY_NCC
						,case E.COD_TIPO_ENVIO when 3 then '' else 'none' end DISPLAY_PP
						,case E.COD_TIPO_ENVIO when 4 then '' else 'none' end DISPLAY_IP
						,case E.COD_TIPO_ENVIO when 1 then '' when 2 then '' else 'none' end DISPLAY_CLI
						,case E.COD_TIPO_ENVIO when 1 then '' when 2 then '' else 'none' end DISPLAY_1_2_TIPO
						,case E.COD_TIPO_ENVIO when 3 then '' when 4 then '' else 'none' end DISPLAY_3_4_TIPO
				FROM ENVIO_SOFTLAND E, USUARIO U, TIPO_ENVIO T
				WHERE E.COD_ENVIO_SOFTLAND = {KEY1}
				  and E.COD_USUARIO = U.COD_USUARIO
				  and T.COD_TIPO_ENVIO = E.COD_TIPO_ENVIO";				
		parent::datawindow($sql);	
		$this->add_control(new static_text('COD_ENVIO_SOFTLAND'));
		$this->add_control(new static_text('FECHA_ENVIO_SOFTLAND'));
		$this->add_control(new edit_text('COD_TIPO_ENVIO', 10, 10, 'hidden', false, true));
		
		$this->add_control(new static_text('NOM_USUARIO'));	
		$sql = "select COD_ESTADO_ENVIO, NOM_ESTADO_ENVIO from ESTADO_ENVIO order by COD_ESTADO_ENVIO";
		$this->add_control(new drop_down_dw('COD_ESTADO_ENVIO', $sql, 0, '', false));	
		$this->add_control($control = new edit_num('NRO_COMPROBANTE'));
		$control->con_separador_miles = false;
		$control->set_onChange("change_nro_comprobante(this);");
		$this->add_control(new edit_num('NRO_CORRELATIVO_INTERNO'));
		$this->add_control(new edit_text('NRO_CORRELATIVO_H', 10, 10, 'hidden', false, true));		
	}
	function new_envio($cod_tipo_envio) {
		$this->insert_row();	
		$this->set_item(0, 'FECHA_ENVIO_SOFTLAND', $this->current_date());
		$this->set_item(0, 'COD_USUARIO', $this->cod_usuario);
		$this->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
		$this->set_item(0, 'COD_TIPO_ENVIO', $cod_tipo_envio);	
		$this->set_item(0, 'COD_ESTADO_ENVIO', 1);	// Emitido
		
		// obtiene el nom del tipo envio
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$sql = "select NOM_TIPO_ENVIO
				from TIPO_ENVIO
				where COD_TIPO_ENVIO = $cod_tipo_envio";
		$result = $db->build_results($sql);
		$nom_tipo_envio = $result[0]['NOM_TIPO_ENVIO'];
		$this->set_item(0, 'NOM_TIPO_ENVIO', $nom_tipo_envio);	
		
		// deja visible slos tabs que correspondan
		// todos no visible
		$this->set_item(0, 'DISPLAY_FA', 'none');
		$this->set_item(0, 'DISPLAY_NC', 'none');
		$this->set_item(0, 'DISPLAY_FC', 'none');
		$this->set_item(0, 'DISPLAY_NCC', 'none');
		$this->set_item(0, 'DISPLAY_PP', 'none');
		$this->set_item(0, 'DISPLAY_IP', 'none');
		$this->set_item(0, 'DISPLAY_CLI', 'none');
		
		if ($cod_tipo_envio==self::K_ENVIO_VENTAS) {// facturas de venta
			$this->set_item(0, 'DISPLAY_FA', '');
			$this->set_item(0, 'DISPLAY_NC', '');
			$this->set_item(0, 'DISPLAY_CLI', '');
		}
		else if ($cod_tipo_envio==self::K_ENVIO_COMPRAS) {	//facturas de compras
			$this->set_item(0, 'DISPLAY_FC', '');
			$this->set_item(0, 'DISPLAY_NCC', '');
			$this->set_item(0, 'DISPLAY_CLI', '');
		}
		else if ($cod_tipo_envio==self::K_ENVIO_EGRESOS) {	//facturas de compras
			$this->set_item(0, 'DISPLAY_PP', '');
		}
		else if ($cod_tipo_envio==self::K_ENVIO_INGRESOS) {	//facturas de compras
			$this->set_item(0, 'DISPLAY_IP', '');
		}
	}
}
class wi_envio_softland_base extends w_input {
	const	K_IMPRESA = 2; 
	const	K_ENVIADA_SII = 3; 
	const	K_ANULADA = 4; 
	const	K_FAPROV_BOLETA = 3;
	const	K_FAPROV_ELECTRONICA = 4;
	var $separador = ";";
	var $max_lineas = 40;
	var $cod_cuenta_otro_ingreso = 7002005;		//para COMERCIAL
	var $cod_cuenta_otro_gasto = 8005001;		//para COMERCIAL
	var $cuenta_por_pagar_boleta = 3010040;		//para COMERCIAL
	var $cc_otro_ingreso = '"001"';				//para COMERCIAL
	var $nro_comprobante;		// Usado en la exportacion
	
	function wi_envio_softland_base($cod_item_menu) {
		parent::w_input('envio_softland', $cod_item_menu);
		$this->add_FK_delete_cascada('ENVIO_EMPRESA');	
		$this->add_FK_delete_cascada('ENVIO_FACTURA');	
		$this->add_FK_delete_cascada('ENVIO_NOTA_CREDITO');	
		
		$this->dws['dw_envio_softland'] = new dw_envio_softland();	
		$this->dws['dw_resumen'] = new dw_resumen();
		
		$this->dws['dw_lista_factura'] = new dw_lista_factura();	
		$this->dws['dw_lista_nota_credito'] = new dw_lista_nota_credito();
		$this->dws['dw_envio_empresa'] = new dw_envio_empresa();

		$this->dws['dw_lista_factura_compra'] = new dw_lista_factura_compra();	
		$this->dws['dw_lista_nota_credito_compra'] = new dw_lista_nota_credito_compra();
		
		$this->dws['dw_lista_pago_faprov'] = new dw_lista_pago_faprov();
		$this->dws['dw_lista_ingreso_pago'] = new dw_lista_ingreso_pago();
		
		//audoria
		$this->add_auditoria('COD_ESTADO_ENVIO');

		$this->set_first_focus('NRO_COMPROBANTE');
	}		
	function habilitar(&$temp, $habilita) { 
		if (!$this->is_new_record())
			$this->b_print_visible = false;
	}
	function new_record() {
		$tipo_envio = session::get("tipo_envio_softland");
		if ($tipo_envio=='VENTAS')
			$cod_tipo_envio = 1;
		else if ($tipo_envio=='COMPRAS')
			$cod_tipo_envio = 2;
		else if ($tipo_envio=='EGRESOS')
			$cod_tipo_envio = 3;
		else if ($tipo_envio=='INGRESOS')
			$cod_tipo_envio = 4;
		else
			$this->error("Tipo_envio desconocido: ".$tipo_envio);		
		
		$this->dws['dw_envio_softland']->new_envio($cod_tipo_envio);	
		$this->dws['dw_envio_softland']->set_entrable('COD_ESTADO_ENVIO', false);
		$this->dws['dw_resumen']->new_envio($cod_tipo_envio);	
		
		if ($cod_tipo_envio == 1) {
			$this->dws['dw_lista_factura']->new_envio();	
			$this->dws['dw_lista_nota_credito']->new_envio();
		}
		else if ($cod_tipo_envio == 2){
			$this->dws['dw_lista_factura_compra']->new_envio();	
			$this->dws['dw_lista_nota_credito_compra']->new_envio();
		}
		else if ($cod_tipo_envio == 3){
			$this->dws['dw_lista_pago_faprov']->new_envio();	
		}
		else if ($cod_tipo_envio == 4){
			$this->dws['dw_lista_ingreso_pago']->new_envio();	
		}
		
		$this->dws['dw_envio_empresa']->new_envio($cod_tipo_envio);		
	}
	function load_record() {
		$cod_envio_softland = $this->get_item_wo($this->current_record, 'COD_ENVIO_SOFTLAND');
		$this->dws['dw_envio_softland']->retrieve($cod_envio_softland);
		$this->dws['dw_envio_softland']->set_entrable('COD_ESTADO_ENVIO', true);
		$cod_tipo_envio = $this->dws['dw_envio_softland']->get_item(0, 'COD_TIPO_ENVIO');
		$this->dws['dw_resumen']->retrieve($cod_envio_softland, $cod_tipo_envio);
		
		$this->dws['dw_lista_factura']->retrieve($cod_envio_softland);
		$this->dws['dw_lista_nota_credito']->retrieve($cod_envio_softland);
		$this->dws['dw_lista_factura_compra']->retrieve($cod_envio_softland);
		$this->dws['dw_lista_nota_credito_compra']->retrieve($cod_envio_softland);
		$this->dws['dw_lista_pago_faprov']->retrieve($cod_envio_softland);
		$this->dws['dw_lista_ingreso_pago']->retrieve($cod_envio_softland);
		$this->dws['dw_envio_empresa']->retrieve($cod_envio_softland);
		
		$cod_estado_envio = $this->dws['dw_envio_softland']->get_item(0, 'COD_ESTADO_ENVIO');
		if ($cod_estado_envio==1) {	// emitida
			$this->b_print_visible  	= false;
			$this->b_modify_visible	 	= true;
			$this->b_save_visible	 	= true;
			$this->b_no_save_visible	= true;
		}
		else if ($cod_estado_envio==2) { // confirmada
			$this->b_print_visible  	= true;
			$this->b_modify_visible	 	= true;
			$this->b_save_visible	 	= true;
			$this->b_no_save_visible	= true;
			
			$this->dws['dw_envio_softland']->set_entrable('NRO_COMPROBANTE', false);
			$this->dws['dw_envio_softland']->set_entrable('NRO_CORRELATIVO_INTERNO', false);

			$sql = "select COD_ESTADO_ENVIO, NOM_ESTADO_ENVIO from ESTADO_ENVIO where COD_ESTADO_ENVIO in (2,3) order by COD_ESTADO_ENVIO";
			$this->dws['dw_envio_softland']->add_control(new drop_down_dw('COD_ESTADO_ENVIO', $sql, 0, '', false));	

			$this->dws['dw_lista_factura']->set_entrable_dw(false);
			$this->dws['dw_lista_nota_credito']->set_entrable_dw(false);
			$this->dws['dw_lista_factura_compra']->set_entrable_dw(false);
			$this->dws['dw_lista_nota_credito_compra']->set_entrable_dw(false);
			$this->dws['dw_lista_pago_faprov']->set_entrable_dw(false);
			$this->dws['dw_lista_pago_faprov']->set_entrable_dw(false);
			$this->dws['dw_lista_ingreso_pago']->set_entrable_dw(false);
			$this->dws['dw_envio_empresa']->set_entrable_dw(false);
		}
		else if ($cod_estado_envio==3) { // anulada
			$this->b_print_visible  	= false;
			$this->b_modify_visible	 	= false;
			$this->b_save_visible	 	= false;
			$this->b_no_save_visible	= false;
		}
	}	
	function get_key() {
		return $this->dws['dw_envio_softland']->get_item(0, 'COD_ENVIO_SOFTLAND');
	}	
	function save_record($db) {
		$cod_envio_softland = $this->get_key();
		$cod_usuario = $this->dws['dw_envio_softland']->get_item(0, 'COD_USUARIO');
		$cod_tipo_envio = $this->dws['dw_envio_softland']->get_item(0, 'COD_TIPO_ENVIO');
		$nro_comprobante = $this->dws['dw_envio_softland']->get_item(0, 'NRO_COMPROBANTE');
		$cod_estado_envio = $this->dws['dw_envio_softland']->get_item(0, 'COD_ESTADO_ENVIO');
		$nro_correlativo_interno = $this->dws['dw_envio_softland']->get_item(0, 'NRO_CORRELATIVO_INTERNO');
		
		$cod_envio_softland = ($cod_envio_softland=='') ? 'null' : $cod_envio_softland;
		$nro_comprobante = ($nro_comprobante=='') ? 'null' : $nro_comprobante;
		$nro_correlativo_interno = ($nro_correlativo_interno=='') ? 'null' : $nro_correlativo_interno;
		
		$sp = 'spu_envio_softland';
	    if ($this->is_new_record())
	    	$operacion = 'INSERT';
	    else
	    	$operacion = 'UPDATE';
	    
		$param	= "'$operacion', $cod_envio_softland, $cod_usuario, $cod_tipo_envio, $nro_comprobante, $cod_estado_envio, $nro_correlativo_interno";

		if ($db->EXECUTE_SP($sp, $param)){
			if ($this->is_new_record()) {
				$cod_envio_softland = $db->GET_IDENTITY();
				$this->dws['dw_envio_softland']->set_item(0, 'COD_ENVIO_SOFTLAND', $cod_envio_softland);
			}
			// FACTURAS VENTAS
			for ($i=0; $i<$this->dws['dw_lista_factura']->row_count(); $i++)
				$this->dws['dw_lista_factura']->set_item($i, 'FA_COD_ENVIO_SOFTLAND', $cod_envio_softland);				
			if (!$this->dws['dw_lista_factura']->update($db))
				return false;
				
			// NOTAS CREDITO  VENTAS
			for ($i=0; $i<$this->dws['dw_lista_nota_credito']->row_count(); $i++)
				$this->dws['dw_lista_nota_credito']->set_item($i, 'NC_COD_ENVIO_SOFTLAND', $cod_envio_softland);
			if (!$this->dws['dw_lista_nota_credito']->update($db))
				return false;

			// FACTURAS COMPRAS
			for ($i=0; $i<$this->dws['dw_lista_factura_compra']->row_count(); $i++)
				$this->dws['dw_lista_factura_compra']->set_item($i, 'FC_COD_ENVIO_SOFTLAND', $cod_envio_softland);				
			if (!$this->dws['dw_lista_factura_compra']->update($db))
				return false;
				
			// NOTAS CREDITO  COMPRAS
			for ($i=0; $i<$this->dws['dw_lista_nota_credito_compra']->row_count(); $i++)
				$this->dws['dw_lista_nota_credito_compra']->set_item($i, 'NCC_COD_ENVIO_SOFTLAND', $cod_envio_softland);
			if (!$this->dws['dw_lista_nota_credito_compra']->update($db))
				return false;

			// EMPRESA
			for ($i=0; $i<$this->dws['dw_envio_empresa']->row_count(); $i++)
				$this->dws['dw_envio_empresa']->set_item($i, 'EM_COD_ENVIO_SOFTLAND', $cod_envio_softland);
			if (!$this->dws['dw_envio_empresa']->update($db))
				return false;
				
			// PAGO FAPROV
			for ($i=0; $i<$this->dws['dw_lista_pago_faprov']->row_count(); $i++)
				$this->dws['dw_lista_pago_faprov']->set_item($i, 'EG_COD_ENVIO_SOFTLAND', $cod_envio_softland);
			if (!$this->dws['dw_lista_pago_faprov']->update($db))
				return false;

			// INGRESO_PAGO
			for ($i=0; $i<$this->dws['dw_lista_ingreso_pago']->row_count(); $i++)
				$this->dws['dw_lista_ingreso_pago']->set_item($i, 'IN_COD_ENVIO_SOFTLAND', $cod_envio_softland);
			if (!$this->dws['dw_lista_ingreso_pago']->update($db))
				return false;

			return true;			
		}
		return false;
	}	
	function export_cliente() {
		$this->separador = ';';
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$this->nro_comprobante = $this->dws['dw_envio_softland']->get_item(0, 'NRO_COMPROBANTE');
		
		$name_archivo = "CLIENTES_".$this->nro_comprobante.".TXT";
		
		$fname = tempnam("/tmp", $name_archivo);
		$handle = fopen($fname,"w");
		for($i=0; $i < $this->dws['dw_envio_empresa']->row_count(); $i++) {
			$em_rut = $this->dws['dw_envio_empresa']->get_item($i, 'EM_RUT');
			$em_dig_verif = $this->dws['dw_envio_empresa']->get_item($i, 'EM_DIG_VERIF');
			
			$em_nom_empresa = $this->dws['dw_envio_empresa']->get_item($i, 'EM_NOM_EMPRESA');
			$em_nom_empresa = str_replace('"', '', $em_nom_empresa);	// borra las comillas dobles " si existen
			$em_nom_empresa = substr($em_nom_empresa, 0, 60);			// Deja un maximo de 60 caracteres
						
			// Obtiene la direccion, telefono y fax
			$em_cod_empresa = $this->dws['dw_envio_empresa']->get_item($i, 'EM_COD_EMPRESA');
			$sql = "select DIRECCION
							,TELEFONO
							,FAX
					from SUCURSAL
					where COD_EMPRESA = $em_cod_empresa
					  and DIRECCION_FACTURA = 'S'";
			$result = $db->build_results($sql);
			
			$direccion = $result[0]['DIRECCION'];
			$direccion = str_replace('"', '', $direccion);	// borra las comillas dobles " si existen
			$direccion = substr($direccion, 0, 60);			// Deja un maximo de 60 caracteres
			
			$telefono = $result[0]['TELEFONO'];
			$telefono = str_replace('"', '', $telefono);	// borra las comillas dobles " si existen
			$telefono = substr($telefono, 0, 15);			// Deja un maximo de 15 caracteres
			
			$fax = $result[0]['FAX'];
			$fax = str_replace('"', '', $fax);	// borra las comillas dobles " si existen
			$fax = substr($fax, 0, 15);			// Deja un maximo de 15 caracteres
			
			fwrite($handle, '"'.$em_rut.'"'.$this->separador);				// 1 Código auxiliar
			fwrite($handle, '"'.$em_nom_empresa.'"'.$this->separador);		// 2 Nombre Auxiliar
			fwrite($handle, '""'.$this->separador);							// 3 Nombre de Fantasía
			fwrite($handle, '"'.$em_rut.'"'.$this->separador);				// 4 RUT Auxiliar
			fwrite($handle, '"S"'.$this->separador);						// 5 Activo
			fwrite($handle, '""'.$this->separador);							// 6 Código Giro Comercial
			fwrite($handle, '""'.$this->separador);							// 7 Código País Auxiliar
			fwrite($handle, '""'.$this->separador);							// 8 Código de región	*** distinto a 4D
			fwrite($handle, '""'.$this->separador);							// 9 Código Ciudad Auxiliar
			fwrite($handle, '""'.$this->separador);							// 10 Código Comuna Auxiliar
			fwrite($handle, '"'.$direccion.'"'.$this->separador);			// 11 Dirección Auxiliar
			fwrite($handle, '""'.$this->separador);							// 12 Número Dir. Auxiliar
			fwrite($handle, '"'.$telefono.'"'.$this->separador);			// 13 Teléfono 1 Auxiliar
			fwrite($handle, '""'.$this->separador);							// 14 Teléfono 2 Auxiliar
			fwrite($handle, '""'.$this->separador);							// 15 Teléfono 3 Auxiliar
			fwrite($handle, '"'.$fax.'"'.$this->separador);					// 16 Fax 1 Auxiliar
			fwrite($handle, "\r\n");
		}
		fclose($handle);
		
		header("Content-Type: application/force-download; name=\"$name_archivo\"");
		header("Content-Disposition: inline; filename=\"$name_archivo\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		//unlink($fname);		
	}
	function formato_fecha($fecha) {
		// mm/dd/yy
		$aFecha = explode("/", $fecha);
		return $aFecha[0]."/".$aFecha[1]."/".substr($aFecha[2], 2,2);
	}
	function formato_cuenta($cod_cuenta) {
		// 00-00-000
		return substr($cod_cuenta, 0, 2)."-".substr($cod_cuenta, 2, 2)."-".substr($cod_cuenta, 4);
	}
	function send_nula($handle, $tipo_doc, $i, $cod_cuenta_contable) {
		if ($tipo_doc=='FA') {
			$nro_doc = $this->dws['dw_lista_factura']->get_item($i, 'FA_NRO_FACTURA');
			$fecha = $this->dws['dw_lista_factura']->get_item($i, 'FA_FECHA_FACTURA');
			$glosa = "FACTURA ANULADA"; 
		}
		else { // NC
			$nro_doc = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_NRO_NOTA_CREDITO');
			$fecha = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_FECHA_NOTA_CREDITO');
			$glosa = "NOTA CREDITO ANULADA"; 
		}
		$fecha = $this->formato_fecha($fecha);
		$cod_cuenta_contable = $this->formato_cuenta($cod_cuenta_contable);
		
		fwrite($handle, '"'.$cod_cuenta_contable.'"'.$this->separador);			// Cuenta contable
		fwrite($handle, '0'.$this->separador);									// monto debe
		fwrite($handle, '0'.$this->separador);									// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor 
		fwrite($handle, '"001"'.$this->separador);								// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"1"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo documento
		fwrite($handle, $nro_doc.$this->separador);							// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo docto referencia
		fwrite($handle, $nro_doc.$this->separador);							// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
		fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto suma detalle libro 
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function send_por_cobrar($handle, $tipo_doc, $i, $cuenta_por_cobrar) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($tipo_doc=='FA' || $tipo_doc=='FV' || $tipo_doc=='EX') {
			$total_con_iva = $this->dws['dw_lista_factura']->get_item($i, 'FA_TOTAL_CON_IVA');
			$cod_doc = $this->dws['dw_lista_factura']->get_item($i, 'FA_COD_FACTURA');
			$nro_doc = $this->dws['dw_lista_factura']->get_item($i, 'FA_NRO_FACTURA');
			$sql = "select E.ALIAS_CONTABLE
							,F.NOM_EMPRESA
							,F.RUT
							,convert(varchar, dateadd(m, 1, FECHA_FACTURA), 103) FECHA_VENCTO
							,V.CODIGO_SOFLAND
					from FACTURA F left outer join VENDEDOR_SOFLAND V on V.COD_VENDEDOR_SOFLAND = F.COD_VENDEDOR_SOFLAND
						, EMPRESA E
					where F.COD_FACTURA = $cod_doc
					  and E.COD_EMPRESA = F.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			$fecha_vencto = $result[0]['FECHA_VENCTO'];
			$vendedor_softland = $result[0]['CODIGO_SOFLAND'];
			
			$fecha = $this->dws['dw_lista_factura']->get_item($i, 'FA_FECHA_FACTURA');
			$total_neto = $this->dws['dw_lista_factura']->get_item($i, 'FA_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_factura']->get_item($i, 'FA_MONTO_IVA');
			
			$monto_debe = $total_con_iva;
			$monto_haber = 0;
			$tipo_doc_ref = $tipo_doc;
			$nro_doc_ref = $nro_doc;
		}
		else {	// NC
			$total_con_iva = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_TOTAL_CON_IVA');
			$cod_doc = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_COD_NOTA_CREDITO');
			$nro_doc = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_NRO_NOTA_CREDITO');
			$sql = "select E.ALIAS_CONTABLE
							,N.NOM_EMPRESA
							,N.RUT
							,convert(varchar, dateadd(m, 1, FECHA_NOTA_CREDITO), 103) FECHA_VENCTO
							,case N.cod_tipo_nota_credito
								when 1 then F.NRO_FACTURA
								else null
							end NRO_FACTURA
					from NOTA_CREDITO N LEFT OUTER JOIN FACTURA F on F.COD_FACTURA = N.COD_DOC, EMPRESA E
					where N.COD_NOTA_CREDITO = $cod_doc
					  and E.COD_EMPRESA = N.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			$fecha_vencto = $result[0]['FECHA_VENCTO'];
			$nro_factura = $result[0]['NRO_FACTURA'];
			$vendedor_softland = '';
			
			$fecha = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_FECHA_NOTA_CREDITO');
			$total_neto = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_MONTO_IVA');			

			$monto_debe = 0;
			$monto_haber = $total_con_iva;
			
			
			  
			$tipo_doc_ref = $tipo_doc;
			$nro_doc_ref = $nro_doc;
			
			/* 03-10-2014 RE solocoto que siempres se haga referencia a si mismo NC
			if ($nro_factura=='') {
				$tipo_doc_ref = $tipo_doc;
				$nro_doc_ref = $nro_doc;
			}
			else {
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				$sql = "select COD_ESTADO_DOC_SII
						from FACTURA 
						where NRO_FACTURA = $nro_factura";
				$result = $db->build_results($sql);
				$cod_estado_doc_sii = $result[0]['COD_ESTADO_DOC_SII']; 
				if ($cod_estado_doc_sii==self::K_IMPRESA)
					$tipo_doc_ref = 'FA';
				else
					$tipo_doc_ref = 'FV';
				$nro_doc_ref = $nro_factura;				
			}
			*/
		}
		$fecha = $this->formato_fecha($fecha);
		$fecha_vencto = $this->formato_fecha($fecha_vencto);	// mas 30 dias
		$cuenta_por_cobrar = $this->formato_cuenta($cuenta_por_cobrar);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta_por_cobrar.'"'.$this->separador);			// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '"'.$vendedor_softland.'"'.$this->separador);			// codigo vendedor
		fwrite($handle, '"001"'.$this->separador);								// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo documento
		fwrite($handle, $nro_doc.$this->separador);							// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc_ref.'"'.$this->separador);				// tipo docto referencia
		fwrite($handle, $nro_doc_ref.$this->separador);						// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
		if ($tipo_doc=='EX') {
			fwrite($handle, '0'.$this->separador);								// monto 1 detalle libro "AFECTO" 
			fwrite($handle, $total_neto.$this->separador);						// monto 2 detalle libro "EXENTO" 
		}
		else {
			fwrite($handle, $total_neto.$this->separador);						// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);								// monto 2 detalle libro "EXENTO" 
		}
		fwrite($handle, $monto_iva.$this->separador);						// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, $total_con_iva.$this->separador);					// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function send_venta_iva($handle, $tipo_doc, $i, $cuenta, $monto, $centro_costo) {
		if ($monto==0)
			return;

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($tipo_doc=='FA' || $tipo_doc=='FV' || $tipo_doc=='EX') {
			$fecha = $this->dws['dw_lista_factura']->get_item($i, 'FA_FECHA_FACTURA');
			$cod_doc = $this->dws['dw_lista_factura']->get_item($i, 'FA_COD_FACTURA');
			$nro_doc = $this->dws['dw_lista_factura']->get_item($i, 'FA_NRO_FACTURA');
			$sql = "select E.ALIAS_CONTABLE
							,F.NOM_EMPRESA
							,F.RUT
					from FACTURA F, EMPRESA E
					where F.COD_FACTURA = $cod_doc
					  and E.COD_EMPRESA = F.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
					
			$monto_debe = 0;
			$monto_haber = $monto;
		}
		else {	// NC
			$fecha = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_FECHA_NOTA_CREDITO');
			$cod_doc = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_COD_NOTA_CREDITO');
			$nro_doc = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_NRO_NOTA_CREDITO');
			$sql = "select E.ALIAS_CONTABLE
							,NC.NOM_EMPRESA
							,NC.RUT
					from NOTA_CREDITO NC, EMPRESA E
					where NC.COD_NOTA_CREDITO = $cod_doc
					  and E.COD_EMPRESA = NC.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			
			$monto_debe = $monto;
			$monto_haber = 0;
		}
		$fecha = $this->formato_fecha($fecha);
		$cuenta = $this->formato_cuenta($cuenta);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta.'"'.$this->separador);						// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor 
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '"'.$centro_costo.'"'.$this->separador);				// codigo centgro costo
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '""'.$this->separador);									// codigo auxiliar
		fwrite($handle, '""'.$this->separador);									// tipo documento
		fwrite($handle, '0'.$this->separador);									// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha vencimiento		
		fwrite($handle, '""'.$this->separador);									// tipo docto referencia
		fwrite($handle, '0'.$this->separador);									// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno
		fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function export_factura_nc($handle) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$tot_linea = 0;
		///////////////////
		// FACTURAS
		for($i=0; $i < $this->dws['dw_lista_factura']->row_count(); $i++) {
			$cod_estado_doc_sii = $this->dws['dw_lista_factura']->get_item($i, 'FA_COD_ESTADO_DOC_SII');
			
			// lineas maximo por cada comprobante
			$cant_linea = ($cod_estado_doc_sii==self::K_ANULADA) ? 1 : 3;
			if ($cant_linea + $tot_linea > $this->max_lineas) {
				$tot_linea = 0;
				$this->nro_comprobante++;
			}
			$tot_linea += $cant_linea; 
			
			// define las cuentas y CC
			$fa_cod_factura = $this->dws['dw_lista_factura']->get_item($i, 'FA_COD_FACTURA');
			$sql = "select C.COD_CENTRO_COSTO
							,C.COD_CUENTA_CONTABLE_VENTAS
							,C.COD_CUENTA_CONTABLE_IVA
							,C.COD_CUENTA_CONTABLE_POR_COBRAR
					from FACTURA F, CENTRO_COSTO C 
					where F.COD_FACTURA = $fa_cod_factura
					  and C.COD_CENTRO_COSTO = dbo.f_fa_get_cc(F.COD_FACTURA)";
			$result = $db->build_results($sql);
			$centro_costo = $result[0]['COD_CENTRO_COSTO'];
			$cuenta_ventas = $result[0]['COD_CUENTA_CONTABLE_VENTAS'];
			$cuenta_iva = $result[0]['COD_CUENTA_CONTABLE_IVA'];
			$cuenta_por_cobrar = $result[0]['COD_CUENTA_CONTABLE_POR_COBRAR'];
		
			if ($cod_estado_doc_sii==self::K_ANULADA)
				$this->send_nula($handle, 'FA', $i, $cuenta_por_cobrar);
			else {

				$total_neto = $this->dws['dw_lista_factura']->get_item($i, 'FA_TOTAL_NETO');
				$monto_iva = $this->dws['dw_lista_factura']->get_item($i, 'FA_MONTO_IVA');
			
				if ($cod_estado_doc_sii==self::K_IMPRESA)
					$tipo_doc = 'FA';
				else {
					if ($monto_iva==0)
						$tipo_doc = 'EX';
					else
						$tipo_doc = 'FV';
				}	
				$this->send_por_cobrar($handle, $tipo_doc, $i, $cuenta_por_cobrar);
				$this->send_venta_iva($handle, $tipo_doc, $i, $cuenta_ventas, $total_neto, $centro_costo);
				$this->send_venta_iva($handle, $tipo_doc, $i, $cuenta_iva, $monto_iva, '');
			}
			$cod_envio_factura = $this->dws['dw_lista_factura']->get_item($i, 'FA_COD_ENVIO_FACTURA');
			$db->EXECUTE_SP('spu_envio_factura', "'NRO_COMPROBANTE', $cod_envio_factura, $this->nro_comprobante");
		}
		
		///////////////////
		// NOTAS DE CREDITO
		for($i=0; $i < $this->dws['dw_lista_nota_credito']->row_count(); $i++) {
			$cod_estado_doc_sii = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_COD_ESTADO_DOC_SII');
			
			// lineas maximo por cada comprobante
			$cant_linea = ($cod_estado_doc_sii==self::K_ANULADA) ? 1 : 3;
			if ($cant_linea + $tot_linea > $this->max_lineas) {
				$tot_linea = 0;
				$this->nro_comprobante++;
			}
			$tot_linea += $cant_linea; 
			
			// define las cuentas y CC
			$nc_cod_nota_credito = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_COD_NOTA_CREDITO');
			$sql = "select C.COD_CENTRO_COSTO
							,C.COD_CUENTA_CONTABLE_VENTAS
							,C.COD_CUENTA_CONTABLE_IVA
							,C.COD_CUENTA_CONTABLE_POR_COBRAR
					from NOTA_CREDITO N, CENTRO_COSTO C 
					where N.COD_NOTA_CREDITO = $nc_cod_nota_credito
					  and C.COD_CENTRO_COSTO = dbo.f_nc_get_cc(N.COD_NOTA_CREDITO)";
			$result = $db->build_results($sql);
			$centro_costo = $result[0]['COD_CENTRO_COSTO'];
			$cuenta_ventas = $result[0]['COD_CUENTA_CONTABLE_VENTAS'];
			$cuenta_iva = $result[0]['COD_CUENTA_CONTABLE_IVA'];
			$cuenta_por_cobrar = $result[0]['COD_CUENTA_CONTABLE_POR_COBRAR'];
			
			if ($cod_estado_doc_sii==self::K_ANULADA)
				$this->send_nula($handle, 'NC', $i, $cuenta_por_cobrar);
			else {
				$total_neto = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_TOTAL_NETO');
				$monto_iva = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_MONTO_IVA');
				
				if ($cod_estado_doc_sii==self::K_IMPRESA)
					$tipo_doc = 'NC';
				else 
					$tipo_doc = 'NL';
				$this->send_por_cobrar($handle, $tipo_doc, $i, $cuenta_por_cobrar);
				$this->send_venta_iva($handle, $tipo_doc, $i, $cuenta_ventas, $total_neto, $centro_costo);
				$this->send_venta_iva($handle, $tipo_doc, $i, $cuenta_iva, $monto_iva, '');
			}
			$cod_envio_nota_credito = $this->dws['dw_lista_nota_credito']->get_item($i, 'NC_COD_ENVIO_NOTA_CREDITO');
			$db->EXECUTE_SP('spu_envio_nota_credito', "'NRO_COMPROBANTE', $cod_envio_nota_credito, $this->nro_comprobante");
		}
	}
	function send_por_pagar($handle, $tipo_doc, $i, $cuenta_por_pagar, $nro_correlativo_interno) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($tipo_doc=='FC' || $tipo_doc=='FE' || $tipo_doc=='FX' || $tipo_doc=='BE') {
			$total_con_iva = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_TOTAL_CON_IVA');
			$cod_doc = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_COD_FAPROV');
			$nro_doc = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_NRO_FACTURA');
			$sql = "select E.ALIAS_CONTABLE
							,E.NOM_EMPRESA
							,E.RUT
							,convert(varchar, dateadd(m, 1, FECHA_FAPROV), 103) FECHA_VENCTO
					from FAPROV F, EMPRESA E
					where F.COD_FAPROV = $cod_doc
					  and E.COD_EMPRESA = F.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			$fecha_vencto = $result[0]['FECHA_VENCTO'];
			
			$fecha = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_FECHA_FACTURA');
			$total_neto = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_MONTO_IVA');
			
			$monto_debe = 0;
			$monto_haber = $total_con_iva;
			$tipo_doc_ref = $tipo_doc;
			$nro_doc_ref = $nro_doc;
		}
		else {	// NC
			$total_con_iva = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_TOTAL_CON_IVA');
			$cod_doc = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_COD_NCPROV');
			$nro_doc = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_NRO_NOTA_CREDITO');
			$sql = "select E.ALIAS_CONTABLE
							,E.NOM_EMPRESA
							,E.RUT
							,convert(varchar, dateadd(m, 1, FECHA_FAPROV), 103) FECHA_VENCTO
					from NCPROV F, EMPRESA E
					where F.COD_NCPROV = $cod_doc
					  and E.COD_EMPRESA = F.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			$fecha_vencto = $result[0]['FECHA_VENCTO'];
			
			$fecha = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_FECHA_NOTA_CREDITO');
			$total_neto = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_MONTO_IVA');
			
			$monto_debe = $total_con_iva;
			$monto_haber = 0;
			$tipo_doc_ref = 'CN';
			$nro_doc_ref = $nro_doc;
		}
		$fecha = $this->formato_fecha($fecha);
		$fecha_vencto = $this->formato_fecha($fecha_vencto);	// mas 30 dias
		$cuenta_por_pagar = $this->formato_cuenta($cuenta_por_pagar);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta_por_pagar.'"'.$this->separador);			// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor
		if (K_CLIENTE=="BODEGA") 
			fwrite($handle, '"001"'.$this->separador);								// codigo ubicacion
		else
			fwrite($handle, '""'.$this->separador);								// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '""'.$this->separador);									// codigo centgro costo
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo documento
		fwrite($handle, $nro_doc.$this->separador);							// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc_ref.'"'.$this->separador);				// tipo docto referencia
		fwrite($handle, $nro_doc_ref.$this->separador);						// nro docto referencia

		fwrite($handle, '"'.$nro_correlativo_interno.'"'.$this->separador);									// nro correlativo interrno
		
		if ($monto_iva==0) {
			fwrite($handle, '0'.$this->separador);								// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);								// monto 2 detalle libro "EXENTO"
			fwrite($handle, $monto_iva.$this->separador);						// monto 3 detalle libro "IVA"
			fwrite($handle, $total_neto.$this->separador);									// monto 4 detalle libro 
		} 
		else {
			fwrite($handle, $total_neto.$this->separador);						// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);								// monto 2 detalle libro "EXENTO"
			fwrite($handle, $monto_iva.$this->separador);						// monto 3 detalle libro "IVA"
			fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		} 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, $total_con_iva.$this->separador);					// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}	
	function send_compra_iva($handle, $tipo_doc, $i, $cuenta, $monto, $centro_costo) {
		if ($monto==0)
			return;
			
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		if ($tipo_doc=='FC' || $tipo_doc=='FE' || $tipo_doc=='FX' || $tipo_doc=='BE') {
			$fecha = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_FECHA_FACTURA');
			$cod_doc = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_COD_FAPROV');
			$nro_doc = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_NRO_FACTURA');
			$sql = "select E.ALIAS_CONTABLE
							,E.NOM_EMPRESA
							,E.RUT
					from FAPROV F, EMPRESA E
					where F.COD_FAPROV = $cod_doc
					  and E.COD_EMPRESA = F.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			
			/*18-12-2014 posible error VM + MH, para BE la el monto IVA deberia ir al haber
			  agregar un if tipo
			  if ($tipo_doc=='BE') {
				$monto_debe = 0;
				$monto_haber = $monto;

				ojo que debe ser solo parav linea del IVA faltaria otra consicion al if para correr
				esperaremos un reclamo de adm !
			 */
			$monto_debe = $monto;
			$monto_haber = 0;
		}
		else {	// NC
			$fecha = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_FECHA_NOTA_CREDITO');
			$cod_doc = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_COD_NCPROV');
			$nro_doc = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_NRO_NOTA_CREDITO');
			$sql = "select E.ALIAS_CONTABLE
							,E.NOM_EMPRESA
							,E.RUT
					from NCPROV NC, EMPRESA E
					where NC.COD_NCPROV = $cod_doc
					  and E.COD_EMPRESA = NC.COD_EMPRESA";
			$result = $db->build_results($sql);
			$alias = $result[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result[0]['NOM_EMPRESA'];
			if ($alias=='')
				$glosa = substr($nom_empresa, 0, 20)." - ".$nro_doc;
			else
				$glosa = substr($alias, 0, 20)." - ".$nro_doc;
			$rut = $result[0]['RUT'];
			
			$monto_debe = 0;
			$monto_haber = $monto;
		}
		$fecha = $this->formato_fecha($fecha);
		$cuenta = $this->formato_cuenta($cuenta);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta.'"'.$this->separador);						// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor 
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '"'.$centro_costo.'"'.$this->separador);				// codigo centgro costo
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '""'.$this->separador);									// codigo auxiliar
		fwrite($handle, '""'.$this->separador);									// tipo documento
		fwrite($handle, '0'.$this->separador);									// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha vencimiento		
		fwrite($handle, '""'.$this->separador);									// tipo docto referencia
		fwrite($handle, '0'.$this->separador);									// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno
		fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function export_factura_nc_compras($handle) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$tot_linea = 0;
		///////////////////
		// FACTURAS COMPRAS
		$nro_correlativo_interno = $this->dws['dw_envio_softland']->get_item(0, 'NRO_CORRELATIVO_INTERNO');
		for($i=0; $i < $this->dws['dw_lista_factura_compra']->row_count(); $i++) {
			// lineas maximo por cada comprobante
			$cant_linea = 3;
			if ($cant_linea + $tot_linea > $this->max_lineas) {
				$tot_linea = 0;
				$this->nro_comprobante++;
			}
			$tot_linea += $cant_linea; 
			
			// define las cuentas y CC
			$fc_cod_faprov = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_COD_FAPROV');
			$sql = "select C.COD_CENTRO_COSTO
							,C.COD_CUENTA_CONTABLE_COMPRA
							,C.COD_CUENTA_CONTABLE_IVA
							,C.COD_CUENTA_CONTABLE_POR_PAGAR
							,F.COD_TIPO_FAPROV
					from FAPROV F, CUENTA_COMPRA C 
					where F.COD_FAPROV = $fc_cod_faprov
					  and C.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA ";
			$result = $db->build_results($sql);
			$centro_costo = $result[0]['COD_CENTRO_COSTO'];
			$cuenta_compra = $result[0]['COD_CUENTA_CONTABLE_COMPRA'];
			$cuenta_iva = $result[0]['COD_CUENTA_CONTABLE_IVA'];
			$cuenta_por_pagar = $result[0]['COD_CUENTA_CONTABLE_POR_PAGAR'];
			$cod_tipo_faprov = $result[0]['COD_TIPO_FAPROV'];
			
			$total_neto = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_MONTO_IVA');
				
			if ($cod_tipo_faprov==self::K_FAPROV_ELECTRONICA) {
				if ($monto_iva == 0)
					$tipo_doc = 'FX';
				else
					$tipo_doc = 'FE';
			}
			else if ($cod_tipo_faprov==self::K_FAPROV_BOLETA)
				$tipo_doc = 'BE';
			else {
				if ($monto_iva == 0)
					$tipo_doc = 'FX';
				else
					$tipo_doc = 'FC';
			}
			$this->send_por_pagar($handle, $tipo_doc, $i, $cuenta_por_pagar, $nro_correlativo_interno);
			$this->send_compra_iva($handle, $tipo_doc, $i, $cuenta_iva, $monto_iva, '');
			$this->send_compra_iva($handle, $tipo_doc, $i, $cuenta_compra, $total_neto, $centro_costo);
			
			$cod_envio_faprov = $this->dws['dw_lista_factura_compra']->get_item($i, 'FC_COD_ENVIO_FAPROV');
			$db->EXECUTE_SP('spu_envio_faprov', "'NRO_COMPROBANTE', $cod_envio_faprov, $this->nro_comprobante, '', $nro_correlativo_interno");
			$this->dws['dw_lista_factura_compra']->set_item($i, 'FC_CORRELATIVO', $nro_correlativo_interno);
			$nro_correlativo_interno++;
		}
		
		///////////////////
		// NOTAS DE CREDITO COMPRAS
		for($i=0; $i < $this->dws['dw_lista_nota_credito_compra']->row_count(); $i++) {
			// lineas maximo por cada comprobante
			$cant_linea = 3;
			if ($cant_linea + $tot_linea > $this->max_lineas) {
				$tot_linea = 0;
				$this->nro_comprobante++;
			}
			$tot_linea += $cant_linea; 
			
			// define las cuentas y CC
			$nc_cod_nota_credito = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_COD_NCPROV');
			$sql = "select C.COD_CENTRO_COSTO
							,C.COD_CUENTA_CONTABLE_COMPRA
							,C.COD_CUENTA_CONTABLE_IVA
							,C.COD_CUENTA_CONTABLE_POR_PAGAR
					from NCPROV N, CUENTA_COMPRA C 
					where N.COD_NCPROV = $nc_cod_nota_credito
					  and C.COD_CUENTA_COMPRA = N.COD_CUENTA_COMPRA";
			$result = $db->build_results($sql);
			$centro_costo = $result[0]['COD_CENTRO_COSTO'];
			$cuenta_compra = $result[0]['COD_CUENTA_CONTABLE_COMPRA'];
			$cuenta_iva = $result[0]['COD_CUENTA_CONTABLE_IVA'];
			$cuenta_por_cobrar = $result[0]['COD_CUENTA_CONTABLE_POR_PAGAR'];
			
			$total_neto = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_TOTAL_NETO');
			$monto_iva = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_MONTO_IVA');
				
			$this->send_por_pagar($handle, 'CN', $i, $cuenta_por_cobrar, $nro_correlativo_interno);
			$this->send_compra_iva($handle, 'CN', $i, $cuenta_iva, $monto_iva, '');
			$this->send_compra_iva($handle, 'CN', $i, $cuenta_compra, $total_neto, $centro_costo);
			
			$ncc_cod_envio_ncprov = $this->dws['dw_lista_nota_credito_compra']->get_item($i, 'NCC_COD_ENVIO_NCPROV');
			$db->EXECUTE_SP('spu_envio_ncprov', "'NRO_COMPROBANTE', $ncc_cod_envio_ncprov, $this->nro_comprobante, '', $nro_correlativo_interno");
			$this->dws['dw_lista_nota_credito_compra']->set_item($i, 'NCC_CORRELATIVO', $nro_correlativo_interno);
			$nro_correlativo_interno++;
		}
	}	
	function export_egresos($handle) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		$tot_linea = 0;
		///////////////////
		// PAGO FAPROV
		for($i=0; $i < $this->dws['dw_lista_pago_faprov']->row_count(); $i++) {
			// lineas maximo por cada comprobante
			$cant_linea = 3;
			if ($cant_linea + $tot_linea > $this->max_lineas) {
				$tot_linea = 0;
				$this->nro_comprobante++;
			}
			$tot_linea += $cant_linea; 
			
			// define las cuentas
			$eg_cod_pago_faprov = $this->dws['dw_lista_pago_faprov']->get_item($i, 'EG_COD_PAGO_FAPROV');
			$sql = "select C.COD_CUENTA_CONTABLE
							,C.COD_CUENTA_CONTABLE_POR_PAGAR
					from PAGO_FAPROV F, CUENTA_CORRIENTE C 
					where F.COD_PAGO_FAPROV = $eg_cod_pago_faprov
					  and C.COD_CUENTA_CORRIENTE = f.COD_CUENTA_CORRIENTE";
			$result = $db->build_results($sql);
			$cuenta_banco = $result[0]['COD_CUENTA_CONTABLE'];
			$cuenta_por_pagar_cta_cte = $result[0]['COD_CUENTA_CONTABLE_POR_PAGAR'];

			$eg_nro_documento = $this->dws['dw_lista_pago_faprov']->get_item($i, 'EG_NRO_DOCUMENTO');
			$this->send_por_pagar_egreso($handle, $eg_cod_pago_faprov, $nro_correlativo_interno, $eg_nro_documento, 'CHEQUE');
			
			$eg_monto_documento = $this->dws['dw_lista_pago_faprov']->get_item($i, 'EG_MONTO_DOCUMENTO');
			$eg_cod_pago_faprov = $this->dws['dw_lista_pago_faprov']->get_item($i, 'EG_COD_PAGO_FAPROV');

			// pago con NC (obs RE 05-058-2014 $CH_o_NC=='NOTA_CREDITO' no se usara)
			//$this->send_por_pagar_egreso($handle, $eg_cod_pago_faprov, $nro_correlativo_interno, null, 'NOTA_CREDITO');
			
			$sql = "select npp.MONTO_ASIGNADO
						,n.NRO_NCPROV
						,npp.COD_NCPROV_PAGO_FAPROV
						,isnull(C.COD_CUENTA_CONTABLE_POR_PAGAR, $cuenta_por_pagar_cta_cte) COD_CUENTA_CONTABLE_POR_PAGAR
						,n.COD_TIPO_NCPROV
					from NCPROV_PAGO_FAPROV npp
						, NCPROV n left outer join CUENTA_COMPRA C on C.COD_CUENTA_COMPRA = n.COD_CUENTA_COMPRA
					where npp.COD_PAGO_FAPROV = $eg_cod_pago_faprov
					  and n.COD_NCPROV = npp.COD_NCPROV";
			$result_ncprov = $db->build_results($sql);
			for ($j=0; $j < count($result_ncprov); $j++) {
				$monto = $result_ncprov[$j]['MONTO_ASIGNADO'];
				$centro_costo = '';
				$nro_documento = $result_ncprov[$j]['NRO_NCPROV'];
				$cod_ncprov_pago_faprov = $result_ncprov[$j]['COD_NCPROV_PAGO_FAPROV'];
				$cuenta_por_pagar = $result_ncprov[$j]['COD_CUENTA_CONTABLE_POR_PAGAR'];
				$cod_tipo_ncprov = $result_ncprov[$j]['COD_TIPO_NCPROV'];
				$this->send_nc_egreso($handle, $monto, $centro_costo, $nro_documento, $eg_cod_pago_faprov, $cod_ncprov_pago_faprov, $eg_nro_documento, $cuenta_por_pagar, $cod_tipo_ncprov);
			}

			//cuenta banco va al final
			$this->send_banco_egreso($handle, $cuenta_banco, $eg_monto_documento, '', $eg_nro_documento, $eg_cod_pago_faprov);
			
			$cod_envio_pago_faprov = $this->dws['dw_lista_pago_faprov']->get_item($i, 'EG_COD_ENVIO_PAGO_FAPROV');
			$db->EXECUTE_SP('spu_envio_pago_faprov', "'NRO_COMPROBANTE', $cod_envio_pago_faprov, $this->nro_comprobante, '', null");

			$this->nro_comprobante++;
		}
	}
	function send_por_pagar_egreso($handle, $cod_pago_faprov, $nro_correlativo_interno, $nro_documento, $CH_o_NC) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		// busca la fecha del cheque
		$sql = "select convert(varchar, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO 
						,c.COD_CUENTA_CONTABLE_POR_PAGAR
				from PAGO_FAPROV p, CUENTA_CORRIENTE C 
				where p.COD_PAGO_FAPROV = $cod_pago_faprov
				  and C.COD_CUENTA_CORRIENTE = P.COD_CUENTA_CORRIENTE";
		$result = $db->build_results($sql);
		$fecha_documento = $result[0]['FECHA_DOCUMENTO'];
		$fecha_documento = $this->formato_fecha($fecha_documento);
		$cod_cuenta_contable_por_pagar = $result[0]['COD_CUENTA_CONTABLE_POR_PAGAR'];	//usada cuando no tiene proyecto contable la FAPROV
		
		if ($CH_o_NC=='CHEQUE') {
			$sql = "select f.COD_FAPROV
							,f.NRO_FAPROV
							,f.COD_TIPO_FAPROV
							,p.MONTO_ASIGNADO
							,isNUll(C.COD_CUENTA_CONTABLE_POR_PAGAR, $cod_cuenta_contable_por_pagar) COD_CUENTA_CONTABLE_POR_PAGAR
							,f.MONTO_IVA
					from PAGO_FAPROV_FAPROV p, FAPROV f left outer join CUENTA_COMPRA C ON C.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA 
					where p.COD_PAGO_FAPROV = $cod_pago_faprov
					  and f.COD_FAPROV = p.COD_FAPROV
					  order by f.NRO_FAPROV DESC";
		}
		else if ($CH_o_NC=='NOTA_CREDITO') {
			$sql = "select f.COD_FAPROV
							,f.NRO_FAPROV
							,f.COD_TIPO_FAPROV
							,nu.MONTO_ASIGNADO
							,C.COD_CUENTA_CONTABLE_POR_PAGAR
							,n.NRO_NCPROV
							,f.MONTO_IVA
					from NCPROV_PAGO_FAPROV npp, NCPROV_USADA nu, CUENTA_COMPRA c, NCPROV n, FAPROV f
					where npp.COD_PAGO_FAPROV = $cod_pago_faprov
					  and nu.COD_NCPROV_PAGO_FAPROV = npp.COD_NCPROV_PAGO_FAPROV
					  and f.COD_FAPROV = nu.COD_FAPROV
					  and C.COD_CUENTA_COMPRA = f.COD_CUENTA_COMPRA
					  and n.COD_NCPROV = npp.COD_NCPROV";
		}
		$result_faprov = $db->build_results($sql);

		for ($j=0; $j < count($result_faprov); $j++) {
			//suma lo pagado con NC (obs RE 05-058-2014 ($CH_o_NC=='NOTA_CREDITO' no se usara)
			$cod_faprov = $result_faprov[$j]['COD_FAPROV']; 
			$sql = "select isnull(sum(nu.MONTO_ASIGNADO), 0) MONTO_ASIGNADO
					from NCPROV_PAGO_FAPROV npp, NCPROV_USADA nu
					where npp.COD_PAGO_FAPROV = $cod_pago_faprov
					  and nu.COD_NCPROV_PAGO_FAPROV = npp.COD_NCPROV_PAGO_FAPROV
					  and nu.COD_FAPROV = $cod_faprov";
			$result_nc_usada = $db->build_results($sql);
			$result_faprov[$j]['MONTO_ASIGNADO'] = $result_faprov[$j]['MONTO_ASIGNADO'] + $result_nc_usada[0]['MONTO_ASIGNADO'];
			//////////////////
					  
			
			
			$cuenta_por_pagar = $result_faprov[$j]['COD_CUENTA_CONTABLE_POR_PAGAR']; 
			$cuenta_por_pagar = $this->formato_cuenta($cuenta_por_pagar);
			$cod_tipo_faprov = $result_faprov[$j]['COD_TIPO_FAPROV']; 
			$monto_iva = $result_faprov[$j]['MONTO_IVA']; 
			if ($cod_tipo_faprov==self::K_FAPROV_ELECTRONICA) {
				if ($monto_iva ==0)
					$tipo_doc = 'FX';
				else
					$tipo_doc = 'FE';
			}
			else if ($cod_tipo_faprov==self::K_FAPROV_BOLETA) {
				$tipo_doc = 'BE';
				$cuenta_por_pagar = $this->formato_cuenta($this->cuenta_por_pagar_boleta);	// Fija
			}
			else {
				if ($monto_iva ==0)
					$tipo_doc = 'FX';
				else
					$tipo_doc = 'FC';
			}
				
			$cod_doc = $result_faprov[$j]['COD_FAPROV']; 
			$nro_doc = $result_faprov[$j]['NRO_FAPROV']; 
			if ($tipo_doc=='FC' || $tipo_doc=='FE' || $tipo_doc=='BE' || $tipo_doc=='FX') {
				$sql = "select E.ALIAS_CONTABLE
								,E.NOM_EMPRESA
								,E.RUT
								,convert(varchar, dateadd(m, 1, FECHA_FAPROV), 103) FECHA_VENCTO
								,convert(varchar, FECHA_FAPROV, 103) FECHA_FAPROV
						from FAPROV F, EMPRESA E
						where F.COD_FAPROV = $cod_doc
						  and E.COD_EMPRESA = F.COD_EMPRESA";
				$result = $db->build_results($sql);
				$alias = $result[0]['ALIAS_CONTABLE'];
				$nom_empresa = $result[0]['NOM_EMPRESA'];
				
				if ($CH_o_NC=='CHEQUE') {
					// "CH/nro_cheque"
					$dato_documento = 'CH/'.$nro_documento;
				}
				else if ($CH_o_NC=='NOTA_CREDITO') {
					// "NC/nro_nc"
					$nro_documento = $result_faprov[$j]['NRO_NCPROV']; 
					$dato_documento = 'NC/'.$nro_documento;
				}
				if ($alias=='')
					$glosa = $dato_documento.' '.substr($nom_empresa, 0, 20)." F/".$nro_doc;
				else
					$glosa = $dato_documento.' '.substr($alias, 0, 20)." F/".$nro_doc;
				
				$rut = $result[0]['RUT'];
				$fecha_vencto = $result[0]['FECHA_VENCTO'];
				$fecha = $result[0]['FECHA_FAPROV'];
				
				$monto_debe = $result_faprov[$j]['MONTO_ASIGNADO'];
				$monto_haber = 0;
				$tipo_doc_ref = $tipo_doc;
				$nro_doc_ref = $nro_doc;
			}
			$fecha = $this->formato_fecha($fecha);
			$fecha_vencto = $this->formato_fecha($fecha_vencto);	// mas 30 dias
		
			// Envia los datos
			fwrite($handle, '"'.$cuenta_por_pagar.'"'.$this->separador);			// Cuenta contable
			fwrite($handle, $monto_debe.$this->separador);							// monto debe
			fwrite($handle, $monto_haber.$this->separador);							// monto haber
			fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
			fwrite($handle, '0'.$this->separador);									// equivalencia moneda
			fwrite($handle, '0'.$this->separador);									// monto al debe adicional
			fwrite($handle, '0'.$this->separador);									// monto al haber adicional
			fwrite($handle, '""'.$this->separador);									// condiciones de vta 
			fwrite($handle, '""'.$this->separador);									// codigo vendedor
			fwrite($handle, '""'.$this->separador);									// codigo ubicacion
			fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
			fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
			fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
			fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
			fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
			fwrite($handle, '""'.$this->separador);									// codigo centgro costo
			fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
			fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
			fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
			fwrite($handle, '"CH"'.$this->separador);								// tipo documento
			fwrite($handle, $nro_documento.$this->separador);							// nro documento
			fwrite($handle, '"'.$fecha_documento.'"'.$this->separador);						// fecha emision
			fwrite($handle, '"'.$fecha_documento.'"'.$this->separador);				// fecha vencimiento
			fwrite($handle, '"'.$tipo_doc_ref.'"'.$this->separador);				// tipo docto referencia
			fwrite($handle, $nro_doc_ref.$this->separador);							// nro docto referencia
			fwrite($handle, '""'.$this->separador);									// nro correlativo interrno
			fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
			fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
			fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
			fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
			fwrite($handle, '0'.$this->separador);									// numero documento desde 
			fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
			fwrite($handle, $this->nro_comprobante."\r\n");							// nro comprobante
		}
	}	
	function send_banco_egreso($handle, $cuenta_banco, $monto, $centro_costo, $nro_documento, $cod_pago_faprov) {
		if ($monto==0)
			return;
			
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		// busca la fecha del cheque
		$sql = "select convert(varchar, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO 
				from PAGO_FAPROV p
				where p.COD_PAGO_FAPROV = $cod_pago_faprov";
		$result = $db->build_results($sql);
		$fecha_documento = $result[0]['FECHA_DOCUMENTO'];
		$fecha_documento = $this->formato_fecha($fecha_documento);
		
		$sql = "select E.ALIAS_CONTABLE
						,E.NOM_EMPRESA
				from PAGO_FAPROV P, EMPRESA E
				where P.COD_PAGO_FAPROV = $cod_pago_faprov
				  and E.COD_EMPRESA = P.COD_EMPRESA";
		$result = $db->build_results($sql);
		$alias = $result[0]['ALIAS_CONTABLE'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		
		if ($alias=='')
			$alias = substr($nom_empresa, 0, 20);
			
			
		$sql = "select F.NRO_FAPROV
				from PAGO_FAPROV_FAPROV P, FAPROV F
				where P.COD_PAGO_FAPROV = $cod_pago_faprov
				  and F.COD_FAPROV = P.COD_FAPROV";
		$result = $db->build_results($sql);
		if (count($result) > 1)
			$nro_fas = "F/VARIAS";
		else
			$nro_fas = "F/".$result[0]['NRO_FAPROV'];
			
		$glosa = 'CH/'.$nro_documento.' '.$alias.' '.$nro_fas;
		$monto_debe = 0;
		$monto_haber = $monto;
		$fecha = $this->formato_fecha($fecha);
		$cuenta = $this->formato_cuenta($cuenta_banco);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta.'"'.$this->separador);						// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);							// monto debe
		fwrite($handle, $monto_haber.$this->separador);							// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor 
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '"'.$centro_costo.'"'.$this->separador);				// codigo centgro costo
		fwrite($handle, '"CH"'.$this->separador);								// tipo docto conciliacion 
		fwrite($handle, $nro_documento.$this->separador);						// nro docto conciliacion
		fwrite($handle, '""'.$this->separador);									// codigo auxiliar
		fwrite($handle, '""'.$this->separador);									// tipo documento
		fwrite($handle, '0'.$this->separador);									// nro documento
		fwrite($handle, '"'.$fecha_documento.'"'.$this->separador);						// fecha emision
		fwrite($handle, '""'.$this->separador);									// fecha vencimiento		
		fwrite($handle, '""'.$this->separador);									// tipo docto referencia
		fwrite($handle, '0'.$this->separador);									// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno
		fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function send_nc_egreso($handle, $monto, $centro_costo, $nro_documento, $cod_pago_faprov, $cod_ncprov_pago_faprov, $nro_cheque, $cuenta_por_pagar, $cod_tipo_ncprov) {
		if ($monto==0)
			return;
			
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		// busca la fecha del cheque
		$sql = "select convert(varchar, FECHA_DOCUMENTO, 103) FECHA_DOCUMENTO 
				from PAGO_FAPROV p
				where p.COD_PAGO_FAPROV = $cod_pago_faprov";
		$result = $db->build_results($sql);
		$fecha_documento = $result[0]['FECHA_DOCUMENTO'];
		$fecha_documento = $this->formato_fecha($fecha_documento);
		
		
		$sql = "select E.ALIAS_CONTABLE
						,E.NOM_EMPRESA
						,E.RUT
				from PAGO_FAPROV P, EMPRESA E
				where P.COD_PAGO_FAPROV = $cod_pago_faprov
				  and E.COD_EMPRESA = P.COD_EMPRESA";
		$result = $db->build_results($sql);
		$alias = $result[0]['ALIAS_CONTABLE'];
		$nom_empresa = $result[0]['NOM_EMPRESA'];
		$rut = $result[0]['RUT'];
		
		// OJO cuando es NC "CH/nro_cheque" cambia por "NC/nro_nc" *********
		if ($alias=='')
			$alias = substr($nom_empresa, 0, 20);
			
		if ($cod_tipo_ncprov==1)		//1	NC papel
			$tipo_doc = "CN"; 
		else if ($cod_tipo_ncprov==2)	//2	NC Electronica
			$tipo_doc = "NE"; 
		else if ($cod_tipo_ncprov==3)	//3	NC exenta papel
			$tipo_doc = "CN"; 
		else if ($cod_tipo_ncprov==4)	//4	NC exenta Electronica
			$tipo_doc = "NE"; 
		else
			$tipo_doc = "NC"; 
		
		$glosa = 'CH/'.$nro_cheque.' '.$alias.' '.'NC/'.$nro_documento;
		
		$monto_debe = 0;
		$monto_haber = $monto;
		$fecha = $this->formato_fecha($fecha);
		$cuenta = $this->formato_cuenta($cuenta_por_pagar);
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta.'"'.$this->separador);						// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);							// monto debe
		fwrite($handle, $monto_haber.$this->separador);							// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);									// codigo vendedor 
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '"'.$centro_costo.'"'.$this->separador);				// codigo centgro costo
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"CH"'.$this->separador);								// tipo documento
		fwrite($handle, $nro_cheque.$this->separador);							// nro documento
		fwrite($handle, '"'.$fecha_documento.'"'.$this->separador);				// fecha emision
		fwrite($handle, '"'.$fecha_documento.'"'.$this->separador);				// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo docto referencia
		fwrite($handle, $nro_documento.$this->separador);						// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno
		fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}	
	
	///////////////
	// INGRESOS
	function excedente_otro_ingreso($in_cod_ingreso_pago, $es_deposito_inmediato, $cod_doc_ingreso_pago) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		//suma los montos documentos
		$sql = "select sum(dip.MONTO_DOC) SUM_MONTO_DOC
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp
				where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		$sum_monto_doc = $result[0]['SUM_MONTO_DOC'];
		
		//suma los montos asignados
		$sql = "select sum(mda.MONTO_DOC_ASIGNADO) SUM_MONTO_ASIGNADO
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
				where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO 
				and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		$sum_monto_asignado = $result[0]['SUM_MONTO_ASIGNADO'];

				$monto_dif = $sum_monto_doc - $sum_monto_asignado;
		return $monto_dif; 
	}
	function excedente_otro_gasto($in_cod_ingreso_pago, $es_deposito_inmediato, $cod_doc_ingreso_pago) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		//suma los montos documentos
		$sql = "select sum(dip.MONTO_DOC) SUM_MONTO_DOC
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp
				where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		$sum_monto_doc = $result[0]['SUM_MONTO_DOC'];
		
		//suma los montos asignados MONTO_DOC_ASIGNADO
		$sql = "select sum(mda.MONTO_DOC_ASIGNADO) SUM_MONTO_ASIGNADO
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
				where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO 
				and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		$sum_monto_asignado_doc = $result[0]['SUM_MONTO_ASIGNADO'];

		//suma los montos asignados en INGRESO_PAGO_FACTURA
		$sql = "select sum(ipf.MONTO_ASIGNADO) SUM_MONTO_ASIGNADO
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
				where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO 
				and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		$sum_monto_asignado_fa = $result[0]['SUM_MONTO_ASIGNADO'];

		if ($es_deposito_inmediato =='S'){
			$sql = "select sum(mda.MONTO_DOC_ASIGNADO) SUM_MONTO_ASIGNADO
					from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
					where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
					and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
					and tdp.ES_DEPOSITO_INMEDIATO = 'N'
					and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO 
					and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA";
			$result = $db->build_results($sql);
			$sum_monto_asignado_doc_otro = $result[0]['SUM_MONTO_ASIGNADO'];
		}
		else {
			$sql = "select sum(mda.MONTO_DOC_ASIGNADO) SUM_MONTO_ASIGNADO
					from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
					where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
					and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
					and tdp.ES_DEPOSITO_INMEDIATO = 'S'
					and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO 
					and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA";
			$result = $db->build_results($sql);
			$sum_monto_asignado_doc_otro = $result[0]['SUM_MONTO_ASIGNADO'];
		}
		//$monto_dif = $sum_monto_asignado_fa - $sum_monto_asignado_doc - $sum_monto_doc;
		$monto_dif = $sum_monto_asignado_fa - $sum_monto_doc - $sum_monto_asignado_doc_otro;
		return $monto_dif; 
	}
	
		
	
	function export_ingresos($handle) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		
		// INGRESO_PAGO
		for($i=0; $i < $this->dws['dw_lista_ingreso_pago']->row_count(); $i++) {
			$in_cod_ingreso_pago = $this->dws['dw_lista_ingreso_pago']->get_item($i, 'IN_COD_INGRESO_PAGO');

			$sql = "select OTRO_INGRESO
							,OTRO_ANTICIPO
							,OTRO_GASTO
					from INGRESO_PAGO
					where COD_INGRESO_PAGO = $in_cod_ingreso_pago";
			$result = $db->build_results($sql);
			$OTRO_INGRESO = $result[0]['OTRO_INGRESO'];
			$OTRO_ANTICIPO = $result[0]['OTRO_ANTICIPO'];
			$OTRO_GASTO = $result[0]['OTRO_GASTO'];
			$monto_OTRO_GASTO = $OTRO_GASTO;
			if ($OTRO_INGRESO > 0) {
				$tipo_OTRO_INGRESO = 'OTRO_INGRESO';
				$monto_OTRO_INGRESO = $OTRO_INGRESO;
			}
			else {
				$tipo_OTRO_INGRESO = 'OTRO_ANTICIPO';
				$monto_OTRO_INGRESO = $OTRO_ANTICIPO;
			}
			
			// es_deposito_onmediato = 'S'
			$sql = "select count(*) CANT
					from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp
					where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
					and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
					and tdp.ES_DEPOSITO_INMEDIATO = 'S'";
			$result = $db->build_results($sql);
			$cant = $result[0]['CANT'];
			if ($cant > 0) {
				$this->send_pago_ingreso($handle, $in_cod_ingreso_pago, 'S');
				$this->send_factura_ingreso($handle, $in_cod_ingreso_pago, 'S');
				
				// otros ingresos o anticipos
				if ($monto_OTRO_INGRESO > 0) {
					$monto_dif = $this->excedente_otro_ingreso($in_cod_ingreso_pago, 'S', 0);
					$this->send_otro_ingreso($handle, $tipo_OTRO_INGRESO, $monto_dif, $in_cod_ingreso_pago);
					$monto_OTRO_INGRESO = $monto_OTRO_INGRESO - $monto_dif;
				}

				// otros gastos
				if ($monto_OTRO_GASTO > 0) {
					$monto_dif_otro_gasto = $this->excedente_otro_gasto($in_cod_ingreso_pago, 'S', 0);
					if ($monto_dif_otro_gasto > 0) {
						$this->send_otro_gasto($handle, $monto_dif_otro_gasto, $in_cod_ingreso_pago);
						$this->send_factura_ingreso_otro_gasto($handle, $in_cod_ingreso_pago, 'S', 0, $monto_dif_otro_gasto);
						$monto_OTRO_GASTO = $monto_OTRO_GASTO - $monto_dif_otro_gasto;
					}
				}
				
				$this->nro_comprobante++;
			}
			
			// es_deposito_onmediato = 'N'
			$sql = "select COD_DOC_INGRESO_PAGO
					from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp
					where dip.COD_INGRESO_PAGO = $in_cod_ingreso_pago
					and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
					and tdp.ES_DEPOSITO_INMEDIATO = 'N'";
			$result_no_dep_inmediato = $db->build_results($sql);
			for($j=0; $j < count($result_no_dep_inmediato); $j++) {
				$cod_doc_ingreso_pago = $result_no_dep_inmediato[$j]['COD_DOC_INGRESO_PAGO'];
				$this->send_pago_ingreso($handle, $in_cod_ingreso_pago, 'N', $cod_doc_ingreso_pago);
				$this->send_factura_ingreso($handle, $in_cod_ingreso_pago, 'N', $cod_doc_ingreso_pago);
				
				// otros ingresos o anticipos
				if ($monto_OTRO_INGRESO > 0) {
					$monto_dif = $this->excedente_otro_ingreso($in_cod_ingreso_pago, 'N', $cod_doc_ingreso_pago);
					$this->send_otro_ingreso($handle, $tipo_OTRO_INGRESO, $monto_dif, $in_cod_ingreso_pago);
					$monto_OTRO_INGRESO = $monto_OTRO_INGRESO - $monto_dif;
				}
					
				// otros gastos
				if ($monto_OTRO_GASTO > 0) {
					$monto_dif_otro_gasto = $this->excedente_otro_gasto($in_cod_ingreso_pago, 'N', $cod_doc_ingreso_pago);
					if ($monto_dif_otro_gasto > 0) {
						$this->send_otro_gasto($handle, $monto_dif_otro_gasto, $in_cod_ingreso_pago);
						$this->send_factura_ingreso_otro_gasto($handle, $in_cod_ingreso_pago, 'N', $cod_doc_ingreso_pago, $monto_dif_otro_gasto);
						$monto_OTRO_GASTO = $monto_OTRO_GASTO - $monto_dif_otro_gasto;
					}
				}
				
				$this->nro_comprobante++;
			}
			
			$cod_envio_ingreso_pago = $this->dws['dw_lista_ingreso_pago']->get_item($i, 'IN_COD_ENVIO_INGRESO_PAGO');
			$db->EXECUTE_SP('spu_envio_ingreso_pago', "'NRO_COMPROBANTE', $cod_envio_ingreso_pago, $this->nro_comprobante");
		}		
	}
	function send_pago_ingreso($handle, $cod_ingreso_pago, $es_deposito_inmediato, $cod_doc_ingreso_pago=0) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		// obtiene el proyecto_ingreso
		$sql = "select COD_PROYECTO_INGRESO
				from INGRESO_PAGO
				where COD_INGRESO_PAGO = $cod_ingreso_pago";
		$result = $db->build_results($sql);
		$cod_proyecto_ingreso = $result[0]['COD_PROYECTO_INGRESO'];
		
		// Recorre los documentos de pago y los envia
		$sql = "select dip.COD_TIPO_DOC_PAGO
					,dip.NRO_DOC
					,convert(varchar, dip.FECHA_DOC, 103) FECHA_DOC 
					,dip.MONTO_DOC
					,dip.COD_TIPO_DOC_PAGO
					,t.TIPO_DOCUMENTO					
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO t
				where dip.COD_INGRESO_PAGO = $cod_ingreso_pago
				and t.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and t.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)";
		$result = $db->build_results($sql);
		for ($i=0; $i < count($result); $i++) {
			$cod_tipo_doc_pago = $result[$i]['COD_TIPO_DOC_PAGO'];
			$sql = "select COD_CUENTA_CONTABLE
					from ITEM_PROYECTO_INGRESO i
					where i.COD_PROYECTO_INGRESO = $cod_proyecto_ingreso
					and i.COD_TIPO_DOC_PAGO = $cod_tipo_doc_pago";
			$result_cuenta = $db->build_results($sql);
			$cuenta = $result_cuenta[0]['COD_CUENTA_CONTABLE'];
			
			$monto_debe = $result[$i]['MONTO_DOC'];  
			$monto_haber = 0;
			
			// glosa
			// alias			
			$sql = "select E.ALIAS_CONTABLE
						,E.NOM_EMPRESA
						,E.RUT
						,E.COD_EMPRESA
				from INGRESO_PAGO I, EMPRESA E
				where I.COD_INGRESO_PAGO = $cod_ingreso_pago
				  and E.COD_EMPRESA = I.COD_EMPRESA";
			$result_alias = $db->build_results($sql);
			$alias = $result_alias[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result_alias[0]['NOM_EMPRESA'];
			$rut = $result_alias[0]['RUT'];
			$cod_empresa = $result_alias[0]['COD_EMPRESA'];
			
			if ($alias=='')
				$alias = substr($nom_empresa, 0, 20);
			
			//nro factura
			$sql = "select F.NRO_FACTURA
					from INGRESO_PAGO_FACTURA IPF, FACTURA F
					where IPF.COD_INGRESO_PAGO = $cod_ingreso_pago
				  	  and IPF.TIPO_DOC = 'FACTURA'
				  	  and F.COD_FACTURA = IPF.COD_DOC";
			$result_factura = $db->build_results($sql);
			if (count($result_factura) > 1)
				$nro_fas = "F/VARIAS";
			else
				$nro_fas = "F/".$result_factura[0]['NRO_FACTURA'];
				
			$glosa = "DEP.$cod_ingreso_pago $alias $nro_fas";
			
			$tipo_doc = $result[$i]['TIPO_DOCUMENTO'];
			$nro_doc = $result[$i]['NRO_DOC'];
			$fecha = $this->dws['dw_envio_softland']->get_item(0, 'FECHA_ENVIO_SOFTLAND');
			$fecha = $this->formato_fecha($fecha);
			$fecha_vencto = $fecha; 
			
			$tipo_docto_conciliacion = "";
			$nro_docto_conciliacion = "0";
			// para tipo docto DP no se envia el $tipo_doc
			if ($tipo_doc=='DP') { 
				$tipo_docto_conciliacion = "DP";
				$nro_docto_conciliacion = "7";
				$tipo_doc = '';
				$nro_doc = "0";
				$rut = "";
				$tipo_docto_referencia = '';
				$nro_docto_referencia = "0";
			}
			else if ($tipo_doc=='CC') {
				$fecha_doc = $result[$i]['FECHA_DOC'];
				$glosa = "CH FECHA $cod_ingreso_pago $alias $nro_fas";

				$sql = "select convert(varchar, ip.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO 
						from INGRESO_PAGO ip
						where ip.COD_INGRESO_PAGO = $cod_ingreso_pago";
				$result_fecha = $db->build_results($sql);
				$fecha_ingreso = $result_fecha[$i]['FECHA_INGRESO_PAGO'];
				$fecha = $this->formato_fecha($fecha_ingreso);
				$fecha_vencto = $this->formato_fecha($fecha_doc);

				$tipo_docto_referencia = $tipo_doc;
				$nro_docto_referencia = $nro_doc;
			}
			else if ($tipo_doc=="TB") {
				$glosa = "TRANSBANK $cod_ingreso_pago $alias $nro_fas";
				$tipo_docto_conciliacion = "";
				$nro_docto_conciliacion = "0";
				
				$tipo_docto_referencia = $tipo_doc;
				$nro_docto_referencia = $nro_doc;
			}
			else if ($tipo_doc=="FC") {
				$sql = "select MONTO_IVA
								,COD_TIPO_FAPROV
						from FAPROV
						where COD_EMPRESA = $cod_empresa
						  and NRO_FAPROV = $nro_doc";
				$result_faprov = $db->build_results($sql);
				$cod_tipo_faprov = $result_faprov[0]['COD_TIPO_FAPROV']; 
				$monto_iva = $result_faprov[0]['MONTO_IVA']; 
				if ($cod_tipo_faprov==self::K_FAPROV_ELECTRONICA) {
					if ($monto_iva == 0)
						$tipo_doc = 'FX';
					else
						$tipo_doc = 'FE';
				}
				else {
					if ($monto_iva == 0)
						$tipo_doc = 'FX';
					else
						$tipo_doc = 'FC';
				}
			}
			else {
				$tipo_docto_conciliacion = "";
				$nro_docto_conciliacion = "0";
				$tipo_docto_referencia = $tipo_doc;
				$nro_docto_referencia = $nro_doc;
				$tipo_doc = 'DP';
				$nro_doc = "7";
			}
			$cuenta = $this->formato_cuenta($cuenta);
			
			// caso cuando se paga cheque a fecha contra NV
			$sql = "select top 1 TIPO_DOC
					from INGRESO_PAGO_FACTURA
					where COD_INGRESO_PAGO = $cod_ingreso_pago";
			$result2 = $db->build_results($sql);
			$tipo_doc_que_paga = $result2[0]['TIPO_DOC'];
			if ($tipo_doc_que_paga == "NOTA_VENTA") {
				$fecha = $result[$i]['FECHA_DOC'];
				$fecha = $this->formato_fecha($fecha);
				$fecha_vencto = $fecha; 
				
				$tipo_docto_referencia = $tipo_doc;
				$nro_docto_referencia = $nro_doc;
			}
			//////////////
			
			// Envia los datos
			fwrite($handle, '"'.$cuenta.'"'.$this->separador);			// Cuenta contable
			fwrite($handle, $monto_debe.$this->separador);						// monto debe
			fwrite($handle, $monto_haber.$this->separador);						// monto haber
			fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
			fwrite($handle, '0'.$this->separador);									// equivalencia moneda
			fwrite($handle, '0'.$this->separador);									// monto al debe adicional
			fwrite($handle, '0'.$this->separador);									// monto al haber adicional
			fwrite($handle, '""'.$this->separador);									// condiciones de vta 
			fwrite($handle, '"'.$vendedor_softland.'"'.$this->separador);			// codigo vendedor
			fwrite($handle, '""'.$this->separador);									// codigo ubicacion
			fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
			fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
			fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
			fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
			fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
			fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
			fwrite($handle, '"'.$tipo_docto_conciliacion.'"'.$this->separador);									// tipo docto conciliacion 
			fwrite($handle, $nro_docto_conciliacion.$this->separador);									// nro docto conciliacion
			fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
			fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo documento
			fwrite($handle, $nro_doc.$this->separador);							// nro documento
			fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
			fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento		
			fwrite($handle, '"'.$tipo_docto_referencia.'"'.$this->separador);		// tipo docto referencia
			fwrite($handle, $nro_docto_referencia.$this->separador);									// nro docto referencia
			fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
			fwrite($handle, '0'.$this->separador);									// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
			fwrite($handle, '0'.$this->separador);									// monto 3 detalle libro "IVA"
			fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
			fwrite($handle, '0'.$this->separador);									// monto suma detalle libro
			fwrite($handle, '0'.$this->separador);									// numero documento desde 
			fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
			fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
		}
	}
	function send_factura_ingreso($handle, $cod_ingreso_pago, $es_deposito_inmediato, $cod_doc_ingreso_pago=0, $monto_otros_gastos=0){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		// Recorre los documentos pagados (FA o NV)
		$sql = "select ipf.TIPO_DOC
						,ipf.COD_DOC
						,mda.MONTO_DOC_ASIGNADO
						,ipf.MONTO_ASIGNADO
						,dip.COD_TIPO_DOC_PAGO
						,dip.NRO_DOC
						,convert(varchar, dip.FECHA_DOC, 103) FECHA_DOC 
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
				where dip.COD_INGRESO_PAGO = $cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO
				and mda.MONTO_DOC_ASIGNADO > 0 
				and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)
				order by dip.COD_INGRESO_PAGO";
		$result = $db->build_results($sql);
		for ($i=0; $i < count($result); $i++) {
			$tipo_doc = $result[$i]['TIPO_DOC'];
			$cod_doc = $result[$i]['COD_DOC'];
			$nro_cheque = $result[$i]['NRO_DOC'];
			$monto_asignado = $result[$i]['MONTO_DOC_ASIGNADO'];
			$cod_tipo_doc_pago = $result[$i]['COD_TIPO_DOC_PAGO'];
			
			if ($tipo_doc=='FACTURA') {
				$sql = "select F.NRO_FACTURA
								,F.RUT
								,F.COD_ESTADO_DOC_SII
								,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA 
								,C.COD_CENTRO_COSTO
								,C.COD_CUENTA_CONTABLE_POR_COBRAR				
						from FACTURA F, CENTRO_COSTO C
						where F.COD_FACTURA = $cod_doc
					  	  and C.COD_CENTRO_COSTO = dbo.f_fa_get_cc(F.COD_FACTURA)";
				$result_fa = $db->build_results($sql);
				$nro_doc_ref = $result_fa[0]['NRO_FACTURA']; 
				$rut = $result_fa[0]['RUT']; 
				$centro_costo = $result_fa[0]['COD_CENTRO_COSTO'];
				$cuenta_por_cobrar = $result_fa[0]['COD_CUENTA_CONTABLE_POR_COBRAR'];
				$cod_estado_doc_sii = $result_fa[0]['COD_ESTADO_DOC_SII'];
				if ($cod_estado_doc_sii==self::K_IMPRESA)
					$tipo_doc_ref = 'FA';
				else 
					$tipo_doc_ref= 'FV';
				$fecha_factura = $result_fa[0]['FECHA_FACTURA'];
			}
			else if ($tipo_doc=='NOTA_VENTA') {
				$sql = "select n.COD_NOTA_VENTA
							,e.RUT
							,convert(varchar, n.FECHA_NOTA_VENTA, 103) FECHA_NOTA_VENTA
							,C.COD_CENTRO_COSTO
							,C.COD_CUENTA_CONTABLE_POR_COBRAR				
						from NOTA_VENTA n, EMPRESA e, CENTRO_COSTO c
						where n.COD_NOTA_VENTA = $cod_doc
						and e.COD_EMPRESA = n.COD_EMPRESA
						and c.COD_CENTRO_COSTO = dbo.f_emp_get_cc(n.COD_EMPRESA)";

				$result_nv = $db->build_results($sql);
				$nro_doc_ref = $result_nv[0]['COD_NOTA_VENTA']; 
				$rut = $result_nv[0]['RUT']; 
				$centro_costo = $result_nv[0]['COD_CENTRO_COSTO'];
				$cuenta_por_cobrar = $result_nv[0]['COD_CUENTA_CONTABLE_POR_COBRAR'];
				$fecha_factura = $result_fa[0]['FECHA_FACTURA'];
				
				if ($cod_tipo_doc_pago==12)	// CH a fecha
					$tipo_doc_ref = 'CC';
				else if ($cod_tipo_doc_pago==5 || $cod_tipo_doc_pago==6)	// pago Transbank
					$tipo_doc_ref = 'TB';
				else
					$tipo_doc_ref = 'DP';
			}

			if ($cod_tipo_doc_pago==12)	{// CH a fecha
				$tipo_doc = 'CC';
			}
			else if ($cod_tipo_doc_pago==5 || $cod_tipo_doc_pago==6) {	// pago Transbank
				$tipo_doc = 'TB';
			}
			else {
				$tipo_doc = 'DP';
			}
			
			$monto_debe = 0;
			$monto_haber = $monto_asignado;
			$cuenta_por_cobrar = $this->formato_cuenta($cuenta_por_cobrar);
			
			$fecha = $this->dws['dw_envio_softland']->get_item(0, 'FECHA_ENVIO_SOFTLAND');
			$fecha = $this->formato_fecha($fecha);
			$fecha_vencto = $fecha; 
			
			// glosa
			// alias			
			$sql = "select E.ALIAS_CONTABLE
						,E.NOM_EMPRESA
						,convert(varchar, i.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO
				from INGRESO_PAGO I, EMPRESA E
				where I.COD_INGRESO_PAGO = $cod_ingreso_pago
				  and E.COD_EMPRESA = I.COD_EMPRESA";
			$result_alias = $db->build_results($sql);
			$alias = $result_alias[0]['ALIAS_CONTABLE'];
			$nom_empresa = $result_alias[0]['NOM_EMPRESA'];
			$fecha_ingreso_pago = $result_alias[0]['FECHA_INGRESO_PAGO'];
			
			if ($alias=='')
				$alias = substr($nom_empresa, 0, 20);
			
			//nro factura
			$sql = "select F.NRO_FACTURA
					from INGRESO_PAGO_FACTURA IPF, FACTURA F
					where IPF.COD_INGRESO_PAGO = $cod_ingreso_pago
				  	  and IPF.TIPO_DOC = 'FACTURA'
				  	  and F.COD_FACTURA = IPF.COD_DOC";
			$result_factura = $db->build_results($sql);
			if (count($result_factura) > 1)
				$nro_fas = "F/VARIAS";
			else
				$nro_fas = "F/".$result_factura[0]['NRO_FACTURA'];

			if ($tipo_doc=="CC") {
				$fecha = $result[$i]['FECHA_DOC'];
				$glosa = "CH FECHA $cod_ingreso_pago $alias $nro_fas";
				
				$fecha = $this->formato_fecha($fecha_ingreso_pago);
				$fecha_vencto = $fecha; 
			}
			else if ($tipo_doc=="TB") {
				$glosa = "TRANSBANK $cod_ingreso_pago $alias $nro_fas";
			}
			else
				$glosa = "DEP.$cod_ingreso_pago $alias $nro_fas";
			
			

			// Envia los datos
			fwrite($handle, '"'.$cuenta_por_cobrar.'"'.$this->separador);			// Cuenta contable
			fwrite($handle, $monto_debe.$this->separador);						// monto debe
			fwrite($handle, $monto_haber.$this->separador);						// monto haber
			fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
			fwrite($handle, '0'.$this->separador);									// equivalencia moneda
			fwrite($handle, '0'.$this->separador);									// monto al debe adicional
			fwrite($handle, '0'.$this->separador);									// monto al haber adicional
			fwrite($handle, '""'.$this->separador);									// condiciones de vta 
			fwrite($handle, '""'.$this->separador);			// codigo vendedor
			fwrite($handle, '""'.$this->separador);									// codigo ubicacion
			fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
			fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
			fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
			fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
			fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
			fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
			fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
			fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
			fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
			if ($tipo_doc=="CC" || $tipo_doc=="TB") {
				fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);					// tipo documento
				fwrite($handle, $nro_cheque.$this->separador);									// nro documento
			}
			else {
				fwrite($handle, '"DP"'.$this->separador);								// tipo documento
				fwrite($handle, $nro_doc_ref.$this->separador);									// nro documento
			}
			fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
			fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
			fwrite($handle, '"'.$tipo_doc_ref.'"'.$this->separador);				// tipo docto referencia
			fwrite($handle, $nro_doc_ref.$this->separador);						// nro docto referencia
			fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
			fwrite($handle, '0'.$this->separador);						// monto 1 detalle libro "AFECTO" 
			fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
			fwrite($handle, '0'.$this->separador);						// monto 3 detalle libro "IVA"
			fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
			fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
			fwrite($handle, '0'.$this->separador);					// monto suma detalle libro
			fwrite($handle, '0'.$this->separador);									// numero documento desde 
			fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
			fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
		}
	}
	function send_factura_ingreso_otro_gasto($handle, $cod_ingreso_pago, $es_deposito_inmediato, $cod_doc_ingreso_pago=0, $monto_otros_gastos){
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		// Recorre los documentos pagados (FA o NV)
		$sql = "select ipf.TIPO_DOC
						,ipf.COD_DOC
						,mda.MONTO_DOC_ASIGNADO
						,ipf.MONTO_ASIGNADO
				from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
				where dip.COD_INGRESO_PAGO = $cod_ingreso_pago
				and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
				and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
				and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO
				and mda.MONTO_DOC_ASIGNADO > 0 
				and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
				and ($cod_doc_ingreso_pago=0 or dip.COD_DOC_INGRESO_PAGO = $cod_doc_ingreso_pago)
				order by dip.COD_INGRESO_PAGO";
		$result = $db->build_results($sql);
		for ($i=0; $i < count($result); $i++) {
			$tipo_doc = $result[$i]['TIPO_DOC'];
			$cod_doc = $result[$i]['COD_DOC'];
			
			//otros gastos
			if ($monto_otros_gastos > 0) {
				$sql = "select sum(mda.MONTO_DOC_ASIGNADO) SUM_MONTO_DOC_ASIGNADO
						from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
						where dip.COD_INGRESO_PAGO = $cod_ingreso_pago
						and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
						and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
						and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO
						and mda.MONTO_DOC_ASIGNADO > 0 
						and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
						and ipf.COD_DOC = $cod_doc
						and ipf.TIPO_DOC = '$tipo_doc'";
				$result_asig_doc = $db->build_results($sql);
				
				$sql = "select sum(ipf.MONTO_ASIGNADO) SUM_MONTO_FA_ASIGNADO
						from DOC_INGRESO_PAGO dip, TIPO_DOC_PAGO tdp, MONTO_DOC_ASIGNADO mda, INGRESO_PAGO_FACTURA ipf
						where dip.COD_INGRESO_PAGO = $cod_ingreso_pago
						and tdp.COD_TIPO_DOC_PAGO = dip.COD_TIPO_DOC_PAGO
						and tdp.ES_DEPOSITO_INMEDIATO = '$es_deposito_inmediato'
						and mda.COD_DOC_INGRESO_PAGO = dip.COD_DOC_INGRESO_PAGO
						and mda.MONTO_DOC_ASIGNADO > 0 
						and ipf.COD_INGRESO_PAGO_FACTURA = mda.COD_INGRESO_PAGO_FACTURA
						and ipf.COD_DOC = $cod_doc
						and ipf.TIPO_DOC = '$tipo_doc'";
				$result_asig_fa = $db->build_results($sql);
				
				$dif = $result_asig_doc[$i]['SUM_MONTO_DOC_ASIGNADO'] - $result[$i]['SUM_MONTO_FA_ASIGNADO'];
				if ($monto_otros_gastos > $dif)
					$monto_asignado = $dif;
				else
					$monto_asignado = $monto_otros_gastos;
				$monto_otros_gastos = $monto_otros_gastos - $monto_asignado;
			

				if ($tipo_doc=='FACTURA') {
					$sql = "select F.NRO_FACTURA
									,F.RUT
									,F.COD_ESTADO_DOC_SII
									,convert(varchar, F.FECHA_FACTURA, 103) FECHA_FACTURA 
									,C.COD_CENTRO_COSTO
									,C.COD_CUENTA_CONTABLE_POR_COBRAR				
							from FACTURA F, CENTRO_COSTO C
							where F.COD_FACTURA = $cod_doc
						  	  and C.COD_CENTRO_COSTO = dbo.f_fa_get_cc(F.COD_FACTURA)";
					$result_fa = $db->build_results($sql);
					$nro_doc = $result_fa[0]['NRO_FACTURA']; 
					$rut = $result_fa[0]['RUT']; 
					$centro_costo = $result_fa[0]['COD_CENTRO_COSTO'];
					$cuenta_por_cobrar = $result_fa[0]['COD_CUENTA_CONTABLE_POR_COBRAR'];
					$cod_estado_doc_sii = $result_fa[0]['COD_ESTADO_DOC_SII'];
					if ($cod_estado_doc_sii==self::K_IMPRESA)
						$tipo_doc = 'FA';
					else 
						$tipo_doc = 'FV';
					$fecha_factura = $result_fa[0]['FECHA_FACTURA'];
				}
				else if ($tipo_doc=='NOTA_VENTA') {
					$nro_doc = $cod_doc;
					//************ que se hace aqui ???
				}
				$monto_debe = 0;
				$monto_haber = $monto_asignado;
				$cuenta_por_cobrar = $this->formato_cuenta($cuenta_por_cobrar);
				
				$fecha = $this->dws['dw_envio_softland']->get_item(0, 'FECHA_ENVIO_SOFTLAND');
				$fecha = $this->formato_fecha($fecha);
				$fecha_vencto = $fecha; 
				
				// glosa
				// alias			
				$sql = "select E.ALIAS_CONTABLE
							,E.NOM_EMPRESA
					from INGRESO_PAGO I, EMPRESA E
					where I.COD_INGRESO_PAGO = $cod_ingreso_pago
					  and E.COD_EMPRESA = I.COD_EMPRESA";
				$result_alias = $db->build_results($sql);
				$alias = $result_alias[0]['ALIAS_CONTABLE'];
				$nom_empresa = $result_alias[0]['NOM_EMPRESA'];
			
				if ($alias=='')
					$alias = substr($nom_empresa, 0, 20);
				
				//nro factura
				$sql = "select F.NRO_FACTURA
						from INGRESO_PAGO_FACTURA IPF, FACTURA F
						where IPF.COD_INGRESO_PAGO = $cod_ingreso_pago
					  	  and IPF.TIPO_DOC = 'FACTURA'
					  	  and F.COD_FACTURA = IPF.COD_DOC";
				$result_factura = $db->build_results($sql);
				if (count($result_factura) > 1)
					$nro_fas = "F/VARIAS";
				else
					$nro_fas = "F/".$result_factura[0]['NRO_FACTURA'];
					
				$glosa = "DEP.$cod_ingreso_pago $alias $nro_fas";
				
				
				// Envia los datos
				fwrite($handle, '"'.$cuenta_por_cobrar.'"'.$this->separador);			// Cuenta contable
				fwrite($handle, $monto_debe.$this->separador);						// monto debe
				fwrite($handle, $monto_haber.$this->separador);						// monto haber
				fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
				fwrite($handle, '0'.$this->separador);									// equivalencia moneda
				fwrite($handle, '0'.$this->separador);									// monto al debe adicional
				fwrite($handle, '0'.$this->separador);									// monto al haber adicional
				fwrite($handle, '""'.$this->separador);									// condiciones de vta 
				fwrite($handle, '""'.$this->separador);			// codigo vendedor
				fwrite($handle, '""'.$this->separador);									// codigo ubicacion
				fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
				fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
				fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
				fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
				fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
				fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
				fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
				fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
				fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
				fwrite($handle, '"DP"'.$this->separador);								// tipo documento
				fwrite($handle, '7'.$this->separador);									// nro documento
				fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
				fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
				fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);				// tipo docto referencia
				fwrite($handle, $nro_doc.$this->separador);						// nro docto referencia
				fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
				fwrite($handle, '0'.$this->separador);						// monto 1 detalle libro "AFECTO" 
				fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
				fwrite($handle, '0'.$this->separador);						// monto 3 detalle libro "IVA"
				fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
				fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
				fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
				fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
				fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
				fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
				fwrite($handle, '0'.$this->separador);					// monto suma detalle libro
				fwrite($handle, '0'.$this->separador);									// numero documento desde 
				fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
				fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
			}
		}
	}
	function send_otro_ingreso($handle, $tipo, $monto_haber, $cod_ingreso_pago){
		if ($monto_haber==0)
			return;
			
		if ($tipo=='OTRO_INGRESO') {
			$cuenta_otro_ingreso = $this->formato_cuenta($this->cod_cuenta_otro_ingreso);
			$glosa = "OTROS INGRESOS SEGUN INGRESO PAGO $cod_ingreso_pago";
			$tipo_doc = '';
			$nro_doc = '';
			$cc = $this->cc_otro_ingreso;
		}
		elseif ($tipo=='OTRO_ANTICIPO') {
			$cod_tipo_doc_pago = $result[$i]['COD_TIPO_DOC_PAGO'];
			$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
			$sql = "select COD_CUENTA_CONTABLE
							,convert(varchar, ip.FECHA_INGRESO_PAGO, 103) FECHA_INGRESO_PAGO 
					from INGRESO_PAGO ip, ITEM_PROYECTO_INGRESO i
					where ip.COD_INGRESO_PAGO = $cod_ingreso_pago
					  and i.COD_PROYECTO_INGRESO = ip.COD_PROYECTO_INGRESO
					  and i.COD_TIPO_DOC_PAGO = 9";	// anticipo
			$result_cuenta = $db->build_results($sql);
			$cuenta_otro_ingreso = $result_cuenta[0]['COD_CUENTA_CONTABLE'];
			$cuenta_otro_ingreso = $this->formato_cuenta($cuenta_otro_ingreso);
			$glosa = "ANTICIPO CLIENTE $cod_ingreso_pago";
			$tipo_doc = 'SC';
			$nro_doc = $cod_ingreso_pago;
			$fecha = $result_cuenta[0]['FECHA_INGRESO_PAGO'];
			$fecha = $this->formato_fecha($fecha);
			$fecha_vencto = $fecha; 
			
			// rut cliente
			$sql = "select  e.RUT
					from INGRESO_PAGO ip, EMPRESA e
					where ip.COD_INGRESO_PAGO = $cod_ingreso_pago
					  and e.COD_EMPRESA = ip.COD_EMPRESA";
			$result = $db->build_results($sql);
			$rut = $result[0]['RUT'];
			$cc = '""';
		}
		$monto_debe = 0;
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta_otro_ingreso.'"'.$this->separador);			// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);			// codigo vendedor
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, $cc.$this->separador);									// codigo centgro costo 
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);								// tipo documento
		fwrite($handle, $nro_doc.$this->separador);									// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);				// tipo docto referencia
		fwrite($handle, $nro_doc.$this->separador);						// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
		fwrite($handle, '0'.$this->separador);						// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);						// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);					// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	function send_otro_gasto($handle, $monto_debe, $cod_ingreso_pago){
		if ($monto_debe<=0)
			return;
			
		$cuenta_otro_gasto = $this->formato_cuenta($this->cod_cuenta_otro_gasto);
		$glosa = "OTROS GASTOS SEGUN INGRESO PAGO $cod_ingreso_pago";
		$tipo_doc = '';
		$nro_doc = 0;
		$monto_haber = 0;
		
		// Envia los datos
		fwrite($handle, '"'.$cuenta_otro_gasto.'"'.$this->separador);			// Cuenta contable
		fwrite($handle, $monto_debe.$this->separador);						// monto debe
		fwrite($handle, $monto_haber.$this->separador);						// monto haber
		fwrite($handle, '"'.$glosa.'"'.$this->separador);						// glosa movimiento
		fwrite($handle, '0'.$this->separador);									// equivalencia moneda
		fwrite($handle, '0'.$this->separador);									// monto al debe adicional
		fwrite($handle, '0'.$this->separador);									// monto al haber adicional
		fwrite($handle, '""'.$this->separador);									// condiciones de vta 
		fwrite($handle, '""'.$this->separador);			// codigo vendedor
		fwrite($handle, '""'.$this->separador);									// codigo ubicacion
		fwrite($handle, '""'.$this->separador);									// codigo concepto caja 
		fwrite($handle, '""'.$this->separador);									// codigo instrumento financiero 
		fwrite($handle, '0'.$this->separador);									// cantidad instrumento financiero
		fwrite($handle, '""'.$this->separador);									// codigo detalle de gasto 
		fwrite($handle, '0'.$this->separador);									// cantidad concepto gassto
		fwrite($handle, '""'.$this->separador);									// codigo centgro costo 
		fwrite($handle, '""'.$this->separador);									// tipo docto conciliacion 
		fwrite($handle, '0'.$this->separador);									// nro docto conciliacion
		fwrite($handle, '"'.$rut.'"'.$this->separador);							// codigo auxiliar
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);								// tipo documento
		fwrite($handle, $nro_doc.$this->separador);									// nro documento
		fwrite($handle, '"'.$fecha.'"'.$this->separador);						// fecha emision
		fwrite($handle, '"'.$fecha_vencto.'"'.$this->separador);				// fecha vencimiento
		fwrite($handle, '"'.$tipo_doc.'"'.$this->separador);				// tipo docto referencia
		fwrite($handle, ''.$this->separador);						// nro docto referencia
		fwrite($handle, '""'.$this->separador);									// nro correlativo interrno 
		fwrite($handle, '0'.$this->separador);						// monto 1 detalle libro "AFECTO" 
		fwrite($handle, '0'.$this->separador);									// monto 2 detalle libro "EXENTO" 
		fwrite($handle, '0'.$this->separador);						// monto 3 detalle libro "IVA"
		fwrite($handle, '0'.$this->separador);									// monto 4 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 5 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 6 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 7 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 8 detalle libro 
		fwrite($handle, '0'.$this->separador);									// monto 9 detalle libro
		fwrite($handle, '0'.$this->separador);					// monto suma detalle libro
		fwrite($handle, '0'.$this->separador);									// numero documento desde 
		fwrite($handle, '0'.$this->separador);									// numerro documento hasta 
		fwrite($handle, $this->nro_comprobante."\r\n");			// nro comprobante
	}
	///////////////
	function export_comprobantes() {
		$this->separador = ',';
		$cod_tipo_envio = $this->dws['dw_envio_softland']->get_item(0, 'COD_TIPO_ENVIO');
		$this->nro_comprobante = $this->dws['dw_envio_softland']->get_item(0, 'NRO_COMPROBANTE');
		
		if ($cod_tipo_envio==1)		// ventas
			$name_archivo = "FACTURA_VENTAS_".$this->nro_comprobante.".TXT";
		else if ($cod_tipo_envio==2) {		// compras
			$name_archivo = "FACTURA_COMPRAS_".$this->nro_comprobante.".TXT";
		}
		else if ($cod_tipo_envio==3) {		// egreso
			$name_archivo = "EGRESOS_".$this->nro_comprobante.".TXT";
		}
		else if ($cod_tipo_envio==4) {		// ingreso
			$name_archivo = "INGRESOS_".$this->nro_comprobante.".TXT";
		}
		$fname = tempnam("/tmp", $name_archivo);
		$handle = fopen($fname,"w");
		
		if ($cod_tipo_envio==1)		// ventas
			$this->export_factura_nc($handle);
		else if ($cod_tipo_envio==2)		// compras
			$this->export_factura_nc_compras($handle);
		else if ($cod_tipo_envio==3)		// egresos
			$this->export_egresos($handle);
		else if ($cod_tipo_envio==4)		// ingresos
			$this->export_ingresos($handle);
			
		fclose($handle);
		header("Content-Type: application/force-download; name=\"$name_archivo\"");
		header("Content-Disposition: inline; filename=\"$name_archivo\"");
		$fh=fopen($fname, "rb");
		fpassthru($fh);
		//unlink($fname);		
	}
	function print_record() {
		$sel = $_POST['wi_hidden'];
		if ($sel=='auxiliar')
			$this->export_cliente();
		elseif ($sel=='comprobante')
			$this->export_comprobantes();
		else
			$this->error('Seleccion NO valida');
	}
}

// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_envio_softland.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wi_envio_softland extends wi_envio_softland_base {
		function wi_envio_softland($cod_item_menu) {
			parent::wi_envio_softland_base($cod_item_menu); 
		}
	}
}
?>