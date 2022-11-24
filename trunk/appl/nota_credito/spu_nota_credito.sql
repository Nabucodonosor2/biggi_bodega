---------------------spu_nota_credito-----------------
ALTER PROCEDURE [dbo].[spu_nota_credito] (@ve_operacion				varchar(20)
								,@ve_cod_nota_credito		numeric
								,@ve_cod_usuario_impresion	numeric = NULL
								,@ve_cod_usuario			numeric = NULL
								,@ve_nro_nota_credito		numeric = NULL
								,@ve_fecha_nota_credito		varchar(10) = null
								,@ve_cod_estado_doc_sii		numeric = NULL	
								,@ve_cod_empresa			numeric = NULL
								,@ve_cod_sucursal_factura	numeric = NULL
								,@ve_cod_persona			numeric = NULL
								,@ve_referencia				varchar(100) = NULL
								,@ve_obs					text	 = NULL
								,@ve_cod_bodega				numeric = NULL
								,@ve_cod_tipo_nota_credito	numeric = NULL
								,@ve_cod_doc				numeric = NULL
								,@ve_subtotal				T_PRECIO = NULL
								,@ve_total_neto				T_PRECIO = NULL
								,@ve_porc_dscto1			T_PORCENTAJE = NULL
								,@ve_porc_dscto2			T_PORCENTAJE = NULL
								,@ve_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO = NULL
								,@ve_monto_dscto1			T_PRECIO = NULL
								,@ve_ingreso_usuario_dscto2 T_INGRESO_USUARIO_DSCTO = NULL
								,@ve_monto_dscto2			T_PRECIO = NULL
								,@ve_porc_iva				T_PORCENTAJE = NULL
								,@ve_monto_iva				T_PRECIO = NULL 
								,@ve_total_con_iva			T_PRECIO = NULL
								,@ve_motivo_anula			varchar(100) = NULL
								,@ve_cod_usuario_anula		numeric = NULL
								,@ve_cod_motivo_nota_credito numeric = 99
								,@ve_genera_entrada			varchar(1) = NULL
								,@ve_cod_tipo_nc_interno_sii numeric = NULL
								,@ve_xml_dte				text = NULL
								,@ve_track_id_dte			varchar(100) = NULL
								,@ve_resp_emitir_dte		text=NULL
								,@ve_origen_nc				varchar(50)=NULL
								)

