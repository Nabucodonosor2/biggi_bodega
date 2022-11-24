-------------------- spu_entrada_bodega ---------------------------------
ALTER  PROCEDURE [dbo].[spu_entrada_bodega](@ve_operacion				varchar(20)
									,@ve_cod_entrada_bodega		numeric=null
									,@ve_cod_usuario			numeric=null
									,@ve_cod_bodega				numeric=null
									,@ve_tipo_doc				varchar(100)=null
									,@ve_cod_doc				numeric=null
									,@ve_referencia				varchar(100)=null
									,@ve_obs					text	 = NULL
									,@ve_nro_fa_proveedor		numeric=null
									,@ve_fecha_fa_proveedor		varchar(10) = null
									,@ve_tipo_fa_proveedor		numeric = null
									)
AS
BEGIN
	declare	 @vl_cod_entrada_bodega	numeric
			,@vl_cod_empresa		numeric
			,@vl_cod_faprov			numeric
			,@vl_nro_oc				numeric
			,@vl_total_neto			T_PRECIO
			,@vl_subtotal			T_PRECIO
			,@vl_porc_dscto1		T_PORCENTAJE
			,@vl_porc_dscto2		T_PORCENTAJE
			,@vl_monto_dscto1		T_PRECIO
			,@vl_monto_dscto2		T_PRECIO
			,@vl_total_iva			T_PRECIO
			,@vl_total_con_iva		T_PRECIO
			,@vl_nro_fa_proveedor	numeric
			,@vl_fecha_fa_proveedor varchar(10)
			,@vl_cod_usuario		numeric
			,@vl_referencia			varchar(100)
			,@vl_cod_bodega			numeric
			,@vl_tipo_doc			varchar(100)
			,@vl_arma_compuesto		varchar(1)
			,@vl_terminado_compuesto		varchar(1)
			,@vl_precio_oc			T_PRECIO
			,@vl_porc_iva			numeric(10,2)
	
	if (@ve_operacion='INSERT')
	begin
		insert into ENTRADA_BODEGA
			(FECHA_ENTRADA_BODEGA
			,COD_USUARIO
			,COD_BODEGA 
			,TIPO_DOC   
			,COD_DOC
			,REFERENCIA
			,OBS
			,NRO_FACTURA_PROVEEDOR
			,FECHA_FACTURA_PROVEEDOR
			)
		values
			(getdate()
			,@ve_cod_usuario			
			,@ve_cod_bodega				
			,@ve_tipo_doc				
			,@ve_cod_doc
			,@ve_referencia
			,@ve_obs
			,@ve_nro_fa_proveedor
			,dbo.to_date(@ve_fecha_fa_proveedor)
			)
	end
	else if (@ve_operacion='UPDATE')
		update ENTRADA_BODEGA
		set COD_USUARIO = @ve_cod_usuario
			,COD_BODEGA = @ve_cod_bodega
			,TIPO_DOC = @ve_tipo_doc
			,COD_DOC = @ve_cod_doc
			,REFERENCIA = @ve_referencia
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
	else if (@ve_operacion='DELETE') begin
		delete ITEM_ENTRADA_BODEGA
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega

		delete ENTRADA_BODEGA
		where COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
	end 
	else if (@ve_operacion='FAPROV') begin
		--se obtiene los datos para crear factura proveedor
		SELECT   @vl_cod_empresa = OC.COD_EMPRESA
				,@vl_nro_oc = OC.COD_ORDEN_COMPRA
				,@vl_nro_fa_proveedor = EB.NRO_FACTURA_PROVEEDOR
				,@vl_fecha_fa_proveedor = convert(varchar(10),EB.FECHA_FACTURA_PROVEEDOR,103)
				,@vl_cod_usuario = EB.COD_USUARIO
		FROM ENTRADA_BODEGA EB, ORDEN_COMPRA OC
		WHERE EB.COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
		AND EB.COD_DOC = OC.COD_ORDEN_COMPRA

		-- Situacion no manejada (genera ERROR)
		-- a) si la OC tiene varios items se esta tomando el precio del 1er item 
		-- b) si la OC tiene descuentos estos no se estan considerando
		select top 1 @vl_precio_oc = precio
		from item_orden_compra 
		where cod_orden_compra = @vl_nro_oc

		SELECT	@vl_subtotal = (CANTIDAD * @vl_precio_oc)
		FROM ITEM_ENTRADA_BODEGA
		WHERE COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
		
		SELECT @vl_monto_dscto1 = @vl_subtotal * (PORC_DSCTO1/100)
		FROM ORDEN_COMPRA
		WHERE COD_ORDEN_COMPRA = @vl_nro_oc
		
		SELECT @vl_monto_dscto2 = (@vl_subtotal - @vl_monto_dscto1) * (PORC_DSCTO2/100)
		FROM ORDEN_COMPRA
		WHERE COD_ORDEN_COMPRA = @vl_nro_oc
		
		set @vl_total_neto = @vl_subtotal - (@vl_monto_dscto1 + @vl_monto_dscto2)
		select @vl_porc_iva = dbo.f_get_parametro(1)
		
		SET @vl_total_iva = ((@vl_total_neto * @vl_porc_iva)/100)
		SET @vl_total_con_iva = ROUND(@vl_total_neto + @vl_total_iva, 0)
		
		--crear faprov
		exec spu_faprov 'INSERT'
						,NULL					--@ve_cod_faprov
						,@vl_cod_usuario
						,@vl_cod_empresa
						,4	                    --@ve_cod_tipo_faprov = FA siempre es tipo 4 / MH 1008  2020
						,1						--@ve_cod_estado_faprov = Ingresada
						,@vl_nro_fa_proveedor	--@ve_nro_faprov
						,@vl_fecha_fa_proveedor --@ve_fecha_faprov = fecha participacion
						,@vl_total_neto
						,@vl_total_iva			--@ve_monto_iva
						,@vl_total_con_iva		--@ve_total_con_iva
						,NULL					--@ve_cod_usuario_anula
						,NULL					--@ve_motivo_anula
						,'ORDEN_COMPRA'			--@ve_origen_faprov
						,NULL					--@ve_cod_cuenta_compra		
	
		set @vl_cod_faprov = @@identity

		exec spu_item_faprov 'INSERT'
							,NULL					--@ve_cod_item_faprov
							,@vl_cod_faprov
							,@vl_nro_oc				--cod_orden_compra
							,@vl_total_con_iva		--total_con_iva para item_faprov
		
		SELECT	@vl_arma_compuesto = ITS.ARMA_COMPUESTO
				,@vl_terminado_compuesto = S.TERMINADO_COMPUESTO
		FROM ITEM_ORDEN_COMPRA IT, ITEM_SOLICITUD_COMPRA ITS, SOLICITUD_COMPRA S
		WHERE IT.COD_ORDEN_COMPRA = @vl_nro_oc
		AND ITS.COD_ITEM_SOLICITUD_COMPRA = IT.COD_ITEM_DOC
		AND S.COD_SOLICITUD_COMPRA = ITS.COD_SOLICITUD_COMPRA

		if (@vl_arma_compuesto = 'S' and @vl_terminado_compuesto='C') begin
			--se crea  salida cuando se ingresa  una OC que empresa Armado
			EXEC spu_entrada_bodega	 'CREA_SALIDA_BODEGA'	,@ve_cod_entrada_bodega
		end
	end
	else if (@ve_operacion='CREA_SALIDA_BODEGA') begin
		DECLARE	 @vl_cod_salida_bodega	NUMERIC
				,@vl_cod_item_entrada_bodega	NUMERIC
				,@vl_orden  		NUMERIC
				,@vl_item			NUMERIC
				,@vl_cod_producto	VARCHAR(30)
				,@vl_cod_producto_hijo	VARCHAR(30)
				,@vl_nom_producto	VARCHAR(100)
				,@vl_cantidad		NUMERIC
				,@vl_cod_orden_compra	NUMERIC
				,@vl_cod_producto_armado	VARCHAR(30)
				,@vc_cod_producto_hijo	VARCHAR(30)
				,@vc_cantidad		NUMERIC
				,@vc_genera_compra	NUMERIC
				,@vc_nom_producto	VARCHAR(100)
				,@vc_orden			NUMERIC
				,@vl_i				NUMERIC

		SELECT   @vl_cod_empresa = OC.COD_EMPRESA
				,@vl_referencia = EB.REFERENCIA
				,@vl_cod_usuario = EB.COD_USUARIO
		FROM ENTRADA_BODEGA EB, ORDEN_COMPRA OC
		WHERE EB.COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
		AND EB.COD_DOC = OC.COD_ORDEN_COMPRA
		
		SELECT @vl_cod_bodega = COD_BODEGA
		FROM EMPRESA
		WHERE COD_EMPRESA = @vl_cod_empresa
		
			
		SET @vl_tipo_doc = 'ENTRADA_BODEGA'		
		
		EXEC spu_salida_bodega	 'INSERT'
								,NULL					--@ve_cod_salida_bodega
								,@vl_cod_usuario
								,@vl_cod_bodega
								,@vl_tipo_doc
								,@ve_cod_entrada_bodega
								,@vl_referencia

		set @vl_cod_salida_bodega = @@identity
		
		--se crea  los item_salida necesarios para el producto  final 
		SELECT	 @vl_cod_item_entrada_bodega = COD_ITEM_ENTRADA_BODEGA
				,@vl_cod_producto = ITB.COD_PRODUCTO
				,@vl_nom_producto = ITB.NOM_PRODUCTO
				,@vl_cantidad = ITB.CANTIDAD
				,@vl_cod_orden_compra = EB.COD_DOC
		FROM	ITEM_ENTRADA_BODEGA ITB, ENTRADA_BODEGA EB 
		WHERE 	ITB.COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
		AND		ITB.COD_ENTRADA_BODEGA = EB.COD_ENTRADA_BODEGA 
		
		SELECT @vl_cod_producto_armado = ITS.COD_PRODUCTO
		FROM ITEM_ORDEN_COMPRA IT, ITEM_SOLICITUD_COMPRA ITS
		WHERE IT.COD_ORDEN_COMPRA = @vl_cod_orden_compra
		AND ITS.COD_ITEM_SOLICITUD_COMPRA = IT.COD_ITEM_DOC
		AND ITS.ARMA_COMPUESTO = 'S'
		
		DECLARE C_PRODUCTO_HIJO CURSOR FOR 
		select	 PC.COD_PRODUCTO_HIJO
				,PC.CANTIDAD
				,P.NOM_PRODUCTO
				,PC.ORDEN
		from PRODUCTO_COMPUESTO PC, PRODUCTO P
		where PC.COD_PRODUCTO = @vl_cod_producto
		and	PC.COD_PRODUCTO_HIJO <> @vl_cod_producto_armado
		and P.COD_PRODUCTO = PC.COD_PRODUCTO_HIJO
		order by PC.ORDEN
		
		SET @vl_i = 1
		OPEN C_PRODUCTO_HIJO
		FETCH C_PRODUCTO_HIJO INTO   @vc_cod_producto_hijo	,@vc_cantidad
									,@vc_nom_producto		,@vc_orden		
		WHILE @@FETCH_STATUS = 0 BEGIN
			
			SET @vl_cantidad = @vc_cantidad * @vl_cantidad
			EXEC spu_item_salida_bodega	'INSERT'
										,NULL
										,@vl_cod_salida_bodega
										,@vc_orden
										,@vl_i
										,@vc_cod_producto_hijo
										,@vc_nom_producto
										,@vl_cantidad
										,@vl_cod_item_entrada_bodega
			SET @vl_i = @vl_i + 1
		FETCH C_PRODUCTO_HIJO INTO   @vc_cod_producto_hijo	,@vc_cantidad
									,@vc_nom_producto		,@vc_orden		
		END
		CLOSE C_PRODUCTO_HIJO
		DEALLOCATE C_PRODUCTO_HIJO					
	end 
END
GO