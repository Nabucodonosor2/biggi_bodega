-------------------- spi_bodega_inventario ---------------------------------	
create PROCEDURE spi_bodega_inventario(@ve_cod_bodega				numeric
									,@ve_fecha_stock			datetime)
AS
BEGIN
	declare @TEMPO TABLE    
	   (COD_PRODUCTO				varchar(100)
		,NOM_PRODUCTO				varchar(100)
		,COD_MARCA					numeric
		,NOM_MARCA					varchar(100)
		,CANTIDAD					numeric(10,2)
		,POR_RECIBIR				NUMERIC(10,2)
		)

	insert into @TEMPO
	   (COD_PRODUCTO				
		,NOM_PRODUCTO				
		,COD_MARCA					
		,NOM_MARCA					
		,CANTIDAD
		,POR_RECIBIR
		)
	select P.COD_PRODUCTO
			,P.NOM_PRODUCTO
			,P.COD_MARCA
			,M.NOM_MARCA
			,dbo.f_bodega_stock(P.COD_PRODUCTO, @ve_cod_bodega, @ve_fecha_stock) CANTIDAD
			,dbo.f_bodega_por_recibir(P.COD_PRODUCTO) POR_RECIBIR
	from PRODUCTO P left outer join MARCA M on M.COD_MARCA = P.COD_MARCA
	where substring(sistema_valido, 2, 1) = 'S'
	  and P.maneja_inventario = 'S'

	select row_number() over (order by NOM_PRODUCTO)  ROWNUMBER, t.*
	from @TEMPO t
	order by NOM_PRODUCTO
END

