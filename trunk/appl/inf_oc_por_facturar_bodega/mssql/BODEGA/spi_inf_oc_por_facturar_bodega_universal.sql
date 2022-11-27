CREATE PROCEDURE spi_inf_oc_por_facturar_bodega_universal(@ve_cod_usuario   numeric,
                                                          @ve_origen        varchar(50))
AS
BEGIN
    if(@ve_origen = 'COMERCIAL') BEGIN
        -- borra el resultado de informes anteriores del mismo usuario
        delete BIGGI.dbo.INF_OC_POR_FACTURAR_BODEGA
        where cod_usuario = @ve_cod_usuario
        
        INSERT INTO BIGGI.dbo.INF_OC_POR_FACTURAR_BODEGA(FECHA_INF_OC_POR_FACTURAR_BODEGA
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
												SELECT getdate()
														,@ve_cod_usuario
														,O.COD_ORDEN_COMPRA
														,FECHA_ORDEN_COMPRA
														,COD_ITEM_ORDEN_COMPRA
														,COD_PRODUCTO
														,NOM_PRODUCTO
														,CANTIDAD
														,0
														,0
														,nv.COD_NOTA_VENTA
														,(SELECT U.INI_USUARIO FROM BIGGI.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
														,(SELECT U.NOM_USUARIO FROM BIGGI.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
												from BIGGI.dbo.ITEM_ORDEN_COMPRA i, BIGGI.dbo.ORDEN_COMPRA o left outer join BIGGI.dbo.NOTA_VENTA nv on o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
												where o.COD_ORDEN_COMPRA > 223493
												and o.COD_EMPRESA = 1138    --bodega
												and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
												--and O.COD_ESTADO_ORDEN_COMPRA = 1
												and O.COD_ESTADO_ORDEN_COMPRA in (1,3)
												and BIGGI.dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
												AND i.FACTURADO_SIN_WS = 'N'
                                        
        SELECT convert(varchar, FECHA_INF_OC_POR_FACTURAR_BODEGA, 103) FECHA_INF_OC_POR_FACTURAR_TDNX	--se usa el nombre TDNX para que la variable se llame igual en todos lados
                ,COD_USUARIO
                ,COD_ORDEN_COMPRA
                ,convert(varchar, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
                ,COD_NOTA_VENTA
                ,COD_USUARIO_VENDEDOR
                ,COD_ITEM_ORDEN_COMPRA
                ,COD_PRODUCTO
                ,NOM_PRODUCTO
                ,CANTIDAD_OC
                ,NOM_USUARIO
        FROM BIGGI.dbo.INF_OC_POR_FACTURAR_BODEGA
        where cod_usuario = @ve_cod_usuario
        ORDER BY FECHA_INF_OC_POR_FACTURAR_BODEGA DESC
	END
    ELSE IF(@ve_origen = 'RENTAL') BEGIN
		
		-- borra el resultado de informes anteriores del mismo usuario
		delete RENTAL.dbo.INF_OC_POR_FACTURAR_BODEGA
		where cod_usuario = @ve_cod_usuario
	
		INSERT INTO RENTAL.dbo.INF_OC_POR_FACTURAR_BODEGA(FECHA_INF_OC_POR_FACTURAR_BODEGA
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
												SELECT getdate()
														,@ve_cod_usuario
														,O.COD_ORDEN_COMPRA
														,FECHA_ORDEN_COMPRA
														,COD_ITEM_ORDEN_COMPRA
														,COD_PRODUCTO
														,NOM_PRODUCTO
														,CANTIDAD
														,0
														,0
														,case 
															when TIPO_ORDEN_COMPRA = 'ARRIENDO' then COD_DOC
															when TIPO_ORDEN_COMPRA = 'NOTA_VENTA' then nv.COD_NOTA_VENTA
														end COD_NOTA_VENTA
														,(SELECT U.INI_USUARIO FROM RENTAL.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
														,(SELECT U.NOM_USUARIO FROM RENTAL.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
													from RENTAL.dbo.ITEM_ORDEN_COMPRA i, RENTAL.dbo.ORDEN_COMPRA o left outer join RENTAL.dbo.NOTA_VENTA nv on o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
													where o.COD_ORDEN_COMPRA > 65671
													and o.COD_EMPRESA = 28    --bodega
													and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
													and O.COD_ESTADO_ORDEN_COMPRA = 4
													and RENTAL.dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
													AND i.FACTURADO_SIN_WS = 'N'
									 
		SELECT convert(varchar, FECHA_INF_OC_POR_FACTURAR_BODEGA, 103) FECHA_INF_OC_POR_FACTURAR_TDNX	--se usa el nombre TDNX para que la variable se llame igual en todos lados
				,COD_USUARIO
				,COD_ORDEN_COMPRA
				,convert(varchar, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
				,COD_NOTA_VENTA
				,COD_USUARIO_VENDEDOR
				,COD_ITEM_ORDEN_COMPRA
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD_OC
				,NOM_USUARIO
		FROM RENTAL.dbo.INF_OC_POR_FACTURAR_BODEGA
		where cod_usuario = @ve_cod_usuario
		ORDER BY FECHA_INF_OC_POR_FACTURAR_BODEGA DESC

	END
    ELSE IF(@ve_origen = 'TODOINOX') BEGIN
		
		-- borra el resultado de informes anteriores del mismo usuario
		delete TODOINOX.dbo.INF_OC_POR_FACTURAR_BODEGA
		where cod_usuario = @ve_cod_usuario
	
		INSERT INTO TODOINOX.dbo.INF_OC_POR_FACTURAR_BODEGA(FECHA_INF_OC_POR_FACTURAR_BODEGA
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
													SELECT getdate()
															,@ve_cod_usuario
															,O.COD_ORDEN_COMPRA
															,FECHA_ORDEN_COMPRA
															,COD_ITEM_ORDEN_COMPRA
															,COD_PRODUCTO
															,NOM_PRODUCTO
															,CANTIDAD
															,0
															,0
															,nv.COD_NOTA_VENTA
															,(SELECT U.INI_USUARIO FROM TODOINOX.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) COD_USUARIO_VENDEDOR
															,(SELECT U.NOM_USUARIO FROM TODOINOX.dbo.USUARIO U WHERE U.COD_USUARIO = nv.COD_USUARIO_VENDEDOR1) NOM_USUARIO
														from TODOINOX.dbo.ITEM_ORDEN_COMPRA i, TODOINOX.dbo.ORDEN_COMPRA o left outer join TODOINOX.dbo.NOTA_VENTA nv on o.COD_NOTA_VENTA = nv.COD_NOTA_VENTA
														where o.COD_ORDEN_COMPRA > 22231
														and o.COD_EMPRESA = 37    --BODEGA
														and i.COD_ORDEN_COMPRA = o.COD_ORDEN_COMPRA
														and O.COD_ESTADO_ORDEN_COMPRA = 1
														and TODOINOX.dbo.f_oc_get_saldo_sin_faprov(O.COD_ORDEN_COMPRA) > 0
														AND i.FACTURADO_SIN_WS = 'N'
									 
		SELECT convert(varchar, FECHA_INF_OC_POR_FACTURAR_BODEGA, 103) FECHA_INF_OC_POR_FACTURAR_TDNX	--se usa el nombre TDNX para que la variable se llame igual en todos lados
				,COD_USUARIO
				,COD_ORDEN_COMPRA
				,convert(varchar, FECHA_ORDEN_COMPRA, 103) FECHA_ORDEN_COMPRA
				,COD_NOTA_VENTA
				,COD_USUARIO_VENDEDOR
				,COD_ITEM_ORDEN_COMPRA
				,COD_PRODUCTO
				,NOM_PRODUCTO
				,CANTIDAD_OC
				,NOM_USUARIO
		FROM TODOINOX.dbo.INF_OC_POR_FACTURAR_BODEGA
		where cod_usuario = @ve_cod_usuario
		ORDER BY FECHA_INF_OC_POR_FACTURAR_BODEGA DESC
    END
END