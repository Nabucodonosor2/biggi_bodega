------------------------------- spu_nota_venta -------------------------------
ALTER PROCEDURE [dbo].[spu_nota_venta] 
			(@ve_operacion varchar(20),
			@ve_cod_nota_venta numeric,
			@ve_fecha_nota_venta varchar(10)= null,
			@ve_cod_usuario numeric = null, 
			@ve_cod_estado_nota_venta numeric = null, 
			@ve_nro_orden_compra varchar(40) = null,
			@ve_fecha_orden_compra_cliente datetime = null,
			@ve_centro_costo_cliente varchar(20) = null, 
			@ve_cod_moneda numeric = null, 
			@ve_valor_tipo_cambio T_PRECIO = null, 
			@ve_cod_cotizacion numeric = null,
			@ve_cod_usuario_vendedor1 numeric = null,
			@ve_porc_vendedor1 T_PORCENTAJE = null,
			@ve_cod_usuario_vendedor2 numeric = null,
			@ve_porc_vendedor2 T_PORCENTAJE = null,
			@ve_cod_cuenta_corriente numeric = null,
			@ve_cod_origen_venta numeric = null,
			@ve_referencia varchar(100) = null,
			@ve_cod_empresa numeric = null,
			@ve_cod_sucursal_despacho numeric = null,
			@ve_cod_sucursal_factura numeric = null,
			@ve_cod_persona numeric = null,
			@ve_subtotal T_PRECIO = null,
			@ve_porc_dscto1 T_PORCENTAJE = null,
			@ve_monto_dscto1 T_PRECIO = null,
			@ve_porc_dscto2 T_PORCENTAJE = null,
			@ve_monto_dscto2 T_PRECIO = null,
			@ve_porc_iva T_PORCENTAJE = null,
			@ve_monto_iva T_PRECIO = null,
			@ve_total_con_iva T_PRECIO = null,
			@ve_fecha_entrega varchar(10) = null,
			@ve_obs_despacho text = null,
			@ve_obs text = null,	
			@ve_cod_forma_pago numeric = null,
			@ve_motivo_anula varchar(100) = null,
			@ve_cod_usuario_anula numeric = null,
			@ve_total_neto T_PRECIO = null,
			@ve_ingreso_usuario_dscto1 varchar(1) = null,
			@ve_ingreso_usuario_dscto2 varchar(1) = null,
			@ve_nom_forma_pago_otro varchar(100) = null,
			@ve_cantidad_doc_forma_pago_otro numeric = null,
			@ve_porc_dscto_corporativo T_PORCENTAJE = null,
			@ve_cod_usuario_cierre numeric = null,
			@ve_fecha_plazo_cierre varchar(20) = null,
			@ve_cod_usuario_confirma numeric = null)

