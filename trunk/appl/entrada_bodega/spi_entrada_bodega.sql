-------------------- spi_entrada_bodega ---------------------------------	
alter  PROCEDURE spi_entrada_bodega(@ve_cod_entrada_bodega numeric)
AS
BEGIN
	declare @TEMPO TABLE    
	   (COD_ENTRADA_BODEGA		numeric
		,FECHA_IMPRESION		varchar(100)
		,HORA_IMPRESION			varchar(100)
		,FECHA_ENTRADA_BODEGA	varchar(100)
		,HORA_ENTRADA_BODEGA	varchar(100)
		,NOM_BODEGA				varchar(100)
		,TIPO_DOC				varchar(100)
		,COD_DOC				numeric
		,FECHA_DOC				varchar(100)
		,REFERENCIA				varchar(100)
		,OBS					text
		,NOM_EMPRESA			varchar(100)
		,NRO_FACTURA_PROVEEDOR  numeric
		,FECHA_FACTURA_PROVEEDOR  varchar(100)
		,TOTAL_ENTRADA			numeric
		,ITEM					varchar(100)
		,COD_PRODUCTO			varchar(100)
		,NOM_PRODUCTO			varchar(100)
		,CANTIDAD				numeric(10,2)
		,PRECIO					numeric
		,TOTAL					numeric
		)

	insert into @TEMPO 
	   (COD_ENTRADA_BODEGA		
		,FECHA_IMPRESION		
		,HORA_IMPRESION			
		,FECHA_ENTRADA_BODEGA	
		,HORA_ENTRADA_BODEGA	
		,NOM_BODEGA				
		,TIPO_DOC				
		,COD_DOC				
		,FECHA_DOC	
		,REFERENCIA	
		,OBS		
		,NOM_EMPRESA
		,NRO_FACTURA_PROVEEDOR  
		,FECHA_FACTURA_PROVEEDOR
		,TOTAL_ENTRADA
		,ITEM					
		,COD_PRODUCTO			
		,NOM_PRODUCTO			
		,CANTIDAD				
		,PRECIO					
		,TOTAL					
		)
	select E.COD_ENTRADA_BODEGA
		,CONVERT(varchar, getdate(), 103) FECHA_IMPRESION
		,CONVERT(varchar, getdate(), 108) HORA_IMPRESION
		,dbo.f_format_date(E.FECHA_ENTRADA_BODEGA, 3) FECHA_ENTRADA_BODEGA
		,CONVERT(varchar, E.FECHA_ENTRADA_BODEGA, 108) HORA_ENTRADA_BODEGA
		,B.NOM_BODEGA
		,E.TIPO_DOC
		,E.COD_DOC
		,null	--FECHA_DOC
		,E.REFERENCIA
		,E.OBS
		,null --NOM_EMPRESA
		,E.NRO_FACTURA_PROVEEDOR  
		,CONVERT(varchar, E.FECHA_FACTURA_PROVEEDOR, 103) FECHA_FACTURA_PROVEEDOR
		,null --TOTAL_ENTRADA
		,I.ITEM
		,I.COD_PRODUCTO
		,I.NOM_PRODUCTO
		,I.CANTIDAD
		,I.PRECIO
		,round(I.CANTIDAD * I.PRECIO, 0)	--TOTAL
	from ENTRADA_BODEGA E, ITEM_ENTRADA_BODEGA I, BODEGA B
	where E.COD_ENTRADA_BODEGA = @ve_cod_entrada_bodega
	  and I.COD_ENTRADA_BODEGA = E.COD_ENTRADA_BODEGA
	  and B.COD_BODEGA = E.COD_BODEGA
	order by ORDEN

	declare
		@vl_tipo_doc		varchar(100)
		,@vl_fecha_doc		varchar(100)
		,@vl_nom_empresa	varchar(100)
		,@vl_suma			numeric

	select @vl_tipo_doc	= tipo_doc		
	from entrada_bodega 
	where cod_entrada_bodega = @ve_cod_entrada_bodega
	
	if (@vl_tipo_doc = 'ORDEN_COMPRA')
		select @vl_fecha_doc = convert(varchar, o.fecha_orden_compra, 103)
				,@vl_nom_empresa = em.nom_empresa
		from entrada_bodega e, orden_compra o, empresa em
		where e.cod_entrada_bodega = @ve_cod_entrada_bodega
		  and o.cod_orden_compra = e.cod_doc
		  and em.cod_empresa = o.cod_empresa
	else begin
		set @vl_fecha_doc = null
		set @vl_nom_empresa = null
	end

	select @vl_suma = sum(TOTAL)
	from @TEMPO

	update @TEMPO
	set fecha_doc = @vl_fecha_doc
		,nom_empresa = @vl_nom_empresa
		,TOTAL_ENTRADA = @vl_suma

	select * from @TEMPO
END
