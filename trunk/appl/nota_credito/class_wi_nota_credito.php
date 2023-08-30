<?php
require_once(dirname(__FILE__)."/../../../../commonlib/trunk/php/auto_load.php");
require_once(dirname(__FILE__)."/../common_appl/class_w_cot_nv.php");
require_once(dirname(__FILE__)."/../empresa/class_dw_help_empresa.php");

class dw_item_nota_credito extends datawindow {
    const K_ESTADO_SII_EMITIDA 			= 1;
    
    function dw_item_nota_credito() {
        $sql = "SELECT ITNC.COD_ITEM_NOTA_CREDITO,
						ITNC.COD_NOTA_CREDITO,
						ITNC.ORDEN,
						ITNC.ITEM,
						ITNC.COD_PRODUCTO,
						ITNC.COD_PRODUCTO COD_PRODUCTO_OLD,
						ITNC.NOM_PRODUCTO,
						ITNC.CANTIDAD,
						ITNC.PRECIO,
						ITNC.COD_ITEM_DOC,
						dbo.f_fa_cant_por_nc(ITNC.COD_ITEM_DOC, default) CANTIDAD_POR_NC,
						case
							when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
							else 'none'
						end TD_DISPLAY_CANT_POR_NC_ITEM,
						case
							when NC.COD_DOC IS NULL then ''
							else 'none'
						end TD_DISPLAY_ELIMINAR_ITEM,
						NC.COD_DOC,
						COD_TIPO_TE,
						MOTIVO_TE,
						'' BOTON_PRECIO -- se utiliza en funcion comun js 'ingreso_TE'
				FROM    ITEM_NOTA_CREDITO ITNC, NOTA_CREDITO NC
				WHERE   NC.COD_NOTA_CREDITO = ITNC.COD_NOTA_CREDITO AND
					    ITNC.COD_NOTA_CREDITO = {KEY1}
				ORDER BY ORDEN";
        
        
        parent::datawindow($sql, 'ITEM_NOTA_CREDITO', true, true);
        
        $this->add_control(new edit_text_upper('COD_ITEM_NOTA_CREDITO',10, 10, 'hidden'));
        $this->add_control(new edit_text_upper('COD_DOC',10, 10, 'hidden'));
        $this->add_control(new edit_num('ORDEN',4, 10));
        $this->add_control(new edit_text_upper('ITEM',4, 5));
        $this->add_control($control = new edit_cantidad('CANTIDAD',12,10));
        $control->set_onChange("this.value = valida_ct_x_nc(this);");
        $this->add_control(new edit_text('COD_TIPO_TE',10, 100, 'hidden'));
        $this->add_control(new edit_text('MOTIVO_TE',10, 100, 'hidden'));
        $this->add_control(new edit_text('BOTON_PRECIO',10, 10, 'hidden'));
        $this->add_control(new static_num('CANTIDAD_POR_NC',1));
        
        $this->add_control(new computed('PRECIO', 0));
        $this->set_computed('TOTAL', '[CANTIDAD] * [PRECIO]');
        $this->accumulate('TOTAL', "calc_dscto();");
        $this->add_controls_producto_help();		// Debe ir despues de los computed donde esta involucrado precio, porque add_controls_producto_help() afecta el campos PRECIO
        
        // Agrega script adicional a COD_PRODUCTO
        $this->controls['COD_PRODUCTO']->set_onChange("change_item_nota_credito(this, 'COD_PRODUCTO');");
        
        $this->set_first_focus('COD_PRODUCTO');
        
        
        // asigna los mandatorys
        $this->set_mandatory('ORDEN', 'Orden');
        $this->set_mandatory('COD_PRODUCTO', 'Código del producto');
        $this->set_mandatory('CANTIDAD', 'Cantidad');
    }
    
    function insert_row($row=-1) {
        $row = parent::insert_row($row);
        $this->set_item($row, 'ITEM', $this->row_count());
        $this->set_item($row, 'ORDEN', $this->row_count() * 10);
        $this->set_item($row, 'TD_DISPLAY_CANT_POR_NC_ITEM', 'none');
        return $row;
    }
    
    function fill_record(&$temp, $record) {
        parent::fill_record($temp, $record);
        // si existe COD_DOC no despliega boton "-".
        $COD_DOC = $this->get_item(0, 'COD_DOC');
        if ($COD_DOC != ''){
            $row = $this->redirect($record);
            $eliminar = '<img src="../../../../commonlib/trunk/images/b_delete_line.jpg" onClick="del_line_item(\''.$this->label_record.'_'.$row.'\', \''.$this->nom_tabla.'\');" style="display:none">';
            $temp->setVar($this->label_record.".ELIMINAR_".strtoupper($this->label_record), $eliminar);
        }
    }
    
