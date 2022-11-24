--------------- spi_ventas_por_equipo --------------
ALTER PROCEDURE spi_ventas_por_equipo(
@ve_cod_usuario			numeric,
@ve_ano 	numeric,
@ve_5_ano varchar(1)='N'
)
AS
BEGIN

declare
	@vl_fecha_actual		datetime
    ,@vl_count    NUMERIC

	set @vl_fecha_actual = getdate()
	

	if(@ve_5_ano = 'S')BEGIN 

 
    ---borra los datos del año en curso
    	delete INF_VENTAS_POR_EQUIPO
        where cod_usuario = @ve_cod_usuario
        AND origen='5ANO'
        and ANO= year(getdate())

        select @vl_count = count (*  )
        from INF_VENTAS_POR_EQUIPO  
        WHERE COD_USUARIO= @ve_cod_usuario
        AND origen='5ANO'
 
		declare  @vl_5_ano numeric

        IF(@vl_count=0)BEGIN
            set @vl_5_ano = year(getdate())-5  ---si nunca genero el informe inserta  los ultimos 5 años
        END
        ELSE BEGIN
            set @vl_5_ano = year(getdate())  ---si genero el informe inserta los datos del año en curso
        END
		
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
        ,ORIGEN                         
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
        ,'5ANO'
	from	ITEM_FACTURA I, FACTURA F
	where	F.COD_ESTADO_DOC_SII in (2,3)
		and F.SUBTOTAL > 0
		and I.COD_FACTURA = F.COD_FACTURA 
		and year(F.FECHA_FACTURA) BETWEEN   @vl_5_ano AND year(getdate())
  
  
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
        ,ORIGEN                        
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
         ,'5ANO'
	from	ITEM_NOTA_CREDITO I, NOTA_CREDITO N
	where	N.COD_ESTADO_DOC_SII in (2,3)
	  and   N.SUBTOTAL > 0
	  and 	I.COD_NOTA_CREDITO = N.COD_NOTA_CREDITO
	  and year(N.FECHA_NOTA_CREDITO) BETWEEN @vl_5_ano AND year(getdate())
     
	END 
	ELSE BEGIN 
        delete INF_VENTAS_POR_EQUIPO
        where cod_usuario = @ve_cod_usuario
        AND origen='1ANO'

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
        ,ORIGEN                         
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
         ,'1ANO'
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
        ,ORIGEN                         
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
        ,'1ANO'
	from	ITEM_NOTA_CREDITO I, NOTA_CREDITO N
	where	N.COD_ESTADO_DOC_SII in (2,3)
	  and   N.SUBTOTAL > 0
	  and 	I.COD_NOTA_CREDITO = N.COD_NOTA_CREDITO
	  and year(N.FECHA_NOTA_CREDITO) = @ve_ano
  	END

 END;
