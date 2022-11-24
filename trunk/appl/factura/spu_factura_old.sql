ALTER PROCEDURE spu_factura
								(@ve_operacion				varchar(20)
								,@ve_cod_factura			numeric
								,@ve_cod_usuario_impresion	numeric = NULL
								,@ve_cod_usuario			numeric = NULL
								,@ve_nro_factura			numeric = NULL
								,@ve_fecha_factura			varchar(10) = null
								,@ve_cod_estado_doc_sii		numeric = NULL
								,@ve_cod_empresa			numeric = NULL
								,@ve_cod_sucursal_factura	numeric = NULL
								,@ve_cod_persona			numeric = NULL
								,@ve_referencia				varchar(100) = NULL
								,@ve_nro_orden_compra		varchar(40) = NULL
								,@ve_fecha_orden_compra_cliente			datetime = null
								,@ve_obs					text	 = NULL
								,@ve_retirado_por			varchar(100) = NULL
								,@ve_rut_retirado_por		numeric = NULL
								,@ve_dig_verif_retirado_por	varchar(1) = NULL
								,@ve_guia_transporte		varchar(100) = NULL
								,@ve_patente				varchar(100) = NULL
								,@ve_cod_bodega				numeric = NULL
								,@ve_cod_tipo_factura		numeric = NULL
								,@ve_cod_doc				numeric = NULL
								,@ve_motivo_anula			varchar(100) = NULL
								,@ve_cod_usuario_anula		numeric = NULL
								,@ve_cod_usuario_vendedor1	numeric = NULL
								,@ve_porc_vendedor1			T_PORCENTAJE = NULL
								,@ve_cod_usuario_vendedor2	numeric = NULL
								,@ve_porc_vendedor2			T_PORCENTAJE = NULL
								,@ve_cod_forma_pago			numeric = NULL
								,@ve_cod_origen_venta		numeric = NULL
								,@ve_subtotal				T_PRECIO = NULL
								,@ve_porc_dscto1			T_PORCENTAJE = NULL
								,@ve_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO = NULL
								,@ve_monto_dscto1			T_PRECIO = NULL
								,@ve_porc_dscto2			T_PORCENTAJE = NULL
								,@ve_ingreso_usuario_dscto2 T_INGRESO_USUARIO_DSCTO = NULL
								,@ve_monto_dscto2			T_PRECIO = NULL
								,@ve_total_neto				T_PRECIO = NULL
								,@ve_porc_iva				T_PORCENTAJE = NULL
								,@ve_monto_iva				T_PRECIO = NULL 
								,@ve_total_con_iva			T_PRECIO = NULL
								,@ve_porc_factura_parcial	T_PORCENTAJE = NULL
								,@ve_nom_forma_pago_otro	varchar(100)=NULL
								,@ve_genera_salida			T_SI_NO=NULL
								,@ve_tipo_doc				varchar(30)= NULL
								,@ve_cancelada				T_SI_NO=NULL
								,@ve_cod_centro_costo		varchar(30)=NULL
								,@ve_cod_vendedor_sofland	numeric=NULL
)
AS
BEGIN  
		declare		@kl_cod_estado_fa_emitida numeric,
					@kl_cod_estado_fa_impresa numeric,
					@kl_cod_estado_fa_anulada numeric,
					@kl_cod_estado_fa_enviada numeric,
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
					@vl_nro_factura numeric,
					@vl_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO,
					@vl_ingreso_usuario_dscto2 T_INGRESO_USUARIO_DSCTO,
					@vl_porc_iva T_PORCENTAJE,
					@vl_monto_dscto1 T_PRECIO,
					@vl_porc_dscto1 T_PORCENTAJE,
					@vl_monto_dscto2 T_PRECIO,
					@vl_porc_dscto2 T_PORCENTAJE,
					@vl_sub_total T_PRECIO,
					@vl_sub_total_con_dscto1 T_PRECIO,
					@vl_total_neto T_PRECIO,
					@vl_monto_iva T_PRECIO,
					@vl_total_con_iva T_PRECIO,
					@vl_cod_doc				numeric,
					@vl_total_fa			T_PRECIO, 
					@vl_monto_asignado		T_PRECIO,
					@vl_por_asignar_fa		T_PRECIO,
					@vl_new_monto_asignado_nv	T_PRECIO,
					@vl_cod_ingreso_pago_factura	numeric,
					@vl_cod_ingreso_pago numeric,
					@vl_cod_monto_doc_asignado		numeric, 
					@vl_monto_doc_asignado			numeric,
					@vl_new_ingreso_pago_factura	numeric,
					@vl_cod_doc_ingreso_pago		numeric
				,@vl_cod_usuario	NUMERIC
				,@vl_cod_bodega		NUMERIC
				,@vl_referencia		VARCHAR(100)
				,@vl_new_cod_salida_bodega	NUMERIC
				,@vl_orden_factura	NUMERIC
				,@vl_item_factura	NUMERIC
				,@vl_cod_producto_factura	VARCHAR(30)
				,@vl_nom_producto_factura	VARCHAR(100)
				,@vl_item_cantidad_factura	NUMERIC
				,@vl_cod_item_factura		NUMERIC
				,@vl_genera_salida			varchar(1)
				,@vl_new_cod_entrada_bodega	NUMERIC

		set @kl_cod_estado_fa_emitida = 1  --- estado de la gd = emitida
		set @kl_cod_estado_fa_impresa = 2  --- estado de la gd = impresa	
		set @kl_cod_estado_fa_anulada = 4  --- estado de la gd = anulada
		set @kl_cod_estado_fa_enviada = 3  --- estado de la gd = enviada SII
