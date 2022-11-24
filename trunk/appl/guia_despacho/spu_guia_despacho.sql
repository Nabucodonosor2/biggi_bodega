ALTER PROCEDURE [dbo].[spu_guia_despacho](@ve_operacion				varchar(20)
								,@ve_cod_guia_despacho		numeric
								,@ve_cod_usuario_impresion	numeric = NULL
								,@ve_cod_usuario			numeric = NULL
								,@ve_nro_guia_despacho		numeric = NULL
								,@ve_cod_estado_doc_sii		numeric = NULL
								,@ve_cod_empresa			numeric = NULL
								,@ve_cod_sucursal_despacho	numeric = NULL
								,@ve_cod_persona			numeric = NULL
								,@ve_referencia				varchar(100) = NULL
								,@ve_nro_orden_compra		varchar(40) = NULL
								,@ve_obs					text	 = NULL
								,@ve_retirado_por			varchar(100) = NULL
								,@ve_rut_retirado_por		numeric = NULL
								,@ve_dig_verif_retirado_por	varchar(1) = NULL
								,@ve_guia_transporte		varchar(100) = NULL
								,@ve_patente				varchar(100) = NULL
								,@ve_cod_factura			numeric = NULL
								,@ve_cod_bodega				numeric = NULL
								,@ve_cod_tipo_guia_despacho	numeric = NULL
								,@ve_cod_doc				numeric = NULL
								,@ve_motivo_anula			varchar(100) = NULL
								,@ve_cod_usuario_anula		numeric = NULL
								,@ve_cod_indicador_tipo_traslado	numeric = NULL
								,@ve_xml_dte						text=NULL
                                ,@ve_track_id_dte					varchar(100)=NULL
                                ,@ve_resp_emitir_dte				text=NULL)
