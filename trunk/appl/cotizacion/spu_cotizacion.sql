ALTER PROCEDURE [dbo].[spu_cotizacion]
			(@ve_operacion					varchar(20)
			,@ve_cod_cotizacion				numeric = null
			,@ve_fecha_cotizacion			varchar(10) = null
			,@ve_cod_usuario				numeric = null
			,@ve_cod_usuario_vend1			numeric = null
			,@ve_porc_vendedor1				T_PORCENTAJE = null
			,@ve_cod_usuario_vend2			numeric = null
			,@ve_porc_vendedor2				T_PORCENTAJE = null
			,@ve_cod_moneda					numeric = null
			,@ve_idioma						varchar(1) = null
			,@ve_referencia					varchar(100) = null
			,@ve_cod_est_cot				numeric = null
			,@ve_cod_orig_cot				numeric = null
			,@ve_cod_coti_desde				numeric = null
			,@ve_cod_empresa				numeric = null
			,@ve_cod_suc_despacho			numeric = null
			,@ve_cod_suc_factura			numeric = null
			,@ve_cod_persona				numeric = null
			,@ve_sumar_items				varchar(1) = null
			,@ve_sub_total					T_PRECIO = null
			,@ve_porc_dscto1				T_PORCENTAJE = null
			,@ve_monto_dscto1				T_PRECIO = null
			,@ve_porc_dscto2				T_PORCENTAJE = null
			,@ve_monto_dscto2				T_PRECIO = null
			,@ve_total_neto					T_PRECIO = null
			,@ve_porc_iva					T_PORCENTAJE = null
			,@ve_monto_iva					T_PRECIO = null
			,@ve_total_con_iva				T_PRECIO = null
			,@ve_cod_forma_pago				numeric = null
			,@ve_validez_oferta				numeric = null
			,@ve_entrega					varchar(100) = null
			,@ve_cod_embalaje_cot			numeric = null
			,@ve_cod_flete_cot				numeric = null
			,@ve_cod_inst_cot				numeric = null
			,@ve_garantia					varchar(100) = null
			,@ve_obs						text = null
			,@ve_posibilidad_cierre			T_PORCENTAJE = null
			,@ve_fecha_posible_cierre		varchar(10) = null
			,@ve_ingreso_usuario_dscto1		T_INGRESO_USUARIO_DSCTO = null
			,@ve_ingreso_usuario_dscto2		T_INGRESO_USUARIO_DSCTO = null
			,@ve_nom_forma_pago_otro		varchar(100) = null
			,@ve_cod_solicitud_cotizacion	numeric(10) = null)

AS
	declare @vl_ingreso_usuario_dscto1 T_INGRESO_USUARIO_DSCTO
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

