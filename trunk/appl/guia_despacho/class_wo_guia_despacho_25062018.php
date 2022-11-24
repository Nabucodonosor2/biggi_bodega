<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_output_biggi.php");

class wo_guia_despacho extends w_output_biggi {
	const K_ESTADO_SII_EMITIDA 	= 1;
	const K_ESTADO_CONFIRMADA	= 4;
	const K_ESTADO_CERRADA		= 2;
	const K_TIPO_ARRIENDO		= 5;
   
	function wo_guia_despacho() {
		$sql = "select	GD.COD_GUIA_DESPACHO
						,GD.NRO_GUIA_DESPACHO
						,convert(varchar(20), GD.FECHA_GUIA_DESPACHO, 103) FECHA_GUIA_DESPACHO
						,GD.FECHA_GUIA_DESPACHO DATE_GUIA_DESPACHO
						,GD.RUT
						,GD.DIG_VERIF
						,GD.NOM_EMPRESA
						,EDS.COD_ESTADO_DOC_SII
						,EDS.NOM_ESTADO_DOC_SII
						,GD.COD_FACTURA
						,dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO) NRO_FACTURA
						,TGD.COD_TIPO_GUIA_DESPACHO
						,TGD.NOM_TIPO_GUIA_DESPACHO
						,GD.COD_DOC
						,dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII) TIPO_GD
			from 		GUIA_DESPACHO GD LEFT OUTER JOIN FACTURA F 
					ON GD.COD_FACTURA = F.COD_FACTURA
						,ESTADO_DOC_SII EDS
						,TIPO_GUIA_DESPACHO TGD
			where		GD.COD_ESTADO_DOC_SII = EDS.COD_ESTADO_DOC_SII  and
						GD.COD_TIPO_GUIA_DESPACHO = TGD.COD_TIPO_GUIA_DESPACHO and
						GD.COD_TIPO_GUIA_DESPACHO <> ".self::K_TIPO_ARRIENDO."
			order by	isnull(NRO_GUIA_DESPACHO, 9999999999) desc, COD_GUIA_DESPACHO desc";
			
		parent::w_output_biggi('guia_despacho', $sql, $_REQUEST['cod_item_menu']);
	
		$this->dw->add_control(new edit_nro_doc('COD_GUIA_DESPACHO','GUIA_DESPACHO'));
		$this->dw->add_control(new static_num('RUT'));
					
		// headers
		$this->add_header($control = new header_date('FECHA_GUIA_DESPACHO', 'FECHA_GUIA_DESPACHO', 'Fecha'));
		$control->field_bd_order = 'DATE_GUIA_DESPACHO';
		$this->add_header(new header_num('NRO_GUIA_DESPACHO', 'NRO_GUIA_DESPACHO', 'Nº GD'));
		$this->add_header(new header_rut('RUT', 'GD', 'Rut'));
		$this->add_header(new header_text('NOM_EMPRESA', 'GD.NOM_EMPRESA', 'Razón Social'));
		$sql_estado_doc_sii = "select COD_ESTADO_DOC_SII ,NOM_ESTADO_DOC_SII from ESTADO_DOC_SII order by	COD_ESTADO_DOC_SII";
		$this->add_header(new header_drop_down('NOM_ESTADO_DOC_SII', 'EDS.COD_ESTADO_DOC_SII', 'Estado', $sql_estado_doc_sii));
		$this->add_header($control = new header_num('NRO_FACTURA', '(dbo.f_gd_nros_factura(GD.COD_GUIA_DESPACHO))', 'Nº Factura'));
		$control->field_bd_order = 'NRO_FACTURA';
		
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by COD_TIPO_GUIA_DESPACHO";
		$this->add_header($control = new header_num('NRO_FACTURA', 'dbo.f_gd_nros_factura(COD_GUIA_DESPACHO)', 'Nº Factura'));
		$control->field_bd_order = 'NRO_FACTURA';
		$sql_tipo_guia_despacho = "select COD_TIPO_GUIA_DESPACHO ,NOM_TIPO_GUIA_DESPACHO from TIPO_GUIA_DESPACHO order by	COD_TIPO_GUIA_DESPACHO";

		$this->add_header(new header_drop_down('NOM_TIPO_GUIA_DESPACHO', 'TGD.COD_TIPO_GUIA_DESPACHO', 'Tipo Guía', $sql_tipo_guia_despacho));
		$this->add_header(new header_num('COD_DOC', 'GD.COD_DOC', 'N° Docto.'));
		$sql = "SELECT 'Sin tipo' ES_TIPO, 'Sin tipo' TIPO_GD 
				UNION 
				SELECT 'Papel' ES_TIPO , 'Papel' TIPO_GD
				UNION 
				SELECT 'Electrónica' ES_TIPO , 'Electrónica' TIPO_GD";

		$this->add_header($control = new header_drop_down_string('TIPO_GD', '(dbo.f_tipo_fa(EDS.NOM_ESTADO_DOC_SII))', 'Tipo GD', $sql));
  		$control->field_bd_order = 'TIPO_GD';
	}

	function crear_gd_from_nv($cod_nota_venta) {
		$db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
		///valida que la NV exista
		$sql = "select * from NOTA_VENTA where COD_NOTA_VENTA = $cod_nota_venta";
		$result = $db->build_results($sql);
		if (count($result) == 0){
			$this->_redraw();
			$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no existe.');								
			return;
		}
		
		//valida que la NV este confirmada
		$sql = "select * from NOTA_VENTA 
				where COD_NOTA_VENTA = ".$cod_nota_venta." 
				and	COD_ESTADO_NOTA_VENTA IN (".self::K_ESTADO_CONFIRMADA.", ".self::K_ESTADO_CERRADA.")";
		$result = $db->build_results($sql);
		if (count($result) == 0){
				$this->_redraw();
				$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' no esta confirmada.');								
				return;
		}
		
		/* valida que la NV no tenga GDs anteriores en estado = emitida
		ya que es suceptible a errores tener varias GD en estado emitida, ya que la cantidad por despachar siempre será la misma 
		cantidad de la NV.
		*/
		$sql = "select * from GUIA_DESPACHO
				where COD_DOC = $cod_nota_venta 
				and COD_TIPO_GUIA_DESPACHO <> ".self::K_TIPO_ARRIENDO."
				and COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA;
		$result = $db->build_results($sql);
		if (count($result) != 0){
			$this->_redraw();
			$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' tiene Guía(s) pendientes(s) en estado emitido. Para poder generar más guías deberá imprimir los documentos emitidos.');						
			return;
		}
		
		// valida que hayan item por despachar
		$sql = "select sum(dbo.f_nv_cant_por_despachar(IT.COD_ITEM_NOTA_VENTA, 'TODO_ESTADO')) POR_DESPACHAR
				from ITEM_NOTA_VENTA IT, NOTA_VENTA NV
				where NV.COD_NOTA_VENTA = $cod_nota_venta and
					  NV.COD_NOTA_VENTA = IT.COD_NOTA_VENTA";
		$result = $db->build_results($sql);
		$por_despachar = $result[0]['POR_DESPACHAR'];
		if ($por_despachar <= 0){
			//S  =  genera salida. es decir que la factura se toma  como Guia de Despacho.
			$sql = "select f.cod_factura,
							u.nom_usuario
					from factura f, usuario u
					where f.cod_doc = $cod_nota_venta
					and f.cod_estado_doc_sii = ".self::K_ESTADO_SII_EMITIDA."
					and f.genera_salida = 'S'
					and f.cod_usuario = u.cod_usuario";
			$result = $db->build_results($sql);
			$count_fa = count($result);
			$emisor = $result[0]['nom_usuario'];			
			
			if($count_fa == 0){
				$this->_redraw();
				$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' está totalmente despachada.');								
				return;
			}else{
				$this->_redraw();
				$this->alert('La Nota de Venta Nº '.$cod_nota_venta.' tiene asociada a una Factura que esta marcada como Genera Salida. \nEmisor: '.$emisor);							
				return;
			}
		}
	  	
		$db->BEGIN_TRANSACTION();
		
		$cod_usuario = $this->cod_usuario;			
		$sp = 'sp_gd_crear_desde_nv';
		$param = "$cod_nota_venta, $cod_usuario";
		
		if ($db->EXECUTE_SP($sp, $param)){ 
			$db->COMMIT_TRANSACTION();
			$this->detalle_record_desde(true);
		}
		else{ 
			$db->ROLLBACK_TRANSACTION();	
			$this->_redraw();
			$this->alert("No se pudo crear la guía de despacho. Error en 'sp_gd_crear_desde_nv', favor contacte a IntegraSystem.");
		}			
	}
	function procesa_event() {		
		if(isset($_POST['b_create_x']))
			$this->crear_gd_from_nv($_POST['wo_hidden']);
		else
			parent::procesa_event();
	}
}
?>