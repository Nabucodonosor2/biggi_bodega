<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");

// Esta clase es auxiliar y la idea es que todo el codigo que sea comun entre help_empresa.php y helpo_lista_empresa.php este en esta clase
class help_empresa {	
		
	const MAX_LISTA = 100;		// Cantidad máxima de empresas que se deben cargar en la lista
	const K_PARAM_CTA_CTE_COMERCIAL = 33;
	
	static function una_row($fields, $row) {
		/* Arma un string con todo el contenido cuando se selecciona una empresa desde la lista o cuando la busqueda dio por
			 resultado 1 registro
		*/
		$resp = '';
		for ($j=0; $j<count($fields); $j++)
			$resp .= $row[$fields[$j]->name]."|";

		$cod_empresa = $row['COD_EMPRESA'];
	
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);

		// sucursal factura
		$sql = "select COD_SUCURSAL
									,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION		
					  from	 SUCURSAL
					  where  COD_EMPRESA = ".$cod_empresa." and DIRECCION_FACTURA='S'";
		$result = $db->build_results($sql);
		$cod_sucursal_factura = count($result) > 0 ? $result[0]['COD_SUCURSAL'] : '';
		$direccion_factura = count($result) > 0 ? $result[0]['DIRECCION'] : '';
		$cod_sucursal = new drop_down_sucursal('COD_SUCURSAL_FACTURA');
		$resp .= $cod_sucursal->draw_entrable_help($cod_empresa, $cod_sucursal_factura)."|";
		$resp .= $direccion_factura."|";

