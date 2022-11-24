ALTER PROCEDURE [dbo].[spi_facturas_por_mes](@ve_cod_usuario			numeric,
                                                  @ve_ano               numeric )
AS
BEGIN

declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_FACTURAS_POR_MES
	where cod_usuario = @ve_cod_usuario

	insert into	INF_FACTURAS_POR_MES
		(FECHA_INF_FACTURAS_POR_MES  
		,COD_USUARIO                 
		,MES                         
		,ANO                         
		,TIPO_DOC                    
		,COD_DOC                     
		,NRO_DOC                     
		,FECHA_DOC                   
		,NOM_EMPRESA                 
		,TOTAL_NETO                  
		,MONTO_IVA                   
		,TOTAL_CON_IVA               
		)
	select @vl_fecha_actual
		,@ve_cod_usuario
		,MONTH(F.FECHA_FACTURA) MES
		,year(F.FECHA_FACTURA) ANO
		,'FA'	TIPO_DOC
		,F.COD_FACTURA COD_DOC
		,F.NRO_FACTURA NRO_DOC
		,F.FECHA_FACTURA
		,F.NOM_EMPRESA
		,F.TOTAL_NETO
		,F.MONTO_IVA
		,F.TOTAL_CON_IVA
	from	FACTURA F
	where	F.COD_ESTADO_DOC_SII in (2,3)
		and F.SUBTOTAL > 0
        and year(F.FECHA_FACTURA) = @ve_ano
  
	insert into	INF_FACTURAS_POR_MES
		(FECHA_INF_FACTURAS_POR_MES  
		,COD_USUARIO                 
		,MES                         
		,ANO                         
		,TIPO_DOC                    
		,COD_DOC                     
		,NRO_DOC                     
		,FECHA_DOC                   
		,NOM_EMPRESA                 
		,TOTAL_NETO                  
		,MONTO_IVA                   
		,TOTAL_CON_IVA               
		)
	select @vl_fecha_actual
		,@ve_cod_usuario
		,MONTH(N.FECHA_NOTA_CREDITO) MES
		,year(N.FECHA_NOTA_CREDITO) ANO
		,'NC'	TIPO_DOC
		,N.COD_NOTA_CREDITO COD_DOC
		,N.NRO_NOTA_CREDITO NRO_DOC
		,N.FECHA_NOTA_CREDITO
		,N.NOM_EMPRESA
		,- N.TOTAL_NETO
		,- N.MONTO_IVA
		,- N.TOTAL_CON_IVA
	from	NOTA_CREDITO N
	where	N.COD_ESTADO_DOC_SII in (2,3)
		and N.SUBTOTAL > 0
        and year(N.FECHA_NOTA_CREDITO) = @ve_ano
END
GO
