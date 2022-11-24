-------------------- spu_arriendo---------------------------------
ALTER PROCEDURE spu_arriendo(@ve_operacion						varchar(20)
							,@ve_cod_arriendo					numeric
							,@ve_nom_arriendo					varchar(100)=null
							,@ve_cod_usuario					numeric=null
							,@ve_cod_usuario_vendedor1			numeric=null
							,@ve_nro_orden_compra				varchar(100)=null
							,@ve_cod_empresa					numeric=null
							,@ve_cod_sucursal					numeric=null
							,@ve_cod_persona					numeric=null
							,@ve_ejecutivo_contacto				varchar(100)=null
							,@ve_ejecutivo_telefono				varchar(100)=null
							,@ve_ejecutivo_mail					varchar(100)=null
							,@ve_cod_cotizacion_arriendo		numeric=null
							,@ve_referencia						varchar(100)=null
							,@ve_centro_costo_cliente			varchar(100)=null
							,@ve_porc_adicional_recuperacion	T_PORCENTAJE=null
							,@ve_monto_adicional_recuperacion	numeric=null
							,@ve_nro_meses						numeric=null
							,@ve_porc_arriendo					T_PORCENTAJE=null
							,@ve_subtotal						numeric=null
							,@ve_total_neto						numeric=null
							,@ve_porc_iva						T_PORCENTAJE=null
							,@ve_monto_iva						numeric=null
							,@ve_total_con_iva					numeric=null
							,@ve_fecha_entrega					datetime=null
							,@ve_ubicacion_direccion			varchar(100)=null
							,@ve_ubicacion_cod_comuna			numeric=null
							,@ve_ubicacion_cod_ciudad			numeric=null
							,@ve_cod_estado_arriendo			numeric=null
							,@ve_obs						    text = null
							)
