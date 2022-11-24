
ALTER PROCEDURE [dbo].[spi_salida_bodega](@ve_cod_salida_bodega numeric)
AS
BEGIN
	declare @TEMPO TABLE    
	   (COD_SALIDA_BODEGA		numeric
		,FECHA_IMPRESION		varchar(100)
		,HORA_IMPRESION			varchar(100)
		,FECHA_SALIDA_BODEGA	varchar(100)
		,HORA_SALIDA_BODEGA		varchar(100)
		,NOM_BODEGA				varchar(100)
		,TIPO_DOC				varchar(100)
		,NRO_DOC				numeric
		,FECHA_DOC				varchar(100)
		,REFERENCIA				varchar(100)
		,COD_ORDEN_COMPRA_COMERCIAL numeric
		,COD_NOTA_VENTA			numeric
		,NOM_VENDEDOR			varchar(100)
		,ITEM					varchar(100)
		,COD_PRODUCTO			varchar(100)
		,NOM_PRODUCTO			varchar(100)
		,CANTIDAD				numeric(10,2)
		)

	insert into @TEMPO 
	   (COD_SALIDA_BODEGA		
		,FECHA_IMPRESION		
		,HORA_IMPRESION			
		,FECHA_SALIDA_BODEGA	
		,HORA_SALIDA_BODEGA		
		,NOM_BODEGA				
		,TIPO_DOC				
		,NRO_DOC				
		,FECHA_DOC				
		,REFERENCIA				
		,COD_ORDEN_COMPRA_COMERCIAL		
		,COD_NOTA_VENTA	
		,NOM_VENDEDOR			
		,ITEM					
		,COD_PRODUCTO			
		,NOM_PRODUCTO			
		,CANTIDAD				
		)
	select S.COD_SALIDA_BODEGA
		,CONVERT(varchar, getdate(), 103) FECHA_IMPRESION
		,CONVERT(varchar, getdate(), 108) HORA_IMPRESION
		,dbo.f_format_date(S.FECHA_SALIDA_BODEGA, 3) FECHA_SALIDA_BODEGA
		,CONVERT(varchar, S.FECHA_SALIDA_BODEGA, 108) HORA_SALIDA_BODEGA
		,B.NOM_BODEGA
		,S.TIPO_DOC
		,dbo.f_get_nro_doc(S.TIPO_DOC, S.COD_DOC)	--NRO_DOC
		,CONVERT(varchar, dbo.f_salida_fecha_doc(S.COD_SALIDA_BODEGA), 103)	--FECHA_DOC
		,S.REFERENCIA
		,dbo.f_salida_OC_COMERCIAL(S.COD_SALIDA_BODEGA) --COD_ORDEN_COMPRA_COMECIAL
		,dbo.f_salida_NV_COMERCIAL(S.COD_SALIDA_BODEGA) --COD_NOTA_VENTA	
		,dbo.f_salida_VEND_COMERCIAL(S.COD_SALIDA_BODEGA) --NOM_VENDEDOR			
		,I.ITEM
		,I.COD_PRODUCTO
		,I.NOM_PRODUCTO
		,I.CANTIDAD
	from SALIDA_BODEGA S, ITEM_SALIDA_BODEGA I, BODEGA B
	where S.COD_SALIDA_BODEGA = @ve_cod_salida_bodega
	  and I.COD_SALIDA_BODEGA = S.COD_SALIDA_BODEGA
	  and B.COD_BODEGA = S.COD_BODEGA
	order by I.ORDEN

	select * from @TEMPO
END