AS
BEGIN  
		declare		@vl_cod_estado_gd_emitida numeric,
					@vl_cod_estado_gd_impresa numeric,
					@vl_cod_estado_gd_enviada numeric,
					@vl_cod_estado_gd_anulada numeric,	
					@vl_cod_usuario_anula numeric,
					@vl_cod_tipo_doc_sii numeric,
					@vl_nom_sucursal varchar (100),
					@vl_direccion  varchar (100),
					@vl_cod_comuna numeric,
					@vl_cod_ciudad numeric,	
					@vl_cod_pais numeric,
					@vl_telefono varchar (100),
					@vl_fax varchar (100),
					@vl_rut numeric,
					@vl_dig_verif varchar (1),
					@vl_nom_empresa varchar(100),
					@vl_giro varchar(100),
					@vl_nom_persona varchar(100),
					@vl_mail varchar(100),
					@vl_cod_cargo numeric,
					@vl_nro_guia_despacho numeric


		set @vl_cod_estado_gd_emitida = 1  --- estado de la gd = emitida
		set @vl_cod_estado_gd_impresa = 2  --- estado de la gd = impresa
		set @vl_cod_estado_gd_enviada = 3  --- estado de la gd = enviada
		set @vl_cod_estado_gd_anulada = 4  --- estado de la gd = anulada
		set @vl_cod_tipo_doc_sii = 2  --- tipo de doc_sii = gd

	
		if (@ve_operacion='UPDATE') 
			begin
				select	@vl_cod_usuario_anula = gd.cod_usuario_anula,
						@vl_rut = e.rut,
						@vl_dig_verif = e.dig_verif,	
						@vl_nom_empresa = e.nom_empresa,	
						@vl_giro  = e.giro,
						@vl_nom_sucursal = s.nom_sucursal,
						@vl_direccion = s.direccion,		
						@vl_cod_comuna = s.cod_comuna,
						@vl_cod_ciudad = s.cod_ciudad, 
						@vl_cod_pais = s.cod_pais,
						@vl_telefono = s.telefono,
						@vl_fax = s.fax
				from guia_despacho gd, sucursal s, empresa e
				where gd.cod_guia_despacho = @ve_cod_guia_despacho and
					gd.cod_sucursal_despacho = s.cod_sucursal and
					gd.cod_empresa = e.cod_empresa

				select	@vl_nom_persona = nom_persona,
						@vl_mail = email,
						@vl_cod_cargo = cod_cargo
				from persona
				where cod_persona = @ve_cod_persona

				-- si estado = emitida, hace update a empresa, sucursal y persona para grabar en la GD  					
				if (@ve_cod_estado_doc_sii = @vl_cod_estado_gd_emitida )
					update guia_despacho
					set nom_sucursal = @vl_nom_sucursal, 
						direccion = @vl_direccion,		
						cod_comuna = @vl_cod_comuna ,
						cod_ciudad = @vl_cod_ciudad, 
						cod_pais = @vl_cod_pais ,
						telefono = @vl_telefono,
						fax = @vl_fax, 
						rut = @vl_rut,
						dig_verif = @vl_dig_verif,	
						nom_empresa = @vl_nom_empresa,	
						giro = @vl_giro,
						nom_persona = @vl_nom_persona,
						mail = @vl_mail,
						cod_cargo = @vl_cod_cargo 
					where cod_guia_despacho = @ve_cod_guia_despacho
				
				-- si estado = anulada, hace update a datos de anulacion en la GD
				else if (@ve_cod_estado_doc_sii = @vl_cod_estado_gd_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la GD = anulada 
					update guia_despacho
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_guia_despacho = @ve_cod_guia_despacho
			
				-- update general
				update guia_despacho		
				set		cod_usuario				= @ve_cod_usuario	
						,nro_guia_despacho		= @ve_nro_guia_despacho		
						,cod_estado_doc_sii		= @ve_cod_estado_doc_sii		
						,cod_empresa			= @ve_cod_empresa				
						,cod_sucursal_despacho	= @ve_cod_sucursal_despacho	
						,cod_persona			= @ve_cod_persona				
						,referencia				= @ve_referencia				
						,nro_orden_compra		= @ve_nro_orden_compra		
						,obs					= @ve_obs						
						,retirado_por			= @ve_retirado_por			
						,rut_retirado_por		= @ve_rut_retirado_por		
						,dig_verif_retirado_por	= @ve_dig_verif_retirado_por	
						,guia_transporte		= @ve_guia_transporte			
						,patente				= @ve_patente					
						,cod_factura			= @ve_cod_factura				
						,cod_bodega				= @ve_cod_bodega				
						,cod_tipo_guia_despacho	= @ve_cod_tipo_guia_despacho	
						,cod_doc				= @ve_cod_doc						
						,cod_usuario_impresion	= @ve_cod_usuario_impresion
						,cod_indicador_tipo_traslado = @ve_cod_indicador_tipo_traslado
				where cod_guia_despacho = @ve_cod_guia_despacho
			end
		else if (@ve_operacion='INSERT') 
			begin
						
				select	@vl_rut				 = e.rut,
						@vl_dig_verif		 = e.dig_verif,	
						@vl_nom_empresa		 = e.nom_empresa,	
						@vl_giro			 = e.giro,
						@vl_nom_sucursal	 = s.nom_sucursal,
						@vl_direccion		 = s.direccion,		
						@vl_cod_comuna		 = s.cod_comuna,
						@vl_cod_ciudad		 = s.cod_ciudad, 
						@vl_cod_pais		 = s.cod_pais,
						@vl_telefono		 = s.telefono,
						@vl_fax				 = s.fax
				from empresa e, sucursal s
				where e.cod_empresa = @ve_cod_empresa and 
					s.cod_sucursal = @ve_cod_sucursal_despacho 

				select @vl_nom_persona = nom_persona,
					@vl_mail = email,
					@vl_cod_cargo = cod_cargo
				from persona	
				where cod_persona = @ve_cod_persona

				insert into guia_despacho
					(fecha_registro			
					,cod_usuario				
					,nro_guia_despacho	
					,cod_estado_doc_sii		
					,cod_empresa				
					,cod_sucursal_despacho	
					,cod_persona				
					,referencia				
					,nro_orden_compra		
					,obs						
					,retirado_por			
					,rut_retirado_por		
					,dig_verif_retirado_por	
					,guia_transporte			
					,patente					
					,cod_factura				
					,cod_bodega				
					,cod_tipo_guia_despacho	
					,cod_doc					
					,rut						
					,dig_verif				
					,nom_empresa				
					,giro					
					,nom_sucursal			
					,direccion				
					,cod_comuna				
					,cod_ciudad				
					,cod_pais				
					,telefono				
					,fax						
					,nom_persona				
					,mail					
					,cod_cargo				
					,cod_usuario_impresion
					,genera_salida
					,cod_indicador_tipo_traslado)
				values 
					(getdate()
					,@ve_cod_usuario
					,@ve_nro_guia_despacho
					,@ve_cod_estado_doc_sii		
					,@ve_cod_empresa				
					,@ve_cod_sucursal_despacho	
					,@ve_cod_persona				
					,@ve_referencia				
					,@ve_nro_orden_compra		
					,@ve_obs						
					,@ve_retirado_por			
					,@ve_rut_retirado_por		
					,@ve_dig_verif_retirado_por	
					,@ve_guia_transporte			
					,@ve_patente					
					,@ve_cod_factura				
					,@ve_cod_bodega				
					,@ve_cod_tipo_guia_despacho
					,@ve_cod_doc					
					,@vl_rut						
					,@vl_dig_verif			
					,@vl_nom_empresa				
					,@vl_giro					
					,@vl_nom_sucursal			
					,@vl_direccion				
					,@vl_cod_comuna				
					,@vl_cod_ciudad				
					,@vl_cod_pais				
					,@vl_telefono				
					,@vl_fax						
					,@vl_nom_persona				
					,@vl_mail					
					,@vl_cod_cargo				
					,@ve_cod_usuario_impresion
					,'N'
					,@ve_cod_indicador_tipo_traslado)
				end 
			else if (@ve_operacion='DELETE') 
				begin
					delete item_guia_despacho
    				where cod_guia_despacho = @ve_cod_guia_despacho

					delete guia_despacho
    				where cod_guia_despacho = @ve_cod_guia_despacho
				end
			else if (@ve_operacion='ENVIA_DTE') 
				begin
					declare @vl_count numeric

					select	@vl_nro_guia_despacho = nro_guia_despacho
					from	guia_despacho
					where  cod_guia_despacho = @ve_cod_guia_despacho

					if (@vl_nro_guia_despacho is null)
						begin
							select	@vl_count = count(*)
							from	guia_despacho
							where	cod_estado_doc_sii = @vl_cod_estado_gd_enviada
						
							if(@vl_count > 0)begin
								select @vl_nro_guia_despacho = max(nro_guia_despacho) + 1
								from guia_despacho
								where cod_estado_doc_sii = @vl_cod_estado_gd_enviada
							end
							else begin
								set @vl_nro_guia_despacho = 50001 --numero inicial asignado por SP 15/12/2010 nro_actual = 50001
							end
						
							update guia_despacho
							set nro_guia_despacho = @vl_nro_guia_despacho,
								fecha_guia_despacho = getdate(),
								cod_estado_doc_sii = @vl_cod_estado_gd_enviada,
								cod_usuario_impresion = @ve_cod_usuario_impresion
							where  cod_guia_despacho = @ve_cod_guia_despacho
						end --@vl_nro_guia_despacho is null
					else
						begin
							declare	@vl_cod_guia_despacho	varchar(20)
							
							set @vl_cod_guia_despacho = convert(varchar, @ve_cod_guia_despacho)
							exec sp_log_cambio 'GUIA_DESPACHO', @vl_cod_guia_despacho, @ve_cod_usuario_impresion, 'R'	--Reenvia la NC a SII
						end
				end
			else if (@ve_operacion='PRINT') 	
				begin
					declare @K_PARAM_MAX_IT_GD			numeric,
							@vl_valor_max_cant_it_gd	numeric,
							@vl_count_item				numeric

					set @K_PARAM_MAX_IT_GD = 28
					set @vl_valor_max_cant_it_gd = dbo.f_get_parametro(@K_PARAM_MAX_IT_GD)

					
					select @vl_count_item = count(*)
					from item_guia_despacho 
					where cod_guia_despacho = @ve_cod_guia_despacho

					declare @vl_cod_guia_despacho_ant numeric
					set @vl_cod_guia_despacho_ant = @ve_cod_guia_despacho

					-- debe crear mas de 1 GD
					while (@vl_count_item > @vl_valor_max_cant_it_gd) begin
						-- duplica la GD
						insert into GUIA_DESPACHO
							(FECHA_REGISTRO,		COD_USUARIO,			COD_ESTADO_DOC_SII,			COD_EMPRESA,
							COD_SUCURSAL_DESPACHO,	COD_PERSONA,			REFERENCIA,					NRO_ORDEN_COMPRA,
							OBS,					RETIRADO_POR,			RUT_RETIRADO_POR,			DIG_VERIF_RETIRADO_POR,
							GUIA_TRANSPORTE,		PATENTE,				COD_FACTURA,				GENERA_SALIDA,
							COD_BODEGA,				COD_TIPO_GUIA_DESPACHO,	COD_DOC,					RUT,
							DIG_VERIF,				NOM_EMPRESA,			GIRO,						NOM_SUCURSAL,
							DIRECCION,				COD_COMUNA,				COD_CIUDAD,					COD_PAIS,
							TELEFONO,				FAX,					NOM_PERSONA,				MAIL,
							COD_CARGO,				COD_USUARIO_IMPRESION)
						select getdate(),			@ve_cod_usuario_impresion,@vl_cod_estado_gd_emitida,COD_EMPRESA,
							COD_SUCURSAL_DESPACHO,	COD_PERSONA,			REFERENCIA,					NRO_ORDEN_COMPRA,
							OBS,					RETIRADO_POR,			RUT_RETIRADO_POR,			DIG_VERIF_RETIRADO_POR,
							GUIA_TRANSPORTE,		PATENTE,				COD_FACTURA,				GENERA_SALIDA,
							COD_BODEGA,				COD_TIPO_GUIA_DESPACHO,	COD_DOC,					RUT,
							DIG_VERIF,				NOM_EMPRESA,			GIRO,						NOM_SUCURSAL,
							DIRECCION,				COD_COMUNA,				COD_CIUDAD,					COD_PAIS,
							TELEFONO,				FAX,					NOM_PERSONA,				MAIL,
							COD_CARGO,				COD_USUARIO_IMPRESION
						from guia_despacho
						where cod_guia_despacho = @ve_cod_guia_despacho

						declare @vl_cod_guia_despacho_new numeric
						set @vl_cod_guia_despacho_new = @@identity

						update item_guia_despacho
						set cod_guia_despacho = @vl_cod_guia_despacho_new
						where cod_guia_despacho = @vl_cod_guia_despacho_ant
						  and orden > (@vl_valor_max_cant_it_gd * 10)

						-- reasigna los orden
						execute sp_orden_no_parametricas 'item_guia_despacho','guia_despacho',@vl_cod_guia_despacho_new

						select @vl_count_item = count(*)
						from item_guia_despacho 
						where cod_guia_despacho = @vl_cod_guia_despacho_new

						set @vl_cod_guia_despacho_ant = @vl_cod_guia_despacho_new
					end

					select @vl_nro_guia_despacho = nro_guia_despacho
					from guia_despacho
					where  cod_guia_despacho = @ve_cod_guia_despacho
					if (@vl_nro_guia_despacho is null)
						update guia_despacho
						set nro_guia_despacho = @ve_nro_guia_despacho,
							fecha_guia_despacho = getdate(),
							cod_estado_doc_sii = @vl_cod_estado_gd_impresa,
							cod_usuario_impresion = @ve_cod_usuario_impresion
						where  cod_guia_despacho = @ve_cod_guia_despacho
				end
			else if (@ve_operacion='SAVE_EMITIR_DTE')begin
				update guia_despacho
				set RESP_EMITIR_DTE	= @ve_resp_emitir_dte
				where  cod_guia_despacho 	= @ve_cod_guia_despacho
			end
			else if (@ve_operacion='SAVE_DTE') begin
				select	@vl_nro_guia_despacho = nro_guia_despacho
				from	guia_despacho
				where  cod_guia_despacho = @ve_cod_guia_despacho
		
				if (@vl_nro_guia_despacho is null)begin
					update guia_despacho
					set nro_guia_despacho		= @ve_nro_guia_despacho
						,fecha_guia_despacho	= getdate()
						,cod_estado_doc_sii		= @ve_cod_estado_doc_sii
						,cod_usuario_impresion	= @ve_cod_usuario_impresion
						,xml_dte				= @ve_xml_dte
		    			,track_id_dte			= @ve_track_id_dte
					where  cod_guia_despacho 	= @ve_cod_guia_despacho
				end
			end	
END
