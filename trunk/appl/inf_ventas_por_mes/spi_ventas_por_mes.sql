--------------- spi_ventas_por_mes --------------
ALTER PROCEDURE [dbo].[spi_ventas_por_mes](@ve_cod_usuario			numeric
									,@ve_ano				numeric)
AS
BEGIN

declare
	@vl_fecha_actual		datetime

	set @vl_fecha_actual = getdate()
	
	-- borra el resultado de informes anteriores del mismo usuario
	delete INF_VENTAS_POR_MES
	where cod_usuario = @ve_cod_usuario

	insert into	INF_VENTAS_POR_MES
		(FECHA_INF_VENTAS_POR_MES  
		,COD_USUARIO               
		,COD_NOTA_VENTA            
    	,MES                       
    	,ANO                       
    	,NOM_MES                   
    	,FECHA_NOTA_VENTA          
    	,NOM_EMPRESA                   
    	,INI_USUARIO  
		,COD_USUARIO_VENDEDOR1             
    	,SUBTOTAL                  
    	,TOTAL_NETO                
    	,TOTAL_VENTA               
    	,PORC_DSCTO                
    	,MONTO_DSCTO               
    	,MONTO_DSCTO_CORPORATIVO   
    	,DESPACHADO_NETO           
    	,COBRADO_NETO              
    	,POR_COBRAR_NETO           
    	,NV_CONFIRMADA             
    	,NV_X_CONFIRMAR            
		,NOM_ESTADO_NOTA_VENTA     
    	,COD_ESTADO_NOTA_VENTA     
    	,CANT_NV                   
	)
	select @vl_fecha_actual
			,@ve_cod_usuario
			,COD_NOTA_VENTA
			,MONTH(NV.FECHA_NOTA_VENTA) MES
			,year(NV.FECHA_NOTA_VENTA) ANO
			,M.NOM_MES
			,NV.FECHA_NOTA_VENTA
			,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 'ANULADA'
				else E.NOM_EMPRESA 
			 end CLIENTE
			,U.INI_USUARIO
			,NV.COD_USUARIO_VENDEDOR1
			,case NV.COD_ESTADO_NOTA_VENTA 
				when 3 then 0
				else ROUND(NV.SUBTOTAL, 0) 
			 end SUBTOTAL
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else ROUND(NV.TOTAL_NETO, 0) 
			 end TOTAL_NETO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else ROUND(NV.TOTAL_NETO - dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'), 0) 
			 end TOTAL_VENTA
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else case NV.SUBTOTAL when 0 then 0
					 else ROUND((NV.SUBTOTAL - NV.TOTAL_NETO) / NV.SUBTOTAL * 100, 1)
					 end
			 end PORC_DSCTO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else ROUND((NV.SUBTOTAL - NV.TOTAL_NETO), 1)
			 end MONTO_DSCTO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 1
				else ROUND(dbo.f_nv_get_resultado(NV.COD_NOTA_VENTA, 'MONTO_DSCTO_CORPORATIVO'), 0)
			 end MONTO_DSCTO_CORPORATIVO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else ROUND(dbo.f_nv_despachado_neto(NV.COD_NOTA_VENTA), 0)
			 end DESPACHADO_NETO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else dbo.f_nv_cobrado_neto(NV.COD_NOTA_VENTA)
			 end COBRADO_NETO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 3 then 0
				else dbo.f_nv_por_cobrar_neto(NV.COD_NOTA_VENTA)
			 end POR_COBRAR_NETO
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 4 then 1
				else 0
			 end NV_CONFIRMADA
			 ,case NV.COD_ESTADO_NOTA_VENTA
				when 1 then 1
				else 0
			 end NV_X_CONFIRMAR
			 ,NOM_ESTADO_NOTA_VENTA
			 ,NV.COD_ESTADO_NOTA_VENTA
			 ,1 CANT_NV
	FROM NOTA_VENTA NV, EMPRESA E, USUARIO U, MES M, ESTADO_NOTA_VENTA ENV
	WHERE E.COD_EMPRESA = NV.COD_EMPRESA
	  AND U.COD_USUARIO = NV.COD_USUARIO_VENDEDOR1
	  AND M.COD_MES = MONTH(NV.FECHA_NOTA_VENTA)
	  and ENV.COD_ESTADO_NOTA_VENTA = NV.COD_ESTADO_NOTA_VENTA 
	  and year(NV.FECHA_NOTA_VENTA) = @ve_ano

	update INF_VENTAS_POR_MES
	set SUBTOTAL                  = 0
    	,TOTAL_NETO               = 0 
    	,TOTAL_VENTA               = 0
    	,PORC_DSCTO                = 0
    	,MONTO_DSCTO               = 0
    	,MONTO_DSCTO_CORPORATIVO   = 0
    	,DESPACHADO_NETO           = 0
    	,COBRADO_NETO              = 0
    	,POR_COBRAR_NETO           = 0
    	,NV_CONFIRMADA             = 0
    	,NV_X_CONFIRMAR            = 0
	where cod_usuario = @ve_cod_usuario
	  and cod_nota_venta in (57630, 57215, 57237, 57708)

END