AS
BEGIN  

		declare		@kl_cod_estado_nc_emitida numeric,
					@kl_cod_estado_nc_impresa numeric,
					@kl_cod_estado_nc_enviada numeric,
					@kl_cod_estado_nc_anulada numeric,
					@kl_cod_tipo_doc_sii numeric,
					@vl_cod_usuario_anula numeric,
					@vl_rut numeric,
					@vl_dig_verif varchar (1),
					@vl_nom_empresa varchar(100),
					@vl_giro varchar(100),
					@vl_nom_sucursal varchar (100),
					@vl_direccion  varchar (100),
					@vl_cod_comuna numeric,
					@vl_cod_ciudad numeric,	
					@vl_cod_pais numeric,
					@vl_telefono varchar (100),
					@vl_fax varchar (100),
					@vl_nom_persona varchar(100),
					@vl_mail varchar(100),
					@vl_cod_cargo numeric,
					@vl_nro_nota_credito numeric
					,@vl_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO
					,@vl_ingreso_usuario_dscto2 T_INGRESO_USUARIO_DSCTO
					,@vl_porc_iva T_PORCENTAJE
					,@vl_monto_dscto1 T_PRECIO
					,@vl_porc_dscto1 T_PORCENTAJE
					,@vl_monto_dscto2 T_PRECIO
					,@vl_porc_dscto2 T_PORCENTAJE
					,@vl_sub_total T_PRECIO
					,@vl_sub_total_con_dscto1 T_PRECIO
					,@vl_total_neto T_PRECIO
					,@vl_monto_iva T_PRECIO
					,@vl_total_con_iva T_PRECIO
					,@vl_cod_ingreso_pago numeric
					,@kl_tipo_doc_pago_nc numeric
					,@kl_ingreso_pago_confirmada numeric
					,@vl_cod_empresa numeric
					,@vl_cod_doc numeric
					,@vl_total_con_iva_nc T_PRECIO
					,@vl_fecha_nota_credito varchar(20)
					,@kl_ingreso_pago_anulada numeric
					,@vl_cod_usuario	NUMERIC
					,@vl_cod_bodega		NUMERIC
					,@vl_referencia		VARCHAR(100)
					,@vl_new_cod_salida_bodega	NUMERIC
					,@vl_orden_nc	NUMERIC
					,@vl_item_nc	NUMERIC
					,@vl_cod_producto_nc	VARCHAR(30)
					,@vl_nom_producto_nc	VARCHAR(100)
					,@vl_item_cantidad_nc	NUMERIC
					,@vl_cod_item_nc		NUMERIC
					,@vl_genera_entrada			varchar(1)
					,@vl_new_cod_entrada_bodega	numeric
					,@vl_precio					numeric
					,@kl_ingreso_pago_emitida   numeric


		set @kl_cod_estado_nc_emitida = 1  --- estado de la nc = emitida
		set @kl_cod_estado_nc_impresa = 2  --- estado de la nc = impresa
		set @kl_cod_estado_nc_enviada = 3  --- estado de la nc = enviada
		set @kl_cod_estado_nc_anulada = 4  --- estado de la nc = anulada
		set @kl_cod_tipo_doc_sii = 3  --- tipo de doc_sii = nota_credito
		set @kl_tipo_doc_pago_nc = 7
		set @kl_ingreso_pago_confirmada = 2
		set @kl_ingreso_pago_anulada = 3
		set @kl_ingreso_pago_emitida = 1
	
		if (@ve_operacion='UPDATE') 
			begin
				select	@vl_cod_usuario_anula = nc.cod_usuario_anula,
						@vl_rut				 = e.rut,
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
				from nota_credito nc, empresa e, sucursal s
				where nc.cod_nota_credito = @ve_cod_nota_credito and
					e.cod_empresa = @ve_cod_empresa and 
					s.cod_sucursal = @ve_cod_sucursal_factura 

				select @vl_nom_persona = nom_persona,
					@vl_mail = email,
					@vl_cod_cargo = cod_cargo
				from persona	
				where cod_persona = @ve_cod_persona

				-- si estado = emitida, hace update a empresa, sucursal y persona para grabar en la NC  					
				if (@ve_cod_estado_doc_sii = @kl_cod_estado_nc_emitida )
					update nota_credito
					set rut			 = @vl_rut,
						dig_verif	 = @vl_dig_verif,	
						nom_empresa	 = @vl_nom_empresa,	
						giro		 = @vl_giro,
						nom_sucursal = @vl_nom_sucursal, 
						direccion	 = @vl_direccion,		
						cod_comuna	 = @vl_cod_comuna ,
						cod_ciudad	 = @vl_cod_ciudad, 
						cod_pais	 = @vl_cod_pais ,
						telefono	 = @vl_telefono,
						fax			 = @vl_fax, 
						nom_persona	 = @vl_nom_persona,
						mail		 = @vl_mail,
						cod_cargo	 = @vl_cod_cargo
					where cod_nota_credito = @ve_cod_nota_credito
				
				-- si estado = anulada, hace update a datos de anulacion en la FA
				else if (@ve_cod_estado_doc_sii = @kl_cod_estado_nc_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la nc = anulada 
				begin
					update nota_credito
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_nota_credito = @ve_cod_nota_credito
					
					--anula el ingreso de pago asociado a la nota de crédito
					update ingreso_pago
					set fecha_anula			= getdate ()
						,motivo_anula		= 'ANULACIÓN DE NOTA DE CRÉDITO'			
						,cod_usuario_anula	= @ve_cod_usuario_anula
						,cod_estado_ingreso_pago = @kl_ingreso_pago_anulada			
					where cod_ingreso_pago  in (select ip.cod_ingreso_pago from doc_ingreso_pago dip, ingreso_pago ip
												where dip.nro_doc = @ve_nro_nota_credito
													and dip.cod_tipo_doc_pago = @kl_tipo_doc_pago_nc
													and dip.cod_ingreso_pago = ip.cod_ingreso_pago
													and ip.cod_estado_ingreso_pago <> @kl_ingreso_pago_anulada)
													
					--CREA SALIDA BODEGA
					exec spu_nota_credito 'CREA_SALIDA', @ve_cod_nota_credito

				end
				-- update general
				update nota_credito		
				set		cod_usuario				= @ve_cod_usuario	
						,nro_nota_credito		= @ve_nro_nota_credito
						,fecha_nota_credito		= dbo.to_date(@ve_fecha_nota_credito)
						,cod_estado_doc_sii		= @ve_cod_estado_doc_sii		
						,cod_empresa			= @ve_cod_empresa
						,cod_sucursal_factura	= @ve_cod_sucursal_factura
						,cod_persona			= @ve_cod_persona				
						,referencia				= @ve_referencia					
						,obs					= @ve_obs						
						,cod_bodega				= @ve_cod_bodega				
						,cod_tipo_nota_credito	= @ve_cod_tipo_nota_credito	
						,cod_doc				= @ve_cod_doc						
						,cod_usuario_impresion	= @ve_cod_usuario_impresion
						,subtotal				= @ve_subtotal
						,porc_dscto1			= @ve_porc_dscto1
						,ingreso_usuario_dscto1	= @ve_ingreso_usuario_dscto1
						,monto_dscto1			= @ve_monto_dscto1
						,porc_dscto2			= @ve_porc_dscto2
						,ingreso_usuario_dscto2	= @ve_ingreso_usuario_dscto2
						,monto_dscto2			= @ve_monto_dscto2
						,total_neto				= @ve_total_neto
						,porc_iva				= @ve_porc_iva
						,monto_iva				= @ve_monto_iva
						,total_con_iva			= @ve_total_con_iva
						,cod_motivo_nota_credito = @ve_cod_motivo_nota_credito
						,cod_tipo_nc_interno_sii = @ve_cod_tipo_nc_interno_sii
				where cod_nota_credito = @ve_cod_nota_credito
			end
		else if (@ve_operacion='INSERT') 
			begin
				--------------------------------
				-- para bodega Biggi debre crear entradas a la bodega 2 siempre
				declare
					@vl_sistema			varchar(100)

				select @vl_sistema = valor 
				from parametro
				where cod_parametro = 3		-- sistema

				if (@vl_sistema = 'BODEGA') begin
					set @ve_genera_entrada = 'S'
					set @ve_cod_bodega = 2	-- bodega de equipos terminados
				end
				--------------------------------

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
					s.cod_sucursal = @ve_cod_sucursal_factura 

				select @vl_nom_persona = nom_persona,
					@vl_mail = email,
					@vl_cod_cargo = cod_cargo
				from persona	
				where cod_persona = @ve_cod_persona

				insert into nota_credito
						(fecha_registro			
						,cod_usuario				
						,cod_estado_doc_sii		
						,cod_empresa				
						,cod_sucursal_factura	
						,cod_persona				
						,cod_tipo_nota_credito	
						,cod_doc
						,referencia					
						,obs
						,genera_entrada
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
						,cod_bodega
						,subtotal
						,porc_dscto1
						,ingreso_usuario_dscto1
						,monto_dscto1
						,porc_dscto2
						,ingreso_usuario_dscto2
						,monto_dscto2
						,total_neto
						,porc_iva
						,monto_iva
						,total_con_iva
						,cod_motivo_nota_credito
						,cod_tipo_nc_interno_sii
						,origen_nc
						)					
					values (getdate()
						,@ve_cod_usuario
						,@ve_cod_estado_doc_sii		
						,@ve_cod_empresa				
						,@ve_cod_sucursal_factura	
						,@ve_cod_persona
						,@ve_cod_tipo_nota_credito
						,@ve_cod_doc
						,@ve_referencia	
						,@ve_obs
						,@ve_genera_entrada
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
						,@ve_cod_bodega
						,@ve_subtotal
						,@ve_porc_dscto1
						,@ve_ingreso_usuario_dscto1
						,@ve_monto_dscto1
						,@ve_porc_dscto2
						,@ve_ingreso_usuario_dscto2
						,@ve_monto_dscto2
						,@ve_total_neto
						,@ve_porc_iva
						,@ve_monto_iva
						,@ve_total_con_iva
						,@ve_cod_motivo_nota_credito
						,@ve_cod_tipo_nc_interno_sii
						,@ve_origen_nc)
				end 
			else if (@ve_operacion='DELETE') 
				begin
					delete item_nota_credito
    				where cod_nota_credito = @ve_cod_nota_credito
					
					delete nota_credito
					where cod_nota_credito = @ve_cod_nota_credito
				end
			else if (@ve_operacion='ENVIA_DTE') 
				begin
					declare @vl_count numeric

					select	@vl_nro_nota_credito = nro_nota_credito
					from	nota_credito
					where	cod_nota_credito = @ve_cod_nota_credito

					if (@vl_nro_nota_credito is null)
						begin
							select	@vl_count = count(*)
							from	nota_credito
							where	cod_estado_doc_sii = @kl_cod_estado_nc_enviada
							
							if(@vl_count > 0)begin
								select @vl_nro_nota_credito = max(nro_nota_credito) + 1
								from nota_credito
								where cod_estado_doc_sii = @kl_cod_estado_nc_enviada
							end
							else 
							begin
								set @vl_nro_nota_credito = 9001 --numero inicial asignado por SP 15/12/2010 nro_actual = 9001
							end
												
							update nota_credito
							set nro_nota_credito = @vl_nro_nota_credito,
								fecha_nota_credito = getdate(),
								cod_estado_doc_sii = @kl_cod_estado_nc_enviada,
								cod_usuario_impresion = @ve_cod_usuario_impresion
							where cod_nota_credito = @ve_cod_nota_credito
							
							if(@vl_cod_empresa <> 4 AND @vl_cod_empresa <> 1)begin
								exec spu_nota_credito 'CREA_INGRESO_PAGO', @ve_cod_nota_credito
							end

							--CREA ENTRADA BODEGA
							exec spu_nota_credito 'CREA_ENTRADA', @ve_cod_nota_credito
						end
					else
						begin
							declare	@vl_cod_nota_credito	varchar(20)
							
							set @vl_cod_nota_credito = convert(varchar, @ve_cod_nota_credito)
							exec sp_log_cambio 'NOTA_CREDITO', @vl_cod_nota_credito, @ve_cod_usuario_impresion, 'R'	--Reenvia la NC a SII
						end

				end
			else if (@ve_operacion='PRINT') 	
				begin
					select @vl_nro_nota_credito = nro_nota_credito
					from nota_credito
					where cod_nota_credito = @ve_cod_nota_credito

					if (@vl_nro_nota_credito is null)
					begin
						update nota_credito
						set nro_nota_credito = dbo.f_get_nro_doc_sii (@kl_cod_tipo_doc_sii , @ve_cod_usuario_impresion),
							fecha_nota_credito = getdate(),
							cod_estado_doc_sii = @kl_cod_estado_nc_impresa,
							cod_usuario_impresion = @ve_cod_usuario_impresion
						where cod_nota_credito = @ve_cod_nota_credito

						exec spu_nota_credito 'CREA_INGRESO_PAGO', @ve_cod_nota_credito

						--CREA ENTRADA BODEGA
						exec spu_nota_credito 'CREA_ENTRADA', @ve_cod_nota_credito
					end
		end 
		else if (@ve_operacion='CREA_INGRESO_PAGO') begin
			-- obtiene valores de la NC que no vienen como parametros de entrada
			select @vl_nro_nota_credito = nro_nota_credito 
				,@vl_cod_empresa = cod_empresa 
				,@vl_cod_doc = cod_doc
				,@vl_total_con_iva_nc = total_con_iva
				,@vl_fecha_nota_credito = convert(varchar(20), fecha_nota_credito, 103)
				,@ve_cod_usuario_impresion = cod_usuario_impresion
			from nota_credito
			where cod_nota_credito = @ve_cod_nota_credito
			
			-- crea ingreso de pago asociado a la factura si es que existe
			if(@vl_cod_doc is not null)
			begin
				exec spu_ingreso_pago 'INSERT'
								,NULL			--COD_INGRESO_PAGO
								,@ve_cod_usuario_impresion  --COD_USUARIO
								,@vl_cod_empresa			--COD_EMPRESA
								,0							--OTRO_INGRESO
								,0							--OTRO_GASTO
								,@kl_ingreso_pago_emitida --COD_ESTADO_INGRESO_PAGO
								,NULL						--COD_USUARIO_ANULA	
								,NULL						--MOTIVO_ANULA
								,NULL						--COD_USUARIO_CONFIRMA
								,0						--OTRO_ANTICIPO
				
				set @vl_cod_ingreso_pago = @@identity
				-- crea ingreso_pago_factura
				exec spu_ingreso_pago_factura 'INSERT', NULL, @vl_cod_ingreso_pago, @vl_cod_doc, 'FACTURA', @vl_total_con_iva_nc 
				
				-- crea doc_ingreso_pago
				exec spu_doc_ingreso_pago 'INSERT', NULL, @vl_cod_ingreso_pago, @kl_tipo_doc_pago_nc, NULL, @vl_nro_nota_credito, @vl_fecha_nota_credito, @vl_total_con_iva_nc
				
				--confirma el ingreso de pago
				exec spu_ingreso_pago 'CONFIRMA'
									,@vl_cod_ingreso_pago
									,NULL	--COD_USUARIO
									,NULL	--COD_EMPRESA
									,NULL	--OTRO_INGRESO
									,NULL	--OTRO_GASTO
									,@kl_ingreso_pago_emitida --COD_ESTADO_INGRESO_PAGO
									,NULL	--COD_USUARIO_ANULA
									,NULL	--MOTIVO_ANULA
									,@ve_cod_usuario_impresion --COD_USUARIO_CONFIRMA
			end
		end
		else if(@ve_operacion='RECALCULA')
		begin
			select @vl_ingreso_usuario_dscto1 = ingreso_usuario_dscto1
					,@vl_ingreso_usuario_dscto2 = ingreso_usuario_dscto2
					,@vl_porc_iva = isnull(porc_iva, 0)
					,@vl_monto_dscto1 = isnull(monto_dscto1, 0)
					,@vl_porc_dscto1 = isnull(porc_dscto1, 0)
					,@vl_monto_dscto2 = isnull(monto_dscto2, 0)
					,@vl_porc_dscto2 = isnull(porc_dscto2, 0)
			from nota_credito
			where cod_nota_credito = @ve_cod_nota_credito

			select @vl_sub_total = sum(round(cantidad * precio, 0))
			from item_nota_credito
			where cod_nota_credito = @ve_cod_nota_credito

			if (@vl_ingreso_usuario_dscto1='M')
				set @vl_porc_dscto1 = round((@vl_monto_dscto1 / @vl_sub_total) * 100, 1)
			else
				set @vl_monto_dscto1 = round(@vl_sub_total * @vl_porc_dscto1 /100, 0)
				
			set @vl_sub_total_con_dscto1 = @vl_sub_total - @vl_monto_dscto1
			if (@vl_ingreso_usuario_dscto2='M')
				set @vl_porc_dscto2 = round((@vl_monto_dscto2 / @vl_sub_total_con_dscto1) * 100, 1)
			else
				set @vl_monto_dscto2 = round(@vl_sub_total_con_dscto1 * @vl_porc_dscto2 / 100, 0)
			
			set @vl_total_neto = @vl_sub_total - @vl_monto_dscto1 - @vl_monto_dscto2
			set @vl_monto_iva = round(@vl_total_neto * @vl_porc_iva / 100, 0) 
			set @vl_total_con_iva = @vl_total_neto + @vl_monto_iva

			update nota_credito		
			set	subtotal					=	@vl_sub_total		
				,porc_dscto1				=	@vl_porc_dscto1	
				,monto_dscto1				=	@vl_monto_dscto1	
				,porc_dscto2				=	@vl_porc_dscto2	
				,monto_dscto2				=	@vl_monto_dscto2	
				,total_neto					=	@vl_total_neto				
				,monto_iva					=	@vl_monto_iva		
				,total_con_iva				=	@vl_total_con_iva	
			where cod_nota_credito = @ve_cod_nota_credito 				
		end
		else if(@ve_operacion='CREA_ENTRADA')	-- Al print de NC
		begin
			select	 @vl_cod_usuario = COD_USUARIO
					,@vl_cod_bodega = COD_BODEGA
					,@vl_referencia = referencia
					,@vl_genera_entrada = genera_entrada
			  from	nota_credito
			 where	cod_nota_credito = @ve_cod_nota_credito
			
			if(@vl_genera_entrada='S' and @vl_cod_bodega  is not null) begin
				exec spu_entrada_bodega	 'INSERT'			,null		,@vl_cod_usuario
										,@vl_cod_bodega		,'NOTA_CREDITO'	,@ve_cod_nota_credito	,@vl_referencia
				set @vl_new_cod_entrada_bodega = @@identity
				
				declare c_item_nc cursor for 
				select i.ORDEN
					,i.ITEM
					,i.COD_PRODUCTO
					,i.NOM_PRODUCTO
					,i.CANTIDAD
					,i.COD_ITEM_NOTA_CREDITO
				from item_nota_credito i, producto p
				where i.cod_nota_credito= @ve_cod_nota_credito
				and i.cod_producto = p.cod_producto
				and p.maneja_inventario = 'S' 

				open c_item_nc 
				fetch c_item_nc 
				into	 @vl_orden_nc			,@vl_item_nc			,@vl_cod_producto_nc
						,@vl_nom_producto_nc	,@vl_item_cantidad_nc	,@vl_cod_item_nc
				WHILE @@FETCH_STATUS = 0 BEGIN	
						set @vl_precio = dbo.f_bodega_precio(@vl_cod_producto_nc, @vl_cod_bodega, getdate())
						exec spu_item_entrada_bodega 'INSERT'
													,null
													,@vl_new_cod_entrada_bodega
													,@vl_orden_nc
													,@vl_item_nc
													,@vl_cod_producto_nc
													,@vl_nom_producto_nc
													,@vl_item_cantidad_nc
													,@vl_precio
													,@vl_cod_item_nc
													
					fetch c_item_nc
					into	 @vl_orden_nc			,@vl_item_nc			,@vl_cod_producto_nc
							,@vl_nom_producto_nc	,@vl_item_cantidad_nc	,@vl_cod_item_nc
				end
				close c_item_nc
				deallocate c_item_nc
			end
		end	
		else if(@ve_operacion='CREA_SALIDA') begin	-- cuando se ANULA una NC anual
			select	 @vl_cod_usuario = COD_USUARIO
					,@vl_cod_bodega = COD_BODEGA
					,@vl_nro_nota_credito = nro_nota_credito
					,@vl_genera_entrada = genera_entrada
			  from	nota_credito
			 where	cod_nota_credito = @ve_cod_nota_credito
			
			if(@vl_genera_entrada='S' and @vl_cod_bodega  is not null) begin
				set @vl_referencia = 'Anula NC Nro: ' + convert(varchar, @vl_nro_nota_credito) 
				exec spu_salida_bodega	 'INSERT'			,null		,@vl_cod_usuario
										,@vl_cod_bodega		,'NOTA_CREDITO'	,@ve_cod_nota_credito	,@vl_referencia
				set @vl_new_cod_salida_bodega = @@identity
				
				declare c_item_nc cursor for 
				select i.ORDEN
					,i.ITEM
					,i.COD_PRODUCTO
					,i.NOM_PRODUCTO
					,i.CANTIDAD
					,i.COD_ITEM_NOTA_CREDITO
				from item_nota_credito i, producto p
				where i.cod_nota_credito= @ve_cod_nota_credito
				and i.cod_producto = p.cod_producto
				and p.maneja_inventario = 'S' 

				open c_item_nc 
				fetch c_item_nc 
				into	 @vl_orden_nc			,@vl_item_nc			,@vl_cod_producto_nc
						,@vl_nom_producto_nc	,@vl_item_cantidad_nc	,@vl_cod_item_nc
				WHILE @@FETCH_STATUS = 0 BEGIN	
						exec spu_item_salida_bodega 'INSERT'
													,null
													,@vl_new_cod_salida_bodega
													,@vl_orden_nc
													,@vl_item_nc
													,@vl_cod_producto_nc
													,@vl_nom_producto_nc
													,@vl_item_cantidad_nc
													,@vl_cod_item_nc
					fetch c_item_nc 
					into	 @vl_orden_nc			,@vl_item_nc			,@vl_cod_producto_nc
							,@vl_nom_producto_nc	,@vl_item_cantidad_nc	,@vl_cod_item_nc
				end
				close c_item_nc
				deallocate c_item_nc
			end
		end
		else if (@ve_operacion='SAVE_EMITIR_DTE')begin
			update nota_credito
			set RESP_EMITIR_DTE	= @ve_resp_emitir_dte
			where cod_nota_credito = @ve_cod_nota_credito
		end
		else if (@ve_operacion='SAVE_DTE') begin
			select @vl_nro_nota_credito = nro_nota_credito
			from nota_credito
			where cod_nota_credito = @ve_cod_nota_credito
	
			if (@vl_nro_nota_credito is null)begin
				update nota_credito
				set nro_nota_credito		= @ve_nro_nota_credito
					,fecha_nota_credito		= getdate()
					,cod_estado_doc_sii 		= @ve_cod_estado_doc_sii
					,cod_usuario_impresion	= @ve_cod_usuario_impresion
					,xml_dte				= @ve_xml_dte
					,track_id_dte			= @ve_track_id_dte
				where cod_nota_credito = @ve_cod_nota_credito
	
				exec spu_nota_credito 'CREA_INGRESO_PAGO', @ve_cod_nota_credito
	
				--CREA ENTRADA BODEGA
				exec spu_nota_credito 'CREA_ENTRADA', @ve_cod_nota_credito
			end
		end
END