    function fill_template(&$temp) {
        parent::fill_template($temp);
        // si existe COD_DOC no despliega boton "+".
        
        if ($this->row_count()==0)
            $COD_DOC = '';		// debe ser == '' para que se agregue el boton "+"
            else
                $COD_DOC = $this->get_item(0, 'COD_DOC');
                if ($COD_DOC != ''){
                    $agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_item(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="display:none">';
                    $temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
                }
                
                if ($this->b_add_line_visible) {
                    if ($this->entrable){
                        if ($COD_DOC != '')
                            $agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_nc(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
                            else
                            {
                                $agregar = '<img src="../../../../commonlib/trunk/images/b_add_line.jpg" onClick="add_line_nc(\''.$this->label_record.'\', \''.$this->nom_tabla.'\');" style="cursor:pointer">';
                                $temp->setVar("AGREGAR_".strtoupper($this->label_record), $agregar);
                            }
                            
                    }
                    else
                        $agregar = '<img src="../../../../commonlib/trunk/images/b_add_line_d.jpg">';
                        
                }
                
    }
    
    
    
    function update($db)	{
        $sp = 'spu_item_nota_credito';
        
        for ($i = 0; $i < $this->row_count(); $i++){
            $statuts = $this->get_status_row($i);
            if ($statuts == K_ROW_NOT_MODIFIED || $statuts == K_ROW_NEW)
                continue;
                
                $COD_ITEM_NOTA_CREDITO		= $this->get_item($i, 'COD_ITEM_NOTA_CREDITO');
                $COD_NOTA_CREDITO 			= $this->get_item($i, 'COD_NOTA_CREDITO');
                $ORDEN 					= $this->get_item($i, 'ORDEN');
                $ITEM 					= $this->get_item($i, 'ITEM');
                $COD_PRODUCTO 			= $this->get_item($i, 'COD_PRODUCTO');
                $NOM_PRODUCTO 			= $this->get_item($i, 'NOM_PRODUCTO');
                $PRECIO 				= $this->get_item($i, 'PRECIO');
                $COD_ITEM_DOC			= $this->get_item($i, 'COD_ITEM_DOC');
                $CANTIDAD 				= $this->get_item($i, 'CANTIDAD');
                $COD_TIPO_TE			= $this->get_item($i, 'COD_TIPO_TE');
                $COD_TIPO_TE			= ($COD_TIPO_TE =='') ? "null" : "$COD_TIPO_TE";
                $MOTIVO_TE		 		= $this->get_item($i, 'MOTIVO_TE');
                $MOTIVO_TE		 		= ($MOTIVO_TE =='') ? "null" : "'".$MOTIVO_TE."'";
                
                if ($PRECIO=='') $PRECIO = 0;
                $COD_ITEM_NOTA_CREDITO   = ($COD_ITEM_NOTA_CREDITO=='') ? "null" : $COD_ITEM_NOTA_CREDITO;
                $COD_ITEM_DOC = ($COD_ITEM_DOC=='') ? "null" : $COD_ITEM_DOC;
                
                if ($statuts == K_ROW_NEW_MODIFIED)
                    $operacion = 'INSERT';
                    else if ($statuts == K_ROW_MODIFIED)
                        $operacion = 'UPDATE';
                        
                        $NOM_PRODUCTO = str_replace("'", "''", $NOM_PRODUCTO);
                        
                        $param = "'$operacion', $COD_ITEM_NOTA_CREDITO, $COD_NOTA_CREDITO, $ORDEN, '$ITEM', '$COD_PRODUCTO', '$NOM_PRODUCTO', $CANTIDAD, $PRECIO, $COD_ITEM_DOC, $COD_TIPO_TE, $MOTIVO_TE";
                        
                        if (!$db->EXECUTE_SP($sp, $param))
                            return false;
                            else {
                                if ($statuts == K_ROW_NEW_MODIFIED) {
                                    $COD_ITEM_NOTA_CREDITO = $db->GET_IDENTITY();
                                    $this->set_item($i, 'COD_ITEM_NOTA_CREDITO', $COD_ITEM_NOTA_CREDITO);
                                }
                            }
        }
        
        for ($i = 0; $i < $this->row_count('delete'); $i++) {
            $statuts = $this->get_status_row($i, 'delete');
            if ($statuts == K_ROW_NEW || $statuts == K_ROW_NEW_MODIFIED)
                continue;
                
                $COD_ITEM_NOTA_CREDITO = $this->get_item($i, 'COD_ITEM_NOTA_CREDITO', 'delete');
                if (!$db->EXECUTE_SP($sp, "'DELETE', $COD_ITEM_NOTA_CREDITO"))
                    return false;
        }
        return true;
    }
}


class dw_nota_credito extends dw_help_empresa {
    const K_ESTADO_SII_EMITIDA 			= 1;
    const K_ESTADO_SII_ANULADA			= 4;
    const K_PARAM_PORC_DSCTO_MAX 		= 26;
    
    function dw_nota_credito() {
        
        
        $sql = "SELECT	NC.COD_NOTA_CREDITO,
					NC.FECHA_REGISTRO,
					NC.COD_USUARIO,
					U.NOM_USUARIO,
					NC.NRO_NOTA_CREDITO,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO,
					convert(varchar(20), NC.FECHA_NOTA_CREDITO, 103) FECHA_NOTA_CREDITO_I,
					NC.COD_ESTADO_DOC_SII,
					EDS.NOM_ESTADO_DOC_SII,
					NC.COD_EMPRESA,
					NC.COD_SUCURSAL_FACTURA,
					dbo.f_get_direccion('NOTA_CREDITO', NC.COD_NOTA_CREDITO, '[DIRECCION]  [NOM_COMUNA] [NOM_CIUDAD] - TELEFONO: [TELEFONO] - FAX: [FAX]') DIRECCION_FACTURA,
					NC.COD_PERSONA,
					dbo.f_emp_get_mail_cargo_persona(NC.COD_PERSONA,  '[EMAIL]') MAIL_CARGO_PERSONA,
					NC.REFERENCIA,
					NC.OBS,
					NC.COD_BODEGA,
					NC.COD_TIPO_NOTA_CREDITO,
					NC.COD_DOC COD_DOC_H,
					NC.SUBTOTAL SUM_TOTAL,
					NC.TOTAL_NETO,
					NC.INGRESO_USUARIO_DSCTO1,
					NC.MONTO_DSCTO1,
					NC.PORC_DSCTO1,
					NC.PORC_DSCTO2,
					NC.INGRESO_USUARIO_DSCTO2,
					NC.MONTO_DSCTO2,
					NC.PORC_IVA,
					NC.MONTO_IVA,
					NC.TOTAL_CON_IVA,
					convert(varchar(20), NC.FECHA_ANULA, 103) +'  '+ convert(varchar(20), NC.FECHA_ANULA, 8) FECHA_ANULA,
					NC.MOTIVO_ANULA,
					NC.COD_USUARIO_ANULA,
					NC.RUT RUT_NC,
					NC.DIG_VERIF DIG_VERIF_NC,
					NC.NOM_EMPRESA NOM_EMPRESA_NC,
					NC.GIRO GIRO_NC,
					NC.NOM_SUCURSAL,
					E.ALIAS,
					NC.COD_CENTRO_COSTO,
					E.RUT,
					E.DIG_VERIF,
					E.NOM_EMPRESA,
					E.GIRO,
					NC.DIRECCION,
					NC.COD_CARGO,
					NC.TELEFONO,
					NC.FAX,
					NC.NOM_PERSONA,
					NC.MAIL,
					NC.COD_CARGO,
					NC.COD_USUARIO_IMPRESION,
					case NC.COD_ESTADO_DOC_SII
						when ".self::K_ESTADO_SII_ANULADA." then ''
						else 'none'
					end TR_DISPLAY 	,
					'' VISIBLE_DTE,
					case
						when NC.COD_DOC IS NULL then ''
						else 'none'
					end TD_DISPLAY_ELIMINAR,
					case
						when NC.COD_DOC IS not NULL and NC.COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_EMITIDA." then ''
						else 'none'
					end TD_DISPLAY_CANT_POR_NC,
					(select valor from parametro where cod_parametro=40 ) VALOR_NC_H,
					FA.NRO_FACTURA,
					NC.COD_MOTIVO_NOTA_CREDITO,
					NC.GENERA_ENTRADA,
					NC.COD_TIPO_NC_INTERNO_SII,
					MNC.NOM_MOTIVO_NOTA_CREDITO,
					TNC.NOM_TIPO_NOTA_CREDITO,
					ORIGEN_NC,
                    dbo.f_get_ip_from_nc(NRO_NOTA_CREDITO, 'S') NOM_INGRESO_PAGO,
                    dbo.f_get_ip_from_nc(NRO_NOTA_CREDITO, 'N') COD_INGRESO_PAGO
				FROM  NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA
					 ,USUARIO U
					 ,EMPRESA E
					 ,ESTADO_DOC_SII EDS
					 ,MOTIVO_NOTA_CREDITO MNC
					 ,TIPO_NOTA_CREDITO TNC
				WHERE NC.COD_NOTA_CREDITO = {KEY1} AND
					  NC.COD_USUARIO = U.COD_USUARIO AND
					  E.COD_EMPRESA = NC.COD_EMPRESA AND
					  EDS.COD_ESTADO_DOC_SII = NC.COD_ESTADO_DOC_SII AND
					  NC.COD_MOTIVO_NOTA_CREDITO = MNC.COD_MOTIVO_NOTA_CREDITO AND
					  NC.COD_TIPO_NOTA_CREDITO	= TNC.COD_TIPO_NOTA_CREDITO";
        
        parent::dw_help_empresa($sql);
        
        $this->add_control(new edit_text_hidden('COD_NOTA_CREDITO'));
        $this->add_control(new static_link('COD_INGRESO_PAGO', '../../../../commonlib/trunk/php/link_wi.php?modulo_origen=nota_credito&modulo_destino=ingreso_pago&cod_modulo_destino=[COD_INGRESO_PAGO]&cod_item_menu=2505&current_tab_page=0'));
        
        $this->add_control(new edit_text_hidden('COD_MOTIVO_NOTA_CREDITO'));
        $this->add_control(new edit_text_hidden('COD_TIPO_NOTA_CREDITO'));
        $this->add_control(new static_text('NOM_MOTIVO_NOTA_CREDITO'));
        $this->add_control(new static_text('NOM_TIPO_NOTA_CREDITO'));
        
        $this->add_control(new edit_nro_doc('NRO_NOTA_CREDITO','NOTA_CREDITO'));
        
        $this->add_control(new static_text('FECHA_NOTA_CREDITO_I'));
        $this->add_control($control = new edit_date('FECHA_NOTA_CREDITO'));
        $control->set_onChange("change_fecha();");
        
        /*$sql	= "select 	 COD_TIPO_NOTA_CREDITO
         ,NOM_TIPO_NOTA_CREDITO
         from 	 TIPO_NOTA_CREDITO
         order by COD_TIPO_NOTA_CREDITO";
         $this->add_control(new drop_down_dw('COD_TIPO_NOTA_CREDITO',$sql,150));
         $this->set_entrable('COD_TIPO_NOTA_CREDITO', true);
         */
        $sql	= "SELECT COD_TIPO_NC_INTERNO_SII
	  					 ,NOM_TIPO_NC_INTERNO_SII
	  			   FROM TIPO_NC_INTERNO_SII";
        $this->add_control($control = new drop_down_dw('COD_TIPO_NC_INTERNO_SII',$sql,150));
        $control->set_onChange("change_tipo_motivo_nc();");
        
        $this->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
        $this->add_control(new static_text('NOM_ESTADO_DOC_SII'));
        
        $this->add_control(new edit_text('COD_DOC_H',10,10, 'hidden'));
        $this->add_control(new static_text('NRO_FACTURA'));
        $this->add_control(new edit_text_upper('REFERENCIA',200, 100));
        /*$sql	= "select 	 COD_MOTIVO_NOTA_CREDITO
         ,NOM_MOTIVO_NOTA_CREDITO
         from 	 MOTIVO_NOTA_CREDITO
         order by COD_MOTIVO_NOTA_CREDITO";
         $this->add_control(new drop_down_dw('COD_MOTIVO_NOTA_CREDITO',$sql,150));*/
        $this->add_control(new edit_text_multiline('OBS',50,2));
        
        // usuario anulacion
        $sql = "select 	COD_USUARIO
						,NOM_USUARIO
				from USUARIO";
        
        $this->add_control(new drop_down_dw('COD_USUARIO_ANULA',$sql,150));
        $this->set_entrable('COD_USUARIO_ANULA', false);
        
        // campos duplicados
        $this->add_control(new static_num('RUT_NC'));
        $this->add_control(new static_text('DIG_VERIF_NC'));
        $this->add_control(new static_text('NOM_EMPRESA_NC'));
        $this->add_control(new static_text('GIRO_NC'));
        
        $this->add_control(new static_text('NOM_SUCURSAL'));
        $this->add_control(new static_text('NOM_PERSONA'));
        
        // asigna los mandatorys
        $this->set_mandatory('COD_ESTADO_DOC_SII', 'Estado');
        $this->set_mandatory('FECHA_NOTA_CREDITO', 'Fecha de Nota Credito');
        $this->set_mandatory('COD_EMPRESA', 'Empresa');
        $this->set_mandatory('COD_SUCURSAL_FACTURA', 'Sucursal de factura');
        $this->set_mandatory('COD_PERSONA', 'Persona');
        $this->set_mandatory('REFERENCIA', 'Referencia');
        //$this->set_mandatory('COD_MOTIVO_NOTA_CREDITO', 'Motivo NC');
        //$this->set_mandatory('COD_TIPO_NOTA_CREDITO', 'Tipo NC');
        
        $this->add_control(new edit_text('COD_CIUDAD',10, 100, 'hidden'));
        $this->add_control(new edit_text('COD_PAIS',10, 100, 'hidden'));
        
        $this->add_control(new edit_text('VALOR_NC_H',10, 10, 'hidden'));
        
    }
    
    function fill_record(&$temp, $record) {
        parent::fill_record($temp, $record);
        
        $COD_DOC = $this->get_item(0, 'COD_DOC_H');
        $COD_ESTADO_DOC_SII = $this->get_item(0, 'COD_ESTADO_DOC_SII');
        
        if (($COD_DOC != '') or ($COD_ESTADO_DOC_SII != 1))  //la NC viene desde FA, o estado <> emitida
            $temp->setVar('DISABLE_BUTTON', 'style="display:none"');
            else{
                if ($this->entrable)
                    $temp->setVar('DISABLE_BUTTON', '');
                    else
                        $temp->setVar('DISABLE_BUTTON', 'disabled="disabled"');
            }
    }
    
}

class wi_nota_credito_base extends w_cot_nv {
    const K_ESTADO_SII_EMITIDA 			= 1;
    const K_ESTADO_SII_IMPRESA			= 2;
    const K_ESTADO_SII_ENVIADA			= 3;
    const K_ESTADO_SII_ANULADA			= 4;
    const K_TIPO_NOTA_CREDITO_VENTA 	= 1;
    const K_PUEDE_ENVIAR_NC_DTE			= '993520';
    //const K_AUTORIZA_TIPO_NC	 		= '993525';
    const K_AUTORIZA_VISIBLE_BTN_DTE	= '993525';
    const K_AUTORIZA_MOD_TIPO_NC_INTERNO= '993530';
    const K_AUTORIZA_MOD_MONTO_DSCTO	= '993565';
    const K_IP_FTP		= 42;		// Direccion del FTP
    const K_USER_FTP	= 43;		//usuario para el FTP
    const K_PASS_FTP	= 44;		// password para el FTP
    
    //PARAM PARA LIBRE DTE.
    const K_PARAM_RUTEMISOR = 20;
    const K_PARAM_RZNSOC	= 6;
    const K_PARAM_GIROEMIS	= 21;
    const K_PARAM_DIRORIGEN	= 10;
    const K_PARAM_CMNAORIGEN= 70;
    const K_TIPO_DOC		= 61;//NC
    const K_ACTV_ECON		= 292510;// FORJA, PRENSADO, ESTAMPADO Y LAMINADO DE METAL; INCLUYE PULVIMETALURGIA
    
    const K_AUTORIZA_ENVIAR_DTE = '993540';
    const K_AUTORIZA_IMPRIMIR_DTE = '993545';
    const K_AUTORIZA_CONSULTAR_DTE = '993550';
    const K_AUTORIZA_XML_DTE = '993555';
    const K_AUTORIZA_REENVIAR_DTE='993570';
    
    const K_PARAM_HASH = 200;
    
    function wi_nota_credito_base($cod_item_menu) {
        
        parent::w_cot_nv('nota_credito', $cod_item_menu);
        $this->add_FK_delete_cascada('ITEM_NOTA_CREDITO');
        
        // tab nota credito
        // DATAWINDOWS NOTA_CREDITO
        $this->dws['dw_nota_credito'] = new dw_nota_credito();
        $this->add_controls_cot_nv();
        
        // tab items
        // DATAWINDOWS ITEMS NOTA_CREDITO
        $this->dws['dw_item_nota_credito'] = new dw_item_nota_credito();
        
        //auditoria Solicitado por IS.
        $this->add_auditoria('COD_ESTADO_DOC_SII');
        $this->add_auditoria('FECHA_NOTA_CREDITO');
        $this->add_auditoria('COD_EMPRESA');
        $this->add_auditoria('COD_TIPO_NOTA_CREDITO');
        $this->add_auditoria('COD_SUCURSAL_FACTURA');
        $this->add_auditoria('COD_PERSONA');
        $this->add_auditoria('PORC_IVA');
    }
    
    function new_record() {
        $this->b_delete_visible  = false; //cuando es un registro nuevo no muestra el boton eliminar
        
        $this->dws['dw_nota_credito']->insert_row();
        $this->dws['dw_nota_credito']->set_item(0, 'TR_DISPLAY', 'none');
        $this->dws['dw_nota_credito']->set_item(0, 'COD_USUARIO', $this->cod_usuario);
        $this->dws['dw_nota_credito']->set_item(0, 'NOM_USUARIO', $this->nom_usuario);
        
        $this->dws['dw_nota_credito']->set_item(0, 'COD_ESTADO_DOC_SII', self::K_ESTADO_SII_EMITIDA);
        $this->dws['dw_nota_credito']->set_item(0, 'NOM_ESTADO_DOC_SII', 'EMITIDA');
        $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
        
        $this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO',	false);
        
        $priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_TIPO_NC_INTERNO, $this->cod_usuario);
        if($priv == 'E')
            $this->dws['dw_nota_credito']->controls['COD_TIPO_NC_INTERNO_SII']->enabled = true;
            else
                $this->dws['dw_nota_credito']->controls['COD_TIPO_NC_INTERNO_SII']->enabled = false;
                
                $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
                $sql="select valor valor_nc from parametro where cod_parametro=40";
                $result = $db->build_results($sql);
                $valor_nc = $result[0]['valor_nc'];
                $this->dws['dw_nota_credito']->set_item(0, 'VALOR_NC_H', $valor_nc);
                $this->dws['dw_nota_credito']->set_item(0, 'TD_DISPLAY_CANT_POR_NC', 'none');
                $this->dws['dw_nota_credito']->set_item(0, 'GENERA_ENTRADA', 'N');
    }
    
    function load_record() {
        $cod_nota_credito = $this->get_item_wo($this->current_record, 'COD_NOTA_CREDITO');
        $this->dws['dw_nota_credito']->retrieve($cod_nota_credito);
        $cod_empresa = $this->dws['dw_nota_credito']->get_item(0, 'COD_EMPRESA');
        $this->dws['dw_nota_credito']->controls['COD_SUCURSAL_FACTURA']->retrieve($cod_empresa);
        $this->dws['dw_nota_credito']->controls['COD_PERSONA']->retrieve($cod_empresa);
        $this->dws['dw_item_nota_credito']->retrieve($cod_nota_credito);
        
        $COD_ESTADO_DOC_SII			= $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
        $COD_TIPO_NC_INTERNO_SII	= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NC_INTERNO_SII');
        
        unset($this->dws['dw_nota_credito']->controls['COD_TIPO_NC_INTERNO_SII']);
        $sql	= "SELECT COD_TIPO_NC_INTERNO_SII
	  					 ,NOM_TIPO_NC_INTERNO_SII
	  			   FROM TIPO_NC_INTERNO_SII
	  			   WHERE COD_TIPO_NC_INTERNO_SII =$COD_TIPO_NC_INTERNO_SII";
        $this->dws['dw_nota_credito']->add_control($control = new drop_down_dw('COD_TIPO_NC_INTERNO_SII',$sql,150));
        
        $this->b_print_visible 	 = true;
        $this->b_no_save_visible = true;
        $this->b_save_visible 	 = true;
        $this->b_modify_visible	 = true;
        $this->b_delete_visible  = true;
        
        $this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	,false);
        $this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, true);
        $this->dws['dw_nota_credito']->set_entrable('OBS'					, true);
        $this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, true);
        $this->dws['dw_nota_credito']->set_entrable('ALIAS'					, true);
        $this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, true);
        $this->dws['dw_nota_credito']->set_entrable('RUT'					, true);
        $this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'	, true);
        $this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, true);
        // aqui se dejan no modificables los datos del tab items
        $this->dws['dw_item_nota_credito']->set_entrable_dw(true);
        
        $this->dws['dw_nota_credito']->set_entrable('COD_TIPO_NC_INTERNO_SII'	, true);
        
        if($COD_TIPO_NC_INTERNO_SII == 1 || $COD_TIPO_NC_INTERNO_SII == 2) //Anulacion Factura Total
            $this->dws['dw_item_nota_credito']->controls['CANTIDAD']->readonly = true;
            else
                $this->dws['dw_item_nota_credito']->controls['CANTIDAD']->readonly = false;
                
                if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA) {
                    unset($this->dws['dw_nota_credito']->controls['COD_ESTADO_DOC_SII']);
                    $this->dws['dw_nota_credito']->add_control(new edit_text('COD_ESTADO_DOC_SII',10,10, 'hidden'));
                    $this->dws['dw_nota_credito']->controls['NOM_ESTADO_DOC_SII']->type = '';
                    
                    if($this->tiene_privilegio_opcion(self::K_PUEDE_ENVIAR_NC_DTE)){
                        $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', '');
                    }else{
                        $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
                    }
                    
                    $COD_DOC = $this->dws['dw_nota_credito']->get_item(0, 'COD_DOC_H');
                    if ($COD_DOC  != '') {
                        $this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
                        $this->dws['dw_nota_credito']->set_entrable('ALIAS'					, false);
                        $this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
                        $this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
                        $this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'   , false);
                        $this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);
                        
                        // aqui se dejan no modificables los datos del tab items
                        if(($COD_TIPO_NC_INTERNO_SII == 1)||($COD_TIPO_NC_INTERNO_SII == 2)||($COD_TIPO_NC_INTERNO_SII == 3)){
                            $this->dws['dw_item_nota_credito']->set_entrable('ORDEN'      		, false);
                            $this->dws['dw_item_nota_credito']->set_entrable('ITEM'      		, false);
                            $this->dws['dw_item_nota_credito']->set_entrable('COD_PRODUCTO'   	, false);
                            $this->dws['dw_item_nota_credito']->set_entrable('NOM_PRODUCTO'  	, false);
                        }
                        
                        // Es una FA desde NV por anticipo
                        if ($this->dws['dw_item_nota_credito']->row_count()==1) {
                            $cod_producto = $this->dws['dw_item_nota_credito']->get_item(0, 'COD_PRODUCTO');
                            $nom_producto = $this->dws['dw_item_nota_credito']->get_item(0, 'NOM_PRODUCTO');
                            if ($cod_producto=='TE' && $nom_producto=='__ADMINISTRATIVA__') {
                                $this->dws['dw_item_nota_credito']->set_item(0, 'COD_PRODUCTO', '');
                                $this->dws['dw_item_nota_credito']->set_item(0, 'NOM_PRODUCTO', '');
                                $this->dws['dw_item_nota_credito']->set_item(0, 'CANTIDAD_POR_NC', 1);
                                $this->dws['dw_item_nota_credito']->set_entrable('COD_PRODUCTO', true);
                                $this->dws['dw_item_nota_credito']->controls['COD_PRODUCTO']->set_onChange("change_item_nc_adm(this);");
                                $this->dws['dw_item_nota_credito']->set_entrable('NOM_PRODUCTO', true);
                                $this->dws['dw_item_nota_credito']->controls['NOM_PRODUCTO']->set_readonly(true);
                            }
                        }
                    }
                }
                else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_IMPRESA) {
                    $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
                    $sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_IMPRESA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
                    
                    unset($this->dws['dw_nota_credito']->controls['COD_ESTADO_DOC_SII']);
                    $this->dws['dw_nota_credito']->add_control($control = new drop_down_dw('COD_ESTADO_DOC_SII',$sql,150));
                    $control->set_onChange("mostrarOcultar_Anula(this);");
                    $this->dws['dw_nota_credito']->controls['NOM_ESTADO_DOC_SII']->type = 'hidden';
                    $this->dws['dw_nota_credito']->add_control(new edit_text_upper('MOTIVO_ANULA',110, 100));
                    
                    $this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	, true);
                    $this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('OBS'					, false);
                    $this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('ALIAS'				    , false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'  , false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);
                    
                    $this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO1'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO1'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO2'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO2'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('PORC_IVA'				, false);
                    
                    
                    // aqui se dejan no modificables los datos del tab items
                    $this->dws['dw_item_nota_credito']->set_entrable_dw(false);
                    
                    $this->b_delete_visible  = false;
                    
                }
                else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA) {
                    //SI USUARIO TIENE PRIVILEGIOS DE ENVIAR POR SEGUNDA VES LA NC-ELECTRONICA
                    if($this->tiene_privilegio_opcion(self::K_AUTORIZA_VISIBLE_BTN_DTE)){
                        $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', '');
                    }else{
                        $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
                    }
                    $this->b_print_visible 	 = false;
                    
                    $sql = "select 	COD_ESTADO_DOC_SII
							,NOM_ESTADO_DOC_SII
					from ESTADO_DOC_SII
					where COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ENVIADA." or
							COD_ESTADO_DOC_SII = ".self::K_ESTADO_SII_ANULADA."
					order by COD_ESTADO_DOC_SII";
                    
                    $this->dws['dw_nota_credito']->set_entrable('FECHA_NOTA_CREDITO'	, false);
                    $this->dws['dw_nota_credito']->set_entrable('REFERENCIA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('OBS'					, false);
                    $this->dws['dw_nota_credito']->set_entrable('NOM_EMPRESA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('ALIAS'				    , false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_EMPRESA'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('RUT'					, false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_SUCURSAL_FACTURA'  , false);
                    $this->dws['dw_nota_credito']->set_entrable('COD_PERSONA'			, false);
                    
                    $this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO1'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO1'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('PORC_DSCTO2'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('MONTO_DSCTO2'			, false);
                    $this->dws['dw_nota_credito']->set_entrable('PORC_IVA'				, false);
                    
                    
                    // aqui se dejan no modificables los datos del tab items
                    $this->dws['dw_item_nota_credito']->set_entrable_dw(false);
                    
                    $this->b_delete_visible  = false;
                    
                }
                else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ANULADA) {
                    $this->b_print_visible 	 = false;
                    $this->b_no_save_visible = false;
                    $this->b_save_visible 	 = false;
                    $this->b_modify_visible  = false;
                    $this->b_delete_visible  = false;
                    $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
                }
                
                //campos duplicados
                if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){ // estado = emitida
                    
                    $giro = $this->dws['dw_nota_credito']->get_item(0, 'GIRO');
                    
                    $this->dws['dw_nota_credito']->controls['RUT']->type = 'text';
                    $this->dws['dw_nota_credito']->controls['RUT_NC']->type = 'hidden';
                    
                    $this->dws['dw_nota_credito']->controls['DIG_VERIF']->type = 'text';
                    $this->dws['dw_nota_credito']->controls['DIG_VERIF_NC']->type = 'hidden';
                    
                    $this->dws['dw_nota_credito']->controls['NOM_EMPRESA']->type = 'text';
                    $this->dws['dw_nota_credito']->controls['NOM_EMPRESA_NC']->type = 'hidden';
                    
                    $this->dws['dw_nota_credito']->controls['GIRO']->type = '';
                    $this->dws['dw_nota_credito']->controls['GIRO_NC']->type = 'hidden';
                    
                    $this->dws['dw_nota_credito']->set_visible('COD_SUCURSAL_FACTURA', true);
                    $this->dws['dw_nota_credito']->controls['NOM_SUCURSAL']->type = 'hidden';
                    
                    $this->dws['dw_nota_credito']->set_visible('COD_PERSONA', true);
                    $this->dws['dw_nota_credito']->controls['NOM_PERSONA']->type = 'hidden';
                    
                }
                
                else{
                    $this->dws['dw_nota_credito']->controls['RUT']->type = 'hidden';
                    $this->dws['dw_nota_credito']->controls['RUT_NC']->type = '';
                    
                    $this->dws['dw_nota_credito']->controls['DIG_VERIF']->type = 'hidden';
                    $this->dws['dw_nota_credito']->controls['DIG_VERIF_NC']->type = '';
                    
                    $this->dws['dw_nota_credito']->controls['NOM_EMPRESA']->type = 'hidden';
                    $this->dws['dw_nota_credito']->controls['NOM_EMPRESA_NC']->type = '';
                    
                    $this->dws['dw_nota_credito']->controls['GIRO']->type = 'hidden';
                    $this->dws['dw_nota_credito']->controls['GIRO_NC']->type = '';
                    
                    $this->dws['dw_nota_credito']->set_visible('COD_SUCURSAL_FACTURA', false);
                    $this->dws['dw_nota_credito']->controls['NOM_SUCURSAL']->type = '';
                    
                    $this->dws['dw_nota_credito']->set_visible('COD_PERSONA', false);
                    $this->dws['dw_nota_credito']->controls['NOM_PERSONA']->type = '';
                    
                }
                if (session::is_set("cant_nc_a_hacer")){
                    $this->b_print_visible 	 = false;
                    $this->dws['dw_nota_credito']->set_item(0, 'VISIBLE_DTE', 'none');
                }
                
                $priv = $this->get_privilegio_opcion_usuario(self::K_AUTORIZA_MOD_MONTO_DSCTO, $this->cod_usuario);
                if ($priv=='E'){
                    $this->dws['dw_nota_credito']->controls['MONTO_DSCTO1']->readonly = false;
                    $this->dws['dw_nota_credito']->controls['MONTO_DSCTO2']->readonly = false;
                }else{
                    $this->dws['dw_nota_credito']->controls['MONTO_DSCTO1']->readonly = true;
                    $this->dws['dw_nota_credito']->controls['MONTO_DSCTO2']->readonly = true;
                }
                
                $ORIGEN_NC	= $this->dws['dw_nota_credito']->get_item(0, 'ORIGEN_NC');
                
                if($ORIGEN_NC == 'CREAR'){
                    unset($this->dws['dw_nota_credito']->controls['PORC_IVA']);
                    $this->dws['dw_nota_credito']->add_control($control = new drop_down_iva());
                    $control->set_onChange("computed(get_num_rec_field(this.id), 'MONTO_IVA');");
                }else if($ORIGEN_NC == 'CREAR_DESDE'){
                    unset($this->dws['dw_nota_credito']->controls['PORC_IVA']);
                    $porc_iva = $this->dws['dw_nota_credito']->get_item(0, 'PORC_IVA');
                    $porc_iva_s = number_format($porc_iva, 1, ',', '.');
                    $sql = "SELECT $porc_iva PORC_IVA
	   					  ,$porc_iva_s PORC_IVA_S";
                    $this->dws['dw_nota_credito']->add_control(new drop_down_dw('PORC_IVA',$sql,52, '', false));
                }
                
                if($COD_TIPO_NC_INTERNO_SII == 4){
                    $this->dws['dw_nota_credito']->set_item(0, 'TD_DISPLAY_CANT_POR_NC', 'none');
                    $this->dws['dw_nota_credito']->set_item(0, 'TD_DISPLAY_ELIMINAR', '');
                    
                    for($i=0 ; $i < $this->dws['dw_item_nota_credito']->row_count() ; $i++){
                        $this->dws['dw_item_nota_credito']->set_item($i, 'TD_DISPLAY_CANT_POR_NC_ITEM', 'none');
                        $this->dws['dw_item_nota_credito']->set_item($i, 'TD_DISPLAY_ELIMINAR_ITEM', '');
                    }
                }
    }
    
    function goto_record($record) {
        if (!session::is_set("cant_nc_a_hacer"))
            parent::goto_record($record);
            else {
                $cant_nc_a_hacer = session::get("cant_nc_a_hacer");
                $this->current_record = $record;
                $this->load_record();
                $this->modify_record();
                session::un_set("cant_nc_a_hacer");
                if ($cant_nc_a_hacer > 1)
                    $this->alert('Se harán '.$cant_nc_a_hacer.' Notas de Crédito de esta Factura.');
            }
    }
    
    function get_key() {
        return $this->dws['dw_nota_credito']->get_item(0, 'COD_NOTA_CREDITO');
    }
    
    function get_key_para_ruta_menu() {
        return $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
    }
    
    function save_record($db) {
        $COD_NOTA_CREDITO			= $this->get_key();
        $COD_USUARIO				= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO');
        $NRO_NOTA_CREDITO			= $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
        
        $FECHA_NOTA_CREDITO			= $this->dws['dw_nota_credito']->get_item(0, 'FECHA_NOTA_CREDITO');
        
        $COD_ESTADO_DOC_SII			= $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
        $COD_EMPRESA				= $this->dws['dw_nota_credito']->get_item(0, 'COD_EMPRESA');
        $COD_SUCURSAL_FACTURA		= $this->dws['dw_nota_credito']->get_item(0, 'COD_SUCURSAL_FACTURA');
        $COD_PERSONA				= $this->dws['dw_nota_credito']->get_item(0, 'COD_PERSONA');
        $REFERENCIA					= $this->dws['dw_nota_credito']->get_item(0, 'REFERENCIA');
        $OBS						= $this->dws['dw_nota_credito']->get_item(0, 'OBS');
        $COD_BODEGA					= $this->dws['dw_nota_credito']->get_item(0, 'COD_BODEGA');
        $COD_TIPO_NOTA_CREDITO		= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NOTA_CREDITO');
        $COD_DOC					= $this->dws['dw_nota_credito']->get_item(0, 'COD_DOC_H');
        $SUBTOTAL					= $this->dws['dw_nota_credito']->get_item(0, 'SUM_TOTAL');
        $TOTAL_NETO					= $this->dws['dw_nota_credito']->get_item(0, 'TOTAL_NETO');
        $PORC_DSCTO1				= $this->dws['dw_nota_credito']->get_item(0, 'PORC_DSCTO1');
        $PORC_DSCTO2				= $this->dws['dw_nota_credito']->get_item(0, 'PORC_DSCTO2');
        $INGRESO_USUARIO_DSCTO1		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO1');
        $MONTO_DSCTO1				= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO1');
        $INGRESO_USUARIO_DSCTO2		= $this->dws['dw_nota_credito']->get_item(0, 'INGRESO_USUARIO_DSCTO2');
        $MONTO_DSCTO2				= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_DSCTO2');
        $PORC_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'PORC_IVA');
        $MONTO_IVA					= $this->dws['dw_nota_credito']->get_item(0, 'MONTO_IVA');
        $TOTAL_CON_IVA				= $this->dws['dw_nota_credito']->get_item(0, 'TOTAL_CON_IVA');
        $MOTIVO_ANULA				= $this->dws['dw_nota_credito']->get_item(0, 'MOTIVO_ANULA');
        $COD_USUARIO_ANULA			= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO_ANULA');
        $COD_MOTIVO_NOTA_CREDITO	= $this->dws['dw_nota_credito']->get_item(0, 'COD_MOTIVO_NOTA_CREDITO');
        $GENERA_ENTRADA				= $this->dws['dw_nota_credito']->get_item(0, 'GENERA_ENTRADA');
        $COD_TIPO_NC_INTERNO_SII	= $this->dws['dw_nota_credito']->get_item(0, 'COD_TIPO_NC_INTERNO_SII');
        
        if (($MOTIVO_ANULA != '') && ($COD_USUARIO_ANULA == '')) // se anula
            $COD_USUARIO_ANULA			= $this->cod_usuario;
            else
                $COD_USUARIO_ANULA			= "null";
                
                $COD_USUARIO_IMPRESION		= $this->dws['dw_nota_credito']->get_item(0, 'COD_USUARIO_IMPRESION');
                
                $COD_NOTA_CREDITO			= ($COD_NOTA_CREDITO =='') ? "null" : $COD_NOTA_CREDITO;
                $NRO_NOTA_CREDITO			= ($NRO_NOTA_CREDITO =='') ? "null" : $NRO_NOTA_CREDITO;
                $OBS						= ($OBS =='') ? "null" : "'$OBS'";
                $COD_BODEGA					= ($COD_BODEGA =='') ? "null" : $COD_BODEGA;
                $COD_TIPO_NOTA_CREDITO		= ($COD_TIPO_NOTA_CREDITO =='') ? "null" : $COD_TIPO_NOTA_CREDITO;
                $COD_DOC					= ($COD_DOC =='') ? "null" : $COD_DOC;
                $SUBTOTAL 					= ($SUBTOTAL == '' ? 0: "$SUBTOTAL");
                $TOTAL_NETO = ($TOTAL_NETO == '' ? 0: "$TOTAL_NETO");
                $PORC_DSCTO1 = ($PORC_DSCTO1 == '' ? 0: "$PORC_DSCTO1");
                $PORC_DSCTO2 = ($PORC_DSCTO2 == '' ? 0: "$PORC_DSCTO2");
                $INGRESO_USUARIO_DSCTO1 	= ($INGRESO_USUARIO_DSCTO1 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO1'";
                $INGRESO_USUARIO_DSCTO2 	= ($INGRESO_USUARIO_DSCTO2 =='') ? "null" : "'$INGRESO_USUARIO_DSCTO2'";
                $MONTO_DSCTO1 				= ($MONTO_DSCTO1 == '' ? 0: "$MONTO_DSCTO1");
                $MONTO_DSCTO2 				= ($MONTO_DSCTO2 == '' ? 0: "$MONTO_DSCTO2");
                $PORC_IVA 					= ($PORC_IVA == '' ? 0: "$PORC_IVA");
                $MONTO_IVA 					= ($MONTO_IVA == '' ? 0: "$MONTO_IVA");
                $TOTAL_CON_IVA 				= ($TOTAL_CON_IVA == '' ? 0: "$TOTAL_CON_IVA");
                $MOTIVO_ANULA				= ($MOTIVO_ANULA =='') ? "null" : "'$MOTIVO_ANULA'";
                $COD_USUARIO_IMPRESION		= ($COD_USUARIO_IMPRESION =='') ? "null" : $COD_USUARIO_IMPRESION;
                
                
                $sp = 'spu_nota_credito';
                if ($this->is_new_record())
                    $operacion = 'INSERT';
                    else
                        $operacion = 'UPDATE';
                        
                        $param	= "'$operacion'
				,$COD_NOTA_CREDITO
				,$COD_USUARIO_IMPRESION
				,$COD_USUARIO
				,$NRO_NOTA_CREDITO
				,'$FECHA_NOTA_CREDITO'
				,$COD_ESTADO_DOC_SII
				,$COD_EMPRESA
				,$COD_SUCURSAL_FACTURA
				,$COD_PERSONA
				,'$REFERENCIA'
				,$OBS
				,$COD_BODEGA
				,$COD_TIPO_NOTA_CREDITO
				,$COD_DOC
				,$SUBTOTAL
				,$TOTAL_NETO
				,$PORC_DSCTO1
				,$PORC_DSCTO2
				,$INGRESO_USUARIO_DSCTO1
				,$MONTO_DSCTO1
				,$INGRESO_USUARIO_DSCTO2
				,$MONTO_DSCTO2
				,$PORC_IVA
				,$MONTO_IVA
				,$TOTAL_CON_IVA
				,$MOTIVO_ANULA
				,$COD_USUARIO_ANULA
				,$COD_MOTIVO_NOTA_CREDITO
				,'$GENERA_ENTRADA'
				,$COD_TIPO_NC_INTERNO_SII
				,NULL
				,NULL
				,NULL
				,'CREAR'";
                        
                        if ($db->EXECUTE_SP($sp, $param)){
                            if ($this->is_new_record()) {
                                $COD_NOTA_CREDITO = $db->GET_IDENTITY();
                                $this->dws['dw_nota_credito']->set_item(0, 'COD_NOTA_CREDITO', $COD_NOTA_CREDITO);
                            }
                            if (($MOTIVO_ANULA != 'null') && ($COD_USUARIO_ANULA != 'null')){ // se anula
                                $this->f_envia_mail('ANULADA');
                            }
                            for ($i=0; $i<$this->dws['dw_item_nota_credito']->row_count(); $i++)
                                $this->dws['dw_item_nota_credito']->set_item($i, 'COD_NOTA_CREDITO', $COD_NOTA_CREDITO);
                                
                                if (!$this->dws['dw_item_nota_credito']->update($db))
                                    return false;
                                    
                                    $parametros_sp = "'item_nota_credito','nota_credito',$COD_NOTA_CREDITO";
                                    if (!$db->EXECUTE_SP('sp_orden_no_parametricas', $parametros_sp))
                                        return false;
                                        
                                        $parametros_sp = "'RECALCULA', $COD_NOTA_CREDITO";
                                        if (!$db->EXECUTE_SP('spu_nota_credito', $parametros_sp))
                                            return false;
                                            
                                            return true;
                        }
                        
                        return false;
    }
    
    function print_record() {
        if (!$this->lock_record())
            return false;
            $cod_nota_credito = $this->get_key();
            $cod_tipo_doc_sii = 3;
            $cod_usuario_impresion = $this->cod_usuario;
            
            $nro_nota_credito = $this->dws['dw_nota_credito']->get_item(0, 'NRO_NOTA_CREDITO');
            $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
            if($nro_nota_credito == ''){
                $sql = "select dbo.f_get_nro_doc_sii ($cod_tipo_doc_sii , $cod_usuario_impresion) NRO_NOTA_CREDITO";
                $result = $db->build_results($sql);
                $nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
            }
            
            
            //declrar constante para que el monto con iva del reporte lo transpforme a palabras
            $sql = "select TOTAL_CON_IVA from NOTA_CREDITO where COD_NOTA_CREDITO = $cod_nota_credito";
            
            $resultado = $db->build_results($sql);
            $total_con_iva = $resultado [0] ['TOTAL_CON_IVA'] ;
            $total_en_palabras =  Numbers_Words::toWords($total_con_iva,"es");
            $total_en_palabras = strtr($total_en_palabras, "áéíóú", "aeiou");
            
            if ($nro_nota_credito == -1){
                $this->redraw();
                $this->dws['dw_nota_credito']->message("Sr(a). Usuario: Ud. no tiene documentos asignados, para imprimir la Nota de Crédito.");
                return false;
                
            }
            else{
                $db->BEGIN_TRANSACTION();
                $sp = 'spu_nota_credito';
                $param = "'PRINT', $cod_nota_credito, $cod_usuario_impresion";
                
                if ($db->EXECUTE_SP($sp, $param)) {
                    $db->COMMIT_TRANSACTION();
                    
                    $estado_nc_impresa = self::K_ESTADO_SII_IMPRESA;
                    $cod_estado_nc = $this->dws['dw_nota_credito']->get_item(0, 'COD_ESTADO_DOC_SII');
                    if ($cod_estado_nc != $estado_nc_impresa)//es la 1era vez que se imprime la Guia de Despacho
                        $this->f_envia_mail('IMPRESO');
                        
                        $sql= "SELECT NC.COD_NOTA_CREDITO,
								NC.NRO_NOTA_CREDITO,
								dbo.f_format_date(NC.FECHA_NOTA_CREDITO,3)FECHA_NOTA_CREDITO,
								NC.COD_USUARIO_IMPRESION,
								NC.REFERENCIA,
								NC.NOM_EMPRESA,
								NC.GIRO,
								NC.RUT,
								NC.DIG_VERIF,
								NC.DIRECCION,
								NC.TELEFONO,
								NC.FAX,
								NC.SUBTOTAL,
								NC.PORC_DSCTO1,
								NC.MONTO_DSCTO1,
								NC.PORC_DSCTO2,
								NC.MONTO_DSCTO2,
								NC.MONTO_DSCTO1 + NC.MONTO_DSCTO2 TOTAL_DSCTO,
								NC.TOTAL_NETO,
								NC.PORC_IVA,
								NC.MONTO_IVA,
								NC.TOTAL_CON_IVA,
								COM.NOM_COMUNA,
								CIU.NOM_CIUDAD,
								ITNC.ITEM,
								ITNC.CANTIDAD,
								ITNC.COD_PRODUCTO,
								ITNC.NOM_PRODUCTO,
								ITNC.PRECIO,
								ITNC.PRECIO * ITNC.CANTIDAD TOTAL_NC,
								'".$total_en_palabras."' TOTAL_EN_PALABRAS,
								NC.REFERENCIA,
								convert(varchar(5), GETDATE(), 8) HORA,
								FA.NRO_FACTURA,
								U.INI_USUARIO
						FROM 	NOTA_CREDITO NC LEFT OUTER JOIN FACTURA FA ON NC.COD_DOC = FA.COD_FACTURA
												LEFT OUTER JOIN COMUNA COM ON COM.COD_COMUNA = NC.COD_COMUNA
												LEFT OUTER JOIN CIUDAD CIU ON CIU.COD_CIUDAD = NC.COD_CIUDAD, ITEM_NOTA_CREDITO ITNC, USUARIO U
						WHERE 	NC.COD_NOTA_CREDITO = $cod_nota_credito
						AND		NC.COD_USUARIO = U.COD_USUARIO
						AND		ITNC.COD_NOTA_CREDITO = NC.COD_NOTA_CREDITO";
                        
                        // reporte
                        $labels = array();
                        $labels['strCOD_NOTA_CREDITO'] = $cod_nota_credito;
                        $file_name = $this->find_file('nota_credito', 'nota_credito.xml');
                        $rpt = new print_nota_credito($sql, $file_name, $labels, "Nota Crédito ".$cod_nota_credito.".pdf", 0);
                        $this->_load_record();
                        $this->b_delete_visible  = false;
                        return true;
                }
                else {
                    $db->ROLLBACK_TRANSACTION();
                    return false;
                }
            }
            $this->unlock_record();
    }
    
    
    // esta funcio envia mail  cuando se imprime e documento de guia despacho
    function f_envia_mail($estado_nota_credito){
        $cod_nota_credito = $this->get_key();
        $remitente = $this->nom_usuario;
        $cod_remitente = $this->cod_usuario;
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql = "SELECT NRO_NOTA_CREDITO FROM NOTA_CREDITO WHERE COD_NOTA_CREDITO = $cod_nota_credito";
        $result = $db->build_results($sql);
        $nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        // obtiene el mail de quien creo la tarea y manda el mail
        $sql_remitente = "SELECT MAIL from USUARIO where COD_USUARIO = $cod_remitente";
        
        $result_remitente = $db->build_results($sql_remitente);
        $mail_remitente = $result_remitente[0]['MAIL'];
        
        // Mail destinatarios
        $para_admin1 = 'mulloa@integrasystem.cl';
        $para_admin2 = 'mulloa@integrasystem.cl';
        /*
         $para_admin1 = 'mherrera@integrasystem.cl';
         $para_admin2 = 'imeza@integrasystem.cl';
         */
        
        if($estado_nota_credito == 'IMPRESO')
        {
            $asunto = 'Impresion de Nota de Credito N° '.$nro_nota_credito;
            $mensaje = 'Se ha <b>IMPRESO</b> la <b>Nota de Credito N° '.$nro_nota_credito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
        }
        
        if($estado_nota_credito == 'ANULADA')
        {
            $asunto = 'Anulacion de Nota de Credito N° '.$nro_nota_credito;
            $mensaje = 'Se ha <b>ANULADO</b> la <b>Nota de Credito N° '.$nro_nota_credito.'</b> por el usuario <b><i>'.$remitente.'<i><b>';
        }
        
        $cabeceras  = 'MIME-Version: 1.0' . "\n";
        $cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\n";
        $cabeceras .= 'From: '.$mail_remitente. "\n";
        //se comenta el envio de mail por q ya no es necesario => Vmelo.
        //mail($para_admin1, $asunto, $mensaje, $cabeceras);
        //mail($para_admin2, $asunto, $mensaje, $cabeceras);
        return 0;
    }
    
    function Envia_DTE($name_archivo, $fname){
        //SOLO para el CHAITEN
        /*	if (K_SERVER <> "192.168.2.26")
         return false;*/
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $sql_ftp =	"select dbo.f_get_parametro(".self::K_IP_FTP.") DIRECCION_FTP
								,dbo.f_get_parametro(".self::K_USER_FTP.")	USER_FTP
								,dbo.f_get_parametro(".self::K_PASS_FTP.")	PASS_FTP";
        
        $result_ftp = $db->build_results($sql_ftp);
        
        // datos de FTP Local
        $file_name_ftp = (dirname(__FILE__)."/../../ftp_dte.php");
        if (file_exists($file_name_ftp)){
            require_once($file_name_ftp);
            $K_DIRECCION_FTP	= K_DIRECCION_FTP;
            $K_USUARIO_FTP		= K_USUARIO_FTP;
            $K_PASSWORD_FTP		= K_PASSWORD_FTP;
            $K_PORT 			= 21;
        }else{
            //datos de FTP
            $K_DIRECCION_FTP	= $result_ftp[0]['DIRECCION_FTP'] ;	//Ip FTP
            $K_USUARIO_FTP		= $result_ftp[0]['USER_FTP'] ;		//Usuario FTP
            $K_PASSWORD_FTP		= $result_ftp[0]['PASS_FTP'] ;		//Password FTP
            $K_PORT 			= 21; 		// PUERTO DEL FTP
        }
        // establecer una conexion basica
        $conn_id = ftp_connect($K_DIRECCION_FTP);
        if ($conn_id===false)
            return false;
            
            // iniciar una sesion con nombre de usuario y contrasena
            $login_result = ftp_login($conn_id, $K_USUARIO_FTP, $K_PASSWORD_FTP);
            if($login_result === false)
                return false;
                
                ftp_pasv ($conn_id, true) ;
                // subir un archivo
                //$upload = ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY);
                if(!(ftp_put($conn_id, $name_archivo, $fname, FTP_BINARY)))
                    return false;
                    
                    // cerrar la conexion ftp
                    ftp_close($conn_id);
                    
                    return true;
    }
    
    
    function habilita_boton(&$temp, $boton, $habilita) {
        parent::habilita_boton($temp, $boton, $habilita);
        
        if($boton == 'enviar_dte'){
            if($habilita){
                $control = '<input name="b_enviar_dte" id="b_enviar_dte" src="../../images_appl/b_enviar_dte.jpg" type="image" '.
                    'onMouseDown="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_click.jpg\',1)" '.
                    'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
                    'onMouseOver="MM_swapImage(\'b_enviar_dte\',\'\',\'../../images_appl/b_enviar_dte_over.jpg\',1)"
							 onClick="var vl_tab = document.getElementById(\'wi_current_tab_page\'); if (TabbedPanels1 && vl_tab) vl_tab.value =TabbedPanels1.getCurrentTabIndex();
									 if (document.getElementById(\'b_save\')) {
										 if (validate_save()) {
										 		document.getElementById(\'wi_hidden\').value = \'save_enviar_dte\';
										 		document.getElementById(\'b_save\').click();
										 		return true;
										 	}
										 	else
										 		return false;
									 }
								 	 else
								 	 		return true;"/>';
            }else{
                $control = '<img src="../../images_appl/b_enviar_dte_d.jpg">';
            }
            
            $temp->setVar("WSWAP_ENVIA_DTE", $control);
        }
        if($boton == 'consultar_dte'){
            if($habilita){
                $control = '<input name="b_consultar_dte" id="b_consultar_dte" src="../../images_appl/b_consultar_dte.jpg" type="image" '.
                    'onMouseDown="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_click.jpg\',1)" '.
                    'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
                    'onMouseOver="MM_swapImage(\'b_consultar_dte\',\'\',\'../../images_appl/b_consultar_dte_over.jpg\',1)"
							 onClick="return true;"/>';
            }else{
                $control = '<img src="../../images_appl/b_consultar_dte_d.jpg">';
            }
            
            $temp->setVar("WSWAP_CONSULTAR_DTE", $control);
        }
        if($boton == 'imprimir_dte'){
            if($habilita){
                $control = '<input name="b_imprimir_dte" id="b_imprimir_dte" src="../../images_appl/b_reimprime_dte.jpg" type="image" '.
                    'onMouseDown="MM_swapImage(\'b_imprimir_dte\',\'\',\'../../images_appl/b_reimprime_dte_click.jpg\',1)" '.
                    'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
                    'onMouseOver="MM_swapImage(\'b_imprimir_dte\',\'\',\'../../images_appl/b_reimprime_dte_over.jpg\',1)"
							 onClick="return true;"/>';
            }else{
                $control = '<img src="../../images_appl/b_reimprime_dte_d.jpg">';
            }
            
            $temp->setVar("WSWAP_IMPRIMIR_DTE", $control);
        }
        if($boton == 'reenviar_dte'){
            if($habilita){
                $control = '<input name="b_reenviar_dte" id="b_reenviar_dte" src="../../images_appl/b_reenviar.jpg" type="image" '.
                    'onMouseDown="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_click.jpg\',1)" '.
                    'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
                    'onMouseOver="MM_swapImage(\'b_reenviar_dte\',\'\',\'../../images_appl/b_reenviar_over.jpg\',1)"
							 onClick="return true;"/>';
            }else{
                $control = '<img src="../../images_appl/b_reenviar_d.jpg">';
            }
            
            $temp->setVar("WSWAP_REENVIAR_DTE", $control);
        }
        if($boton == 'xml_dte'){
            if($habilita){
                $control = '<input name="b_xml_dte" id="b_xml_dte" src="../../images_appl/b_xml_dte.jpg" type="image" '.
                    'onMouseDown="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_click.jpg\',1)" '.
                    'onMouseUp="MM_swapImgRestore()" onMouseOut="MM_swapImgRestore()" '.
                    'onMouseOver="MM_swapImage(\'b_xml_dte\',\'\',\'../../images_appl/b_xml_dte_over.jpg\',1)"
							 onClick="return true;"/>';
            }else{
                $control = '<img src="../../images_appl/b_xml_dte_d.jpg">';
            }
            
            $temp->setVar("WSWAP_XML_DTE", $control);
        }
    }
    
    function navegacion(&$temp){
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        parent::navegacion($temp);
        
        $cod_nota_credito = $this->get_key();
        if($cod_nota_credito <> ""){
            $sql= "SELECT N.COD_ESTADO_DOC_SII
							,N.TRACK_ID_DTE
							,N.RESP_EMITIR_DTE
				    FROM NOTA_CREDITO N
					WHERE N.COD_NOTA_CREDITO = $cod_nota_credito ";
            $result = $db->build_results($sql);
            $COD_ESTADO_DOC_SII = $result[0]['COD_ESTADO_DOC_SII'];
            $TRACK_ID_DTE		= $result[0]['TRACK_ID_DTE'];
            $RESP_EMITIR_DTE	= $result[0]['RESP_EMITIR_DTE'];
        }
        
        if($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_EMITIDA){
            if($RESP_EMITIR_DTE == '' && $TRACK_ID_DTE == ''){ //ingresa por primera vez
                if($this->tiene_privilegio_opcion(self::K_AUTORIZA_ENVIAR_DTE)== 'S')
                    $this->habilita_boton($temp, 'enviar_dte', true);
                    else
                        $this->habilita_boton($temp, 'enviar_dte', false);
                        
            }else if($RESP_EMITIR_DTE <> '' && $TRACK_ID_DTE == ''){ //Reimprime
                $this->habilita_boton($temp, 'enviar_dte', false);
                
            }
            $this->habilita_boton($temp, 'imprimir_dte', false);
            $this->habilita_boton($temp, 'consultar_dte', false);
            $this->habilita_boton($temp, 'xml_dte', false);
            $this->habilita_boton($temp, 'reenviar_dte', false);
        }else if ($COD_ESTADO_DOC_SII == self::K_ESTADO_SII_ENVIADA){
            if($TRACK_ID_DTE <> ''){
                $this->habilita_boton($temp, 'enviar_dte', false);
                
                if($this->tiene_privilegio_opcion(self::K_AUTORIZA_CONSULTAR_DTE)== 'S')
                    $this->habilita_boton($temp, 'consultar_dte', true);
                    else
                        $this->habilita_boton($temp, 'consultar_dte', false);
                        
                        if($this->tiene_privilegio_opcion(self::K_AUTORIZA_XML_DTE)== 'S')
                            $this->habilita_boton($temp, 'xml_dte', true);
                            else
                                $this->habilita_boton($temp, 'xml_dte', false);
                                
                                if($this->tiene_privilegio_opcion(self::K_AUTORIZA_REENVIAR_DTE)== 'S')
                                    $this->habilita_boton($temp, 'reenviar_dte', true);
                                    else
                                        $this->habilita_boton($temp, 'reenviar_dte', false);
            }
            
            if($this->tiene_privilegio_opcion(self::K_AUTORIZA_IMPRIMIR_DTE)== 'S')
                $this->habilita_boton($temp, 'imprimir_dte', true);
                else
                    $this->habilita_boton($temp, 'imprimir_dte', false);
        }else{
            $this->habilita_boton($temp, 'enviar_dte', false);
            $this->habilita_boton($temp, 'imprimir_dte', false);
            $this->habilita_boton($temp, 'consultar_dte', false);
            $this->habilita_boton($temp, 'xml_dte', false);
            $this->habilita_boton($temp, 'reenviar_dte', false);
        }
    }
    
    function procesa_event() {
        if(isset($_POST['b_save_x'])) {
            if (isset($_POST['b_save'])) $this->current_tab_page = $_POST['b_save'];
            if ($this->_save_record()) {
                if ($_POST['wi_hidden']=='save_desde_print')		// Si el save es gatillado desde el boton print, se fuerza que se ejecute nuevamente el print
                    print '<script type="text/javascript"> document.getElementById(\'b_print\').click(); </script>';
                    elseif ($_POST['wi_hidden']=='save_desde_dte')		// Es es el codigo NUEVO
                    print '<script type="text/javascript"> document.getElementById(\'b_print_dte\').click(); </script>';
                    elseif ($_POST['wi_hidden']=='save_enviar_dte')		// Es es el save enviar_dte
                    print '<script type="text/javascript"> document.getElementById(\'b_enviar_dte\').click(); </script>';
            }
        }
        
        /*if(isset($_POST['b_print_dte_x']))
         $this->envia_NC_Electronica();
         else*/
        else if(isset($_POST['b_enviar_dte_x'])){
            $this->enviar_dte();
        }else if(isset($_POST['b_consultar_dte_x'])){
            $this->actualizar_estado_dte();
        }else if(isset($_POST['b_imprimir_dte_x'])){
            $this->imprimir_dte();
        }else if(isset($_POST['b_reenviar_dte_x'])){
            $this->reenviar_dte();
        }else if(isset($_POST['b_xml_dte_x'])){
            $this->xml_dte();
        }else
            parent::procesa_event();
    }
    
    function enviar_dte($reenviar = false){
        if (!$this->lock_record())
            return false;
            
            $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
            $cod_nota_credito = $this->get_key();
            
            $sql = "SELECT NRO_NOTA_CREDITO
				FROM NOTA_CREDITO
				WHERE COD_NOTA_CREDITO = $cod_nota_credito";
            $result = $db->build_results($sql);
            
            
            $sql_nc = "SELECT NRO_NOTA_CREDITO
					  ,TOTAL_CON_IVA TOTAL_CON_IVA_NC
					  ,(SELECT TOTAL_CON_IVA FROM FACTURA WHERE COD_FACTURA = NC.COD_DOC) TOTAL_CON_IVA_FA
				FROM NOTA_CREDITO NC
				WHERE COD_NOTA_CREDITO = $cod_nota_credito";
            $result_nc = $db->build_results($sql_nc);
            
            
            if($result[0]['NRO_NOTA_CREDITO'] <> '' && $reenviar == false)
                return false;
                
                
                if($result_nc[0]['TOTAL_CON_IVA_NC'] > $result_nc[0]['TOTAL_CON_IVA_FA']){
                    $this->_load_record();
                    print " <script>alert('Error al Emitir Dte, el total de la Nota de Crédito supera al monto de la Factura.');</script>";
                    return false;
                }
                
                if($reenviar){
                    //Llamamos a dte.
                    $dte = new dte();
                    
                    //Se le pasa como variable hash de la clase obtenida en parametros en la BD
                    $SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";
                    $Datos_Hash = $db->build_results($SqlHash);
                    $dte->hash = $Datos_Hash[0]['K_HASH'];
                    
                    $sql = "SELECT NRO_NOTA_CREDITO
						 ,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-5','') as RUTEMISOR
					FROM NOTA_CREDITO
					WHERE COD_NOTA_CREDITO = $cod_nota_credito";
                    
                    $result = $db->build_results($sql);
                    
                    $nro_nota_credito 	= $result[0]['NRO_NOTA_CREDITO'];
                    $tipo_doc = 61;
                    $rutemisor = $result[0]['RUTEMISOR'];
                    
                    //resuelve la cadena entrega
                    $objEnJson_genera = $dte->eliminar_dte($nro_nota_credito,$tipo_doc,$rutemisor);
                    if(trim($objEnJson_genera) <> 'true'){
                        //Llamamos al envio consultar estado documento.
                        $response = $dte->actualizar_estado($tipo_doc,$nro_nota_credito,$rutemisor);
                        $actualizar_estado = $dte->respuesta_actualizar_estado($response);
                        $revision_estado	= substr($actualizar_estado[6], 0, 3);
                        
                        if($revision_estado == 'EPR')
                            $estado_libre_dte = 'Aceptada';
                            else if($revision_estado == 'RPR')
                                $estado_libre_dte = 'Aceptado con Reparos';
                                else if($revision_estado == 'RLV')
                                    $estado_libre_dte = 'Aceptada con Reparos Leves';
                                    
                                    $this->_load_record();
                                    print "<script>alert('No se puede reenviar el DTE al SII pues su estado actual es: ".$estado_libre_dte."');</script>";
                                    return;
                    }
                }
                
                /*********************************INICIO VALIDA FACTURA EN SII****************************************/
                $sql = "SELECT COD_DOC COD_FACTURA
					 ,PORC_IVA
				FROM NOTA_CREDITO
				WHERE COD_NOTA_CREDITO = $cod_nota_credito";
                $result = $db->build_results($sql);
                $COD_FACTURA = $result[0]['COD_FACTURA'];
                
                if($COD_FACTURA == ""){
                    $this->_load_record();
                    print " <script>alert('Error al Emitir Dte, esta Nota de Crédito tiene que estar asociado a una Factura');</script>";
                    return;
                }
                
                $sql = "SELECT '33' DTE
	              		,F.NRO_FACTURA
	              		,REPLACE(REPLACE(dbo.f_get_parametro(20),'.',''),'-7','') as RUTEMISOR
				FROM FACTURA F
				WHERE F.COD_FACTURA = $COD_FACTURA";
                $consultar = $db->build_results($sql);
                
                $tipodte		= $consultar[0]['DTE'];
                $nro_factura	= $consultar[0]['NRO_FACTURA'];
                $rutemisor		= $consultar[0]['RUTEMISOR'];
                
                //Llamamos a dte.
                $dte = new dte();
                
                //Se le pasa como variable hash de la clase obtenida en parametros en la BD
                $SqlHash = "SELECT dbo.f_get_parametro(200) K_HASH";
                $Datos_Hash = $db->build_results($SqlHash);
                $dte->hash = $Datos_Hash[0]['K_HASH'];
                
                //Llamamos al envio consultar estado de socumento.
                $response = $dte->actualizar_estado($tipodte,$nro_factura,$rutemisor);
                $actualizar_estado	= $dte->respuesta_actualizar_estado($response);
                $revision_estado	= substr($actualizar_estado[6], 0, 3);

				//MH 01062022 SE MODIFICA PARA QUE VALIDE SOLO DESDE EL COD_FACTURA 29941
				//MH 01062022 DESDE EL 31052022 AL CERRAR BIGGI SE DEJA DE USAR LIBRE DTE INTEGRASYSTEM Y SE PASA A LIBRE DTE BY MH                
                if($revision_estado <> 'EPR' && $revision_estado <> 'RPR' && $revision_estado <> 'RLV' && $COD_FACTURA > 29941){//ultima factura signature cod_factura 12593
                    $this->_load_record();
                    print " <script>alert('Error al Emitir Dte, La factura debe estar Aceptada en el SII');</script>";
                    return;
                }
                /**********************************FIN VALIDA FACTURA EN SII***************************************/
                
                $sql = "SELECT (CAST(N.RUT AS NVARCHAR(8)))+'-'+(CAST (N.DIG_VERIF AS NVARCHAR(1))) as RUT_COMPLETO,
						N.NOM_EMPRESA,
						N.GIRO,
						N.DIRECCION,
						C.NOM_COMUNA,
						(SELECT NRO_FACTURA FROM FACTURA WHERE COD_FACTURA = N.COD_DOC) NRO_FA,
						(SELECT REPLACE(CONVERT(VARCHAR,FECHA_FACTURA,102),'.','-')F FROM FACTURA WHERE COD_FACTURA = N.COD_DOC) FECHA_FACTURA,
						N.OBS,
						N.REFERENCIA,
						N.COD_TIPO_NOTA_CREDITO,
						MONTO_DSCTO1,
              			MONTO_DSCTO2
				FROM NOTA_CREDITO N, COMUNA C
				WHERE N.COD_NOTA_CREDITO =$cod_nota_credito
				AND C.COD_COMUNA = N.COD_COMUNA";
                $contenido = $db->build_results($sql);
                
                $SqlDetalles = "SELECT ROW_NUMBER()OVER(ORDER BY ITC.ORDEN) AS NroLinDet,
								('INT1')AS TpoCodigo,
								ITC.NOM_PRODUCTO AS NmbItem,
								ITC.COD_PRODUCTO AS VlrCodigo,
								ITC.CANTIDAD,
								ITC.PRECIO,
								(ITC.CANTIDAD * ITC.PRECIO) AS MONTO_TOTAL
						FROM ITEM_NOTA_CREDITO ITC WHERE ITC.COD_NOTA_CREDITO = $cod_nota_credito
						ORDER BY ITC.ORDEN";
                $Detalles = $db->build_results($SqlDetalles);
                
                for($i = 0; $i < count($Detalles); $i++){
                    $NmbItem		= substr($Detalles[$i]['NmbItem'], 0, 80);
                    $VlrCodigo		= substr($Detalles[$i]['VlrCodigo'], 0, 35);
                    $CANTIDAD		= substr($Detalles[$i]['CANTIDAD'], 0, 18);
                    $PRECIO			= substr($Detalles[$i]['PRECIO'], 0, 18);
                    
                    $ad['Detalle'][$i]["NmbItem"]= utf8_encode(trim($NmbItem));
                    $ad['Detalle'][$i]["CdgItem"]= $VlrCodigo;
                    
                    if($contenido[0]['COD_TIPO_NOTA_CREDITO'] != 2){
                        $ad['Detalle'][$i]["PrcItem"]= $PRECIO;
                        $ad['Detalle'][$i]["QtyItem"]= $CANTIDAD;
                    }
                    
                    if($result[0]['PORC_IVA'] == 0){
                        $ad['Detalle'][$i]["IndExe"]= 1;
                    }
                }
                
                $RutRecep		= substr($contenido[0]['RUT_COMPLETO'], 0, 10);
                $RznSocRecep	= substr($contenido[0]['NOM_EMPRESA'], 0, 100);
                $GiroRecep		= substr($contenido[0]['GIRO'], 0, 40);
                $DirRecep		= substr($contenido[0]['DIRECCION'], 0, 70);
                $ComRecep		= substr($contenido[0]['NOM_COMUNA'], 0, 20);
                $DireccionC		= substr(str_replace("#","N",$DirRecep), 0, 70);
                $GiroRecep40	= substr($GiroRecep, 0, 40);
                $FolioRef		= substr($contenido[0]['NRO_FA'], 0, 18);
                $FchRef			= substr($contenido[0]['FECHA_FACTURA'], 0, 10);
                $RazonRef		= substr($contenido[0]['REFERENCIA'], 0, 90);
                $CodRef			= substr($contenido[0]['COD_TIPO_NOTA_CREDITO'], 0, 1);
                $DescuentoMonto1= substr($contenido[0]['MONTO_DSCTO1'], 0, 18);
                $DescuentoMonto2= substr($contenido[0]['MONTO_DSCTO2'], 0, 18);
                
                if($ComRecep == ''){
                    $this->_load_record();
                    print " <script>alert('Error al Emitir Dte, la empresa de la Nota Credito no tiene asignada Comuna.');</script>";
                    return;
                }
                
                $GiroRecep=ltrim(rtrim($GiroRecep));
                if($GiroRecep == ''){
                    $this->_load_record();
                    print " <script>alert('Error al Emitir Dte, la empresa de la Nota Credito no tiene Giro.');</script>";
                    return;
                }
                
                $SqlEmisor ="SELECT	REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.','') RUTEMISOR
							,dbo.f_get_parametro(".self::K_PARAM_RZNSOC.") RZNSOC
							,dbo.f_get_parametro(".self::K_PARAM_GIROEMIS.") GIROEMIS
							,dbo.f_get_parametro(".self::K_PARAM_DIRORIGEN.") DIRORIGEN
							,dbo.f_get_parametro(".self::K_PARAM_CMNAORIGEN.") CMNAORIGEN";
                $Datos_Emisor = $db->build_results($SqlEmisor);
                
                $rutemisor	= $Datos_Emisor[0]['RUTEMISOR'];
                $rznsoc		= $Datos_Emisor[0]['RZNSOC'];
                $giroemis	= $Datos_Emisor[0]['GIROEMIS'];
                $dirorigen	= $Datos_Emisor[0]['DIRORIGEN'];
                $cmnaorigen	= $Datos_Emisor[0]['CMNAORIGEN'];
                
                $a['Encabezado']['IdDoc']['TipoDTE']		= self::K_TIPO_DOC; //61 NC
                $a['Encabezado']['IdDoc']['Folio']			= 0; //el folio lo otrorga el sistema
                $a['Encabezado']['Emisor']['RUTEmisor']		= substr($rutemisor, 0, 10);
                $a['Encabezado']['Emisor']['RznSoc']		= utf8_encode(substr($rznsoc, 0, 100));
                $a['Encabezado']['Emisor']['GiroEmis']		= utf8_encode(substr($giroemis, 0, 80));
                $a['Encabezado']['Emisor']['Acteco']		= self::K_ACTV_ECON;
                $a['Encabezado']['Emisor']['DirOrigen']		= utf8_encode(substr($dirorigen, 0, 60));
                $a['Encabezado']['Emisor']['CmnaOrigen']	= utf8_encode(substr($cmnaorigen, 0, 20));
                $a['Encabezado']['Receptor']['RUTRecep']	= $RutRecep;
                $a['Encabezado']['Receptor']['RznSocRecep'] = utf8_encode($RznSocRecep);
                $a['Encabezado']['Receptor']['GiroRecep']	= utf8_encode($GiroRecep40);
                $a['Encabezado']['Receptor']['DirRecep']	= utf8_encode($DireccionC);
                $a['Encabezado']['Receptor']['CmnaRecep']	= utf8_encode($ComRecep);
                
                /**INI VALIDACION Nota de Crédito sin derecho a descontar débito**/
                
                $sql = "select DATEDIFF (month, FECHA_FACTURA, getdate()) +
                                CASE
                                    WHEN DATEPART(day, FECHA_FACTURA) < DATEPART(day, getdate()) THEN 1 ELSE 0
                                END DIF_FECHA
                        from FACTURA 
                        where COD_FACTURA  = $COD_FACTURA";
                
                $RESULT = $db->build_results($sql);
                $DIF_FECHA = $RESULT[0]['DIF_FECHA'];
                
                if($CodRef == 1 && $DIF_FECHA > 6){
                    $a['Encabezado']['IndNoRebaja']	= 1;
                }
                /**FIN VALIDACION Nota de Crédito sin derecho a descontar débito**/
                
                if($result[0]['PORC_IVA'] == 0)
                    $b['Referencia']['TpoDocRef']= '34';
                    else
                        $b['Referencia']['TpoDocRef']= '33';
                        
                        $b['Referencia']['FolioRef']= $FolioRef;
                        $b['Referencia']['FchRef']= $FchRef;
                        $b['Referencia']['CodRef']= $CodRef;
                        $b['Referencia']['RazonRef']= utf8_encode($RazonRef);
                        
                        $tiene_descuento = 'N';
                        if($DescuentoMonto1 <> 0){
                            $b['DscRcgGlobal'][0]['NroLinDR']	= 1;
                            $b['DscRcgGlobal'][0]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
                            $b['DscRcgGlobal'][0]['TpoValor']= '$';//Indica si es Porcentaje o Monto
                            $b['DscRcgGlobal'][0]['ValorDR']	= $DescuentoMonto1;
                            
                            $tiene_descuento = 'S';
                            //junta los arreglos en uno.
                        }
                        
                        if($DescuentoMonto2 <> 0){
                            $b['DscRcgGlobal'][1]['NroLinDR']	= 2;
                            $b['DscRcgGlobal'][1]['TpoMov']	= 'D'; //D(descuento) o R(recargo)
                            $b['DscRcgGlobal'][1]['TpoValor']= '$';//Indica si es Porcentaje o Monto
                            $b['DscRcgGlobal'][1]['ValorDR']	= $DescuentoMonto2;
                            
                            $tiene_descuento = 'S';
                            //junta los arreglos en uno.
                        }
                        //junta los arreglos en uno.
                        $resultado = array_merge($a,$ad,$b);
                        
                        //se agrega el json_para codificacion requerida por libre_dte.
                        $objEnJson = json_encode($resultado );
                        
                        //LLamo a la nueva clase dte.
                        $dte = new dte();
                        //Se le pasa como variable hash de la clase obtenida en parametros en la BD
                        $SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";
                        $Datos_Hash = $db->build_results($SqlHash);
                        $dte->hash = $Datos_Hash[0]['K_HASH'];
                        
                       	$Sql= "SELECT RESP_EMITIR_DTE
								    FROM NOTA_CREDITO
									WHERE COD_NOTA_CREDITO =$cod_nota_credito";
						$result_emitir = $db->build_results($Sql);
						$RESP_EMITIR_DTE	= $result_emitir[0]['RESP_EMITIR_DTE'];
				
						if(trim($RESP_EMITIR_DTE) <> ''){
							$this->_load_record();
						    print " <script>alert('Error al Emitir Dte, el documento ya tiene un intento de envío al SII.');</script>";
						    return;
						}
						
						$Sql= "select COD_LOG_CAMBIO
				                from LOG_CAMBIO 
				                where KEY_TABLA='$cod_nota_credito'
				                AND TIPO_CAMBIO='E'
				                and NOM_TABLA='NOTA_CREDITO'";
						$result_log = $db->build_results($Sql);
								
						if(count($result_log)>0){
						    $this->_load_record();
						    print " <script>alert('Error al Emitir Dte, el documento ya tiene un intento de envío al SII.');</script>";
						    return;
						}else{
						    
						    $sp = 'sp_log_cambio';
						    $param="'NOTA_CREDITO'
				                ,'$cod_nota_credito'
						        ,$this->cod_usuario
						        ,'E'";
						    
						    if (!$db->EXECUTE_SP($sp, $param)) {
								$this->_load_record();
							    print " <script>alert('Error al guardar Log Cambio.');</script>";
							    return;
						    }    
						}
						
						//envio json al la funcion de la clase dte.
						$response = $dte->post_emitir_dte($objEnJson);
						$response2 = str_replace("'", "''", $response);	// reemplaza ' por ''
                        
                        //Guarda el response de la funcion emitir_dte.
                        
                        $sp = 'spu_nota_credito';
                        $param = "'SAVE_EMITIR_DTE'			--@ve_operacion
								,$cod_nota_credito			--@ve_cod_nota_credito
								,NULL						--@ve_cod_usuario_impresion
								,NULL						--@ve_cod_usuario
								,NULL						--@ve_nro_nota_credito
								,NULL						--@ve_fecha_nota_credito
								,NULL						--@ve_cod_estado_doc_sii
								,NULL						--@ve_cod_empresa
								,NULL						--@ve_cod_sucursal_factura
								,NULL						--@ve_cod_persona
								,NULL						--@ve_referencia
								,NULL						--@ve_obs
								,NULL						--@ve_cod_bodega
								,NULL						--@ve_cod_tipo_nota_credito
								,NULL						--@ve_cod_doc
								,NULL						--@ve_subtotal
								,NULL						--@ve_total_neto
								,NULL						--@ve_porc_dscto1
								,NULL						--@ve_porc_dscto2
								,NULL						--@ve_ingreso_usuario_dscto1
								,NULL						--@ve_monto_dscto1
								,NULL						--@ve_ingreso_usuario_dscto2
								,NULL						--@ve_monto_dscto2
								,NULL						--@ve_porc_iva
								,NULL						--@ve_monto_iva
								,NULL						--@ve_total_con_iva
								,NULL						--@ve_motivo_anula
								,NULL						--@ve_cod_usuario_anula
								,NULL						--@ve_cod_motivo_nota_credito
								,NULL						--@ve_genera_entrada
								,NULL						--@ve_cod_tipo_nc_interno_sii
								,NULL						--@ve_xml_dte
								,NULL						--@ve_track_id_dte
								,'$response2'				--@ve_resp_emitir_dte";
    					
						if (!$db->EXECUTE_SP($sp, $param)) {
							$this->_load_record();
							print " <script>alert('Error al emitir DTE.');</script>";
							return;
						}
						
                        //Verificamos que realice bien el documento emitido.
                        $rep_response = explode("200 OK", $response);
                        
                        if($rep_response[1] <> ''){
                            //resuelve la cadena entrega
                            $objEnJson_genera = $dte->respuesta_emitir_dte($response);
                            //se envia al genera.
                            $response_genera = $dte->post_genera_dte($objEnJson_genera);
                            
                        	//Se consulta por el registro que hizo en log cambio
							$sql_detalle_dte = "select COD_LOG_CAMBIO
					                from LOG_CAMBIO 
					                where KEY_TABLA='$cod_nota_credito'
					                AND TIPO_CAMBIO='E'
					                and NOM_TABLA='NOTA_CREDITO'";
					        $result_detalle_dte = $db->build_results($sql_detalle_dte);
							$cod_log_cambio	= $result_detalle_dte[0]['COD_LOG_CAMBIO'];
							
							//se registra en BD la respuesta de libredTE, venga bien o mal
							if($cod_log_cambio <> ''){
								$sp = "sp_log_detalle_dte";
								$response_genera2 = str_replace("'", "''", $response_genera);	// reemplaza ' por ''
								$param = "$cod_log_cambio,'$response_genera2'";
								if (!$db->EXECUTE_SP($sp, $param)) {
									$this->_load_record();
									print " <script>alert('Error con sp_log_detalle_dte');</script>";
									return;
								}
							}
							else{
								$this->_load_record();
								print " <script>alert('No se encuentra Log Cambio del Envio Dte');</script>";
								return;
							}
							
							//se valida si vienen OK la respuesta de libreDte
							$pos = strpos($response_genera, "200 OK");
							if ($pos === false) {
								$this->_load_record();
								print " <script>alert('Conexion al SII es inestable. Favor notificar a Informática.');</script>";
								return;
							}
                            
                            //resuelve cadena enviada desde el genera
                            $respuesta_genera_dte = $dte->respuesta_genera_dte($response_genera);
                            
                            $nro_nc_dte		= $respuesta_genera_dte [6];
                            $EnvioDTExml	= $respuesta_genera_dte [28];
                            $track_id		= $respuesta_genera_dte [30];
                            
                            if (($nro_nc_dte <> '') && ($EnvioDTExml <> '')&& ($track_id <> '')){
                                
                                $sp = 'spu_nota_credito';
                                
                                if($reenviar)
                                    $operacion = "REENVIO_SAVE_DTE";
                                    else
                                        $operacion = "SAVE_DTE";
                                        
                                        $param = "'$operacion'			--@ve_operacion
								,$cod_nota_credito			--@ve_cod_nota_credito
								,$this->cod_usuario			--@ve_cod_usuario_impresion
								,NULL						--@ve_cod_usuario
								,$nro_nc_dte				--@ve_nro_nota_credito
								,NULL						--@ve_fecha_nota_credito
								,".self::K_ESTADO_SII_ENVIADA."	--@ve_cod_estado_doc_sii
								,NULL						--@ve_cod_empresa
								,NULL						--@ve_cod_sucursal_factura
								,NULL						--@ve_cod_persona
								,NULL						--@ve_referencia
								,NULL						--@ve_obs
								,NULL						--@ve_cod_bodega
								,NULL						--@ve_cod_tipo_nota_credito
								,NULL						--@ve_cod_doc
								,NULL						--@ve_subtotal
								,NULL						--@ve_total_neto
								,NULL						--@ve_porc_dscto1
								,NULL						--@ve_porc_dscto2
								,NULL						--@ve_ingreso_usuario_dscto1
								,NULL						--@ve_monto_dscto1
								,NULL						--@ve_ingreso_usuario_dscto2
								,NULL						--@ve_monto_dscto2
								,NULL						--@ve_porc_iva
								,NULL						--@ve_monto_iva
								,NULL						--@ve_total_con_iva
								,NULL						--@ve_motivo_anula
								,NULL						--@ve_cod_usuario_anula
								,NULL						--@ve_cod_motivo_nota_credito
								,NULL						--@ve_genera_entrada
								,NULL						--@ve_cod_tipo_nc_interno_sii
								,'$EnvioDTExml'				--@ve_xml_dte
								,$track_id					--@ve_track_id_dte";
                                
                            	if (!$db->EXECUTE_SP($sp, $param)) {
									$this->_load_record();
									print " <script>alert('Error al generar DTE.');</script>";
									return;
								}
                                            
								if($reenviar)
                                	$this->alert('Se ha reenviado exitosamente el DTE al SII');
                                                
                                print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_nota_credito&DTE_ORIGEN=61')</script>";
                               	$this->_load_record();
                                        
                            }else{//if sin datos generar.
                                $this->_load_record();
                                print " <script>alert('Error al Generar Dte contactarse con Integrasystem. $respuesta_genera_dte[0]');</script>";
                            }
                        }else{//if response
                            //responde al dte consultado.
                            $this->_load_record();
                            print " <script>alert('Error al Emitir Dte contactarse con Integrasystem.');</script>";
                        }
                        $this->unlock_record();
    }
    
	function validate_delete($db) {
		$cod_nota_credito = $this->get_key();
		
		$Sql= "select COD_LOG_CAMBIO
                from LOG_CAMBIO 
                where KEY_TABLA='$cod_nota_credito'
                AND TIPO_CAMBIO='E'
                and NOM_TABLA='NOTA_CREDITO'";
		$result_log = $db->build_results($Sql);
				
		if(count($result_log)>0){
		    return 'Error al Eliminar Dte, el documento ya tiene un intento de envío al SII.';
		}
		else 
			return '';
	}
    
    function actualizar_estado_dte(){
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $cod_nota_credito = $this->get_key();
        
        $sql = "SELECT '".self::K_TIPO_DOC."' DTE
              			,N.NRO_NOTA_CREDITO
              			,REPLACE(REPLACE(dbo.f_get_parametro(".self::K_PARAM_RUTEMISOR."),'.',''),'-8','') as RUTEMISOR
				FROM NOTA_CREDITO N
				WHERE N.COD_NOTA_CREDITO = $cod_nota_credito";
        $consultar = $db->build_results($sql);
        
        $tipodte			= $consultar[0]['DTE'];
        $nro_nota_credito	= $consultar[0]['NRO_NOTA_CREDITO'];
        $rutemisor			= $consultar[0]['RUTEMISOR'];
        
        //Llamamos a dte.
        $dte = new dte();
        
        //Se le pasa como variable hash de la clase obtenida en parametros en la BD
        $SqlHash = "SELECT dbo.f_get_parametro(".self::K_PARAM_HASH.") K_HASH";
        $Datos_Hash = $db->build_results($SqlHash);
        $dte->hash = $Datos_Hash[0]['K_HASH'];
        
        //Llamamos al envio consultar estado de socumento.
        $response = $dte->actualizar_estado($tipodte,$nro_nota_credito,$rutemisor);
        
        $actualizar_estado = $dte->respuesta_actualizar_estado($response);
        
        $revision_estado	= $actualizar_estado [9]; //respuesta de aceptado.
        if ($revision_estado == ''){
            $revision_estado	= $actualizar_estado [6]; //respuesta de rechazado.
        }
        //responde al dte consultado.
        $this->_load_record();
        print "<script>alert('Su documento electronico se encuentra en estado: $revision_estado');</script>";
    }
    
    function imprimir_dte($desde_output=false){
        $cod_nota_credito = $this->get_key();
        
        if($cod_nota_credito > 403)
            print " <script>window.open('../common_appl/print_dte.php?cod_documento=$cod_nota_credito&DTE_ORIGEN=61')</script>";
            else{
                $nro_nota_credito = $this->get_key_para_ruta_menu();
                $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
                $sql= "SELECT YEAR(FECHA_NOTA_CREDITO) YEAR
				   FROM NOTA_CREDITO
				   WHERE COD_NOTA_CREDITO = $cod_nota_credito";
                $result = $db->build_results($sql);
                $year = $result[0]['YEAR'];
                
                if(file_exists("../../../../PDF/PDFBIGGICHILE/$year/61_$nro_nota_credito.pdf"))
                    print " <script>window.open('../../../../PDF/PDFBIGGICHILE/$year/61_$nro_nota_credito.pdf')</script>";
                    else
                        $this->alert('No se registra PDF del documento solicitado en respaldos Signature.');
            }
            
            if(!$desde_output)
                $this->_load_record();
    }
    function reenviar_dte(){
        $this->enviar_dte(true);
    }
    
    function xml_dte(){
        $cod_nota_credito = $this->get_key();
        $name_archivo = "XML_DTE_61_".$this->get_key_para_ruta_menu().".xml";
        
        $fname = tempnam("/tmp", $name_archivo);
        $handle = fopen($fname,"w");
        
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        
        $sql= "SELECT XML_DTE
			   FROM NOTA_CREDITO
			   WHERE COD_NOTA_CREDITO = $cod_nota_credito";
        $result = $db->build_results($sql);
        
        $XML_DTE = base64_decode($result[0]['XML_DTE']);
        
        fwrite($handle, $XML_DTE);
        fwrite($handle, "\r\n");
        
        fclose($handle);
        
        header("Content-Type: application/force-download; name=\"$name_archivo\"");
        header("Content-Disposition: inline; filename=\"$name_archivo\"");
        $fh=fopen($fname, "rb");
        fpassthru($fh);
    }
}


class print_nota_credito_base extends reporte {
    function print_nota_credito_base($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
        parent::reporte($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
    }
    
    function modifica_pdf(&$pdf) {
        $pdf->AutoPageBreak=false;
        $db = new database(K_TIPO_BD, K_SERVER, K_BD, K_USER, K_PASS);
        $result = $db->build_results($this->sql);
        $count = count($result);
        
        $fecha = $result[0]['FECHA_NOTA_CREDITO'];
        // CABECERA
        $cod_nota_credito = $result[0]['COD_NOTA_CREDITO'];
        $nro_nota_credito = $result[0]['NRO_NOTA_CREDITO'];
        $nom_empresa = $result[0]['NOM_EMPRESA'];
        $rut = number_format($result[0]['RUT'], 0, ',', '.')."-".$result[0]['DIG_VERIF'];
        $direccion = $result[0]['DIRECCION'];
        $comuna = $result[0]['NOM_COMUNA'];
        $ciudad = $result[0]['NOM_CIUDAD'];
        $giro = $result[0]['GIRO'];
        $fono = $result[0]['TELEFONO'];
        $total_en_palabras = $result[0]['TOTAL_EN_PALABRAS'];
        $subtotal = number_format($result[0]['SUBTOTAL'], 0, ',', '.');
        $porc_dscto1 = number_format($result[0]['PORC_DSCTO1'], 1, ',', '.');
        $monto_dscto1 = number_format($result[0]['MONTO_DSCTO1'], 0, ',', '.');
        $porc_dscto2 = number_format($result[0]['PORC_DSCTO2'], 1, ',', '.');
        $monto_dscto2 = number_format($result[0]['MONTO_DSCTO2'], 0, ',', '.');
        $total_dscto = number_format($result[0]['TOTAL_DSCTO'], 0, ',', '.');
        $neto = number_format($result[0]['TOTAL_NETO'], 0, ',', '.');
        $porc_iva = number_format($result[0]['PORC_IVA'], 0, ',', '.');
        $monto_iva = number_format($result[0]['MONTO_IVA'], 0, ',', '.');
        $total_con_iva = number_format($result[0]['TOTAL_CON_IVA'], 0, ',', '.');
        $REFERENCIA	= 'REF: '.substr ($result[0]['REFERENCIA'], 0, 68);
        $NRO_FACTURA	= $result[0]['NRO_FACTURA'];
        $INI_USUARIO	= $result[0]['INI_USUARIO'];
        
        // DIBUJANDO LA CABECERA
        $pdf->SetFont('Arial','',11);
        $pdf->Text(83, 143,$fecha);
        $pdf->SetFont('Arial','',8);
        $pdf->Text(433, 117, $nro_nota_credito);
        $pdf->Text(515, 117, $INI_USUARIO);
        $pdf->SetFont('Arial','',11);
        $pdf->SetXY(83,155);
        $pdf->SetFont('Arial','B',11);
        $pdf->MultiCell(250,10,"$nom_empresa");
        $pdf->Text(398, 164, $rut);
        $pdf->SetFont('Arial','',11);
        //$pdf->Text(83, 213, $direccion);
        $pdf->SetXY(80,205);
        $pdf->MultiCell(250,9,"$direccion");
        
        $pdf->Text(418, 213, $comuna);
        $pdf->Text(68, 236, $ciudad);
        
        $pdf->SetXY(217, 228);
        $pdf->MultiCell(150,9,"$giro");
        
        $pdf->Text(408, 236, $fono);
        $pdf->SetFont('Arial','B',10);
        $pdf->Text(150, 310, "$REFERENCIA");
        $pdf->SetFont('Arial','',10);
        
        //DIBUJANDO LOS ITEMS DE LA NOTA_CREDITO
        for($i=0; $i<$count; $i++){
            $item = $result[$i]['ITEM'];
            $cantidad = $result[$i]['CANTIDAD'];
            $modelo = $result[$i]['COD_PRODUCTO'];
            $detalle = substr ($result[$i]['NOM_PRODUCTO'], 0, 45);
            $p_unitario = number_format($result[$i]['PRECIO'], 0, ',', '.');
            $total = number_format($result[$i]['TOTAL_NC'], 0, ',', '.');
            // por cada pasada le asigna una nueva posicion
            $pdf->Text(43, 334+(14*$i), $item);
            $pdf->Text(73, 334+(14*$i), $cantidad);
            $pdf->Text(103, 334+(14*$i), $modelo);
            
            $pdf->SetXY(158,326+(14*$i));
            $pdf->Cell(4,1,"$detalle");
            
            $pdf->SetXY(400,329+(14*$i));
            $pdf->MultiCell(80,5, $p_unitario,0, 'R');
            $pdf->SetXY(467,329+(14*$i));
            $pdf->MultiCell(80,5, $total,0, 'R');
        }
        
        // DIBUJANDO TOTALES
        $pdf->SetFont('Arial','',12);
        $pdf->Text(98, 630, 'Son: '.$total_en_palabras.' pesos.');
        
        if($total_dscto <> 0){//tiene dscto
            if(($monto_dscto1 <> 0 && $monto_dscto2 == 0) || ($monto_dscto2 <> 0 && $monto_dscto1 == 0)){//solo tiene un DSCTO 1
                $pdf->SetXY(412,649);
                $pdf->SetFont('Arial','',9);
                $pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
                
                $pdf->SetXY(444,649);
                $pdf->SetFont('Arial','B',11);
                $pdf->MultiCell(105,4,$subtotal,0, 'R');
                
                if($monto_dscto1 <> 0){
                    $pdf->SetXY(412,664);
                    $pdf->SetFont('Arial','',9);
                    $pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO $ ',0, 'R');
                    
                    $pdf->SetXY(444,664);
                    $pdf->SetFont('Arial','B',11);
                    $pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
                }
                else{
                    $pdf->SetXY(412,664);
                    $pdf->SetFont('Arial','',9);
                    $pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO $ ',0, 'R');
                    
                    $pdf->SetXY(444,664);
                    $pdf->SetFont('Arial','B',11);
                    $pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');
                }
            }else if($monto_dscto1 <> 0 && $monto_dscto2 <> 0){//tiene ambos DSCTO
                
                $pdf->SetXY(412,634);
                $pdf->SetFont('Arial','',9);
                $pdf->MultiCell(80,4, 'SUBTOTAL  $ ',0, 'R');
                
                $pdf->SetXY(444,634);
                $pdf->SetFont('Arial','B',11);
                $pdf->MultiCell(105,4,$subtotal,0, 'R');
                
                $pdf->SetXY(412,649);
                $pdf->SetFont('Arial','',9);
                $pdf->MultiCell(80,4,$porc_dscto1.' % DSCTO 1 $ ',0, 'R');
                
                $pdf->SetXY(444,649);
                $pdf->SetFont('Arial','B',11);
                $pdf->MultiCell(105,4,$monto_dscto1, 0, 'R');
                
                $pdf->SetXY(412,664);
                $pdf->SetFont('Arial','',9);
                $pdf->MultiCell(80,4,$porc_dscto2.' % DSCTO 2 $ ',0, 'R');
                
                $pdf->SetXY(444,664);
                $pdf->SetFont('Arial','B',11);
                $pdf->MultiCell(105,4,$monto_dscto2, 0, 'R');
            }
        }
        
        $pdf->SetXY(412,679);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(80,4, 'TOTAL NETO  $ ',0, 'R');
        $pdf->SetXY(444,679);
        $pdf->SetFont('Arial','B',11);
        $pdf->MultiCell(105,4,$neto,0, 'R');
        $pdf->SetXY(412,694);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(80,4, $porc_iva.' % IVA  $ ',0, 'R');
        $pdf->SetXY(444,694);
        $pdf->SetFont('Arial','B',11);
        $pdf->MultiCell(105,4,$monto_iva,0, 'R');
        $pdf->Rect( 430, 705, 120, 2, 'f');
        $pdf->SetXY(412,714);
        $pdf->SetFont('Arial','',9);
        $pdf->MultiCell(80,4,'TOTAL  $ ',0, 'R');
        $pdf->SetXY(444,714);
        $pdf->SetFont('Arial','B',11);
        $pdf->MultiCell(105,4,$total_con_iva,0, 'R');
        
        
        //DIBUJANDO PERSONA QUE RETIRA PRODUCTOS
        $pdf->SetFont('Arial','',13);
        $pdf->Text(43, 685, $NRO_FACTURA);
        
    }
}

/////////////////////////////////////////////////////////////
// Se separa por K_CLIENTE
$file_name = dirname(__FILE__)."/".K_CLIENTE."/class_wi_nota_credito.php";
if (file_exists($file_name))
    require_once($file_name);
    else {
        class wi_nota_credito extends wi_nota_credito_base {
            function wi_nota_credito($cod_item_menu) {
                parent::wi_nota_credito_base($cod_item_menu);
            }
        }
        class print_nota_credito extends print_nota_credito_base {
            function print_nota_credito($sql, $xml, $labels=array(), $titulo, $con_logo, $vuelve_a_presentacion=false) {
                parent::print_nota_credito_base($sql, $xml, $labels, $titulo, $con_logo, $vuelve_a_presentacion);
            }
        }
    }
    ?>