---------------------spu_nota_debito-----------------
ALTER PROCEDURE [dbo].[spu_nota_debito] (@ve_operacion		varchar(20)
								,@ve_cod_nota_debito		numeric = NULL
								,@ve_cod_usuario_impresion	numeric = NULL
								,@ve_cod_usuario			numeric = NULL
								,@ve_nro_nota_debito		numeric = NULL
								,@ve_fecha_nota_debito		varchar(10) = null
								,@ve_cod_estado_doc_sii		numeric = NULL	
								,@ve_cod_empresa			numeric = NULL								
								,@ve_referencia				varchar(100) = NULL
								,@ve_cod_sucursal_factura	numeric = NULL
								,@ve_cod_tipo_nota_debito	numeric = NULL
								,@ve_tipo_nota_debito		varchar(100) = null
								,@ve_cod_doc				numeric = NULL
								,@ve_cod_persona			numeric = NULL								
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
								)

AS
BEGIN  
	declare		
				@vl_rut numeric
				,@vl_dig_verif varchar (1)
				,@vl_nom_empresa varchar(100)
				,@vl_giro varchar(100)
				,@vl_nom_sucursal varchar (100)
				,@vl_direccion  varchar (100)
				,@vl_cod_comuna numeric
				,@vl_cod_ciudad numeric
				,@vl_telefono varchar (100)
				,@vl_fax varchar (100)
				,@vl_nom_persona varchar(100)
				,@vl_mail varchar(100)
				,@vl_cod_cargo numeric
				,@vl_nro_nota_debito numeric
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
				,@kl_tipo_doc_pago_nd numeric
				,@kl_ingreso_pago_confirmada numeric
				,@vl_cod_empresa numeric
				,@vl_cod_doc numeric
				,@vl_total_con_iva_nd T_PRECIO
				,@vl_fecha_nota_debito varchar(20)
				,@kl_ingreso_pago_anulada numeric
				,@vl_cod_usuario	NUMERIC
				,@vl_referencia		VARCHAR(100)
				,@vl_orden_nd	NUMERIC
				,@vl_item_nd	NUMERIC
				,@vl_cod_producto_nd	VARCHAR(30)
				,@vl_nom_producto_nd	VARCHAR(100)
				,@vl_item_cantidad_nd	NUMERIC
				,@vl_cod_item_nd		NUMERIC
				,@vl_precio					numeric
				,@kl_cod_estado_nd_emitida numeric
				,@kl_cod_estado_nd_impresa numeric
				,@kl_cod_estado_nd_enviada numeric
				,@kl_cod_estado_nd_anulada numeric
				,@kl_cod_tipo_doc_sii numeric
				,@vl_cod_usuario_anula numeric
				
		set @kl_cod_estado_nd_emitida = 1  --- estado de la nd = emitida
		set @kl_cod_estado_nd_impresa = 2  --- estado de la nd = impresa
		set @kl_cod_estado_nd_enviada = 3  --- estado de la nd = enviada
		set @kl_cod_estado_nd_anulada = 4  --- estado de la nd = anulada
		set @kl_cod_tipo_doc_sii = 4  --- tipo de doc_sii = nota_debito
		
			 if (@ve_operacion='INSERT') 
			begin
					select	@vl_rut				= e.rut
							,@vl_dig_verif		= e.dig_verif	
							,@vl_nom_empresa	= e.nom_empresa	
							,@vl_giro			= e.giro
							,@vl_nom_sucursal	= s.nom_sucursal
							,@vl_direccion		= s.direccion		
							,@vl_cod_comuna		= s.cod_comuna
							,@vl_cod_ciudad		= s.cod_ciudad 
							,@vl_telefono		= s.telefono
							,@vl_fax			= s.fax
					from empresa e, sucursal s
					where e.cod_empresa = @ve_cod_empresa and 
					s.cod_sucursal = @ve_cod_sucursal_factura 

					select @vl_nom_persona = nom_persona,
					@vl_mail = email
					,@vl_cod_cargo = cod_cargo
					from persona	
					where cod_persona = @ve_cod_persona

					insert into nota_debito
								(fecha_registro
					           ,cod_usuario
					           ,nro_nota_debito
					           ,fecha_nota_debito
					           ,cod_estado_doc_sii
					           ,cod_empresa
					           ,cod_sucursal
					           ,cod_persona
					           ,cod_tipo_nota_debito
					           ,tipo_doc
					           ,cod_doc
					           ,referencia
					           ,rut
					           ,dig_verif
					           ,nom_empresa
					           ,giro
					           ,nom_sucursal
					           ,direccion
					           ,cod_ciudad
					           ,cod_comuna
					           ,telefono
					           ,fax
					           ,nom_persona
					           ,mail
					           ,cod_cargo
					           ,cod_usuario_impresion
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
					           )
							values (getdate()
								,@ve_cod_usuario
								,@ve_nro_nota_debito
								,@ve_fecha_nota_debito
								,@ve_cod_estado_doc_sii		
								,@ve_cod_empresa
								,@ve_cod_sucursal_factura
								,@ve_cod_persona
								,@ve_cod_tipo_nota_debito
								,@ve_tipo_nota_debito
								,@ve_cod_doc
								,@ve_referencia	
								,@vl_rut						
								,@vl_dig_verif			
								,@vl_nom_empresa				
								,@vl_giro					
								,@vl_nom_sucursal			
								,@vl_direccion				
								,@vl_cod_ciudad	
								,@vl_cod_comuna	
								,@vl_telefono				
								,@vl_fax						
								,@vl_nom_persona				
								,@vl_mail	
								,@vl_cod_cargo
								,NULL
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
								)
			end 

			if (@ve_operacion='UPDATE') 
				begin
					select	@vl_rut				 = e.rut
							,@vl_dig_verif		 = e.dig_verif	
							,@vl_nom_empresa	 = e.nom_empresa	
							,@vl_giro			 = e.giro
							,@vl_nom_sucursal	 = s.nom_sucursal
							,@vl_direccion		 = s.direccion	
							,@vl_cod_comuna		 = s.cod_comuna
							,@vl_cod_ciudad		 = s.cod_ciudad 
							,@vl_telefono		 = s.telefono
							,@vl_fax			 = s.fax
							
					from nota_debito nd, empresa e, sucursal s
					where nd.cod_nota_debito = @ve_cod_nota_debito 
					and e.cod_empresa = @ve_cod_empresa 
					and s.cod_sucursal = @ve_cod_sucursal_factura 

					select @vl_nom_persona = nom_persona,
						@vl_mail = email
						,@vl_cod_cargo = cod_cargo
					from persona	
					where cod_persona = @ve_cod_persona
					
					-- si estado = emitida, hace update a empresa, sucursal y persona para grabar en la ND 					
					if (@ve_cod_estado_doc_sii = @kl_cod_estado_nd_emitida )
						update nota_debito
						set rut			 = @vl_rut,
							dig_verif	 = @vl_dig_verif,	
							nom_empresa	 = @vl_nom_empresa,	
							giro		 = @vl_giro,
							nom_sucursal = @vl_nom_sucursal, 
							direccion	 = @vl_direccion,		
							cod_comuna	 = @vl_cod_comuna ,
							cod_ciudad	 = @vl_cod_ciudad, 
							telefono	 = @vl_telefono,
							fax			 = @vl_fax, 
							nom_persona	 = @vl_nom_persona,
							mail		 = @vl_mail,
							cod_cargo	 = @vl_cod_cargo
						where cod_nota_debito = @ve_cod_nota_debito
					--- si estado = anula solo update a campos anula.
					else if (@ve_cod_estado_doc_sii = @kl_cod_estado_nd_anulada) and (@vl_cod_usuario_anula is NULL) -- estado de la nc = anulada 
					begin
						update nota_debito
						set fecha_anula			= getdate ()
							,motivo_anula		= @ve_motivo_anula			
							,cod_usuario_anula	= @ve_cod_usuario_anula				
						where cod_nota_debito 	= @ve_cod_nota_debito
					end
					-- update general
					update nota_debito		
					set		cod_usuario				= @ve_cod_usuario	
							,nro_nota_debito		= @ve_nro_nota_debito
							,fecha_nota_debito		= dbo.to_date(@ve_fecha_nota_debito)
							,cod_estado_doc_sii		= @ve_cod_estado_doc_sii		
							,cod_empresa			= @ve_cod_empresa
							,cod_persona			= @ve_cod_persona				
							,referencia				= @ve_referencia					
							,cod_doc				= @ve_cod_doc						
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
							
					where cod_nota_debito = @ve_cod_nota_debito
				end
				
				else if (@ve_operacion='DELETE') 
					begin
						delete item_nota_debito
						where cod_nota_debito = @ve_cod_nota_debito
						
						delete nota_debito
						where cod_nota_debito = @ve_cod_nota_debito
				end
				else if (@ve_operacion='ENVIA_DTE') 
				begin
					declare @vl_count numeric
							,@vl_nro_nd_rental	numeric 
							
					select	@vl_nro_nota_debito = nro_nota_debito
					from	nota_debito
					where	cod_nota_debito = @ve_cod_nota_debito

					if (@vl_nro_nota_debito is null)
						begin
							select	@vl_count = count(*)
							from	nota_debito
							where	cod_estado_doc_sii = @kl_cod_estado_nd_enviada
							
							if(@vl_count > 0)begin
								select @vl_nro_nota_debito = max(nro_nota_debito) + 1
								from nota_debito
								where cod_estado_doc_sii = @kl_cod_estado_nd_enviada
								
								select   @vl_nro_nd_rental=  max(nro_nota_debito) + 1
								from    RENTAL.dbo.nota_debito
								where    cod_estado_doc_sii = @kl_cod_estado_nd_enviada
								
								if (@vl_nro_nd_rental > @vl_nro_nota_debito)
									set @vl_nro_nota_debito  = @vl_nro_nd_rental
							end
							else 
							begin
								set @vl_nro_nota_debito = 9001 --numero inicial asignado por SP 15/12/2010 nro_actual = 9001
							end
												
							update nota_debito
							set nro_nota_debito = @vl_nro_nota_debito,
								fecha_nota_debito = getdate(),
								cod_estado_doc_sii = @kl_cod_estado_nd_enviada,
								cod_usuario_impresion = @ve_cod_usuario_impresion
							where cod_nota_debito = @ve_cod_nota_debito
							/*
							exec spu_nota_debito 'CREA_INGRESO_PAGO', @ve_cod_nota_debito

							--CREA ENTRADA BODEGA
							exec spu_nota_debito 'CREA_ENTRADA', @ve_cod_nota_debito
							*/
						end
					else
						begin
							declare	@vl_cod_nota_debito	varchar(20)
							
							set @vl_cod_nota_debito = convert(varchar, @ve_cod_nota_debito)
							exec sp_log_cambio 'NOTA_DEBITO', @vl_cod_nota_debito, @ve_cod_usuario_impresion, 'R'	--Reenvia la ND a SII
						end

				end
			else if (@ve_operacion='PRINT') 	
			begin
					select @vl_nro_nota_debito = nro_nota_debito
					from nota_debito
					where cod_nota_debito = @ve_cod_nota_debito

					if (@vl_nro_nota_debito is null)
					begin
						update nota_debito
						set nro_nota_debito = dbo.f_get_nro_doc_sii (@kl_cod_tipo_doc_sii , @ve_cod_usuario_impresion),
							fecha_nota_debito = getdate(),
							cod_estado_doc_sii = @kl_cod_estado_nd_impresa,
							cod_usuario_impresion = @ve_cod_usuario_impresion
						where cod_nota_debito = @ve_cod_nota_debito
						/*
						exec spu_nota_debito 'CREA_INGRESO_PAGO', @ve_cod_nota_debito

						--CREA ENTRADA BODEGA
						exec spu_nota_debito 'CREA_ENTRADA', @ve_cod_nota_debito
						*/
					end
			end 
end