BEGIN
		if (@ve_operacion='UPDATE') 
			begin
				UPDATE cotizacion		
				SET			fecha_cotizacion			=	dbo.to_date(@ve_fecha_cotizacion)		 
							,cod_usuario				=	@ve_cod_usuario	
							,cod_usuario_vendedor1		=	@ve_cod_usuario_vend1	
							,porc_vendedor1				=	@ve_porc_vendedor1	
							,cod_usuario_vendedor2		=	@ve_cod_usuario_vend2	
							,porc_vendedor2				=	@ve_porc_vendedor2
							,cod_moneda					=	@ve_cod_moneda		
							,idioma						=	@ve_idioma		
							,referencia					=	@ve_referencia		
							,cod_estado_cotizacion		=	@ve_cod_est_cot	
							,cod_origen_cotizacion		=	@ve_cod_orig_cot	
							,cod_cotizacion_desde		=	@ve_cod_coti_desde
							,cod_empresa				=	@ve_cod_empresa	
							,cod_sucursal_despacho		=	@ve_cod_suc_despacho	
							,cod_sucursal_factura		=	@ve_cod_suc_factura	
							,cod_persona				=	@ve_cod_persona	
							,sumar_items				=	@ve_sumar_items	
							,subtotal					=	@ve_sub_total		
							,porc_dscto1				=	@ve_porc_dscto1	
							,monto_dscto1				=	@ve_monto_dscto1	
							,porc_dscto2				=	@ve_porc_dscto2	
							,monto_dscto2				=	@ve_monto_dscto2	
							,total_neto					=	@ve_total_neto	
							,porc_iva					=	@ve_porc_iva		
							,monto_iva					=	@ve_monto_iva		
							,total_con_iva				=	@ve_total_con_iva	
							,cod_forma_pago				=	@ve_cod_forma_pago
							,validez_oferta				=	@ve_validez_oferta	
							,entrega					=	@ve_entrega		
							,cod_embalaje_cotizacion	=	@ve_cod_embalaje_cot	
							,cod_flete_cotizacion		=	@ve_cod_flete_cot	
							,cod_instalacion_cotizacion	=	@ve_cod_inst_cot	
							,garantia					=	@ve_garantia		
							,obs						=	@ve_obs	
							,posibilidad_cierre			=	@ve_posibilidad_cierre	
							,fecha_posible_cierre		=	dbo.to_date(@ve_fecha_posible_cierre)
							,ingreso_usuario_dscto1		=	@ve_ingreso_usuario_dscto1
							,ingreso_usuario_dscto2		=	@ve_ingreso_usuario_dscto2
							,nom_forma_pago_otro		=	@ve_nom_forma_pago_otro
							,cod_solicitud_cotizacion	=	@ve_cod_solicitud_cotizacion
		
		
		select*
		from cotizacion
		
				WHERE cod_cotizacion = @ve_cod_cotizacion
			end
		else if (@ve_operacion='INSERT') 
			begin
				insert into cotizacion
					(fecha_registro_cotizacion
					,fecha_cotizacion
					,cod_usuario
					,cod_usuario_vendedor1
					,porc_vendedor1
					,cod_usuario_vendedor2
					,porc_vendedor2
					,cod_moneda
					,idioma
					,referencia
					,cod_estado_cotizacion
					,cod_origen_cotizacion
					,cod_cotizacion_desde
					,cod_empresa
					,cod_sucursal_despacho
					,cod_sucursal_factura
					,cod_persona
					,sumar_items
					,subtotal
					,porc_dscto1
					,monto_dscto1
					,porc_dscto2
					,monto_dscto2
					,total_neto
					,porc_iva
					,monto_iva
					,total_con_iva
					,cod_forma_pago
					,validez_oferta
					,entrega
					,cod_embalaje_cotizacion
					,cod_flete_cotizacion
					,cod_instalacion_cotizacion
					,garantia
					,obs
					,posibilidad_cierre
					,fecha_posible_cierre
					,ingreso_usuario_dscto1
					,ingreso_usuario_dscto2
					,nom_forma_pago_otro
					,VALOR_TIPO_CAMBIO
					,cod_solicitud_cotizacion)
				values 
					(getdate()
					,dbo.f_makedate(day(getdate()), month(getdate()), year(getdate()))
					,@ve_cod_usuario	
					,@ve_cod_usuario_vend1	
					,@ve_porc_vendedor1	
					,@ve_cod_usuario_vend2	
					,@ve_porc_vendedor2	
					,@ve_cod_moneda		
					,@ve_idioma		
					,@ve_referencia		
					,@ve_cod_est_cot	
					,@ve_cod_orig_cot	
					,@ve_cod_coti_desde
					,@ve_cod_empresa	
					,@ve_cod_suc_despacho	
					,@ve_cod_suc_factura	
					,@ve_cod_persona	
					,@ve_sumar_items	
					,@ve_sub_total		
					,@ve_porc_dscto1	
					,@ve_monto_dscto1	
					,@ve_porc_dscto2	
					,@ve_monto_dscto2	
					,@ve_total_neto		
					,@ve_porc_iva		
					,@ve_monto_iva		
					,@ve_total_con_iva	
					,@ve_cod_forma_pago	
					,@ve_validez_oferta	
					,@ve_entrega		
					,@ve_cod_embalaje_cot	
					,@ve_cod_flete_cot	
					,@ve_cod_inst_cot	
					,@ve_garantia		
					,@ve_obs		
					,@ve_posibilidad_cierre	
					,dbo.to_date(@ve_fecha_posible_cierre)
					,@ve_ingreso_usuario_dscto1
					,@ve_ingreso_usuario_dscto2
					,@ve_nom_forma_pago_otro
					,1
					,@ve_cod_solicitud_cotizacion)
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
			from cotizacion
			where cod_cotizacion = @ve_cod_cotizacion

			select @vl_sub_total = isnull(sum(round(cantidad * precio, 0)), 0)
			from item_cotizacion
			where cod_cotizacion = @ve_cod_cotizacion

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

			update cotizacion		
			set	subtotal					=	@vl_sub_total		
				,porc_dscto1				=	@vl_porc_dscto1	
				,monto_dscto1				=	@vl_monto_dscto1	
				,porc_dscto2				=	@vl_porc_dscto2	
				,monto_dscto2				=	@vl_monto_dscto2	
				,total_neto					=	@vl_total_neto				
				,monto_iva					=	@vl_monto_iva		
				,total_con_iva				=	@vl_total_con_iva	
			where cod_cotizacion = @ve_cod_cotizacion 
				-- and sumar_items = 'S' = esta condición no esta implementada, todas las cotizaciones estan "sumar_items" = 'N'
					
		end
END
