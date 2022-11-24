<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../../appl.ini");

class wo_nota_credito_base extends w_output {
	const K_ESTADO_SII_EMITIDA	= 1;
	const K_PARAM_MAX_IT_NC		= 40;
	const K_ESTADO_SII_ANULADA	= 4;
	const K_AUTORIZA_AGREGAR	= '993505';
	const K_AUTORIZA_CREAR_DESDE = '993510';
	const K_AUTORIZA_EXPORTAR = '993515';
	
	function wo_nota_credito_base() {
		$sql = "select NC.COD_NOTA_CREDITO
						,convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO
						,NC.FECHA_NOTA_CREDITO DATE_NOTA_CREDITO
						,NC.NRO_NOTA_CREDITO
						,NC.RUT
						,NC.DIG_VERIF
						,NC.NOM_EMPRESA
						,EDS.NOM_ESTADO_DOC_SII
						,TNC.NOM_TIPO_NOTA_CREDITO
						,NC.TOTAL_CON_IVA
						,F.NRO_FACTURA
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_NC
				from	NOTA_CREDITO NC LEFT OUTER JOIN TIPO_NOTA_CREDITO TNC ON NC.COD_TIPO_NOTA_CREDITO = TNC.COD_TIPO_NOTA_CREDITO 
										LEFT OUTER JOIN FACTURA F ON F.COD_FACTURA = NC.COD_DOC, 
						ESTADO_DOC_SII EDS
				where	NC.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII
				order by	isnull(NRO_NOTA_CREDITO, 9999999999) desc, COD_NOTA_CREDITO desc";
				
	
	     parent::w_output('nota_credito', $sql, $_REQUEST['cod_item_menu']);
	     $this->dw->add_control(new static_num('RUT'));
	     $this->dw->add_control(new static_num('TOTAL_CON_IVA'));
	     
		//tiene acceso al boton agregar NC
   		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_AGREGAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_add_visible = true;
      	}
      	else {
			$this->b_add_visible = false;
      	}
      	
