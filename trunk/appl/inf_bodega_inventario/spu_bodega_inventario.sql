-------------------- spu_bodega_inventario ---------------------------------	
CREATE PROCEDURE [dbo].[spu_bodega_inventario](@ve_cod_usuario			numeric)
AS
BEGIN
	declare @vl_fecha_actual		datetime,
            @vl_cod_bodega				numeric

	set @vl_fecha_actual = getdate()
    set @vl_cod_bodega = 2 -- eq terminado

    delete INF_BODEGA_INVENTARIO
	where FECHA_INF_BODEGA_INVENTARIO < DATEADD(DAY, -10, @vl_fecha_actual)

	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_BODEGA_INVENTARIO
	where cod_usuario = @ve_cod_usuario

	insert into INF_BODEGA_INVENTARIO
	   (FECHA_INF_BODEGA_INVENTARIO
        ,COD_USUARIO
        ,COD_PRODUCTO				
		,NOM_PRODUCTO				
		,COD_MARCA					
		,NOM_MARCA					
		,CANTIDAD
		,POR_RECIBIR
		)
	select  @vl_fecha_actual
            ,@ve_cod_usuario
            ,P.COD_PRODUCTO
			,P.NOM_PRODUCTO
			,P.COD_MARCA
			,M.NOM_MARCA
			,dbo.f_bodega_stock(P.COD_PRODUCTO, @vl_cod_bodega, @vl_fecha_actual) CANTIDAD
			,dbo.f_bodega_por_recibir(P.COD_PRODUCTO) POR_RECIBIR
	from PRODUCTO P left outer join MARCA M on M.COD_MARCA = P.COD_MARCA
	where substring(sistema_valido, 2, 1) = 'S'
	  and P.maneja_inventario = 'S'

END


GO