--FACTURA EXENTA 10/08/2010 MU	SE LE ASIGNA VALOR EN EL PRINT DEPENDIENDO DE PORC_IVA
		--set @kl_cod_tipo_doc_sii = 1  --- tipo de doc_sii = factura
	
		if (@ve_operacion='UPDATE') 
			begin
				select	@vl_cod_usuario_anula = f.cod_usuario_anula,
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
						@vl_fax = s.fax,
						@vl_cod_doc = f.cod_doc	-- NV
				from factura f, sucursal s, empresa e
				where f.cod_factura = @ve_cod_factura and
					f.cod_sucursal_factura = s.cod_sucursal and
					f.cod_empresa = e.cod_empresa

				select	@vl_nom_persona = nom_persona,
						@vl_mail = email,
						@vl_cod_cargo = cod_cargo
				from persona
				where cod_persona = @ve_cod_persona

				-- si estado = emitida, hace update a empresa, sucursal y persona para grabar en la FA 			
				if (@ve_cod_estado_doc_sii = @kl_cod_estado_fa_emitida )
					update factura
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
					where cod_factura = @ve_cod_factura
				
				-- si estado = anulada, hace update a datos de anulacion en la FA
				else if (@ve_cod_estado_doc_sii = @kl_cod_estado_fa_anulada) and (@vl_cod_usuario_anula is NULL) begin-- estado de la fa = anulada 
					update factura
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_factura = @ve_cod_factura

					-- traspaso los pagos si existen a la NV
					if (@vl_cod_doc is not null)
						update ingreso_pago_factura
						set tipo_doc = 'NOTA_VENTA'
							,cod_doc = @vl_cod_doc
						where tipo_doc = 'FACTURA'
						  and cod_doc = @ve_cod_factura

					--CREA ENTRADA BODEGA
					exec spu_factura 'CREA_ENTRADA', @ve_cod_factura

				end
						
				-- update general
				update factura		
				set		cod_usuario				= @ve_cod_usuario	
						,nro_factura			= @ve_nro_factura
						,fecha_factura			= dbo.to_date(@ve_fecha_factura)
						,cod_estado_doc_sii		= @ve_cod_estado_doc_sii		
						,cod_empresa			= @ve_cod_empresa				
						,cod_sucursal_factura	= @ve_cod_sucursal_factura
						,cod_persona			= @ve_cod_persona				
						,referencia				= @ve_referencia				
						,nro_orden_compra		= @ve_nro_orden_compra
						,fecha_orden_compra_cliente		= @ve_fecha_orden_compra_cliente
						,obs					= @ve_obs						
						,retirado_por			= @ve_retirado_por			
						,rut_retirado_por		= @ve_rut_retirado_por		
						,dig_verif_retirado_por	= @ve_dig_verif_retirado_por	
						,guia_transporte		= @ve_guia_transporte			
						,patente				= @ve_patente	
						,cod_bodega				= @ve_cod_bodega				
						,cod_tipo_factura		= @ve_cod_tipo_factura	
						,cod_doc				= @ve_cod_doc						
						,cod_usuario_impresion	= @ve_cod_usuario_impresion
						,cod_usuario_vendedor1	= @ve_cod_usuario_vendedor1
						,porc_vendedor1			= @ve_porc_vendedor1
						,cod_usuario_vendedor2	= @ve_cod_usuario_vendedor2
						,porc_vendedor2			= @ve_porc_vendedor2
						,cod_forma_pago			= @ve_cod_forma_pago
						,cod_origen_venta		= @ve_cod_origen_venta
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
						,porc_factura_parcial	= @ve_porc_factura_parcial
						,nom_forma_pago_otro	= @ve_nom_forma_pago_otro
						,genera_salida			= @ve_genera_salida
						,cancelada				= @ve_cancelada		
						,cod_centro_costo		= @ve_cod_centro_costo
						,cod_vendedor_sofland	= @ve_cod_vendedor_sofland
				where cod_factura = @ve_cod_factura
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
					s.cod_sucursal = @ve_cod_sucursal_factura 

				select @vl_nom_persona = nom_persona,
					@vl_mail = email,
					@vl_cod_cargo = cod_cargo
				from persona	
				where cod_persona = @ve_cod_persona

				insert into factura
						(fecha_registro			
						,cod_usuario				
						,nro_factura	
						,cod_estado_doc_sii		
						,cod_empresa				
						,cod_sucursal_factura	
						,cod_persona				
						,referencia				
						,nro_orden_compra
						,fecha_orden_compra_cliente
						,obs						
						,retirado_por			
						,rut_retirado_por		
						,dig_verif_retirado_por	
						,guia_transporte			
						,patente
						,cod_bodega				
						,cod_tipo_factura	
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
						,cod_usuario_vendedor1
						,porc_vendedor1
						,cod_usuario_vendedor2
						,porc_vendedor2
						,cod_forma_pago
						,cod_origen_venta
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
						,porc_factura_parcial
						,nom_forma_pago_otro
						,genera_salida
						,tipo_doc
						,cancelada
						,cod_centro_costo
						,cod_vendedor_sofland)					
					values 
						(getdate()
						,@ve_cod_usuario
						,@ve_nro_factura
						,@ve_cod_estado_doc_sii		
						,@ve_cod_empresa				
						,@ve_cod_sucursal_factura	
						,@ve_cod_persona				
						,@ve_referencia				
						,@ve_nro_orden_compra
						,@ve_fecha_orden_compra_cliente
						,@ve_obs						
						,@ve_retirado_por			
						,@ve_rut_retirado_por		
						,@ve_dig_verif_retirado_por	
						,@ve_guia_transporte			
						,@ve_patente		
						,@ve_cod_bodega
						,@ve_cod_tipo_factura
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
						,@ve_cod_usuario_vendedor1
						,@ve_porc_vendedor1
						,@ve_cod_usuario_vendedor2
						,@ve_porc_vendedor2
						,@ve_cod_forma_pago
						,@ve_cod_origen_venta
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
						,@ve_porc_factura_parcial
						,@ve_nom_forma_pago_otro
						,@ve_genera_salida
						,@ve_tipo_doc
						,@ve_cancelada
						,@ve_cod_centro_costo
						,@ve_cod_vendedor_sofland)
				end 
			else if (@ve_operacion='DELETE') 
				begin
					delete GUIA_DESPACHO_FACTURA
    				where cod_factura = @ve_cod_factura

					delete item_factura
    				where cod_factura = @ve_cod_factura
					
					delete factura
					where cod_factura = @ve_cod_factura
				end
			else if (@ve_operacion='PRINT') 	
				begin
					select	@vl_nro_factura = nro_factura
							,@vl_porc_iva = porc_iva
					from factura
					where  cod_factura = @ve_cod_factura

					if (@vl_nro_factura is null)begin

							if(@vl_porc_iva = 0)--FACTURA EXENTA mu	
								set @kl_cod_tipo_doc_sii = 5
							else--FACTURA NORMAL mu	
								set @kl_cod_tipo_doc_sii = 1

						update factura
						set nro_factura = dbo.f_get_nro_doc_sii (@kl_cod_tipo_doc_sii , @ve_cod_usuario_impresion),
							fecha_factura = getdate(),
							cod_estado_doc_sii = @kl_cod_estado_fa_impresa,
							cod_usuario_impresion = @ve_cod_usuario_impresion
						where  cod_factura = @ve_cod_factura

						exec spu_factura 'REASIGNA_PAGO', @ve_cod_factura
						
						--CREA SALIDA BODEGA
						exec spu_factura 'CREA_SALIDA', @ve_cod_factura 
					end --if (@vl_nro_factura is null)
				end
			else if (@ve_operacion='ENVIA_DTE') 	
				begin
					declare @vl_count numeric

					select	@vl_nro_factura = nro_factura
							,@vl_porc_iva = porc_iva
					from	factura
					where	cod_factura = @ve_cod_factura

					if (@vl_nro_factura is null)
						begin
							if(@vl_porc_iva = 0)--FACTURA EXENTA mu	
								begin
									set @vl_nro_factura = 501 --numero inicial asignado por SP 15/12/2010 nro_actual = 501
									
									select	@vl_count = count(*)
									from	factura
									where	cod_estado_doc_sii = @kl_cod_estado_fa_enviada
									and		porc_iva = 0
	
									if(@vl_count > 0)
										begin
											select	@vl_nro_factura = max(nro_factura) + 1
											from	factura
											where	cod_estado_doc_sii = @kl_cod_estado_fa_enviada
											and		porc_iva = 0
										end
										
									update factura
									set nro_factura = @vl_nro_factura,
										fecha_factura = getdate(),
										cod_estado_doc_sii = @kl_cod_estado_fa_enviada,
										cod_usuario_impresion = @ve_cod_usuario_impresion
									where  cod_factura = @ve_cod_factura
								end 							
							else--FACTURA NORMAL mu
								begin
									set @vl_nro_factura = 60001 --numero inicial asignado por SP 15/12/2010 nro_actual = 60001
	
									select	@vl_count = count(*)
									from	factura
									where	cod_estado_doc_sii = @kl_cod_estado_fa_enviada
									and		porc_iva <> 0
									
									if(@vl_count > 0)
										begin
											select	@vl_nro_factura = max(nro_factura) + 1
											from	factura
											where	cod_estado_doc_sii = @kl_cod_estado_fa_enviada
											and		porc_iva <> 0
										end
	
									update factura
									set nro_factura = @vl_nro_factura,
										fecha_factura = getdate(),
										cod_estado_doc_sii = @kl_cod_estado_fa_enviada,
										cod_usuario_impresion = @ve_cod_usuario_impresion
									where  cod_factura = @ve_cod_factura
								end
							exec spu_factura 'REASIGNA_PAGO', @ve_cod_factura
							
							--CREA SALIDA BODEGA
							exec spu_factura 'CREA_SALIDA', @ve_cod_factura
						end --if (@vl_nro_factura is null)
					else
						begin
							declare	@vl_cod_factura	varchar(20)
							
							set @vl_cod_factura = convert(varchar, @ve_cod_factura)
							exec sp_log_cambio 'FACTURA', @vl_cod_factura, @ve_cod_usuario_impresion, 'R'	--Reenvia la FA a SII
						end
				end
			else if (@ve_operacion='REASIGNA_PAGO') begin
				select @vl_cod_doc = cod_doc
						,@vl_total_fa = total_con_iva
				from factura
				where  cod_factura = @ve_cod_factura

				if (@vl_cod_doc is not null)--cod de NV
				begin
					declare c_ingreso_pago_factura cursor for 
					select cod_ingreso_pago_factura
						,cod_ingreso_pago
						,monto_asignado
					from ingreso_pago_factura
					where tipo_doc = 'NOTA_VENTA'
						and cod_doc = @vl_cod_doc
					
					open c_ingreso_pago_factura 
					fetch c_ingreso_pago_factura into @vl_cod_ingreso_pago_factura, @vl_cod_ingreso_pago, @vl_monto_asignado
					WHILE @@FETCH_STATUS = 0 BEGIN								
						-- cursor en monto_doc_asignado
						declare c_monto_doc_asignado cursor for 
						select cod_monto_doc_asignado
							,monto_doc_asignado
							,cod_doc_ingreso_pago
						from monto_doc_asignado
						where cod_ingreso_pago_factura = @vl_cod_ingreso_pago_factura

						insert into ingreso_pago_factura (cod_ingreso_pago, monto_asignado, tipo_doc, cod_doc)
						values (@vl_cod_ingreso_pago, 0, 'FACTURA', @ve_cod_factura)
						set @vl_new_ingreso_pago_factura = @@identity

						open c_monto_doc_asignado 
						fetch c_monto_doc_asignado into @vl_cod_monto_doc_asignado, @vl_monto_doc_asignado, @vl_cod_doc_ingreso_pago
						WHILE (@@FETCH_STATUS = 0 and @vl_total_fa > 0)BEGIN	
							if(@vl_monto_doc_asignado >= @vl_total_fa) begin --se debe asignar solo lo necesario
								set @vl_por_asignar_fa = @vl_total_fa

								-- borrar monto_doc_asignado de la NV
							end
							else
								set @vl_por_asignar_fa = @vl_monto_doc_asignado

							-- monto_doc_asignado nuevo
							insert into monto_doc_asignado (cod_doc_ingreso_pago, cod_ingreso_pago_factura, monto_doc_asignado)
							values (@vl_cod_doc_ingreso_pago, @vl_new_ingreso_pago_factura, @vl_por_asignar_fa)
							-- actualiza monto_doc_asignado antiguo
							update monto_doc_asignado
							set monto_doc_asignado = monto_doc_asignado - @vl_por_asignar_fa
							where cod_monto_doc_asignado = @vl_cod_monto_doc_asignado
							-- rebaja el total por pagar
							set @vl_total_fa = @vl_total_fa - @vl_por_asignar_fa
							-- borra si monto = 0
							--delete monto_doc_asignado
							--where cod_monto_doc_asignado = @vl_cod_monto_doc_asignado
							  --and monto_doc_asignado = 0

							-- ingreso_pago_factura nuevo
							update ingreso_pago_factura
							set monto_asignado = monto_asignado + @vl_por_asignar_fa
							where cod_ingreso_pago_factura = @vl_new_ingreso_pago_factura
							-- actualiza ingreso_pago_factura antiguo
							update ingreso_pago_factura
							set monto_asignado = monto_asignado - @vl_por_asignar_fa
							where cod_ingreso_pago_factura = @vl_cod_ingreso_pago_factura
					
							fetch c_monto_doc_asignado into @vl_cod_monto_doc_asignado, @vl_monto_doc_asignado, @vl_cod_doc_ingreso_pago
						end
						close c_monto_doc_asignado
						deallocate c_monto_doc_asignado
							
						-- borra las tablas intermedias con monto asignado en cero
						delete monto_doc_asignado
						where monto_doc_asignado = 0

						delete ingreso_pago_factura
						where monto_asignado = 0

						fetch c_ingreso_pago_factura into @vl_cod_ingreso_pago_factura, @vl_cod_ingreso_pago, @vl_monto_asignado
					end
					close c_ingreso_pago_factura
					deallocate c_ingreso_pago_factura
				end --if (@vl_cod_doc is not null)
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
					from factura
					where cod_factura = @ve_cod_factura

					select @vl_sub_total = sum(round(cantidad * precio, 0))
					from item_factura
					where cod_factura = @ve_cod_factura

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

					update factura		
					set	subtotal					=	@vl_sub_total		
						,porc_dscto1				=	@vl_porc_dscto1	
						,monto_dscto1				=	@vl_monto_dscto1	
						,porc_dscto2				=	@vl_porc_dscto2	
						,monto_dscto2				=	@vl_monto_dscto2	
						,total_neto					=	@vl_total_neto				
						,monto_iva					=	@vl_monto_iva		
						,total_con_iva				=	@vl_total_con_iva	
					where cod_factura = @ve_cod_factura
				end	
			else if(@ve_operacion='CREA_SALIDA')
				begin
					-- al print,  dte cod_bodega crea salida
					select	 @vl_cod_usuario = COD_USUARIO
							,@vl_cod_bodega = COD_BODEGA
							,@vl_referencia = REFERENCIA
							,@vl_nro_factura = nro_factura 
							,@vl_genera_salida = genera_salida
					  from	factura
					 where	cod_factura = @ve_cod_factura
					
					if(@vl_genera_salida='S' and @vl_cod_bodega  is not null) begin
						exec spu_salida_bodega	 'INSERT'			,null		,@vl_cod_usuario
												,@vl_cod_bodega		,'FACTURA'	,@ve_cod_factura	,@vl_referencia
						set @vl_new_cod_salida_bodega = @@identity
						
						declare c_item_salida_factura cursor for 
						select ifa.ORDEN
							,ifa.ITEM
							,ifa.COD_PRODUCTO
							,ifa.NOM_PRODUCTO
							,ifa.CANTIDAD
							,ifa.COD_ITEM_FACTURA
						from item_factura ifa, producto p
						where ifa.cod_factura = @ve_cod_factura
						and ifa.cod_producto = p.cod_producto
						and p.maneja_inventario = 'S' 

						open c_item_salida_factura 
						fetch c_item_salida_factura 
						into	 @vl_orden_factura			,@vl_item_factura			,@vl_cod_producto_factura
								,@vl_nom_producto_factura	,@vl_item_cantidad_factura	,@vl_cod_item_factura
						WHILE @@FETCH_STATUS = 0 BEGIN	
								exec spu_item_salida_bodega 'INSERT'
															,null
															,@vl_new_cod_salida_bodega
															,@vl_orden_factura
															,@vl_item_factura
															,@vl_cod_producto_factura
															,@vl_nom_producto_factura
															,@vl_item_cantidad_factura
															,@vl_cod_item_factura
							fetch c_item_salida_factura 
							into	 @vl_orden_factura			,@vl_item_factura			,@vl_cod_producto_factura
									,@vl_nom_producto_factura	,@vl_item_cantidad_factura	,@vl_cod_item_factura
						end
						close c_item_salida_factura
						deallocate c_item_salida_factura
					end
				end	
			else if(@ve_operacion='CREA_ENTRADA')	-- cuando se anula una FA MANUAL
				begin
					select	 @vl_cod_usuario = COD_USUARIO
							,@vl_cod_bodega = COD_BODEGA
							,@vl_nro_factura = nro_factura 
							,@vl_genera_salida = genera_salida
							,@vl_nro_factura = nro_factura
					  from	factura
					 where	cod_factura = @ve_cod_factura
					
					if(@vl_genera_salida='S' and @vl_cod_bodega  is not null) begin
						set @vl_referencia = 'Anula Factura Nro: ' + convert(varchar, @vl_nro_factura) 
						exec spu_entrada_bodega	 'INSERT'			,null		,@vl_cod_usuario
												,@vl_cod_bodega		,'FACTURA'	,@ve_cod_factura	,@vl_referencia
						set @vl_new_cod_entrada_bodega = @@identity
						
						declare c_item_entrada_factura cursor for 
						select ifa.ORDEN
							,ifa.ITEM
							,ifa.COD_PRODUCTO
							,ifa.NOM_PRODUCTO
							,ifa.CANTIDAD
							,ifa.COD_ITEM_FACTURA
						from item_factura ifa, producto p
						where ifa.cod_factura = @ve_cod_factura
						and ifa.cod_producto = p.cod_producto
						and p.maneja_inventario = 'S' 

						open c_item_entrada_factura 
						fetch c_item_entrada_factura 
						into	 @vl_orden_factura			,@vl_item_factura			,@vl_cod_producto_factura
								,@vl_nom_producto_factura	,@vl_item_cantidad_factura	,@vl_cod_item_factura
						WHILE @@FETCH_STATUS = 0 BEGIN	
								exec spu_item_entrada_bodega 'INSERT'
															,null
															,@vl_new_cod_entrada_bodega
															,@vl_orden_factura
															,@vl_item_factura
															,@vl_cod_producto_factura
															,@vl_nom_producto_factura
															,@vl_item_cantidad_factura
															,@vl_cod_item_factura
															
							fetch c_item_entrada_factura 
							into	 @vl_orden_factura			,@vl_item_factura			,@vl_cod_producto_factura
									,@vl_nom_producto_factura	,@vl_item_cantidad_factura	,@vl_cod_item_factura
						end
						close c_item_entrada_factura
						deallocate c_item_entrada_factura
					end
				end	
END
go