CREATE PROCEDURE spi_inf_oc_por_facturar_servindus(@ve_cod_usuario	numeric)
as
BEGIN
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_OC_POR_FACTURAR_TDNX
	where cod_usuario = @ve_cod_usuario
		
	INSERT INTO INF_OC_POR_FACTURAR_TDNX(FECHA_INF_OC_POR_FACTURAR_TDNX
										,COD_USUARIO
										,COD_ORDEN_COMPRA
										,FECHA_ORDEN_COMPRA
										,COD_ITEM_ORDEN_COMPRA
										,COD_PRODUCTO
										,NOM_PRODUCTO
										,CANTIDAD_OC
										,CANT_FA
										,CANT_POR_FACT
										,COD_NOTA_VENTA
										,COD_USUARIO_VENDEDOR
										,NOM_USUARIO)
								SELECT CONVERT(VARCHAR, GETDATE(), 103)
										,@ve_cod_usuario COD_USUARIO
										,O.COD_ORDEN_COMPRA
										,CONVERT(VARCHAR, FECHA_ORDEN_COMPRA, 103)
										,COD_ITEM_ORDEN_COMPRA
										,COD_PRODUCTO
										,NOM_PRODUCTO
										,CANTIDAD
										,0
										,0
										,COD_DOC
										,(SELECT INI_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) COD_USUARIO_VENDEDOR
										,(SELECT NOM_USUARIO FROM USUARIO WHERE COD_USUARIO = o.COD_USUARIO_SOLICITA) NOM_USUARIO
								from ITEM_ORDEN_COMPRA i, ORDEN_COMPRA o
								where o.COD_ORDEN_COMPRA > 56576
								and o.COD_EMPRESA = 5    -- servindus
								and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
								and O.COD_ESTADO_ORDEN_COMPRA = 1
								and o.TIPO_ORDEN_COMPRA = 'SOLICITUD_COMPRA'
								and dbo.f_oc_por_llegar (i.cod_item_orden_compra) > 0
								AND i.FACTURADO_SIN_WS = 'N'
	
	SELECT FECHA_INF_OC_POR_FACTURAR_TDNX
			,COD_USUARIO
			,COD_ORDEN_COMPRA
			,FECHA_ORDEN_COMPRA
			,COD_NOTA_VENTA
			,COD_USUARIO_VENDEDOR
			,COD_ITEM_ORDEN_COMPRA
			,COD_PRODUCTO
			,NOM_PRODUCTO
			,CANTIDAD_OC
			,COD_USUARIO_VENDEDOR
			,NOM_USUARIO
	FROM INF_OC_POR_FACTURAR_TDNX
	where cod_usuario = @ve_cod_usuario
	ORDER BY FECHA_INF_OC_POR_FACTURAR_TDNX DESC
END