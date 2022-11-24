-------------------- spu_guia_recepcion ---------------------------------
ALTER PROCEDURE [dbo].[spu_guia_recepcion]
			(@ve_operacion					varchar(20)
			,@ve_cod_guia_recepcion			numeric
			,@ve_cod_usuario				numeric		= NULL
			,@ve_cod_empresa				numeric		= NULL
			,@ve_cod_sucursal				numeric		= NULL
			,@ve_cod_persona				numeric		= NULL
			,@ve_cod_estado_guia_recepcion	numeric		= NULL
			,@ve_cod_tipo_guia_recepcion	numeric		= NULL
			,@ve_tipo_doc					varchar(30)	= NULL
			,@ve_nro_doc					numeric		= NULL
			,@ve_cod_doc					numeric		= NULL
			,@ve_obs						text		= NULL
			,@ve_cod_usuario_anula			numeric		= NULL
			,@ve_motivo_anula				varchar(100)= NULL)

AS
BEGIN
		declare	@kl_cod_estado_gr_ingresada	numeric,
				@kl_cod_estado_gr_anulada	numeric,
				@kl_cod_estado_gr_impresa	numeric,
				@vl_cod_usuario_anula		numeric,
				@KL_BODEGA_ORIGEN			numeric,
				@KL_BODEGA_DESTINO			numeric,
				@vl_referencia				varchar(100),
				@vl_nom_bodega_origen		varchar(100),
				@vl_nom_bodega_destino		varchar(100),
				@vl_cod_estado_guia_recepcion	numeric

		set @kl_cod_estado_gr_ingresada = 1  --- estado de la gr = ingresada
		set @kl_cod_estado_gr_impresa = 2  --- estado de la gr = impresa
		set @kl_cod_estado_gr_anulada = 3  --- estado de la gr = anulada	
		
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE guia_recepcion		
				SET		
							cod_empresa					=	@ve_cod_empresa	
							,cod_sucursal				=	@ve_cod_sucursal
							,cod_persona				=	@ve_cod_persona
							,cod_estado_guia_recepcion	=	@ve_cod_estado_guia_recepcion	
							,cod_tipo_guia_recepcion	=	@ve_cod_tipo_guia_recepcion	
							,tipo_doc					=	@ve_tipo_doc
							,nro_doc					=	@ve_nro_doc
							,obs						=	@ve_obs

				WHERE cod_guia_recepcion = @ve_cod_guia_recepcion
				if (@ve_cod_estado_guia_recepcion = @kl_cod_estado_gr_anulada) and (@vl_cod_usuario_anula is NULL)begin -- estado de la GR = anulada 
					update guia_recepcion
					set fecha_anula				= getdate ()
						,motivo_anula			= @ve_motivo_anula			
						,cod_usuario_anula		= @ve_cod_usuario_anula				
					where cod_guia_recepcion	= @ve_cod_guia_recepcion

					exec spu_guia_recepcion 'MUEVE_BODEGA', @ve_cod_guia_recepcion, @ve_cod_usuario_anula
				end
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into guia_recepcion
					(fecha_guia_recepcion
					,cod_usuario
					,cod_empresa
					,cod_sucursal
					,cod_persona
					,cod_estado_guia_recepcion	
					,cod_tipo_guia_recepcion
					,tipo_doc
					,nro_doc
					,cod_doc
					,obs)
				values 
					(getdate()
					,@ve_cod_usuario	
					,@ve_cod_empresa	
					,@ve_cod_sucursal
					,@ve_cod_persona
					,@ve_cod_estado_guia_recepcion
					,@ve_cod_tipo_guia_recepcion
					,@ve_tipo_doc
					,@ve_nro_doc
					,@ve_cod_doc
					,@ve_obs)
			end 
		else if (@ve_operacion='DELETE_ALL') 
				begin
					delete item_guia_recepcion 
    				where cod_guia_recepcion = @ve_cod_guia_recepcion
					
					delete guia_recepcion
					where cod_guia_recepcion = @ve_cod_guia_recepcion
				end 
		else if (@ve_operacion='PRINT') begin
			select @vl_cod_estado_guia_recepcion = cod_estado_guia_recepcion
			from guia_recepcion
			where  cod_guia_recepcion = @ve_cod_guia_recepcion
			
			if (@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_ingresada)begin
				update guia_recepcion
				set cod_estado_guia_recepcion = @kl_cod_estado_gr_impresa
				where  cod_guia_recepcion = @ve_cod_guia_recepcion
			
				exec spu_guia_recepcion 'MUEVE_BODEGA', @ve_cod_guia_recepcion, @ve_cod_usuario
			end
			
		end
		else if (@ve_operacion='MUEVE_BODEGA') begin
				declare
				@K_GR_ARRIENDO					numeric
				,@K_BODEGA_POR_RECICLAR			numeric
				,@K_GR_EMITIDA					numeric
				,@vl_cod_tipo_guia_recepcion	numeric
				,@vl_cod_arriendo				numeric
				,@vl_cod_bodega					numeric
				,@vl_cod_salida_bodega			numeric
				,@vl_cod_entrada_bodega			numeric
				,@vc_cod_producto				varchar(30)
				,@vc_nom_producto				varchar(100)
				,@vc_cantidad					T_CANTIDAD
				,@vl_item						numeric

			set @K_GR_ARRIENDO = 4
			set @K_BODEGA_POR_RECICLAR = 2
			set @K_GR_EMITIDA = 1

			select @vl_cod_tipo_guia_recepcion = cod_tipo_guia_recepcion 
					,@vl_cod_arriendo = cod_doc
					,@vl_cod_estado_guia_recepcion = cod_estado_guia_recepcion
			from guia_recepcion
			where cod_guia_recepcion = @ve_cod_guia_recepcion
			if (@vl_cod_tipo_guia_recepcion = @K_GR_ARRIENDO) begin	-- MOVER INVENTARIO
				if (@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_impresa)begin
					select @vl_cod_bodega = cod_bodega
					from arriendo
					where cod_arriendo = @vl_cod_arriendo
					
					select @vl_nom_bodega_origen = nom_bodega
					from bodega
					where cod_bodega = @vl_cod_bodega

					set @KL_BODEGA_DESTINO = 2 --RENTAL POR RECICLAR

					select @vl_nom_bodega_destino = nom_bodega
					from bodega
					where cod_bodega = @KL_BODEGA_DESTINO

					set @vl_referencia = 'Traspaso desde bodega '+@vl_nom_bodega_origen+' a bodega '+@vl_nom_bodega_destino

					exec spu_traspaso_bodega 'DESDE_GR_ARR', null, @ve_cod_usuario, @vl_cod_bodega, @KL_BODEGA_DESTINO, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia 
				end
				else if(@vl_cod_estado_guia_recepcion = @kl_cod_estado_gr_anulada) begin

					set @KL_BODEGA_ORIGEN = 2 --RENTAL POR RECICLAR

					select @vl_nom_bodega_origen = nom_bodega
					from bodega
					where cod_bodega = @KL_BODEGA_ORIGEN

					select @vl_cod_bodega = cod_bodega
					from arriendo
					where cod_arriendo = @vl_cod_arriendo
					
					select @vl_nom_bodega_destino = nom_bodega
					from bodega
					where cod_bodega = @vl_cod_bodega

					set @vl_referencia = 'Anulación de GR: Traspaso desde bodega '+@vl_nom_bodega_origen+' a bodega '+@vl_nom_bodega_destino
					exec spu_traspaso_bodega 'DESDE_GR_ARR', null, @ve_cod_usuario, @KL_BODEGA_ORIGEN, @vl_cod_bodega, 'GUIA_RECEPCION', @ve_cod_guia_recepcion, @vl_referencia 
				end
			end
		end

END
go
