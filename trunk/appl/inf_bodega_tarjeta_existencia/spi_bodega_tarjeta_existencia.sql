-------------------- spi_bodega_tarjeta_existencia ---------------------------------	
alter PROCEDURE spi_bodega_tarjeta_existencia(@ve_cod_bodega				numeric
												,@ve_cod_producto			varchar(100)
												,@ve_fecha_inicio			datetime
												,@ve_fecha_termino			datetime)
AS
BEGIN
	declare @TEMPO TABLE    
	   (ID							numeric identity
		,FECHA						datetime
		,FECHA_STR					varchar(100)
		,REFERENCIA					varchar(100)
		,TIPO_DOC					varchar(100)
		,COD_DOC					numeric
		,CANTIDAD_ENTRADA			numeric(10,2)
		,CANTIDAD_SALIDA			numeric(10,2)
		,CANTIDAD_STOCK				numeric(10,2)
		)

	-- stock inicial
	insert into @TEMPO
	   (FECHA						
		,FECHA_STR					
		,REFERENCIA					
		,TIPO_DOC					
		,COD_DOC					
		,CANTIDAD_ENTRADA			
		,CANTIDAD_SALIDA			
		,CANTIDAD_STOCK				
		)
	values
		({ts '1900-01-01 00:00:00.000'}
		,null
		,'STOCK_INICIAL'
		,null
		,null
		,dbo.f_bodega_stock(@ve_cod_producto, @ve_cod_bodega, @ve_fecha_inicio)
		,0
		,null
		)

	-- entradas
	insert into @TEMPO
	   (FECHA						
		,FECHA_STR					
		,REFERENCIA					
		,TIPO_DOC					
		,COD_DOC					
		,CANTIDAD_ENTRADA			
		,CANTIDAD_SALIDA			
		,CANTIDAD_STOCK				
		)
	select e.fecha_entrada_bodega
			,convert(varchar, e.fecha_entrada_bodega, 103)
			,e.referencia
			,e.tipo_doc
			,dbo.f_get_nro_doc(e.tipo_doc, e.cod_doc) 
			,i.cantidad
			,0
			,null
	from entrada_bodega e, item_entrada_bodega i
	where e.fecha_entrada_bodega > @ve_fecha_inicio
	  and e.fecha_entrada_bodega <= @ve_fecha_termino
	  and e.cod_bodega = @ve_cod_bodega
	  and i.cod_entrada_bodega = e.cod_entrada_bodega
	  and i.cod_producto = @ve_cod_producto

	-- salidas
	insert into @TEMPO
	   (FECHA						
		,FECHA_STR					
		,REFERENCIA					
		,TIPO_DOC					
		,COD_DOC					
		,CANTIDAD_ENTRADA			
		,CANTIDAD_SALIDA			
		,CANTIDAD_STOCK				
		)
	select s.fecha_salida_bodega
		,convert(varchar, s.fecha_salida_bodega, 103)
		,s.referencia
		,s.tipo_doc
		,dbo.f_get_nro_doc(s.tipo_doc, s.cod_doc) 
		,0
		,i.cantidad
		,null
	from salida_bodega s, item_salida_bodega i
	where s.fecha_salida_bodega > @ve_fecha_inicio
	  and s.fecha_salida_bodega <= @ve_fecha_termino
	  and s.cod_bodega = @ve_cod_bodega
	  and i.cod_salida_bodega = s.cod_salida_bodega
	  and i.cod_producto = @ve_cod_producto

	DECLARE C_TEMPO CURSOR FOR
    SELECT ID							
			,CANTIDAD_ENTRADA			
			,CANTIDAD_SALIDA			
    FROM @TEMPO
	order by FECHA
            
	declare
		@vc_id					numeric
		,@vc_cantidad_entrada	numeric(10,2)
		,@vc_cantidad_salida	numeric(10,2)
		,@vl_stock				numeric(10,2)

	set @vl_stock = 0

	OPEN C_TEMPO
	FETCH C_TEMPO INTO @vc_id, @vc_cantidad_entrada, @vc_cantidad_salida
	WHILE @@FETCH_STATUS = 0 BEGIN
		set @vl_stock = @vl_stock + @vc_cantidad_entrada - @vc_cantidad_salida
		update @TEMPO
		set CANTIDAD_STOCK = @vl_stock
		where ID = @vc_id
	    
		FETCH C_TEMPO INTO @vc_id, @vc_cantidad_entrada, @vc_cantidad_salida
	END
	CLOSE C_TEMPO
	DEALLOCATE C_TEMPO

	select FECHA_STR					
		,REFERENCIA					
		,TIPO_DOC					
		,COD_DOC					
		,CANTIDAD_ENTRADA			
		,CANTIDAD_SALIDA			
		,CANTIDAD_STOCK				
	from @TEMPO
	order by FECHA
END