AS
BEGIN
	declare
		@K_BODEGA_ARRIENDO			numeric
		,@cod_bodega				numeric
		,@nom_bodega				varchar(100)


	set @K_BODEGA_ARRIENDO = 2

	if (@ve_operacion='INSERT')
		insert into ARRIENDO
			(NOM_ARRIENDO
			,FECHA_ARRIENDO
			,COD_USUARIO
			,COD_USUARIO_VENDEDOR1
			,NRO_ORDEN_COMPRA
			,COD_EMPRESA
			,COD_SUCURSAL
			,COD_PERSONA
			,EJECUTIVO_CONTACTO
			,EJECUTIVO_TELEFONO
			,EJECUTIVO_MAIL
			,COD_COTIZACION_ARRIENDO
			,REFERENCIA
			,CENTRO_COSTO_CLIENTE
			,PORC_ADICIONAL_RECUPERACION
			,MONTO_ADICIONAL_RECUPERACION
			,NRO_MESES
			,PORC_ARRIENDO
			,SUBTOTAL
			,TOTAL_NETO
			,PORC_IVA
			,MONTO_IVA
			,TOTAL_CON_IVA
			,FECHA_ENTREGA
			,UBICACION_DIRECCION
			,UBICACION_COD_COMUNA
			,UBICACION_COD_CIUDAD
			,COD_ESTADO_ARRIENDO
			,COD_BODEGA
			,OBS
			)
		values
			(@ve_nom_arriendo					--NOM_ARRIENDO
			,getdate()							--FECHA_ARRIENDO
			,@ve_cod_usuario					--COD_USUARIO
			,@ve_cod_usuario_vendedor1			--COD_USUARIO_VENDEDOR1
			,@ve_nro_orden_compra				--NRO_ORDEN_COMPRA
			,@ve_cod_empresa					--COD_EMPRESA
			,@ve_cod_sucursal					--COD_SUCURSAL
			,@ve_cod_persona					--COD_PERSONA
			,@ve_ejecutivo_contacto				--EJECUTIVO_CONTACTO
			,@ve_ejecutivo_telefono				--EJECUTIVO_TELEFONO
			,@ve_ejecutivo_mail					--EJECUTIVO_MAIL
			,@ve_cod_cotizacion_arriendo		--COD_COTIZACION_ARRIENDO
			,@ve_referencia						--REFERENCIA
			,@ve_centro_costo_cliente			--CENTRO_COSTO_CLIENTE
			,@ve_porc_adicional_recuperacion	--PORC_ADICIONAL_RECUPERACION
			,@ve_monto_adicional_recuperacion	--MONTO_ADICIONAL_RECUPERACION
			,@ve_nro_meses						--NRO_MESES
			,@ve_porc_arriendo					--PORC_ARRIENDO
			,@ve_subtotal						--SUBTOTAL
			,@ve_total_neto						--TOTAL_NETO
			,@ve_porc_iva						--PORC_IVA
			,@ve_monto_iva						--MONTO_IVA
			,@ve_total_con_iva					--TOTAL_CON_IVA
			,@ve_fecha_entrega					--FECHA_ENTREGA
			,@ve_ubicacion_direccion			--UBICACION_DIRECCION
			,@ve_ubicacion_cod_comuna			--UBICACION_COD_COMUNA
			,@ve_ubicacion_cod_ciudad			--UBICACION_COD_CIUDAD
			,@ve_cod_estado_arriendo			--COD_ESTADO_ARRIENDO
			,null								--COD_BODEGA
			,@ve_obs							--OBS
			)
	else if (@ve_operacion='UPDATE') begin
		update ARRIENDO
		set NOM_ARRIENDO = @ve_nom_arriendo
			,COD_USUARIO = @ve_cod_usuario
			,COD_USUARIO_VENDEDOR1 = @ve_cod_usuario_vendedor1
			,NRO_ORDEN_COMPRA = @ve_nro_orden_compra
			,COD_EMPRESA = @ve_cod_empresa
			,COD_SUCURSAL = @ve_cod_sucursal
			,COD_PERSONA = @ve_cod_persona
			,EJECUTIVO_CONTACTO = @ve_ejecutivo_contacto
			,EJECUTIVO_TELEFONO = @ve_ejecutivo_telefono
			,EJECUTIVO_MAIL = @ve_ejecutivo_mail
			,COD_COTIZACION_ARRIENDO = @ve_cod_cotizacion_arriendo
			,REFERENCIA = @ve_referencia
			,CENTRO_COSTO_CLIENTE = @ve_centro_costo_cliente
			,PORC_ADICIONAL_RECUPERACION = @ve_porc_adicional_recuperacion
			,MONTO_ADICIONAL_RECUPERACION = @ve_monto_adicional_recuperacion
			,NRO_MESES = @ve_nro_meses
			,PORC_ARRIENDO = @ve_porc_arriendo
			,SUBTOTAL = @ve_subtotal
			,TOTAL_NETO = @ve_total_neto
			,PORC_IVA = @ve_porc_iva
			,MONTO_IVA = @ve_monto_iva
			,TOTAL_CON_IVA = @ve_total_con_iva
			,FECHA_ENTREGA = @ve_fecha_entrega
			,UBICACION_DIRECCION = @ve_ubicacion_direccion
			,UBICACION_COD_COMUNA = @ve_ubicacion_cod_comuna
			,UBICACION_COD_CIUDAD = @ve_ubicacion_cod_ciudad
			,COD_ESTADO_ARRIENDO = @ve_cod_estado_arriendo
			,OBS = @ve_obs	
		where COD_ARRIENDO = @ve_cod_arriendo
	end
	else if (@ve_operacion='DELETE') begin
		delete ITEM_ARRIENDO 
		where COD_ARRIENDO = @ve_cod_arriendo

		delete ARRIENDO 
		where COD_ARRIENDO = @ve_cod_arriendo
	end		
	else if (@ve_operacion='APROBAR') begin
		set @nom_bodega = 'Rental contrato ' + convert(varchar, @ve_cod_arriendo)
		exec spu_bodega 'INSERT', null, @nom_bodega, @K_BODEGA_ARRIENDO
		set @cod_bodega = @@identity

		update ARRIENDO
		set COD_BODEGA = @cod_bodega
		where COD_ARRIENDO = @ve_cod_arriendo

		declare C_IT_ARR CURSOR FOR  
		select cod_item_arriendo
		from  item_arriendo
		where cod_arriendo = @ve_cod_arriendo
		  and cod_producto = 'TE'

		declare 
			@vc_cod_item_arriendo		numeric

		OPEN C_IT_ARR
		FETCH C_IT_ARR INTO @vc_cod_item_arriendo
		WHILE @@FETCH_STATUS = 0 BEGIN	
			exec sp_arr_crear_producto_TE @vc_cod_item_arriendo
			FETCH C_IT_ARR INTO @vc_cod_item_arriendo
		END
		CLOSE C_IT_ARR
		DEALLOCATE C_IT_ARR		
	end
END