		// sucursal despacho
		$sql = "select COD_SUCURSAL
									,dbo.f_get_direccion('SUCURSAL', COD_SUCURSAL, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION			
					  from	 SUCURSAL
					  where  COD_EMPRESA = ".$cod_empresa." and DIRECCION_DESPACHO='S'";
		$result = $db->build_results($sql);
		$cod_sucursal_despacho = count($result) > 0 ? $result[0]['COD_SUCURSAL'] : '';
		$direccion_despacho = count($result) > 0 ? $result[0]['DIRECCION'] : '';
		$cod_sucursal = new drop_down_sucursal('COD_SUCURSAL_DESPACHO');
		$resp .= $cod_sucursal->draw_entrable_help($cod_empresa, $cod_sucursal_despacho)."|";
		$resp .= $direccion_despacho."|";

		// personal
		$cod_persona = new drop_down_persona('COD_PERSONA');
		$resp .= $cod_persona->draw_entrable_help($cod_empresa)."|";
		
		// Se obtien la cuenta corriente asociada al cliente
		$sql = "select 	COD_CUENTA_CORRIENTE,
						NOM_CUENTA_CORRIENTE,
						NRO_CUENTA_CORRIENTE
				from CUENTA_CORRIENTE
				where COD_CUENTA_CORRIENTE = dbo.f_emp_get_cta_cte(".$cod_empresa.")";
		$result = $db->build_results($sql);
		if (count($result)==0) 
			$resp .= "|||";
		else {
			$resp .= $result[0]['COD_CUENTA_CORRIENTE']."|";
			$resp .= $result[0]['NOM_CUENTA_CORRIENTE']."|";
			$resp .= $result[0]['NRO_CUENTA_CORRIENTE']."|";
		}
		
		$resp = substr($resp, 0, strlen($resp) - 1);	// borra el ultimo caracter
		return $resp;	
	}
	static function find_empresa($cod_empresa, $rut, $alias, $nom_empresa, $tipo_empresa) {
		/* Esta funcion es llamada desde el ajax y busca las empresas que cumplan con los datos ingresados
			 Si el resultado de la busqueda es mayor a 1 retorna el sql para que se despliegue la ventana de selección de empresas
		*/
		if (!is_numeric($cod_empresa))	$cod_empresa = '';
		if (!is_numeric($rut))	$rut = '';

		if ($cod_empresa=='' && $rut=='' && $alias=='' && $nom_empresa=='') {
			$resp = "0|";
			print urlencode($resp);	
			return;
		}

		$sql_base = "SELECT 	 COD_EMPRESA,
										RUT,
										ALIAS,
										NOM_EMPRESA,
										DIG_VERIF,
										GIRO, 
										case SUJETO_A_APROBACION 
											when 'S' then 'SUJETO A APROBACION'
											else ''
										end SUJETO_A_APROBACION										
						FROM 	EMPRESA
						WHERE ";
						
		$where_tipo_empresa = '(';
		if (strpos($tipo_empresa, 'C')!==false)
			$where_tipo_empresa .= "(ES_CLIENTE = 'S') or ";
		if (strpos($tipo_empresa, 'P')!==false)
			$where_tipo_empresa .= "(ES_PROVEEDOR_INTERNO = 'S') or (ES_PROVEEDOR_EXTERNO = 'S') or ";
		if (strpos($tipo_empresa, 'T')!==false)
			$where_tipo_empresa .= "(ES_PERSONAL = 'S') or ";
		$where_tipo_empresa = substr($where_tipo_empresa, 0, strlen($where_tipo_empresa) - 3);	// borra "or "
	  $where_tipo_empresa .= ') and ';
	  $sql_base .= $where_tipo_empresa;
								
		if ($cod_empresa!='')
			$sql = $sql_base."(COD_EMPRESA = ".$cod_empresa.")";
		elseif ($rut!='')
			$sql = $sql_base."(RUT = ".$rut.")";
		elseif ($alias!='')
			$sql = $sql_base."(ALIAS like '".$alias."')";
		elseif ($nom_empresa!='')
			$sql = $sql_base."(NOM_EMPRESA like '".$nom_empresa."')";
					
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$db->query($sql);
		$count_rows = $db->count_rows();
		if ($count_rows==0) {
			// Busqueda con % (contiente) %
			if ($alias!='')
				$sql = $sql_base."(ALIAS like '%".$alias."%')";
			elseif ($nom_empresa!='')
				$sql = $sql_base."(NOM_EMPRESA like '%".$nom_empresa."%')";
			$db->query($sql);
			$count_rows = $db->count_rows();
		}

		if ($count_rows==0)
			$resp = "0|";
		elseif ($count_rows==1) {
			$row = $db->get_row();
			$resp = "1|";
			$fields = $db->get_fields();
			$resp .= help_empresa::una_row($fields, $row);
		}
		else
			$resp = $count_rows."|".$sql;
		
		print urlencode($resp);	
	}
	static function draw_htm_lista_empresa($sql) {
		/* Arma el html con la lista de empresas
			 $sql, es el sql con qle que se buscan las empresas
			 
			 Solo carga un máximo de registro definido en $MAX_LSTA
		*/
		
		$temp = new Template_appl('help_lista_empresa.htm');	

		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		$result = $db->build_results($sql, self::MAX_LISTA);
				
		if ($db->count_rows() > self::MAX_LISTA) {
			$temp->setVar("TIENE_MAS_REGISTROS", 'Se cargaron los primeros '.self::MAX_LISTA.' registos de un total de '.$db->count_rows().' registros.<br>Sea má especifico en los datos de búsqueda.');			
			$count = self::MAX_LISTA;
		}
		else {
			$temp->setVar("TIENE_MAS_REGISTROS", '');			
			$count = $db->count_rows();
		}
		
		$fields = $db->get_fields();
		for ($i=0 ; $i <$count; $i++) {
			$returnValue = '1|'.urlencode(help_empresa::una_row($fields, $result[$i]));

			$temp->gotoNext("EMPRESA");		

			if ($i % 2 == 0)
				$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_claro);
			else
				$temp->setVar("EMPRESA.DW_TR_CSS", datawindow::css_oscuro);

			for ($j=0; $j<count($fields); $j++) {
				if ($j==0)
					$temp->setVar("EMPRESA.".$fields[$j]->name, '<a href="#" onClick=" returnValue=\''.$returnValue.'\'; setWindowReturnValue(returnValue); window.close();">'.$result[$i][$fields[$j]->name].'</a>');			
				else
					$temp->setVar("EMPRESA.".$fields[$j]->name, $result[$i][$fields[$j]->name]);			
			}
		}
		print $temp->toString();
	}
}
?>