AS
BEGIN

	declare	@vl_estado_nota_venta_confirma numeric,
			@vl_estado_nota_venta_cerrada numeric,
			@vl_estado_nota_venta_anulada numeric,
			@vl_cod_usuario_anula numeric,
			@vl_plazo_cierre numeric,
			@vl_dif_plazo_cierre numeric
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
			,@vl_estado_orden_compra_emitida numeric
			,@vl_estado_orden_compra_cerrada numeric
			


		set @vl_estado_nota_venta_cerrada = 2
		set @vl_estado_nota_venta_anulada = 3
		set @vl_estado_nota_venta_confirma = 4
		set @vl_estado_orden_compra_emitida = 1
		set @vl_estado_orden_compra_cerrada = 3

		if (@ve_operacion='UPDATE') 
			begin	
				update nota_venta
				set  	fecha_nota_venta = dbo.to_date(@ve_fecha_nota_venta),
						cod_usuario = @ve_cod_usuario, 
						cod_estado_nota_venta = @ve_cod_estado_nota_venta, 
						nro_orden_compra = @ve_nro_orden_compra,
						fecha_orden_compra_cliente = @ve_fecha_orden_compra_cliente,
						centro_costo_cliente = @ve_centro_costo_cliente, 
						cod_moneda = @ve_cod_moneda, 
						valor_tipo_cambio = @ve_valor_tipo_cambio, 
						cod_cotizacion = @ve_cod_cotizacion,
						cod_usuario_vendedor1 = @ve_cod_usuario_vendedor1,
						porc_vendedor1 = @ve_porc_vendedor1,
						cod_usuario_vendedor2 = @ve_cod_usuario_vendedor2,
						porc_vendedor2 = @ve_porc_vendedor2,
						cod_cuenta_corriente = @ve_cod_cuenta_corriente,
						cod_origen_venta = @ve_cod_origen_venta,
						referencia = @ve_referencia,
						cod_empresa = @ve_cod_empresa,
						cod_sucursal_despacho = @ve_cod_sucursal_despacho,
						cod_sucursal_factura = @ve_cod_sucursal_factura,
						cod_persona = @ve_cod_persona,
						subtotal = @ve_subtotal,
						porc_dscto1 = @ve_porc_dscto1,
						monto_dscto1 = @ve_monto_dscto1,
						porc_dscto2 = @ve_porc_dscto2,
						monto_dscto2 = @ve_monto_dscto2,
						porc_iva = @ve_porc_iva,
						monto_iva = @ve_monto_iva,
						total_con_iva = @ve_total_con_iva,
						fecha_entrega = dbo.to_date(@ve_fecha_entrega),
						obs_despacho = @ve_obs_despacho,
						obs = @ve_obs,	
						cod_forma_pago = @ve_cod_forma_pago,
						total_neto = @ve_total_neto,
						ingreso_usuario_dscto1 = @ve_ingreso_usuario_dscto1,
						ingreso_usuario_dscto2 = @ve_ingreso_usuario_dscto2,
						nom_forma_pago_otro = @ve_nom_forma_pago_otro,
						cantidad_doc_forma_pago_otro = @ve_cantidad_doc_forma_pago_otro,
						porc_dscto_corporativo = @ve_porc_dscto_corporativo
				where 	cod_nota_venta = @ve_cod_nota_venta

				-- update a confirma NV
				if (@ve_cod_usuario_confirma is not NULL)
					update nota_venta
					set fecha_confirma		= getdate ()		
						,cod_usuario_confirma  = @ve_cod_usuario_confirma
						,cod_estado_nota_venta = @vl_estado_nota_venta_confirma
					where  cod_nota_venta = @ve_cod_nota_venta

				-- update a cierre de NV
				if (@ve_cod_usuario_cierre is not NULL)
				begin
					update nota_venta
					set fecha_cierre		= getdate ()		
						,cod_usuario_cierre	= @ve_cod_usuario_cierre
						,cod_estado_nota_venta = @vl_estado_nota_venta_cerrada
					where  cod_nota_venta = @ve_cod_nota_venta
					execute dbo.sp_nv_orden_pago @ve_cod_nota_venta, @ve_cod_usuario_cierre 
					
					--todas las OC asociadas a la NV las deja en estado cerrada
					update orden_compra 
					set cod_estado_orden_compra = @vl_estado_orden_compra_cerrada
					where cod_nota_venta = @ve_cod_nota_venta and
						cod_estado_orden_compra = @vl_estado_orden_compra_emitida
				end 

				-- update a anulación de NV
				select	@vl_cod_usuario_anula = cod_usuario_anula
				from nota_venta
				where cod_nota_venta = @ve_cod_nota_venta
				if (@ve_cod_estado_nota_venta = @vl_estado_nota_venta_anulada) and (@vl_cod_usuario_anula is NULL)
					update nota_venta
					set fecha_anula			= getdate ()
						,motivo_anula		= @ve_motivo_anula			
						,cod_usuario_anula	= @ve_cod_usuario_anula				
					where cod_nota_venta = @ve_cod_nota_venta

				-- update a fecha plazo cierre
				select	@vl_dif_plazo_cierre = datediff(d, dbo.to_date (@ve_fecha_plazo_cierre), FECHA_PLAZO_CIERRE)
				from nota_venta
				where cod_nota_venta = @ve_cod_nota_venta

				if (@vl_dif_plazo_cierre <> 0) 
					update nota_venta
					set fecha_plazo_cierre = dbo.to_date (@ve_fecha_plazo_cierre)		
					where cod_nota_venta = @ve_cod_nota_venta		
				
			end 
		else if (@ve_operacion='INSERT') 
			begin
				select @ve_cod_nota_venta = ISNULL(max(cod_nota_venta),0)+1 from nota_venta

				select @vl_plazo_cierre = valor 
				from parametro
				where cod_parametro = 24 

				insert into nota_venta (
					cod_nota_venta,
					fecha_registro,
					fecha_nota_venta, 
					cod_usuario, 
					cod_estado_nota_venta, 
					nro_orden_compra,
					fecha_orden_compra_cliente,
					centro_costo_cliente, 
					cod_moneda, 
					valor_tipo_cambio, 
					cod_cotizacion,
					cod_usuario_vendedor1,
					porc_vendedor1,
					cod_usuario_vendedor2,
					porc_vendedor2,
					cod_cuenta_corriente,
					cod_origen_venta,
					referencia,
					cod_empresa,
					cod_sucursal_despacho,
					cod_sucursal_factura,
					cod_persona,
					fecha_plazo_cierre,
					subtotal,
					porc_dscto1,
					monto_dscto1,
					porc_dscto2,
					monto_dscto2,
					porc_iva,
					monto_iva,
					total_con_iva,
					fecha_entrega,
					obs_despacho,
					obs,	
					cod_forma_pago,
					total_neto,
					ingreso_usuario_dscto1,
					ingreso_usuario_dscto2,
					nom_forma_pago_otro,
					cantidad_doc_forma_pago_otro,
					porc_dscto_corporativo)
				values (
					@ve_cod_nota_venta,
					getdate(),
					dbo.f_makedate(day(getdate()), month(getdate()), year(getdate())),
					@ve_cod_usuario, 
					@ve_cod_estado_nota_venta, 
					@ve_nro_orden_compra,
					@ve_fecha_orden_compra_cliente,
					@ve_centro_costo_cliente, 
					@ve_cod_moneda, 
					@ve_valor_tipo_cambio, 
					@ve_cod_cotizacion,
					@ve_cod_usuario_vendedor1,
					@ve_porc_vendedor1,
					@ve_cod_usuario_vendedor2,
					@ve_porc_vendedor2,
					@ve_cod_cuenta_corriente,
					@ve_cod_origen_venta,
					@ve_referencia,
					@ve_cod_empresa,
					@ve_cod_sucursal_despacho,
					@ve_cod_sucursal_factura,
					@ve_cod_persona,
					getdate() + @vl_plazo_cierre,
					@ve_subtotal,
					@ve_porc_dscto1,
					@ve_monto_dscto1,
					@ve_porc_dscto2,
					@ve_monto_dscto2,
					@ve_porc_iva,
					@ve_monto_iva,
					@ve_total_con_iva,
					dbo.to_date(@ve_fecha_entrega),
					@ve_obs_despacho,
					@ve_obs,	
					@ve_cod_forma_pago,
					@ve_total_neto,
					@ve_ingreso_usuario_dscto1,
					@ve_ingreso_usuario_dscto2,
					@ve_nom_forma_pago_otro,
					@ve_cantidad_doc_forma_pago_otro,
					dbo.f_get_porc_dscto_corporativo_empresa(@ve_cod_empresa, getdate()))
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
			from nota_venta
			where cod_nota_venta = @ve_cod_nota_venta

			select @vl_sub_total = sum(round(cantidad * precio, 0))
			from item_nota_venta
			where cod_nota_venta = @ve_cod_nota_venta

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

			update nota_venta		
			set	subtotal					=	@vl_sub_total		
				,porc_dscto1				=	@vl_porc_dscto1	
				,monto_dscto1				=	@vl_monto_dscto1	
				,porc_dscto2				=	@vl_porc_dscto2	
				,monto_dscto2				=	@vl_monto_dscto2	
				,total_neto					=	@vl_total_neto				
				,monto_iva					=	@vl_monto_iva		
				,total_con_iva				=	@vl_total_con_iva	
			where cod_nota_venta = @ve_cod_nota_venta 	
			
		end
END
go
