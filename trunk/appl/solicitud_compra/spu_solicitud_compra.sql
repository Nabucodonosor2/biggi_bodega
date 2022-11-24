-------------------- spu_solicitud_compra ---------------------------------
ALTER PROCEDURE [dbo].[spu_solicitud_compra]
			(@ve_operacion						varchar(20)
			,@ve_cod_solicitud_compra			numeric
			,@ve_cod_usuario					numeric	= NULL
			,@ve_cod_estado_solicitud_compra	numeric	= NULL
			,@ve_cod_producto					varchar(30) = NULL
			,@ve_cantidad						T_CANTIDAD = NULL
			,@ve_referencia						varchar(100) = NULL
			,@ve_terminado_compuesto			varchar(1)=null)
AS
BEGIN
	IF (@ve_operacion='INSERT') 
		BEGIN
			INSERT INTO solicitud_compra
					(fecha_solicitud_compra
					,cod_usuario
					,cod_estado_solicitud_compra
					,cod_producto
					,cantidad
					,referencia
					,terminado_compuesto)
			VALUES  (getdate()
					,@ve_cod_usuario	
					,@ve_cod_estado_solicitud_compra
					,@ve_cod_producto
					,@ve_cantidad
					,@ve_referencia
					,@ve_terminado_compuesto)
		END
	ELSE IF (@ve_operacion='UPDATE') 
		BEGIN
			DECLARE @vl_cod_estado_old	NUMERIC
		
			SELECT @vl_cod_estado_old = COD_ESTADO_SOLICITUD_COMPRA
            FROM SOLICITUD_COMPRA
            WHERE COD_SOLICITUD_COMPRA = @ve_cod_solicitud_compra

			UPDATE	solicitud_compra
			SET		 cod_usuario					= @ve_cod_usuario	
					,cod_estado_solicitud_compra	= @ve_cod_estado_solicitud_compra
					,cod_producto					= @ve_cod_producto
					,cantidad						= @ve_cantidad
					,referencia						= @ve_referencia
					,terminado_compuesto			= @ve_terminado_compuesto
			WHERE cod_solicitud_compra				= @ve_cod_solicitud_compra
			
			IF (@vl_cod_estado_old <> @ve_cod_estado_solicitud_compra AND @ve_cod_estado_solicitud_compra = 3) -- Anulada
				exec spu_solicitud_compra 'ANULAR', @ve_cod_solicitud_compra

		END 
	ELSE IF (@ve_operacion='DELETE') 
		BEGIN
			delete item_solicitud_compra
			WHERE cod_solicitud_compra	=	@ve_cod_solicitud_compra
			
			delete solicitud_compra
			WHERE cod_solicitud_compra	=	@ve_cod_solicitud_compra
		END 
	ELSE IF (@ve_operacion='COMPRAR')
		BEGIN
			DECLARE C_IT CURSOR FOR  
			select i.cod_item_solicitud_compra
					,i.cod_producto
					,p.nom_producto
					,i.cantidad_total
					,i.precio_compra
					,i.cod_empresa
			from item_solicitud_compra i, producto p
			where i.cod_solicitud_compra  = @ve_cod_solicitud_compra
			  and i.genera_compra = 'S'
			  and p.cod_producto = i.cod_producto

			declare
				@K_PARAM_IVA				numeric
				,@vc_cod_item_solicitud_compra	numeric
				,@vc_cod_producto			varchar(30)
				,@vc_nom_producto			varchar(100)
				,@vc_cantidad_total			T_CANTIDAD
				,@vc_precio_compra			T_PRECIO
				,@vc_cod_empresa			numeric
				,@vl_cod_orden_compra		numeric
				,@vl_cod_usuario_confirma	numeric
				,@vl_referencia				varchar(100)
				,@vl_cod_sucursal			numeric
				,@vl_porc_iva				T_PORCENTAJE

			set @K_PARAM_IVA = 1
			set @vl_porc_iva = dbo.f_get_parametro(@K_PARAM_IVA)

			select @vl_referencia = referencia
			from solicitud_compra
			where cod_solicitud_compra  = @ve_cod_solicitud_compra
			
			OPEN C_IT
			FETCH C_IT INTO @vc_cod_item_solicitud_compra, @vc_cod_producto, @vc_nom_producto, @vc_cantidad_total, @vc_precio_compra, @vc_cod_empresa
			WHILE @@FETCH_STATUS = 0 BEGIN	
				-- NOTA: Cada item es una OC siempre
				select @vl_cod_sucursal = cod_sucursal
				from sucursal
				where cod_empresa = @vc_cod_empresa
				  and direccion_factura = 'S'

				exec spu_orden_compra 'INSERT'	
										, Null		 
										, @ve_cod_usuario			-- @ve_cod_usuario		 	 
										, @ve_cod_usuario			-- @ve_cod_usuario_solicita	 
										, 1									-- @ve_cod_moneda == PESOS			 
										, 1									-- @ve_cod_estado_orden_compra = EMITIDA
										, null								-- @ve_cod_nota_venta			 
										, 1									-- @ve_cod_cuenta_corriente == CUENTA_CORRIENTE UNICA DE BODEGA 
										, @vl_referencia					-- @ve_referencia				 
										, @vc_cod_empresa					-- @ve_cod_empresa			 
										, @vl_cod_sucursal					-- @ve_cod_suc_factura		 
										, NULL								-- @ve_cod_persona			 
										, 0									-- @ve_sub_total
										, NULL								-- @ve_porc_dscto1
										, 0									-- @ve_monto_dscto1
										, NULL								-- @ve_porc_dscto1
										, 0									-- @ve_monto_dscto2
										, 0									-- @ve_total_neto				 
										, @vl_porc_iva						-- @ve_porc_iva				 
										, 0									-- @ve_monto_iva				 
										, 0									-- @ve_total_con_iva			 
										, null								-- @ve_obs					 
										, null								-- @ve_motivo_anula			 
										, null								-- @ve_cod_usuario_anula		 
										, null								-- @ve_ingreso_usuario_dscto1  
										, null								-- @ve_ingreso_usuario_dscto2
										, 'SOLICITUD_COMPRA'				-- @ve_tipo_OC
										, @ve_cod_solicitud_compra			-- COD_DOC

				set @vl_cod_orden_compra  = @@identity

				--item
				exec spu_item_orden_compra 'INSERT' 
											, Null								-- @ve_cod_item_orden_compra 
											, @vl_cod_orden_compra				-- @ve_cod_orden_compra 
											, 10								-- @ve_orden 
											, '1'								-- @ve_item 
											, @vc_cod_producto					-- @ve_cod_producto 
											, @vc_nom_producto					-- @ve_nom_producto 
											, @vc_cantidad_total				-- @ve_cantidad 
											, @vc_precio_compra					-- @ve_precio
											, null								-- @ve_cod_tipo_te
											, null								-- @ve_motivo_te
											, null								-- @ve_cod_item_nota_venta
											, @vc_cod_item_solicitud_compra		-- cod_item_doc

				exec spu_orden_compra 'RECALCULA', @vl_cod_orden_compra		 

				FETCH C_IT INTO @vc_cod_item_solicitud_compra, @vc_cod_producto, @vc_nom_producto, @vc_cantidad_total, @vc_precio_compra, @vc_cod_empresa
			END
			CLOSE C_IT
			DEALLOCATE C_IT
		END
	ELSE IF (@ve_operacion='ANULAR')BEGIN
		
		UPDATE ORDEN_COMPRA
		SET COD_ESTADO_ORDEN_COMPRA = 2 -- Anulada
		   ,MOTIVO_ANULA = 'Orden Compra anulada automaticamente por anulacion de Solicitud Compra N° '+CONVERT(VARCHAR,@ve_cod_solicitud_compra)
		WHERE COD_ORDEN_COMPRA in (SELECT COD_ORDEN_COMPRA 
								   FROM ORDEN_COMPRA
								   WHERE COD_DOC = @ve_cod_solicitud_compra
								   AND TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA')
		
	END	
END


