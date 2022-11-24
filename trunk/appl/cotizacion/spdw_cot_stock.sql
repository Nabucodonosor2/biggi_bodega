-------------------------- spdw_cot_stock ---------------------------
alter PROCEDURE spdw_cot_stock(@ve_cod_cotizacion numeric)
AS
BEGIN
	declare @TEMPO TABLE 	 
		   (ST_COD_PRODUCTO			varchar(30)  
			,ST_NOM_PRODUCTO			varchar(100) 
			,ST_CANTIDAD				numeric(10,2)
			,ST_STOCK					numeric(10,2)
			,ST_COLOR_FONT				varchar(100)
			)
			
	insert into @TEMPO
		   (ST_COD_PRODUCTO			
			,ST_NOM_PRODUCTO			
			,ST_CANTIDAD				
			,ST_STOCK	
			,ST_COLOR_FONT				
			)
	select COD_PRODUCTO			
			,NOM_PRODUCTO			
			,sum(CANTIDAD)
			,BODEGA_BIGGI.dbo.f_bodega_stock_cero(cod_producto, 2, GETDATE())	-- cod_bodega = 2 = EQ TERMINADOS
			,'#000000'	--negro
	from ITEM_COTIZACION
	where COD_COTIZACION = @ve_cod_cotizacion
	  and COD_PRODUCTO not in ('T', 'TE', 'I', 'F', 'E')
	group by COD_PRODUCTO, NOM_PRODUCTO


	declare C_TEMPO cursor for
	select	 ST_COD_PRODUCTO
			,ST_CANTIDAD				
			,ST_STOCK
	from @TEMPO

	declare
		@vc_cod_producto		varchar(100)
		,@vc_cantidad			numeric(10,2)
		,@vc_stock				numeric(10,2)
		,@vl_count				numeric

	OPEN C_TEMPO
	FETCH C_TEMPO INTO @vc_cod_producto, @vc_cantidad, @vc_stock
	WHILE @@FETCH_STATUS = 0 BEGIN
		select @vl_count = count(*)
		from BODEGA_BIGGI.dbo.PRODUCTO P
		where P.COD_PRODUCTO = @vc_cod_producto
		  and substring(sistema_valido, 2, 1) = 'S'	-- bodega biggi
		  and P.maneja_inventario = 'S'
		
		if (@vl_count = 0)-- Bodega Biggi no maneja inventario para este producto
			update @TEMPO
			set ST_STOCK = null
			where ST_COD_PRODUCTO = @vc_cod_producto
		else if (@vc_cantidad > @vc_stock)	-- bajo stock
			update @TEMPO
			set ST_COLOR_FONT = '#FF0000'	-- rojo
			where ST_COD_PRODUCTO = @vc_cod_producto
		
		FETCH C_TEMPO INTO @vc_cod_producto, @vc_cantidad, @vc_stock
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO
		
	select *
	from @TEMPO 
END