		//tiene acceso al boton agregar NC
   		$priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_EXPORTAR, $this->cod_usuario);
		if ($priv=='E') {
			$this->b_export_visible = true;
      	}
      	else {
			$this->b_export_visible = false;
      	}

		// headers
		$this->add_header($control = new header_date('FECHA_NOTA_CREDITO', 'FECHA_NOTA_CREDITO', 'Fecha'));
		$control->field_bd_order = 'DATE_NOTA_CREDITO';
		$this->add_header(new header_num('NRO_NOTA_CREDITO', 'NRO_NOTA_CREDITO', 'Nº NC'));
		$this->add_header(new header_rut('RUT', 'NC', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'NC.NOM_EMPRESA', 'Razón Social'));
		$this->add_header(new header_num('NRO_FACTURA', 'F.NRO_FACTURA', 'Nº Factura'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$sql = "select COD_TIPO_NOTA_CREDITO, NOM_TIPO_NOTA_CREDITO from TIPO_NOTA_CREDITO order by	COD_TIPO_NOTA_CREDITO";
		$this->add_header(new header_drop_down('NOM_TIPO_NOTA_CREDITO', 'NC.COD_TIPO_NOTA_CREDITO', 'Tipo Docto. SII', $sql));
		$this->add_header(new header_num('TOTAL_CON_IVA', 'NC.TOTAL_CON_IVA', 'Total c/iva'));
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_NC 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_NC
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_NC";
		$this->add_header(new header_drop_down_string('TIPO_NC', '(select dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII))', 'Tipo NC', $sql));
	}

	function detalle_record_desde($modificar, $cant_nc_a_hacer) 
	{
		// No se llama al ancestro porque se reimplementa toda la rutina
		session::set("cant_nc_a_hacer", $cant_nc_a_hacer);

		// retrieve
		$this->set_count_output();
		$this->last_page = Ceil($this->row_count_output / $this->row_per_page);
		$this->set_current_page(0);
		$this->save_SESSION();

		$pag_a_mostrar=$cant_nc_a_hacer -1;

		$this->detalle_record($pag_a_mostrar);	// Se va al primer registro
	}
	
  	function crear_nc_from($valor_devuelto) 
  	{
  		//se maneja así porque se crea NC desde FA o GR
	  	list($opcion, $nro_factura)=split('[|]', $valor_devuelto);
	  	
	  	$cantidad_max = $this->get_parametro(self::K_PARAM_MAX_IT_NC);
		$cod_usuario = $this->cod_usuario;	
		if ($opcion=='desde_fa' || $opcion=='desde_fa_adm')
		{
				//crear la NC para todos los itemsFA que tengan pendiente por devolver
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				///valida que la FA exista
				$sql = "select COD_FACTURA, NRO_FACTURA, COD_ESTADO_DOC_SII from FACTURA where NRO_FACTURA = $nro_factura";
				$sql_fa_anulada = "select COD_FACTURA, NRO_FACTURA, COD_ESTADO_DOC_SII from FACTURA where NRO_FACTURA = $nro_factura and COD_ESTADO_DOC_SII <> ".self::K_ESTADO_SII_ANULADA;
				
				$result = $db->build_results($sql);
				$result_anulada = $db->build_results($sql_fa_anulada);
				if (count($result) == 0){
						$this->_redraw();
						$this->alert('La Factura Nº '.$nro_factura.' no existe.');								
						return;
				}elseif (count($result_anulada) == 0){
						$this->_redraw();
						$this->alert('La Factura Nº '.$nro_factura.' esta anulada, no se puede hacer nota de crédito.');								
						return;
				}else{
					$cod_factura = $result[0]['COD_FACTURA'];
				}	
						
				/* valida que la FA no tenga NCs anteriores en estado = emitida
				ya que es suceptible a errores tener varias NCs en estado emitida, ya que la cantidad por despachar 
				siempre será la misma cantidad de la FA.
				*/

				//el COD_DOC es igual al cod de la factura
				$sql = "select * from NOTA_CREDITO
							where COD_DOC = $cod_factura and
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
				$result = $db->build_results($sql);
				if (count($result) != 0)
				{
						$this->_redraw();
						$this->alert('La Factura Nº '.$nro_factura.' tiene Notas de Crédito pendiente(s) en estado emitida. Para poder generar más Notas de Crédito deberá imprimir los documentos emitidos.');						
						return;
				}
						
				
				/*********************************
				 *  if (fa es de rental ) => cod_tipo_factura = 2
				 * 		llamar a un sp_nc_fa_rental, es un sp nuevo debe crear la FA y un item TE para la golsa  y monto
				 * 		la NC debe quedar marcada como RENTAL, actualmente nose como => me tinca un nuevo campo es_rental ????
				 * else {
				 *  todo lo que ya esta 
				 * 
				 */
				///YA ESTA CREADA LA FUNCIO FALTA IMPLEMENTAR SELECT IS	
				
				if ($opcion=='desde_fa') {
					// valida que hayan item pendientes
					$sql = "select sum(dbo.f_fa_cant_por_nc(ITF.COD_ITEM_FACTURA, 'TODO_ESTADO')) POR_NC
					from ITEM_FACTURA ITF, FACTURA F
					where F.COD_FACTURA = $cod_factura and
						  ITF.COD_FACTURA = F.COD_FACTURA";
					
					$result = $db->build_results($sql);
					//echo $sql;
					$por_nc = $result[0]['POR_NC'];
					
					if ($por_nc <= 0)
					{
							$this->_redraw();
							$this->alert('Todos los ítems de la Factura Nº '.$nro_factura.', tienen Nota de Crédito.');								
							return;
					}
				  	
					//cuenta cuantos items hay
					$sql_cuenta="select count(*) CANTIDAD
								from ITEM_FACTURA ITF, FACTURA F
								where F.COD_FACTURA = $cod_factura and
								ITF.COD_FACTURA = F.COD_FACTURA";
					$result_cuenta = $db->build_results($sql_cuenta);
					$cantidad = $result_cuenta[0]['CANTIDAD'];
					$cant_nc_a_hacer=ceil($cantidad/$cantidad_max);

					$sp = 'sp_nc_crear_desde_fa';
					$param = "$cod_factura, $cod_usuario";
				}
				else if ($opcion=='desde_fa_adm') {
					$cant_nc_a_hacer = 1;
					$sp = 'sp_nc_crear_desde_fa_adm';
					$param = "$cod_factura, $cod_usuario";
				}
				$db->BEGIN_TRANSACTION();
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
					$this->detalle_record_desde(true,$cant_nc_a_hacer);
				}
				else { 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la nota crédito. Error en 'sp_nc_crear_desde_fa', favor contacte a IntegraSystem.");
				}
		
		}else if ($opcion=='desde_gr'){
				$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
				///valida que la FA exista
				$sql = "select cod_guia_recepcion, cod_doc from GUIA_RECEPCION where cod_guia_recepcion = $nro_factura";
				
				$result = $db->build_results($sql);
				if (count($result) == 0){
						$this->_redraw();
						$this->alert('La Guia de Recepcion  Nº '.$nro_factura.' no existe.');								
						return;
				}else{
					$cod_guia_recepcion = $result[0]['cod_guia_recepcion'];
				}
				
				/* valida que la FA no tenga NCs anteriores en estado = emitida
				ya que es suceptible a errores tener varias NCs en estado emitida, ya que la cantidad por despachar 
				siempre será la misma cantidad de la FA.
				*/

				//el COD_DOC es igual al cod de la Guia de Recepcion
				$sql = "select * from NOTA_CREDITO
							where COD_DOC = $cod_guia_recepcion and
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
				$result = $db->build_results($sql);
				if (count($result) != 0)
				{
						$this->_redraw();
						$this->alert('La Guia de Recepcion Nº '.$cod_guia_recepcion.' tiene Notas de Crédito pendiente(s) en estado emitida. Para poder generar más Notas de Crédito deberá imprimir los documentos emitidos.');
						return;
				}

				// valida que hayan item pendiente para nota credito
				$sql = "select sum(dbo.f_fa_cant_por_nc(COD_ITEM_GUIA_RECEPCION, 'TODO_ESTADO')) POR_NC
				from ITEM_GUIA_RECEPCION ITG, GUIA_RECEPCION GR
				where GR.COD_GUIA_RECEPCION = $cod_guia_recepcion
				and	  GR.COD_GUIA_RECEPCION = ITG.COD_GUIA_RECEPCION";
				$result = $db->build_results($sql);
				$por_recepcion = $result[0]['POR_NC'];
				
				if ($por_recepcion <= 0)
				{
						$this->_redraw();
						$this->alert('La Guia de Recepcion Nº '.$cod_guia_recepcion.' está totalmente en Nota Credito.');								
						return;
				}
				
				//cuenta cuantos items hay
				$sql_cuenta="select count(*) CANTIDAD
							from ITEM_GUIA_RECEPCION ITG, GUIA_RECEPCION GR
							where GR.COD_GUIA_RECEPCION = $cod_guia_recepcion
							and	  GR.COD_GUIA_RECEPCION = ITG.COD_GUIA_RECEPCION";
				$result_cuenta = $db->build_results($sql_cuenta);
				$cantidad = $result_cuenta[0]['CANTIDAD'];
				$cant_nc_a_hacer=ceil($cantidad/$cantidad_max);
				
				$db->BEGIN_TRANSACTION();
					
				$cod_usuario = $this->cod_usuario;	
							
				$sp = 'sp_nc_crear_desde';
				$param = "$cod_guia_recepcion, $cod_usuario";
				
					
				if ($db->EXECUTE_SP($sp, $param)) { 
					$db->COMMIT_TRANSACTION();
					$this->detalle_record_desde(true,$cant_nc_a_hacer);
				}
				else { 
					$db->ROLLBACK_TRANSACTION();
					$this->_redraw();
					$this->alert("No se pudo crear la Nota de Credito. Error en 'sp_fa_crear_desde_nv', favor contacte a IntegraSystem.");
				}
				
				/*para  probar que funcione  la variables */
				echo $cantidad.'---$cantidad---';
				echo $cant_nc_a_hacer.'--$cant_nc_a_hacer+++++++';
				echo 'hola - desde_gr';
				/*termina la variables*/
		}
	}
	
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_nc_from($_POST['wo_hidden']);
		else
			parent::procesa_event();		
	} 	
}
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wo_nota_credito.php";
if (file_exists($file_name)) 
	require_once($file_name);
else {
	class wo_nota_credito extends wo_nota_credito_base {
		function wo_nota_credito() {
			parent::wo_nota_credito_base(); 
		}
	}	
}
?>