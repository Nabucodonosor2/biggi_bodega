--------------- spi_ventas_por_equipo --------------
alter PROCEDURE spi_ventas_por_equipo(@ve_cod_usuario			numeric, @ve_ano 	numeric)
AS
BEGIN

declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_VENTAS_POR_EQUIPO
	where cod_usuario = @ve_cod_usuario

	insert into	INF_VENTAS_POR_EQUIPO
		(FECHA_INF_VENTAS_POR_EQUIPO 
		,COD_USUARIO 
		,MES                          
		,ANO                          
		,COD_PRODUCTO                 
		,TIPO_DOC                     
		,COD_DOC                      
		,NRO_DOC                      
		,FECHA_DOC                    
		,NOM_EMPRESA                  
		,CANTIDAD                     
		,PRECIO                       
		,TOTAL                        
		)
	select @vl_fecha_actual
		,@ve_cod_usuario
		,MONTH(F.FECHA_FACTURA) MES
		,year(F.FECHA_FACTURA) ANO
		,case F.DESDE_4D
			when 'S' then I.COD_PRODUCTO_4D
			else I.COD_PRODUCTO
		end 
		,'FA'	TIPO_DOC
		,F.COD_FACTURA COD_DOC
		,F.NRO_FACTURA NRO_DOC
		,F.FECHA_FACTURA
		,F.NOM_EMPRESA
		,I.CANTIDAD
		,round(I.PRECIO * F.TOTAL_NETO / F.SUBTOTAL, 0) PRECIO
		,round(I.CANTIDAD * I.PRECIO * F.TOTAL_NETO / F.SUBTOTAL, 0) TOTAL
	from	ITEM_FACTURA I, FACTURA F
	where	F.COD_ESTADO_DOC_SII in (2,3)
		and F.SUBTOTAL > 0
		and I.COD_FACTURA = F.COD_FACTURA 
		and year(F.FECHA_FACTURA) = @ve_ano
  
  
	insert into	INF_VENTAS_POR_EQUIPO
		(FECHA_INF_VENTAS_POR_EQUIPO 
		,COD_USUARIO 
		,MES                          
		,ANO                          
		,COD_PRODUCTO                 
		,TIPO_DOC                     
		,COD_DOC                      
		,NRO_DOC                      
		,FECHA_DOC                    
		,NOM_EMPRESA                  
		,CANTIDAD                     
		,PRECIO                       
		,TOTAL                        
		)
	select @vl_fecha_actual
		,@ve_cod_usuario
		,MONTH(N.FECHA_NOTA_CREDITO) MES
		,year(N.FECHA_NOTA_CREDITO) ANO
		,I.COD_PRODUCTO
		,'NC'	TIPO_DOC
		,N.COD_NOTA_CREDITO
		,N.NRO_NOTA_CREDITO
		,N.FECHA_NOTA_CREDITO
		,N.NOM_EMPRESA
		,- I.CANTIDAD
		,- round(I.PRECIO * N.TOTAL_NETO / N.SUBTOTAL, 0) PRECIO
		,- round(I.CANTIDAD * I.PRECIO * N.TOTAL_NETO / N.SUBTOTAL, 0) TOTAL
	from	ITEM_NOTA_CREDITO I, NOTA_CREDITO N
	where	N.COD_ESTADO_DOC_SII in (2,3)
	  and   N.SUBTOTAL > 0
	  and 	I.COD_NOTA_CREDITO = N.COD_NOTA_CREDITO
	  and year(N.FECHA_NOTA_CREDITO) = @ve_ano
  

 END